<?php

// Set the script name variable to identify this script in the logs
$scriptName = 'ukg_csv_to_DB'; // Adjust this to your specific script name

// Include the logger functions (assuming you want to log steps)
require_once 'logger.php';

logMessage("Script started.", 'info');

// Get today's date in the format yyyy-mm-dd
$todaysDate = date('Y-m-d');

// Directory location of the CSV file with today's date in the filename
$csvFile = '/home/joe/drivermgmt/ukgcsv/' . $todaysDate . '_drivers.csv';
logMessage("Using CSV file: $csvFile", 'info');

// Database connection details
require_once '/home/joe/drivermgmt/dbconnectconf.php';

// Connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if (!$conn) {
    logMessage("Database connection failed: " . mysqli_connect_error(), 'error');
    die("Connection failed: " . mysqli_connect_error());
}
logMessage("Database connection successful.", 'info');

// Check if the table is not empty
$tableCheckQuery = "SELECT COUNT(*) AS count FROM ukg_csv";
$result = mysqli_query($conn, $tableCheckQuery);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $rowCount = $row['count'];
    
    if ($rowCount > 0) {
        logMessage("Table 'ukg_csv' is not empty. Row count: $rowCount.", 'info');
        
        // Truncate the table to remove existing data
        $truncateQuery = "TRUNCATE TABLE ukg_csv";
        if (mysqli_query($conn, $truncateQuery)) {
            logMessage("Table 'ukg_csv' truncated successfully.", 'info');
        } else {
            logMessage("Failed to truncate table 'ukg_csv': " . mysqli_error($conn), 'error');
            die("Error truncating table.");
        }
    } else {
        logMessage("Table 'ukg_csv' is empty. Proceeding with data import.", 'info');
    }
} else {
    logMessage("Failed to check table 'ukg_csv' count: " . mysqli_error($conn), 'error');
    die("Error checking table count.");
}

// Open the CSV file
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    logMessage("Opened CSV file successfully.", 'info');
    
    // Get the header row
    $headers = fgetcsv($handle);
    logMessage("CSV headers: " . implode(", ", $headers), 'info');

    // Prepare an SQL statement for inserting data
    $stmt = $conn->prepare("INSERT INTO ukg_csv (EmployeeNumber, FirstName, LastName, OrgLevel2Code, OrgLevel2, DateReceived, LicenseCertificationCode, LicenseCertification, Number, TypeCode, ProviderCode, RenewalDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        logMessage("Failed to prepare SQL statement: " . $conn->error, 'error');
        die("Failed to prepare statement: " . $conn->error);
    }
    
    logMessage("SQL statement prepared successfully.", 'info');

    // Bind parameters to the SQL statement
    $stmt->bind_param("ssssssssssss", $employeeNumber, $firstName, $lastName, $orgLevel2Code, $orgLevel2, $dateReceived, $licenseCertificationCode, $licenseCertification, $number, $typeCode, $providerCode, $renewalDate);

    // Process each row of the CSV file
    while (($data = fgetcsv($handle)) !== FALSE) {
        // Ensure the data row has the expected number of columns
        if (count($data) >= 12) { // Change 12 to the number of columns expected
            // Map CSV data to variables
            list($employeeNumber, $firstName, $lastName, $orgLevel2Code, $orgLevel2, $dateReceived, $licenseCertificationCode, $licenseCertification, $number, $typeCode, $providerCode, $renewalDate) = $data;

            // Handle empty values for all columns
            $employeeNumber = !empty($employeeNumber) ? $employeeNumber : NULL;
            $firstName = !empty($firstName) ? $firstName : NULL;
            $lastName = !empty($lastName) ? $lastName : NULL;
            $orgLevel2Code = !empty($orgLevel2Code) ? $orgLevel2Code : NULL;
            $orgLevel2 = !empty($orgLevel2) ? $orgLevel2 : NULL;
            $dateReceived = !empty($dateReceived) ? $dateReceived : NULL;
            $licenseCertificationCode = !empty($licenseCertificationCode) ? $licenseCertificationCode : NULL;
            $licenseCertification = !empty($licenseCertification) ? $licenseCertification : NULL;
            $number = !empty($number) ? $number : NULL;
            $typeCode = !empty($typeCode) ? $typeCode : NULL;
            $providerCode = !empty($providerCode) ? $providerCode : NULL;
            $renewalDate = !empty($renewalDate) ? $renewalDate : NULL;

            // Execute the prepared statement
            if ($stmt->execute()) {
                logMessage("Inserted row for EmployeeNumber: $employeeNumber", 'info');
            } else {
                logMessage("Failed to insert row: " . $stmt->error, 'error');
            }
        } else {
            logMessage("Row with insufficient columns: " . implode(", ", $data), 'warning');
        }
    }

    // Close the file and statement
    fclose($handle);
    logMessage("CSV file closed.", 'info');

    $stmt->close();
    logMessage("SQL statement closed.", 'info');
} else {
    logMessage("Failed to open CSV file: $csvFile", 'error');
}

// Close the database connection
$conn->close();
logMessage("Database connection closed.", 'info');

echo "Data imported successfully.";
logMessage("Script completed successfully.", 'info');
?>
