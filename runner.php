  GNU nano 7.2                                           runner.php                                                     <?php

// Function to run a PHP script and capture the output and return status
function runScript($script) {
    // Execute the script and capture the output and exit code
    $output = [];
    $returnVar = 0;
    exec("php $script", $output, $returnVar);

    // Log output and errors for debugging
    echo "Running $script...\n";
    if ($returnVar === 0) {
        echo "$script executed successfully.\n";
    } else {
        echo "$script failed with errors:\n";
        echo implode("\n", $output) . "\n";
    }

    return $returnVar === 0;
}

while (true) {
    // Run 1.php and check for errors
    if (runScript('1.php')) {
        // Run 2.php only if 1.php was successful
        runScript('2.php');
    } else {
        echo "1.php encountered an error. Skipping 2.php.\n";
    }

    // Wait for 5 minutes (300 seconds) before the next iteration
    echo "Waiting 5 minutes before the next run...\n";
    sleep(300);
}
?>

