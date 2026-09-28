"""
Train Machine Learning Models
Predictify - ML-Based Project Prediction System

This script trains three regression models:
1. Linear Regression
2. Random Forest Regressor
3. Gradient Boosting Regressor

"""

import sys
import json
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.metrics import r2_score, mean_absolute_error, mean_squared_error
import pickle
import os
import warnings
warnings.filterwarnings('ignore')

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import seaborn as sns

def load_data(csv_path):
    """Load and preprocess training data"""
    try:
        df = pd.read_csv(csv_path)

        # Expected columns
        required_columns = [
            'Project Size', 'Team Members', 'Equipment Count',
            'Material Cost', 'Complexity Level', 'Duration', 'Budget'
        ]

        # Check if all required columns exist
        missing_cols = [col for col in required_columns if col not in df.columns]
        if missing_cols:
            return None, f"Missing columns: {', '.join(missing_cols)}"

        # Remove rows with missing values
        df = df.dropna()

        if len(df) < 10:
            return None, "Insufficient data: minimum 10 records required"

        return df, None
    except Exception as e:
        return None, f"Error loading data: {str(e)}"

def generate_correlation_heatmap(df):
    """Generate and save correlation heatmap"""
    try:
        # Create plots directory if it doesn't exist
        plots_dir = os.path.join(os.path.dirname(__file__), '..', 'assets', 'uploads', 'plots')
        os.makedirs(plots_dir, exist_ok=True)

        # Select numeric columns for correlation
        numeric_cols = ['Project Size', 'Team Members', 'Equipment Count',
                       'Material Cost', 'Complexity Level', 'Duration', 'Budget']

        # Calculate correlation matrix
        corr_matrix = df[numeric_cols].corr()

        # Create figure
        plt.figure(figsize=(10, 8))
        sns.heatmap(corr_matrix, annot=True, fmt='.2f', cmap='coolwarm',
                   center=0, square=True, linewidths=1, cbar_kws={"shrink": 0.8})
        plt.title('Feature Correlation Heatmap', fontsize=14, fontweight='bold')
        plt.tight_layout()

        # Save plot
        plot_path = os.path.join(plots_dir, 'training_heatmap.png')
        plt.savefig(plot_path, dpi=100, bbox_inches='tight')
        plt.close()

        return 'training_heatmap.png'
    except Exception:
        return None

def prepare_features(df):
    """Prepare features and target variables"""
    # Feature columns
    feature_cols = [
        'Project Size', 'Team Members', 'Equipment Count',
        'Material Cost', 'Complexity Level'
    ]

    X = df[feature_cols].values

    # Target variables (we'll predict both cost and duration)
    y_cost = df['Budget'].values
    y_duration = df['Duration'].values

    return X, y_cost, y_duration

def train_model(X_train, X_test, y_train, y_test, model, model_name):
    """Train a model and return metrics"""
    try:
        # Train the model
        model.fit(X_train, y_train)

        # Make predictions
        y_pred = model.predict(X_test)

        # Calculate metrics
        r2 = r2_score(y_test, y_pred)
        mae = mean_absolute_error(y_test, y_pred)
        rmse = np.sqrt(mean_squared_error(y_test, y_pred))

        return {
            'r2_score': float(r2),
            'mae': float(mae),
            'rmse': float(rmse)
        }
    except Exception as e:
        return None

def main():
    """Main training function"""
    # Check command line arguments
    if len(sys.argv) < 2:
        result = {
            'success': False,
            'error': 'Usage: python train_models.py <path_to_training_csv>'
        }
        print(json.dumps(result))
        sys.exit(1)

    csv_path = sys.argv[1]

    # Load data
    df, error = load_data(csv_path)
    if error:
        result = {
            'success': False,
            'error': error
        }
        print(json.dumps(result))
        sys.exit(1)

    # Generate correlation heatmap BEFORE training
    heatmap_filename = generate_correlation_heatmap(df)

    # Prepare features
    X, y_cost, y_duration = prepare_features(df)

    # Split data
    X_train, X_test, y_cost_train, y_cost_test = train_test_split(
        X, y_cost, test_size=0.2, random_state=42
    )
    _, _, y_dur_train, y_dur_test = train_test_split(
        X, y_duration, test_size=0.2, random_state=42
    )

    # Scale features
    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)

    # Define models
    models = {
        'LinearRegression': LinearRegression(),
        'RandomForest': RandomForestRegressor(n_estimators=100, random_state=42),
        'GradientBoosting': GradientBoostingRegressor(n_estimators=100, random_state=42)
    }

    results = {}
    best_model_name = None
    best_score = -1

    # Create saved_models directory if it doesn't exist
    models_dir = os.path.join(os.path.dirname(__file__), 'saved_models')
    os.makedirs(models_dir, exist_ok=True)

    # Train each model for cost prediction
    for name, model in models.items():
        metrics = train_model(X_train_scaled, X_test_scaled, y_cost_train, y_cost_test, model, name)

        if metrics:
            results[name] = metrics

            # Save the trained model
            model_filename = os.path.join(models_dir, f'{name}_cost.pkl')
            with open(model_filename, 'wb') as f:
                pickle.dump(model, f)

            # Track best model
            if metrics['r2_score'] > best_score:
                best_score = metrics['r2_score']
                best_model_name = name

    # Train duration models
    for name, model_class in [
        ('LinearRegression', LinearRegression()),
        ('RandomForest', RandomForestRegressor(n_estimators=100, random_state=42)),
        ('GradientBoosting', GradientBoostingRegressor(n_estimators=100, random_state=42))
    ]:
        model_class.fit(X_train_scaled, y_dur_train)

        # Save the trained model
        model_filename = os.path.join(models_dir, f'{name}_duration.pkl')
        with open(model_filename, 'wb') as f:
            pickle.dump(model_class, f)

    # Save the scaler
    scaler_filename = os.path.join(models_dir, 'scaler.pkl')
    with open(scaler_filename, 'wb') as f:
        pickle.dump(scaler, f)

    # Return results
    result = {
        'success': True,
        'models': results,
        'best_model': best_model_name,
        'best_score': float(best_score),
        'training_records': len(df)
    }

    print(json.dumps(result))

    # Print heatmap filename as LAST line
    if heatmap_filename:
        print(heatmap_filename)

if __name__ == '__main__':
    main()
