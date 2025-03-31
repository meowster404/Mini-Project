<?php
require_once 'db.php';

// Get JSON data
$jsonFile = file_get_contents('../../Frontend/js/product-data.js');
$pattern = '/const productData = (.+?);/s';
preg_match($pattern, $jsonFile, $matches);
$productData = json_decode($matches[1], true);

// Clear existing products
mysqli_query($conn, "TRUNCATE TABLE products");

// Insert products
foreach ($productData['products'] as $product) {
    $stmt = $conn->prepare("INSERT INTO products (id, name, description, price, image, category, farm_name, is_organic, discount, farming_method, stock, popularity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issdsssiisii", 
        $product['id'],
        $product['name'],
        $product['description'],
        $product['price'],
        $product['image'],
        $product['category'],
        $product['farm_name'],
        $product['is_organic'],
        $product['discount'],
        $product['farming_method'],
        $product['stock'],
        $product['popularity']
    );
    
    $stmt->execute();
}

echo "Products imported successfully!";
?>