<?php

getUserValues();

// Check the number of attempts of userToken
checkUserAttempt(ATTEMPT_TYPE_USER_TOKEN);

$token = getHeaderValue('Authorization'); //getAuthorizationHeader();

if (!$token) {

    $token = getRequestValue('token'); // Request update token

} else {

    $token = getBearerToken($token); // Header update token
}

if ($token) {

    $sqlRequest = "SELECT JSON_OBJECT ( 'userId', BIN_TO_UUID( user_id, true ), 'roleId', BIN_TO_UUID( role_id, true ), 'username', username, 'password', HEX(password), 'status', status ) FROM `users` WHERE update_token = '$token'";

    $jsonResponse = $database->getStringValue($sqlRequest);

    $currentUser = json_decode($jsonResponse);

} else {

    postErrorResponse($sessionId, $errorHttpAuthRequired, $errorAuthFailed);
}

if ($currentUser) {

    // Check user status
    $currentUserStatus = $currentUser->{'status'};
    checkUserStatus($currentUserStatus);

    $currentUserUsername = $currentUser->{'username'};

    // Make new access token (encrypt)
    if ($useJwtToken) {

        require_once DEFAULT_DIR . INCLUDES_DIR . $fileJwt;

        getTokenExpiredTime();
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
    $sqlRequest = "UPDATE `users` SET access_token = '$accessToken', update_token = '$updateToken', access_token_created = CURRENT_TIMESTAMP WHERE username = '$currentUserUsername'";
    
    $database->query($sqlRequest);

    clearUserAttempts(ATTEMPT_TYPE_USER_TOKEN);

    // Log to database

    $results = '{"accessToken": "' . $accessToken . '", "updateToken": "' . $updateToken . '"}';

    $rowsCount = 1;

    postSuccessResponseData($sessionId, $results, $rowsCount);

} else {

    // Save the number of attempts of userToken
    saveUserAttempt(ATTEMPT_TYPE_USER_TOKEN);

    postErrorResponse($sessionId, $errorHttpAuthRequired, $errorInvalidUpdateToken);
}

?>
