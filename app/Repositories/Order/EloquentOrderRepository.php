<?php

namespace App\Repositories\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class EloquentOrderRepository
 * Implements the OrderRepositoryInterface using Eloquent.
 */
class EloquentOrderRepository implements OrderRepositoryInterface
{
    /**
     * Create a new order from a shopping cart.
     * This runs in a transaction and creates a snapshot of product data.
     *
     * @param Cart $cart
     * @return Order
     */
    public function createFromCart(Cart $cart): Order
    {
        // Use a database transaction to ensure this either all succeeds or all fails
        return DB::transaction(function () use ($cart) {

            // 1. Create the master Order record
            $order = Order::create([
                'user_id' => $cart->user_id,
                'status' => 'pending', // Default status for a new order
            ]);

            // 2. Load cart items with their associated product data
            // We use withDefault() to prevent a crash if the product was deleted
            $cart->load(['items.product.translation' => function ($query) {
                $query->withDefault();
            }]);

            // 3. Prepare the OrderItem records (the snapshot)
            $orderItems = [];
            foreach ($cart->items as $cartItem) {
                // Get the product, or the 'default' model if it was deleted
                $product = $cartItem->product;
                $translation = $cartItem->product->translation;

                // --- THIS IS THE FIX ---
                // We use null coalescing (??) to provide defaults in case
                // the product was deleted or its data is null.
                $orderItems[] = new OrderItem([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,

                    // --- Create the snapshot ---
                    'title' => $translation->title ?? '[Product Not Found]', // Provides a fallback title
                    'company_name' => $translation->company_name ?? null,
                    'dimensions' => $product->dimensions ?? null,
                    'material' => $translation->material ?? null,
                    'chrome_plating' => $translation->chrome_plating ?? null,
                ]);
            }

            // 4. Save all the new OrderItem models to the Order
            $order->items()->saveMany($orderItems);

            // 5. Return the completed order with its items
            return $order->load('items.product');
        });
    }

    /**
     * Get a paginated list of all orders (for admin).
     * ... existing code ...
     */
    public function getAllPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()->with('user:id,first_name,last_name,mobile,email'); // Eager load user contact info

        // Add filter by status (e.g., /api/panel/orders?status=pending)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Add other filters as needed (e.g., search by user name)

        return $query->latest()->paginate($perPage); // Show newest orders first
    }

    /**
     * Get a paginated list of a specific user's orders (for user's "My Orders" page).
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getForUserPaginated(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::where('user_id', $userId)
            ->with('items.product') // Eager load the items and their (current) product
            ->latest() // Show newest orders first
            ->paginate($perPage);
    }

    /**
     * Find a specific order by its ID (for admin).
     * ... existing code ...
     */
    public function findById(int $orderId): ?Order
    {
        return Order::with('user', 'items.product.media')->find($orderId);
    }

    /**
     * Update the status of an order (e.g., 'pending' -> 'contacted').
     * ... existing code ...
     */
    public function updateStatus(int $orderId, string $status): Order
    {
        $order = Order::findOrFail($orderId);
        $order->status = $status;
        $order->save();
        return $order;
    }

    /**
     * Delete an order.
     * ... existing code ...
     */
    public function delete(int $orderId): bool
    {
        $order = Order::find($orderId);
        if ($order) {
            // Associated OrderItems will be deleted automatically
            // by the database 'onDelete('cascade')' constraint.
            return $order->delete();
        }
        return false;
    }
}
