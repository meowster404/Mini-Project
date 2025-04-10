<?php
session_start();

// Dummy farmer data
$farmer = [
    'id' => 1,
    'name' => 'John Doe',
    'profile_image' => null
];

// Dummy statistics
$stats = [
    'total_sales' => 25000,
    'total_orders' => 150,
    'total_products' => 45,
    'total_customers' => 89,
    'sales_change' => 15,
    'orders_change' => 8,
    'new_products' => 5,
    'new_customers' => 12,
    'orders_this_month' => 32,
    'unread_notifications' => 3
];

// Current and last month for comparisons
$current_month = date('Y-m');
$last_month = date('Y-m', strtotime('-1 month'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
    <link rel="stylesheet" href="../../Frontend/css/farmer-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../../assets/image/logo.png" type="image/x-icon">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo-container">
            <a href="../../Frontend/Html/Index.html"><img src="../../assets/image/logo.png" alt="Farmers Marketplace Logo" height="50"></a>
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
                        <a href="farmer-yojana.php"><i class="fas fa-hand-holding-usd"></i> Farmer Yojana</a>
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
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="user-profile">
                        <img src="assets/images/default-profile.jpg" alt="User profile" class="profile-img">
                        <span class="user-name">Ganesh Dubey</span>
                    </div>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Sales</h3>
                            <p class="stat-value">₹<?php echo number_format($stats['total_sales'], 2); ?></p>
                            <p class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                15% from last month
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Total Orders</h3>
                            <p class="stat-value"><?php echo $stats['total_orders']; ?></p>
                            <p class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                8% from last month
                            </p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
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
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
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
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
    .stat-change {
        font-size: 0.9em;
        color: #666;
        margin-top: 5px;
    }
    .stat-change.positive {
        color: #4CAF50;
    }
    .stat-change.negative {
        color: #f44336;
    }
    .stat-icon {
        font-size: 2em;
        color: #4CAF50;
        opacity: 0.8;
    }
    </style>
</body>
</html>