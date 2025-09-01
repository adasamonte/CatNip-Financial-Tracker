from flask import Flask, jsonify, request
from flask_cors import CORS
import mysql.connector
import logging
import traceback
import json
from datetime import datetime, timedelta
import os
import subprocess

# Configure logging
logging.basicConfig(level=logging.DEBUG)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

def get_db_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="financial_tracker_1"
    )

def get_predictions_from_model(user_data):
    try:
        # Get the absolute path to predict_sales.py
        script_dir = os.path.dirname(os.path.abspath(__file__))
        python_script = os.path.join(script_dir, 'python', 'predict_sales.py')
        
        # Convert user data to JSON string
        user_data_json = json.dumps(user_data)
        
        # Run the Python script and capture its output
        result = subprocess.run(['python', python_script, user_data_json], 
                              capture_output=True, 
                              text=True)
        
        # Parse the JSON output
        if result.returncode == 0:
            return json.loads(result.stdout)
        else:
            logger.error(f"Error running prediction script: {result.stderr}")
            raise Exception("Failed to get predictions from model")
            
    except Exception as e:
        logger.error(f"Error in model prediction: {str(e)}")
        raise

@app.route('/predict')
def get_predictions():
    try:
        # Get user_id from query parameters
        user_id = request.args.get('user_id')
        if not user_id:
            return jsonify({
                "error": "User ID is required"
            }), 400

        # Connect to database
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Get historical data for specific user
        query = """
        SELECT Day, Daily_Income, Daily_Fixed_Expenses, Daily_Variable_Expenses, 
               Unexpected_Daily_Costs, Daily_Savings_From_Previous_Day, Daily_Savings 
        FROM daily_savings 
        WHERE user_id = %s
        ORDER BY Day ASC
        """
        
        cursor.execute(query, (user_id,))
        records = cursor.fetchall()
        
        if not records:
            return jsonify({
                "error": "No historical data available for predictions",
                "user_id": user_id
            }), 400
        
        # Get the last record for prediction
        last_record = records[-1]
        user_data = {
            'Daily_Income': last_record['Daily_Income'],
            'Daily_Fixed_Expenses': last_record['Daily_Fixed_Expenses'],
            'Daily_Variable_Expenses': last_record['Daily_Variable_Expenses'],
            'Unexpected_Daily_Costs': last_record['Unexpected_Daily_Costs'],
            'Daily_Savings_From_Previous_Day': last_record['Daily_Savings_From_Previous_Day']
        }
        
        # Get predictions from the AI model
        model_predictions = get_predictions_from_model(user_data)
        
        if "error" in model_predictions:
            return jsonify({
                "error": "Failed to generate predictions",
                "details": model_predictions["error"]
            }), 500
        
        # Prepare historical data for visualization
        historical_data = {
            "days": [record['Day'] for record in records],
            "savings": [record['Daily_Savings'] for record in records],
            "income": [record['Daily_Income'] for record in records],
            "fixed_expenses": [record['Daily_Fixed_Expenses'] for record in records],
            "variable_expenses": [record['Daily_Variable_Expenses'] for record in records],
            "unexpected_costs": [record['Unexpected_Daily_Costs'] for record in records]
        }
        
        # Prepare future days for x-axis
        last_day = records[-1]['Day']
        future_days = list(range(last_day + 1, last_day + 8))
        
        logger.debug(f"Generated predictions for user {user_id}: {model_predictions['predictions']}")
        
        return jsonify({
            "historical": historical_data,
            "predictions": {
                "days": future_days,
                "values": model_predictions['predictions']
            },
            "status": "success",
            "user_id": user_id
        })

    except Exception as e:
        logger.error(f"Error in prediction for user {user_id}: {str(e)}")
        logger.error(traceback.format_exc())
        return jsonify({
            "error": "Internal server error",
            "details": str(e)
        }), 500
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()

@app.route('/test')
def test():
    return jsonify({"status": "working"})

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
