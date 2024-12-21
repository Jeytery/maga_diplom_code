<?php

/*
    Get Picture of User
    Url: GET /service/api/v1/user/picture
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // PictureType
    $pictureType = getRequestValue('type');

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        // UserId
        $userId = getRequestValue('userId');

        if (!$userId) {

            $userId = $currentUserId;

        } else if (!isValidUUID($userId)) {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $currentUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

            $userId = $currentUserId;

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $picture = $database->getStringValue("SELECT picture FROM `users` WHERE user_id = UUID_TO_BIN( $userId, true)");

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
