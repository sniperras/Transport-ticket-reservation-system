<?php
require_once 'includes/auth.php';

if (!Auth::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

require_once 'config/database.php';

// Get search parameters with proper filtering
$origin = isset($_GET['origin']) ? trim($_GET['origin']) : '';
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;

$database = new Database();
$db = $database->getConnection();

$routes = [];
$search_performed = false;

if (!empty($origin) && !empty($destination)) {
    $search_performed = true;
    
    $query = "SELECT r.*, s.departure_time, s.arrival_time, s.id as schedule_id,
                     b.bus_number, b.bus_type, b.total_seats,
                     d.name as driver_name,
                     (SELECT COUNT(*) FROM seats WHERE bus_id = b.id AND is_available = 1) as available_seats
              FROM routes r
              JOIN schedules s ON r.id = s.route_id
              JOIN buses b ON s.bus_id = b.id
              LEFT JOIN drivers d ON b.driver_id = d.id
              WHERE LOWER(r.origin) LIKE LOWER(?) 
              AND LOWER(r.destination) LIKE LOWER(?)
              AND DATE(s.departure_time) = ?
              AND s.status = 'scheduled'
              AND s.departure_time > NOW()
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .search-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 25px;
        }
        .route-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .available-seats {
            font-size: 0.9rem;
            padding: 3px 8px;
            border-radius: 15px;
        }
        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">🚌 Transport System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link active" href="search_routes.php">Search Routes</a>
                <a class="nav-link" href="my_bookings.php">My Bookings</a>
                <a class="nav-link" href="logout.php">Logout (<?php echo htmlspecialchars(Auth::getUsername()); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Search Bus Routes</h2>
        
        <!-- Search Form -->
        <div class="search-card mb-4">
            <h4 class="mb-4"><i class="bi bi-search"></i> Find Your Route</h4>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="text" class="form-control" name="origin" 
                           placeholder="Enter origin city" value="<?php echo htmlspecialchars($origin); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="text" class="form-control" name="destination" 
                           placeholder="Enter destination city" value="<?php echo htmlspecialchars($destination); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Travel Date</label>
                    <input type="date" class="form-control" name="date" 
                           value="<?php echo htmlspecialchars($date); ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Passengers</label>
                    <select class="form-select" name="passengers">
                        <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $passengers ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Passenger<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
            
            <!-- Popular Routes -->
            <div class="mt-4">
                <small class="text-muted">Popular routes:</small>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <a href="?origin=New York&destination=Boston&date=<?php echo date('Y-m-d'); ?>&passengers=1" 
                       class="btn btn-sm btn-outline-primary">NY → Boston</a>
                    <a href="?origin=Boston&destination=New York&date=<?php echo date('Y-m-d'); ?>&passengers=1" 
                       class="btn btn-sm btn-outline-primary">Boston → NY</a>
                    <a href="?origin=New York&destination=Washington DC&date=<?php echo date('Y-m-d'); ?>&passengers=1" 
                       class="btn btn-sm btn-outline-primary">NY → DC</a>
                </div>
            </div>
        </div>

        <!-- Results -->
        <?php if ($search_performed): ?>
            <?php if (empty($routes)): ?>
                <div class="alert alert-warning">
                    <h5><i class="bi bi-exclamation-triangle"></i> No routes found</h5>
                    <p>We couldn't find any routes matching your search criteria. Please try:</p>
                    <ul>
                        <li>Checking your spelling</li>
                        <li>Searching for a different date</li>
                        <li>Trying nearby cities</li>
                        <li>Using our popular routes above</li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="bi bi-geo-alt"></i> 
                            <?php echo htmlspecialchars(ucwords($origin)); ?> → 
                            <?php echo htmlspecialchars(ucwords($destination)); ?>
                            <small class="text-muted">(<?php echo count($routes); ?> routes found)</small>
                        </h4>
                    </div>
                    
                    <?php foreach ($routes as $route): ?>
                        <?php
                        $departure_time = new DateTime($route['departure_time']);
                        $arrival_time = new DateTime($route['arrival_time']);
                        $duration = $departure_time->diff($arrival_time);
                        $available_seats = $route['available_seats'] ?? 0;
                        $can_book = $available_seats >= $passengers;
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="route-card card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <i class="bi bi-bus-front"></i> 
                                                <?php echo htmlspecialchars($route['bus_number']); ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($route['bus_type']); ?></span>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-person-badge"></i> Driver: 
                                                <?php echo htmlspecialchars($route['driver_name'] ?? 'Not assigned'); ?>
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="available-seats badge bg-<?php echo $can_book ? 'success' : 'danger'; ?>">
                                                <i class="bi bi-person"></i> 
                                                <?php echo $available_seats; ?> seats available
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center">
                                                <small class="text-muted d-block">Departure</small>
                                                <div class="fw-bold"><?php echo $departure_time->format('h:i A'); ?></div>
                                                <small><?php echo $departure_time->format('D, M d'); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center">
                                                <small class="text-muted d-block">Arrival</small>
                                                <div class="fw-bold"><?php echo $arrival_time->format('h:i A'); ?></div>
                                                <small><?php echo $arrival_time->format('D, M d'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <small class="text-muted">Duration</small>
                                            <div>
                                                <i class="bi bi-clock"></i> 
                                                <?php echo $duration->format('%h hr %i min'); ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Per person</small>
                                            <div class="price-tag">$<?php echo number_format($route['price'], 2); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Total for <?php echo $passengers; ?> passenger<?php echo $passengers > 1 ? 's' : ''; ?></small>
                                            <div class="h5">$<?php echo number_format($route['price'] * $passengers, 2); ?></div>
                                        </div>
                                        <div>
                                            <?php if ($can_book): ?>
                                                <a href="book_ticket.php?schedule_id=<?php echo $route['schedule_id']; ?>&passengers=<?php echo $passengers; ?>" 
                                                   class="btn btn-primary">
                                                    <i class="bi bi-ticket"></i> Book Now
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary" disabled>
                                                    <i class="bi bi-x-circle"></i> Not Enough Seats
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
        <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <div class="display-1 mb-4">🚌</div>
                <h4>Ready to Travel?</h4>
                <p>Enter your journey details above to find available routes and book your tickets.</p>
                <p class="text-muted">Search for routes like "New York to Boston" or click on the popular routes above.</p>
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