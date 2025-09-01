<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle cell update action
    if (isset($_POST['action']) && $_POST['action'] === 'update_cell' && isset($_POST['id']) && isset($_POST['field']) && isset($_POST['value'])) {
        $allowed_fields = [
            'Day', 'Daily_Income', 'Daily_Fixed_Expenses', 'Daily_Variable_Expenses',
            'Unexpected_Daily_Costs', 'Daily_Savings_From_Previous_Day', 'Daily_Savings', 'Description'
        ];

        $field = $_POST['field'];
        $value = $_POST['value'];
        $id = $_POST['id'];

        if (!in_array($field, $allowed_fields)) {
            throw new Exception("Invalid field name");
        }

        // If updating a numeric field, ensure it's a valid number
        if ($field !== 'Description' && $field !== 'Day') {
            $value = floatval($value);
            if (is_nan($value)) {
                throw new Exception("Invalid numeric value");
            }
        }

        // Update the specific field
        $stmt = $pdo->prepare("UPDATE daily_savings SET $field = ? WHERE Day_ID = ? AND user_id = ?");
        $stmt->execute([$value, $id, $_SESSION['user_id']]);

        // If updating income or expenses, recalculate daily savings
        if (in_array($field, ['Daily_Income', 'Daily_Fixed_Expenses', 'Daily_Variable_Expenses', 
                            'Unexpected_Daily_Costs', 'Daily_Savings_From_Previous_Day'])) {
            // Get all values for recalculation
            $stmt = $pdo->prepare("SELECT * FROM daily_savings WHERE Day_ID = ? AND user_id = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate new daily savings
            $daily_savings = $row['Daily_Income'] - $row['Daily_Fixed_Expenses'] - 
                            $row['Daily_Variable_Expenses'] - $row['Unexpected_Daily_Costs'] + 
                            $row['Daily_Savings_From_Previous_Day'];

            // Update daily savings
            $stmt = $pdo->prepare("UPDATE daily_savings SET Daily_Savings = ? WHERE Day_ID = ? AND user_id = ?");
            $stmt->execute([$daily_savings, $id, $_SESSION['user_id']]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // Handle delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM daily_savings WHERE Day_ID = ? AND user_id = ?");
        $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        $required_fields = ['day', 'daily_income', 'daily_fixed_expenses', 'daily_variable_expenses', 'unexpected_daily_costs', 'daily_savings_previous'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Calculate daily savings
        $daily_income = floatval($_POST['daily_income']);
        $daily_fixed_expenses = floatval($_POST['daily_fixed_expenses']);
        $daily_variable_expenses = floatval($_POST['daily_variable_expenses']);
        $unexpected_daily_costs = floatval($_POST['unexpected_daily_costs']);
        $daily_savings_previous = floatval($_POST['daily_savings_previous']);
        
        $daily_savings = $daily_income - $daily_fixed_expenses - $daily_variable_expenses - $unexpected_daily_costs + $daily_savings_previous;

        // Check if we're editing an existing record
        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            $stmt = $pdo->prepare("UPDATE daily_savings SET 
                Day = ?, 
                Daily_Income = ?, 
                Daily_Fixed_Expenses = ?, 
                Daily_Variable_Expenses = ?, 
                Unexpected_Daily_Costs = ?, 
                Daily_Savings_From_Previous_Day = ?, 
                Daily_Savings = ?,
                Description = ?
                WHERE Day_ID = ? AND user_id = ?");
            
            $stmt->execute([
                $_POST['day'],
                $daily_income,
                $daily_fixed_expenses,
                $daily_variable_expenses,
                $unexpected_daily_costs,
                $daily_savings_previous,
                $daily_savings,
                $_POST['description'] ?? '',
                $_POST['edit_id'],
                $_SESSION['user_id']
            ]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO daily_savings 
                (user_id, Day, Daily_Income, Daily_Fixed_Expenses, Daily_Variable_Expenses, 
                Unexpected_Daily_Costs, Daily_Savings_From_Previous_Day, Daily_Savings, Description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $_POST['day'],
                $daily_income,
                $daily_fixed_expenses,
                $daily_variable_expenses,
                $unexpected_daily_costs,
                $daily_savings_previous,
                $daily_savings,
                $_POST['description'] ?? ''
            ]);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 