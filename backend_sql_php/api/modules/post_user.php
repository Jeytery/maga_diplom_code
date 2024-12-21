<?php

/*
    Insert User
    Url: POST /service/api/v1/user
 */

getUserValues();

// Check the number of attempts of userRegister
checkUserAttempt(ATTEMPT_TYPE_USER_REGISTER);

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

$userRoleName = USER_ROLE_NAME_CUSTOMER;
$userRoleId = $database->getStringValue("SELECT BIN_TO_UUID( role_id, true ) FROM `roles` WHERE name = '$userRoleName'");

$userStatus = USER_STATUS_ACTIVATED;

$uuid = $database->getStringValue("SELECT UUID()");

$sqlRequest = "INSERT INTO `users` ( user_id, username, password, first_name, last_name, email, phone, role_id, status ) VALUES ( UUID_TO_BIN( '$uuid', true ), '$username', UNHEX( '$password' ), '$firstName', '$lastName', '$email', '$phone', UUID_TO_BIN( '$userRoleId', true ), $userStatus )";

$isCompleted = $database->query($sqlRequest);

if ($isCompleted) {

    $userId = $uuid;

    // Make new access token (encrypt)
    if ($useJwtToken) {

        require_once DEFAULT_DIR . INCLUDES_DIR . $fileJwt;

        getTokenExpiredTime();
        $accessToken = generateToken($userId, $userRoleId, $username, $tokenExpiredTime);

    } else {

        $accessToken = getTokenOrPasswordData( $username, 64 );
        $accessToken = getBase64EncodedData( $username, $accessToken );
    }

    // Make new update token
    $updateToken = getTokenOrPasswordData( $username, 64 );
    $updateToken = getBase64EncodedData( $username, $updateToken );

    // Save to database
    $sqlRequest = "UPDATE `users` SET access_token = '$accessToken', update_token = '$updateToken', access_token_created = CURRENT_TIMESTAMP WHERE username = '$username'";

    $database->query($sqlRequest);

    // Make user access to object
    $sqlRequest = "INSERT INTO `user_privileges` ( user_id, object_id, object_name, can_select, can_insert, can_update, can_delete )
    VALUES ( UUID_TO_BIN( '$userId', true ), UUID_TO_BIN( '$userId', true ), 'users', true, true, true, true )";

    $database->query($sqlRequest);

    // User option Registration
    $registration = $deviceType . ' ' . APP;
    $sqlRequest = "INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
    VALUES ( UUID_TO_BIN( '$userId', true ), 'users', 'registration', '$registration', 'string')";

    $database->query($sqlRequest);

    // Log to database

    // Send notification via Support service

    // Save the number of attempts of userRegister
    saveUserAttempt(ATTEMPT_TYPE_USER_REGISTER);

    $results = '{"accessToken": "' . $accessToken . '", "updateToken": "' . $updateToken . '"}';

    $rowsCount = 1;

    postSuccessResponseData($sessionId, $results, $rowsCount);

} else {

    $databaseError = $database->getErrorMessage();
    $errorDatabaseError['name'] = $databaseError;
    postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
}

?>
