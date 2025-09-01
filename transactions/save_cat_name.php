<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if cat_name is provided
if (!isset($_POST['cat_name'])) {
    echo json_encode(['success' => false, 'message' => 'No cat name provided']);
    exit();
}

$cat_name = trim($_POST['cat_name']);
$user_id = $_SESSION['user_id'];

try {
    // Insert or update cat name in database
    $stmt = $pdo->prepare("INSERT INTO cat_names (user_id, cat_name) 
                          VALUES (:user_id, :cat_name) 
                          ON DUPLICATE KEY UPDATE cat_name = :cat_name");
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':cat_name' => $cat_name
    ]);

    // Update session
    $_SESSION['cat_name'] = $cat_name;

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 