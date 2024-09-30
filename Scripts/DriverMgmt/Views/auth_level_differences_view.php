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
$table3 = 'map_attributes'; // Mapping table
$primaryKey1 = 'externalIds_employeeID'; // Primary key column name in table1
$primaryKey2 = 'EmployeeNumber'; // Primary key column name in table2
$attributeCol1 = 'attribute_Driver_Authorization_Level_stringValues_0'; // Attribute column in samsara_drivers
$typeCodeCol2 = 'TypeCode'; // TypeCode column in ukg_csv
$typeCodeMapCol = 'typeCode'; // typeCode column in map_attributes (to compare with samsara_drivers)
$mappedValueCol = 'typeDescription'; // typeDescription column in map_attributes (to compare with ukg_csv)

// Define the valid TypeCodes for comparison
$validTypeCodes = "'AU10K', 'AU1026', 'AU26', 'AU21', 'UNAUTH'";

// Prepare the SQL statement for creating the view
$viewName = 'view_driver_authorization_differences'; // Name of the view
$sql = "CREATE OR REPLACE VIEW $viewName AS
        SELECT 
            t1.$primaryKey1 AS samsara_employeeID,
            t2.$primaryKey2 AS ukg_employeeID,
            m.$typeCodeMapCol AS mapped_typeCode_samsara,
            CASE 
                WHEN t1.$attributeCol1 IS NULL AND t2.$typeCodeCol2 NOT IN ($validTypeCodes) THEN NULL
                ELSE t2.$typeCodeCol2
            END AS ukg_typeCode
        FROM 
            $table1 t1
        LEFT JOIN 
            $table3 m ON REPLACE(REPLACE(REPLACE(t1.$attributeCol1, ' ', ''), ',', ''), '-', '') = REPLACE(REPLACE(REPLACE(m.$mappedValueCol, ' ', ''), ',', ''), '-', '') -- Map the authorization level from samsara_drivers to map_attributes using typeDescription
        JOIN 
            $table2 t2 ON t1.$primaryKey1 = t2.$primaryKey2 -- Join samsara_drivers and ukg_csv on primary keys
        WHERE 
            (m.$typeCodeMapCol IS NULL OR m.$typeCodeMapCol != t2.$typeCodeCol2) -- Include rows where the mapped value is NULL or different
            AND t2.$typeCodeCol2 IN ($validTypeCodes) -- Only include if TypeCode in ukg_csv is in the valid set
            OR t1.$attributeCol1 IS NULL"; // Include rows where the attribute in samsara is NULL

// Execute the view creation SQL
if (mysqli_query($conn, $sql)) {
    echo "View '$viewName' created successfully.";
} else {
    echo "Error creating view: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>
