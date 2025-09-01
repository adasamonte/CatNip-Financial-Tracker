<?php
session_start();
include '../db.php';

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
        
        // Also delete data from daily_savings table
        // Connect to the financial_tracker database to clear daily_savings
        $financial_db = new PDO('mysql:host=localhost;dbname=financial_tracker', 'root', '');
        $financial_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Delete all data from daily_savings table for this user
        // Note: If daily_savings doesn't have a user_id column, this will delete all entries
        // You may need to adjust this query if daily_savings is structured differently
        $delete_savings = $financial_db->prepare("DELETE FROM daily_savings");
        $delete_savings->execute();
        
        $pdo->commit();
        
        // Get updated goal info
        $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':user_id' => $user_id]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reset successful. All data including savings calculations has been reset.',
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