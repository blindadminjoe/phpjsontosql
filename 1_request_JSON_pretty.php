<?php

// Define the log file path
$logFile = '1php.log';

// Function to log messages with date and time
function logMessage($message) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    $formattedMessage = "[$date] $message\n";
    file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    echo $formattedMessage; // Also output to the console
}

// Include Guzzle and other necessary files
require_once('vendor/autoload.php');
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client();

try {
    logMessage("Starting the request to fetch JSON data.");

    // Send the request to fetch JSON data
    $response = $client->request('GET', 'https://api.samsara.com/fleet/vehicles?limit=512', [
        'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer samsara_api_XXXXXXXXXXXXXX',
        ],
    ]);

    $jsonData = $response->getBody()->getContents();
    logMessage("Successfully fetched JSON data.");

} catch (RequestException $e) {
    $errorMessage = "Error fetching data: " . $e->getMessage();
    logMessage($errorMessage);
    die($errorMessage);
}

// Decode JSON data
$data = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $errorMessage = "Error decoding JSON: " . json_last_error_msg();
    logMessage($errorMessage);
    die($errorMessage);
}

logMessage("JSON data decoded successfully.");

// Ensure 'data' key exists and is an array
if (!isset($data['data']) || !is_array($data['data'])) {
    $errorMessage = "Invalid data structure: 'data' key is missing or not an array.";
    logMessage($errorMessage);
    die($errorMessage);
}

logMessage("'data' key found and is an array.");

// Function to flatten JSON and replace periods with underscores
function flattenArray($array, $prefix = '') {
    $flatArray = [];
    foreach ($array as $key => $value) {
        // Replace periods with underscores in the key
        $newKey = str_replace('.', '_', $key);

        if (is_array($value)) {
            // Recursively flatten the array
            $flatArray = array_merge($flatArray, flattenArray($value, $prefix . $newKey . '_'));
        } else {
            $flatArray[$prefix . $newKey] = $value;
        }
    }
    return $flatArray;
}

logMessage("Flattening the data...");

// Flatten each data entry but maintain the array structure
$flattenedData = array_map('flattenArray', $data['data']);

// Wrap flattened data in an array with the same 'data' key
$result = ['data' => $flattenedData];

// Convert the result to JSON format
$flattenedJson = json_encode($result, JSON_PRETTY_PRINT);

if ($flattenedJson === false) {
    $errorMessage = "Error encoding flattened JSON: " . json_last_error_msg();
    logMessage($errorMessage);
    die($errorMessage);
}

logMessage("Successfully encoded flattened JSON.");

// Write the flattened JSON to a new file
if (file_put_contents('vehicles.json', $flattenedJson) === false) {
    $errorMessage = "Error writing to file.";
    logMessage($errorMessage);
    die($errorMessage);
}

logMessage("JSON data has been flattened and saved to vehicles.json.");
echo "JSON data has been flattened and saved to vehicles.json.";
?>
