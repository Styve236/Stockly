-- Stockly Database for FIGEC Microfinance
-- Import into phpMyAdmin: Create DB 'stockly' (utf8mb4), then import this SQL

CREATE DATABASE IF NOT EXISTS stockly CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stockly;

-- Users table (roles: 'guichet', 'stock', 'admin')
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Hashed
    role ENUM('guichet', 'stock', 'admin') NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    company_num VARCHAR(50),
    contact VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    supplier_id INT,
    threshold INT DEFAULT 10,  -- Alert if stock <= threshold
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Stock table
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    current_stock INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product (product_id)
);

-- Orders table (bons de commande)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guichet_user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'delivered') DEFAULT 'pending',
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (guichet_user_id) REFERENCES users(id)
);

-- Order Items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Add prix_unitaire to suppliers (run ALTER if new)
-- ALTER TABLE suppliers ADD COLUMN prix_unitaire DECIMAL(10,2) DEFAULT NULL;

-- Sample Data
INSERT INTO users (username, password, role, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@figec.com'),  -- password: password
('stock_mgr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stock', 'stock@figec.com'),  -- password: password
('guichet', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guichet', 'guichet@figec.com');  -- password: password

INSERT INTO suppliers (name, company_num, contact, phone) VALUES
('Fournisseur A', 'SOC001', 'Jean Dupont', '0123456789'),
('Fournisseur B', 'SOC002', 'Marie Curie', '0987654321');

INSERT INTO products (name, description, price, supplier_id, threshold) VALUES
('Cahier', 'Cahier 100 pages', 2.50, 1, 5),
('Stylo', 'Stylo bleu', 0.50, 1, 20),
('Encre', 'Cartouche encre', 15.00, 2, 3);

INSERT INTO stock (product_id, current_stock) VALUES
(1, 50),
(2, 100),
(3, 2);  -- Low stock alert

-- Indexes for performance
CREATE INDEX idx_orders_status_date ON orders(status, date_created);
CREATE INDEX idx_stock_current ON stock(current_stock);

