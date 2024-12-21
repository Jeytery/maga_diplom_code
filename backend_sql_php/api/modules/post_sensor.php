<?php

/*
    Insert Sensor
    Url: POST /service/api/v1/sensor
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'sensors';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "name": <sensorName>, "serialNumber": <serialNumber>, "deviceName": <deviceName>, "phone": <sensorPhone> }

    // SensorName
    $sensorName = getPostRequestJson()->name;

    // SerialNumber
    $serialNumber = getPostRequestJson()->serialNumber;

    // DeviceName
    $deviceName = getPostRequestJson()->deviceName;

    // SensorPhone
    $sensorPhone = getPostRequestJson()->phone;

    if (!$serialNumber || !$deviceName) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    // Check the sensorId
    $sqlRequest = "SELECT BIN_TO_UUID( sensor_id, true ) FROM `sensors` WHERE user_id = UUID_TO_BIN( '$currentUserId', true) AND serial_number = '$serialNumber' AND device_name = '$deviceName'";

    $sensorId = $database->getStringValue($sqlRequest);

    if ($sensorId) {

        $isCompleted = true;

        $sqlRequest = "DELETE FROM `object_options` WHERE object_id = UUID_TO_BIN( '$sensorId', true ) AND object_name = 'sensors'";

        $database->query($sqlRequest);

    } else {

        $uuid = $database->getStringValue("SELECT UUID()");

        $sqlRequest = "INSERT INTO `sensors` ( sensor_id, user_id, name, serial_number, device_name, phone ) VALUES ( UUID_TO_BIN( '$uuid', true ), UUID_TO_BIN( '$currentUserId', true ), '$sensorName', '$serialNumber', '$deviceName', '$sensorPhone' )";

        $isCompleted = $database->query($sqlRequest);
    }

    if ($isCompleted) {

        if (!$sensorId) {

            $sensorId = $uuid;

            // Make user access to object
            $sqlRequest = "INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
            VALUES ( UUID_TO_BIN( '$currentUserId', true ), UUID_TO_BIN( '$sensorId', true ), 'sensors', true, true, true, true )";

            $database->query($sqlRequest);
        }

        // Sensor option DeviceType
        $sqlRequest = "INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
        VALUES ( UUID_TO_BIN( '$sensorId', true ), 'sensors', 'deviceType', '$deviceType', 'string')";

        $database->query($sqlRequest);

        // Sensor option TimeOffset
        $sqlRequest = "INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
        VALUES ( UUID_TO_BIN( '$sensorId', true ), 'sensors', 'timeOffset', '$timeOffset', 'number')";

        $database->query($sqlRequest);

        // Sensor option LanguageCode
        $sqlRequest = "INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
        VALUES ( UUID_TO_BIN( '$sensorId', true ), 'sensors', 'languageCode', '$languageCode', 'string')";

        $database->query($sqlRequest);

        // Log to database

        $results = '{"sensorId": "' . $sensorId . '"}';

        $rowsCount = 1;

        postSuccessResponseData($sessionId, $results, $rowsCount);

    } else {

        $databaseError = $database->getErrorMessage();
        $errorDatabaseError['name'] = $databaseError;
        postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
