<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = $data['amount'];
    $description = $data['description'];
    
    try {
        $pdo->beginTransaction();
        
        // Add to money history
        $stmt = $pdo->prepare("INSERT INTO MoneyHistory (user_id, amount, description) VALUES (:user_id, :amount, :description)");
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':amount' => $amount,
            ':description' => $description
        ]);
        
        // Update goal progress in the existing Goals table
        $stmt = $pdo->prepare("UPDATE Goals SET current_amount = current_amount + :amount WHERE user_id = :user_id AND deadline > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([
            ':amount' => $amount,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        $pdo->commit();
        
        // Get updated goal info
        $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $goal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'current_amount' => $goal['current_amount'],
            'target_amount' => $goal['target_amount']
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?> 