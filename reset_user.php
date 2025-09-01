<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Get the user's ID
        $user_id = $_SESSION['user_id'];
        
        // Delete money history
        $stmt = $pdo->prepare("DELETE FROM MoneyHistory WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        
        // Reset current goal to 0
        $stmt = $pdo->prepare("UPDATE Goals SET current_amount = 0 WHERE user_id = :user_id AND goal_id = (
            SELECT goal_id FROM (
                SELECT goal_id FROM Goals WHERE user_id = :user_id2 ORDER BY created_at DESC LIMIT 1
            ) as latest_goal
        )");
        $stmt->execute([
            ':user_id' => $user_id,
            ':user_id2' => $user_id
        ]);
        
        $pdo->commit();
        
        // Get updated goal info
        $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reset successful',
            'current_amount' => 0,
            'target_amount' => $goal['target_amount']
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 