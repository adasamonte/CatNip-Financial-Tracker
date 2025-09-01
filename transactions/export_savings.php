<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

// Include database connection
include 'db.php';

// Set headers for CSV export
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=daily_savings.csv");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch data from the database
$stmt = $pdo->prepare("SELECT * FROM daily_savings");
$stmt->execute();
$savings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Open output stream
$output = fopen("php://output", "w");

// Write column headers
if (!empty($savings)) {
    fputcsv($output, array_keys($savings[0])); // CSV format (comma-separated)
}

// Write data rows
foreach ($savings as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
