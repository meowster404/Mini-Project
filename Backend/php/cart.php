<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Fresh Market - Your Cart</title>
    <link rel="stylesheet" href="../../Frontend/css/cart.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php
    session_start();
    include 'db.php'; // Database connection file

    // Fetch cart items from the database
    $cartItems = [];
    $subtotal = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $cartIds = implode(',', array_keys($_SESSION['cart']));
        $query = "SELECT * FROM products WHERE id IN ($cartIds)";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $quantity = $_SESSION['cart'][$row['id']];
            $row['quantity'] = $quantity;
            $row['total_item_price'] = $row['price'] * $quantity;
            $cartItems[] = $row;
            $subtotal += $row['total_item_price'];
        }
    }

    // Shipping logic
    $shipping = ($subtotal >= 200) ? 0 : 50;
    $total = $subtotal + $shipping;
    ?>

    <main class="cart-container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart-section">
                <div class="empty-cart-illustration">
                    <img src="/assets/image/emptycart.svg" alt="Empty Cart">
                </div>
                <p class="empty-cart-message">Your cart feels a bit lonely right now.</p>
                <p class="empty-cart-subtext">Looks like you haven't added any items yet.</p>
                <a href="products.php" class="continue-shopping-btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-header-nav">
                <h1>Your Cart</h1>
            </div>
            
            <div class="cart-content">
                <div class="cart-items-section">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-id="<?= $item['id'] ?>">
                            <div class="cart-item-image">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-header">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <span class="cart-item-farm"><?= htmlspecialchars($item['farm'] ?? 'Green Valley Farm') ?></span>
                                </div>
                                <div class="cart-item-footer">
                                    <div class="quantity-control">
                                        <button class="quantity-btn decrease" data-id="<?= $item['id'] ?>">-</button>
                                        <input type="number" value="<?= $item['quantity'] ?>" min="1" data-id="<?= $item['id'] ?>" class="quantity-input">
                                        <button class="quantity-btn increase" data-id="<?= $item['id'] ?>">+</button>
                                    </div>
                                    <div class="cart-item-price">
                                        <span class="unit-price">₹<?= number_format($item['price'], 2) ?></span>
                                        <span class="total-price">₹<?= number_format($item['total_item_price'], 2) ?></span>
                                    </div>
                                    <button class="remove-item" data-id="<?= $item['id'] ?>"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="cart-promo-container">
                        <a href="products.php" class="back-link"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                        <div class="promo-section">
                            <input type="text" placeholder="Promo code" class="promo-input">
                            <button class="promo-apply">Apply</button>
                        </div>
                    </div>
                </div>

                <div class="order-summary-section">
                    <div class="order-summary-card">
                        <h2>Order Summary</h2>
                        
                        <div class="free-delivery-banner">
                            <div class="free-delivery-progress">
                                <div class="progress-bar" style="width: <?= min(($subtotal / 200) * 100, 100) ?>%"></div>
                            </div>
                            <?php if ($subtotal < 200): ?>
                                <p>Add ₹<?= number_format(200 - $subtotal, 2) ?> more to get FREE Delivery!</p>
                            <?php else: ?>
                                <p><i class="fas fa-gift"></i> You've got FREE Delivery!</p>
                            <?php endif; ?>
                        </div>

                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span>₹<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span><?= ($shipping == 0) ? "Free" : "₹" . number_format($shipping, 2) ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total</span>
                                <span>₹<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                        
                        <button class="checkout-btn">Proceed to Checkout</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <script src="../js/cart.js"></script>
    <script src="../js/quantity.js"></script>
</body>
</html>