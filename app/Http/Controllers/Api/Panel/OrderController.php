<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Http\Requests\Panel\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller for Admins to manage user orders.
 */
class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * Get a paginated list of all orders.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status']); // e.g., ?status=pending
        $orders = $this->orderService->getAllOrders($filters);
        return OrderResource::collection($orders)->response();
    }

    /**
     * Get a single detailed order.
     */
    public function show(string $id): JsonResponse
    {
        $order = $this->orderService->getOrderById((int) $id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        return response()->json(new OrderResource($order));
    }

    /**
     * Update an order's status.
     */
    public function update(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus((int) $id, $request->status);
        return response()->json(new OrderResource($order));
    }

    /**
     * Delete an order.
     */
    public function destroy(string $id): JsonResponse
    {
        $success = $this->orderService->deleteOrder((int) $id);
        if (!$success) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Order deleted successfully']);
    }
}
