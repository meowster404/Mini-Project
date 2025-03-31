<?php
// Start session for potential user authentication
session_start();

// Include database connection and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is logged in (simplified version)
if(!isset($_SESSION['farmer_id'])) {
    header("Location: login.php");
    exit();
}

// Get farmer info
$farmer_id = $_SESSION['farmer_id'];
$farmer = getFarmerById($farmer_id);

// Get dashboard stats
$stats = getDashboardStats($farmer_id);

// Get recent orders (limit to 5)
$recentOrders = getRecentOrders($farmer_id, 5);

// Get farmer products
$products = getFarmerProducts($farmer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Fresh - Farmer Dashboard</title>
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
                            $categories = getProductCategories();
                            foreach($categories as $category):
                            ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
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