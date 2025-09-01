document.addEventListener('DOMContentLoaded', function() {
    // Feed Modal Functionality
    const feedBtn = document.querySelector('.feed-btn');
    const feedModal = document.querySelector('.feed-modal');
    const submitFeed = document.querySelector('.submit-feed');
    
    if (feedBtn && feedModal) {
        feedBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            feedModal.style.display = feedModal.style.display === 'block' ? 'none' : 'block';
        });

        // Close feed modal when clicking outside
        document.addEventListener('click', function(e) {
            if (!feedBtn.contains(e.target) && !feedModal.contains(e.target)) {
                feedModal.style.display = 'none';
            }
        });

        // Prevent feed modal from closing when clicking inside
        feedModal.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    if (submitFeed) {
        submitFeed.addEventListener('click', function() {
            const amountInput = document.querySelector('.feed-amount');
            const amount = amountInput.value;
            
            if (!amount || isNaN(amount) || amount <= 0) {
                showToast('Please enter a valid amount', 'error');
                return;
            }

            fetch('/catnip/transactions/add_money.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    amount: parseFloat(amount),
                    description: 'Feed'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const percentage = (data.current_amount / data.target_amount) * 100;
                    
                    // Update all displays
                    document.getElementById('goalCurrent').textContent = formatMoney(data.current_amount);
                    document.getElementById('goalProgress').textContent = percentage.toFixed(1);
                    
                    // Update progress bar and text
                    updateProgressBar(percentage, data.current_amount, data.target_amount);
                    
                    // Update cat stage
                    updateCatStage(percentage);
                    
                    // Update money history
                    updateMoneyHistory();
                    
                    // Reset and close feed modal
                    feedModal.style.display = 'none';
                    amountInput.value = '';
                    
                    showToast('Successfully added money!');
                } else {
                    showToast(data.error || 'Failed to add money', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while adding money', 'error');
            });
        });
    }

    // Set Goal Modal Functionality
    const setGoalBtn = document.querySelector('.set-goal-btn');
    const setGoalModal = document.querySelector('.set-goal-modal');
    const clearGoalForm = document.getElementById('clearGoalForm');
    const updateGoalForm = document.getElementById('updateGoalForm');

    // Add this after your existing modal elements
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal-overlay';
    document.body.appendChild(modalOverlay);

    // Update the Set Goal button click handler
    setGoalBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        if (setGoalModal.style.display === 'block') {
            setGoalModal.style.display = 'none';
        } else {
            setGoalModal.style.display = 'block';
            
            // Pre-fill current values
            const currentTarget = document.getElementById('goalTarget').textContent.replace(/[^0-9.-]+/g,"");
            const currentAmount = document.getElementById('goalCurrent').textContent.replace(/[^0-9.-]+/g,"");
            const currentDeadline = document.getElementById('goalDeadline').getAttribute('data-date');
            
            document.getElementById('newTarget').value = currentTarget;
            document.getElementById('newCurrent').value = currentAmount;
            document.getElementById('newDeadline').value = currentDeadline;
        }
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (!setGoalBtn.contains(e.target) && 
            !setGoalModal.contains(e.target)) {
            setGoalModal.style.display = 'none';
        }
    });

    // Prevent modal from closing when clicking inside it
    setGoalModal.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Update the clear and submit handlers
    clearGoalForm.addEventListener('click', function() {
        updateGoalForm.reset();
    });

    updateGoalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = {
            target: document.getElementById('newTarget').value,
            current: document.getElementById('newCurrent').value,
            deadline: document.getElementById('newDeadline').value
        };

        fetch('/catnip/user_actions/update_goal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const percentage = (data.goal.current_amount / data.goal.target_amount) * 100;
                
                // Update all displays
                document.getElementById('goalTarget').textContent = formatMoney(data.goal.target_amount);
                document.getElementById('goalCurrent').textContent = formatMoney(data.goal.current_amount);
                document.getElementById('goalProgress').textContent = percentage.toFixed(1);
                document.getElementById('goalDeadline').textContent = formatDate(data.goal.deadline);
                
                // Update progress bar and text
                updateProgressBar(percentage, data.goal.current_amount, data.goal.target_amount);
                
                // Update cat stage
                updateCatStage(percentage);
                
                setGoalModal.style.display = 'none';
                updateGoalForm.reset();
                
                showToast('Goal updated successfully!');
            } else {
                showToast('Error updating goal: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating the goal', 'error');
        });
    });

    // Start Over functionality
    const startOverBtn = document.getElementById('confirmStartOver');
    if (startOverBtn) {
        startOverBtn.addEventListener('click', function() {
            fetch('/catnip/user_actions/reset_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the dashboard
                    updateDashboard(data);
                    
                    // Reset cat to stage 1
                    document.querySelector('.cat-image').src = 'assets/cat1.png';
                    
                    // Clear money history
                    const historyList = document.querySelector('.money-history .list-group');
                    historyList.innerHTML = '';
                    
                    // Close the modal
                    $('#startOverModal').modal('hide');
                    
                    // Show success message
                    alert('Your progress has been reset successfully!');
                } else {
                    alert('Error resetting progress: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while resetting your progress.');
            });
        });
    }

    function updateDashboard(data) {
        // Update goal information
        document.getElementById('goalTarget').textContent = formatMoney(data.target_amount);
        document.getElementById('goalCurrent').textContent = formatMoney(data.current_amount);
        document.getElementById('goalProgress').textContent = calculateProgress(data.current_amount, data.target_amount);
        
        // Update progress bar
        const progressBar = document.querySelector('.progress-bar');
        const percentage = (data.current_amount / data.target_amount) * 100;
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        
        // Update cat stage
        updateCatStage(percentage);
        
        // Update money history
        updateMoneyHistory();

        // Add this to ensure proper reset display
        if (data.current_amount === 0) {
            document.getElementById('goalCurrent').textContent = formatMoney(0);
            document.getElementById('goalProgress').textContent = '0.0';
            document.querySelector('.progress-bar').style.width = '0%';
            document.querySelector('.progress-bar').setAttribute('aria-valuenow', 0);
        }
    }

    function formatMoney(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    function calculateProgress(current, target) {
        return ((current / target) * 100).toFixed(1);
    }

    function updateCatStage(percentage) {
        let stage = 1;
        if (percentage >= 25) stage = 2;
        if (percentage >= 50) stage = 3;
        if (percentage >= 75) stage = 4;
        
        document.querySelector('.cat-image').src = `assets/cat${stage}.png`;
    }

    function updateMoneyHistory() {
        fetch('/catnip/transactions/get_money_history.php')
            .then(response => response.json())
            .then(data => {
                const historyList = document.querySelector('.money-history .list-group');
                historyList.innerHTML = data.transactions.map(t => `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>${t.description}</span>
                            <span class="badge badge-primary badge-pill">$${formatMoney(t.amount)}</span>
                        </div>
                    </div>
                `).join('');
            });
    }

    // Add these helper functions
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function updateProgressBar(percentage, current, target) {
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.querySelector('.progress-text');
        
        // Update progress bar
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
        
        // Update progress text
        progressText.textContent = `${formatMoney(current)} / ${formatMoney(target)}`;
    }

    // Add a simple toast notification system
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Add this CSS for the toast notifications
    const style = document.createElement('style');
    style.textContent = `
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.3s, fadeOut 0.3s 2.7s;
        }
        .toast.success {
            background-color: #28a745;
        }
        .toast.error {
            background-color: #dc3545;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
}); 