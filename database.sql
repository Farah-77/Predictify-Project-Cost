
-- Create database
DROP DATABASE IF EXISTS predictify_db;
CREATE DATABASE IF NOT EXISTS predictify_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE predictify_db;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: projects
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_name VARCHAR(200) NOT NULL,
    project_size DECIMAL(10,2) NOT NULL COMMENT 'Project size in square meters',
    team_members INT NOT NULL,
    equipment_count INT NOT NULL,
    material_cost DECIMAL(12,2) NOT NULL,
    complexity_level INT NOT NULL COMMENT '1-5 scale',
    predicted_cost DECIMAL(12,2) DEFAULT NULL,
    predicted_duration INT DEFAULT NULL COMMENT 'Duration in days',
    best_model VARCHAR(50) DEFAULT NULL,
    accuracy DECIMAL(5,4) DEFAULT NULL COMMENT 'R² score',
    plot_image VARCHAR(255) DEFAULT NULL COMMENT 'ML visualization plot filename',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: training_data
CREATE TABLE IF NOT EXISTS training_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dataset_name VARCHAR(200) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT NOT NULL,
    record_count INT DEFAULT 0,
    status ENUM('active', 'archived') DEFAULT 'active',
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: model_performance
CREATE TABLE IF NOT EXISTS model_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_name VARCHAR(50) NOT NULL,
    r2_score DECIMAL(5,4) NOT NULL,
    mae DECIMAL(10,2) DEFAULT NULL COMMENT 'Mean Absolute Error',
    rmse DECIMAL(10,2) DEFAULT NULL COMMENT 'Root Mean Squared Error',
    training_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dataset_id INT DEFAULT NULL,
    FOREIGN KEY (dataset_id) REFERENCES training_data(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Insert Sample Data

-- Default Users with Password: password123
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'Admin@predictify.com', '$2y$10$60asGJMIU6JZ9z8W4G7KS.5buG/WK5M1yY60k.fOk9zw2wN2EU4kq', 'admin'),
('User', 'user@predictify.com', '$2y$10$60asGJMIU6JZ9z8W4G7KS.5buG/WK5M1yY60k.fOk9zw2wN2EU4kq', 'user');

-- Training Data Samples
INSERT INTO training_data (dataset_name, file_path, uploaded_by, record_count, status) VALUES
('Initial Training Dataset', 'models/datasets/initial_training.csv', 1, 1200, 'active'),
('Old Archived Dataset', 'models/datasets/archive_2023.csv', 1, 950, 'archived');

-- Model Performance Samples
INSERT INTO model_performance (model_name, r2_score, mae, rmse, dataset_id) VALUES
('LinearRegression', 0.8734, 24500.55, 30210.88, 1),
('RandomForestRegressor', 0.9121, 20100.32, 25670.22, 1),
('XGBoost', 0.9287, 18900.10, 23050.44, 1);

-- Projects Samples
INSERT INTO projects (user_id, project_name, project_size, team_members, equipment_count, material_cost, complexity_level, predicted_cost, predicted_duration, best_model, accuracy) VALUES
(2, 'Residential Complex Phase 1', 12500.50, 30, 15, 850000.00, 4, 1210000.00, 180, 'RandomForestRegressor', 0.9121),
(2, 'Shopping Mall Construction', 22000.75, 50, 25, 1750000.00, 5, 2350000.00, 270, 'XGBoost', 0.9287),
(2, 'Warehouse Extension', 8000.00, 18, 10, 520000.00, 3, 690000.00, 120, 'LinearRegression', 0.8734);
