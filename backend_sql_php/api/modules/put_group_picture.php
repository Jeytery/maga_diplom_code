<?php

/*
    Update Group Picture
    Url: PUT /service/api/v1/group/picture
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'groups';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "groupId": <groupId>, "picture": "/9j/4AA..." }

    // GroupId
    $groupId = getPostRequestJson()->groupId;

    if (!$groupId || !isValidUUID($groupId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $groupId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        if ($picture) {

            $objectName = 'groups';

            // Filename
            $randomName = getRandomString($pictureFilenameSize);
            $pictureFilename = $objectName . "_" . $groupId . "_" . $randomName;

            // Storage type
            $storageType = FILES_STORAGE;

            // Save picture to storage
            savePictureToStorage($picture, $pictureFilename, $objectName);

            // Remove old picture
            removePictureFromStorage($objectName, $groupId, 'group_id');

            // Set new picture
            $sqlRequest = ("UPDATE `$objectName` SET picture = 'fileStorage=$storageType; fileName=$pictureFilename' WHERE group_id = UUID_TO_BIN( '$groupId', true )");

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
