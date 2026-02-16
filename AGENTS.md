# Marco Polo Hotel Booking System - Development Notes

## Setup Instructions

### 1. Database Setup
1. Install MySQL and create a database named `hotel_booking`
2. Import the SQL schema:
   ```bash
   mysql -u root -p hotel_booking < hotel_booking.sql
   ```

### 2. Configuration
Edit `config/db.php` to update database credentials:
```php
private $username = 'root';
private $password = ''; // Your MySQL password
```

### 3. Run the Application
- Start a local PHP server:
  ```bash
  php -S localhost:8000
  ```
- Open browser at `http://localhost:8000`

### 4. Default Login Credentials
- **Admin**: admin@hotel.com / admin123
- **Customer**: Register a new account

## File Structure
```
hotel_booking/
├── config/
│   └── db.php              # Database connection and models
├── includes/
│   ├── header.php          # Shared header
│   └── footer.php          # Shared footer
├── pages/
│   ├── login.php           # User login
│   ├── register.php        # User registration
│   ├── logout.php          # Logout handler
│   ├── booking.php         # Room booking
│   ├── my-bookings.php     # User bookings dashboard
│   ├── search.php          # Room search
│   └── profile.php         # User profile
├── admin/
│   ├── dashboard.php       # Admin dashboard
│   ├── rooms.php           # Room management (CRUD)
│   ├── bookings.php        # Booking management
│   └── users.php           # User management
├── index.php               # Landing page
└── hotel_booking.sql       # Database schema
```

## Security Features
- PDO prepared statements prevent SQL injection
- Password hashing with password_hash()
- Session-based authentication
- Input sanitization with sanitize() function

## Features
- Responsive Bootstrap 5 design
- Date-range room search with availability check
- Real-time booking management
- Admin dashboard with analytics
- Room CRUD operations
- Booking status management (pending/confirmed/checked_in/checked_out/cancelled)
