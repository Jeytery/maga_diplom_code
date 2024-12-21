<?php

/*
    Remove Track Part
    Url: DELETE /service/api/v1/track/part
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_DELETE} == true;

if ($roleAccessAllowed) {

    // TrackPartId
    $trackPartId = getRequestValue('trackPartId');

    if (!$trackPartId) {

        // { "trackPartId": <trackPartId> }
        $trackPartId = getPostRequestJson()->trackPartId;
    }

    if (!$trackPartId || !isValidUUID($trackPartId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        $userAccessAllowed = true;

    } else {  // Customer access

        // Check the trackId
        $trackId = $database->getStringValue("SELECT BIN_TO_UUID( t.track_id, true ) FROM `tracks` t, `track_parts` tp WHERE tp.track_part_id = UUID_TO_BIN( '$trackPartId', true) AND tp.track_id = t.track_id");

        if (!$trackId) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $trackId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $objectName = 'track_parts';

        // Remove old picture
        removePictureFromStorage($objectName, $trackPartId, 'track_part_id');

        $sqlRequest = "DELETE FROM `track_parts` WHERE track_part_id = UUID_TO_BIN( '$trackPartId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

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
