<?php

/*
    Remove Track
    Url: DELETE /service/api/v1/track
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

    // TrackId
    $trackId = getRequestValue('trackId');

    if (!$trackId) {

        // { "trackId": <trackId> }
        $trackId = getPostRequestJson()->trackId;
    }

    if (!$trackId || !isValidUUID($trackId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        $userAccessAllowed = true;

    } else {  // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $trackId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $sqlRequest = "DELETE FROM `tracks` WHERE track_id = UUID_TO_BIN( '$trackId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Remove object options
            $sqlRequest = "DELETE FROM `object_options` WHERE object_id = UUID_TO_BIN( '$trackId', true ) AND object_name = 'tracks'";

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
