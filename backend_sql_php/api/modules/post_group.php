<?php

/*
    Insert Group
    Url: POST /service/api/v1/group
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "name": <groupName>, "description": <description>, "tags": <groupTags> }

    // GroupName
    $groupName = getPostRequestJson()->name;

    // Description
    $description = getPostRequestJson()->description;

    // GroupTags
    $groupTags = getPostRequestJson()->tags;

    if (!$groupName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $uuid = $database->getStringValue("SELECT UUID()");

    $sqlRequest = "INSERT INTO `groups` ( group_id, user_id, name, description, tags ) VALUES ( UUID_TO_BIN( '$uuid', true ), UUID_TO_BIN( '$currentUserId', true ), '$groupName', '$description', '$groupTags' )";

    $isCompleted = $database->query($sqlRequest);

    if ($isCompleted) {

        $groupId = $uuid;

        // Make user access to objects: groups, group_users
        $sqlRequest = "INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
        VALUES ( UUID_TO_BIN( '$currentUserId', true ), UUID_TO_BIN( '$groupId', true ), 'groups', true, true, true, true )";

        $database->query($sqlRequest);

        $sqlRequest = "INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
        VALUES ( UUID_TO_BIN( '$currentUserId', true ), UUID_TO_BIN( '$groupId', true ), 'group_users', true, true, true, true )";

        $database->query($sqlRequest);

        // Log to database

        $results = '{"groupId": "' . $groupId . '"}';

        $rowsCount = 1;

        postSuccessResponseData($sessionId, $results, $rowsCount);

    } else {

        $databaseError = $database->getErrorMessage();
        $errorDatabaseError['name'] = $databaseError;
        postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
