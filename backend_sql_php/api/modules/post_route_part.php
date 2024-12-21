<?php

/*
    Insert Route Part
    Url: POST /service/api/v1/route/part
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'route_parts';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "routeId": <routeId>, "trackTypeId": <trackTypeId>, "name": <partName>, "description": <description>, "tags": <routeTags>, "latitude": <latitude>, "longitude": <longitude> }

    // RouteId
    $routeId = getPostRequestJson()->routeId;

    // TrackTypeId
    $trackTypeId = getPostRequestJson()->trackTypeId;

    // PartName
    $partName = getPostRequestJson()->name;

    // Description
    $description = getPostRequestJson()->description;

    // PartTags
    $partTags = getPostRequestJson()->tags;

    // Latitude
    $latitude = getPostRequestJson()->latitude;

    // Longitude
    $longitude = getPostRequestJson()->longitude;

    if (!$routeId || !isValidUUID($routeId) ||
        !$trackTypeId || !isValidUUID($trackTypeId) ||
        !$partName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    // Check the routeId. The default rule: Only the route owner could set part to route
    $routeCount = $database->getIntValue("SELECT COUNT(route_id) FROM `routes` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND route_id = UUID_TO_BIN( '$routeId', true )");

    if ($routeCount == 0) {

        postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
    }

    $uuid = $database->getStringValue("SELECT UUID()");

    $sqlRequest = "INSERT INTO `route_parts` ( route_part_id, route_id, track_type_id, name, description, tags, latitude, longitude ) VALUES ( UUID_TO_BIN( '$uuid', true ), UUID_TO_BIN( '$routeId', true ), UUID_TO_BIN( '$trackTypeId', true ), '$partName', '$description', '$partTags', $latitude, $longitude )";

    $isCompleted = $database->query($sqlRequest);

    if ($isCompleted) {

        $routePartId = $uuid;

        // Log to database

        $results = '{"routePartId": "' . $routePartId . '"}';

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
