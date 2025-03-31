<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POST requests for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?? 1;
    
    switch ($action) {
        case 'add':
            // Get product data from request
            $product_data = isset($_POST['product_data']) ? json_decode($_POST['product_data'], true) : null;
            
            if ($product_data) {
                if (!isset($_SESSION['cart_products'])) {
                    $_SESSION['cart_products'] = [];
                }
                $_SESSION['cart_products'][$product_id] = $product_data;
            }
            
            // Add or update item in cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            
            $response = [
                'success' => true,
                'cart' => $_SESSION['cart'],
                'products' => $_SESSION['cart_products']
            ];
            echo json_encode($response);
            break;
            
        case 'update':
            $_SESSION['cart'][$product_id] = max(1, $quantity);
            echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
            break;
            
        case 'remove':
            unset($_SESSION['cart'][$product_id]);
            echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Handle GET requests - display cart page
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $cartItems = [];
    $total = 0;
    
    // Get cart items details if cart is not empty
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            if ($product) {
                $subtotal = $product['price'] * $quantity;
                $cartItems[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'image' => $product['image']
                ];
                $total += $subtotal;
            }
        }
    }
    
    // Calculate shipping
    $shipping = ($total >= 200) ? 0 : 50;
    $finalTotal = $total + $shipping;
    
    // Start output buffering
    ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Farm Fresh Market</title>
    <link rel="stylesheet" href="../../Frontend/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <main class="cart-container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart-section">
                <div class="empty-cart-illustration">
                    <i class="fas fa-shopping-cart fa-4x"></i>
                </div>
                <h2 class="empty-cart-message">Your cart feels a bit lonely right now.</h2>
                <p class="empty-cart-subtext">Looks like you haven't added any items yet.</p>
                <a href="../../Frontend/Html/products.html" class="continue-shopping-btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items-section">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-id="<?= htmlspecialchars($item['id']) ?>">
                            <div class="cart-item-image">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-header">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <button class="remove-item" onclick="removeFromCart(<?= $item['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="cart-item-footer">
                                    <div class="quantity-control">
                                        <button class="quantity-btn decrease" onclick="updateQuantity(<?= $item['id'] ?>, -1)">-</button>
                                        <input type="number" value="<?= $item['quantity'] ?>" class="quantity-input" readonly>
                                        <button class="quantity-btn increase" onclick="updateQuantity(<?= $item['id'] ?>, 1)">+</button>
                                    </div>
                                    <div class="cart-item-price">
                                        <span class="unit-price">₹<?= number_format($item['price'], 2) ?> each</span>
                                        <span class="total-price">₹<?= number_format($item['subtotal'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary-section">
                    <div class="order-summary-card">
                        <h2>Order Summary</h2>
                        <?php if ($total < 200): ?>
                            <div class="free-delivery-banner">
                                <p>Add ₹<?= number_format(200 - $total, 2) ?> more to get FREE Delivery!</p>
                                <div class="free-delivery-progress">
                                    <div class="progress-bar" style="width: <?= ($total / 200) * 100 ?>%"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="free-delivery-banner">
                                <p>You've got FREE Delivery!</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>₹<?= number_format($total, 2) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span><?= $shipping > 0 ? '₹' . number_format($shipping, 2) : 'FREE' ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>₹<?= number_format($finalTotal, 2) ?></span>
                            </div>
                        </div>
                        
                        <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="../../Frontend/js/cart.js"></script>
</body>
</html>
<?php
    // End output buffering and send content
    echo ob_get_clean();
}
?>