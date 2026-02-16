<?php
$pageTitle = 'Search Results - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$roomObj = new Room($db);

$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$roomType = $_GET['room_type'] ?? '';

if ($checkIn >= $checkOut) {
    $_SESSION['error'] = 'Check-out date must be after check-in date';
    redirect('../index.php');
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1a1a2e;
            --secondary: #16213e;
            --accent: #e94560;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .search-bar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .room-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .room-img {
            height: 220px;
            object-fit: cover;
        }
        
        .btn-accent {
            background: var(--accent);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-accent:hover {
            background: #d63651;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-building"></i> Marco Polo Hotel
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="d-flex align-items-center">
                <a href="my-bookings.php" class="text-white me-3 text-decoration-none">My Bookings</a>
                <span class="text-white me-3"><?= $_SESSION['name'] ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
            <?php else: ?>
            <div>
                <a href="login.php" class="btn btn-accent me-2">Login</a>
                <a href="register.php" class="btn btn-outline-light">Register</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container py-5">
        <div class="search-bar p-4 mb-4">
            <form action="search.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Check In</label>
                    <input type="date" name="check_in" class="form-control" value="<?= $checkIn ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Check Out</label>
                    <input type="date" name="check_out" class="form-control" value="<?= $checkOut ?>" required>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <button type="submit" class="btn btn-accent w-100">Search</button>
                </div>
            </form>
        </div>
        
        <div class="mb-4">
            <h4>
                Available Rooms 
                <small class="text-muted">(<?= count($rooms) ?> found)</small>
            </h4>
            <p class="text-muted">
                <?= date('M d, Y', strtotime($checkIn)) ?> - <?= date('M d, Y', strtotime($checkOut)) ?>
                (<?= calculateNights($checkIn, $checkOut) ?> night<?= calculateNights($checkIn, $checkOut) > 1 ? 's' : '' ?>)
            </p>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (empty($rooms)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <h4 class="mt-3">No Rooms Available</h4>
                <p class="text-muted">No rooms match your criteria for the selected dates.</p>
                <a href="../index.php" class="btn btn-accent">Try Different Dates</a>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($rooms as $room): 
                $amenities = json_decode($room['amenities'] ?? '[]', true);
                $nights = calculateNights($checkIn, $checkOut);
                $totalPrice = $room['price'] * $nights;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card room-card h-100">
                    <img src="<?= htmlspecialchars($room['image']) ?>" class="room-img" alt="<?= htmlspecialchars($room['room_type']) ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary"><?= htmlspecialchars($room['room_type']) ?></span>
                            <span class="text-muted">Room <?= htmlspecialchars($room['room_number']) ?></span>
                        </div>
                        <p class="text-muted small mb-3"><?= htmlspecialchars(substr($room['description'], 0, 80)) ?>...</p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($amenity) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="h5 text-primary">$<?= number_format($room['price'], 0) ?></span>
                                <span class="text-muted">/night</span>
                            </div>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="booking.php?room_id=<?= $room['id'] ?>&check_in=<?= $checkIn ?>&check_out=<?= $checkOut ?>" class="btn btn-accent">Book Now</a>
                            <?php else: ?>
                            <a href="login.php" class="btn btn-accent">Login to Book</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
