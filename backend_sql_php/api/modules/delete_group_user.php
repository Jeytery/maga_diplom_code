<?php

/*
    Remove Group User
    Url: DELETE /service/api/v1/group/user
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'group_users';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_DELETE} == true;

if ($roleAccessAllowed) {

    // GroupId
    $groupId = getRequestValue('groupId');

    if (!$groupId) {

        // { "groupId": <groupId> }
        $groupId = getPostRequestJson()->groupId;
    }

    if (!$groupId || !isValidUUID($groupId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;

        // Admin should provide the Id of the user for remove
        if (!$userId) {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
        }

        $userAccessAllowed = true;

    } else {  // Customer access

        // Check the group owner. The group owner could set user to group_users (Privilege was set when post group)
        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $groupId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            // UserId { "userId": <userId> }
            $userId = getPostRequestJson()->userId;

            // Group owner should provide the Id of the user for insert
            if (!$userId) {

                postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
            }

            $userAccessAllowed = true;

        } else { // The user in the group

            $userId = $currentUserId;

            $hasGroupAccess = isUserInGroup($userId, $groupId);

            if ($hasGroupAccess) {

                $userAccessAllowed = true;
            }
        }
    }

    if ($userAccessAllowed) {

        $sqlRequest = "DELETE FROM `group_users` WHERE group_id = UUID_TO_BIN( '$groupId', true ) AND user_id = UUID_TO_BIN( '$userId', true )";

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
