<?php

/*
    Update Group
    Url: PUT /service/api/v1/group
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

    // { "groupId": <groupId>, "name": <groupName>, "description": <description>, "tags": <groupTags> }

    // GroupId
    $groupId = getPostRequestJson()->groupId;

    // GroupName
    $groupName = getPostRequestJson()->name;

    // Description
    $description = getPostRequestJson()->description;

    // GroupTags
    $groupTags = getPostRequestJson()->tags;

    if (!$groupId || !isValidUUID($groupId) ||
        !$groupName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;
        if ($userId && isValidUUID($userId)) {
            $userId = ", user_id = UUID_TO_BIN( '" . $userId . "', true )";
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $groupId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Update
        $sqlRequest = "UPDATE `groups` SET name = '$groupName', description = '$description', tags = '$groupTags'" . $userId . " WHERE group_id = UUID_TO_BIN( '$groupId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Log to database

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
