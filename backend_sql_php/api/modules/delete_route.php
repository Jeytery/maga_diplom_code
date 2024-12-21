<?php

/*
    Remove Route
    Url: DELETE /service/api/v1/route
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

    // RouteId
    $routeId = getRequestValue('routeId');

    if (!$routeId) {

        // { "routeId": <routeId> }
        $routeId = getPostRequestJson()->routeId;
    }

    if (!$routeId || !isValidUUID($routeId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) { // Admin access

        $userAccessAllowed = true;

    } else {  // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $routeId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_DELETE}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Remove old picture
        removePictureFromStorage($objectName, $routeId, 'route_id');

        $sqlRequest = "DELETE FROM `routes` WHERE route_id = UUID_TO_BIN( '$routeId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Remove object options
            $sqlRequest = "DELETE FROM `object_options` WHERE object_id = UUID_TO_BIN( '$routeId', true ) AND object_name = 'routes'";

            $database->query($sqlRequest);

            // Remove users privileges to object
            $sqlRequest = "DELETE FROM `user_privileges` WHERE object_name = 'routes' AND object_id = UUID_TO_BIN( '$routeId', true )";

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
