<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayTabs (Amwal) Payment Gateway Service
 *
 * Uses Managed Form integration (SAQ A-EP PCI compliance)
 * Card details are handled by PayTabs, we only receive payment tokens
 */
class PayTabsService
{
    private string $server_key;

    private string $client_key;

    private string $profile_id;

    private string $base_url;

    private string $currency;

    private string $country;

    public function __construct()
    {
        $this->server_key = config('services.paytabs.server_key', '');
        $this->client_key = config('services.paytabs.client_key', '');
        $this->profile_id = config('services.paytabs.profile_id', '');
        $this->base_url = config('services.paytabs.base_url', 'https://secure-iraq.paytabs.com');
        $this->currency = config('services.paytabs.currency', 'IQD');
        $this->country = config('services.paytabs.country', 'IQ');
    }

    /**
     * Process payment using payment token from managed form.
     *
     * @param  Order  $order  The order to process payment for
     * @param  string  $payment_token  Payment token from PayTabs managed form
     * @param  array<string, mixed>  $customer_data  Customer information
     * @return array<string, mixed>|null
     */
    public function processPayment(Order $order, string $payment_token, array $customer_data): ?array
    {
        if (empty($this->server_key) || empty($this->profile_id)) {
            Log::error('PayTabs configuration missing', [
                'order_id' => $order->id,
            ]);

            return null;
        }

        try {
            $order->load(['customer', 'store', 'city']);

            $request_data = [
                'profile_id' => $this->profile_id,
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => (string) $order->id,
                'cart_currency' => $this->currency,
                'cart_amount' => (float) $order->total, // Amount in IQD (PayTabs expects float in currency units)
                'cart_description' => "Order #{$order->id} - {$order->store->name}",
                'payment_token' => $payment_token,
                'customer_details' => [
                    'name' => $customer_data['name'] ?? $order->customer->name,
                    'email' => $customer_data['email'] ?? $order->customer->email ?? '',
                    'phone' => $customer_data['phone'] ?? $order->customer->phone ?? '',
                    'street1' => $order->address ?? '',
                    'city' => $order->city?->name ?? '',
                    'country' => $this->country,
                    'zip' => '',
                ],
                'shipping_details' => [
                    'name' => $customer_data['name'] ?? $order->customer->name,
                    'email' => $customer_data['email'] ?? $order->customer->email ?? '',
                    'phone' => $customer_data['phone'] ?? $order->customer->phone ?? '',
                    'street1' => $order->address ?? '',
                    'city' => $order->city?->name ?? '',
                    'country' => $this->country,
                    'zip' => '',
                ],
                'callback' => url('/api/v1/payment/callback'),
                'return' => config('app.frontend_url').'/orders/'.$order->id.'?payment=success',
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->server_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/payment/request", $request_data);

            $result = $response->json();

            Log::info('PayTabs payment request', [
                'order_id' => $order->id,
                'response_status' => $response->status(),
                'response' => $result,
            ]);

            // Handle different response types
            if ($response->successful() && isset($result['tran_ref'])) {
                // Check if redirect is required (3D Secure, etc.)
                if (isset($result['redirect_url'])) {
                    // Redirection required
                    return [
                        'success' => true,
                        'requires_redirect' => true,
                        'transaction_ref' => $result['tran_ref'],
                        'redirect_url' => $result['redirect_url'],
                        'response' => $result,
                    ];
                }

                // Direct result (payment completed immediately)
                if (isset($result['payment_result'])) {
                    $payment_result = $result['payment_result'];
                    $response_status = $payment_result['response_status'] ?? '';

                    return [
                        'success' => $response_status === 'A',
                        'transaction_ref' => $result['tran_ref'],
                        'payment_info' => $result['payment_info'] ?? null,
                        'payment_result' => $payment_result,
                        'response' => $result,
                    ];
                }

                // Transaction created but pending
                return [
                    'success' => true,
                    'transaction_ref' => $result['tran_ref'],
                    'payment_info' => $result['payment_info'] ?? null,
                    'response' => $result,
                ];
            }

            // Error response
            $error_message = $result['message'] ?? 'Payment processing failed';

            // Handle PCI DSS compliance error
            if (stripos($error_message, 'PCI DSS') !== false || stripos($error_message, 'SAQ') !== false) {
                Log::error('PayTabs PCI DSS compliance error', [
                    'order_id' => $order->id,
                    'response' => $result,
                    'status' => $response->status(),
                    'note' => 'PayTabs account/profile may need to be configured for SAQ A-EP compliance. Contact PayTabs support to enable managed form integration.',
                ]);

                return [
                    'success' => false,
                    'error' => 'Payment gateway configuration error. Your PayTabs account needs to be configured for SAQ A-EP compliance. Please contact PayTabs support.',
                    'error_code' => $result['code'] ?? 'PCI_DSS_ERROR',
                    'pci_error' => true,
                    'response' => $result,
                ];
            }

            Log::warning('PayTabs payment failed', [
                'order_id' => $order->id,
                'response' => $result,
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'error' => $error_message,
                'error_code' => $result['code'] ?? null,
                'response' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('PayTabs payment exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Payment processing error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment transaction.
     *
     * @param  string  $transaction_ref  Transaction reference from PayTabs
     * @return array<string, mixed>|null
     */
    public function verifyPayment(string $transaction_ref): ?array
    {
        if (empty($this->server_key) || empty($this->profile_id)) {
            Log::error('PayTabs configuration missing for verification');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->server_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/payment/query", [
                'profile_id' => $this->profile_id,
                'tran_ref' => $transaction_ref,
            ]);

            $result = $response->json();

            Log::info('PayTabs payment verification', [
                'transaction_ref' => $transaction_ref,
                'response' => $result,
            ]);

            if ($response->successful() && isset($result['payment_result']['response_status'])) {
                return $result;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayTabs verification exception', [
                'transaction_ref' => $transaction_ref,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process refund for a transaction.
     *
     * @param  string  $transaction_ref  Original transaction reference
     * @param  float  $amount  Refund amount (in currency units)
     * @param  string  $order_id  Order ID for reference
     * @return array<string, mixed>|null
     */
    public function refund(string $transaction_ref, float $amount, string $order_id): ?array
    {
        if (empty($this->server_key) || empty($this->profile_id)) {
            Log::error('PayTabs configuration missing for refund');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->server_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/payment/request", [
                'profile_id' => $this->profile_id,
                'tran_type' => 'refund',
                'tran_ref' => $transaction_ref,
                'cart_id' => $order_id,
                'cart_description' => "Refund for Order #{$order_id}",
                'cart_currency' => $this->currency,
                'cart_amount' => $amount,
            ]);

            $result = $response->json();

            Log::info('PayTabs refund request', [
                'transaction_ref' => $transaction_ref,
                'amount' => $amount,
                'response' => $result,
            ]);

            if ($response->successful() && isset($result['payment_result'])) {
                return $result;
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Refund failed',
                'error_code' => $result['code'] ?? null,
                'response' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('PayTabs refund exception', [
                'transaction_ref' => $transaction_ref,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Void a transaction.
     *
     * @param  string  $transaction_ref  Transaction reference
     * @param  string  $order_id  Order ID for reference
     * @return array<string, mixed>|null
     */
    public function void(string $transaction_ref, string $order_id): ?array
    {
        if (empty($this->server_key) || empty($this->profile_id)) {
            Log::error('PayTabs configuration missing for void');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->server_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/payment/request", [
                'profile_id' => $this->profile_id,
                'tran_type' => 'void',
                'tran_ref' => $transaction_ref,
                'cart_id' => $order_id,
                'cart_description' => "Void for Order #{$order_id}",
                'cart_currency' => $this->currency,
                'cart_amount' => 0, // Void doesn't require amount
            ]);

            $result = $response->json();

            Log::info('PayTabs void request', [
                'transaction_ref' => $transaction_ref,
                'response' => $result,
            ]);

            if ($response->successful() && isset($result['payment_result'])) {
                return $result;
            }

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Void failed',
                'error_code' => $result['code'] ?? null,
                'response' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('PayTabs void exception', [
                'transaction_ref' => $transaction_ref,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Query token details.
     *
     * @param  string  $token  Payment token
     * @return array<string, mixed>|null
     */
    public function queryToken(string $token): ?array
    {
        if (empty($this->server_key) || empty($this->profile_id)) {
            Log::error('PayTabs configuration missing for token query');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->server_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/payment/token", [
                'profile_id' => $this->profile_id,
                'token' => $token,
            ]);

            $result = $response->json();

            if ($response->successful()) {
                return $result;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayTabs token query exception', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete a payment token.
     *
     * @param  string  $token  Payment token to delete
     */
    public function deleteToken(string $token): bool
    {
        if (empty($this->server_key) || empty($this->profile_id)) {
            Log::error('PayTabs configuration missing for token deletion');

            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->server_key,
                'Content-Type' => 'application/json',
            ])->post("{$this->base_url}/payment/token/delete", [
                'profile_id' => $this->profile_id,
                'token' => $token,
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['code']) && $result['code'] == 0) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('PayTabs token deletion exception', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify callback signature.
     *
     * @param  array<string, mixed>  $request_data  Request data from callback
     */
    public function verifySignature(array $request_data): bool
    {
        if (empty($this->server_key)) {
            return false;
        }

        if (! isset($request_data['signature'])) {
            return false;
        }

        $request_signature = $request_data['signature'];
        unset($request_data['signature']);

        // Filter out empty values
        $signature_fields = array_filter($request_data);

        // Sort fields
        ksort($signature_fields);

        // Generate URL-encoded query string
        $query = http_build_query($signature_fields);

        // Generate signature
        $signature = hash_hmac('sha256', $query, $this->server_key);

        // Compare signatures
        return hash_equals($signature, $request_signature);
    }

    /**
     * Get client key for frontend.
     */
    public function getClientKey(): string
    {
        return $this->client_key;
    }
}
