<?php
$pageTitle = 'Settings - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$settingsObj = new Settings($db);
$paymentMethodObj = new PaymentMethod($db);
$serviceObj = new Service($db);

requireAdmin();

$settings = $settingsObj->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        $settingsData = [
            'hotel_name' => sanitize($_POST['hotel_name']),
            'hotel_email' => sanitize($_POST['hotel_email']),
            'hotel_phone' => sanitize($_POST['hotel_phone']),
            'hotel_address' => sanitize($_POST['hotel_address']),
            'check_in_time' => sanitize($_POST['check_in_time']),
            'check_out_time' => sanitize($_POST['check_out_time']),
            'currency' => sanitize($_POST['currency']),
            'tax_rate' => sanitize($_POST['tax_rate']),
        ];
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = 'logo_' . time() . '_' . basename($_FILES['logo']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $settingsData['logo'] = 'uploads/' . $fileName;
            }
        }
        
        $settingsObj->saveAll($settingsData);
        $_SESSION['success'] = 'Settings saved successfully!';
        redirect('settings.php');
    }
    
    if (isset($_POST['add_payment_method'])) {
        $paymentMethodObj->create([
            'name' => sanitize($_POST['name']),
            'type' => sanitize($_POST['type']),
            'details' => sanitize($_POST['details']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'display_order' => (int)$_POST['display_order']
        ]);
        $_SESSION['success'] = 'Payment method added!';
        redirect('settings.php');
    }
    
    if (isset($_POST['add_service'])) {
        $serviceObj->create([
            'name' => sanitize($_POST['name']),
            'type' => sanitize($_POST['type']),
            'description' => sanitize($_POST['description']),
            'price' => (float)$_POST['price'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'display_order' => (int)$_POST['display_order']
        ]);
        $_SESSION['success'] = 'Service added!';
        redirect('settings.php');
    }
    
    if (isset($_POST['delete_payment'])) {
        $paymentMethodObj->delete((int)$_POST['id']);
        $_SESSION['success'] = 'Payment method deleted!';
        redirect('settings.php');
    }
    
    if (isset($_POST['delete_service'])) {
        $serviceObj->delete((int)$_POST['id']);
        $_SESSION['success'] = 'Service deleted!';
        redirect('settings.php');
    }
    
    if (isset($_POST['toggle_payment'])) {
        $method = $paymentMethodObj->getAllAdmin();
        foreach ($method as $m) {
            if ($m['id'] == $_POST['id']) {
                $paymentMethodObj->update($_POST['id'], [
                    'name' => $m['name'],
                    'type' => $m['type'],
                    'details' => $m['details'],
                    'is_active' => $m['is_active'] ? 0 : 1,
                    'display_order' => $m['display_order']
                ]);
            }
        }
        redirect('settings.php');
    }
    
    if (isset($_POST['toggle_service'])) {
        $services = $serviceObj->getAll();
        foreach ($services as $s) {
            if ($s['id'] == $_POST['id']) {
                $serviceObj->update($_POST['id'], [
                    'name' => $s['name'],
                    'type' => $s['type'],
                    'description' => $s['description'],
                    'price' => $s['price'],
                    'is_active' => $s['is_active'] ? 0 : 1,
                    'display_order' => $s['display_order']
                ]);
            }
        }
        redirect('settings.php');
    }
}

$paymentMethods = $paymentMethodObj->getAllAdmin();
$services = $serviceObj->getAll();
$activeTab = $_GET['tab'] ?? 'general';
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
        
        .nav-pills .nav-link {
            color: rgba(255,255,255,0.7);
            border-radius: 10px;
            padding: 12px 20px;
            margin-bottom: 5px;
        }
        
        .nav-pills .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-pills .nav-link.active {
            background: var(--accent);
            color: white;
        }
        
        .settings-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
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
        
        .logo-preview {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 10px;
            border: 2px dashed #ddd;
            padding: 10px;
        }
        
        .time-picker-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 15px;
            color: white;
        }
        
        .service-card {
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .service-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3 d-none d-md-block">
            <h4 class="text-white mb-4">
                <i class="bi bi-building"></i> Marco Polo Hotel
            </h4>
            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-2">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link" href="rooms.php">
                        <i class="bi bi-door-open me-2"></i>Room Management
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link" href="bookings.php">
                        <i class="bi bi-calendar-check me-2"></i>All Bookings
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people me-2"></i>Users
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link active" href="settings.php">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a class="nav-link" href="../index.php">
                        <i class="bi bi-house me-2"></i>View Website
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/logout.php">
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
                <h3>Settings</h3>
                <span class="text-muted">Welcome, <?= $_SESSION['name'] ?? 'Admin' ?></span>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card settings-card p-3">
                        <ul class="nav nav-pills flex-column" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab === 'general' ? 'active' : '' ?>" href="?tab=general">
                                    <i class="bi bi-building me-2"></i>General
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab === 'times' ? 'active' : '' ?>" href="?tab=times">
                                    <i class="bi bi-clock me-2"></i>Check In/Out Times
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab === 'payments' ? 'active' : '' ?>" href="?tab=payments">
                                    <i class="bi bi-credit-card me-2"></i>Payment Methods
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab === 'services' ? 'active' : '' ?>" href="?tab=services">
                                    <i class="bi bi-cup-hot me-2"></i>Services (Food & Gym)
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <?php if ($activeTab === 'general'): ?>
                    <div class="card settings-card">
                        <div class="card-header bg-white p-4">
                            <h5 class="mb-0"><i class="bi bi-building me-2"></i>Hotel Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Hotel Name</label>
                                        <input type="text" name="hotel_name" class="form-control" value="<?= htmlspecialchars($settings['hotel_name'] ?? 'Marco Polo Hotel') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="hotel_email" class="form-control" value="<?= htmlspecialchars($settings['hotel_email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="hotel_phone" class="form-control" value="<?= htmlspecialchars($settings['hotel_phone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Currency</label>
                                        <select name="currency" class="form-select">
                                            <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                            <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (€)</option>
                                            <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP (£)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tax Rate (%)</label>
                                        <input type="number" name="tax_rate" class="form-control" value="<?= $settings['tax_rate'] ?? 10 ?>" min="0" max="100" step="0.1">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea name="hotel_address" class="form-control" rows="2"><?= htmlspecialchars($settings['hotel_address'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Hotel Logo</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($settings['logo'])): ?>
                                            <img src="../<?= htmlspecialchars($settings['logo']) ?>" class="logo-preview" alt="Logo">
                                            <?php else: ?>
                                            <div class="logo-preview d-flex align-items-center justify-content-center">
                                                <i class="bi bi-building text-muted fs-1"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <input type="file" name="logo" class="form-control" accept="image/*">
                                                <small class="text-muted">Recommended: 200x200px, PNG or JPG</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="save_settings" class="btn btn-accent px-4">
                                    <i class="bi bi-check-circle me-2"></i>Save Settings
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php elseif ($activeTab === 'times'): ?>
                    <div class="card settings-card">
                        <div class="card-header bg-white p-4">
                            <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Check-In & Check-Out Times</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="time-picker-card p-4">
                                            <h6 class="mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Check-In Time</h6>
                                            <input type="time" name="check_in_time" class="form-control" value="<?= $settings['check_in_time'] ?? '14:00' ?>" required>
                                            <small class="opacity-75">Guests can check in from this time</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="time-picker-card p-4">
                                            <h6 class="mb-3"><i class="bi bi-box-arrow-left me-2"></i>Check-Out Time</h6>
                                            <input type="time" name="check_out_time" class="form-control" value="<?= $settings['check_out_time'] ?? '12:00' ?>" required>
                                            <small class="opacity-75">Guests must check out by this time</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Note:</strong> These times will be displayed on the booking page and confirmation emails.
                                </div>
                                <button type="submit" name="save_settings" class="btn btn-accent px-4">
                                    <i class="bi bi-check-circle me-2"></i>Save Times
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php elseif ($activeTab === 'payments'): ?>
                    <div class="card settings-card mb-4">
                        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Methods</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <input type="text" name="name" class="form-control" placeholder="Method Name" required>
                                </div>
                                <div class="col-md-3">
                                    <select name="type" class="form-select">
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="details" class="form-control" placeholder="Details (optional)">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="display_order" class="form-control" placeholder="Order" value="0">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" name="add_payment_method" class="btn btn-accent w-100"><i class="bi bi-plus"></i></button>
                                </div>
                            </form>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Details</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($paymentMethods as $method): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($method['name']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($method['type']) ?></span></td>
                                            <td><?= htmlspecialchars($method['details'] ?? '-') ?></td>
                                            <td>
                                                <?php if ($method['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                                    <button type="submit" name="toggle_payment" class="btn btn-sm btn-outline-<?= $method['is_active'] ? 'warning' : 'success' ?>">
                                                        <i class="bi bi-<?= $method['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this payment method?');">
                                                    <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                                    <button type="submit" name="delete_payment" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($paymentMethods)): ?>
                                        <tr><td colspan="5" class="text-center py-4">No payment methods added yet</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($activeTab === 'services'): ?>
                    <div class="card settings-card mb-4">
                        <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-cup-hot me-2"></i>Services (Food & Gym)</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <input type="text" name="name" class="form-control" placeholder="Service Name" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="type" class="form-select">
                                        <option value="food">Food</option>
                                        <option value="gym">Gym</option>
                                        <option value="spa">Spa</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="description" class="form-control" placeholder="Description">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="price" class="form-control" placeholder="Price ($)" step="0.01">
                                </div>
                                <div class="col-md-1">
                                    <input type="number" name="display_order" class="form-control" placeholder="Order" value="0">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" name="add_service" class="btn btn-accent w-100"><i class="bi bi-plus"></i></button>
                                </div>
                            </form>
                            
                            <div class="row g-3">
                                <?php foreach ($services as $service): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card service-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="service-icon" style="background: <?= $service['type'] === 'food' ? '#fff3e0' : ($service['type'] === 'gym' ? '#e3f2fd' : '#f3e5f5') ?>; color: <?= $service['type'] === 'food' ? '#ff9800' : ($service['type'] === 'gym' ? '#2196f3' : '#9c27b0') ?>;">
                                                    <i class="bi bi-<?= $service['type'] === 'food' ? 'cup-hot' : ($service['type'] === 'gym' ? 'person-lines-outline' : 'star') ?>"></i>
                                                </div>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                                    <button type="submit" name="toggle_service" class="btn btn-sm btn-link text-<?= $service['is_active'] ? 'success' : 'secondary' ?>">
                                                        <i class="bi bi-<?= $service['is_active'] ? 'toggle-on' : 'toggle-off' ?> fs-5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <h6 class="mt-3"><?= htmlspecialchars($service['name']) ?></h6>
                                            <p class="text-muted small mb-2"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-primary fw-bold">$<?= number_format($service['price'], 2) ?></span>
                                                <form method="POST" onsubmit="return confirm('Delete this service?');">
                                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                                    <button type="submit" name="delete_service" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($services)): ?>
                                <div class="col-12 text-center py-4">
                                    <i class="bi bi-cup-hot text-muted fs-1"></i>
                                    <p class="text-muted mt-2">No services added yet</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
