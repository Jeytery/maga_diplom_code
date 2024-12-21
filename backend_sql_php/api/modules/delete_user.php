<?php

/*
    Remove User
    Url: DELETE /service/api/v1/user
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_DELETE} == true;

if ($roleAccessAllowed) {

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        // UserId
        $userId = getRequestValue('userId');

        if (!$userId) {

            // { "userId": <userId> }
            $userId = getPostRequestJson()->userId;
        }

        // Admin should provide the Id of the user for remove
        if (!$userId || !isValidUUID($userId)) {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
        }

        $userAccessAllowed = true;

    } else {  // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $currentUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userId = $currentUserId;

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $userCanDeleteProfile = $database->getStringValue("SELECT value FROM `settings` WHERE name = 'userCanDeleteProfile'");

        if ($userCanDeleteProfile) {

            // Remove old picture
            removePictureFromStorage($objectName, $userId, 'user_id');

            $sqlRequest = "DELETE FROM `users` WHERE user_id = UUID_TO_BIN( '$userId', true )";

            $isCompleted = $database->query($sqlRequest);

            if ($isCompleted) {

                // Remove object options
                $sqlRequest = "DELETE FROM `object_options` WHERE object_id = UUID_TO_BIN( '$userId', true ) AND object_name = 'users'";

                $database->query($sqlRequest);

                // Remove users privileges to objects: users, all objects of user
                $sqlRequest = "DELETE FROM `user_privileges` WHERE ( object_name = 'users' AND object_id = UUID_TO_BIN( '$userId', true ) ) OR ( user_id = UUID_TO_BIN( '$userId', true ) )";

                $database->query($sqlRequest);

                // Log to database

                postSuccessResponse($sessionId, $successHttpResetContent);

            } else {

                $databaseError = $database->getErrorMessage();
                $errorDatabaseError['name'] = $databaseError;
                postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
            }

        } else { // Hide only, set User status to USER_STATUS_REMOVED

            $userStatus = USER_STATUS_REMOVED;

            $sqlRequest = "UPDATE `users` SET status = $userStatus WHERE userId = UUID_TO_BIN( '$userId', true )";

            $database->query($sqlRequest);

            // Log to database

            postSuccessResponse($sessionId, $successHttpResetContent);

        }

    } else {

        postErrorResponse($sessionId, $errorHttpForbidden, $errorUserPrivNotFound);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
