function getProductCategories() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id, name FROM product_categories ORDER BY name ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $categories;
    } catch (Exception $e) {
        error_log("Error getting product categories: " . $e->getMessage());
        return [];
    }
}