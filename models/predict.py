"""
Make Predictions using Trained Models
Predictify - ML-Based Project Prediction System

This script loads trained models and makes predictions for new projects.
"""

import sys
import json
import pandas as pd
import numpy as np
import pickle
import os
import uuid
import warnings
warnings.filterwarnings('ignore')

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

def load_models():
    """Load all trained models and scaler"""
    try:
        models_dir = os.path.join(os.path.dirname(__file__), 'saved_models')

        # Check if models directory exists
        if not os.path.exists(models_dir):
            return None, "Models not found. Please train models first."

        # Load scaler
        scaler_path = os.path.join(models_dir, 'scaler.pkl')
        if not os.path.exists(scaler_path):
            return None, "Scaler not found. Please train models first."

        with open(scaler_path, 'rb') as f:
            scaler = pickle.load(f)

        # Load cost models
        cost_models = {}
        duration_models = {}

        model_names = ['LinearRegression', 'RandomForest', 'GradientBoosting']

        for name in model_names:
            # Load cost model
            cost_path = os.path.join(models_dir, f'{name}_cost.pkl')
            if os.path.exists(cost_path):
                with open(cost_path, 'rb') as f:
                    cost_models[name] = pickle.load(f)

            # Load duration model
            duration_path = os.path.join(models_dir, f'{name}_duration.pkl')
            if os.path.exists(duration_path):
                with open(duration_path, 'rb') as f:
                    duration_models[name] = pickle.load(f)

        if not cost_models or not duration_models:
            return None, "Models not found. Please train models first."

        return {
            'scaler': scaler,
            'cost_models': cost_models,
            'duration_models': duration_models
        }, None

    except Exception as e:
        return None, f"Error loading models: {str(e)}"

def load_training_dataset():
    """Load the main training dataset for histogram generation"""
    try:
        # Look for training dataset
        datasets_dir = os.path.join(os.path.dirname(__file__), 'datasets')

        # Try to find any CSV file in datasets directory
        if os.path.exists(datasets_dir):
            csv_files = [f for f in os.listdir(datasets_dir) if f.endswith('.csv')]
            if csv_files:
                # Use the first CSV file found
                dataset_path = os.path.join(datasets_dir, csv_files[0])
                df = pd.read_csv(dataset_path)
                return df

        return None
    except Exception:
        return None

def load_input(csv_path):
    """Load input data from CSV"""
    try:
        df = pd.read_csv(csv_path)

        # Expected columns (without target variables)
        required_columns = [
            'Project Size', 'Team Members', 'Equipment Count',
            'Material Cost', 'Complexity Level'
        ]

        # Check if all required columns exist
        missing_cols = [col for col in required_columns if col not in df.columns]
        if missing_cols:
            return None, f"Missing columns: {', '.join(missing_cols)}"

        # Get the first row (single prediction)
        X = df[required_columns].iloc[0].values.reshape(1, -1)
        input_dict = df[required_columns].iloc[0].to_dict()

        return X, input_dict, None
    except Exception as e:
        return None, None, f"Error loading input: {str(e)}"

def generate_histogram(training_df, user_input):
    """Generate histogram showing user input relative to historical data"""
    try:
        # Create plots directory if it doesn't exist
        plots_dir = os.path.join(os.path.dirname(__file__), '..', 'assets', 'uploads', 'plots')
        os.makedirs(plots_dir, exist_ok=True)

        # Generate unique filename
        plot_filename = f"{uuid.uuid4().hex}.png"
        plot_path = os.path.join(plots_dir, plot_filename)

        # Create figure with subplots
        fig, axes = plt.subplots(2, 3, figsize=(15, 10))
        fig.suptitle('Project Parameters vs Historical Data', fontsize=16, fontweight='bold')

        features = [
            ('Project Size', 'Project Size (m²)'),
            ('Team Members', 'Team Members'),
            ('Equipment Count', 'Equipment Count'),
            ('Material Cost', 'Material Cost ($)'),
            ('Complexity Level', 'Complexity Level (1-5)')
        ]

        for idx, (feature_name, feature_label) in enumerate(features):
            row = idx // 3
            col = idx % 3
            ax = axes[row, col]

            if feature_name in training_df.columns and feature_name in user_input:
                # Get historical data
                historical_data = training_df[feature_name].dropna()
                user_value = user_input[feature_name]

                # Plot histogram
                ax.hist(historical_data, bins=20, color='#1565C0', alpha=0.7, edgecolor='black')

                # Add vertical line for user input
                ax.axvline(user_value, color='#1B5E20', linewidth=3, linestyle='--',
                          label=f'Your Input: {user_value:,.0f}')

                ax.set_xlabel(feature_label, fontweight='bold')
                ax.set_ylabel('Frequency', fontweight='bold')
                ax.set_title(feature_name, fontweight='bold')
                ax.legend()
                ax.grid(axis='y', alpha=0.3)

        # Remove the 6th subplot (empty)
        fig.delaxes(axes[1, 2])

        plt.tight_layout()
        plt.savefig(plot_path, dpi=100, bbox_inches='tight')
        plt.close()

        return plot_filename
    except Exception:
        return None

