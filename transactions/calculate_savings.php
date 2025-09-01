<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in to access this feature.");
}

// Include database connection
include '../db.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    try {
        // Validate inputs
        $required_fields = ['day', 'daily_income', 'daily_fixed_expenses', 
                          'daily_variable_expenses', 'unexpected_daily_costs', 
                          'daily_savings_previous'];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        $day = $_POST['day'];
        $daily_income = $_POST['daily_income'];
        $fixed_expenses = $_POST['daily_fixed_expenses'];
        $variable_expenses = $_POST['daily_variable_expenses'];
        $unexpected_costs = $_POST['unexpected_daily_costs'];
        $previous_savings = $_POST['daily_savings_previous'];
        $description = isset($_POST['description']) ? $_POST['description'] : '';

        $daily_savings = $daily_income - $fixed_expenses - $variable_expenses - $unexpected_costs + $previous_savings;

        $sql = "INSERT INTO financial_tracker_1.daily_savings (
                    Day, 
                    Daily_Income, 
                    Daily_Fixed_Expenses, 
                    Daily_Variable_Expenses, 
                    Unexpected_Daily_Costs, 
                    Daily_Savings_From_Previous_Day, 
                    Daily_Savings, 
                    Description,
                    user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $day, 
            $daily_income, 
            $fixed_expenses, 
            $variable_expenses, 
            $unexpected_costs, 
            $previous_savings, 
            $daily_savings, 
            $description,
            $_SESSION['user_id']
        ]);

        $success_message = "Record added successfully!";
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Fetch existing records
$records_sql = "SELECT * FROM financial_tracker_1.daily_savings WHERE user_id = ? ORDER BY Day ASC";
try {
    $stmt = $pdo->prepare($records_sql);
    $stmt->execute([$_SESSION['user_id']]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching records: " . $e->getMessage();
    $records = [];
}
?>

<head>
    <!-- Add Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modal container */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999; /* Increased z-index to be above all other elements */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        /* Modal content */
        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 2% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Ensure the calculator container doesn't affect other elements */
        .calculator-container {
            position: relative;
            z-index: 1;
        }

        /* Keep the cat container and rename input in their original position */
        .cat-container {
            position: relative;
            z-index: 1;
        }

        .cat-name-input {
            position: relative;
            z-index: 1;
        }

        .calculator-action-btn {
            padding: 8px 12px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
        }

        .calculator-action-btn i {
            font-size: 16px;
        }

        .calculator-action-btn:hover {
            transform: scale(1.1);
        }

        .edit-btn {
            background-color: #ffc107;
            color: #000;
        }

        .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }

        .edit-btn:hover {
            background-color: #e0a800;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .calculator-table {
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .calculator-table table {
            margin-bottom: 0;
        }

        .calculator-table th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 1;
            padding: 12px;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .calculator-table td {
            padding: 12px;
            vertical-align: middle;
        }

        .calculator-table tr:hover {
            background-color: #f5f5f5;
        }

        /* Ensure the table container takes up available space */
        .col-md-8 {
            height: calc(100vh - 200px);
            overflow: hidden;
        }

        /* Make the table scrollable while keeping the header fixed */
        .table-responsive {
            height: 100%;
            overflow-y: auto;
        }

        /* Add these styles to your existing CSS */
        .editable {
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .editable:hover {
            background-color: #f8f9fa;
        }

        .editable.editing {
            padding: 0;
            background-color: #fff;
        }

        .editable.editing input {
            width: 100%;
            height: 100%;
            padding: 8px 12px;
            border: 1px solid #80bdff;
            border-radius: 4px;
            font-size: 14px;
        }

        .editable.editing input:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        /* Update modal styles */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }

        .modal-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 500;
        }

        .close {
            position: relative;
            right: 0;
            top: 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #666;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            line-height: 1;
        }

        .close:hover {
            color: #333;
        }

        /* Add these styles to your existing CSS */
        .cat-name-container {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .cat-name-input {
            border: none;
            background: transparent;
            font-size: 16px;
            text-align: center;
            width: 150px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.3s;
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
            padding: 4px;
            transition: color 0.3s;
        }

        .edit-cat-name:hover {
            color: #333;
        }

        .cat-name-input:not(:focus) {
            pointer-events: none;
        }

        /* Update the cat container to ensure proper positioning */
        .cat-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 60px; /* Add space for the name input */
        }
    </style>
</head>

