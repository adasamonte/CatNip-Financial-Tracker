$(document).ready(function() {
    // Load dashboard data
    function loadDashboardStats() {
        $.ajax({
            url: '../admin/api.php',
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
            url: '../admin/api.php',
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