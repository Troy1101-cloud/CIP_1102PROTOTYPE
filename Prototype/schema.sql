-- Drop tables if they exist to prevent schema mismatch
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS failed_logins;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS travel_agencies;
DROP TABLE IF EXISTS addons;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
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
    amenities TEXT, -- Comma-separated list of room features
    image VARCHAR(255),
    status ENUM('Available', 'Maintenance') DEFAULT 'Available',
    quantity INT DEFAULT 1,
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

-- Global Add-ons table (Amenities and Dining)
CREATE TABLE IF NOT EXISTS addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    category VARCHAR(50) NOT NULL, -- e.g., 'amenity', 'dining'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
    amenities TEXT, -- JSON array of selected addon IDs
    dining TEXT, -- JSON array of selected dining addon IDs
    payment_method VARCHAR(100),
    booking_ref VARCHAR(100) NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    commission_earned DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    source ENUM('guest', 'staff', 'agency') DEFAULT 'guest',
    checked_in_at TIMESTAMP NULL DEFAULT NULL,
    checked_out_at TIMESTAMP NULL DEFAULT NULL,
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
INSERT INTO users (email, password, role, full_name) 
VALUES ('admin@ralmitrokij.com', 'admin123', 'admin', 'System Admin');

-- Insert default travel agency user (password: agency123)
INSERT INTO users (id, email, password, role, full_name) 
VALUES (2, 'agency@ralmitrokij.com', 'agency123', 'agency', 'Global Travel Partners');

INSERT INTO travel_agencies (user_id, agency_name, commission_rate)
VALUES (2, 'Global Travel Partners', 15.00);

-- Insert sample rooms
INSERT INTO rooms (name, category, price, description, amenities)
VALUES 
('Standard Room', 'Classic Comfort', 4500.00, 'A comfortable and well-appointed room ideal for both business travelers and perfectly accommodating for families with children.', 'Queen Bed, Work Desk, Wi-Fi'),
('Deluxe Room', 'Premium Accommodation', 8500.00, 'Spacious and elegantly appointed, the Deluxe Room offers sweeping city views with premium furnishings, comfortably accommodating families with children.', 'King Bed, City View, Mini Bar, Wi-Fi'),
('Grand Suite', 'Signature Suite', 25999.00, 'The Grand Suite features a separate living area, a private terrace, and butler service, making it the perfect luxurious getaway that is accommodating for families with children.', 'King Bed, Private Terrace, Butler Service, Living Room');

-- Insert default Add-ons
INSERT INTO addons (id, name, price, category)
VALUES 
(1, 'Infinity Pool', 0.00, 'amenity'),
(2, 'Fitness Center', 0.00, 'amenity'),
(3, 'Serenity Spa', 2500.00, 'amenity'),
(4, 'The Sky Lounge', 1500.00, 'dining'),
(5, 'Artisan Café', 800.00, 'dining'),
(6, 'The Copper Bar', 1000.00, 'dining'),
(7, 'Queen Bed', 0.00, 'amenity'),
(8, 'King Bed', 0.00, 'amenity'),
(9, 'Wi-Fi', 0.00, 'amenity'),
(10, 'Work Desk', 0.00, 'amenity'),
(11, 'City View', 0.00, 'amenity'),
(12, 'Mini Bar', 0.00, 'amenity'),
(13, 'Private Terrace', 0.00, 'amenity'),
(14, 'Butler Service', 0.00, 'amenity'),
(15, 'Living Room', 0.00, 'amenity');

-- Contact Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
