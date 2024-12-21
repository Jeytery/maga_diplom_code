<?php

/*
    Insert Admin privileges
    Url: POST /service/api/v1/admin/privileges
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$rolePrivObjectName = 'role_privileges';
$userPrivObjectName = 'user_privileges';
$objectId = 0;
getRoleAndUserPrivileges($rolePrivObjectName, $objectId, $userPrivObjectName);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "userId": <userId> }
    $userId = getPostRequestJson()->userId;

    if (!$userId || !isValidUUID($userId)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $adminRoleName = USER_ROLE_NAME_ADMIN;
    $adminRoleId = $database->getStringValue("SELECT BIN_TO_UUID( role_id, true ) FROM `roles` WHERE name = '$adminRoleName'");

    // Set admin role
    $sqlRequest = "UPDATE `users` SET role_id = UUID_TO_BIN( '" . $roleId . "', true ) WHERE user_id = UUID_TO_BIN( '$userId', true )";

    $database->query($sqlRequest);

    // Set admin privileges
    $sqlRequest = "INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
    VALUES ( UUID_TO_BIN( '$userId', true ), 0x0, 'user_privileges', true, true, true, true ), 
    ( UUID_TO_BIN( '$userId', true ), 0x0, 'users', true, true, true, true ),
    ( UUID_TO_BIN( '$userId', true ), 0x0, 'sensors', true, true, true, true ),
    ( UUID_TO_BIN( '$userId', true ), 0x0, 'tracks', true, true, true, true ),
    ( UUID_TO_BIN( '$userId', true ), 0x0, 'routes', true, true, true, true ),
    ( UUID_TO_BIN( '$userId', true ), 0x0, 'groups', true, true, true, true );";

    $isCompleted = $database->$database->query($sqlRequest);

    if ($isCompleted) {

        postSuccessResponse($sessionId, $successHttpCreated);

    } else {

        $databaseError = $database->getErrorMessage();
        $errorDatabaseError['name'] = $databaseError;
        postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
