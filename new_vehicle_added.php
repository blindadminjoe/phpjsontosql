<?php
// Database connection details
require_once 'dbconnectconf.php';

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// File to keep track of the last timestamp or ID processed
$trackingFile = 'new_vehicle_added/last_checked.txt';

// Check if the file exists
if (!file_exists($trackingFile)) {
    // If the file doesn't exist, create it with the current timestamp
    $currentTimestamp = date('Y-m-d H:i:s'); // Format the current timestamp
    file_put_contents($trackingFile, $currentTimestamp); // Write the current timestamp to the file
    $lastChecked = $currentTimestamp; // Set the last checked timestamp to the current timestamp
} else {
    // Read the last checked timestamp or ID
    $lastChecked = file_get_contents($trackingFile);
}

// Query to fetch new rows based on the 'created_at' column
$sql = "SELECT * FROM vehicles_from_samsara WHERE created_at_time > ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $lastChecked);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are new rows
if ($result->num_rows > 0) {
    // Open CSV file for appending
    $csvFile = fopen('new_vehicle_added/new_rows.csv', 'a');

    // Fetch and write each new row to CSV
    while ($row = $result->fetch_assoc()) {
        // Output the row to the CSV file
        fputcsv($csvFile, $row);

        // Update the last checked timestamp
        $lastChecked = max($lastChecked, $row['created_at_time']);

        // Get the 'name' value for the webhook message
        $gatewayName = $row['name'];

        // Prepare the webhook payload
        $payload = [
            'summary' => "A new gateway '$gatewayName' has been added to Samsara",
            'text' => "A new gateway named '$gatewayName' has been added to Samsara."
        ];

        // Send a webhook notification
        $webhookUrl = 'https://leecontracting.webhook.office.com/webhookb2/49d3d8c7-11e2-4a3e-855b-a62f96559bb3@75acc0bb-70b7-4cd5-974d-8cabff9dec52/IncomingWebhook/12a05c63918a443480bc1c3c9e68150a/99dbd767-0ed3-4932-bc9d-537c88871171'; // Replace with your actual webhook URL

        // Convert the payload to JSON
        $payloadJson = json_encode($payload);

        // Send the webhook request
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            error_log('Webhook error: ' . curl_error($ch));
        }

        // Close the curl session
        curl_close($ch);
    }

    fclose($csvFile);

    // Update the tracking file with the latest timestamp
    file_put_contents($trackingFile, $lastChecked);
} else {
    echo "No new rows found.\n";
}

// Close the database connection
$stmt->close();
$conn->close();
?>
