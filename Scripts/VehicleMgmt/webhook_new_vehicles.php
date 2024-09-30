<?php
// Database connection details
require_once 'dbconnectconf.php'; // Ensure this file contains your DB connection details

// URL of the webhook endpoint
$webhookUrl = 'https://leecontracting.webhook.office.com/webhook'; // Replace with your actual URL endpoint

// Connect to the database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch rows from the past day (last 24 hours)
$sql = "SELECT * FROM samsara_new_vehicles WHERE created_at_time >= NOW() - INTERVAL 1 DAY"; // Adjust the column name if necessary
$result = $conn->query($sql);

// Check if rows exist
if ($result->num_rows > 0) {
    // Iterate through each row and send data to the webhook
    while ($row = $result->fetch_assoc()) {
        // Extract the necessary details from each row
        $name = isset($row['name']) ? $row['name'] : 'Unknown Name';
        $year = isset($row['year']) ? $row['year'] : 'Unknown Year';
        $make = isset($row['make']) ? $row['make'] : 'Unknown Make';
        $model = isset($row['model']) ? $row['model'] : 'Unknown Model';

        // Prepare data for the webhook, including row values in the summary
        $payload = [
            'summary' => "New Vehicle: $year $make $model (Name: $name)", // Dynamically include row data
            'text' => "A new vehicle was added into Samsara within the past day: $year $make $model (Name: $name).", // Detailed description with the same row data
            'data' => $row, // Include the row data as part of the payload
        ];

        // Convert the payload to JSON
        $jsonPayload = json_encode($payload);

        // Send data via cURL
        $response = sendWebhook($webhookUrl, $jsonPayload);

        // Log or display the response for debugging purposes
        echo "Webhook Response: " . $response . "\n";
    }
} else {
    echo "No new rows found within the past day.";
}

// Close the database connection
$conn->close();

/**
 * Function to send data to a webhook URL using cURL
 *
 * @param string $url The webhook URL to send the data to
 * @param string $payload The JSON-encoded data to send
 * @return string The response from the webhook server
 */
function sendWebhook($url, $payload) {
    // Initialize cURL session
    $ch = curl_init($url);

    // cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json', // Set the content type to JSON
        'Content-Length: ' . strlen($payload) // Set the content length header
    ]);
    curl_setopt($ch, CURLOPT_POST, true); // Use POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Attach the JSON payload

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        // Log the error
        echo "cURL Error: " . curl_error($ch);
    }

    // Close the cURL session
    curl_close($ch);

    return $response;
}
?>
