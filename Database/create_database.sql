CREATE DATABASE IF NOT EXISTS farm_fresh_market;
USE farm_fresh_market;

CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(50),
    farm_name VARCHAR(100),
    is_organic BOOLEAN,
    discount INT,
    farming_method VARCHAR(50),
    stock INT,
    popularity INT
);