<?php
header("Content-Type: application/json");

try {
    // Execute the Python script
    $output = shell_exec("python C:/xampp/htdocs/catnip/predict_sales.py 2>&1");
    
    // Debug: Log the raw output
    file_put_contents('python_output_log.txt', $output);
    
    // Check if output is empty
    if (empty($output)) {
        echo json_encode(["error" => "No output from Python script"]);
        exit;
    }
    
    // Try to extract just the JSON part from the output
    if (preg_match('/{.*}/s', $output, $matches)) {
        $json_part = $matches[0];
        $json = json_decode($json_part, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo json_encode($json);
            exit;
        }
    }
    
    // If we reach here, we couldn't extract valid JSON
    echo json_encode([
        "error" => "Failed to parse Python output as JSON",
        "raw_output" => substr($output, 0, 500) // Limit output to 500 chars for security
    ]);
    
} catch (Exception $e) {
    echo json_encode(["error" => "Exception: " . $e->getMessage()]);
}
?>