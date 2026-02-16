<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $host = 'localhost';
    private $dbname = 'hotel_booking';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}

class User {
    private $db;
    private $table = 'users';

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($name, $email, $password, $phone = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO {$this->table} (name, email, password, phone) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$name, $email, $hashedPassword, $phone]);
        return $this->db->lastInsertId();
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $user = $this->db->fetchOne($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function emailExists($email) {
        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]) !== false;
    }
}

class Room {
    private $db;
    private $table = 'rooms';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND room_type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND price <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        } else {
            $sql .= " AND status = 'available'";
        }

        $sql .= " ORDER BY price ASC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (room_number, room_type, description, price, capacity, status, amenities, image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['room_number'],
            $data['room_type'],
            $data['description'],
            $data['price'],
            $data['capacity'],
            $data['status'],
            $data['amenities'],
            $data['image']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET room_number = ?, room_type = ?, description = ?, 
                price = ?, capacity = ?, status = ?, amenities = ?, image = ? WHERE id = ?";
        $this->db->query($sql, [
            $data['room_number'],
            $data['room_type'],
            $data['description'],
            $data['price'],
            $data['capacity'],
            $data['status'],
            $data['amenities'],
            $data['image'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
    }

    public function isAvailable($roomId, $checkIn, $checkOut, $excludeBookingId = null) {
        $sql = "SELECT * FROM bookings WHERE room_id = ? AND status NOT IN ('cancelled') 
                AND ((check_in <= ? AND check_out >= ?) OR (check_in <= ? AND check_out >= ?) 
                OR (check_in >= ? AND check_out <= ?))";
        
        $params = [$roomId, $checkOut, $checkIn, $checkIn, $checkOut, $checkIn, $checkOut];
        
        if ($excludeBookingId) {
            $sql .= " AND id != ?";
            $params[] = $excludeBookingId;
        }
        
        return $this->db->fetchOne($sql, $params) === false;
    }

    public function getAvailableRooms($checkIn, $checkOut, $roomType = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'available'";
        $params = [];

        if ($roomType) {
            $sql .= " AND room_type = ?";
            $params[] = $roomType;
        }

        $sql .= " AND id NOT IN (
            SELECT room_id FROM bookings WHERE status NOT IN ('cancelled') 
            AND ((check_in <= ? AND check_out >= ?) 
            OR (check_in <= ? AND check_out >= ?) 
            OR (check_in >= ? AND check_out <= ?))
        ) ORDER BY price ASC";

        $params = array_merge($params, [$checkOut, $checkIn, $checkIn, $checkOut, $checkIn, $checkOut]);
        return $this->db->fetchAll($sql, $params);
    }

    public function getRoomTypes() {
        $sql = "SELECT DISTINCT room_type FROM {$this->table} ORDER BY room_type";
        return $this->db->fetchAll($sql);
    }

    public function getStats() {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance
                FROM {$this->table}";
        return $this->db->fetchOne($sql);
    }
}

class Booking {
    private $db;
    private $table = 'bookings';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, room_id, check_in, check_out, guests, total_price, status, special_requests) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['user_id'],
            $data['room_id'],
            $data['check_in'],
            $data['check_out'],
            $data['guests'],
            $data['total_price'],
            $data['status'] ?? 'pending',
            $data['special_requests'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getById($id) {
        $sql = "SELECT b.*, r.room_number, r.room_type, r.image, u.name as user_name, u.email as user_email 
                FROM {$this->table} b 
                JOIN rooms r ON b.room_id = r.id 
                JOIN users u ON b.user_id = u.id 
                WHERE b.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function getByUser($userId) {
        $sql = "SELECT b.*, r.room_number, r.room_type, r.image 
                FROM {$this->table} b 
                JOIN rooms r ON b.room_id = r.id 
                WHERE b.user_id = ? 
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function getAll($filters = []) {
        $sql = "SELECT b.*, r.room_number, r.room_type, u.name as user_name, u.email as user_email 
                FROM {$this->table} b 
                JOIN rooms r ON b.room_id = r.id 
                JOIN users u ON b.user_id = u.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND b.check_in >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND b.check_out <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);

        if ($status === 'checked_in') {
            $room = new Room($this->db);
            $booking = $this->getById($id);
            $this->db->query("UPDATE rooms SET status = 'occupied' WHERE id = ?", [$booking['room_id']]);
        } elseif ($status === 'checked_out' || $status === 'cancelled') {
            $booking = $this->getById($id);
            $this->db->query("UPDATE rooms SET status = 'available' WHERE id = ?", [$booking['room_id']]);
        }
    }

    public function getStats($filters = []) {
        $sql = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(total_price) as total_revenue
                FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND check_in >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND check_out <= ?";
            $params[] = $filters['date_to'];
        }

        return $this->db->fetchOne($sql, $params);
    }
}

