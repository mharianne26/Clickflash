<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OrderController;

// Test 1: Check if we have users and products
echo "=== CHECKOUT SYSTEM TEST ===\n\n";

echo "1. Database Status:\n";
echo "   Users: " . User::count() . "\n";
echo "   Orders: " . Order::count() . "\n";
echo "   Products: " . \App\Models\Product::count() . "\n\n";

// Test 2: Simulate checkout process
echo "2. Testing Checkout Process:\n";

$user = User::first();
if (!$user) {
    echo "   ❌ No users found. Please create a user first.\n";
    exit(1);
}

echo "   User found: " . $user->email . "\n";

// Simulate authentication
Auth::login($user);

$cartData = [
    'cart' => [
        [
            'id' => 1,
            'name' => 'Test Product',
            'price' => 100.00,
            'quantity' => 1
        ]
    ],
    'subtotal' => 100.00,
    'shipping' => 50.00,
    'total' => 150.00
];

// Create a mock request
$request = new Request();
$request->merge([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'address' => '123 Main St',
    'city' => 'Test City',
    'postal_code' => '12345',
    'country' => 'Test Country',
    'payment_method' => 'cod',
    'cart_data' => json_encode($cartData)
]);

// Test the processCheckout method
$controller = new OrderController(app(\App\Services\CartService::class));

try {
    $response = $controller->processCheckout($request);
    $responseData = json_decode($response->getContent(), true);

    if ($responseData['success']) {
        echo "   ✅ Order created successfully!\n";
        echo "   Order ID: " . $responseData['order_id'] . "\n";
        echo "   Redirect URL: " . $responseData['redirect_url'] . "\n";

        // Check if order was actually created
        $order = Order::latest()->first();
        if ($order) {
            echo "   ✅ Order found in database\n";
            echo "   Order Number: " . $order->order_number . "\n";
            echo "   Total: $" . $order->total . "\n";
            echo "   Status: " . $order->status . "\n";
            echo "   Items: " . $order->items->count() . "\n";
        } else {
            echo "   ❌ Order not found in database\n";
        }
    } else {
        echo "   ❌ Order creation failed: " . $responseData['message'] . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
