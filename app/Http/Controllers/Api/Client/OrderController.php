<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AddToCartRequest;
use App\Services\CartService;
use App\Services\OrderService;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller for the authenticated user to manage their orders.
 */
class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService, protected CartService $cartService)
    {
    }

    /**
     * Get the user's order history.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getOrderHistory($request->user()->id);
        return OrderResource::collection($orders)->response();
    }

    /**
     * "Confirm" the cart and create a new order.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $order = $this->orderService->confirmOrder($request->user()->id);
            return response()->json(new OrderResource($order), 201); // 201 Created

        } catch (\Exception $e) {
            Log::error('Order confirmation failed for user: ' . $request->user()->id, [
                'error' => $e->getMessage()
            ]);
            // Return a user-friendly error
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422); // 422 Unprocessable Entity (e.g., cart was empty)
        }
    }
    public function orderAndConfirm(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->addProduct(
            $request->user()->id,
            $request->product_id,
            $request->quantity
        );
        $order = $this->orderService->confirmOrder($request->user()->id);
        return response()->json(new OrderResource($order), 201);
    }
}
