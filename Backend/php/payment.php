<?php
// Initialize variables
$errors = [];
$orderComplete = false;

// Start session to access cart data
session_start();

// Remove database connection
// include __DIR__ . '../includes/db.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate shipping information
    if (empty($_POST['firstName'])) {
        $errors['firstName'] = 'First name is required';
    }
    
    if (empty($_POST['lastName'])) {
        $errors['lastName'] = 'Last name is required';
    }
    
    if (empty($_POST['address'])) {
        $errors['address'] = 'Address is required';
    }
    
    if (empty($_POST['city'])) {
        $errors['city'] = 'City is required';
    }
    
    if (empty($_POST['state'])) {
        $errors['state'] = 'State is required';
    }
    
    if (empty($_POST['zipCode'])) {
        $errors['zipCode'] = 'ZIP code is required';
    } elseif (!preg_match('/^\d{5}(-\d{4})?$/', $_POST['zipCode'])) {
        $errors['zipCode'] = 'Invalid ZIP code format';
    }
    
    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^\(\d{3}\) \d{3}-\d{4}$|^\d{3}-\d{3}-\d{4}$/', $_POST['phone'])) {
        $errors['phone'] = 'Invalid phone number format';
    }
    
    // Validate payment information if credit card is selected
    if ($_POST['paymentMethod'] == 'card') {
        if (empty($_POST['cardNumber'])) {
            $errors['cardNumber'] = 'Card number is required';
        } elseif (!preg_match('/^\d{4}\s\d{4}\s\d{4}\s\d{4}$/', $_POST['cardNumber'])) {
            $errors['cardNumber'] = 'Invalid card number format';
        } else {
            // Validate card number using Luhn algorithm
            $cardNumber = str_replace(' ', '', $_POST['cardNumber']);
            if (!validateCreditCard($cardNumber)) {
                $errors['cardNumber'] = 'Invalid credit card number';
            }
        }
        
        if (empty($_POST['expiryDate'])) {
            $errors['expiryDate'] = 'Expiry date is required';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $_POST['expiryDate'])) {
            $errors['expiryDate'] = 'Invalid expiry date format (MM/YY)';
        } else {
            // Check if card is expired
            list($month, $year) = explode('/', $_POST['expiryDate']);
            $expiry = \DateTime::createFromFormat('my', $month . $year);
            $now = new \DateTime();
            if ($expiry < $now) {
                $errors['expiryDate'] = 'Card has expired';
            }
        }
        
        if (empty($_POST['cvv'])) {
            $errors['cvv'] = 'CVV is required';
        } elseif (!preg_match('/^\d{3,4}$/', $_POST['cvv'])) {
            $errors['cvv'] = 'CVV must be 3 or 4 digits';
        }
        
        if (empty($_POST['nameOnCard'])) {
            $errors['nameOnCard'] = 'Name on card is required';
        }
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        // In a real application, you would:
        // 1. Save order to database
        // 2. Process payment
        // 3. Send confirmation email
        // 4. Clear cart
        
        // For demo purposes, just set order as complete
        $orderComplete = true;
        
        // Clear the cart
        unset($_SESSION['cart']);
    }
}

// Function to validate credit card using Luhn algorithm
function validateCreditCard($number) {
    // Remove any non-digits
    $number = preg_replace('/\D/', '', $number);
    
    // Set the string length and parity
    $length = strlen($number);
    $parity = $length % 2;
    
    // Initialize variables
    $total = 0;
    
    // Loop through each digit
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$number[$i];
        
        // Multiply alternate digits by 2
        if ($i % 2 == $parity) {
            $digit *= 2;
            
            // If the result is two digits, add them together
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        
        // Add the current digit to the total
        $total += $digit;
    }
    
    // If the total is divisible by 10, the number is valid
    return ($total % 10 == 0);
}

// Modify the cart items processing to use session data only
$orderItems = [];
$subtotal = 0;
$shipping = 0;

// Check if cart exists in session
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $orderItems[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'] * $item['quantity']
        ];
        
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Use the same shipping logic
    $shipping = ($subtotal >= 200) ? 0 : 50;
} else {
    // Redirect to cart if no items
    header("Location: cart.php");
    exit;
}

