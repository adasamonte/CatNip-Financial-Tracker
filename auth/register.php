<?php
// Set headers in correct order
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

session_start();
include '../db.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if all required fields are present
if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['gender'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$gender = $_POST['gender'];

// Validate input
if (empty($username) || empty($email) || empty($password) || empty($gender)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Check if username or email already exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
    exit();
}

// Handle profile picture upload
$profile_picture_path = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_picture'];
    error_log("Profile picture upload attempt - File details: " . print_r($file, true));
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        error_log("Invalid file type: " . $file['type']);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
        exit();
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        error_log("File too large: " . $file['size']);
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 2MB']);
        exit();
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/profile_pictures/';
    if (!file_exists($upload_dir)) {
        error_log("Creating upload directory: " . $upload_dir);
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $file_extension;
    $profile_picture_path = $upload_dir . $filename;
    error_log("Generated profile picture path: " . $profile_picture_path);

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $profile_picture_path)) {
        error_log("Failed to move uploaded file from " . $file['tmp_name'] . " to " . $profile_picture_path);
        echo json_encode(['success' => false, 'message' => 'Failed to upload profile picture']);
        exit();
    }

    // Log the successful upload
    error_log("Profile picture uploaded successfully to: " . $profile_picture_path);
}

try {
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, gender, profile_picture) VALUES (?, ?, ?, ?, ?)");
    error_log("Attempting to insert user with profile picture path: " . $profile_picture_path);
    $stmt->execute([$username, $email, $hashed_password, $gender, $profile_picture_path]);

    // Set session variables for automatic login
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $username;

    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful',
        'redirect' => 'dashboard.php'
    ]);
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}