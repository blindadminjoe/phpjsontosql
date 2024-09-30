<?php

// Set the script name variable to identify this script in the logs
$scriptName = 'vehicles_to_DB';

// Include the logger functions
require_once 'logger.php';

// Database connection details
require_once 'dbconnectconf.php';

// Load column configuration
$columnConfig = require 'columns_config.php';

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    logMessage("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

logMessage("Database connection established successfully.");

// Read JSON data from file
$jsonData = file_get_contents('vehicles.json');
if ($jsonData === false) {
    logMessage("Error reading JSON data from file.");
    die("Error reading JSON data from file.");
}

logMessage("JSON data read from file successfully.");

// Decode JSON data
$data = json_decode($jsonData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("Error decoding JSON: " . json_last_error_msg());
    die("Error decoding JSON: " . json_last_error_msg());
}

logMessage("JSON data decoded successfully.");

// Prepare column names and placeholders dynamically from config
$columnNames = array_column($columnConfig, 'name');
$placeholders = implode(', ', array_fill(0, count($columnNames), '?'));

// Build the SQL query
$sql = "INSERT INTO samsara_vehicles (" . implode(', ', $columnNames) . ") VALUES ($placeholders)
    ON DUPLICATE KEY UPDATE " . implode(', ', array_map(function($col) {
        return "$col = VALUES($col)";
    }, $columnNames));

$stmt = $conn->prepare($sql);
if (!$stmt) {
    logMessage("Prepare failed: " . $conn->error);
    die("Prepare failed: " . $conn->error);
}

logMessage("SQL statement prepared successfully.");

function bindParamsFromConfig($stmt, $vehicle, $columnConfig) {
    $params = [];
    $types = '';

    foreach ($columnConfig as $config) {
        $name = $config['name'];
        $type = $config['type'];
        $value = $vehicle[$name] ?? null;

        // Handle DateTime conversion for specific fields
        if (in_array($name, ['createdAtTime', 'updatedAtTime']) && $value !== null) {
            // Convert ISO 8601 date (e.g., '2021-04-08T12:10:30Z') to MySQL format ('Y-m-d H:i:s')
            try {
                $dateTime = new DateTime($value);
                $value = $dateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                logMessage("Date conversion error for field $name: " . $e->getMessage());
                $value = null; // Set to null if conversion fails
            }
        }

        $params[] = $value;
        $types .= $type;
    }

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
}

// Loop through the data and insert/update the database
foreach ($data['data'] as $vehicle) {
    bindParamsFromConfig($stmt, $vehicle, $columnConfig);

    if (!$stmt->execute()) {
        logMessage("Execute failed for record: " . json_encode($vehicle) . " Error: " . $stmt->error);
    } else {
        logMessage("Record successfully inserted/updated: " . json_encode($vehicle));
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

logMessage("Database updated successfully and connection closed.");
echo(date('Y-m-d H:i:s') . " - Database updated successfully and connection closed.");
