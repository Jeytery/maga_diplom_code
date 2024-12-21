<?php

/*
    Update Route
    Url: PUT /service/api/v1/route
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "routeId": <routeId>, "routeTypeId": <routeTypeId>, "groupId": <groupId>, "name": <routeName>, "description": <description>, "tags": <routeTags>, "started": <startedTime>, "finished": <finishedTime> }

    // RouteId
    $routeId = getPostRequestJson()->routeId;

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

    if (!$routeId || !isValidUUID($routeId) ||
        !$routeTypeId || !isValidUUID($routeTypeId) ||
        !$routeName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        // UserId { "userId": userId }
        $userId = getPostRequestJson()->userId;
        if ($userId && isValidUUID($userId)) {
            $userId = ", user_id = UUID_TO_BIN( '" . $userId . "', true )";
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $routeId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $routeGroupId = $database->getStringValue("SELECT BIN_TO_UUID( group_id, true ) FROM `routes` WHERE route_id = UUID_TO_BIN( '$routeId', true )");

            if ($routeGroupId != $groupId) { // Set group to route

                // Check the groupId. The default rule: Only the group owner could set group to route
                $groupCount = $database->getIntValue("SELECT COUNT(group_id) FROM `groups` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND group_id = UUID_TO_BIN( '$groupId', true )");

                if ($groupCount == 0) {

                    postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
                }
            }

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Update
        $sqlRequest = "UPDATE `routes` SET route_type_id = UUID_TO_BIN( '$routeTypeId', true ), group_id = UUID_TO_BIN( '$groupId', true ), name = '$routeName', description = '$description', tags = '$routeTags', started = $startedTime, finished = $finishedTime" . $userId . " WHERE route_id = UUID_TO_BIN( '$routeId', true )";

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
