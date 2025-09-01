<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM financial_tracker_1.daily_savings WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);

    echo json_encode([
        'success' => true, 
        'message' => 'All records cleared successfully',
        'deleted_count' => $stmt->rowCount()
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error clearing records: ' . $e->getMessage()]);
}
?> 