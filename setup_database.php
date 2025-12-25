<?php
/**
 * Database Setup Script
 * Run this file once to create database and populate with sample data
 */

echo "<h2>Transport System Database Setup</h2>";

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "transport_system";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to MySQL server successfully.<br>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created or already exists.<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db($database);

// SQL statements to create tables
$tables_sql = array(

    // Drop tables if they exist (in correct order for foreign keys)
    "DROP TABLE IF EXISTS tickets",
    "DROP TABLE IF EXISTS bookings",
    "DROP TABLE IF EXISTS seats",
    "DROP TABLE IF EXISTS schedules",
    "DROP TABLE IF EXISTS routes",
    "DROP TABLE IF EXISTS buses",
    "DROP TABLE IF EXISTS drivers",
    "DROP TABLE IF EXISTS users",

    // Create users table
    "CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        user_type ENUM('admin', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Create drivers table
    "CREATE TABLE drivers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        license_number VARCHAR(50) UNIQUE NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Create buses table
    "CREATE TABLE buses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bus_number VARCHAR(20) UNIQUE NOT NULL,
        bus_type ENUM('minibus', 'bus', 'coach') DEFAULT 'bus',
        total_seats INT NOT NULL,
        amenities TEXT,
        driver_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL
    )",

    // Create routes table
    "CREATE TABLE routes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        origin VARCHAR(100) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        distance DECIMAL(10,2),
        duration TIME,
        price DECIMAL(10,2) NOT NULL,
        UNIQUE KEY unique_route (origin, destination)
    )",

    // Create schedules table
    "CREATE TABLE schedules (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bus_id INT NOT NULL,
        route_id INT NOT NULL,
        departure_time DATETIME NOT NULL,
        arrival_time DATETIME NOT NULL,
        status ENUM('scheduled', 'departed', 'arrived', 'cancelled') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
        FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
    )",

    // Create seats table
    "CREATE TABLE seats (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bus_id INT NOT NULL,
        seat_number VARCHAR(10) NOT NULL,
        seat_type ENUM('regular', 'premium') DEFAULT 'regular',
        is_available BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE,
        UNIQUE KEY unique_seat (bus_id, seat_number)
    )",

    // Create bookings table
    "CREATE TABLE bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        booking_ref VARCHAR(20) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        schedule_id INT NOT NULL,
        total_passengers INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
        payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
    )",

    // Create tickets table
    "CREATE TABLE tickets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        ticket_number VARCHAR(20) UNIQUE NOT NULL,
        booking_id INT NOT NULL,
        passenger_name VARCHAR(100) NOT NULL,
        passenger_age INT,
        seat_number VARCHAR(10) NOT NULL,
        fare DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )"
);

// Execute table creation queries
foreach ($tables_sql as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully.<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

// Insert sample data
echo "<h3>Inserting Sample Data...</h3>";

// Insert admin user (password: admin123)
$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password, full_name, user_type) 
        VALUES ('admin', 'admin@transport.com', '$hashed_password', 'System Admin', 'admin')";
if ($conn->query($sql) === TRUE) {
    echo "Admin user created.<br>";
}

// Insert sample customer (password: user123)
$hashed_password = password_hash('user123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password, full_name, phone) 
        VALUES ('john_doe', 'john@example.com', '$hashed_password', 'John Doe', '123-456-7890')";
if ($conn->query($sql) === TRUE) {
    echo "Sample customer created.<br>";
}

// Insert drivers
$drivers = array(
    "('Michael Johnson', 'DL-789012', '555-0101', '123 Main St, New York')",
    "('Robert Smith', 'DL-345678', '555-0102', '456 Oak Ave, Boston')",
    "('David Wilson', 'DL-901234', '555-0103', '789 Pine Rd, Chicago')"
);

foreach ($drivers as $driver) {
    $sql = "INSERT INTO drivers (name, license_number, phone, address) VALUES $driver";
    if ($conn->query($sql) === TRUE) {
        echo "Driver inserted.<br>";
    }
}

// Insert buses
$buses = array(
    "('BUS-001', 'bus', 40, 'AC, WiFi, Reclining Seats', 1)",
    "('BUS-002', 'coach', 50, 'AC, WiFi, TV, Refreshments', 2)",
    "('MINI-001', 'minibus', 20, 'AC, Comfortable Seats', 3)"
);

foreach ($buses as $bus) {
    $sql = "INSERT INTO buses (bus_number, bus_type, total_seats, amenities, driver_id) VALUES $bus";
    if ($conn->query($sql) === TRUE) {
        echo "Bus inserted.<br>";
    }
}

// Insert routes
$routes = array(
    "('New York', 'Boston', 215.00, '04:00:00', 45.00)",
    "('Boston', 'New York', 215.00, '04:00:00', 45.00)",
    "('New York', 'Washington DC', 225.00, '04:30:00', 50.00)",
    "('Washington DC', 'New York', 225.00, '04:30:00', 50.00)",
    "('Boston', 'Philadelphia', 310.00, '06:00:00', 65.00)",
    "('Philadelphia', 'Boston', 310.00, '06:00:00', 65.00)"
);

foreach ($routes as $route) {
    $sql = "INSERT INTO routes (origin, destination, distance, duration, price) VALUES $route";
    if ($conn->query($sql) === TRUE) {
        echo "Route inserted.<br>";
    }
}

// Insert schedules (for next 7 days)
$today = date('Y-m-d');
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));
    
    // New York to Boston schedules
    $schedules = array(
        "(1, 1, '$date 08:00:00', '$date 12:00:00', 'scheduled')",
        "(1, 1, '$date 14:00:00', '$date 18:00:00', 'scheduled')",
        "(2, 2, '$date 09:00:00', '$date 13:00:00', 'scheduled')",
        "(3, 3, '$date 10:00:00', '$date 14:30:00', 'scheduled')"
    );
    
    foreach ($schedules as $schedule) {
        $sql = "INSERT INTO schedules (bus_id, route_id, departure_time, arrival_time, status) VALUES $schedule";
        if ($conn->query($sql) === TRUE) {
            echo "Schedule inserted for $date.<br>";
        }
    }
}

// Create seats for each bus
$buses_result = $conn->query("SELECT id, total_seats FROM buses");
while ($bus = $buses_result->fetch_assoc()) {
    $bus_id = $bus['id'];
    $total_seats = $bus['total_seats'];
    
    for ($i = 1; $i <= $total_seats; $i++) {
        $seat_number = str_pad($i, 2, '0', STR_PAD_LEFT);
        $seat_type = ($i <= 5) ? 'premium' : 'regular';
        
        $sql = "INSERT INTO seats (bus_id, seat_number, seat_type) 
                VALUES ($bus_id, '$seat_number', '$seat_type')";
        $conn->query($sql);
    }
    echo "Created $total_seats seats for bus $bus_id.<br>";
}

echo "<h3 style='color: green;'>Database setup completed successfully!</h3>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li><a href='index.php'>Go to Home Page</a></li>";
echo "<li><a href='login.php'>Login</a> (admin/admin123 or john_doe/user123)</li>";
echo "</ul>";

$conn->close();
?>