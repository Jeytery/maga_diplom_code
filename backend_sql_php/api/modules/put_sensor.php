<?php

/*
    Update Sensor
    Url: PUT /service/api/v1/sensor
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "sensorId": <sensorId>, "name": <sensorName>, "serialNumber": <serialNumber>, "deviceName": <deviceName>, "phone": <sensorPhone> }

    // SensorId
    $sensorId = getPostRequestJson()->sensorId;

    // SensorName
    $sensorName = getPostRequestJson()->name;

    // SerialNumber
    $serialNumber = getPostRequestJson()->serialNumber;

    // DeviceName
    $deviceName = getPostRequestJson()->deviceName;

    // SensorPhone
    $sensorPhone = getPostRequestJson()->phone;

    if (!$sensorId || !isValidUUID($sensorId) ||
        !$sensorName) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;
        if ($userId && isValidUUID($userId)) {
            $userId = ", user_id = UUID_TO_BIN( '" . $userId . "', true )";
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $sensorId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Update
        $sqlRequest = "UPDATE `sensors` SET name = '$sensorName', serial_number = '$serialNumber', device_name = '$deviceName', phone = '$sensorPhone'" . $userId . " WHERE sensor_id = UUID_TO_BIN( '$sensorId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Log to database

            postSuccessResponse($sessionId, $successHttpAccepted);

        } else {

            $databaseError = $database->getErrorMessage();
            $errorDatabaseError['name'] = $databaseError;
            postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
        }

    } else {

        postErrorResponse($sessionId, $errorHttpForbidden, $errorUserPrivNotFound);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
