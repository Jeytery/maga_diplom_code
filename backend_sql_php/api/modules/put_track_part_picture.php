<?php

/*
    Update Track Part Picture
    Url: PUT /service/api/v1/track/part/picture
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "trackPartId": <trackPartId>, "picture": "/9j/4AA..." }

    // TrackPartId
    $trackPartId = getPostRequestJson()->trackPartId;

    if (!$trackPartId || !isValidUUID($trackPartId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        // Check the trackId
        $trackId = $database->getStringValue("SELECT BIN_TO_UUID( t.track_id, true ) FROM `tracks` t, `track_parts` tp WHERE tp.track_part_id = UUID_TO_BIN( '$trackPartId', true ) AND tp.track_id = t.track_id");

        if (!$trackId) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $trackId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        if ($picture) {

            $objectName = 'track_parts';

            // Filename
            $randomName = getRandomString($pictureFilenameSize);
            $pictureFilename = $objectName . "_" . $trackPartId . "_" . $randomName;

            // Storage type
            $storageType = FILES_STORAGE;

            // Save picture to storage
            savePictureToStorage($picture, $pictureFilename, $objectName);

            // Remove old picture
            removePictureFromStorage($objectName, $trackPartId, 'track_part_id');

            // Set new picture
            $sqlRequest = ("UPDATE `$objectName` SET picture = 'fileStorage=$storageType; fileName=$pictureFilename' WHERE track_part_id = UUID_TO_BIN( '$trackPartId', true )");

            $database->query($sqlRequest);

            postSuccessResponse($sessionId, $successHttpAccepted);

        } else {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
        }

    } else {

        postErrorResponse($sessionId, $errorHttpForbidden, $errorUserPrivNotFound);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