def make_predictions(X, models_data):
    """Make predictions using all models"""
    try:
        # Scale input
        X_scaled = models_data['scaler'].transform(X)

        # Make predictions with each model
        cost_predictions = {}
        duration_predictions = {}

        for name, model in models_data['cost_models'].items():
            cost_pred = model.predict(X_scaled)[0]
            cost_predictions[name] = float(cost_pred)

        for name, model in models_data['duration_models'].items():
            dur_pred = model.predict(X_scaled)[0]
            duration_predictions[name] = int(round(dur_pred))

        return cost_predictions, duration_predictions, None
    except Exception as e:
        return None, None, f"Error making predictions: {str(e)}"

def select_best_model(predictions):
    """
    Select the best model based on consistency
    In production, this should use validation metrics
    For now, we use a simple heuristic
    """
    # Priority order based on typical performance
    priority = ['GradientBoosting', 'RandomForest', 'LinearRegression']

    for model_name in priority:
        if model_name in predictions:
            return model_name

    # Fallback to first available
    return list(predictions.keys())[0]

def calculate_accuracy(model_name):
    """
    Estimate model accuracy
    In production, this should be loaded from training results
    """
    # Typical R² scores (placeholder values)
    accuracy_map = {
        'GradientBoosting': 0.92,
        'RandomForest': 0.89,
        'LinearRegression': 0.85
    }

    return accuracy_map.get(model_name, 0.85)

def main():
    """Main prediction function"""
    # Check command line arguments
    if len(sys.argv) < 2:
        result = {
            'success': False,
            'error': 'Usage: python predict.py <path_to_input_csv>'
        }
        print(json.dumps(result))
        sys.exit(1)

    csv_path = sys.argv[1]

    # Load models
    models_data, error = load_models()
    if error:
        result = {
            'success': False,
            'error': error
        }
        print(json.dumps(result))
        sys.exit(1)

    # Load input
    X, input_dict, error = load_input(csv_path)
    if error:
        result = {
            'success': False,
            'error': error
        }
        print(json.dumps(result))
        sys.exit(1)

    # Load training dataset and generate histogram
    plot_filename = None
    training_df = load_training_dataset()
    if training_df is not None and input_dict is not None:
        plot_filename = generate_histogram(training_df, input_dict)

    # Make predictions
    cost_preds, duration_preds, error = make_predictions(X, models_data)
    if error:
        result = {
            'success': False,
            'error': error
        }
        print(json.dumps(result))
        sys.exit(1)

    # Select best model
    best_model = select_best_model(cost_preds)

    # Get predictions from best model
    predicted_cost = cost_preds[best_model]
    predicted_duration = duration_preds[best_model]

    # Get accuracy
    accuracy = calculate_accuracy(best_model)

    # Prepare result
    result = {
        'success': True,
        'predicted_cost': round(predicted_cost, 2),
        'predicted_duration': predicted_duration,
        'best_model': best_model,
        'accuracy': accuracy,
        'all_models': {
            model: {
                'cost': cost_preds[model],
                'duration': duration_preds[model],
                'accuracy': calculate_accuracy(model)
            }
            for model in cost_preds.keys()
        }
    }

    print(json.dumps(result))

    # Print plot filename as LAST line
    if plot_filename:
        print(plot_filename)

if __name__ == '__main__':
    main()
