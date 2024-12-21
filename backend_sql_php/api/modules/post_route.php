<?php

/*
    Insert Route
    Url: POST /service/api/v1/route
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'routes';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "routeTypeId": <routeTypeId>, "groupId": <groupId>, "name": <routeName>, "description": <description>, "tags": <routeTags>, "started": <startedTime>, "finished": <finishedTime> }

    // RouteTypeId
    $routeTypeId = getPostRequestJson()->routeTypeId;

    // GroupId
    $groupId = getPostRequestJson()->groupId;

    // RouteName
    $routeName = getPostRequestJson()->name;

    // Description
    $description = getPostRequestJson()->description;

    // RouteTags
    $routeTags = getPostRequestJson()->tags;

    // StartedTime
    $startedTime = getPostRequestJson()->started;

    // FinishedTime
    $finishedTime = getPostRequestJson()->finished;

    if (!$routeTypeId  || !isValidUUID($routeTypeId) ||
        !$routeName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    // Check the groupId. The default rule: Only the group owner could set group to route
    $groupCount = $database->getIntValue("SELECT COUNT(group_id) FROM `groups` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND group_id = UUID_TO_BIN( '$groupId', true )");

    if ($groupCount == 0) {

        postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
    }

    $uuid = $database->getStringValue("SELECT UUID()");

    $sqlRequest = "INSERT INTO `routes` ( route_id, route_type_id, user_id, group_id, name, description, tags, started, finished ) VALUES ( UUID_TO_BIN( '$uuid', true ), UUID_TO_BIN( '$routeTypeId', true ), UUID_TO_BIN( '$currentUserId', true ), UUID_TO_BIN( '$groupId', true ), '$routeName', '$description', '$routeTags', $startedTime, $finishedTime )";

    $isCompleted = $database->query($sqlRequest);

    if ($isCompleted) {

        $routeId = $uuid;

        // Make user access to object
        $sqlRequest = "INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
        VALUES ( UUID_TO_BIN( '$currentUserId', true ), UUID_TO_BIN( '$routeId', true ), 'routes', true, true, true, true )";

        $database->query($sqlRequest);

        // Log to database

        $results = '{"routeId": "' . $routeId . '"}';

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
