<?php

/*
    Update User Password
    Url: PUT /service/api/v1/user/password
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

    // { "password": <password> }

    // NewPassword
    $newPassword = getPostRequestJson()->password;

    if (!$newPassword) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;

        // Admin should provide the Id of the user for remove
        if (!$userId || !isValidUUID($userId)) {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
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

        // Update
        $sqlRequest = "UPDATE `users` SET password = '$newPassword' WHERE user_id = UUID_TO_BIN( '$userId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Log to database

            // Send notification via Support service

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
