<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (!Auth::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$schedule_id = $_GET['schedule_id'] ?? 0;
$passengers = $_GET['passengers'] ?? 1;

if (!$schedule_id) {
    header("Location: search_routes.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

// Get schedule details
$query = "SELECT s.*, r.*, b.bus_number, b.total_seats,
                 d.name as driver_name, d.license_number
          FROM schedules s
          JOIN routes r ON s.route_id = r.id
          JOIN buses b ON s.bus_id = b.id
          LEFT JOIN drivers d ON b.driver_id = d.id
          WHERE s.id = ? AND s.status = 'scheduled'";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule) {
    die("Schedule not found or not available.");
}

// Get available seats
$seats_query = "SELECT * FROM seats 
                WHERE bus_id = ? AND is_available = 1 
                ORDER BY seat_number";
$stmt = $db->prepare($seats_query);
$stmt->bind_param("i", $schedule['bus_id']);
$stmt->execute();
$available_seats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($available_seats) < $passengers) {
    die("Not enough seats available.");
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_details = $_POST['passengers'];
    $selected_seats = $_POST['seats'];
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // Generate booking reference
        $booking_ref = $auth->generateBookingRef();
        $total_amount = $schedule['price'] * $passengers;
        
        // Create booking
        $booking_query = "INSERT INTO bookings (booking_ref, user_id, schedule_id, 
                          total_passengers, total_amount) 
                          VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($booking_query);
        $stmt->bind_param("siiid", $booking_ref, $_SESSION['user_id'], 
                         $schedule_id, $passengers, $total_amount);
        $stmt->execute();
        $booking_id = $db->insert_id;
        
        // Create tickets and mark seats as booked
        foreach ($passenger_details as $index => $passenger) {
            $ticket_number = $auth->generateTicketNumber();
            $seat_number = $selected_seats[$index];
            
            // Insert ticket
            $ticket_query = "INSERT INTO tickets (ticket_number, booking_id, 
                            passenger_name, passenger_age, seat_number, fare) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($ticket_query);
            $stmt->bind_param("sisisd", $ticket_number, $booking_id, 
                             $passenger['name'], $passenger['age'], 
                             $seat_number, $schedule['price']);
            $stmt->execute();
            
            // Update seat availability
            $update_seat_query = "UPDATE seats SET is_available = 0 
                                 WHERE bus_id = ? AND seat_number = ?";
            $stmt = $db->prepare($update_seat_query);
            $stmt->bind_param("is", $schedule['bus_id'], $seat_number);
            $stmt->execute();
        }
        
        // Update booking payment status
        $update_booking_query = "UPDATE bookings SET payment_status = 'paid' 
                                WHERE id = ?";
        $stmt = $db->prepare($update_booking_query);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        // Commit transaction
        $db->commit();
        
        // Redirect to success page
        header("Location: booking_success.php?booking_id=" . $booking_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback on error
        $db->rollback();
        $error = "Booking failed: " . $e->getMessage();
    }
}

