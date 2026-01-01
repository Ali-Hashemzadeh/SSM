<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Class EloquentCartRepository
 * Implements the CartRepositoryInterface using Eloquent.
 */
class EloquentCartRepository implements CartRepositoryInterface
{
    public function findOrCreateByUserId(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getWithItems(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)
            ->with(['items.product' => function ($query) {
                // Eager load the product and its primary media
                $query->with(['media' => fn($q) => $q->orderBy('display_order')]);
            }])
            ->first();
    }

    public function addItem(int $cartId, int $productId, int $quantity): Cart
    {
        $cart = Cart::find($cartId);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            // Item exists, increment quantity
            $item->quantity += $quantity;
            $item->save();
        } else {
            // Item does not exist, create new
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return $cart->load('items.product.media');
    }

    public function updateItemQuantity(int $cartId, int $productId, int $quantity): Cart
    {
        $cart = Cart::find($cartId);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            if ($quantity > 0) {
                $item->quantity = $quantity;
                $item->save();
            } else {
                // If quantity is 0 or less, remove the item
                $item->delete();
            }
        }

        return $cart->load('items.product.media');
    }

    public function removeItem(int $cartId, int $productId): Cart
    {
        $cart = Cart::find($cartId);
        $cart->items()->where('product_id', $productId)->delete();

        return $cart->load('items.product.media');
    }

    public function clearCart(int $cartId): Cart
    {
        $cart = Cart::find($cartId);
        $cart->items()->delete();
        return $cart;
    }
}
