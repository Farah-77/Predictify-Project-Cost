# Predictify

**ML-Based Project Cost and Duration Prediction System**

Predictify is a web application that predicts the cost and duration of construction-style projects from historical data. The web interface is built with PHP and MySQL, and the machine learning part is written in Python (scikit-learn). Users enter project details and get predictions from three regression models side by side.

> Developed individually by **Farah Alshammari** during her COOP (cooperative training) period at Saudi Aramco, as the technical project of the COOP program, Department of AI & Data Science, University of Hail. This is an independent educational project, not an official Aramco product, and it uses only publicly available data. Covers system analysis, database design, PHP backend and UI, the ML pipeline, and PHP-Python integration. Built over 6 weeks using the Waterfall methodology.

---

## Features

**Users**
- Predict project cost and duration
- Compare results across three ML models
- Charts and visual analytics (Chart.js)
- Project history tracking
- CSV batch upload

**Admins**
- Dashboard with system statistics
- User and project management
- Upload training datasets
- Retrain models from the browser

---

## Technology Stack

| Layer | Technologies |
|---|---|
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5, Chart.js |
| Backend | PHP 7.4+, MySQL 5.7+ |
| Machine Learning | Python 3.8+, scikit-learn, pandas, numpy |
| Server | XAMPP / MAMP / WAMP / Laragon (Apache) |

---

## Installation

### 1. Get the project

```bash
git clone https://github.com/Farah-77/Predictify-Project-Cost.git
```

Place the folder in your web server directory:
- Windows (XAMPP): `C:\xampp\htdocs\Predictify`
- macOS (XAMPP): `/Applications/XAMPP/htdocs/Predictify`
- macOS (MAMP): `/Applications/MAMP/htdocs/Predictify`

### 2. Set up the database

1. Start Apache and MySQL.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`).
3. Create a database named `predictify_db`.
4. Import `database.sql` into it.

### 3. Configure the connection

Edit `config/db_config.php` if your MySQL credentials differ from the defaults (`root` with an empty password):

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'predictify_db');
```

### 4. Set up Python

```bash
cd Predictify
python -m venv venv
source venv/bin/activate        # macOS / Linux
venv\Scripts\activate           # Windows
pip install -r requirements.txt
```

### 5. Point PHP to Python

In `config/settings.php`, set `PYTHON_EXECUTABLE`:

```php
// Global Python:
define('PYTHON_EXECUTABLE', 'python');

// Or the virtual environment (adjust the path):
define('PYTHON_EXECUTABLE', '/Applications/XAMPP/htdocs/Predictify/venv/bin/python');
```

### 6. Permissions (macOS / Linux)

```bash
chmod -R 755 models/ assets/uploads/
```

Required folders (`models/datasets/`, `models/saved_models/`, `assets/uploads/`) are created automatically on first run.

Then open `http://localhost/Predictify`.

---

## Default Admin Account

The database import creates a default admin account for **local testing only**. Its credentials are in `database.sql`. Change the password after your first login and never deploy with the default account.

---

## Usage

### Users
1. Register and log in.
2. Click **New Prediction** and enter: project name, size (m²), team members, equipment count, material cost ($), and complexity level (1-5).
3. View the predicted cost and duration, model comparison, and charts.
4. Find past predictions under **My Projects**.

### Admins
1. Log in as admin.
2. **Upload Training Data** with a CSV in the format below.
3. **Retrain Models** using the uploaded dataset.
4. Monitor users, projects, and model performance from the dashboard.

---

## CSV Format

### Training data

```csv
Project Size,Team Members,Equipment Count,Material Cost,Complexity Level,Duration,Budget
1500.50,10,5,50000.00,3,120,890000.00
2000.00,15,8,75000.00,4,180,1200000.00
```

