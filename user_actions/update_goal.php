<?php
session_start();
include '../db.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Validate input
if (!isset($_POST['target_amount']) || !isset($_POST['current_amount']) || !isset($_POST['deadline'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$target_amount = floatval($_POST['target_amount']);
$current_amount = floatval($_POST['current_amount']);
$deadline = $_POST['deadline'];

// Validate amounts
if ($target_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Target amount must be greater than 0']);
    exit();
}

if ($current_amount < 0) {
    echo json_encode(['success' => false, 'message' => 'Current amount cannot be negative']);
    exit();
}

if ($current_amount > $target_amount) {
    echo json_encode(['success' => false, 'message' => 'Current amount cannot exceed target amount']);
    exit();
}

// Validate deadline
if (!strtotime($deadline)) {
    echo json_encode(['success' => false, 'message' => 'Invalid deadline date']);
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get current goal
    $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $goal = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($goal) {
        // Update existing goal
        $stmt = $pdo->prepare("UPDATE Goals SET target_amount = :target, current_amount = :current, deadline = :deadline WHERE goal_id = :goal_id");
        $stmt->execute([
            ':target' => $target_amount,
            ':current' => $current_amount,
            ':deadline' => $deadline,
            ':goal_id' => $goal['goal_id']
        ]);
    } else {
        // Create new goal
        $stmt = $pdo->prepare("INSERT INTO Goals (user_id, target_amount, current_amount, deadline) VALUES (:user_id, :target, :current, :deadline)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':target' => $target_amount,
            ':current' => $current_amount,
            ':deadline' => $deadline
        ]);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Goal updated successfully',
        'target_amount' => $target_amount,
        'current_amount' => $current_amount,
        'deadline' => $deadline
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Error updating goal: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 