<!-- Calculator Modal -->
<div class="modal" id="calculatorModal" style="display: block;">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Calculate Your Savings</h5>
            <button type="button" class="close" id="closeCalculator" aria-label="Close">&times;</button>
        </div>
        <div class="container-fluid calculator-container">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger calculator-alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success calculator-alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Input Form Box -->
                <div class="col-md-4">
                    <div class="card calculator-card">
                        <div class="card-body">
                            <h5 class="card-title">Calculate Savings</h5>
                            <form id="savingsForm" class="mb-4">
                                <input type="hidden" id="edit_id" name="edit_id">
                                <div class="form-group calculator-form-group">
                                    <label for="day">Day:</label>
                                    <input type="date" class="form-control" id="day" name="day" required>
                                </div>
                                
                                <div class="form-group calculator-form-group">
                                    <label for="daily_income">Daily Income:</label>
                                    <input type="number" step="0.01" class="form-control" id="daily_income" name="daily_income" required>
                                </div>
                                
                                <div class="form-group calculator-form-group">
                                    <label for="daily_fixed_expenses">Daily Fixed Expenses:</label>
                                    <input type="number" step="0.01" class="form-control" id="daily_fixed_expenses" name="daily_fixed_expenses" required>
                                </div>
                                
                                <div class="form-group calculator-form-group">
                                    <label for="daily_variable_expenses">Daily Variable Expenses:</label>
                                    <input type="number" step="0.01" class="form-control" id="daily_variable_expenses" name="daily_variable_expenses" required>
                                </div>
                                
                                <div class="form-group calculator-form-group">
                                    <label for="unexpected_daily_costs">Unexpected Daily Costs:</label>
                                    <input type="number" step="0.01" class="form-control" id="unexpected_daily_costs" name="unexpected_daily_costs" required>
                                </div>
                                
                                <div class="form-group calculator-form-group">
                                    <label for="daily_savings_previous">Daily Savings From Previous Day:</label>
                                    <input type="number" step="0.01" class="form-control" id="daily_savings_previous" name="daily_savings_previous" required>
                                </div>
                                
                                <div class="form-group calculator-form-group">
                                    <label for="description">Description (Optional):</label>
                                    <input type="text" class="form-control" id="description" name="description">
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary clear-btn" id="clearForm">
                                        <i class="fas fa-undo"></i> Clear
                                    </button>
                                    <button type="submit" class="btn btn-success submit-btn">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="col-md-8">
                    <div class="table-responsive calculator-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Income</th>
                                    <th>Fixed Expenses</th>
                                    <th>Variable Expenses</th>
                                    <th>Unexpected Costs</th>
                                    <th>Previous Savings</th>
                                    <th>Daily Savings</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($records as $record): ?>
                                    <tr data-id="<?php echo $record['Day_ID']; ?>">
                                        <td class="editable" data-field="Day"><?php echo htmlspecialchars($record['Day']); ?></td>
                                        <td class="editable" data-field="Daily_Income">$<?php echo number_format($record['Daily_Income'], 2); ?></td>
                                        <td class="editable" data-field="Daily_Fixed_Expenses">$<?php echo number_format($record['Daily_Fixed_Expenses'], 2); ?></td>
                                        <td class="editable" data-field="Daily_Variable_Expenses">$<?php echo number_format($record['Daily_Variable_Expenses'], 2); ?></td>
                                        <td class="editable" data-field="Unexpected_Daily_Costs">$<?php echo number_format($record['Unexpected_Daily_Costs'], 2); ?></td>
                                        <td class="editable" data-field="Daily_Savings_From_Previous_Day">$<?php echo number_format($record['Daily_Savings_From_Previous_Day'], 2); ?></td>
                                        <td class="editable" data-field="Daily_Savings">$<?php echo number_format($record['Daily_Savings'], 2); ?></td>
                                        <td class="editable" data-field="Description"><?php echo htmlspecialchars($record['Description']); ?></td>
                                        <td>
                                            <button class="calculator-action-btn delete-btn" data-id="<?php echo $record['Day_ID']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update the cat name input HTML -->
