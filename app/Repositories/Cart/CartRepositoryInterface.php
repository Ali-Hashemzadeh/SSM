<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Models\Product;

/**
 * Interface CartRepositoryInterface
 * Defines the contract for cart data operations.
 */
interface CartRepositoryInterface
{
    /**
     * Find a cart by user ID, or create it if it doesn't exist.
     *
     * @param int $userId
     * @return Cart
     */
    public function findOrCreateByUserId(int $userId): Cart;

    /**
     * Get the user's cart with all items and product details.
     *
     * @param int $userId
     * @return Cart|null
     */
    public function getWithItems(int $userId): ?Cart;

    /**
     * Add or update an item in the cart.
     *
     * @param int $cartId
     * @param int $productId
     * @param int $quantity
     * @return Cart
     */
    public function addItem(int $cartId, int $productId, int $quantity): Cart;

    /**
     * Update the quantity of an item in the cart.
     *
     * @param int $cartId
     * @param int $productId
     * @param int $quantity
     * @return Cart
     */
    public function updateItemQuantity(int $cartId, int $productId, int $quantity): Cart;

    /**
     * Remove an item from the cart.
     *
     * @param int $cartId
     * @param int $productId
     * @return Cart
     */
    public function removeItem(int $cartId, int $productId): Cart;

    /**
     * Clear all items from the cart.
     *
     * @param int $cartId
     * @return Cart
     */
    public function clearCart(int $cartId): Cart;
}
