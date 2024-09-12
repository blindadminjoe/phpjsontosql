<?php
// Database connection details
require_once 'dbconnectconf.php';

// Directory location of the CSV file
$csvFile = 'drivers.csv';

// Connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Open the CSV file
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    // Get the header row
    $headers = fgetcsv($handle);

    // Prepare an SQL statement for inserting data
    $stmt = $conn->prepare("INSERT INTO EmployeeLicenses (EmployeeNumber, FirstName, LastName, OrgLevel2Code, OrgLevel2, DateReceived, LicenseCertificationCode, LicenseCertification, Number, TypeCode, ProviderCode, RenewalDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
            $stmt->execute();
        } else {
            // Handle rows with fewer columns (optional)
            // You might want to log this or handle it differently
            // For example, you can log or echo a message
            // echo "Row with insufficient columns: " . implode(", ", $data) . "\n";
        }
    }

    // Close the file and statement
    fclose($handle);
    $stmt->close();
}

// Close the database connection
$conn->close();

echo "Data imported successfully.";
?>
