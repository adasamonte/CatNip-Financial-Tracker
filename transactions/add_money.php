<?php
session_start();
include '../db.php';

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Parse JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log the received data
error_log("Received data: " . print_r($data, true));

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
    error_log("Invalid amount: " . print_r($data, true));
    echo json_encode(['success' => false, 'message' => 'Invalid amount']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get current goal
    $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$goal) {
        $pdo->rollBack();
        error_log("No goal found for user: " . $_SESSION['user_id']);
        echo json_encode(['success' => false, 'message' => 'No goal set']);
        exit();
    }

    // Update goal current amount
    $new_amount = $goal['current_amount'] + $data['amount'];
    $stmt = $pdo->prepare("UPDATE Goals SET current_amount = :amount WHERE goal_id = :goal_id");
    $stmt->execute([
        ':amount' => $new_amount,
        ':goal_id' => $goal['goal_id']
    ]);

    // Add to money history
    $stmt = $pdo->prepare("INSERT INTO MoneyHistory (user_id, amount, description) VALUES (:user_id, :amount, :description)");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':amount' => $data['amount'],
        ':description' => $data['description'] ?? 'Added money to goal'
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'current_amount' => $new_amount,
        'target_amount' => $goal['target_amount']
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Error in add_money.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 