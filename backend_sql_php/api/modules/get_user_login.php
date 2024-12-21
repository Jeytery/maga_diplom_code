<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

if ($username) {

    $sqlRequest = "SELECT JSON_OBJECT ( 'userId', BIN_TO_UUID( user_id, true ), 'roleId', BIN_TO_UUID( role_id, true ), 'username', username, 'password', HEX(password), 'status', status, 'accessTokenCreated', UNIX_TIMESTAMP ( access_token_created ), 'accessToken', access_token, 'updateToken', update_token ) FROM `users` WHERE username = '$username' AND password = UNHEX( '$password' )";

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
    $accessToken = $currentUser->{'accessToken'};
    $updateToken = $currentUser->{'updateToken'};
    $tokenCreated = $currentUser->{'accessTokenCreated'};

    $isActualToken = isActualToken($tokenCreated);

    if (!$isActualToken || !$accessToken || !$updateToken) {

        // Make new access token (encrypt)
        if ($useJwtToken) {

            require_once DEFAULT_DIR . INCLUDES_DIR . $fileJwt;

            $currentUserId = $currentUser->{'userId'};
            $currentRoleId = $currentUser->{'roleId'};
            $accessToken = generateToken($currentUserId, $currentRoleId, $currentUserUsername, $tokenExpiredTime);

        } else {

            $accessToken = getTokenOrPasswordData( $currentUserUsername, 64 );
            $accessToken = getBase64EncodedData( $currentUserUsername, $accessToken );
        }

        // Make new update token
        $updateToken = getTokenOrPasswordData( $currentUserUsername, 64 );
        $updateToken = getBase64EncodedData( $currentUserUsername, $updateToken );

        // Save to database
        $sqlRequest = "UPDATE `users` SET access_token = '$accessToken', update_token = '$updateToken', access_token_created = CURRENT_TIMESTAMP WHERE username = '$username'";

        $database->query($sqlRequest);
    }

    clearUserAttempts(ATTEMPT_TYPE_USER_AUTH);

    // Log to database

    $results = '{"accessToken":"' . $accessToken . '", "updateToken":"' . $updateToken . '"}';

    $rowsCount = 1;
    
    postSuccessResponseData($sessionId, $results, $rowsCount);

} else {

    // Save the number of attempts of userAuth
    saveUserAttempt(ATTEMPT_TYPE_USER_AUTH);

    postErrorResponse($sessionId, $errorHttpAuthRequired, $errorInvalidUsernameOrPassword);
}

?>
