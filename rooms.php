<?php
$pageTitle = 'Room Management - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$roomObj = new Room($db);
$roomMediaObj = new RoomMedia($db);

requireAdmin();

$roomTypes = ['Standard Room', 'Deluxe Room', 'Suite', 'Presidential Suite'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $amenities = json_encode($_POST['amenities'] ?? []);
            
            $imageUrl = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../uploads/rooms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imageUrl = 'uploads/rooms/' . $fileName;
                }
            } elseif (!empty($_POST['image_url'])) {
                $imageUrl = sanitize($_POST['image_url']);
            }
            
            $roomData = [
                'room_number' => sanitize($_POST['room_number']),
                'room_type' => sanitize($_POST['room_type']),
                'description' => sanitize($_POST['description']),
                'price' => (float)$_POST['price'],
                'capacity' => (int)$_POST['capacity'],
                'status' => sanitize($_POST['status']),
                'amenities' => $amenities,
                'image' => $imageUrl
            ];
            $roomObj->create($roomData);
            $_SESSION['success'] = 'Room added successfully';
        } elseif ($_POST['action'] === 'edit') {
            $amenities = json_encode($_POST['amenities'] ?? []);
            
            $imageUrl = $_POST['existing_image'];
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $uploadDir = '../uploads/rooms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $imageUrl = 'uploads/rooms/' . $fileName;
                }
            } elseif (!empty($_POST['image_url'])) {
                $imageUrl = sanitize($_POST['image_url']);
            }
            
            $roomData = [
                'room_number' => sanitize($_POST['room_number']),
                'room_type' => sanitize($_POST['room_type']),
                'description' => sanitize($_POST['description']),
                'price' => (float)$_POST['price'],
                'capacity' => (int)$_POST['capacity'],
                'status' => sanitize($_POST['status']),
                'amenities' => $amenities,
                'image' => $imageUrl
            ];
            $roomObj->update((int)$_POST['room_id'], $roomData);
            $_SESSION['success'] = 'Room updated successfully';
        } elseif ($_POST['action'] === 'delete') {
            $roomObj->delete((int)$_POST['room_id']);
            $_SESSION['success'] = 'Room deleted successfully';
        } elseif ($_POST['action'] === 'add_media') {
            if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
                $uploadDir = '../uploads/rooms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . '_' . basename($_FILES['media']['name']);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
                    $roomMediaObj->create([
                        'room_id' => (int)$_POST['room_id'],
                        'media_url' => 'uploads/rooms/' . $fileName,
                        'media_type' => 'image',
                        'display_order' => (int)$_POST['display_order']
                    ]);
                    $_SESSION['success'] = 'Media added successfully';
                }
            }
        } elseif ($_POST['action'] === 'delete_media') {
            $roomMediaObj->delete((int)$_POST['media_id']);
            $_SESSION['success'] = 'Media deleted successfully';
        }
        redirect('rooms.php');
    }
}

$rooms = $roomObj->getAll(['status' => '']);
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
        
        .room-card {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .room-img {
            height: 150px;
            object-fit: cover;
        }
        
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
                <li class="nav-item mb-2"><a class="nav-link text-white active" href="rooms.php"><i class="bi bi-door-open me-2"></i>Room Management</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>All Bookings</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="users.php"><i class="bi bi-people me-2"></i>Users</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li class="nav-item mt-5"><a class="nav-link text-white" href="../index.php"><i class="bi bi-house me-2"></i>View Website</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="../pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Room Management</h3>
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#roomModal">
                    <i class="bi bi-plus-circle me-2"></i>Add Room
                </button>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="row g-4">
                <?php foreach ($rooms as $room): 
                    $amenities = json_decode($room['amenities'] ?? '[]', true);
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card room-card h-100">
                        <img src="<?= htmlspecialchars($room['image']) ?>" class="room-img w-100" alt="<?= htmlspecialchars($room['room_type']) ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($room['room_type']) ?></h5>
                                    <small class="text-muted">Room <?= htmlspecialchars($room['room_number']) ?></small>
                                </div>
                                <span class="badge bg-<?= $room['status'] === 'available' ? 'success' : ($room['status'] === 'occupied' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($room['status']) ?>
                                </span>
                            </div>
                            <p class="text-muted small mb-2"><?= htmlspecialchars(substr($room['description'], 0, 60)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="h5 text-primary mb-0">$<?= number_format($room['price'], 0) ?>/night</span>
                                <span class="text-muted"><i class="bi bi-people"></i> <?= $room['capacity'] ?></span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $room['id'] ?>">Edit</button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal fade" id="editModal<?= $room['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Room</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($room['image']) ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Room Number</label>
                                        <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Room Type</label>
                                        <select name="room_type" class="form-select" required>
                                            <?php foreach ($roomTypes as $type): ?>
                                                <option value="<?= $type ?>" <?= $room['room_type'] === $type ? 'selected' : '' ?>><?= $type ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($room['description']) ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Price</label>
                                            <input type="number" name="price" class="form-control" value="<?= $room['price'] ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Capacity</label>
                                            <input type="number" name="capacity" class="form-control" value="<?= $room['capacity'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="available" <?= $room['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                                            <option value="occupied" <?= $room['status'] === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                            <option value="maintenance" <?= $room['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Current Image</label>
                                        <?php if (!empty($room['image'])): ?>
                                        <img src="../<?= htmlspecialchars($room['image']) ?>" class="img-thumbnail d-block mb-2" style="max-height: 100px;">
                                        <?php endif; ?>
                                        <label class="form-label">Upload New Image</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                        <small class="text-muted">Or use URL below</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image URL</label>
                                        <input type="url" name="image_url" class="form-control" value="<?= htmlspecialchars($room['image']) ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-accent">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="roomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g., 101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room Type</label>
                            <select name="room_type" class="form-select" required>
                                <?php foreach ($roomTypes as $type): ?>
                                    <option value="<?= $type ?>"><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price per night</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacity</label>
                                <input type="number" name="capacity" class="form-control" value="2" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Or use URL below</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-accent">Add Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
