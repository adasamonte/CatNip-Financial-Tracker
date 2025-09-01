import pandas as pd
import numpy as np
import json
import sys
import os
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression

# Redirect stderr to a file for debugging
sys.stderr = open('C:/xampp/htdocs/catnip/python_errors.log', 'a')

try:
    # Script directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    csv_path = os.path.join(script_dir, 'daily_savings.csv')
    
    # Check if file exists
    if not os.path.exists(csv_path):
        result = {"error": f"CSV file not found at: {csv_path}"}
        print(json.dumps(result))
        sys.exit(1)
    
    # Load training data from CSV
    training_data = pd.read_csv(csv_path)
    
    # Select features and target
    features = ['Daily_Income', 'Daily_Fixed_Expenses', 'Daily_Variable_Expenses', 
                'Unexpected_Daily_Costs', 'Daily_Savings_From_Previous_Day']
    target = 'Daily_Savings'
    
    # Validate expected columns exist in training data
    missing_cols = [col for col in features + [target] if col not in training_data.columns]
    if missing_cols:
        result = {"error": f"Missing columns in CSV: {', '.join(missing_cols)}"}
        print(json.dumps(result))
        sys.exit(1)
    
    # Train model on CSV data
    X_train = training_data[features]
    y_train = training_data[target]
    
    # Train Model
    model = LinearRegression()
    model.fit(X_train, y_train)
    
    # Get user's last data point from command line arguments
    if len(sys.argv) > 1:
        user_data = json.loads(sys.argv[1])
        last_row = pd.Series(user_data)
    else:
        result = {"error": "No user data provided"}
        print(json.dumps(result))
        sys.exit(1)
    
    # Generate Future Predictions for Next 7 Days
    future_days = 7
    future_predictions = []
    
    # Use user's last data point as base for predictions
    current_row = last_row.copy()
    
    # Calculate base savings rate
    base_income = current_row['Daily_Income']
    base_expenses = (current_row['Daily_Fixed_Expenses'] + 
                    current_row['Daily_Variable_Expenses'] + 
                    current_row['Unexpected_Daily_Costs'])
    base_savings_rate = (base_income - base_expenses) / base_income
    
    for i in range(future_days):
        # Add small random variations to simulate real-world changes
        # Income variations (slightly positive bias)
        income_variation = np.random.uniform(-0.02, 0.03)
        current_row['Daily_Income'] = base_income * (1 + income_variation)
        
        # Expense variations (slightly negative bias)
        fixed_expense_variation = np.random.uniform(-0.01, 0.02)
        variable_expense_variation = np.random.uniform(-0.02, 0.03)
        unexpected_cost_variation = np.random.uniform(-0.03, 0.02)
        
        current_row['Daily_Fixed_Expenses'] = base_expenses * 0.4 * (1 + fixed_expense_variation)
        current_row['Daily_Variable_Expenses'] = base_expenses * 0.4 * (1 + variable_expense_variation)
        current_row['Unexpected_Daily_Costs'] = base_expenses * 0.2 * (1 + unexpected_cost_variation)
        
        # Ensure expenses don't exceed income
        total_expenses = (current_row['Daily_Fixed_Expenses'] + 
                         current_row['Daily_Variable_Expenses'] + 
                         current_row['Unexpected_Daily_Costs'])
        
        if total_expenses > current_row['Daily_Income']:
            ratio = current_row['Daily_Income'] / total_expenses
            current_row['Daily_Fixed_Expenses'] *= ratio
            current_row['Daily_Variable_Expenses'] *= ratio
            current_row['Unexpected_Daily_Costs'] *= ratio
        
        # Use previous prediction as savings from previous day
        if future_predictions:
            current_row['Daily_Savings_From_Previous_Day'] = future_predictions[-1]
        
        # Make prediction
        predicted_savings = model.predict(pd.DataFrame([current_row[features]]))[0]
        
        # Apply realistic constraints
        predicted_savings = max(0, predicted_savings)  # Ensure non-negative
        
        # Ensure savings don't exceed income
        predicted_savings = min(predicted_savings, current_row['Daily_Income'])
        
        # Add some randomness while maintaining realistic savings rate
        savings_variation = np.random.uniform(-0.1, 0.1)
        predicted_savings *= (1 + savings_variation)
        
        # Round to 2 decimal places
        predicted_savings = round(float(predicted_savings), 2)
        
        future_predictions.append(predicted_savings)
    
    # Return JSON Response
    result = {"predictions": future_predictions}
    print(json.dumps(result))

except Exception as e:
    # Return error as JSON
    print(json.dumps({"error": str(e)}))

# Important: Don't add any debugging prints or other output!