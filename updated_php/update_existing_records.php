<?php
include 'db.php';

try {
    // Get all users
    $users = $pdo->query("SELECT user_id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($users)) {
        echo "No users found in the database.";
        exit;
    }
    
    // Get all daily_savings records without user_id or with invalid user_id
    $stmt = $pdo->query("SELECT * FROM daily_savings WHERE user_id IS NULL OR user_id NOT IN (SELECT user_id FROM users)");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "No records found that need updating.";
        exit;
    }
    
    // Update each record with a user_id
    $updateStmt = $pdo->prepare("UPDATE daily_savings SET user_id = ? WHERE id = ?");
    $count = 0;
    
    foreach ($records as $record) {
        // Assign to first user (you might want to modify this logic)
        $updateStmt->execute([$users[0], $record['id']]);
        $count++;
    }
    
    echo "Updated $count records successfully!";
    
    // Show summary of records per user
    $stmt = $pdo->query("SELECT user_id, COUNT(*) as count FROM daily_savings GROUP BY user_id");
    $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<br><br>Summary of records per user:";
    echo "<pre>";
    print_r($summary);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 