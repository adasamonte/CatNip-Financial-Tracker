<?php
require_once '../includes/db.php';

// Default admin credentials
$defaultUsername = 'admin';
$defaultPassword = 'admin123'; // You should change this immediately after first login

// Check if admin table exists, if not create it
$createTableQuery = "
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createTableQuery) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Check if admin user already exists
$checkQuery = "SELECT admin_id FROM admin WHERE username = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $defaultUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create new admin user
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $insertQuery = "INSERT INTO admin (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ss", $defaultUsername, $hashedPassword);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!<br>";
        echo "Username: " . htmlspecialchars($defaultUsername) . "<br>";
        echo "Password: " . htmlspecialchars($defaultPassword) . "<br>";
        echo "<strong>Please change these credentials immediately after first login.</strong>";
    } else {
        echo "Error creating admin user: " . $stmt->error;
    }
} else {
    echo "Admin user already exists.";
}

$stmt->close();
$conn->close();
?> 