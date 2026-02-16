<?php
$pageTitle = 'Marco Polo Hotel - Luxury Accommodations';
require_once 'config/db.php';

$db = initDatabase();
$roomObj = new Room($db);

$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$roomType = $_GET['room_type'] ?? '';

$filters = ['status' => 'available'];
if ($roomType) {
    $filters['type'] = $roomType;
}

$rooms = $roomObj->getAvailableRooms($checkIn, $checkOut, $roomType);
$roomTypes = $roomObj->getRoomTypes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #e94560;
            --gold: #c9a227;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar {
            background: transparent !important;
            position: absolute;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar.scrolled {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%) !important;
            position: fixed;
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(26, 26, 46, 0.85) 0%, rgba(22, 33, 62, 0.8) 100%),
                        url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920') center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            line-height: 1.1;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            font-weight: 300;
            opacity: 0.9;
        }
        
        .search-box {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 14px 18px;
            border: 2px solid #e9ecef;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
        }
        
        .btn-accent {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-accent:hover {
            background: #d63651;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(233, 69, 96, 0.4);
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        .room-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            background: white;
        }
        
        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .room-card .card-img-top {
            height: 250px;
            object-fit: cover;
            transition: all 0.4s ease;
        }
        
        .room-card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .room-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .price-tag {
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .price-tag span {
            font-size: 0.8rem;
            font-weight: 400;
            opacity: 0.8;
        }
        
        .amenity-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }
        
        .features-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .feature-card {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px 30px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .footer {
            background: var(--primary);
        }
        
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .stars {
            color: var(--gold);
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .search-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" id="navbar">
        <div class="container">
            <a class="navbar-brand text-white" href="index.php">
                <i class="bi bi-building"></i> Marco Polo Hotel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link text-white" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#rooms">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#contact">Contact</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="pages/my-bookings.php">My Bookings</a></li>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="admin/dashboard.php">Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= $_SESSION['name'] ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="pages/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pages/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-accent ms-3" href="pages/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="hero-title text-white mb-4 animate-on-scroll">Experience<br><span style="color: var(--accent);">Luxury</span> Living</h1>
                    <p class="hero-subtitle text-white mb-5 animate-on-scroll">Discover unparalleled comfort and elegance at Marco Polo Hotel. Your perfect getaway starts here.</p>
                    <a href="#rooms" class="btn btn-accent animate-on-scroll">Explore Rooms</a>
                </div>
                <div class="col-lg-5 mt-5 mt-lg-0">
                    <div class="search-box animate-on-scroll" data-aos="fade-up">
                        <h5 class="mb-4 fw-bold">Book Your Stay</h5>
                        <form action="pages/search.php" method="GET">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Check In</label>
                                    <input type="date" name="check_in" class="form-control" value="<?= $checkIn ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check Out</label>
                                    <input type="date" name="check_out" class="form-control" value="<?= $checkOut ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Room Type</label>
                                    <select name="room_type" class="form-select">
                                        <option value="">All Rooms</option>
                                        <?php foreach ($roomTypes as $type): ?>
                                            <option value="<?= $type['room_type'] ?>" <?= $roomType === $type['room_type'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($type['room_type']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-accent w-100">
                                        <i class="bi bi-search me-2"></i>Search Available Rooms
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="rooms">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title">Our <span style="color: var(--accent);">Rooms</span></h2>
                <p class="text-muted">Choose from our selection of luxurious accommodations</p>
            </div>
            
            <div class="row g-4">
                <?php foreach ($rooms as $room): 
                    $amenities = json_decode($room['amenities'] ?? '[]', true);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="room-card card h-100">
                        <div class="position-relative">
                            <img src="<?= htmlspecialchars($room['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($room['room_type']) ?>">
                            <span class="room-type-badge"><?= htmlspecialchars($room['room_type']) ?></span>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="price-tag">$<?= number_format($room['price'], 0) ?> <span>/night</span></span>
                                <span class="badge bg-success">Available</span>
                            </div>
                            <h5 class="card-title mb-2">Room <?= htmlspecialchars($room['room_number']) ?></h5>
                            <p class="text-muted small mb-3"><?= htmlspecialchars(substr($room['description'], 0, 100)) ?>...</p>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php foreach (array_slice($amenities, 0, 4) as $amenity): ?>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($amenity) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people me-1"></i> Max <?= $room['capacity'] ?> Guests</span>
                                <a href="pages/booking.php?room_id=<?= $room['id'] ?>" class="btn btn-accent">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($rooms)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <h4 class="mt-3">No rooms available for selected dates</h4>
                    <p class="text-muted">Please try different dates or room type</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features-section py-5" id="features">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="section-title text-white">Why Choose <span style="color: var(--accent);">Us</span></h2>
                <p class="text-white-50">Experience world-class amenities and service</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center text-white">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-wifi"></i>
                        </div>
                        <h4>Free WiFi</h4>
                        <p class="text-white-50 mb-0">High-speed internet throughout the hotel</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center text-white">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-cup-straw"></i>
                        </div>
                        <h4>Free Breakfast</h4>
                        <p class="text-white-50 mb-0">Start your day with our delicious breakfast</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center text-white">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-parking"></i>
                        </div>
                        <h4>Free Parking</h4>
                        <p class="text-white-50 mb-0">Complimentary parking for all guests</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center text-white">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <h4>Pool & Spa</h4>
                        <p class="text-white-50 mb-0">Relax in our luxury pool and spa facilities</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center text-white">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>24/7 Security</h4>
                        <p class="text-white-50 mb-0">Your safety is our top priority</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center text-white">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4>24/7 Support</h4>
                        <p class="text-white-50 mb-0">Round-the-clock assistance for all needs</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="contact">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-6">
                    <h2 class="section-title">Get in <span style="color: var(--accent);">Touch</span></h2>
                    <p class="text-muted mb-4">Have questions? We'd love to hear from you.</p>
                    <div class="d-flex align-items-center mb-4">
                        <div class="amenity-icon me-3">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Address</h6>
                            <p class="text-muted mb-0">123 Hotel Street, Luxury District, City 12345</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <div class="amenity-icon me-3">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Phone</h6>
                            <p class="text-muted mb-0">+1 234 567 890</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="amenity-icon me-3">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Email</h6>
                            <p class="text-muted mb-0">info@grandhotel.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <form class="card border-0 shadow-lg p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="Subject" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-accent w-100">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Marco Polo Hotel</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2026 Marco Polo Hotel. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
        
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkIn = new Date(this.querySelector('input[name="check_in"]').value);
            const checkOut = new Date(this.querySelector('input[name="check_out"]').value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (checkIn < today) {
                e.preventDefault();
                alert('Check-in date cannot be in the past');
            }
            if (checkOut <= checkIn) {
                e.preventDefault();
                alert('Check-out date must be after check-in date');
            }
        });
    </script>
</body>
</html>
