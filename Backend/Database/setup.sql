-- Create database
CREATE DATABASE IF NOT EXISTS farm_fresh_market;
USE farm_fresh_market;

-- Create consumers table
CREATE TABLE IF NOT EXISTS consumers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create farmers table
CREATE TABLE IF NOT EXISTS farmers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    farm_name VARCHAR(100) NOT NULL,
    farm_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    category VARCHAR(50),
    is_organic BOOLEAN DEFAULT FALSE,
    discount INT DEFAULT 0,
    farming_method VARCHAR(50),
    farmer_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmers(id)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    consumer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consumer_id) REFERENCES consumers(id)
);

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    consumer_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (consumer_id) REFERENCES consumers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Create promo_codes table
CREATE TABLE IF NOT EXISTS promo_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uses_left INT NOT NULL DEFAULT -1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO consumers (full_name, email, phone, password) 
VALUES ('Test Consumer', 'consumer@test.com', '+91 1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO farmers (name, email, phone, password, farm_name, farm_address) 
VALUES 
('John Doe', 'john@greenvalley.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Green Valley Farm', 'Green Valley Road'),
('Jane Smith', 'jane@dairydreams.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dairy Dreams', 'Dairy Lane'),
('Mike Johnson', 'mike@poultryparadise.com', '5555555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Poultry Paradise', 'Paradise Street');

-- Insert sample promo codes
INSERT INTO promo_codes (code, discount_type, discount_value, min_order_value, start_date, end_date, uses_left) 
VALUES
('WELCOME10', 'percentage', 10, 500, '2024-01-01', '2024-12-31', 100),
('FRESH50', 'fixed', 50, 1000, '2024-01-01', '2024-12-31', 50);

-- Add cart index
CREATE INDEX idx_cart_consumer ON cart(consumer_id);

-- Add stock check trigger
DELIMITER //
CREATE TRIGGER check_stock_before_cart
BEFORE INSERT ON cart
FOR EACH ROW
BEGIN
    DECLARE available_stock INT;
    SELECT stock_quantity INTO available_stock
    FROM products
    WHERE id = NEW.product_id;
    
    IF available_stock < NEW.quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Insufficient stock available';
    END IF;
END;//
DELIMITER ;