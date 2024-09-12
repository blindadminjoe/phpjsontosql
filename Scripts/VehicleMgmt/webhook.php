<?php
// Database connection details
require_once 'dbconnectconf.php'; // Ensure this file contains your DB connection details

// URL of the webhook endpoint
$webhookUrl = 'https://leecontracting.webhook.office.com/webhookb2/49d3d8c7-11e2-4a3e-855b-a62f96559bb3@75acc0bb-70b7-4cd5-974d-8cabff9dec52/IncomingWebhook/12a05c63918a443480bc1c3c9e68150a/99dbd767-0ed3-4932-bc9d-537c88871171'; // Replace with your actual URL endpoint

// Connect to the database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch data from your table
$sql = "SELECT * FROM vehicle_changes"; // Adjust the table name and query as needed
$result = $conn->query($sql);

// Check if rows exist
if ($result->num_rows > 0) {
    // Iterate through each row and send data to the webhook
    while ($row = $result->fetch_assoc()) {
        // Prepare data for the webhook, including required fields like "summary" or "text"
        $payload = [
            'summary' => 'New Vehicle in table.', // Replace with relevant summary
            'text' => 'New Vehicle in table.', // Replace with relevant description
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
    echo "No rows found in the table.";
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
