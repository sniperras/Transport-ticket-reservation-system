<?php
/**
 * Authentication functions with session management
 */

class Auth {
    private $db;
    
    public function __construct() {
        $this->startSession();
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Start session only if not already started
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register new user
     */
    public function register($username, $email, $password, $full_name, $phone = null) {
        // Check if user exists
        $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($check_query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            return false; // User already exists
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $insert_query = "INSERT INTO users (username, email, password, full_name, phone) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($insert_query);
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $phone);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Login user
     */
    public function login($username, $password) {
        $query = "SELECT id, username, email, password, user_type, full_name 
                  FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['full_name'] = $user['full_name'];
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
    
    /**
     * Generate booking reference
     */
    public function generateBookingRef() {
        return 'BK-' . strtoupper(uniqid());
    }
    
    /**
     * Generate ticket number
     */
    public function generateTicketNumber() {
        return 'TKT-' . strtoupper(uniqid());
    }
    
    /**
     * Get current user ID
     */
    public static function getUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current username
     */
    public static function getUsername() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['username'] ?? null;
    }
    
    /**
     * Get current user type
     */
    public static function getUserType() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_type'] ?? null;
    }
    
    /**
     * Get current full name
     */
    public static function getFullName() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['full_name'] ?? null;
    }
}
?>