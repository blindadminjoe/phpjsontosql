<?php
// Declare a global variable to hold the script name; this must be set in each script that includes this file
global $scriptName;

// Function to ensure the log directory exists
function ensureLogDirExists($logDir) {
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true); // Creates the directory recursively with permissions
    }
}

// Function to initialize logging settings based on the script name
function initLogger() {
    global $scriptName, $logDir, $logFile;
    $scriptName = $scriptName ?? 'UnknownScript'; // Fallback in case $scriptName is not set
    $logDir = "/var/log/joelogs/$scriptName"; // Directory named after the script
    $logFile = $logDir . '/' . $scriptName . '_' . date('Y-m-d') . '.log'; // Log file with the script name and date
    ensureLogDirExists($logDir); // Ensure the log directory exists
}

// Function to log messages to the file only
function logMessage($message) {
    global $logFile, $scriptName;
    $scriptName = $scriptName ?? 'UnknownScript'; // Fallback in case $scriptName is not set
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - [$scriptName] " . $message . PHP_EOL, FILE_APPEND);
}

// Custom exception handler function
function customExceptionHandler($exception) {
    global $scriptName;
    $scriptName = $scriptName ?? 'UnknownScript'; // Fallback in case $scriptName is not set
    $errorDir = "/var/log/joelogs/$scriptName"; // Error log directory based on the script name
    ensureLogDirExists($errorDir);
    $errorLogFile = $errorDir . '/error.log';
    $errorMessage = "Uncaught Exception in [$scriptName]: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    file_put_contents($errorLogFile, date('Y-m-d H:i:s') . " - " . $errorMessage . PHP_EOL, FILE_APPEND);
}

// Set the custom exception handler
set_exception_handler("customExceptionHandler");

// Initialize the logger based on the script name
initLogger();
?>
