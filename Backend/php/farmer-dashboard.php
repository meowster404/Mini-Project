<?php
// Start session and set secure headers
session_start();
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Include database connection and functions
require_once 'db.php';
require_once 'includes/functions.php';

// Strong authentication check
if (!isset($_SESSION['farmer_id']) || !isset($_SESSION['csrf_token'])) {
    header("Location: login.php");
    exit();
}

// Initialize error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

try {
    // Get farmer info with prepared statement
    $farmer_id = filter_var($_SESSION['farmer_id'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $conn->prepare("SELECT * FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $farmer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$farmer) {
        throw new Exception("Farmer not found");
    }

    // Get dashboard stats using prepared statements
    $stats = [
        'total_sales' => 0,
        'total_orders' => 0,
        'total_products' => 0,
        'total_customers' => 0,
        'sales_change' => 0,
        'orders_change' => 0,
        'new_products' => 0,
        'new_customers' => 0,
        'orders_this_month' => 0,
        'unread_notifications' => 0
    ];

    // Get recent orders with prepared statement
    $stmt = $conn->prepare("
        SELECT o.*, c.name as customer_name 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.farmer_id = ? 
        ORDER BY o.order_date DESC 
        LIMIT 5
    ");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $recentOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get farmer products from dummy data
    $jsonFile = file_get_contents('../../Frontend/js/dummy-data.js');
    $pattern = '/const productData = (.+?);/s';
    preg_match($pattern, $jsonFile, $matches);
    $products = json_decode($matches[1], true);

    // Filter products for current farmer
    $products = array_filter($products, function($product) use ($farmer_id) {
        return $product['farmer_id'] == $farmer_id;
    });

    // Update product stats
    $stats['total_products'] = count($products);
    $stats['new_products'] = array_reduce($products, function($carry, $product) use ($current_month) {
        return $carry + (date('Y-m', strtotime($product['created_at'])) === $current_month ? 1 : 0);
    }, 0);

    // Get total sales and orders
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_sales,
            SUM(CASE WHEN DATE_FORMAT(order_date, '%Y-%m') = ? THEN 1 ELSE 0 END) as orders_this_month,
            SUM(CASE WHEN DATE_FORMAT(order_date, '%Y-%m') = ? THEN total_amount ELSE 0 END) as sales_this_month,
            SUM(CASE WHEN DATE_FORMAT(order_date, '%Y-%m') = ? THEN total_amount ELSE 0 END) as sales_last_month
        FROM orders 
        WHERE farmer_id = ?
    ");
    $stmt->bind_param("sssi", $current_month, $current_month, $last_month, $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Calculate stats
    $stats = [
        'total_sales' => $result['total_sales'] ?? 0,
        'total_orders' => $result['total_orders'] ?? 0,
        'orders_this_month' => $result['orders_this_month'] ?? 0,
        'sales_change' => 0
    ];

    // Calculate sales change percentage
    if ($result['sales_last_month'] > 0) {
        $stats['sales_change'] = round((($result['sales_this_month'] - $result['sales_last_month']) / $result['sales_last_month']) * 100);
    }

    // Get product stats
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN 1 ELSE 0 END) as new_products
        FROM products 
        WHERE farmer_id = ?
    ");
    $stmt->bind_param("si", $current_month, $farmer_id);
    $stmt->execute();
    $product_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stats['total_products'] = $product_stats['total_products'] ?? 0;
    $stats['new_products'] = $product_stats['new_products'] ?? 0;

    // Get customer stats
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT customer_id) as total_customers,
            COUNT(DISTINCT CASE WHEN DATE_FORMAT(order_date, '%Y-%m') = ? THEN customer_id END) as new_customers
        FROM orders 
        WHERE farmer_id = ?
    ");
    $stmt->bind_param("si", $current_month, $farmer_id);
    $stmt->execute();
    $customer_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stats['total_customers'] = $customer_stats['total_customers'] ?? 0;
    $stats['new_customers'] = $customer_stats['new_customers'] ?? 0;

} catch (Exception $e) {
    error_log("Error in farmer dashboard: " . $e->getMessage());
    $error_message = "An error occurred. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>EcoKart - Farmer Dashboard</title>
    <link rel="stylesheet" href="../css/farmer-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo-container">
                <h1 class="logo">Farm Fresh</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li class="active">
                        <a href="farmer-dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="products.php"><i class="fas fa-box"></i> Products</a>
                    </li>
                    <li>
                        <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                    </li>
                    <li>
                        <a href="customers.php"><i class="fas fa-users"></i> Customers</a>
                    </li>
                    <li>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">Farmer Dashboard</h2>
                <div class="user-actions">
                    <button class="notification-btn">
                        <i class="far fa-bell"></i>
                        <?php if($stats['unread_notifications'] > 0): ?>
                            <span class="notification-badge"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="user-profile">
                        <img src="<?php echo $farmer['profile_image'] ?: 'assets/images/default-profile.jpg'; ?>" alt="User profile" class="profile-img">
                        <span class="user-name"><?php echo htmlspecialchars($farmer['name']); ?></span>
                    </div>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Sales</h3>
                            <p class="stat-value">$<?php echo number_format($stats['total_sales'], 2); ?></p>
                            <p class="stat-change <?php echo $stats['sales_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fas fa-arrow-<?php echo $stats['sales_change'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs($stats['sales_change']); ?>% from last month
                            </p>
                        </div>
                        <div class="stat-icon sales-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Orders</h3>
                            <p class="stat-value"><?php echo $stats['total_orders']; ?></p>
                            <p class="stat-change <?php echo $stats['orders_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fas fa-arrow-<?php echo $stats['orders_change'] >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs($stats['orders_change']); ?>% from last month
                            </p>
                        </div>
                        <div class="stat-icon orders-icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Products</h3>
                            <p class="stat-value"><?php echo $stats['total_products']; ?></p>
                            <p class="stat-change">
                                +<?php echo $stats['new_products']; ?> new this month
                            </p>
                        </div>
                        <div class="stat-icon products-icon">
                            <i class="fas fa-cube"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Customers</h3>
                            <p class="stat-value"><?php echo $stats['total_customers']; ?></p>
                            <p class="stat-change">
                                +<?php echo $stats['new_customers']; ?> new this month
                            </p>
                        </div>
                        <div class="stat-icon customers-icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
                <div class="dashboard-sections">
                    <section class="recent-orders">
                        <div class="section-header">
                            <div>
                                <h2>Recent Orders</h2>
                                <p>You have <?php echo $stats['orders_this_month']; ?> orders this month</p>
                            </div>
                            <a href="orders.php" class="btn view-all-btn">View All</a>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($recentOrders) > 0): ?>
                                        <?php foreach($recentOrders as $order): ?>
                                            <tr data-order-id="<?php echo $order['id']; ?>">
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $order['status']; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="no-records">No recent orders</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <section class="your-products">
                        <div class="section-header">
                            <div>
                                <h2>Your Products</h2>
                                <p>You have <?php echo count($products); ?> products listed</p>
                            </div>
                            <button class="btn add-product-btn"><i class="fas fa-plus"></i> Add Product</button>
                        </div>
                        <div class="products-list">
                            <?php if(count($products) > 0): ?>
                                <?php foreach($products as $product): ?>
                                    <div class="product-item">
                                        <div class="product-info">
                                            <div class="product-image">
                                                <img src="<?php echo $product['image'] ?: 'assets/images/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                            </div>
                                            <div class="product-details">
                                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                                <p>$<?php echo number_format($product['price'], 2); ?> • <?php echo $product['stock']; ?> in stock</p>
                                            </div>
                                        </div>
                                        <div class="product-actions" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-products">
                                    <p>You don't have any products yet.</p>
                                    <p>Start by adding your first product!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="view-all-products">
                            <a href="products.php" class="btn secondary-btn">Manage All Products</a>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Product Modal -->
    <div class="modal" id="addProductModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" action="includes/process_product.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="productName">Product Name</label>
                        <input type="text" id="productName" name="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Description</label>
                        <textarea id="productDescription" name="productDescription" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="productCategory">Category</label>
                        <select id="productCategory" name="productCategory">
                            <option value="">Select a category</option>
                            <?php
                            $categories = [
                                ['id' => 'vegetables', 'name' => 'Vegetables'],
                                ['id' => 'fruits', 'name' => 'Fruits'],
                                ['id' => 'dairy', 'name' => 'Dairy'],
                                ['id' => 'meat', 'name' => 'Meat'],
                                ['id' => 'grains', 'name' => 'Grains']
                            ];
                            foreach($categories as $category):
                            ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Price ($)</label>
                        <input type="number" id="productPrice" name="productPrice" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stock Quantity</label>
                        <input type="number" id="productStock" name="productStock" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="productImage">Product Image</label>
                        <input type="file" id="productImage" name="productImage">
                    </div>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="farmer_id" value="<?php echo $farmer_id; ?>">
                    <div class="form-actions">
                        <button type="button" class="btn secondary-btn cancel-btn">Cancel</button>
                        <button type="submit" class="btn primary-btn">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal" id="orderDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Order Details: #<span id="orderIdDetail"></span></h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded via Ajax -->
                <div class="loading">Loading order details...</div>
            </div>
        </div>
    </div>

    <!-- Product Actions Menu -->
    <div class="dropdown-menu" id="productActionsMenu">
        <ul>
            <li data-action="edit"><i class="fas fa-edit"></i> Edit Product</li>
            <li data-action="view"><i class="fas fa-eye"></i> View Details</li>
            <li data-action="delete" class="text-danger"><i class="fas fa-trash"></i> Delete</li>
        </ul>
    </div>

    <div class="overlay" id="overlay"></div>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>

    // Add CSRF token to all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    document.addEventListener('DOMContentLoaded', function() {
        // Sanitize data before displaying
        function sanitizeHTML(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    
        // Add error handling for AJAX requests
        function handleAjaxError(error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again later.');
        }
    
        // ... Rest of your JavaScript code ...
        });
    </script>
</body>
</html>