-- Hotel Booking and Management System - SQL Schema
-- Database: hotel_booking

CREATE DATABASE IF NOT EXISTS hotel_booking;
USE hotel_booking;

-- Users Table (Admin and Customers)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) NOT NULL UNIQUE,
    room_type VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    amenities JSON,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_room_type (room_type),
    INDEX idx_status (status),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_room_id (room_id),
    INDEX idx_check_in (check_in),
    INDEX idx_check_out (check_out),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample rooms
INSERT INTO rooms (room_number, room_type, description, price, capacity, status, amenities, image) VALUES
('101', 'Standard Room', 'Comfortable standard room with all basic amenities. Perfect for solo travelers or couples.', 99.00, 2, 'available', '["WiFi", "TV", "Air Conditioning", "Private Bathroom"]', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400'),
('102', 'Standard Room', 'Comfortable standard room with all basic amenities. Perfect for solo travelers or couples.', 99.00, 2, 'available', '["WiFi", "TV", "Air Conditioning", "Private Bathroom"]', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400'),
('201', 'Deluxe Room', 'Spacious deluxe room with premium amenities and city view.', 149.00, 2, 'available', '["WiFi", "TV", "Air Conditioning", "Mini Bar", "Safe", "City View"]', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400'),
('202', 'Deluxe Room', 'Spacious deluxe room with premium amenities and city view.', 149.00, 2, 'available', '["WiFi", "TV", "Air Conditioning", "Mini Bar", "Safe", "City View"]', 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=400'),
('301', 'Suite', 'Luxurious suite with separate living area, premium amenities, and panoramic view.', 249.00, 4, 'available', '["WiFi", "TV", "Air Conditioning", "Mini Bar", "Safe", "Ocean View", "Balcony", "Living Room", "Jacuzzi"]', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=400'),
('302', 'Suite', 'Luxurious suite with separate living area, premium amenities, and panoramic view.', 249.00, 4, 'available', '["WiFi", "TV", "Air Conditioning", "Mini Bar", "Safe", "Ocean View", "Balcony", "Living Room", "Jacuzzi"]', 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=400'),
('401', 'Presidential Suite', 'The ultimate luxury experience with private terrace, butler service, and exclusive amenities.', 499.00, 6, 'available', '["WiFi", "TV", "Air Conditioning", "Mini Bar", "Safe", "Ocean View", "Private Terrace", "Living Room", "Jacuzzi", "Butler Service", "Private Pool"]', 'https://images.unsplash.com/photo-1591088398332-8a7791972843?w=400');
