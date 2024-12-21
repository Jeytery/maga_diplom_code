<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'user_privileges';
$objectId = null;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // $userId
    // $objectId
    // $objectName

    $sort = getRequestValue('sort');

    if ($sort) {
      $sort = " $sort";
    }

    $results = $database->getString(
    "SELECT JSON_OBJECT (
        'userId', BIN_TO_UUID( user_id, true ),
        'objectId', BIN_TO_UUID( object_id, true ),
        'objectName', object_name,
        'canSelect', can_select = 1,
        'canInsert', can_insert = 1,
        'canUpdate', can_update = 1,
        'canDelete', can_delete = 1,
        'createdAtDate', created_at,
        'createdAt', UNIX_TIMESTAMP( created_at )
    ) as results
    FROM `user_privileges`");

    if ($results) {

        $foundRows = 1;
        postSuccessResponseData($sessionId, $results, $foundRows);

    } else {

        $databaseError = $database->getErrorMessage();

        if ($databaseError) {

            $errorDatabaseError['name'] = $databaseError;
            postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);

        } else {

            postSuccessResponse($sessionId, $successHttpNoContent);
        }
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
