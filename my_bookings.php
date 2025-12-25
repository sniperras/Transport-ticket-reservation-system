<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (!Auth::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get user bookings
$query = "SELECT b.*, r.origin, r.destination, s.departure_time, s.arrival_time
          FROM bookings b
          JOIN schedules s ON b.schedule_id = s.id
          JOIN routes r ON s.route_id = r.id
          WHERE b.user_id = ?
          ORDER BY b.booking_date DESC";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Transport System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">🚌 Transport System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="search_routes.php">Book Tickets</a>
                <a class="nav-link active" href="my_bookings.php">My Bookings</a>
                <a class="nav-link" href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>My Bookings</h2>
        
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">
                You have no bookings yet. <a href="search_routes.php">Book your first ticket!</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Booking Ref</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Passengers</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['booking_ref']; ?></td>
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
                                    <span class="badge bg-<?php 
                                        switch($booking['payment_status']) {
                                            case 'paid': echo 'success'; break;
                                            case 'pending': echo 'warning'; break;
                                            case 'failed': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="print_ticket.php?booking_id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                        <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>