<?php

/*
    Insert Group User
    Url: POST /service/api/v1/group/user
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "groupId": <groupId> }

    // GroupId
    $groupId = getPostRequestJson()->groupId;

    if (!$groupId || !isValidUUID($groupId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_INSERT}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;

        // Admin should provide the Id of the user for insert
        if (!$userId || !isValidUUID($userId)) {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
        }

        $userAccessAllowed = true;

    } else { // Customer access

        // Check the group owner. The group owner could set user to group_users (Privilege was set when post group)
        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $groupId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_INSERT}) {

            // UserId { "userId": <userId> }
            $userId = getPostRequestJson()->userId;

            // Group owner should provide the Id of the user for insert
            if (!$userId || !isValidUUID($userId)) {

                postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
            }

            $userAccessAllowed = true;

        } else { // The user sent the invitation code

            // Invitation code { "invitationCode": <invitationCode> }
            $invitationCode = getPostRequestJson()->invitationCode;

            if (!$invitationCode) {

                postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
            }

            if ($invitationCode != 'code0') {

                postErrorResponse($sessionId, $errorHttpBadRequest, $errorHttpNotAcceptable);
            }

            $userId = $currentUserId;

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $sqlRequest = "INSERT INTO `group_users` ( group_id, user_id ) VALUES ( UUID_TO_BIN( '$groupId', true ), UUID_TO_BIN( '$userId', true ) )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Log to database

            postSuccessResponse($sessionId, $successHttpCreated);

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
