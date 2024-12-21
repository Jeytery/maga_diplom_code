<?php

/*
    Get Picture of Track Part (For pictures debug purpose)
    Url: GET /service/api/v1/track/part/picture
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

/*
    The User has access to picture when:
    - Group member of Route
    - Access to Sensor of Track
    - Access to Route of Track
 */

// Get the privileges
$objectName = 'tracks';
$objectId = null;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // TrackPartId
    $trackPartId = getRequestValue('trackPartId');

    if (!$trackPartId || !isValidUUID($trackPartId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    // PictureType
    $pictureType = getRequestValue('type');

    $userAccessAllowed = true; // Already available

    if ($userAccessAllowed) {

        $picture = $database->getStringValue("SELECT picture FROM `track_parts` WHERE track_part_id = UUID_TO_BIN( $trackPartId, true)");

        if ($picture) {

            // Send picture response
            sendPictureResponse($picture, $pictureType, $objectName);

        } else {

            //postErrorResponse($sessionId, $errorHttpNotFound, $errorDataNotFound);
            postSuccessResponse($sessionId, $successHttpNoContent);
        }

    }  else {

        postErrorResponse($sessionId, $errorHttpForbidden, $errorUserPrivNotFound);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
