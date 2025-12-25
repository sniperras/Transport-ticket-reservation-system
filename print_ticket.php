<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (!Auth::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$booking_id = $_GET['booking_id'] ?? 0;
if (!$booking_id) {
    die("Invalid booking ID");
}

$database = new Database();
$db = $database->getConnection();

// Get booking details
$query = "SELECT b.*, u.full_name, u.email, u.phone,
                 s.departure_time, s.arrival_time,
                 r.origin, r.destination, r.price,
                 bus.bus_number, bus.bus_type,
                 d.name as driver_name, d.license_number as driver_license
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

if (!$booking) {
    die("Booking not found");
}

// Get ticket details
$tickets_query = "SELECT * FROM tickets WHERE booking_id = ?";
$stmt = $db->prepare($tickets_query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Ticket - <?php echo $booking['booking_ref']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .ticket {
                border: 2px solid #000 !important;
                margin: 0 !important;
            }
        }
        .ticket {
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            background: white;
        }
        .ticket-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .barcode {
            font-family: 'Libre Barcode 128', cursive;
            font-size: 40px;
            text-align: center;
            margin: 20px 0;
        }
        .ticket-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="ticket">
            <!-- Ticket Header -->
            <div class="ticket-header text-center">
                <h1 class="text-primary">BUS TICKET</h1>
                <h3>Transport Ticket System</h3>
                <div class="barcode">*<?php echo $booking['booking_ref']; ?>*</div>
            </div>
            
            <!-- Booking Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="ticket-info">
                        <h5>Booking Information</h5>
                        <p><strong>Booking Reference:</strong> <?php echo $booking['booking_ref']; ?></p>
                        <p><strong>Booking Date:</strong> <?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-success">CONFIRMED</span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ticket-info">
                        <h5>Passenger Information</h5>
                        <p><strong>Name:</strong> <?php echo $booking['full_name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $booking['phone']; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Journey Details -->
            <div class="ticket-info mb-4">
                <h5>Journey Details</h5>
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h4><?php echo $booking['origin']; ?></h4>
                        <p><strong>Departure:</strong><br>
                        <?php echo date('M d, Y', strtotime($booking['departure_time'])); ?><br>
                        <?php echo date('h:i A', strtotime($booking['departure_time'])); ?></p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="mt-4">
                            <i class="bi bi-arrow-right" style="font-size: 2rem;"></i>
                            <p><strong>Bus:</strong> <?php echo $booking['bus_number']; ?></p>
                            <p><strong>Driver:</strong> <?php echo $booking['driver_name']; ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <h4><?php echo $booking['destination']; ?></h4>
                        <p><strong>Arrival:</strong><br>
                        <?php echo date('M d, Y', strtotime($booking['arrival_time'])); ?><br>
                        <?php echo date('h:i A', strtotime($booking['arrival_time'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Ticket Details -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
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
                                <td><strong><?php echo $ticket['seat_number']; ?></strong></td>
                                <td>$<?php echo number_format($ticket['fare'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-active">
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                            <td><strong>$<?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Instructions -->
            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle"></i> Important Instructions:</h6>
                <ul class="mb-0">
                    <li>Please arrive at the boarding point 30 minutes before departure</li>
                    <li>Carry a valid ID proof along with this ticket</li>
                    <li>Ticket is non-transferable</li>
                    <li>For cancellation, contact our customer service at least 2 hours before departure</li>
                </ul>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-4">
                <p class="mb-1"><strong>Customer Support:</strong> +1 (234) 567-8900 | support@transport.com</p>
                <p class="text-muted">Ticket generated on: <?php echo date('M d, Y h:i A'); ?></p>
            </div>
        </div>
        
        <!-- Print Button -->
        <div class="text-center no-print mt-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Ticket
            </button>
            <a href="my_bookings.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>
    
    <script>
        // Auto print on page load
        window.onload = function() {
            // Optional: Uncomment to auto-print
            // window.print();
        };
    </script>
</body>
</html>