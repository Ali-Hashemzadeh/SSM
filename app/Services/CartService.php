<?php

namespace App\Services;

use App\Repositories\Cart\CartRepositoryInterface;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

/**
 * Class CartService
 * Handles the business logic for cart operations.
 */
class CartService
{
    protected $cartRepository;

    /**
     * CartService constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Get the currently authenticated user's cart.
     *
     * @param int $userId
     * @return \App\Models\Cart
     */
    public function getCart(int $userId)
    {
        return $this->cartRepository->getWithItems($userId)
            ?? $this->cartRepository->findOrCreateByUserId($userId);
    }

    /**
     * Add a product to the user's cart.
     * Logic to increment quantity is handled by the repository.
     *
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @return \App\Models\Cart
     */
    public function addProduct(int $userId, int $productId, int $quantity)
    {
        // Ensure the product exists and is purchasable before adding
        Product::findOrFail($productId); // Throws 404 if not found

        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        return $this->cartRepository->addItem($cart->id, $productId, $quantity);
    }

    /**
     * Update an item's quantity in the user's cart.
     *
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @return \App\Models\Cart
     */
    public function updateItemQuantity(int $userId, int $productId, int $quantity)
    {
        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        return $this->cartRepository->updateItemQuantity($cart->id, $productId, $quantity);
    }

    /**
     * Remove a product from the user's cart.
     *
     * @param int $userId
     * @param int $productId
     * @return \App\Models\Cart
     */
    public function removeItem(int $userId, int $productId)
    {
        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        return $this->cartRepository->removeItem($cart->id, $productId);
    }

    /**
     * Clear all items from the user's cart.
     *
     * @param int $userId
     * @return \App\Models\Cart
     */
    public function clearCart(int $userId)
    {
        $cart = $this->cartRepository->findOrCreateByUserId($userId);
        return $this->cartRepository->clearCart($cart->id);
    }
}
