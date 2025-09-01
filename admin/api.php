<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Handle different API actions
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_dashboard_stats':
        getDashboardStats($conn);
        break;
    case 'get_recent_activity':
        getRecentActivity($conn);
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

function getDashboardStats($conn) {
    try {
        // Get total users
        $userQuery = "SELECT COUNT(*) as total FROM users";
        $userResult = $conn->query($userQuery);
        $totalUsers = $userResult->fetch_assoc()['total'];

        // Get total savings
        $savingsQuery = "SELECT SUM(amount) as total FROM daily_savings";
        $savingsResult = $conn->query($savingsQuery);
        $totalSavings = $savingsResult->fetch_assoc()['total'] ?? 0;

        // Get active goals
        $goalsQuery = "SELECT COUNT(*) as total FROM goals WHERE deadline >= CURDATE()";
        $goalsResult = $conn->query($goalsQuery);
        $activeGoals = $goalsResult->fetch_assoc()['total'];

        // Get today's transactions
        $transactionsQuery = "SELECT COUNT(*) as total FROM transactions WHERE DATE(date) = CURDATE()";
        $transactionsResult = $conn->query($transactionsQuery);
        $todayTransactions = $transactionsResult->fetch_assoc()['total'];

        echo json_encode([
            'totalUsers' => $totalUsers,
            'totalSavings' => number_format($totalSavings, 2),
            'activeGoals' => $activeGoals,
            'todayTransactions' => $todayTransactions
        ]);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Failed to fetch dashboard stats']);
        exit;
    }
}

function getRecentActivity($conn) {
    try {
        // Get recent transactions and user activity
        $query = "
            SELECT 
                t.date as time,
                u.username as user,
                'Transaction' as action,
                CONCAT('$', t.amount, ' - ', t.description) as details
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.date DESC
            LIMIT 10
        ";
        
        $result = $conn->query($query);
        $activities = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['time'] = date('Y-m-d H:i:s', strtotime($row['time']));
            $activities[] = $row;
        }

        echo json_encode($activities);
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Failed to fetch recent activity']);
        exit;
    }
} 