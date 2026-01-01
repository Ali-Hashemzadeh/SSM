<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Http\Requests\Client\AddToCartRequest;
use App\Http\Requests\Client\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controller for managing the user's shopping cart.
 */
class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    /**
     * Get the current user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user()->id);
        return response()->json(new CartResource($cart));
    }

    /**
     * Add an item to the cart.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->addProduct(
            $request->user()->id,
            $request->product_id,
            $request->quantity
        );
        return response()->json(new CartResource($cart), 201); // 201 Created
    }

    /**
     * Update an item's quantity in the cart.
     * We use the product_id from the URL.
     */
    public function update(UpdateCartItemRequest $request, string $productId): JsonResponse
    {
        $cart = $this->cartService->updateItemQuantity(
            $request->user()->id,
            (int) $productId,
            $request->quantity
        );
        return response()->json(new CartResource($cart));
    }

    /**
     * Remove an item from the cart.
     * We use the product_id from the URL.
     */
    public function destroy(Request $request, string $productId): JsonResponse
    {
        $cart = $this->cartService->removeItem(
            $request->user()->id,
            (int) $productId
        );
        return response()->json(new CartResource($cart));
    }
}
