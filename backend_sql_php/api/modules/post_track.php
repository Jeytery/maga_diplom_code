<?php

/*
    Insert Track
    Url: POST /service/api/v1/track
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'tracks';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "sensorId": <sensorId>, "latitude": <latitude>, "longitude": <longitude>, "time": <trackTime>, "altitude": <altitude>, "accuracy": <accuracy>, "bearing": <bearing>, "speed": <speed>, "satellites": <satellites>, "battery": <battery>, "timeOffset": <timeOffset> }

    // SensorId
    $sensorId = getPostRequestJson()->sensorId;

    // Latitude
    $latitude = getPostRequestJson()->latitude;

    // Longitude
    $longitude = getPostRequestJson()->longitude;

    // TrackTime
    $trackTime = getPostRequestJson()->time;

    // Altitude
    $altitude = getPostRequestJson()->altitude;

    // Accuracy
    $accuracy = getPostRequestJson()->accuracy;

    // Bearing
    $bearing = getPostRequestJson()->bearing;

    // Speed
    $speed = getPostRequestJson()->speed;

    // Satellites
    $satellites = getPostRequestJson()->satellites;

    // Battery
    $battery = getPostRequestJson()->battery;

    // TimezoneOffset
    $timeOffset = getPostRequestJson()->timeOffset;

    if (!$sensorId || !isValidUUID($sensorId) ||
        !$latitude || !$longitude) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    // Check the sensorId. The default rule: Only the sensor owner could set sensor to track
    $sensorCount = $database->getIntValue("SELECT COUNT(sensor_id) FROM `sensors` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND sensor_id = UUID_TO_BIN( '$sensorId', true )");

    if ($sensorCount == 0) {

        postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
    }

    $uuid = $database->getStringValue("SELECT UUID()");

    $sqlRequest = "INSERT INTO `tracks` ( track_id, sensor_id, latitude, longitude, time, altitude, accuracy, bearing, speed, satellites, battery, time_offset ) VALUES ( UUID_TO_BIN( '$uuid', true ), UUID_TO_BIN( '$sensorId', true ), $latitude, $longitude, $trackTime, $altitude, $accuracy, $bearing, $speed, $satellites, $battery, $timeOffset )";

    $isCompleted = $database->query($sqlRequest);

    if ($isCompleted) {

        $trackId = $uuid;

        // Log to database

        $results = '{"trackId": "' . $trackId . '"}';

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
