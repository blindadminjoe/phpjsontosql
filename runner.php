<?php

// Paths to the PHP scripts and log files
$script1 = '/home/joe/VehicleManagement/1.php';
$logFile1 = '/var/log/joelogs/runner1.log';
$script2 = '/home/joe/VehicleManagement/2.php';
$logFile2 = '/var/log/joelogs/runner2.log';

// Function to execute a PHP script and log its output
function executeScript($script, $logFile) {
    // Prepare the command to run the PHP script
    $command = "php $script 2>&1";
    
    // Execute the command and capture the output
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);
    
    // Log the output
    file_put_contents($logFile, implode("\n", $output), FILE_APPEND);
    
    // Return whether the command was successful
    return $returnVar === 0;
}

// Run the first script and log its output
if (executeScript($script1, $logFile1)) {
    // If the first script executed successfully, run the second script
    executeScript($script2, $logFile2);
} else {
    // Handle error if needed
    file_put_contents($logFile1, "Error running $script1\n", FILE_APPEND);
}
