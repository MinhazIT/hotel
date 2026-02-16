<?php
$pageTitle = 'Admin Dashboard - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$roomObj = new Room($db);
$bookingObj = new Booking($db);
$userObj = new User($db);

requireAdmin();

$roomStats = $roomObj->getStats();
$bookingStats = $bookingObj->getStats();
$recentBookings = $bookingObj->getAll();
$allUsers = $userObj->getAll();

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
        redirect('dashboard.php');
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
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .stat-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
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
        }
        
        .btn-accent:hover {
            background: #d63651;
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <h4 class="text-white mb-4">
                <i class="bi bi-building"></i> Marco Polo Hotel
            </h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a class="nav-link text-white active" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="rooms.php">
                        <i class="bi bi-door-open me-2"></i>Room Management
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="bookings.php">
                        <i class="bi bi-calendar-check me-2"></i>All Bookings
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="users.php">
                        <i class="bi bi-people me-2"></i>Users
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-white" href="settings.php">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a class="nav-link text-white" href="../index.php">
                        <i class="bi bi-house me-2"></i>View Website
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="../pages/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="main-content flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary rounded mb-4 d-md-none">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Marco Polo Hotel Admin</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Dashboard</h3>
                <span class="text-muted">Welcome, <?= $_SESSION['name'] ?></span>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Rooms</p>
                                    <h3 class="mb-0"><?= $roomStats['total'] ?></h3>
                                </div>
                                <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                                    <i class="bi bi-door-open"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Available</p>
                                    <h3 class="mb-0 text-success"><?= $roomStats['available'] ?></h3>
                                </div>
                                <div class="stat-icon" style="background: #d4edda; color: #28a745;">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Bookings</p>
                                    <h3 class="mb-0"><?= $bookingStats['total_bookings'] ?? 0 ?></h3>
                                </div>
                                <div class="stat-icon" style="background: #fff3cd; color: #ffc107;">
                                    <i class="bi bi-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Total Revenue</p>
                                    <h3 class="mb-0 text-primary">$<?= number_format($bookingStats['total_revenue'] ?? 0, 0) ?></h3>
                                </div>
                                <div class="stat-icon" style="background: #fce4ec; color: #e94560;">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Booking Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Confirmed</span>
                                <span class="badge booking-status-confirmed"><?= $bookingStats['confirmed'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Pending</span>
                                <span class="badge booking-status-pending"><?= $bookingStats['pending'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Checked In</span>
                                <span class="badge booking-status-checked_in"><?= $bookingStats['checked_in'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Cancelled</span>
                                <span class="badge booking-status-cancelled"><?= $bookingStats['cancelled'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Room Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Available</span>
                                <span class="badge bg-success"><?= $roomStats['available'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Occupied</span>
                                <span class="badge bg-warning"><?= $roomStats['occupied'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Maintenance</span>
                                <span class="badge bg-secondary"><?= $roomStats['maintenance'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Bookings</h5>
                    <a href="bookings.php" class="btn btn-sm btn-accent">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentBookings, 0, 5) as $booking): ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                    <td><?= htmlspecialchars($booking['room_type']) ?> (<?= htmlspecialchars($booking['room_number']) ?>)</td>
                                    <td><?= date('M d, Y', strtotime($booking['check_in'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['check_out'])) ?></td>
                                    <td>$<?= number_format($booking['total_price'], 2) ?></td>
                                    <td>
                                        <span class="status-badge booking-status-<?= $booking['status'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $booking['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="confirm">
                                            <button type="submit" class="btn btn-sm btn-success">Confirm</button>
                                        </form>
                                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="checkin">
                                            <button type="submit" class="btn btn-sm btn-primary">Check In</button>
                                        </form>
                                        <?php elseif ($booking['status'] === 'checked_in'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="checkout">
                                            <button type="submit" class="btn btn-sm btn-info">Check Out</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($recentBookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No bookings yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
