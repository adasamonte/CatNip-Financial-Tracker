<?php
include 'db.php';

try {
    // Check if foreign key exists
    $stmt = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS 
                        WHERE CONSTRAINT_SCHEMA = 'financial_tracker_1' 
                        AND TABLE_NAME = 'daily_savings' 
                        AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        // Add foreign key constraint if it doesn't exist
        $pdo->exec("ALTER TABLE daily_savings ADD CONSTRAINT fk_daily_savings_user 
                    FOREIGN KEY (user_id) REFERENCES users(user_id)");
        echo "Foreign key constraint added successfully!";
    } else {
        echo "Foreign key constraint already exists.";
    }
    
    // Check if user_id column is nullable
    $stmt = $pdo->query("SHOW COLUMNS FROM daily_savings WHERE Field = 'user_id'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column['Null'] === 'YES') {
        // Make user_id NOT NULL if it's currently nullable
        $pdo->exec("ALTER TABLE daily_savings MODIFY COLUMN user_id INT NOT NULL");
        echo "<br>user_id column updated to NOT NULL";
    } else {
        echo "<br>user_id column is already NOT NULL";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 