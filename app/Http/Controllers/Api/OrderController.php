<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Order\PlaceOrderRequest;
use App\Services\OrderService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @Group Order API
 */
#[Group('Orders', weight: 3)]
class OrderController extends BaseController
{
    public function __construct(public OrderService $order_service) {}

    /**
     * Place order from cart.
     *
     * Creates orders grouped by store (one order per store). Physical stores require address and city.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array{
     *         orders: array<int, array{
     *             id: int,
     *             store_id: int,
     *             total: int,
     *             status: string
     *         }>
     *     }
     * }
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $customer = $request->user();
            $address_data = $request->input('address_data', []);
            $payment_method = $request->input('payment_method', 'cod');

            $orders = $this->order_service->place_order($customer, $address_data, $payment_method);

            $orders_data = array_map(function ($order) {
                return [
                    'id' => $order->id,
                    'store_id' => $order->store_id,
                    'total' => $order->total,
                    'status' => $order->status->value,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                ];
            }, $orders);

            return $this->success_response([
                'orders' => $orders_data,
            ], 'Orders placed successfully');
        } catch (\InvalidArgumentException $e) {
            return $this->error_response($e->getMessage(), 422);
        }
    }

    /**
     * Get customer's orders (paginated).
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<string, mixed>
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $per_page = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $result = $this->order_service->get_customer_orders($customer, $per_page, $page);

        return $this->paginated_response(
            $result['paginator'],
            $result['data'],
            'Orders retrieved successfully'
        );
    }

    /**
     * Get order details.
     *
     * @param  int  $order  The order ID.
     *
     * @response array{
     *     status: bool,
     *     message: string,
     *     data: array<string, mixed>
     * }
     */
    public function show(Request $request, int $order): JsonResponse
    {
        $customer = $request->user();

        $order_data = $this->order_service->get_order($customer, $order);

        if (! $order_data) {
            return $this->error_response('Order not found', 404);
        }

        return $this->success_response($order_data, 'Order retrieved successfully');
    }
}
