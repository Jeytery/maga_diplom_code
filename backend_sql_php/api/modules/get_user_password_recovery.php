<?php

getUserValues();

// Check the number of attempts of userPasswordRecovery
checkUserAttempt(ATTEMPT_TYPE_USER_PASSWORD_RECOVERY);

if ($username) {

    $sqlRequest = "SELECT JSON_OBJECT ( 'userId', BIN_TO_UUID( user_id, true ), 'username', username, 'password', HEX(password), 'status', status ) FROM `users` WHERE username = '$username'";

    $jsonResponse = $database->getStringValue($sqlRequest);

    $currentUser = json_decode($jsonResponse);

} else {

    postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
}

if ($currentUser) {

    // Check user status
    $currentUserStatus = $currentUser->{'status'};
    checkUserStatus($currentUserStatus);

    $currentUserUsername = $currentUser->{'username'};

    // Send e-mail letter to username

    // Log to database

    // Save the number of attempts of userPasswordRecovery
    saveUserAttempt(ATTEMPT_TYPE_USER_PASSWORD_RECOVERY);

    postSuccessResponse($sessionId, $successHttpAccepted);

} else {

    postErrorResponse($sessionId, $errorHttpAuthRequired, $errorInvalidUpdateToken);
}

?>
