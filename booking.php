<?php
$pageTitle = 'Book Room - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$roomObj = new Room($db);
$bookingObj = new Booking($db);
$settingsObj = new Settings($db);
$paymentMethodObj = new PaymentMethod($db);
$serviceObj = new Service($db);

requireLogin();

$roomId = $_GET['room_id'] ?? null;
$checkIn = $_GET['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));

if (!$roomId) {
    redirect('../index.php');
}

$room = $roomObj->getById($roomId);
if (!$room) {
    $_SESSION['error'] = 'Room not found';
    redirect('../index.php');
}

$settings = $settingsObj->getAll();
$paymentMethods = $paymentMethodObj->getAll();
$services = $serviceObj->getActive();
$checkInTime = $settings['check_in_time'] ?? '14:00';
$checkOutTime = $settings['check_out_time'] ?? '12:00';
$taxRate = (float)($settings['tax_rate'] ?? 10);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkIn = sanitize($_POST['check_in']);
    $checkOut = sanitize($_POST['check_out']);
    $guests = (int)$_POST['guests'];
    $specialRequests = sanitize($_POST['special_requests']);
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');
    $selectedServices = $_POST['services'] ?? [];
    
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($checkInDate < $today) {
        $error = 'Check-in date cannot be in the past';
    } elseif ($checkOutDate <= $checkInDate) {
        $error = 'Check-out date must be after check-in date';
    } elseif ($guests > $room['capacity']) {
        $error = 'Maximum capacity for this room is ' . $room['capacity'] . ' guests';
    } elseif (!$roomObj->isAvailable($roomId, $checkIn, $checkOut)) {
        $error = 'Room is not available for selected dates';
    } else {
        $nights = calculateNights($checkIn, $checkOut);
        $roomPrice = $room['price'] * $nights;
        
        $servicesPrice = 0;
        foreach ($selectedServices as $serviceId) {
            foreach ($services as $s) {
                if ($s['id'] == $serviceId) {
                    $servicesPrice += $s['price'];
                }
            }
        }
        
        $totalPrice = $roomPrice + $servicesPrice;
        $taxAmount = $totalPrice * ($taxRate / 100);
        $grandTotal = $totalPrice + $taxAmount;
        
        $bookingData = [
            'user_id' => $_SESSION['user_id'],
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'total_price' => $grandTotal,
            'status' => 'pending',
            'special_requests' => $specialRequests
        ];
        
        $bookingId = $bookingObj->create($bookingData);
        
        if (!empty($selectedServices)) {
            $paymentObj = new Payment($db);
            $paymentObj->create([
                'booking_id' => $bookingId,
                'user_id' => $_SESSION['user_id'],
                'amount' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'transaction_id' => 'BOOK-' . $bookingId . '-' . time()
            ]);
        }
        
        $_SESSION['success'] = 'Booking created successfully! Booking ID: ' . $bookingId;
        redirect('my-bookings.php');
    }
}

