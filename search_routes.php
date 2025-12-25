<?php
require_once 'includes/auth.php';

if (!Auth::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$passengers = $_GET['passengers'] ?? 1;

$database = new Database();
$db = $database->getConnection();

$routes = [];
if (!empty($origin) && !empty($destination)) {
    $query = "SELECT r.*, s.departure_time, s.arrival_time, s.id as schedule_id,
                     b.bus_number, b.bus_type, b.total_seats,
                     d.name as driver_name,
                     (SELECT COUNT(*) FROM seats WHERE bus_id = b.id AND is_available = 1) as available_seats
              FROM routes r
              JOIN schedules s ON r.id = s.route_id
              JOIN buses b ON s.bus_id = b.id
              LEFT JOIN drivers d ON b.driver_id = d.id
              WHERE r.origin LIKE ? AND r.destination LIKE ? 
              AND DATE(s.departure_time) = ?
              AND s.status = 'scheduled'
              ORDER BY s.departure_time";
    
    $stmt = $db->prepare($query);
    $origin_param = "%$origin%";
    $destination_param = "%$destination%";
    $stmt->bind_param("sss", $origin_param, $destination_param, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $routes = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Routes - Transport System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">🚌 Transport System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="search_routes.php">Search Routes</a>
                <a class="nav-link" href="my_bookings.php">My Bookings</a>
                <a class="nav-link" href="logout.php">Logout (<?php echo Auth::getUsername(); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Search Results</h2>
        
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="origin" 
                               placeholder="From" value="<?php echo htmlspecialchars($origin); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="destination" 
                               placeholder="To" value="<?php echo htmlspecialchars($destination); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date" 
                               value="<?php echo $date; ?>" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="passengers">
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == $passengers ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> Passengers
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <?php if (empty($origin) || empty($destination)): ?>
            <div class="alert alert-info">
                Please enter origin and destination to search for routes.
            </div>
        <?php elseif (empty($routes)): ?>
            <div class="alert alert-warning">
                No routes found for your search criteria. Please try different locations or date.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($routes as $route): ?>
                    <?php
                    $departure_time = new DateTime($route['departure_time']);
                    $arrival_time = new DateTime($route['arrival_time']);
                    $duration = $departure_time->diff($arrival_time);
                    $available_seats = $route['available_seats'] ?? 0;
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($route['origin']); ?> → 
                                            <?php echo htmlspecialchars($route['destination']); ?>
                                        </h5>
                                        <h6 class="text-muted">Bus: <?php echo $route['bus_number']; ?> (<?php echo $route['bus_type']; ?>)</h6>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo $available_seats >= $passengers ? 'success' : 'danger'; ?>">
                                            <?php echo $available_seats; ?> Seats Available
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Departure</small>
                                        <div class="fw-bold"><?php echo $departure_time->format('h:i A'); ?></div>
                                        <small><?php echo $departure_time->format('M d, Y'); ?></small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Arrival</small>
                                        <div class="fw-bold"><?php echo $arrival_time->format('h:i A'); ?></div>
                                        <small><?php echo $arrival_time->format('M d, Y'); ?></small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Duration</small>
                                    <div><?php echo $duration->format('%h hr %i min'); ?></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="h4 text-primary">$<?php echo number_format($route['price'], 2); ?></span>
                                        <small class="text-muted">/person</small>
                                    </div>
                                    <div>
                                        <?php if ($available_seats >= $passengers): ?>
                                            <a href="book_ticket.php?schedule_id=<?php echo $route['schedule_id']; ?>&passengers=<?php echo $passengers; ?>" 
                                               class="btn btn-primary">
                                                Book Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                Not Enough Seats
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Transport Ticket Reservation System</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>