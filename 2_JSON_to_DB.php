<?php

// Database connection details
require_once 'dbconnectconf.php';

// Create connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read JSON data from file
$jsonData = file_get_contents('vehicles.json');

// Decode JSON data
$data = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Error decoding JSON: " . json_last_error_msg());
}

// Prepare SQL statement
$stmt = $conn->prepare("
    INSERT INTO vehicles_from_samsara (
        externalIds_samsara_serial, externalIds_samsara_vin, gateway_serial, gateway_model, harshaccelerationsettingtype, id, licenseplate, make, model, name, notes, serial, staticassigneddriver_id, staticassigneddriver_name, tags_0_id, tags_0_name, tags_0_parentTagId, vin, year, vehicleregulationmode, created_at_time, updated_at_time, esn
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        externalIds_samsara_serial = VALUES(externalIds_samsara_serial),
        externalIds_samsara_vin = VALUES(externalIds_samsara_vin),
        gateway_serial = VALUES(gateway_serial),
        gateway_model = VALUES(gateway_model),
        harshaccelerationsettingtype = VALUES(harshAccelerationSettingType),
        id = VALUES(id),
        licenseplate = VALUES(licensePlate),
        make = VALUES(make),
        model = VALUES(model),
        name = VALUES(name),
        notes = VALUES(notes),
        serial = VALUES(serial),
        staticassigneddriver_id = VALUES(staticAssignedDriver_id),
        staticassigneddriver_name = VALUES(staticAssignedDriver_name),
        tags_0_id = VALUES(tags_0_id),
        tags_0_name = VALUES(tags_0_name),
        tags_0_parentTagId = VALUES(tags_0_parentTagId),
        vin = VALUES(vin),
        year = VALUES(year),
        vehicleregulationmode = VALUES(vehicleRegulationMode),
        created_at_time = VALUES(created_at_time),
        updated_at_time = VALUES(updated_at_time),
        esn = VALUES(esn)
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Loop through the data and insert/update the database
foreach ($data['data'] as $vehicle) {
    // Handle missing keys with default values
    $externalIds_samsara_serial = isset($vehicle['externalIds_samsara_serial']) ? $vehicle['externalIds_samsara_serial'] : null;
    $externalIds_samsara_vin = isset($vehicle['externalIds_samsara_vin']) ? $vehicle['externalIds_samsara_vin'] : null;
    $gateway_serial = isset($vehicle['gateway_serial']) ? $vehicle['gateway_serial'] : null;
    $gateway_model = isset($vehicle['gateway_model']) ? $vehicle['gateway_model'] : null;
    $harshAccelerationSettingType = isset($vehicle['harshAccelerationSettingType']) ? $vehicle['harshAccelerationSettingType'] : null;
    $id = isset($vehicle['id']) ? $vehicle['id'] : null;
    $licensePlate = isset($vehicle['licensePlate']) ? $vehicle['licensePlate'] : null;
    $make = isset($vehicle['make']) ? $vehicle['make'] : null;
    $model = isset($vehicle['model']) ? $vehicle['model'] : null;
    $name = isset($vehicle['name']) ? $vehicle['name'] : null;
    $notes = isset($vehicle['notes']) ? $vehicle['notes'] : null;
    $serial = isset($vehicle['serial']) ? $vehicle['serial'] : null;
    $staticAssignedDriver_id = isset($vehicle['staticAssignedDriver_id']) ? $vehicle['staticAssignedDriver_id'] : null;
    $staticAssignedDriver_name = isset($vehicle['staticAssignedDriver_name']) ? $vehicle['staticAssignedDriver_name'] : null;
    $tags_0_id = isset($vehicle['tags_0_id']) ? $vehicle['tags_0_id'] : null;
    $tags_0_name = isset($vehicle['tags_0_name']) ? $vehicle['tags_0_name'] : null;
    $tags_0_parentTagId = isset($vehicle['tags_0_parentTagId']) ? $vehicle['tags_0_parentTagId'] : null;
    $vin = isset($vehicle['vin']) ? $vehicle['vin'] : null;
    $year = isset($vehicle['year']) ? $vehicle['year'] : null;
    $vehicleRegulationMode = isset($vehicle['vehicleRegulationMode']) ? $vehicle['vehicleRegulationMode'] : null;
    $createdAtTime = isset($vehicle['createdAtTime']) ? (new DateTime($vehicle['createdAtTime']))->format('Y-m-d H:i:s') : null;
    $updatedAtTime = isset($vehicle['updatedAtTime']) ? (new DateTime($vehicle['updatedAtTime']))->format('Y-m-d H:i:s') : null;
    $esn = isset($vehicle['esn']) ? $vehicle['esn'] : null;

    // Bind parameters and execute the statement
    $stmt->bind_param(
        "sssssissssssssssissssss",
        $externalIds_samsara_serial,
        $externalIds_samsara_vin,
        $gateway_serial,
        $gateway_model,
        $harshAccelerationSettingType,
        $id,
        $licensePlate,
        $make,
        $model,
        $name,
        $notes,
        $serial,
        $staticAssignedDriver_id,
        $staticAssignedDriver_name,
        $tags_0_id, 
        $tags_0_name,
        $tags_0_parentTagId,
        $vin,
        $year,
        $vehicleRegulationMode,
        $createdAtTime,
        $updatedAtTime,
        $esn
    );

    if (!$stmt->execute()) {
        echo "Execute failed for record: " . json_encode($vehicle) . " Error: " . $stmt->error . "\n";
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

echo "Database updated successfully.";
?>