$nights = calculateNights($checkIn, $checkOut);
$totalPrice = calculateTotal($room['price'], $checkIn, $checkOut);
$amenities = json_decode($room['amenities'] ?? '[]', true);
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
            border-radius: 20px;
            overflow: hidden;
        }
        
        .room-img {
            height: 300px;
            object-fit: cover;
        }
        
        .price-summary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 15px;
            color: white;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(233, 69, 96, 0.1);
        }
        
        .btn-accent {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-accent:hover {
            background: #d63651;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(233, 69, 96, 0.4);
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
                <span class="text-white me-3">Welcome, <?= $_SESSION['name'] ?? 'Guest' ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="card booking-card mb-4">
                    <img src="<?= htmlspecialchars($room['image']) ?>" class="room-img" alt="<?= htmlspecialchars($room['room_type']) ?>">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3><?= htmlspecialchars($room['room_type']) ?></h3>
                                <p class="text-muted mb-0">Room <?= htmlspecialchars($room['room_number']) ?></p>
                            </div>
                            <span class="badge bg-success">Available</span>
                        </div>
                        
                        <p class="text-muted"><?= htmlspecialchars($room['description']) ?></p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6><i class="bi bi-cash me-2"></i>Price per night</h6>
                                <h4 class="text-primary">$<?= number_format($room['price'], 2) ?></h4>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-people me-2"></i>Max guests</h6>
                                <h4><?= $room['capacity'] ?> Persons</h4>
                            </div>
                        </div>
                        
                        <h6 class="mt-4">Amenities</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($amenities as $amenity): ?>
                                <span class="badge bg-light text-dark p-2">
                                    <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($amenity) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card booking-card">
                    <div class="card-body p-4">
                        <h4 class="mb-4">Booking Details</h4>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Check-in Date <span class="text-danger">*</span></label>
                                    <input type="date" name="check_in" class="form-control" value="<?= $checkIn ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Check-out Date <span class="text-danger">*</span></label>
                                    <input type="date" name="check_out" class="form-control" value="<?= $checkOut ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Number of Guests <span class="text-danger">*</span></label>
                                    <select name="guests" class="form-select" required>
                                        <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <?php if (!empty($services)): ?>
                                <div class="col-12">
                                    <label class="form-label">Add Extra Services</label>
                                    <div class="row g-2">
                                        <?php foreach ($services as $service): ?>
                                        <div class="col-md-6">
                                            <div class="form-check border rounded p-3">
                                                <input class="form-check-input" type="checkbox" name="services[]" value="<?= $service['id'] ?>" id="service<?= $service['id'] ?>">
                                                <label class="form-check-label w-100" for="service<?= $service['id'] ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>
                                                            <i class="bi bi-<?= $service['type'] === 'food' ? 'cup-hot' : ($service['type'] === 'gym' ? 'person-lines-outline' : 'star') ?> me-1"></i>
                                                            <?= htmlspecialchars($service['name']) ?>
                                                        </span>
                                                        <span class="text-primary fw-bold">$<?= number_format($service['price'], 2) ?></span>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($paymentMethods)): ?>
                                <div class="col-12">
                                    <label class="form-label">Payment Method</label>
                                    <div class="row g-2">
                                        <?php foreach ($paymentMethods as $method): ?>
                                        <div class="col-md-4">
                                            <div class="form-check border rounded p-3">
                                                <input class="form-check-input" type="radio" name="payment_method" value="<?= $method['type'] ?>" id="payment<?= $method['id'] ?>" <?= $method['type'] === 'cash' ? 'checked' : '' ?>>
                                                <label class="form-check-label w-100" for="payment<?= $method['id'] ?>">
                                                    <i class="bi bi-<?= $method['type'] === 'card' ? 'credit-card' : ($method['type'] === 'cash' ? 'cash' : ($method['type'] === 'bank' ? 'bank' : 'paypal')) ?> me-1"></i>
                                                    <?= htmlspecialchars($method['name']) ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-12">
                                    <label class="form-label">Special Requests</label>
                                    <textarea name="special_requests" class="form-control" rows="3" placeholder="Any special requests?"></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-accent w-100">
                                        <i class="bi bi-check-circle me-2"></i>Confirm Booking
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card price-summary p-4 mb-4">
                    <h5 class="mb-3">Price Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Room Rate</span>
                        <span>$<?= number_format($room['price'], 2) ?> x <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<?= number_format($room['price'] * $nights, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Taxes & Fees (<?= $taxRate ?>%)</span>
                        <span>$<?= number_format($room['price'] * $nights * ($taxRate / 100), 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5 class="mb-0">Total</h5>
                        <h5 class="mb-0">$<?= number_format($totalPrice * (1 + $taxRate / 100), 2) ?></h5>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Booking Policy</h6>
                        <ul class="list-unstyled text-muted small">
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Free cancellation before 24h</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Check-in: <?= date('h:i A', strtotime($checkInTime)) ?></li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Check-out: <?= date('h:i A', strtotime($checkOutTime)) ?></li>
                            <li><i class="bi bi-check text-success me-2"></i>No pets allowed</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const checkInInput = document.querySelector('input[name="check_in"]');
        const checkOutInput = document.querySelector('input[name="check_out"]');
        
        checkInInput.addEventListener('change', function() {
            const checkIn = new Date(this.value);
            const checkOut = new Date(checkOutInput.value);
            const tomorrow = new Date(checkIn);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            if (checkOut <= checkIn) {
                checkOutInput.value = tomorrow.toISOString().split('T')[0];
            }
            
            checkOutInput.min = tomorrow.toISOString().split('T')[0];
        });
        
        const today = new Date().toISOString().split('T')[0];
        checkInInput.min = today;
    </script>
</body>
</html>
