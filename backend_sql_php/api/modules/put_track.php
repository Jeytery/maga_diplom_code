<?php

/*
    Update Track
    Url: PUT /service/api/v1/track
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
// Important track privileges info: Does not set the track privelege to the User when posted new one.
// Admin has access record to all tracks with 0 object Id vqlue.
$objectName = 'tracks';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "trackId": <trackId>, "sensorId": <sensorId>, "latitude": <latitude>, "longitude": <longitude>, "time": <trackTime>, "altitude": <altitude>, "accuracy": <accuracy>, "bearing": <bearing>, "speed": <speed>, "satellites": <satellites>, "battery": <battery>, "timeOffset": <timeOffset> }

    // TrackId
    $trackId = getPostRequestJson()->trackId;

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

    if (!$trackId || !isValidUUID($trackId) ||
        !$sensorId || !isValidUUID($sensorId) ||
        !$latitude || !$longitude) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        // Do not check the User privilege to track
        // Check the trackId. Verify if the User is track owner
        $trackCount = $database->getIntValue("SELECT COUNT(track_id) FROM `tracks` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND track_id = UUID_TO_BIN( '$trackId', true )");

        if ($trackCount == 0) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        // Check the sensorId. The default rule: Only the track owner could set sensor to track
        $sensorCount = $database->getIntValue("SELECT COUNT(sensor_id) FROM `sensors` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND sensor_id = UUID_TO_BIN( '$sensorId', true )");

        if ($sensorCount == 0) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        $userAccessAllowed = true;
    }

    if ($userAccessAllowed) {

        // Update
        $sqlRequest = "UPDATE `tracks` SET sensor_id = UUID_TO_BIN( '$sensorId', true ), latitude = $latitude, longitude = $longitude, time = $trackTime, altitude = $altitude, accuracy = $accuracy, bearing = $bearing, speed = $speed, satellites = $satellites, battery = $battery, time_offset = $timeOffset WHERE track_id = UUID_TO_BIN( '$trackId', true )";

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
