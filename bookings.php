<?php
$pageTitle = 'Bookings Management - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$bookingObj = new Booking($db);

requireAdmin();

$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}

$bookings = $bookingObj->getAll($filters);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bookingId = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    
    $statusMap = [
        'confirm' => 'confirmed',
        'cancel' => 'cancelled',
        'checkin' => 'checked_in',
        'checkout' => 'checked_out'
    ];
    
    if (isset($statusMap[$action])) {
        $bookingObj->updateStatus($bookingId, $statusMap[$action]);
        $_SESSION['success'] = 'Booking status updated successfully';
        redirect('bookings.php');
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
        
        body { font-family: 'Poppins', sans-serif; background-color: #f5f5f5; }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        .main-content { margin-left: 250px; padding: 20px; }
        
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .booking-status-pending { background: #fff3cd; color: #856404; }
        .booking-status-confirmed { background: #d4edda; color: #155724; }
        .booking-status-checked_in { background: #cce5ff; color: #004085; }
        .booking-status-checked_out { background: #e2e3e5; color: #383d41; }
        .booking-status-cancelled { background: #f8d7da; color: #721c24; }
        
        .btn-accent { background: var(--accent); color: white; border: none; }
        .btn-accent:hover { background: #d63651; color: white; }
        
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <h4 class="text-white mb-4"><i class="bi bi-building"></i> Marco Polo Hotel</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a class="nav-link text-white" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="rooms.php"><i class="bi bi-door-open me-2"></i>Room Management</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white active" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>All Bookings</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="users.php"><i class="bi bi-people me-2"></i>Users</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li class="nav-item mt-5"><a class="nav-link text-white" href="../index.php"><i class="bi bi-house me-2"></i>View Website</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="../pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content flex-grow-1">
            <h3 class="mb-4">Bookings Management</h3>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="checked_in" <?= ($filters['status'] ?? '') === 'checked_in' ? 'selected' : '' ?>>Checked In</option>
                                <option value="checked_out" <?= ($filters['status'] ?? '') === 'checked_out' ? 'selected' : '' ?>>Checked Out</option>
                                <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" placeholder="From Date" value="<?= $filters['date_from'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" placeholder="To Date" value="<?= $filters['date_to'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-accent w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="card">
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
                                    <th>Guests</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>#<?= $booking['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($booking['user_name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($booking['user_email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($booking['room_type']) ?> (<?= htmlspecialchars($booking['room_number']) ?>)</td>
                                    <td><?= date('M d, Y', strtotime($booking['check_in'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['check_out'])) ?></td>
                                    <td><?= $booking['guests'] ?></td>
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
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?')">Cancel</button>
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
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">No bookings found</td>
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
