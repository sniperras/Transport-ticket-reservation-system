<?php
require_once 'includes/auth.php';
$auth = new Auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transport Ticket Reservation System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: white;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .search-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 1;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">🚌 Transport System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (Auth::isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="search_routes.php">Search Routes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_bookings.php">My Bookings</a>
                        </li>
                        <?php if (Auth::isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout (<?php echo Auth::getUsername(); ?>)</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Book Your Bus Tickets Online</h1>
                    <p class="lead mb-4">Easy, fast, and secure bus ticket reservation system. Travel comfortably to your destination.</p>
                    <div class="d-flex gap-3">
                        <a href="search_routes.php" class="btn btn-primary btn-lg">Book Now</a>
                        <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div style="font-size: 10rem; line-height: 1;">🚌</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Our Service?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🚌</div>
                        <h4>Wide Network</h4>
                        <p>Connect to multiple destinations across the country</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">💺</div>
                        <h4>Easy Seat Selection</h4>
                        <p>Choose your preferred seat with our interactive seat map</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🖨️</div>
                        <h4>E-Tickets</h4>
                        <p>Get instant e-tickets via email or download PDF</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="search-box">
                        <h3 class="mb-4 text-center">Find Your Route</h3>
                        <form action="search_routes.php" method="GET">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="origin" class="form-label">From</label>
                                    <input type="text" class="form-control" id="origin" name="origin" 
                                           placeholder="Enter origin city" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="destination" class="form-label">To</label>
                                    <input type="text" class="form-control" id="destination" name="destination" 
                                           placeholder="Enter destination city" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="date" class="form-label">Travel Date</label>
                                    <input type="date" class="form-control" id="date" name="date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="passengers" class="form-label">Passengers</label>
                                    <select class="form-select" id="passengers" name="passengers">
                                        <?php for($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Passenger<?php echo $i > 1 ? 's' : ''; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 btn-lg">Search Routes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Transport Ticket System</h5>
                    <p>Your reliable partner for comfortable and safe travel.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="search_routes.php" class="text-white text-decoration-none">Book Tickets</a></li>
                        <li><a href="login.php" class="text-white text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p><i class="bi bi-telephone"></i> +1 (234) 567-8900</p>
                    <p><i class="bi bi-envelope"></i> support@transport.com</p>
                </div>
            </div>
            <hr class="bg-white">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Transport Ticket Reservation System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>