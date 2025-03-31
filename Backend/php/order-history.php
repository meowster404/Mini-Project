<?php
// Start session to maintain user state
session_start();

// Database connection (replace with your actual database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mock user ID (in a real application, this would come from authentication)
$userId = 1;

// Function to get all orders for a user
function getOrderHistory($conn, $userId) {
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status 
            FROM orders o 
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    return $orders;
}

// Get order history
$orders = getOrderHistory($conn, $userId);

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <!-- Using the dedicated CSS file for order-history page -->
    <link rel="stylesheet" href="../css/order-history.css">
</head>
<body>
    <div class="container">
        <h1>My Order History</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr data-order-id="<?php echo $order['order_id']; ?>">
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars(date('F j, Y', strtotime($order['order_date']))); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="my-order.php?order_id=<?php echo $order['order_id']; ?>" class="btn">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        // Simple JavaScript for enhancing the user experience
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event to table rows for easier navigation
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('click', function(e) {
                    // Don't trigger if they clicked the button directly
                    if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
                        const orderId = this.getAttribute('data-order-id');
                        if (orderId) {
                            window.location = 'my-order.php?order_id=' + orderId;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>