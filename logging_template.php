<?php

// Log directory and log file with today's date
$logDir = '/var/log/joelogs/?????';
$logFile = $logDir . '/new_' . date('Y-m-d') . '.log'; // Creates a log file named with today's date

// Function to ensure the log directory exists
function ensureLogDirExists($logDir) {
    // Check if the log directory exists; if not, create it with appropriate permissions
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true); // Creates directory recursively with permissions
    }
}

// Function to log messages to the file only
function logMessage($message) {
    global $logFile;
    // Append the message with a timestamp to the log file
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Custom exception handler function
function customExceptionHandler($exception) {
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    file_put_contents('/var/log/joelogs/????/error.log', date('Y-m-d H:i:s') . " - " . $errorMessage . PHP_EOL, FILE_APPEND);
}

// Set custom exception handler
set_exception_handler("customExceptionHandler");


// Ensure the log directory exists before proceeding
ensureLogDirExists($logDir);