-- Create database
CREATE DATABASE IF NOT EXISTS ralmitrokij_hotel;
USE ralmitrokij_hotel;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'agency') NOT NULL,
    full_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Travel agencies table
CREATE TABLE IF NOT EXISTS travel_agencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agency_name VARCHAR(255) NOT NULL,
    commission_rate DECIMAL(5, 2) DEFAULT 10.00, -- 10% default
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reservations table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL, -- For agency bookings
    room_id INT NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests_adults INT NOT NULL DEFAULT 1,
    guests_children INT NOT NULL DEFAULT 0,
    special_requests TEXT,
    payment_method VARCHAR(100),
    booking_ref VARCHAR(100) NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Failed login attempts table
CREATE TABLE IF NOT EXISTS failed_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (email, password_hash, role, full_name) 
VALUES ('admin@ralmitrokij.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Admin');

-- Insert sample rooms
INSERT INTO rooms (name, category, price, description)
VALUES 
    ('Standard Room', 'Classic Comfort', 4500.00, 'A comfortable and well-appointed room ideal for both business and leisure travelers.'),
    ('Deluxe Room', 'Premium Accommodation', 8500.00, 'Spacious and elegantly appointed, the Deluxe Room offers sweeping city views with premium furnishings.'),
    ('Grand Suite', 'Signature Suite', 25999.00, 'The Grand Suite features a separate living area, a private terrace, and butler service.');
