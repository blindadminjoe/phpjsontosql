<?php

// Paths to the PHP scripts
$script1 = '/home/joe/VehicleManagement/1.php';
$script2 = '/home/joe/VehicleManagement/2.php';
$script3 = '/home/joe/VehicleManagement/3.php';

// Log file path
$logFile = '/var/log/joelogs/runner.log';

// Function to log messages to runner.log
function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Function to execute a PHP script and capture its output
function executeScript($script) {
    // Prepare the command to run the PHP script
    $command = "php $script 2>&1";
    
    // Execute the command and capture the output and return status
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    // Log output if there's an error
    if ($returnVar !== 0) {
        logMessage("Error running $script: " . implode("\n", $output));
    }

    // Return whether the command was successful
    return $returnVar === 0;
}

// Run the first script and check its success
$script1Success = executeScript($script1);

// Run the second script only if the first one was successful
$script2Success = $script1Success ? executeScript($script2) : false;

// Run the third script only if the first two scripts were successful
$script3Success = ($script1Success && $script2Success) ? executeScript($script3) : false;

// Log final success or failure message
if ($script1Success && $script2Success && $script3Success) {
    logMessage("All three scripts ran successfully.");
} else {
    if (!$script1Success) {
        logMessage("Error encountered in 1.php. Investigate further.");
    }
    if (!$script2Success) {
        logMessage("Error encountered in 2.php. Investigate further.");
    }
    if (!$script3Success) {
        logMessage("Error encountered in 3.php. Investigate further.");
    }
}
