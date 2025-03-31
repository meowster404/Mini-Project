<?php
require_once 'db.php';

// Function to get product by ID
function getProductById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    return $product;
}

// Function to get filtered products
function getFilteredProducts($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($filters['categories'])) {
        $sql .= " AND category IN (" . str_repeat("?,", count($filters['categories']) - 1) . "?)";
        $params = array_merge($params, $filters['categories']);
        $types .= str_repeat("s", count($filters['categories']));
    }
    
    if (!empty($filters['farming_methods'])) {
        $sql .= " AND farming_method IN (" . str_repeat("?,", count($filters['farming_methods']) - 1) . "?)";
        $params = array_merge($params, $filters['farming_methods']);
        $types .= str_repeat("s", count($filters['farming_methods']));
    }
    
    if (!empty($filters['price_min'])) {
        $sql .= " AND price >= ?";
        $params[] = $filters['price_min'];
        $types .= "d";
    }
    
    if (!empty($filters['price_max'])) {
        $sql .= " AND price <= ?";
        $params[] = $filters['price_max'];
        $types .= "d";
    }
    
    $sql .= " ORDER BY name ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $products;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'get_product':
                $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
                if (!$id) {
                    throw new Exception('Invalid product ID');
                }
                $product = getProductById($id);
                echo json_encode(['success' => true, 'data' => $product]);
                break;
                
            case 'get_filtered':
                $filters = [
                    'categories' => $_GET['categories'] ?? [],
                    'farming_methods' => $_GET['farming_methods'] ?? [],
                    'price_min' => $_GET['price_min'] ?? null,
                    'price_max' => $_GET['price_max'] ?? null
                ];
                $products = getFilteredProducts($filters);
                echo json_encode(['success' => true, 'data' => $products]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>