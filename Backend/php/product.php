<?php
// Database connection
function connectDB() {
    // Replace with your actual database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ecokart";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Function to get all products
function getAllProducts() {
    $conn = connectDB();
    $products = [];
    
    $sql = "SELECT * FROM products ORDER BY category, name";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $conn->close();
    return $products;
}

// Function to get products by category
function getProductsByCategory($category) {
    $conn = connectDB();
    $products = [];
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? ORDER BY name");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    return $products;
}

// Function to get products with filters
function getFilteredProducts($category = null, $farming_method = null) {
    $conn = connectDB();
    $products = [];
    
    // Build the query based on filters
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    if ($farming_method) {
        $sql .= " AND farming_method = ?";
        $params[] = $farming_method;
        $types .= "s";
    }
    
    $sql .= " ORDER BY category, name";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    return $products;
}

// AJAX handler
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_all_products':
            echo json_encode(getAllProducts());
            break;
            
        case 'get_products_by_category':
            if (isset($_GET['category'])) {
                echo json_encode(getProductsByCategory($_GET['category']));
            } else {
                echo json_encode(['error' => 'Category parameter is required']);
            }
            break;
            
        case 'get_filtered_products':
            $category = isset($_GET['category']) ? $_GET['category'] : null;
            $farming_method = isset($_GET['farming_method']) ? $_GET['farming_method'] : null;
            
            echo json_encode(getFilteredProducts($category, $farming_method));
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    
    exit;
}

// Include the HTML template if accessed directly
include_once('../../Frontend/Html/products.html');
?>