<?php
session_start();
include '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if (!isset($_POST['record_id'])) {
    echo json_encode(['success' => false, 'message' => 'Record ID not provided']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM financial_tracker_1.daily_savings WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $_POST['record_id'],
        ':user_id' => $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found or already deleted']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error deleting record: ' . $e->getMessage()]);
}
?> 