| Column | Description |
|---|---|
| Project Size | Area in square meters |
| Team Members | Number of workers |
| Equipment Count | Number of major equipment pieces |
| Material Cost | Materials cost in dollars |
| Complexity Level | 1-5 |
| Duration | Project duration in days (target) |
| Budget | Total cost in dollars (target) |

A sample is included at `models/datasets/sample_training_data.csv`.

### Batch prediction input

```csv
Project Name,Project Size,Team Members,Equipment Count,Material Cost,Complexity Level
"Office Building",1500,10,5,50000,3
```

Guidelines: comma delimiter, include a header row, plain numbers only (no currency symbols), no empty cells, UTF-8 encoding, max file size 5 MB.

---

## Machine Learning

Two targets are predicted (cost and duration), each with three models:

- **Linear Regression**: fast, interpretable baseline
- **Random Forest**: handles non-linear relationships
- **Gradient Boosting**: strongest on complex patterns, needs more data

Pipeline: 80/20 train/test split, feature scaling with `StandardScaler`, evaluation with R², MAE, and RMSE. Trained models are stored as `.pkl` files in `models/saved_models/`. A minimum of 10 records is required to train (100+ recommended).

### Data

The models were trained on a structured tabular dataset of historical construction/project records from an open-source repository (Kaggle-style public data), processed with pandas and numpy.

- **Features**: Project Size, Team Members, Equipment Count, Material Cost, Complexity Level
- **Targets**: Budget (cost) and Duration
- **Preprocessing**: schema check, removal of missing/invalid rows, 80/20 train/test split, `StandardScaler`
- **Analysis**: a correlation heatmap was generated before training to support feature selection

This repository includes only a small sample (`models/datasets/sample_training_data.csv`), not the full dataset.

### Results

R² scores from the initial training run (Initial Training Dataset, Nov 21, 2025):

| Model | R² Score |
|---|---|
| Linear Regression | 87.34% |
| Random Forest Regressor | 91.21% |
| Gradient Boosting Regressor | 92.87% |

Gradient Boosting achieved the best score, followed by Random Forest. The system evaluates R², MAE, and RMSE during training and automatically selects the best-performing model.

The `.pkl` files in `models/saved_models/` are for demonstration. For meaningful predictions, retrain on a larger dataset from the Admin panel.

---

## Project Structure

```
Predictify/
├── index.php            # Landing page
├── database.sql         # Database schema
├── requirements.txt     # Python dependencies
├── config/              # DB connection and app settings
├── includes/            # Shared header, footer, navbar, auth guard
├── auth/                # Login, register, logout
├── user/                # Prediction form, results, project history
├── admin/               # Dashboard, users, projects, training, PHP-Python bridge
├── models/              # train_models.py, predict.py, datasets, saved models
└── assets/              # CSS, JS, images, uploads
```

---

## Security Notes

- Database queries go through prepared statements (`executePreparedQuery` in `config/db_config.php`).
- User input is sanitized with `htmlspecialchars`.
- Uploads are restricted to `.csv` files up to 5 MB.
- Not yet implemented: CSRF tokens and rate limiting.
- `display_errors` is enabled in `config/settings.php` for development. Disable it in production.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| Database connection error | Make sure MySQL is running and `db_config.php` matches your credentials |
| `python` not found | Install Python and update `PYTHON_EXECUTABLE` in `settings.php` |
| `No module named 'sklearn'` | Run `pip install -r requirements.txt` (activate the venv first) |
| Models not found | Go to Admin, Retrain Models, and train with a dataset |
| Upload fails | Check folder permissions, file size (under 5 MB), and CSV format |
| Charts not showing | Chart.js loads from a CDN, so check your internet connection |

---

## Future Work

- Additional models (XGBoost, neural networks)
- Larger and more diverse training data
- Prediction REST API
- More analytics (cost comparison, duration distribution, feature importance)
- Cloud deployment (currently runs locally)
- CSRF protection

---

## License

Developed for educational purposes as a COOP project at the University of Hail.
