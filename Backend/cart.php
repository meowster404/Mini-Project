<?php
// Start session to store cart data
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'cart' => []
];

// Check if cart exists in session, if not create it
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle different actions based on request method and parameters
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'get':
        // Return the current cart
        $response['success'] = true;
        $response['cart'] = $_SESSION['cart'];
        break;
        
    case 'add':
        // Add item to cart
        if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            $productData = isset($_POST['product_data']) ? json_decode($_POST['product_data'], true) : null;
            
            // Find if product already exists in cart
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] === $productId) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            // If product not found in cart, add it
            if (!$found) {
                $newItem = [
                    'id' => $productId,
                    'quantity' => $quantity
                ];
                
                // Add additional product data if available
                if ($productData) {
                    $newItem['name'] = $productData['name'];
                    $newItem['price'] = $productData['price'];
                    $newItem['image'] = $productData['image'];
                }
                
                $_SESSION['cart'][] = $newItem;
            }
            
            $response['success'] = true;
            $response['message'] = 'Product added to cart';
            $response['cart'] = $_SESSION['cart'];
        } else {
            $response['message'] = 'Missing product_id or quantity';
        }
        break;
        
    case 'update':
        // Update item quantity
        if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
                    return $item['id'] !== $productId;
                });
            } else {
                // Update quantity
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] === $productId) {
                        $item['quantity'] = $quantity;
                        break;
                    }
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Cart updated';
            $response['cart'] = $_SESSION['cart'];
        } else {
            $response['message'] = 'Missing product_id or quantity';
        }
        break;
        
    case 'remove':
        // Remove item from cart
        if (isset($_POST['product_id'])) {
            $productId = intval($_POST['product_id']);
            
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
                return $item['id'] !== $productId;
            });
            
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
            $response['cart'] = $_SESSION['cart'];
        } else {
            $response['message'] = 'Missing product_id';
        }
        break;
        
    case 'clear':
        // Clear the entire cart
        $_SESSION['cart'] = [];
        $response['success'] = true;
        $response['message'] = 'Cart cleared';
        break;
        
    case 'sync':
        // Sync cart with client-side cart
        if (isset($_POST['cart'])) {
            $clientCart = json_decode($_POST['cart'], true);
            
            if (is_array($clientCart)) {
                $_SESSION['cart'] = $clientCart;
                $response['success'] = true;
                $response['message'] = 'Cart synced';
                $response['cart'] = $_SESSION['cart'];
            } else {
                $response['message'] = 'Invalid cart data';
            }
        } else {
            $response['message'] = 'Missing cart data';
        }
        break;
        
    case 'promo':
        // Apply promo code
        if (isset($_POST['code'])) {
            $code = strtoupper($_POST['code']);
            
            // Store promo code in session
            $_SESSION['promo_code'] = $code;
            
            // In a real application, you would validate the promo code against a database
            // For this example, we'll just accept any code
            $response['success'] = true;
            $response['message'] = 'Promo code applied';
        } else {
            $response['message'] = 'Missing promo code';
        }
        break;
        
    default:
        // If no action specified, redirect to the cart HTML page
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            header('Location: ../../Frontend/Html/cart.html');
            exit;
        }
        
        $response['message'] = 'Invalid action';
        break;
}

// Output JSON response
echo json_encode($response);
exit;
?>