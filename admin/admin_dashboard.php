<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Financial Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .nav-link {
            color: #fff;
        }
        .nav-link:hover {
            background: #495057;
            color: #fff;
        }
        .card-stats {
            transition: transform 0.3s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar p-3">
            <h4 class="text-white mb-4">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-section="dashboard">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="users">
                        <i class="fas fa-users me-2"></i>User Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="transactions">
                        <i class="fas fa-exchange-alt me-2"></i>Transactions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="categories">
                        <i class="fas fa-tags me-2"></i>Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="reports">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <!-- Dashboard Overview -->
            <div id="dashboard-section" class="section active">
                <h2 class="mb-4">Dashboard Overview</h2>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-stats bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <h2 class="card-text" id="total-users">Loading...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Savings</h5>
                                <h2 class="card-text" id="total-savings">Loading...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Goals</h5>
                                <h2 class="card-text" id="active-goals">Loading...</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-stats bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Today's Transactions</h5>
                                <h2 class="card-text" id="today-transactions">Loading...</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-activity">
                                    <!-- Will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other sections will be shown/hidden via JavaScript -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Load dashboard data
    function loadDashboardStats() {
        $.ajax({
            url: 'admin_api.php',
            method: 'GET',
            data: { action: 'get_dashboard_stats' },
            success: function(response) {
                const data = JSON.parse(response);
                $('#total-users').text(data.totalUsers);
                $('#total-savings').text('$' + data.totalSavings);
                $('#active-goals').text(data.activeGoals);
                $('#today-transactions').text(data.todayTransactions);
            },
            error: function() {
                console.error('Failed to load dashboard stats');
            }
        });
    }

    // Load recent activity
    function loadRecentActivity() {
        $.ajax({
            url: 'admin_api.php',
            method: 'GET',
            data: { action: 'get_recent_activity' },
            success: function(response) {
                const activities = JSON.parse(response);
                const tbody = $('#recent-activity');
                tbody.empty();
                
                activities.forEach(activity => {
                    tbody.append(`
                        <tr>
                            <td>${activity.time}</td>
                            <td>${activity.user}</td>
                            <td>${activity.action}</td>
                            <td>${activity.details}</td>
                        </tr>
                    `);
                });
            },
            error: function() {
                console.error('Failed to load recent activity');
            }
        });
    }

    // Navigation
    $('.nav-link').click(function(e) {
        e.preventDefault();
        const section = $(this).data('section');
        $('.section').removeClass('active').hide();
        $(`#${section}-section`).addClass('active').show();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
    });

    // Initial load
    loadDashboardStats();
    loadRecentActivity();
    
    // Refresh data every 5 minutes
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivity();
    }, 300000);
});
</script>

</body>
</html> 