<?php
// Database connection details
require_once 'dbconnectconf.php';

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    logMessage("Connection failed: " . $conn->connect_error);
    die();
}

// Set the script name variable to identify this script in the logs
$scriptName = 'new_vehicles_added'; // Adjust this to your specific script name

// Include the logger functions
require_once 'logger.php';

// Get the previous date (one day before the current date)
$previousDate = date('Y-m-d', strtotime('-1 day'));
logMessage("Checking for updates on: $previousDate");

// Query to fetch rows updated on the previous day based on the 'created_at_time' column
$sql = "SELECT * FROM vehicles_from_samsara WHERE DATE(created_at_time) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $previousDate);
$stmt->execute();
$result = $stmt->get_result();

// Load column configuration
$columns = require 'columns_config.php';
$columnNames = array_column($columns, 'name');
$placeholders = implode(', ', array_fill(0, count($columnNames), '?'));
$columnList = implode(', ', $columnNames);

if ($result->num_rows > 0) {
    logMessage("New updates found for the previous day.");

    // Build the insert query dynamically
    $insertSql = "INSERT INTO vehicle_changes ($columnList) VALUES ($placeholders)";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        logMessage("Error preparing insert statement: " . $conn->error);
        die();
    }

    while ($row = $result->fetch_assoc()) {
        $bindParams = [];
        $bindTypes = '';

        // Prepare bind parameters and types based on the configuration
        foreach ($columns as $column) {
            $bindParams[] = $row[$column['name']] ?? null; // Use null if the column doesn't exist in the row
            $bindTypes .= $column['type'];
        }

        // Bind parameters dynamically using array unpacking
        $insertStmt->bind_param($bindTypes, ...$bindParams);

        // Execute the insert statement
        if (!$insertStmt->execute()) {
            logMessage("Error inserting row into vehicle_changes: " . $insertStmt->error);
        } else {
            logMessage("Inserted row into vehicle_changes for vehicle: " . $row['name']);
        }
    }

    $insertStmt->close();
} else {
    logMessage("No new updates found for the previous day.");
}

// Close the database connection
$stmt->close();
$conn->close();
logMessage("Database connection closed.");
?>
