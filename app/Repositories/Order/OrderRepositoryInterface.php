<?php

namespace App\Repositories\Order;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface OrderRepositoryInterface
 * Defines the contract for order data operations.
 */
interface OrderRepositoryInterface
{
    /**
     * Create a new order from a shopping cart.
     *
     * @param Cart $cart
     * @return Order
     */
    public function createFromCart(Cart $cart): Order;

    /**
     * Get a paginated list of all orders (for admin).
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a paginated list of a specific user's orders.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getForUserPaginated(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a specific order by its ID (for admin).
     *
     * @param int $orderId
     * @return Order|null
     */
    public function findById(int $orderId): ?Order;

    /**
     * Update the status of an order.
     *
     * @param int $orderId
     * @param string $status
     * @return Order
     */
    public function updateStatus(int $orderId, string $status): Order;

    /**
     * Delete an order.
     *
     * @param int $orderId
     * @return bool
     */
    public function delete(int $orderId): bool;
}
