// Global variables
let savingsChart = null;

// Document Ready Handler
$(document).ready(function() {
    // Initialize event listeners
    initializeEventListeners();
    
    // Initially hide the predictions display
    $("#predictionsDisplay").hide();
});

// Initialize Event Listeners
function initializeEventListeners() {
    // Future savings button handler
    $("#checkFutureSavings").on("click", handleFutureSavings);

    // Calculator modal handler
    $('#calculateSavingsModal').on('show.bs.modal', function() {
        console.log("Calculator modal opening");
        loadCalculatorContent();
    });

    // Add error handler for modal
    $('#calculateSavingsModal').on('error.bs.modal', function() {
        console.error("Error in calculator modal");
    });
}

// Handle Future Savings Button Click
function handleFutureSavings() {
    console.log("Button clicked");
    
    // Show loading state
    $("#predictionsDisplay").fadeIn();
    
    // Initialize graph container
    initializeGraphContainer();
    
    // Fetch predictions
    fetchPredictions();
    
    // Show the modal
    $("#futureSavingsModal").modal("show");
}

// Initialize Graph Container
function initializeGraphContainer() {
    $("#graph").html(`
        <div style="height: 400px;">
            <canvas id="savingsChart"></canvas>
        </div>
    `);
    
    // Add loading overlay
    $("#graph").append(`
        <div class="loading-overlay">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading predictions...</p>
            </div>
        </div>
    `);
}

// Fetch Predictions
function fetchPredictions() {
    console.log("Starting prediction fetch...");
    
    // Get user_id from PHP session
    const userId = document.getElementById('user_id')?.value;
    if (!userId) {
        console.error("No user ID found");
        handlePredictionError(null, "No user ID", "User ID is required");
        return;
    }
    
    $.ajax({
        url: "get_predictions.php",
        type: "GET",
        data: { user_id: userId },
        dataType: "json",
        success: function(response) {
            console.log("Raw response received:", response);
            handlePredictionResponse(response);
        },
        error: function(xhr, status, error) {
            console.log("AJAX error details:", {
                status: status,
                error: error,
                responseText: xhr.responseText,
                statusText: xhr.statusText,
                statusCode: xhr.status
            });
            handlePredictionError(xhr, status, error);
        }
    });
}

// Handle Prediction Response
function handlePredictionResponse(response) {
    if (!response) {
        console.error("Empty response received");
        $("#graph").html("<p class='text-danger'>Empty response from server</p>");
        return;
    }

    if (!response.predictions || !response.historical) {
        console.error("Invalid response format:", response);
        $("#graph").html("<p class='text-danger'>Invalid response format from server</p>");
        return;
    }

    // Update both the graph and the modal list
    updatePredictionsList(response.predictions.values);
    updateVisualization(response);
}

// Update Predictions List
function updatePredictionsList(predictions) {
    let list = $("#predictionsList");
    list.empty();
    
    predictions.forEach((amount, index) => {
        list.append(`
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Day ${index + 1}
                <span class="badge badge-primary badge-pill">$${amount}</span>
            </li>
        `);
    });
}

// Handle Prediction Errors
function handlePredictionError(xhr, status, error) {
    console.error("Prediction error:", {
        status: status,
        error: error,
        xhr: xhr
    });
    
    // Remove loading overlay if it exists
    $(".loading-overlay").remove();
    
    // Display error in the graph container
    $("#graph").html(`
        <div class="alert alert-danger">
            <h5>Error</h5>
            <p>Failed to fetch predictions. Please try again later.</p>
            <small>If the problem persists, please contact support.</small>
        </div>
    `);
}

// Load Calculator Content
function loadCalculatorContent() {
    console.log("Loading calculator content...");
    $.ajax({
        url: 'calculate_savings.php',
        type: 'GET',
        dataType: 'html',
        success: function(response) {
            console.log("Calculator content loaded successfully");
            $('#calculateSavingsContent').html(response);
            attachSavingsFormListener();
        },
        error: function(xhr, status, error) {
            console.error("Error loading calculator content:", error);
            $('#calculateSavingsContent').html(`
                <div class="alert alert-danger">
                    <h5>Error</h5>
                    <p>Failed to load calculator content. Please try again.</p>
                </div>
            `);
        }
    });
}

// Attach Savings Form Listener
function attachSavingsFormListener() {
    $('#savingsForm').off('submit').on('submit', function(event) {
        event.preventDefault();
        console.log("Form submitted");
        var formData = $(this).serialize();
        
        $.ajax({
            url: 'calculate_savings.php',
            type: 'POST',
            data: formData,
            dataType: 'html',
            success: function(response) {
                console.log("Form submitted successfully");
                $('#calculateSavingsContent').html(response);
                attachSavingsFormListener();
            },
            error: function(xhr, status, error) {
                console.error("Error submitting form:", error);
                alert('Error saving data. Please try again.');
            }
        });
    });
}