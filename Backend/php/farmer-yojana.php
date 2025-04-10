<?php
session_start();

// Dummy farmer data for header
$farmer = [
    'name' => 'John Doe',
    'profile_image' => null
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Yojana</title>
    <link rel="stylesheet" href="../../Frontend/css/farmer-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../../assets/image/logo.png" type="image/x-icon">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo-container">
            <a href="#home"><img src="../../assets/image/logo.png" alt="Farmers Marketplace Logo" height="50"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li>
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
                    <li class="active">
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
                <h2 class="page-title">Farmer Yojana</h2>
                <div class="user-actions">
                    <div class="user-profile">
                        <img src="assets/images/default-profile.jpg" alt="User profile" class="profile-img">
                        <span class="user-name"><?php echo htmlspecialchars($farmer['name']); ?></span>
                    </div>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="yojana-container">
                    <div class="yojana-card">
                        <div class="yojana-header">
                            <i class="fas fa-money-bill-wave"></i>
                            <h3>PM-KISAN</h3>
                        </div>
                        <div class="yojana-content">
                            <p>Direct income support of ₹6,000 per year to farmer families in three equal installments.</p>
                            <ul>
                                <li>Eligible: All landholding farmer families</li>
                                <li>Amount: ₹6,000 per year</li>
                                <li>Installments: 3 of ₹2,000 each</li>
                            </ul>
                            <div class="yojana-actions">
                                <a href="https://pmkisan.gov.in/" target="_blank" class="btn apply-btn">Apply Now</a>
                                <a href="https://pmkisan.gov.in/BeneficiaryStatus.aspx" target="_blank" class="btn status-btn">Check Status</a>
                            </div>
                        </div>
                    </div>

                    <div class="yojana-card">
                        <div class="yojana-header">
                            <i class="fas fa-shield-alt"></i>
                            <h3>PM Fasal Bima Yojana</h3>
                        </div>
                        <div class="yojana-content">
                            <p>Crop insurance scheme providing coverage and financial support in case of crop failure.</p>
                            <ul>
                                <li>Premium: 1.5% to 5% of sum insured</li>
                                <li>Covers: Natural calamities, pests & diseases</li>
                                <li>Available for all food crops, oilseeds & annual commercial/horticultural crops</li>
                            </ul>
                            <div class="yojana-actions">
                                <a href="https://pmfby.gov.in/" target="_blank" class="btn apply-btn">Apply Now</a>
                                <a href="https://pmfby.gov.in/customerService" target="_blank" class="btn status-btn">Check Status</a>
                            </div>
                        </div>
                    </div>

                    <div class="yojana-card">
                        <div class="yojana-header">
                            <i class="fas fa-credit-card"></i>
                            <h3>Kisan Credit Card</h3>
                        </div>
                        <div class="yojana-content">
                            <p>Easy access to credit for farmers at reduced interest rates.</p>
                            <ul>
                                <li>Interest: 4% per annum</li>
                                <li>Credit limit based on land holding</li>
                                <li>Includes personal accident insurance</li>
                            </ul>
                            <div class="yojana-actions">
                                <a href="https://www.sbi.co.in/web/agri-rural/agriculture-banking/crop-loan/kisan-credit-card" target="_blank" class="btn apply-btn">Apply Now</a>
                            </div>
                        </div>
                    </div>

                    <div class="yojana-card">
                        <div class="yojana-header">
                            <i class="fas fa-flask"></i>
                            <h3>Soil Health Card</h3>
                        </div>
                        <div class="yojana-content">
                            <p>Detailed report of soil fertility status and recommended nutrients for crops.</p>
                            <ul>
                                <li>Free soil testing</li>
                                <li>Crop-wise recommendations</li>
                                <li>Valid for 3 years</li>
                            </ul>
                            <div class="yojana-actions">
                                <a href="https://soilhealth.dac.gov.in/" target="_blank" class="btn apply-btn">Get Card</a>
                                <a href="https://soilhealth.dac.gov.in/health/card" target="_blank" class="btn status-btn">Check Status</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
    .yojana-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 24px;
        padding: 24px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .yojana-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .yojana-header {
        background: #67B46A;
        color: white;
        padding: 20px;
        font-size: 1.25rem;
    }

    .yojana-header i {
        margin-right: 12px;
    }

    .yojana-content {
        padding: 24px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .yojana-content p {
        color: #444;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .yojana-content ul {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
        flex-grow: 1;
    }

    .yojana-content li {
        color: #666;
        margin-bottom: 12px;
        padding-left: 24px;
        position: relative;
        line-height: 1.4;
    }

    .yojana-content li:before {
        content: "•";
        color: #67B46A;
        position: absolute;
        left: 8px;
    }

    .yojana-actions {
        display: flex;
        gap: 12px;
        margin-top: auto;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        text-align: center;
        flex: 1;
        transition: all 0.3s ease;
    }

    .apply-btn {
        background: #67B46A;
        color: white;
    }

    .status-btn {
        background: #F5F5F5;
        color: #444;
        border: 1px solid #E0E0E0;
    }

    .apply-btn:hover {
        background: #5AA15D;
    }

    .status-btn:hover {
        background: #E8E8E8;
    }
    </style>
</body>
</html>