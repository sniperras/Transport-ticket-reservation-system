<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (!Auth::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;
if (!$booking_id) {
    header("Location: my_bookings.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT b.*, u.full_name, u.email, u.phone,
                 s.departure_time, s.arrival_time,
                 r.origin, r.destination, r.price,
                 bus.bus_number, bus.bus_type,
                 d.name as driver_name
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN schedules s ON b.schedule_id = s.id
          JOIN routes r ON s.route_id = r.id
          JOIN buses bus ON s.bus_id = bus.id
          LEFT JOIN drivers d ON bus.driver_id = d.id
          WHERE b.id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// Get ticket details
$tickets_query = "SELECT * FROM tickets WHERE booking_id = ?";
$stmt = $db->prepare($tickets_query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-check-circle"></i> Booking Successful!</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="success-icon">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="text-success mt-3">Thank You for Your Booking!</h3>
                        <p class="text-muted">Your booking has been confirmed. Details have been sent to your email.</p>
                    </div>

                    <div class="booking-summary">
                        <h5 class="mb-3">Booking Summary</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Booking Reference:</strong><br>
                                   <span class="h5 text-primary"><?php echo $booking['booking_ref']; ?></span>
                                </p>
                                <p><strong>Customer:</strong><br>
                                   <?php echo $booking['full_name']; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Booking Date:</strong><br>
                                   <?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?>
                                </p>
                                <p><strong>Status:</strong><br>
                                   <span class="badge bg-success">Confirmed</span>
                                </p>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Journey Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Route:</strong> <?php echo $booking['origin']; ?> → <?php echo $booking['destination']; ?></p>
                                        <p><strong>Bus:</strong> <?php echo $booking['bus_number']; ?> (<?php echo $booking['bus_type']; ?>)</p>
                                        <p><strong>Driver:</strong> <?php echo $booking['driver_name'] ?? 'Not Assigned'; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Departure:</strong> <?php echo date('M d, Y h:i A', strtotime($booking['departure_time'])); ?></p>
                                        <p><strong>Arrival:</strong> <?php echo date('M d, Y h:i A', strtotime($booking['arrival_time'])); ?></p>
                                        <p><strong>Passengers:</strong> <?php echo $booking['total_passengers']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Ticket Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Ticket No.</th>
                                                <th>Passenger Name</th>
                                                <th>Age</th>
                                                <th>Seat No.</th>
                                                <th>Fare</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tickets as $ticket): ?>
                                                <tr>
                                                    <td><?php echo $ticket['ticket_number']; ?></td>
                                                    <td><?php echo $ticket['passenger_name']; ?></td>
                                                    <td><?php echo $ticket['passenger_age']; ?></td>
                                                    <td><?php echo $ticket['seat_number']; ?></td>
                                                    <td>$<?php echo number_format($ticket['fare'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                                                <td><strong>$<?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i>
                            <strong>Important:</strong> Please arrive at the boarding point at least 30 minutes before departure.
                            Bring a printed copy of your ticket or show the e-ticket on your mobile device.
                        </div>

                        <div class="text-center mt-4">
                            <a href="print_ticket.php?booking_id=<?php echo $booking_id; ?>" 
                               class="btn btn-primary" target="_blank">
                                <i class="bi bi-printer"></i> Print Ticket
                            </a>
                            <a href="my_bookings.php" class="btn btn-outline-primary">
                                <i class="bi bi-list"></i> View All Bookings
                            </a>
                            <button onclick="window.location.href='search_routes.php'" 
                                    class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Book Another Trip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: bounce 1s ease infinite alternate;
}

@keyframes bounce {
    from { transform: scale(1); }
    to { transform: scale(1.1); }
}

.booking-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
}
</style>

<?php require_once 'includes/footer.php'; ?>