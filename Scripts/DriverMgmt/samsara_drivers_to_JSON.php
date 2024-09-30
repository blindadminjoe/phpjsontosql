<?php

// Set the script name variable to identify this script in the logs
$scriptName = 'drivers_to_JSON'; // Adjust this to your specific script name

// Include the logger functions
require_once 'logger.php';

// Include Guzzle and other necessary files
require_once('vendor/autoload.php');
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Load the API token from configuration file
$apiConfig = require 'apiconf.php';
$samsaraApiToken = $apiConfig['samsara_api_token']; // Load token from config

$client = new Client();

try {
    logMessage("Starting the request to fetch JSON data.");

    // Send the request to fetch JSON data
    $response = $client->request('GET', 'https://api.samsara.com/fleet/drivers', [
        'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $samsaraApiToken, // Use token from config
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

// Function to flatten JSON and replace periods and spaces with underscores
function flattenArray($array, $prefix = '') {
    $flatArray = [];
    
    foreach ($array as $key => $value) {
        // Replace periods and spaces with underscores in the key
        $newKey = str_replace(['.', ' '], '_', $key);

        // Check if the value is an array
        if (is_array($value)) {
            // Handle attributes specifically if they are arrays with sub-values
            if ($newKey === 'attributes') {
                // Iterate through each attribute and use the attribute name as the key
                foreach ($value as $index => $attribute) {
                    if (isset($attribute['name'])) {
                        // Use the attribute 'name' as part of the key
                        $attributeName = str_replace(['.', ' '], '_', $attribute['name']);
                        $flatArray = array_merge($flatArray, flattenArray($attribute, $prefix . 'attribute_' . $attributeName . '_'));
                    } else {
                        // Fallback to index if no name is available
                        $flatArray = array_merge($flatArray, flattenArray($attribute, $prefix . 'attribute_' . $index . '_'));
                    }
                }
            } else {
                // Recursively flatten the array for non-attribute values
                $flatArray = array_merge($flatArray, flattenArray($value, $prefix . $newKey . '_'));
            }
        } else {
            // Directly add the flattened key-value pair
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
if (file_put_contents('drivers.json', $flattenedJson) === false) {
    $errorMessage = "Error writing to file.";
    logMessage($errorMessage);
    die($errorMessage);
}

logMessage("JSON data has been flattened and saved to drivers.json.");
echo "JSON data has been flattened and saved to drivers.json.";
?>
