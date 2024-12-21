<?php

/*
    Insert Track List
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

    // {"tracks": [{ "sensorId": <sensorId>, "latitude": <latitude>, "longitude": <longitude>, "time": <trackTime>, "altitude": <altitude>, "accuracy": <accuracy>, "bearing": <bearing>, "speed": <speed>, "satellites": <satellites>, "battery": <battery>, "timeOffset": <timeOffset> }, { ... }] }

    $tracks = getPostRequestJson()->tracks;

    if (!isset($tracks) || !is_array($tracks)) {
        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $sqlRequests = "";

    foreach ($tracks as $track) {

        // SensorId
        $sensorId = $track->sensorId;

        // Latitude
        $latitude = $track->latitude;

        // Longitude
        $longitude = $track->longitude;

        // TrackTime
        $trackTime = $track->time;

        // Altitude
        $altitude = $track->altitude;

        // Accuracy
        $accuracy = $track->accuracy;

        // Bearing
        $bearing = $track->bearing;

        // Speed
        $speed = $track->speed;

        // Satellites
        $satellites = $track->satellites;

        // Battery
        $battery = $track->battery;

        // TimezoneOffset
        $timeOffset = $track->timeOffset;

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

        $sqlRequests .= "INSERT INTO `tracks` ( track_id, sensor_id, latitude, longitude, time, altitude, accuracy, bearing, speed, satellites, battery, time_offset ) VALUES ( UUID_TO_BIN( '$uuid', true ), UUID_TO_BIN( '$sensorId', true ), $latitude, $longitude, $trackTime, $altitude, $accuracy, $bearing, $speed, $satellites, $battery, $timeOffset );";
    }

    $isCompleted = $database->multiQuery($sqlRequests);

    if ($isCompleted) {

        postSuccessResponse($sessionId, $successHttpCreated);

    } else {

        $databaseError = $database->getErrorMessage();
        $errorDatabaseError['name'] = $databaseError;
        postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
