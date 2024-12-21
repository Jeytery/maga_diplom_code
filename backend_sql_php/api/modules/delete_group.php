<?php

/*
    Remove Group
    Url: DELETE /service/api/v1/group
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

        $userAccessAllowed = true;

    } else {  // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $groupId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Remove old picture
        removePictureFromStorage($objectName, $groupId, 'group_id');

        $sqlRequest = "DELETE FROM `groups` WHERE group_id = UUID_TO_BIN( '$groupId', true)";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Remove object options
            $sqlRequest = "DELETE FROM `object_options` WHERE object_id = UUID_TO_BIN( '$groupId', true ) AND object_name = 'groups'";

            $database->query($sqlRequest);

            // Remove users privileges to objects: groups, group_users
            $sqlRequest = "DELETE FROM `user_privileges` WHERE ( object_name = 'groups' AND object_id = UUID_TO_BIN( '$groupId', true ) ) OR ( object_name = 'group_users' AND object_id = UUID_TO_BIN( '$groupId', true ) )";

            $database->query($sqlRequest);

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