<div class="cat-container">
    <img src="cat.png" alt="Cat" class="cat-image">
    <div class="cat-name-container">
        <input type="text" class="cat-name-input" id="catName" placeholder="Name your cat" value="<?php echo isset($_SESSION['cat_name']) ? htmlspecialchars($_SESSION['cat_name']) : ''; ?>">
        <button type="button" class="edit-cat-name" id="editCatName">
            <i class="fas fa-pencil-alt"></i>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('savingsForm');
    const clearBtn = document.getElementById('clearForm');
    const editIdInput = document.getElementById('edit_id');
    const modal = document.getElementById('calculatorModal');
    const closeBtn = document.getElementById('closeCalculator');

    // Function to show modal
    function showCalculator() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    // Function to hide modal
    function hideCalculator() {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Show modal on page load
    showCalculator();

    // Close button click handler
    closeBtn.onclick = function() {
        hideCalculator();
    };

    // Close modal when clicking outside
    window.onclick = function(e) {
        if (e.target === modal) {
            hideCalculator();
        }
    };

    // Close modal with escape key
    document.onkeydown = function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            hideCalculator();
        }
    };

    // Clear form
    clearBtn.addEventListener('click', function() {
        form.reset();
        editIdInput.value = '';
    });

    // Handle inline editing
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('editable')) {
            const cell = e.target;
            const currentValue = cell.textContent.replace(/[$,]/g, '');
            const field = cell.dataset.field;
            
            // Create input element
            const input = document.createElement('input');
            input.type = field === 'Description' ? 'text' : 'number';
            input.value = currentValue;
            input.step = field !== 'Day' ? '0.01' : '1';
            
            // Add input to cell
            cell.textContent = '';
            cell.appendChild(input);
            cell.classList.add('editing');
            
            // Focus input
            input.focus();
            
            // Handle input blur (when clicking outside)
            input.addEventListener('blur', function() {
                const newValue = this.value;
                const row = cell.closest('tr');
                const rowId = row.dataset.id;
                
                // Update the cell content
                if (field !== 'Description') {
                    cell.textContent = '$' + parseFloat(newValue).toFixed(2);
                } else {
                    cell.textContent = newValue;
                }
                
                // Remove editing class
                cell.classList.remove('editing');
                
                // Update database
                updateCell(rowId, field, newValue);
            });
            
            // Handle enter key
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.blur();
                }
            });
        }
    });

    // Function to update cell in database
    function updateCell(rowId, field, value) {
        fetch('/catnip/transactions/calculator_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_cell&id=${rowId}&field=${field}&value=${value}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error updating record');
                location.reload(); // Reload to show correct data
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating record');
            location.reload();
        });
    }

    // Edit button click handler
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const cells = row.cells;
            
            // Remove dollar signs and commas from values
            const cleanValue = (value) => parseFloat(value.replace(/[$,]/g, ''));
            
            // Fill form with row data
            document.getElementById('day').value = cells[0].textContent;
            document.getElementById('daily_income').value = cleanValue(cells[1].textContent);
            document.getElementById('daily_fixed_expenses').value = cleanValue(cells[2].textContent);
            document.getElementById('daily_variable_expenses').value = cleanValue(cells[3].textContent);
            document.getElementById('unexpected_daily_costs').value = cleanValue(cells[4].textContent);
            document.getElementById('daily_savings_previous').value = cleanValue(cells[5].textContent);
            document.getElementById('description').value = cells[7].textContent;
            editIdInput.value = this.dataset.id;

            // Scroll to form
            document.querySelector('.calculator-card').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Delete button click handler
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                const id = this.dataset.id;
                fetch('/catnip/transactions/calculator_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table
                        this.closest('tr').remove();
                        
                        // If no rows left, show a message
                        const tbody = document.querySelector('.calculator-table tbody');
                        if (tbody.children.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="9" class="text-center">No records found</td></tr>';
                        }
                    } else {
                        alert(data.message || 'Error deleting record');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting record');
                });
            }
        });
    });

    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Add action type to formData
        formData.append('action', formData.get('edit_id') ? 'edit' : 'add');
        
        fetch('/catnip/transactions/calculator_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success calculator-alert';
                alertDiv.textContent = formData.get('edit_id') ? 'Record updated successfully!' : 'Record added successfully!';
                form.parentNode.insertBefore(alertDiv, form);
                
                // Clear form
                form.reset();
                editIdInput.value = '';
                
                // Remove alert after 3 seconds
                setTimeout(() => alertDiv.remove(), 3000);
                
                // Reload the page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                alert(data.message || 'Error saving record');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving record');
        });
    });

    const catNameInput = document.getElementById('catName');
    const editCatNameBtn = document.getElementById('editCatName');
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

    // Edit button click handler
    editCatNameBtn.addEventListener('click', function() {
        isEditing = true;
        catNameInput.style.pointerEvents = 'auto';
        catNameInput.focus();
        editCatNameBtn.style.display = 'none';
    });

    // Handle enter key
    catNameInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            saveCatName();
        }
    });

    // Handle blur (clicking outside)
    catNameInput.addEventListener('blur', function() {
        if (isEditing) {
            saveCatName();
        }
    });
});
</script>