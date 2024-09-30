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
$table3 = 'map_depts'; // Mapping table for tags_0_id, tags_1_id, and tags_2_id to OrgLevel2Code
$primaryKey1 = 'externalIds_employeeID'; // Primary key column name in table1
$primaryKey2 = 'EmployeeNumber'; // Primary key column name in table2
$tagsCol0 = 'tags_0_id'; // tags_0_id column in samsara_drivers
$tagsCol1 = 'tags_1_id'; // tags_1_id column in samsara_drivers
$tagsCol2 = 'tags_2_id'; // tags_2_id column in samsara_drivers
$orgLevel2CodeCol = 'OrgLevel2Code'; // OrgLevel2Code column in ukg_csv

// Prepare the SQL statement for creating the view
$viewName = 'view_driver_orglevel2code_differences'; // Name of the new view
$sql = "CREATE OR REPLACE VIEW $viewName AS
        SELECT DISTINCT
            t1.$primaryKey1 AS samsara_employeeID,
            t2.$primaryKey2 AS ukg_employeeID,
            d.$orgLevel2CodeCol AS mapped_orgLevel2Code, 
            t2.$orgLevel2CodeCol AS ukg_orgLevel2Code
        FROM 
            $table1 t1
        LEFT JOIN 
            $table3 d ON (
                REPLACE(REPLACE(REPLACE(t1.$tagsCol0, ' ', ''), ',', ''), '-', '') = REPLACE(REPLACE(REPLACE(d.$tagsCol0, ' ', ''), ',', ''), '-', '') OR
                REPLACE(REPLACE(REPLACE(t1.$tagsCol1, ' ', ''), ',', ''), '-', '') = REPLACE(REPLACE(REPLACE(d.$tagsCol0, ' ', ''), ',', ''), '-', '') OR
                REPLACE(REPLACE(REPLACE(t1.$tagsCol2, ' ', ''), ',', ''), '-', '') = REPLACE(REPLACE(REPLACE(d.$tagsCol0, ' ', ''), ',', ''), '-', '')
            ) -- Map the tags_0_id, tags_1_id, and tags_2_id to OrgLevel2Code using map_depts
        JOIN 
            $table2 t2 ON t1.$primaryKey1 = t2.$primaryKey2 -- Join samsara_drivers and ukg_csv on primary keys
        WHERE 
            d.$orgLevel2CodeCol IS NOT NULL -- Only include rows where the mapped value exists in map_depts
            AND d.$orgLevel2CodeCol != t2.$orgLevel2CodeCol"; // Only include rows where the mapped value is different from ukg_csv

// Execute the view creation SQL
if (mysqli_query($conn, $sql)) {
    echo "View '$viewName' created successfully.";
} else {
    echo "Error creating view: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>
