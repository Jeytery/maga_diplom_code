<?php

/*
    Update Route Part Picture
    Url: PUT /service/api/v1/route/part/picture
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'routes';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "routePartId": <routePartId>, "picture": "/9j/4AA..." }

    // RoutePartId
    $routePartId = getPostRequestJson()->routePartId;

    if (!$routePartId || !isValidUUID($routePartId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        // Check the routeId
        $routeId = $database->getStringValue("SELECT BIN_TO_UUID( r.route_id, true) FROM `routes` r, `route_parts` rp WHERE rp.route_part_id = UUID_TO_BIN( '$routePartId', true ) AND rp.route_id = r.route_id");

        if (!$routeId) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $routeId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        if ($picture) {

            $objectName = 'route_parts';

            // Filename
            $randomName = getRandomString($pictureFilenameSize);
            $pictureFilename = $objectName . "_" . $routePartId . "_" . $randomName;

            // Storage type
            $storageType = FILES_STORAGE;

            // Save picture to storage
            savePictureToStorage($picture, $pictureFilename, $objectName);

            // Remove old picture
            removePictureFromStorage($objectName, $routePartId, 'route_part_id');

            // Set new picture
            $sqlRequest = ("UPDATE `$objectName` SET picture = 'fileStorage=$storageType; fileName=$pictureFilename' WHERE route_part_id = UUID_TO_BIN( '$routePartId', true )");

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
