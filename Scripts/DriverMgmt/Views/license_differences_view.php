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
$primaryKey1 = 'externalIds_employeeID'; // Primary key column name in table1
$primaryKey2 = 'EmployeeNumber'; // Primary key column name in table2
$licenseCol1 = 'licenseNumber'; // Driver's license column in table1
$licenseCol2 = 'Number'; // Driver's license column in table2
$typeCodeCol2 = 'TypeCode'; // TypeCode column in table2

// Define the valid TypeCodes for comparison
$validTypeCodes = "'CDL', 'CDLB', 'CDLC', 'CHAUFL', 'CLPA', 'DL'";

// Prepare the SQL statement for creating the view
$viewName = 'view_driver_license_differences'; // Name of the view
$sql = "CREATE OR REPLACE VIEW $viewName AS
        SELECT 
            t1.$primaryKey1,
            t2.$primaryKey2,
            t1.$licenseCol1 AS license_samsara,
            t2.$licenseCol2 AS license_ukg
        FROM 
            $table1 t1
        JOIN 
            $table2 t2 ON t1.$primaryKey1 = t2.$primaryKey2
        WHERE 
            t1.$licenseCol1 != t2.$licenseCol2 AND  -- Only include differences
            t2.$typeCodeCol2 IN ($validTypeCodes)"; // Apply TypeCode condition to table2

// Execute the view creation SQL
if (mysqli_query($conn, $sql)) {
    echo "View '$viewName' created successfully.";
} else {
    echo "Error creating view: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>
