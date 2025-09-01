<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Include database connection
include 'db.php';

// Fetch user information
$stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch cat name from database
$stmt = $pdo->prepare("SELECT cat_name FROM cat_names WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$cat_name = $stmt->fetchColumn();

// If no cat name in database but exists in session, save it to database
if (!$cat_name && isset($_SESSION['cat_name'])) {
    $stmt = $pdo->prepare("INSERT INTO cat_names (user_id, cat_name) VALUES (:user_id, :cat_name)");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':cat_name' => $_SESSION['cat_name']
    ]);
    $cat_name = $_SESSION['cat_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CatNip</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="style/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- PyScript -->
    <link rel="stylesheet" href="https://pyscript.net/releases/2025.2.4/core.css" />
    <script type="module" src="https://pyscript.net/releases/2025.2.4/core.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="bg-warning fixed-top">
        <div class="container d-flex justify-content-between align-items-center p-2">
            <h1 style="font-family: 'Pacifico', cursive; color: white; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);">CatNip</h1>
            <div>
                <a href="auth/logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </header>

    <!-- Add this right after the header -->
    <input type="hidden" id="user_id" value="<?php echo $_SESSION['user_id']; ?>">

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar ml-4 mt-5">
            <!-- Profile Section -->
            <div class="profile mb-4">
                <?php
                // Enable error reporting for debugging
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                
                // Check if profile picture exists and is not empty
                error_log("User data: " . print_r($user, true));
                error_log("Document Root: " . $_SERVER['DOCUMENT_ROOT']);
                
                // Get the absolute path to the profile picture
                $profile_pic = null;
                
                if (!empty($user['profile_picture'])) {
                    // Get the full path to the profile picture
                    $profile_pic = $user['profile_picture'];
                    error_log("Profile picture path from database: " . $profile_pic);
                    
                    // Check if the file exists using the absolute path
                    $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/catnip/' . $profile_pic;
                    error_log("Checking absolute path: " . $absolute_path);
                    
                    if (file_exists($absolute_path)) {
                        error_log("Profile picture exists at: " . $absolute_path);
                    } else {
                        error_log("Profile picture not found at: " . $absolute_path);
                        // Set default placeholder based on gender
                        $profile_pic = ($user['gender'] === 'male') ? 
                            'assets/male_ph.png' : 
                            'assets/female_ph.jpg';
                        error_log("Using default profile picture: " . $profile_pic);
                    }
                } else {
                    error_log("No profile picture found in database");
                    // Set default placeholder based on gender
                    $profile_pic = ($user['gender'] === 'male') ? 
                        'assets/male_ph.png' : 
                        'assets/female_ph.jpg';
                    error_log("Using default profile picture: " . $profile_pic);
                }
                ?>
                <div class="profile-pic-container">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
                         alt="<?php echo htmlspecialchars($user['username']); ?>'s Profile" 
                         class="profile-pic"
                         onerror="this.onerror=null; this.src='assets/default_ph.png'; console.log('Image failed to load:', this.src);">
                </div>
                <p class="mt-2">Hello, <span id="username"><?php echo htmlspecialchars($user['username']); ?></span>!</p>
            </div>

            <!-- Goals Section -->
            <div class="goals-section">
                <h3 class="mb-3">Your Goal</h3>
                <?php
                // Initialize default values
                $percentage = 0;
                $current_amount = 0;
                $target_amount = 0;
                $deadline = '';
                
                // Fetch user's goal
                $stmt = $pdo->prepare("SELECT * FROM Goals WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([':user_id' => $_SESSION['user_id']]);
                $goal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($goal): 
                    $percentage = ($goal['current_amount'] / $goal['target_amount']) * 100;
                    $current_amount = $goal['current_amount'];
                    $target_amount = $goal['target_amount'];
                    $deadline = $goal['deadline'];
                ?>
                    <div class="current-goal p-3 bg-light rounded">
                        <p>Target: <span id="goalTarget"><?php echo number_format($target_amount, 2); ?></span></p>
                        <p>Current: <span id="goalCurrent"><?php echo number_format($current_amount, 2); ?></span></p>
                        <p>Progress: <span id="goalProgress"><?php echo number_format($percentage, 1); ?></span>%</p>
                        <p>Deadline: <span id="goalDeadline"><?php echo date('M d, Y', strtotime($deadline)); ?></span></p>
                    </div>
                <?php else: ?>
                    <div class="current-goal p-3 bg-light rounded">
                        <p>Target: <span id="goalTarget">$0.00</span></p>
                        <p>Current: <span id="goalCurrent">$0.00</span></p>
                        <p>Progress: <span id="goalProgress">0.0</span>%</p>
                        <p>Deadline: <span id="goalDeadline">Not set</span></p>
                    </div>
                <?php endif; ?>
                <div class="position-relative">
                    <button class="set-goal-btn btn btn-primary mt-3">Set New Goal</button>
                </div>
            </div>

            <!-- Calculate my Savings -->
            <button class="calculate-btn btn btn-success mt-3" data-toggle="modal" data-target="#calculateSavingsModal">
                Calculate My Savings
            </button>

            <!-- Export Button -->
            <a href="transactions/export_savings.php" class="btn btn-success mt-auto">Export Savings</a>

        </div>

        <!-- Main Content -->
        <div class="main-content container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Dashboard Content Container -->
                    <div class="dashboard-content-container mt-5 p-4">
                        <!-- Cat Name Input -->
                        <div class="cat-name-container text-center mb-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="cat-name-wrapper">
                                    <input type="text" class="cat-name-input" id="catName" placeholder="Name your cat" value="<?php echo $cat_name ? htmlspecialchars($cat_name) : ''; ?>">
                                    <button type="button" class="edit-cat-name" id="editCatName">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Growing Cat Image -->
                        <div class="cat-container text-center">
                            <?php
                            // Initialize default values
                            $percentage = 0;
                            $catStage = 1;
                            $current_amount = 0;
                            $target_amount = 0;
                            
                            // Only calculate if goal exists
                            if ($goal): 
                                $percentage = ($goal['current_amount'] / $goal['target_amount']) * 100;
                                $current_amount = $goal['current_amount'];
                                $target_amount = $goal['target_amount'];
                                
                                // Calculate cat stage based on percentage
                                if ($percentage >= 25) $catStage = 2;
                                if ($percentage >= 50) $catStage = 3;
                                if ($percentage >= 75) $catStage = 4;
                            endif;
                            ?>
                            <img src="assets/cat<?php echo $catStage; ?>.png" alt="Growing Cat" class="cat-image mb-3">
                            
                            <div class="progress-container mb-3">
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%" 
                                         aria-valuenow="<?php echo $percentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <p class="progress-text mt-2">
                                    $<?php echo number_format($current_amount, 2); ?>/$<?php echo number_format($target_amount, 2); ?>
                                </p>
                            </div>

                            <!-- Feed Button and Modal -->
                            <div class="feed-container position-relative">
                                <button class="feed-btn btn btn-warning">Feed</button>
                                <div class="feed-modal" style="display: none;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add Money</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                    <div class="form-group">
                                                <label for="feedAmount">Amount</label>
                                                <input type="number" class="feed-amount form-control" id="feedAmount" placeholder="Enter amount" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="button" class="submit-feed btn btn-primary">Feed</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Money History -->
                        <div class="money-history mt-4">
                            <h3>Money History</h3>
                            <div class="list-group">
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM MoneyHistory WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
                                $stmt->execute([':user_id' => $_SESSION['user_id']]);
                                while ($transaction = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($transaction['description']); ?></span>
                                            <span class="badge badge-primary badge-pill">
                                                $<?php echo number_format($transaction['amount'], 2); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <!-- Start Over Button -->
                            <div class="text-center mt-4">
                                <button class="btn btn-danger" id="startOverBtn" data-toggle="modal" data-target="#startOverModal">
                                    Start Over
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-warning fixed-bottom py-2">
        <p class="text-center mb-0">&copy; 2025 CatNip. All rights reserved.</p>
    </footer>

    <!-- Add this temporarily for debugging -->
    <?php if (isset($_SESSION['debug'])): ?>
    <div style="display:none">
        Debug Info:
        <pre>
        <?php
        echo "User Gender: " . htmlspecialchars($user['gender']) . "\n";
        echo "Profile Picture Path: " . htmlspecialchars($profile_pic) . "\n";
        echo "File Exists: " . (file_exists($profile_pic) ? 'Yes' : 'No') . "\n";
        ?>
        </pre>
    </div>
    <?php endif; ?>

    <!-- Forecast sales -->
    <div class="main-content container-fluid">
    <div class="text-center mt-4">
        <button id="checkFutureSavings" class="btn btn-info">Check Your Future Savings</button>
    </div>

    <div id="predictionsDisplay" class="mt-4 prediction-container" style="display: none;">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="predictionTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="graph-tab" data-toggle="tab" href="#graph" role="tab">Graph View</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="list-tab" data-toggle="tab" href="#list" role="tab">List View</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="predictionTabContent">
                                <div class="tab-pane fade show active" id="graph" role="tabpanel">
                                    <div style="height: 400px;"> <!-- Fixed height container -->
                                        <canvas id="savingsChart"></canvas>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="list" role="tabpanel">
                                    <ul id="predictionsList" class="list-group"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal to Display Future Savings -->
<div class="modal fade" id="futureSavingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Future Savings Forecast</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="futureSavingsList" class="list-group"></ul>
            </div>
        </div>
    </div>
</div>

    <!-- Start Over Confirmation Modal -->
    <div class="modal fade" id="startOverModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Reset</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to start over? This will:</p>
                    <ul>
                        <li>Reset your current goal to 0</li>
                        <li>Clear your money history</li>
                        <li>Reset your cat's progress</li>
                    </ul>
                    <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmStartOver">Yes, Start Over</button>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Move this outside the sidebar, right before the closing body tag -->
    <div class="set-goal-modal">
        <h2>Update Goal</h2>
        <form id="updateGoalForm">
            <div class="form-group">
                <label>Target Amount</label>
                <input type="number" id="newTarget" required>
            </div>
            <div class="form-group">
                <label>Current Amount</label>
                <input type="number" id="newCurrent" required>
            </div>
            <div class="form-group">
                <label>Deadline</label>
                <input type="date" id="newDeadline" required>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn btn-secondary" id="clearGoalForm">Clear</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>

    <!-- All script tags should be at the bottom of the body -->
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Then Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <!-- Then Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Then Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Your custom scripts last -->
    <script src="js/scripts.js"></script>
    <script src="js/dashboard.js"></script>

    <!-- Calculator Modal Script -->
<script>
$(document).ready(function() {
    // Load savings calculator content
    $('#calculateSavingsModal').on('show.bs.modal', function() {
        $.ajax({
            url: 'calculate_savings.php',
            type: 'GET',
            success: function(response) {
                $('#calculateSavingsContent').html(response);
                attachSavingsFormListener();
                
                // Add click handler for the close button
                $('#calculateSavingsModal .close').on('click', function() {
                    $('#calculateSavingsModal').modal('hide');
                });
            },
            error: function() {
                $('#calculateSavingsContent').html('<p class="text-danger">Error loading content.</p>');
            }
        });
    });
    
    function attachSavingsFormListener() {
        $('#savingsForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize() + '&submit=1';
            
            $.ajax({
                url: 'calculate_savings.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#calculateSavingsContent').html(response);
                    attachSavingsFormListener();
                },
                error: function() {
                    alert('Error saving data. Please try again.');
                }
            });
        });
    }
});
</script>

    <!-- Calculate Savings Modal -->
    <div class="modal fade" id="calculateSavingsModal" tabindex="-1" role="dialog" aria-labelledby="calculateSavingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="calculateSavingsModalLabel">Calculate Your Savings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="calculateSavingsContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Add these styles to your existing CSS */
    .cat-name-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }

    /* Add styles for the goal update modal */
    .set-goal-modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1060;
        width: 90%;
        max-width: 500px;
    }

    .set-goal-modal h2 {
        margin-bottom: 20px;
        color: #333;
    }

    .set-goal-modal .form-group {
        margin-bottom: 15px;
    }

    .set-goal-modal label {
        display: block;
        margin-bottom: 5px;
        color: #666;
    }

    .set-goal-modal input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .set-goal-modal .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .set-goal-modal button {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .set-goal-modal button[type="submit"] {
        background-color: #007bff;
        color: white;
    }

    .set-goal-modal button[type="button"] {
        background-color: #6c757d;
        color: white;
    }

    .cat-name-wrapper {
        background: rgba(255, 255, 255, 0.9);
        padding: 8px 16px;
        border-radius: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .cat-name-wrapper:hover {
        background: rgba(255, 255, 255, 1);
    }

    .cat-name-input {
        border: none;
        background: transparent;
        font-size: 18px;
        text-align: center;
        width: 150px;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }

    .cat-name-input:focus {
        outline: none;
        background-color: rgba(255, 255, 255, 0.8);
    }

    .edit-cat-name {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 4px 8px;
        transition: color 0.3s;
        font-size: 16px;
        margin-left: 8px;
        position: relative;
        z-index: 1;
    }

    .edit-cat-name:hover {
        color: #333;
    }

    .cat-name-input:not(:focus) {
        pointer-events: none;
    }

    /* Ensure modals don't affect the cat name container */
    .modal {
        z-index: 1050;
    }

    .modal-backdrop {
        z-index: 1040;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const catNameInput = document.getElementById('catName');
        const editCatNameBtn = document.getElementById('editCatName');
        const catNameWrapper = document.querySelector('.cat-name-wrapper');
        let isEditing = false;

        // Function to save cat name
        function saveCatName() {
            const name = catNameInput.value.trim();
            if (name) {
                fetch('/catnip/user_actions/save_cat_name.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cat_name=${encodeURIComponent(name)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        catNameInput.blur();
                        isEditing = false;
                        catNameInput.style.pointerEvents = 'none';
                        editCatNameBtn.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Function to start editing
        function startEditing() {
            isEditing = true;
            catNameInput.style.pointerEvents = 'auto';
            catNameInput.focus();
            editCatNameBtn.style.display = 'none';
        }

        // Click handler for the wrapper
        catNameWrapper.addEventListener('click', function() {
            if (!isEditing) {
                startEditing();
            }
        });

        // Edit button click handler
        editCatNameBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            startEditing();
        });

        // Handle enter key
        catNameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveCatName();
            }
        });

        // Handle blur (clicking outside)
        catNameInput.addEventListener('blur', function() {
            if (isEditing) {
                saveCatName();
            }
        });

        // Make sure the input is initially disabled
        catNameInput.style.pointerEvents = 'none';
});
</script>

    <!-- Add this right before the closing body tag -->
    <script>
    $(document).ready(function() {
        // Feed button click handler
        $('.feed-btn').click(function() {
            $('.feed-modal').show();
        });

        // Close feed modal when clicking outside
        $(window).click(function(event) {
            if ($(event.target).hasClass('feed-modal')) {
                $('.feed-modal').hide();
            }
        });

        // Handle feed submission
        $('.submit-feed').click(function() {
            const amount = $('#feedAmount').val();
            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            $.ajax({
                url: 'add_money.php',
                type: 'POST',
                data: {
                    amount: amount,
                    description: 'Added money to goal'
                },
                success: function(response) {
                    if (response.success) {
                        // Update the progress bar and amounts
                        updateProgress(response.current_amount, response.target_amount);
                        // Update money history
                        updateMoneyHistory();
                        // Close the modal
                        $('.feed-modal').hide();
                        // Clear the input
                        $('#feedAmount').val('');
                    } else {
                        alert('Error adding money: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error adding money. Please try again.');
                }
            });
        });

        // Handle goal update form submission
        $('#updateGoalForm').submit(function(e) {
            e.preventDefault();
            
            const formData = {
                target_amount: $('#newTarget').val(),
                current_amount: $('#newCurrent').val(),
                deadline: $('#newDeadline').val()
            };

            $.ajax({
                url: 'update_goal.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Update the display
                        $('#goalTarget').text(parseFloat(response.target_amount).toFixed(2));
                        $('#goalCurrent').text(parseFloat(response.current_amount).toFixed(2));
                        $('#goalProgress').text(((response.current_amount / response.target_amount) * 100).toFixed(1));
                        $('#goalDeadline').text(new Date(response.deadline).toLocaleDateString());
                        
                        // Update progress bar
                        const percentage = (response.current_amount / response.target_amount) * 100;
                        $('.progress-bar').css('width', percentage + '%');
                        $('.progress-bar').attr('aria-valuenow', percentage);
                        $('.progress-text').text('$' + parseFloat(response.current_amount).toFixed(2) + '/$' + parseFloat(response.target_amount).toFixed(2));
                        
                        // Update cat stage
                        let catStage = 1;
                        if (percentage >= 25) catStage = 2;
                        if (percentage >= 50) catStage = 3;
                        if (percentage >= 75) catStage = 4;
                        $('.cat-image').attr('src', 'assets/cat' + catStage + '.png');
                        
                        // Hide the modal
                        $('.set-goal-modal').hide();
                        
                        // Clear the form
                        $('#updateGoalForm')[0].reset();
                    } else {
                        alert('Error updating goal: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error updating goal. Please try again.');
                }
            });
        });

        // Handle clear button
        $('#clearGoalForm').click(function() {
            $('#updateGoalForm')[0].reset();
        });

        // Handle set goal button
        $('.set-goal-btn').click(function() {
            $('.set-goal-modal').show();
        });

        // Function to update progress bar and amounts
        function updateProgress(current, target) {
            const percentage = (current / target) * 100;
            $('.progress-bar').css('width', percentage + '%');
            $('.progress-bar').attr('aria-valuenow', percentage);
            $('.progress-text').text('$' + parseFloat(current).toFixed(2) + '/$' + parseFloat(target).toFixed(2));
            
            // Update goal section
            $('#goalCurrent').text(parseFloat(current).toFixed(2));
            $('#goalProgress').text(percentage.toFixed(1));
            
            // Update cat stage
            let catStage = 1;
            if (percentage >= 25) catStage = 2;
            if (percentage >= 50) catStage = 3;
            if (percentage >= 75) catStage = 4;
            $('.cat-image').attr('src', 'assets/cat' + catStage + '.png');
        }

        // Function to update money history
        function updateMoneyHistory() {
            $.ajax({
                url: 'get_money_history.php',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const historyHtml = response.transactions.map(transaction => `
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>${transaction.description}</span>
                                    <span class="badge badge-primary badge-pill">
                                        $${parseFloat(transaction.amount).toFixed(2)}
                                    </span>
                                </div>
                            </div>
                        `).join('');
                        $('.money-history .list-group').html(historyHtml);
                    }
                }
            });
        }
    });
    </script>
</body>
</html>