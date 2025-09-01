<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $gender = $_POST['gender'];
    
    // Handle profile picture
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['size'] > 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $target_file;
        }
    } else {
        // Set default profile picture based on gender
        $profile_picture = $_POST['gender'] === 'male' ? 'assets/male_ph.png' : 'assets/female_ph.jpg';
    }

    try {
        $pdo->beginTransaction();

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password, gender, profile_picture) VALUES (:username, :email, :password, :gender, :profile_picture)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':gender' => $gender,
            ':profile_picture' => $profile_picture
        ]);

        $user_id = $pdo->lastInsertId();

        // Insert initial goal
        $stmt = $pdo->prepare("INSERT INTO Goals (user_id, target_amount, current_amount, deadline) VALUES (:user_id, :target_amount, 0, :deadline)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':target_amount' => $_POST['initial_goal'],
            ':deadline' => $_POST['goal_deadline']
        ]);

        $pdo->commit();

        // Start session and log user in
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;

        // Return success response
        echo json_encode([
            'success' => true,
            'redirect' => 'dashboard.php'
        ]);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style/styles.css">
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form id="register-form" method="POST" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="file" name="profile_picture" accept="image/*" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="index.php#loginModal">Login here</a></p>
    </div>
    <script src="js/scripts.js"></script>
</body>
</html>