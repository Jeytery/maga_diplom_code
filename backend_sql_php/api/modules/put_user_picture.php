<?php

/*
    Update User Picture
    Url: PUT /service/api/v1/user/picture
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'users';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "picture": "/9j/4AA..." }

    // Picture
    $picture = getPostRequestJson()->picture;

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;

        if (!$userId || !isValidUUID($userId)) {

            $userId = $currentUserId;
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $currentUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userId = $currentUserId;

            $userAccessAllowed = true;

        }
    }

    if ($userAccessAllowed) {

        if ($picture) {

            // Filename
            $randomName = getRandomString($pictureFilenameSize);
            $pictureFilename = $objectName . "_" . $userId . "_" . $randomName;

            // Storage type
            $storageType = FILES_STORAGE;

            // Save picture to storage
            savePictureToStorage($picture, $pictureFilename, $objectName);

            // Remove old picture
            removePictureFromStorage($objectName, $userId, 'user_id');

            // Set new picture
            $sqlRequest = ("UPDATE `$objectName` SET picture = 'fileStorage=$storageType; fileName=$pictureFilename' WHERE user_id = UUID_TO_BIN( '$userId', true )");

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
