<?php

/*
    Insert User
    Url: POST /service/api/v1/user
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the number of attempts of userRegister
checkUserAttempt(ATTEMPT_TYPE_USER_REGISTER);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'users';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "inviteCode": <inviteCode>, "username": <username>, "password": <password>, "firstName": <firstname>, "lastName": <lastname>, "email": <email>, "phone": <phone> }
    //$inviteCode = getPostRequestJson()->inviteCode; // Special code for the registration
    $firstName = getPostRequestJson()->firstName;
    $lastName = getPostRequestJson()->lastName;
    $email = getPostRequestJson()->email;
    $phone = getPostRequestJson()->phone;

    $username = getPostRequestJson()->username;
    $password = getPostRequestJson()->password;

    if ($password) {
        $password = getUserPasswordHash($password);
    }

    if (!$email || !isValidEmail($email) ||
        !$username || !$password) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userRoleName = USER_ROLE_NAME_ADMIN;
    $userRoleId = $database->getStringValue("SELECT BIN_TO_UUID( role_id, true ) FROM `roles` WHERE name = '$userRoleName'");

    $userStatus = USER_STATUS_ACTIVATED;

    $uuid = $database->getStringValue("SELECT UUID()");

    $sqlRequest = "INSERT INTO `users` ( user_id, username, password, first_name, last_name, email, phone, role_id, status ) VALUES ( UUID_TO_BIN( '$uuid', true ), '$username', UNHEX( '$password' ), '$firstName', '$lastName', '$email', '$phone', UUID_TO_BIN( '$userRoleId', true ), $userStatus )";

    $isCompleted = $database->query($sqlRequest);

    if ($isCompleted) {

        $userId = $uuid;

        // Log to database

        // Save the number of attempts of userRegister
        saveUserAttempt(ATTEMPT_TYPE_USER_REGISTER);

        $results = '{"userId": "' . $userId . '"}';

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
