<?php
$pageTitle = 'User Management - Marco Polo Hotel';
require_once '../config/db.php';

$db = initDatabase();
$userObj = new User($db);

requireAdmin();

$users = $userObj->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        $userId = (int)$_POST['user_id'];
        if ($userId !== $_SESSION['user_id']) {
            $db->query("DELETE FROM users WHERE id = ?", [$userId]);
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'You cannot delete your own account';
        }
        redirect('users.php');
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
                <li class="nav-item mb-2"><a class="nav-link text-white" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>All Bookings</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white active" href="users.php"><i class="bi bi-people me-2"></i>Users</a></li>
                <li class="nav-item mb-2"><a class="nav-link text-white" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li class="nav-item mt-5"><a class="nav-link text-white" href="../index.php"><i class="bi bi-house me-2"></i>View Website</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="../pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content flex-grow-1">
            <h3 class="mb-4">User Management</h3>
            
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <?= htmlspecialchars($user['name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'warning' : 'success' ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted">You</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .avatar-circle {
            width: 35px;
            height: 35px;
            background: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
