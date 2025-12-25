<?php
require_once '../includes/auth.php';

if (!Auth::isLoggedIn() || !Auth::isAdmin()) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get statistics
$queries = [
    'total_bookings' => "SELECT COUNT(*) as count FROM bookings",
    'today_bookings' => "SELECT COUNT(*) as count FROM bookings WHERE DATE(booking_date) = CURDATE()",
    'total_users' => "SELECT COUNT(*) as count FROM users",
    'active_schedules' => "SELECT COUNT(*) as count FROM schedules WHERE status = 'scheduled' AND departure_time > NOW()",
    'total_revenue' => "SELECT SUM(total_amount) as revenue FROM bookings WHERE payment_status = 'paid'"
];

$stats = [];
foreach ($queries as $key => $query) {
    $result = $db->query($query);
    $stats[$key] = $result->fetch_assoc();
}

// Recent bookings
$recent_bookings = $db->query("
    SELECT b.*, u.username, u.full_name, 
           r.origin, r.destination, s.departure_time
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN schedules s ON b.schedule_id = s.id
    JOIN routes r ON s.route_id = r.id
    ORDER BY b.booking_date DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar a:hover {
            background: #495057;
        }
        .sidebar a.active {
            background: #007bff;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <h4 class="text-white p-3">Admin Panel</h4>
                <nav class="nav flex-column">
                    <a href="dashboard.php" class="active">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="manage_routes.php">
                        <i class="bi bi-signpost"></i> Manage Routes
                    </a>
                    <a href="manage_schedules.php">
                        <i class="bi bi-clock"></i> Manage Schedules
                    </a>
                    <a href="manage_buses.php">
                        <i class="bi bi-bus-front"></i> Manage Buses
                    </a>
                    <a href="manage_bookings.php">
                        <i class="bi bi-ticket"></i> Manage Bookings
                    </a>
                    <a href="manage_users.php">
                        <i class="bi bi-people"></i> Manage Users
                    </a>
                    <a href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="p-4">
                    <h2>Admin Dashboard</h2>
                    <p class="text-muted">Welcome, <?php echo $_SESSION['full_name']; ?>!</p>

                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-subtitle">Total Bookings</h6>
                                            <h3 class="card-title"><?php echo $stats['total_bookings']['count']; ?></h3>
                                        </div>
                                        <i class="bi bi-ticket-perforated" style="font-size: 2rem;"></i>
                                    </div>
                                    <small>Today: <?php echo $stats['today_bookings']['count']; ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-subtitle">Total Revenue</h6>
                                            <h3 class="card-title">$<?php echo number_format($stats['total_revenue']['revenue'] ?? 0, 2); ?></h3>
                                        </div>
                                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                                    </div>
                                    <small>All time revenue</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-subtitle">Total Users</h6>
                                            <h3 class="card-title"><?php echo $stats['total_users']['count']; ?></h3>
                                        </div>
                                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                                    </div>
                                    <small>Registered users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-subtitle">Active Schedules</h6>
                                            <h3 class="card-title"><?php echo $stats['active_schedules']['count']; ?></h3>
                                        </div>
                                        <i class="bi bi-clock" style="font-size: 2rem;"></i>
                                    </div>
                                    <small>Upcoming trips</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Bookings Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Bookings</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Customer</th>
                                            <th>Route</th>
                                            <th>Departure</th>
                                            <th>Passengers</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo $booking['booking_ref']; ?></td>
                                                <td><?php echo $booking['full_name']; ?></td>
                                                <td><?php echo $booking['origin']; ?> → <?php echo $booking['destination']; ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($booking['departure_time'])); ?></td>
                                                <td><?php echo $booking['total_passengers']; ?></td>
                                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        switch($booking['status']) {
                                                            case 'confirmed': echo 'success'; break;
                                                            case 'cancelled': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
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
        </div>
    </div>
</body>
</html>