require_once 'includes/header.php';
?>
<div class="container mt-5">
    <h2 class="mb-4">Book Tickets</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Schedule Summary -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Journey Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Route:</strong> <?php echo $schedule['origin']; ?> → <?php echo $schedule['destination']; ?></p>
                    <p><strong>Bus:</strong> <?php echo $schedule['bus_number']; ?></p>
                    <p><strong>Driver:</strong> <?php echo $schedule['driver_name'] ?? 'Not Assigned'; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Departure:</strong> <?php echo date('M d, Y h:i A', strtotime($schedule['departure_time'])); ?></p>
                    <p><strong>Arrival:</strong> <?php echo date('M d, Y h:i A', strtotime($schedule['arrival_time'])); ?></p>
                    <p><strong>Fare:</strong> $<?php echo number_format($schedule['price'], 2); ?> per person</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Passenger Details & Seat Selection</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="bookingForm">
                <!-- Passenger Details -->
                <div class="mb-4">
                    <h6>Passenger Information</h6>
                    <div id="passengerFields">
                        <?php for($i = 0; $i < $passengers; $i++): ?>
                            <div class="row g-3 mb-3 passenger-row">
                                <div class="col-md-6">
                                    <label class="form-label">Passenger <?php echo $i + 1; ?> Name *</label>
                                    <input type="text" class="form-control" 
                                           name="passengers[<?php echo $i; ?>][name]" 
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Age *</label>
                                    <input type="number" class="form-control" 
                                           name="passengers[<?php echo $i; ?>][age]" 
                                           min="1" max="120" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Seat *</label>
                                    <select class="form-control seat-select" 
                                            name="seats[<?php echo $i; ?>]" required>
                                        <option value="">Select Seat</option>
                                        <?php foreach ($available_seats as $seat): ?>
                                            <option value="<?php echo $seat['seat_number']; ?>">
                                                <?php echo $seat['seat_number']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <!-- Seat Map -->
                <div class="mb-4">
                    <h6>Seat Map</h6>
                    <div class="seat-map">
                        <div class="driver-seat text-center mb-3">
                            <span class="badge bg-secondary">Driver</span>
                        </div>
                        <div class="row justify-content-center">
                            <?php
                            $rows = ceil($schedule['total_seats'] / 4);
                            $seat_count = 1;
                            
                            for ($row = 1; $row <= $rows; $row++):
                            ?>
                                <div class="row mb-2">
                                    <?php for ($col = 1; $col <= 4; $col++): ?>
                                        <?php if ($seat_count <= $schedule['total_seats']): ?>
                                            <?php
                                            $seat_number = str_pad($seat_count, 2, '0', STR_PAD_LEFT);
                                            $is_available = false;
                                            $seat_index = array_search($seat_number, array_column($available_seats, 'seat_number'));
                                            $is_selected = false;
                                            ?>
                                            <div class="col-3 text-center">
                                                <div class="seat <?php echo $seat_index !== false ? 'available' : 'booked'; ?> 
                                                    <?php echo $is_selected ? 'selected' : ''; ?>"
                                                    data-seat="<?php echo $seat_number; ?>">
                                                    <?php echo $seat_number; ?>
                                                </div>
                                            </div>
                                            <?php $seat_count++; ?>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="seat-legend mt-3">
                            <span class="me-3"><span class="seat-legend-box available"></span> Available</span>
                            <span class="me-3"><span class="seat-legend-box booked"></span> Booked</span>
                            <span class="me-3"><span class="seat-legend-box selected"></span> Selected</span>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Summary -->
                <div class="mb-4">
                    <h6>Payment Summary</h6>
                    <table class="table table-bordered">
                        <tr>
                            <td>Base Fare (x<?php echo $passengers; ?>)</td>
                            <td class="text-end">$<?php echo number_format($schedule['price'] * $passengers, 2); ?></td>
                        </tr>
                        <tr>
                            <td>Service Tax</td>
                            <td class="text-end">$<?php echo number_format($schedule['price'] * $passengers * 0.05, 2); ?></td>
                        </tr>
                        <tr class="table-active">
                            <th>Total Amount</th>
                            <th class="text-end">$<?php echo number_format($schedule['price'] * $passengers * 1.05, 2); ?></th>
                        </tr>
                    </table>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">Confirm Booking & Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Seat selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const seatElements = document.querySelectorAll('.seat.available');
    const seatSelects = document.querySelectorAll('.seat-select');
    let selectedSeats = [];
    
    // Initialize seat selects
    seatSelects.forEach(select => {
        select.addEventListener('change', function() {
            const seatNumber = this.value;
            const seatDiv = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
            
            // Clear previous selection for this select
            if (this.previousValue) {
                const prevSeat = document.querySelector(`.seat[data-seat="${this.previousValue}"]`);
                if (prevSeat && prevSeat.classList.contains('selected')) {
                    prevSeat.classList.remove('selected');
                    const index = selectedSeats.indexOf(this.previousValue);
                    if (index > -1) {
                        selectedSeats.splice(index, 1);
                    }
                }
            }
            
            // Select new seat
            if (seatNumber && seatDiv) {
                seatDiv.classList.add('selected');
                selectedSeats.push(seatNumber);
                this.previousValue = seatNumber;
            }
        });
    });
    
    // Seat click selection
    seatElements.forEach(seat => {
        seat.addEventListener('click', function() {
            const seatNumber = this.getAttribute('data-seat');
            const select = Array.from(seatSelects).find(s => s.value === '');
            
            if (select && !selectedSeats.includes(seatNumber)) {
                select.value = seatNumber;
                select.dispatchEvent(new Event('change'));
            }
        });
    });
});
</script>

<style>
.seat {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    border: 2px solid #ddd;
    border-radius: 5px;
    margin: 2px;
    cursor: pointer;
    user-select: none;
}

.seat.available {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.seat.booked {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
    cursor: not-allowed;
}

.seat.selected {
    background-color: #007bff;
    border-color: #0056b3;
    color: white;
}

.seat-legend-box {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.seat-legend-box.available { background-color: #d4edda; }
.seat-legend-box.booked { background-color: #f8d7da; }
.seat-legend-box.selected { background-color: #007bff; }
</style>

<?php require_once 'includes/footer.php'; ?>