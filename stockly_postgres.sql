-- Stockly Database for FIGEC Microfinance (PostgreSQL Version)

-- Create Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) CHECK (role IN ('guichet', 'stock', 'admin')) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Suppliers Table
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    company_num VARCHAR(50),
    contact VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Products Table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    supplier_id INT,
    threshold INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Create Stock Table
CREATE TABLE stock (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL UNIQUE,
    current_stock INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create Orders Table
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    guichet_user_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'delivered')),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    CONSTRAINT fk_user FOREIGN KEY (guichet_user_id) REFERENCES users(id)
);

-- Create Order Items Table
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2),
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_product_item FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Trigger function to update timestamps
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.date_updated = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply trigger to orders
CREATE TRIGGER trg_update_orders_timestamp
BEFORE UPDATE ON orders
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

-- Sample Data
INSERT INTO users (username, password, role, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@figec.com'),
('stock_mgr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'stock', 'stock@figec.com'),
('guichet', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guichet', 'guichet@figec.com');

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
(3, 2);

-- Indexes
CREATE INDEX idx_orders_status_date ON orders(status, date_created);
CREATE INDEX idx_stock_current ON stock(current_stock);
