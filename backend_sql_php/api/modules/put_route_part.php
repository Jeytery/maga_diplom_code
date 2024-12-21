<?php

/*
    Update Route Part
    Url: PUT /service/api/v1/route/part
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

    // { "routePartId": <routePartId>, "trackTypeId": <trackTypeId>, "name": <partName>, "description": <description>, "tags": <routeTags> }

    // RoutePartId
    $routePartId = getPostRequestJson()->routePartId;

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

    if (!$routePartId || !isValidUUID($routePartId) ||
        !$trackTypeId || !isValidUUID($trackTypeId) ||
        !$partName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        // Check the routeId
        $routeId = $database->getStringValue("SELECT BIN_TO_UUID( r.route_id, true ) FROM `routes` r, `route_parts` rp WHERE rp.route_part_id = UUID_TO_BIN( '$routePartId', true ) AND rp.route_id = r.route_id");

        if (!$routeId) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $routeId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Update
        $sqlRequest = "UPDATE `route_parts` SET track_type_id = UUID_TO_BIN( '$trackTypeId', true ), name = '$partName', description = '$description', tags = '$partTags', latitude = $latitude, longitude = $longitude WHERE route_part_id = UUID_TO_BIN( '$routePartId', true )";

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
