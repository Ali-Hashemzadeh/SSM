<?php

namespace App\Services;

use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Cart\CartRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class OrderService
 * Handles the business logic for creating and managing orders.
 */
class OrderService
{
    protected $orderRepository;
    protected $cartRepository;

    /**
     * OrderService constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Confirm the user's cart and convert it into an order.
     * This is the core "checkout" logic.
     *
     * @param int $userId
     * @return \App\Models\Order
     * @throws \Exception
     */
    public function confirmOrder(int $userId)
    {
        // 1. Get the user's cart
        $cart = $this->cartRepository->getWithItems($userId);

        // 2. Check if the cart is empty
        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception('Your cart is empty. Cannot create an order.');
        }

        // 3. Create the order from the cart (this is a transactional snapshot)
        $order = $this->orderRepository->createFromCart($cart);

        // 4. Clear the user's cart
        $this->cartRepository->clearCart($cart->id);

        // 5. Return the new order
        return $order;
    }

    /**
     * Get the order history for a specific user.
     *
     * @param int $userId
     * @return LengthAwarePaginator
     */
    public function getOrderHistory(int $userId): LengthAwarePaginator
    {
        return $this->orderRepository->getForUserPaginated($userId);
    }

    /**
     * Get all orders for the admin panel, with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllOrders(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        return $this->orderRepository->getAllPaginated($filters, $perPage);
    }

    /**
     * Get a single order by ID (for admin).
     *
     * @param int $orderId
     * @return \App\Models\Order|null
     */
    public function getOrderById(int $orderId): ?\App\Models\Order
    {
        return $this->orderRepository->findById($orderId);
    }

    /**
     * Update an order's status (for admin).
     *
     * @param int $orderId
     * @param string $status
     * @return \App\Models\Order
     */
    public function updateOrderStatus(int $orderId, string $status): \App\Models\Order
    {
        // You could add logic here to validate the status (e.g., must be 'pending', 'contacted', 'completed')
        return $this->orderRepository->updateStatus($orderId, $status);
    }

    /**
     * Delete an order (for admin).
     *
     * @param int $orderId
     * @return bool
     */
    public function deleteOrder(int $orderId): bool
    {
        return $this->orderRepository->delete($orderId);
    }
}
