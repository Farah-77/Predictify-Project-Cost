<?php
/**
 * PHP-Python Integration Layer
 * Handles communication between PHP and Python scripts
 */

/**
 * Train ML models using Python script
 *
 * @param string $datasetPath Path to training CSV file
 * @return array Result with success status and data
 */
function trainModels($datasetPath) {
    // Convert relative paths to absolute paths
    $projectRoot = BASE_PATH;

    // Handle both absolute and relative paths for dataset
    if (!file_exists($datasetPath)) {
        $datasetPath = $projectRoot . DIRECTORY_SEPARATOR . $datasetPath;
    }

    // Build absolute path to training script
    $scriptPath = $projectRoot . DIRECTORY_SEPARATOR . TRAIN_SCRIPT;

    // Verify files exist
    if (!file_exists($scriptPath)) {
        return [
            'success' => false,
            'error' => 'Training script not found at: ' . $scriptPath
        ];
    }

    if (!file_exists($datasetPath)) {
        return [
            'success' => false,
            'error' => 'Dataset file not found at: ' . $datasetPath
        ];
    }

    // Escape paths for shell
    $escapedPath = escapeshellarg($datasetPath);
    $escapedScript = escapeshellarg($scriptPath);
    $escapedProjectRoot = escapeshellarg($projectRoot);

    // Build command with working directory change
    // Use 'cd' to change to project root, then run the command
    $command = "cd $escapedProjectRoot && " . PYTHON_EXECUTABLE . " $escapedScript $escapedPath 2>&1";

    // Execute command
    $output = shell_exec($command);

    // Parse JSON output
    if ($output) {
        // Split output into lines
        $lines = explode("\n", trim($output));

        // The last non-empty line should be the heatmap filename
        $heatmapFilename = null;
        $jsonOutput = '';

        if (count($lines) > 1) {
            // Last line is heatmap filename
            $lastLine = trim($lines[count($lines) - 1]);
            if (!empty($lastLine) && strpos($lastLine, '.png') !== false) {
                $heatmapFilename = $lastLine;
                // Join all other lines as JSON
                $jsonOutput = implode("\n", array_slice($lines, 0, -1));
            } else {
                $jsonOutput = $output;
            }
        } else {
            $jsonOutput = $output;
        }

        $result = json_decode($jsonOutput, true);
        if ($result && isset($result['success'])) {
            // Add heatmap filename to result
            if ($heatmapFilename) {
                $result['heatmap_image'] = $heatmapFilename;
            }
            return $result;
        }
    }

    return [
        'success' => false,
        'error' => 'Failed to execute training script or parse output',
        'raw_output' => $output
    ];
}

/**
 * Make prediction using Python script
 *
 * @param array $projectData Project input data
 * @return array Result with prediction data
 */
function makePrediction($projectData) {
    // Convert relative paths to absolute paths
    $projectRoot = BASE_PATH;

    // Create temporary CSV file
    $tempFile = tempnam(sys_get_temp_dir(), 'predict_');
    $csvFile = $tempFile . '.csv';
    rename($tempFile, $csvFile);

    // Write data to CSV
    $fp = fopen($csvFile, 'w');

    // Write header
    fputcsv($fp, [
        'Project Size',
        'Team Members',
        'Equipment Count',
        'Material Cost',
        'Complexity Level'
    ]);

    // Write data
    fputcsv($fp, [
        $projectData['project_size'],
        $projectData['team_members'],
        $projectData['equipment_count'],
        $projectData['material_cost'],
        $projectData['complexity_level']
    ]);

    fclose($fp);

    // Build absolute path to prediction script
    $scriptPath = $projectRoot . DIRECTORY_SEPARATOR . PREDICT_SCRIPT;

    // Verify script exists
    if (!file_exists($scriptPath)) {
        @unlink($csvFile);
        return [
            'success' => false,
            'error' => 'Prediction script not found at: ' . $scriptPath
        ];
    }

    // Escape paths
    $escapedCsvPath = escapeshellarg($csvFile);
    $escapedScript = escapeshellarg($scriptPath);
    $escapedProjectRoot = escapeshellarg($projectRoot);

    // Build command with working directory change
    $command = "cd $escapedProjectRoot && " . PYTHON_EXECUTABLE . " $escapedScript $escapedCsvPath 2>&1";

    // Execute command
    $output = shell_exec($command);

    // Clean up temp file
    @unlink($csvFile);

    // Parse JSON output
    if ($output) {
        // Split output into lines
        $lines = explode("\n", trim($output));

        // The last non-empty line should be the plot filename
        $plotFilename = null;
        $jsonOutput = '';

        if (count($lines) > 1) {
            // Last line is plot filename
            $lastLine = trim($lines[count($lines) - 1]);
            if (!empty($lastLine) && strpos($lastLine, '.png') !== false) {
                $plotFilename = $lastLine;
                // Join all other lines as JSON
                $jsonOutput = implode("\n", array_slice($lines, 0, -1));
            } else {
                $jsonOutput = $output;
            }
        } else {
            $jsonOutput = $output;
        }

        $result = json_decode($jsonOutput, true);
        if ($result && isset($result['success'])) {
            // Add plot filename to result
            if ($plotFilename) {
                $result['plot_image'] = $plotFilename;
            }
            return $result;
        }
    }

    return [
        'success' => false,
        'error' => 'Failed to execute prediction script or parse output',
        'raw_output' => $output
    ];
}

?>
