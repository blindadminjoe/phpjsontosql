<?php
// Database connection details
require_once 'dbconnectconf.php';

// Log directory and log file with today's date
$logDir = '/var/log/joelogs/new_vehicles';
$logFile = $logDir . '/new_' . date('Y-m-d') . '.log'; // Creates a log file named with today's date

// Function to ensure the log directory exists
function ensureLogDirExists($logDir) {
    // Check if the log directory exists; if not, create it with appropriate permissions
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true); // Creates directory recursively with permissions
    }
}

// Function to log messages to the file only
function logMessage($message) {
    global $logFile;
    // Append the message with a timestamp to the log file
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Custom exception handler function
function customExceptionHandler($exception) {
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    file_put_contents('/var/log/joelogs/new_vehicles/error.log', date('Y-m-d H:i:s') . " - " . $errorMessage . PHP_EOL, FILE_APPEND);
}

// Set custom exception handler
set_exception_handler("customExceptionHandler");


// Ensure the log directory exists before proceeding
ensureLogDirExists($logDir);

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    logMessage("Connection failed: " . $conn->connect_error);
    die();
}

// Get the previous date (one day before the current date)
$previousDate = date('Y-m-d', strtotime('-1 day')); // Format: 'YYYY-MM-DD'
logMessage("Checking for updates on: $previousDate");

// Query to fetch rows updated on the previous day based on the 'created_at_time' column
$sql = "SELECT * FROM vehicles_from_samsara WHERE DATE(created_at_time) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $previousDate);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are new rows
if ($result->num_rows > 0) {
    logMessage("New updates found for the previous day.");

    // Prepare the insert query for vehicle_changes table
    $insertSql = "INSERT INTO vehicle_changes (
        externalIds_samsara_serial, externalIds_samsara_vin, gateway_serial, gateway_model, harshaccelerationsettingtype, id, licenseplate, make, model, name, notes, serial, staticassigneddriver_id, staticassigneddriver_name, tags_0_id, tags_0_name, tags_0_parentTagId, vin, year, vehicleregulationmode, created_at_time, updated_at_time, esn
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare statement for inserting into vehicle_changes
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        logMessage("Error preparing insert statement: " . $conn->error);
        die();
    }

    // Fetch and insert each new row into the vehicle_changes table
    while ($row = $result->fetch_assoc()) {
        // Bind parameters and execute the statement
        $insertStmt->bind_param(
            "sssssissssssssssissssss",
            $row['externalIds_samsara_serial'],
            $row['externalIds_samsara_vin'],
            $row['gateway_serial'],
            $row['gateway_model'],
            $row['harshAccelerationSettingType'],
            $row['id'],
            $row['licensePlate'],
            $row['make'],
            $row['model'],
            $row['name'],
            $row['notes'],
            $row['serial'],
            $row['staticAssignedDriver_id'],
            $row['staticAssignedDriver_name'],
            $row['tags_0_id'],
            $row['tags_0_name'],
            $row['tags_0_parentTagId'],
            $row['vin'],
            $row['year'],
            $row['vehicleRegulationMode'],
            $row['created_at_time'],
            $row['updated_at_time'],
            $row['esn']
        );

        // Execute the insert statement
        if (!$insertStmt->execute()) {
            logMessage("Error inserting row into vehicle_changes: " . $insertStmt->error);
        } else {
            logMessage("Inserted row into vehicle_changes for vehicle: " . $row['name']);
        }
    }

    // Close the insert statement
    $insertStmt->close();
} else {
    logMessage("No new updates found for the previous day.");
}

// Close the database connection
$stmt->close();
$conn->close();
logMessage("Database connection closed.");
?>