// Calculate total
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Fresh Market - Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../Frontend/css/payment.css">
</head>
<body>
    <div class="container">
        <?php if ($orderComplete): ?>
            <div class="order-confirmation">
                <i class="fas fa-check-circle confirmation-icon"></i>
                <h2>Order Placed Successfully!</h2>
                <p>Thank you for your purchase. Your order has been received and is being processed.</p>
                <p>A confirmation email has been sent to your email address.</p>
                <a href="index.php" class="btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
            <a href="cart.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>

            <h1>Checkout</h1>

            <?php if (!empty($errors)): ?>
                <div class="error-summary">
                    <p>Please correct the following errors:</p>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="checkout-content">
                    <div class="form-container">
                        <div class="card">
                            <h2>Shipping Information</h2>

                            <div class="form-row">
                                <div class="form-col">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="firstName" name="firstName" value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>">
                                    <?php if (isset($errors['firstName'])): ?>
                                        <span class="error-message"><?php echo $errors['firstName']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-col">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="lastName" name="lastName" value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>">
                                    <?php if (isset($errors['lastName'])): ?>
                                        <span class="error-message"><?php echo $errors['lastName']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                                <?php if (isset($errors['address'])): ?>
                                    <span class="error-message"><?php echo $errors['address']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                                    <?php if (isset($errors['city'])): ?>
                                        <span class="error-message"><?php echo $errors['city']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-col">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                                    <?php if (isset($errors['state'])): ?>
                                        <span class="error-message"><?php echo $errors['state']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <label for="zipCode">ZIP Code</label>
                                    <input type="text" id="zipCode" name="zipCode" value="<?php echo isset($_POST['zipCode']) ? htmlspecialchars($_POST['zipCode']) : ''; ?>">
                                    <?php if (isset($errors['zipCode'])): ?>
                                        <span class="error-message"><?php echo $errors['zipCode']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-col">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    <?php if (isset($errors['phone'])): ?>
                                        <span class="error-message"><?php echo $errors['phone']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <h2>Payment Method</h2>

                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="cardPayment" name="paymentMethod" value="card" <?php echo (!isset($_POST['paymentMethod']) || $_POST['paymentMethod'] == 'card') ? 'checked' : ''; ?>>
                                    <label for="cardPayment">Credit/Debit Card</label>
                                    <span class="card-icon">
                                        <i class="far fa-credit-card"></i>
                                    </span>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="cashDelivery" name="paymentMethod" value="cash" <?php echo (isset($_POST['paymentMethod']) && $_POST['paymentMethod'] == 'cash') ? 'checked' : ''; ?>>
                                    <label for="cashDelivery">Cash on Delivery</label>
                                </div>
                            </div>

                            <div id="cardDetails">
                                <div class="form-group">
                                    <label for="cardNumber">Card Number</label>
                                    <input type="text" id="cardNumber" name="cardNumber" value="<?php echo isset($_POST['cardNumber']) ? htmlspecialchars($_POST['cardNumber']) : ''; ?>">
                                    <?php if (isset($errors['cardNumber'])): ?>
                                        <span class="error-message"><?php echo $errors['cardNumber']; ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-row">
                                    <div class="form-col">
                                        <label for="expiryDate">Expiry Date</label>
                                        <input type="text" id="expiryDate" name="expiryDate" value="<?php echo isset($_POST['expiryDate']) ? htmlspecialchars($_POST['expiryDate']) : ''; ?>">
                                        <?php if (isset($errors['expiryDate'])): ?>
                                            <span class="error-message"><?php echo $errors['expiryDate']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-col">
                                        <label for="cvv">CVV</label>
                                        <input type="text" id="cvv" name="cvv" maxlength="4" value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>">
                                        <?php if (isset($errors['cvv'])): ?>
                                            <span class="error-message"><?php echo $errors['cvv']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="nameOnCard">Name on Card</label>
                                    <input type="text" id="nameOnCard" name="nameOnCard" value="<?php echo isset($_POST['nameOnCard']) ? htmlspecialchars($_POST['nameOnCard']) : ''; ?>">
                                    <?php if (isset($errors['nameOnCard'])): ?>
                                        <span class="error-message"><?php echo $errors['nameOnCard']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="order-summary">
                        <div class="card">
                            <h2>Order Summary</h2>

                            <?php if (empty($orderItems)): ?>
                                <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
                            <?php else: ?>
                                <?php foreach ($orderItems as $item): ?>
                                    <div class="order-item">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?> ×<?php echo $item['quantity']; ?></span>
                                        <span>₹<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                <?php endforeach; ?>

                                <div class="divider"></div>

                                <div class="order-item">
                                    <span>Subtotal</span>
                                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="order-item">
                                    <span>Shipping</span>
                                    <span><?php echo $shipping > 0 ? '₹' . number_format($shipping, 2) : 'Free'; ?></span>
                                </div>

                                <div class="divider"></div>

                                <div class="total-row">
                                    <span>Total</span>
                                    <span>₹<?php echo number_format($total, 2); ?></span>
                                </div>

                                <button type="submit" class="btn-place-order">Place Order</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cardPayment = document.getElementById('cardPayment');
            const cashDelivery = document.getElementById('cashDelivery');
            const cardDetails = document.getElementById('cardDetails');

            function toggleCardDetails() {
                if (cardPayment.checked) {
                    cardDetails.style.display = 'block';
                } else {
                    cardDetails.style.display = 'none';
                }
            }

            cardPayment.addEventListener('change', toggleCardDetails);
            cashDelivery.addEventListener('change', toggleCardDetails);

            // Initialize form state
            toggleCardDetails();

            // Format card number with spaces
            const cardNumberInput = document.getElementById('cardNumber');
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/g, '');
                let formattedValue = '';
                
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }
                
                e.target.value = formattedValue.substring(0, 19);
            });

            // Format expiry date
            const expiryDateInput = document.getElementById('expiryDate');
            expiryDateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^0-9]/g, '');
                if (value.length > 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value.substring(0, 5);
            });

            // Format phone number
            const phoneInput = document.getElementById('phone');
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                let formattedValue = '';
                
                if (value.length > 0) {
                    formattedValue = '(' + value.substring(0, 3);
                    if (value.length > 3) {
                        formattedValue += ') ' + value.substring(3, 6);
                        if (value.length > 6) {
                            formattedValue += '-' + value.substring(6, 10);
                        }
                    }
                }
                
                e.target.value = formattedValue;
            });

            // Format ZIP code
            const zipCodeInput = document.getElementById('zipCode');
            zipCodeInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 9);
                }
                e.target.value = value.substring(0, 10);
            });
        });
    </script>
</body>
</html>