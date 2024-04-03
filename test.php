<?php

function isServerLoadHealthy()
{
    // Execute a command to get server load metrics
    $loadAverage = sys_getloadavg(); // This function returns an array containing the 1, 5, and 15 minute load averages

    // Check if the load averages exceed certain thresholds
    $loadThreshold = 1.0; // Example threshold value

    // You may adjust these thresholds according to your server's capacity and performance requirements
    $isHealthy = true;
    foreach ($loadAverage as $load) {
        if ($load > $loadThreshold) {
            $isHealthy = false;
            break;
        }
    }
    return $isHealthy;
}

function isFileSystemHealthy()
{
    // Specify the directories you want to check
    $directories = array('/var/www/html', '/var/log');

    // Define a threshold for minimum free disk space in bytes
    $minFreeSpace = 1 * 1024 * 1024; // 1 MB (adjust as needed)

    // Iterate through each directory and check its free disk space
    foreach ($directories as $directory) {
        // Get disk space info for the directory
        $diskFreeSpace = disk_free_space($directory);

        // Check if free disk space is below the threshold
        if ($diskFreeSpace < $minFreeSpace) {
            return false; // File system is not healthy
        }
    }

    return true; // File system is healthy
}


function isDatabaseHealthy()
{
    $db = new PDO("mysql:host=localhost;dbname=mne", "root", "", array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ));
    if (!$db) {
        return false;
    }
    // Perform a basic query to ensure database responsiveness
    $query_rsSetting =  $db->prepare("SELECT * FROM `tbl_company_settings`");
    $query_rsSetting->execute();
    $totalRows_rsSetting = $query_rsSetting->rowCount();
    // Check if the query was successful
    if (!$totalRows_rsSetting) {
        $db = null;
        return false; // Database is not healthy
    }
    $db = null;
    return true;
}

// Call the isServerLoadHealthy function
if (isServerLoadHealthy()) {
    echo 'Server load is healthy.';
} else {
    echo 'Server load is high.';
}

// Call the isFileSystemHealthy function
if (isFileSystemHealthy()) {
    echo 'File system is healthy.';
} else {
    echo 'File system is not healthy.';
}

// Call the isDatabaseHealthy function
if (isDatabaseHealthy()) {
    echo 'Database is healthy.';
} else {
    echo 'Database is not healthy.';
}
