<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Update the most recent goal for the user
        $stmt = $pdo->prepare("
            UPDATE Goals 
            SET target_amount = :target, 
                current_amount = :current, 
                deadline = :deadline 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute([
            ':target' => $data['target'],
            ':current' => $data['current'],
            ':deadline' => $data['deadline'],
            ':user_id' => $_SESSION['user_id']
        ]);

        // Fetch the updated goal data
        $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $updatedGoal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Goal updated successfully',
            'goal' => [
                'target_amount' => $updatedGoal['target_amount'],
                'current_amount' => $updatedGoal['current_amount'],
                'deadline' => $updatedGoal['deadline']
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 