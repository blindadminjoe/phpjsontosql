<?php
// Database connection details
require_once '/home/joe/drivermgmt/dbconnectconf.php';

// Connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Define the tables and columns
$table1 = 'samsara_drivers'; // Your first table name
$table2 = 'ukg_csv'; // Your second table name
$primaryKey1 = 'externalIds_employeeID'; // Primary key column name in samsara_drivers
$primaryKey2 = 'EmployeeNumber'; // Primary key column name in ukg_csv

// Prepare the SQL statement for creating the view
$viewName = 'deactivate_list'; // Name of the new view
$sql = "CREATE OR REPLACE VIEW $viewName AS
        SELECT t1.* -- Select all columns from samsara_drivers
        FROM $table1 t1
        LEFT JOIN $table2 t2
        ON t1.$primaryKey1 = t2.$primaryKey2
        WHERE t2.$primaryKey2 IS NULL"; // Only include rows where there is no match in ukg_csv

// Execute the view creation SQL
if (mysqli_query($conn, $sql)) {
    echo "View '$viewName' created successfully.";
} else {
    echo "Error creating view: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>