class Payment {
    private $db;
    private $table = 'payments';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (booking_id, user_id, amount, payment_method, payment_status, transaction_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['booking_id'],
            $data['user_id'],
            $data['amount'],
            $data['payment_method'],
            $data['payment_status'],
            $data['transaction_id'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function getByBooking($bookingId) {
        $sql = "SELECT * FROM {$this->table} WHERE booking_id = ?";
        return $this->db->fetchOne($sql, [$bookingId]);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET payment_status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
    }
}

function initDatabase() {
    return new Database();
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('pages/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('index.php');
    }
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function calculateNights($checkIn, $checkOut) {
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    return $checkOutDate->diff($checkInDate)->days;
}

function calculateTotal($price, $checkIn, $checkOut) {
    $nights = calculateNights($checkIn, $checkOut);
    return $price * $nights;
}

class Settings {
    private $db;
    private $table = 'settings';

    public function __construct($db) {
        $this->db = $db;
    }

    public function get($key) {
        $sql = "SELECT setting_value FROM {$this->table} WHERE setting_key = ?";
        $result = $this->db->fetchOne($sql, [$key]);
        return $result ? $result['setting_value'] : null;
    }

    public function set($key, $value) {
        $existing = $this->get($key);
        if ($existing !== null) {
            $sql = "UPDATE {$this->table} SET setting_value = ? WHERE setting_key = ?";
            $this->db->query($sql, [$value, $key]);
        } else {
            $sql = "INSERT INTO {$this->table} (setting_key, setting_value) VALUES (?, ?)";
            $this->db->query($sql, [$key, $value]);
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table}";
        $rows = $this->db->fetchAll($sql);
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function saveAll($data) {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
}

class PaymentMethod {
    private $db;
    private $table = 'payment_methods';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY display_order";
        return $this->db->fetchAll($sql);
    }

    public function getAllAdmin() {
        $sql = "SELECT * FROM {$this->table} ORDER BY display_order";
        return $this->db->fetchAll($sql);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, type, details, is_active, display_order) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['type'],
            $data['details'] ?? '',
            $data['is_active'] ?? 1,
            $data['display_order'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET name = ?, type = ?, details = ?, is_active = ?, display_order = ? WHERE id = ?";
        $this->db->query($sql, [
            $data['name'],
            $data['type'],
            $data['details'] ?? '',
            $data['is_active'] ?? 1,
            $data['display_order'] ?? 0,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
    }
}

class Service {
    private $db;
    private $table = 'services';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($type = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($type) {
            $sql .= " WHERE type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY display_order";
        return $this->db->fetchAll($sql, $params);
    }

    public function getActive($type = null) {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY display_order";
        return $this->db->fetchAll($sql, $params);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, type, description, price, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['type'],
            $data['description'] ?? '',
            $data['price'],
            $data['is_active'] ?? 1,
            $data['display_order'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET name = ?, type = ?, description = ?, price = ?, is_active = ?, display_order = ? WHERE id = ?";
        $this->db->query($sql, [
            $data['name'],
            $data['type'],
            $data['description'] ?? '',
            $data['price'],
            $data['is_active'] ?? 1,
            $data['display_order'] ?? 0,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
    }
}

class RoomMedia {
    private $db;
    private $table = 'room_media';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByRoom($roomId) {
        $sql = "SELECT * FROM {$this->table} WHERE room_id = ? ORDER BY display_order";
        return $this->db->fetchAll($sql, [$roomId]);
    }

    public function create($data) {
        $sql = "INSERT INTO {$this->table} (room_id, media_url, media_type, display_order) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['room_id'],
            $data['media_url'],
            $data['media_type'] ?? 'image',
            $data['display_order'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $this->db->query($sql, [$id]);
    }
}
