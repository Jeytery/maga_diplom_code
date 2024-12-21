<?php

/*
    Remove Sensor
    Url: DELETE /service/api/v1/sensor
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_DELETE} == true;

if ($roleAccessAllowed) {

    // SensorId
    $sensorId = getRequestValue('sensorId');

    if (!$sensorId) {

        // { "sensorId": <sensorId> }
        $sensorId = getPostRequestJson()->sensorId;
    }

    if (!$sensorId || !isValidUUID($sensorId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        $userAccessAllowed = true;

    } else {  // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $sensorId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $sqlRequest = "DELETE FROM `sensors` WHERE sensor_id = UUID_TO_BIN( '$sensorId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Remove object options
            $sqlRequest = "DELETE FROM `object_options` WHERE object_id = UUID_TO_BIN( '$sensorId', true ) AND object_name = 'sensors'";

            $database->query($sqlRequest);

            // Remove users privileges to object
            $sqlRequest = "DELETE FROM `user_privileges` WHERE object_name = 'sensors' AND object_id = UUID_TO_BIN( '$sensorId', true )";

            $database->query($sqlRequest);

            // Log to database

            postSuccessResponse($sessionId, $successHttpResetContent);

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
