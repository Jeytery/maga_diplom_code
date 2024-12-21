<?php

/*
    Remove Route Part
    Url: DELETE /service/api/v1/route/part
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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_DELETE} == true;

if ($roleAccessAllowed) {

    // RoutePartId
    $routePartId = getRequestValue('routePartId');

    if (!$routePartId) {

        // { "routePartId": <routePartId> }
        $routePartId = getPostRequestJson()->routePartId;
    }

    if (!$routePartId || !isValidUUID($routePartId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        $userAccessAllowed = true;

    } else {  // Customer access

        // Check the routeId
        $routeId = $database->getStringValue("SELECT BIN_TO_UUID( r.route_id, true ) FROM `routes` r, `route_parts` rp WHERE rp.route_part_id = UUID_TO_BIN( '$routePartId', true) AND rp.route_id = r.route_id");

        if (!$routeId) {

            postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
        }

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $routeId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        $objectName = 'route_parts';

        // Remove old picture
        removePictureFromStorage($objectName, $routePartId, 'route_part_id');

        $sqlRequest = "DELETE FROM `route_parts` WHERE route_part_id = UUID_TO_BIN( '$routePartId', true)";

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
