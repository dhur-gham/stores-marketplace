<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\PayTabsService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @Group Payment API
 */
#[Group('Payments', weight: 4)]
class PaymentController extends BaseController
{
    public function __construct(public PayTabsService $paytabs_service) {}

    /**
     * Process payment for an order using PayTabs token.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         order_id: int,
     *         payment_status: string,
     *         transaction_ref: string|null,
     *         redirect_url: string|null
     *     }
     * }
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payment_token' => 'required|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        $customer = $request->user();
        $order = Order::query()
            ->where('id', $request->input('order_id'))
            ->where('customer_id', $customer->id)
            ->first();

        if (! $order) {
            return $this->error_response('Order not found or access denied', 404);
        }

        if ($order->payment_status === PaymentStatus::Completed->value) {
            return $this->error_response('Order already paid', 422);
        }

        $customer_data = [
            'name' => $request->input('customer_name', $customer->name),
            'email' => $request->input('customer_email', $customer->email ?? ''),
            'phone' => $request->input('customer_phone', $customer->phone ?? ''),
        ];

        $payment_result = $this->paytabs_service->processPayment(
            $order,
            $request->input('payment_token'),
            $customer_data
        );

        if (! $payment_result || ! ($payment_result['success'] ?? false)) {
            // Update order payment status to failed
            $order->update([
                'payment_status' => PaymentStatus::Failed->value,
                'payment_metadata' => $payment_result ?? null,
            ]);

            $error_message = $payment_result['error'] ?? 'Payment processing failed';
            
            // Provide more helpful error for PCI DSS issues
            if (isset($payment_result['pci_error']) && $payment_result['pci_error']) {
                // Log full error details for debugging
                Log::error('PayTabs PCI DSS error - Full details', [
                    'order_id' => $order->id,
                    'full_error' => $payment_result['error'] ?? null,
                    'error_code' => $payment_result['error_code'] ?? null,
                    'paytabs_response' => $payment_result['response'] ?? null,
                    'note' => 'Contact PayTabs support to enable SAQ A-EP compliance for your account',
                ]);
                
                // In development/debug mode, show full error details
                if (config('app.debug')) {
                    $error_message = $payment_result['error'] ?? 'Payment gateway configuration error. Your PayTabs account needs SAQ A-EP compliance enabled. Contact PayTabs support.';
                } else {
                    $error_message = 'Payment gateway configuration error. Your PayTabs account needs SAQ A-EP compliance enabled. Please contact PayTabs support.';
                }
            }

            return $this->error_response($error_message, 422);
        }

        // Update order with payment information
        $order->update([
            'payment_method' => 'paytabs',
            'payment_status' => PaymentStatus::Processing->value,
            'payment_transaction_id' => $payment_result['transaction_ref'] ?? null,
            'payment_metadata' => $payment_result,
        ]);

        // Check if redirect is required (3D Secure, etc.)
        if (isset($payment_result['requires_redirect']) && $payment_result['requires_redirect']) {
            return $this->success_response([
                'order_id' => $order->id,
                'payment_status' => $order->payment_status,
                'transaction_ref' => $payment_result['transaction_ref'] ?? null,
                'requires_redirect' => true,
                'redirect_url' => $payment_result['redirect_url'] ?? null,
            ], 'Payment requires 3D Secure authentication');
        }

        // Check if payment was completed immediately
        if (isset($payment_result['payment_result']) && $payment_result['payment_result']['response_status'] === 'A') {
            // Payment completed immediately, update order
            $order->update([
                'payment_status' => PaymentStatus::Completed->value,
                'payment_transaction_id' => $payment_result['transaction_ref'] ?? null,
                'paid_at' => now(),
                'payment_metadata' => $payment_result,
            ]);

            if ($order->status->value === 'new') {
                $order->update(['status' => \App\Enums\OrderStatus::Processing]);
                $order->recordStatusChange(\App\Enums\OrderStatus::Processing);
            }
        }

        return $this->success_response([
            'order_id' => $order->id,
            'payment_status' => $order->payment_status,
            'transaction_ref' => $payment_result['transaction_ref'] ?? null,
            'redirect_url' => $payment_result['redirect_url'] ?? null,
        ], 'Payment processed successfully');
    }

    /**
     * PayTabs payment callback/webhook.
     *
     * This endpoint is called by PayTabs after payment processing.
     */
    public function callback(Request $request): JsonResponse
    {
        Log::info('PayTabs callback received', [
            'request_data' => $request->all(),
        ]);

        // Verify signature if present (recommended by PayTabs)
        $request_data = $request->all();
        if (isset($request_data['signature'])) {
            if (! $this->paytabs_service->verifySignature($request_data)) {
                Log::warning('PayTabs callback signature verification failed', [
                    'request' => $request_data,
                ]);

                return $this->error_response('Invalid signature', 400);
            }
        }

        $transaction_ref = $request->input('tranRef');
        $cart_id = $request->input('cartId');

        if (empty($transaction_ref) || empty($cart_id)) {
            Log::warning('PayTabs callback missing required data', [
                'request' => $request->all(),
            ]);

            return $this->error_response('Invalid callback data', 400);
        }

        // Verify payment with PayTabs
        $verification = $this->paytabs_service->verifyPayment($transaction_ref);

        if (! $verification) {
            Log::warning('PayTabs payment verification failed', [
                'transaction_ref' => $transaction_ref,
                'cart_id' => $cart_id,
            ]);

            return $this->error_response('Payment verification failed', 400);
        }

        $payment_result = $verification['payment_result'] ?? [];
        $response_status = $payment_result['response_status'] ?? '';

        // Handle response status from callback (may be different field name)
        if (empty($response_status)) {
            $response_status = $request->input('respStatus', '');
        }

        $order = Order::query()->find($cart_id);

        if (! $order) {
            Log::error('PayTabs callback: Order not found', [
                'cart_id' => $cart_id,
            ]);

            return $this->error_response('Order not found', 404);
        }

        DB::beginTransaction();

        try {
            if ($response_status === 'A') {
                // Payment approved
                $order->update([
                    'payment_status' => PaymentStatus::Completed->value,
                    'payment_transaction_id' => $transaction_ref,
                    'payment_reference' => $payment_result['payment_info']['payment_reference'] ?? null,
                    'paid_at' => now(),
                    'payment_metadata' => $verification,
                ]);

                // Update order status to processing if it's still new
                if ($order->status->value === 'new') {
                    $order->update(['status' => \App\Enums\OrderStatus::Processing]);
                    $order->recordStatusChange(\App\Enums\OrderStatus::Processing);
                }

                // Return HTTP 200 or 201 as required by PayTabs
                return response()->json([
                    'status' => true,
                    'message' => 'Payment completed successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'status' => 'completed',
                    ],
                ], 200);

                DB::commit();

                Log::info('PayTabs payment completed', [
                    'order_id' => $order->id,
                    'transaction_ref' => $transaction_ref,
                ]);
            } else {
                // Payment failed
                $order->update([
                    'payment_status' => PaymentStatus::Failed->value,
                    'payment_transaction_id' => $transaction_ref,
                    'payment_metadata' => $verification,
                ]);

                DB::commit();

                Log::warning('PayTabs payment failed', [
                    'order_id' => $order->id,
                    'transaction_ref' => $transaction_ref,
                    'response_status' => $response_status,
                ]);

                // Return HTTP 200 even on failure (PayTabs requirement)
                return response()->json([
                    'status' => false,
                    'message' => 'Payment failed',
                    'data' => [
                        'order_id' => $order->id,
                        'status' => 'failed',
                    ],
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('PayTabs callback exception', [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return HTTP 200 even on error (PayTabs requirement)
            return response()->json([
                'status' => false,
                'message' => 'Payment processing error',
            ], 200);
        }
    }

    /**
     * Process refund for a payment.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<string, mixed>
     * }
     */
    public function refund(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $customer = $request->user();
        $order = Order::query()
            ->where('id', $request->input('order_id'))
            ->where('customer_id', $customer->id)
            ->first();

        if (! $order) {
            return $this->error_response('Order not found or access denied', 404);
        }

        if (empty($order->payment_transaction_id)) {
            return $this->error_response('Order has no payment transaction', 422);
        }

        $amount = $request->input('amount', $order->total);

        $refund_result = $this->paytabs_service->refund(
            $order->payment_transaction_id,
            (float) $amount,
            (string) $order->id
        );

        if (! $refund_result || ! ($refund_result['success'] ?? false)) {
            return $this->error_response(
                $refund_result['error'] ?? 'Refund failed',
                422
            );
        }

        // Update order payment status
        $order->update([
            'payment_status' => PaymentStatus::Refunded->value,
            'payment_metadata' => array_merge($order->payment_metadata ?? [], [
                'refund' => $refund_result,
                'refunded_at' => now()->toISOString(),
            ]),
        ]);

        return $this->success_response([
            'order_id' => $order->id,
            'refund_result' => $refund_result,
        ], 'Refund processed successfully');
    }

    /**
     * Void a payment transaction.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<string, mixed>
     * }
     */
    public function void(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $customer = $request->user();
        $order = Order::query()
            ->where('id', $request->input('order_id'))
            ->where('customer_id', $customer->id)
            ->first();

        if (! $order) {
            return $this->error_response('Order not found or access denied', 404);
        }

        if (empty($order->payment_transaction_id)) {
            return $this->error_response('Order has no payment transaction', 422);
        }

        $void_result = $this->paytabs_service->void(
            $order->payment_transaction_id,
            (string) $order->id
        );

        if (! $void_result || ! ($void_result['success'] ?? false)) {
            return $this->error_response(
                $void_result['error'] ?? 'Void failed',
                422
            );
        }

        // Update order payment status
        $order->update([
            'payment_status' => PaymentStatus::Cancelled->value,
            'payment_metadata' => array_merge($order->payment_metadata ?? [], [
                'void' => $void_result,
                'voided_at' => now()->toISOString(),
            ]),
        ]);

        return $this->success_response([
            'order_id' => $order->id,
            'void_result' => $void_result,
        ], 'Transaction voided successfully');
    }

    /**
     * Get PayTabs client key for frontend.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         client_key: string
     *     }
     * }
     */
    public function getClientKey(): JsonResponse
    {
        $client_key = $this->paytabs_service->getClientKey();

        if (empty($client_key)) {
            return $this->error_response('PayTabs client key not configured', 500);
        }

        return $this->success_response([
            'client_key' => $client_key,
        ], 'Client key retrieved successfully');
    }
}
