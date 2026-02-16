<?php
$pageTitle = 'My Bookings - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$bookingObj = new Booking($db);

requireLogin();

$bookings = $bookingObj->getByUser($_SESSION['user_id']);

$statusActions = [
    'pending' => ['confirm' => 'confirmed', 'cancel' => 'cancelled'],
    'confirmed' => ['checkin' => 'checked_in', 'cancel' => 'cancelled'],
    'checked_in' => ['checkout' => 'checked_out']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bookingId = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    
    $allowedActions = ['confirm', 'cancel', 'checkin', 'checkout'];
    $statusMap = [
        'confirm' => 'confirmed',
        'cancel' => 'cancelled',
        'checkin' => 'checked_in',
        'checkout' => 'checked_out'
    ];
    
    if (in_array($action, $allowedActions)) {
        $bookingObj->updateStatus($bookingId, $statusMap[$action]);
        $_SESSION['success'] = 'Booking status updated successfully';
        redirect('my-bookings.php');
    }
}
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
        
        .booking-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .room-img {
            height: 180px;
            object-fit: cover;
        }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .booking-status-pending { background: #fff3cd; color: #856404; }
        .booking-status-confirmed { background: #d4edda; color: #155724; }
        .booking-status-checked_in { background: #cce5ff; color: #004085; }
        .booking-status-checked_out { background: #e2e3e5; color: #383d41; }
        .booking-status-cancelled { background: #f8d7da; color: #721c24; }
        
        .btn-accent {
            background: var(--accent);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-accent:hover {
            background: #d63651;
            color: white;
        }
        
        .btn-outline {
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background: var(--primary);
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
            <div class="d-flex align-items-center">
                <a href="../index.php" class="text-white me-3 text-decoration-none">Home</a>
                <span class="text-white me-3">Welcome, <?= $_SESSION['name'] ?? 'Guest' ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Bookings</h2>
            <a href="../index.php" class="btn btn-accent">
                <i class="bi bi-plus-circle me-2"></i>Book New Room
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <h4 class="mt-3">No Bookings Yet</h4>
                <p class="text-muted">Start exploring our rooms and make your first booking!</p>
                <a href="../index.php" class="btn btn-accent">Browse Rooms</a>
            </div>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($bookings as $booking): 
                $nights = calculateNights($booking['check_in'], $booking['check_out']);
            ?>
            <div class="col-lg-6">
                <div class="card booking-card h-100">
                    <div class="row g-0">
                        <div class="col-md-5">
                            <img src="<?= htmlspecialchars($booking['image']) ?>" class="room-img w-100 h-100" alt="Room">
                        </div>
                        <div class="col-md-7">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($booking['room_type']) ?></h5>
                                    <span class="status-badge booking-status-<?= $booking['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">Room <?= htmlspecialchars($booking['room_number']) ?></p>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?= date('M d, Y', strtotime($booking['check_in'])) ?> - 
                                        <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                                    </small>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="bi bi-moon me-1"></i><?= $nights ?> Night<?= $nights > 1 ? 's' : '' ?>
                                        <i class="bi bi-people ms-2 me-1"></i><?= $booking['guests'] ?> Guest<?= $booking['guests'] > 1 ? 's' : '' ?>
                                    </small>
                                </div>
                                
                                <h5 class="text-primary mt-3">$<?= number_format($booking['total_price'], 2) ?></h5>
                                
                                <?php if (isset($statusActions[$booking['status']])): ?>
                                <div class="d-flex gap-2 mt-3">
                                    <?php if (isset($statusActions[$booking['status']]['confirm'])): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn btn-accent btn-sm">Confirm</button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($statusActions[$booking['status']]['checkin'])): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="checkin">
                                        <button type="submit" class="btn btn-accent btn-sm">Check In</button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($statusActions[$booking['status']]['checkout'])): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="checkout">
                                        <button type="submit" class="btn btn-outline btn-sm">Check Out</button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($statusActions[$booking['status']]['cancel']) && $booking['status'] !== 'cancelled'): ?>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
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
