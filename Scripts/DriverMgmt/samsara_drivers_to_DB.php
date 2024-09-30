<?php
// Load configuration from external file
$config = require 'driver_column_conf.php';

// Database connection details
require_once 'dbconnectconf.php';

// Set the script name variable to identify this script in the logs
$scriptName = 'drivers_to_DB'; // Adjust this to your specific script name

// Include the logger functions
require_once 'logger.php';

// Connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    logMessage("Connection failed: " . mysqli_connect_error(), 'error');
    die("Connection failed: " . mysqli_connect_error());
}
logMessage("Database connection successful.", 'info');

// Read the JSON file
$jsonFile = 'drivers.json';
$jsonData = file_get_contents($jsonFile);
if ($jsonData === false) {
    logMessage("Failed to read JSON file.", 'error');
    die("Failed to read JSON file.");
}
logMessage("JSON file read successfully.", 'info');

$dataArray = json_decode($jsonData, true);

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    logMessage("Error decoding JSON: " . json_last_error_msg(), 'error');
    die("Error decoding JSON: " . json_last_error_msg());
}
logMessage("JSON decoded successfully.", 'info');

// Extract data from the nested "data" key
if (!isset($dataArray['data']) || !is_array($dataArray['data'])) {
    logMessage("Invalid data format in JSON file.", 'error');
    die("Invalid data format in JSON file.");
}
$dataArray = $dataArray['data']; // Now $dataArray is an array of objects
logMessage("Data extracted from JSON file.", 'info');

// Remove all values from the samsara_drivers table
$deleteSql = "DELETE FROM samsara_drivers";
if (!$conn->query($deleteSql)) {
    logMessage("Error deleting existing records: " . $conn->error, 'error');
    die("Error deleting existing records: " . $conn->error);
}
logMessage("All existing records deleted from samsara_drivers.", 'info');

// Prepare the SQL statement for updating data
$columns = array_column($config, 'name');
$updateSets = [];
$placeholders = [];
$types = '';
foreach ($config as $column) {
    $colName = $column['name'];
    $colType = $column['type'];
    $updateSets[] = "$colName = VALUES($colName)";
    $placeholders[] = $colName;
    $types .= ($colType == 'i') ? 'i' : 's'; // 'i' for integer, 's' for string
}
$updateSets = implode(', ', $updateSets);

$sql = "INSERT INTO samsara_drivers (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ") ON DUPLICATE KEY UPDATE $updateSets";
$stmt = $conn->prepare($sql);

// Check if the statement was prepared correctly
if (!$stmt) {
    logMessage("Prepare failed: " . $conn->error, 'error');
    die("Prepare failed: " . $conn->error);
}
logMessage("Prepared statement successfully.", 'info');

// Iterate over the data array
foreach ($dataArray as $row) {
    // Map JSON data to variables, filtering by the column config
    $params = [];
    foreach ($columns as $col) {
        // Only include values that exist in the JSON and match the config
        $value = isset($row[$col]) ? $row[$col] : null;

        // Convert datetime format if needed
        if (in_array($col, ['updatedAtTime', 'createdAtTime']) && $value) {
            // Convert from '2024-06-21T13:50:31.47816Z' to '2024-06-21 13:50:31'
            $value = date('Y-m-d H:i:s', strtotime($value));
        }

        $params[] = $value;
    }

    // Check if the number of parameters matches the number of placeholders
    if (count($params) !== count($columns)) {
        logMessage("Mismatch between number of columns and data values.", 'error');
        die("Mismatch between number of columns and data values.");
    }

    // Bind parameters to the SQL statement
    $stmt->bind_param($types, ...$params);

    // Execute the prepared statement
    if (!$stmt->execute()) {
        logMessage("Execute failed: " . $stmt->error, 'error');
        die("Execute failed: " . $stmt->error);
    }
}

// Close the statement and database connection
$stmt->close();
$conn->close();
logMessage("Statement and connection closed.", 'info');

echo "Data updated successfully.";
?>
