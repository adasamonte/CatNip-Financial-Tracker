<?php
session_start();
include '../db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Log request details
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request Headers: " . print_r(getallheaders(), true));
error_log("POST Data: " . print_r($_POST, true));

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if username and password are provided
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    error_log("Login failed - Missing credentials");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Validate input
if (empty($username) || empty($password)) {
    error_log("Login failed - Empty credentials");
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

try {
    // Get user from database
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log database query result
    error_log("Database query completed. User found: " . ($user ? 'yes' : 'no'));

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        error_log("Login successful for user: " . $username);
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        error_log("Login failed - Invalid credentials for user: " . $username);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} catch (PDOException $e) {
    error_log("Database error during login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}