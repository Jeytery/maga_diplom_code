<?php

/*
    Insert User with Apple
    Url: POST /service/api/v1/user/register/apple
         { "idToken": <idToken> }

    Firebase for PHP
    https://firebase-php.readthedocs.io/en/7.12.0/overview.html
 */

require_once DEFAULT_DIR . LIBRARIES_DIR . 'google-api-php-client' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once DEFAULT_DIR . LIBRARIES_DIR . 'firebase-php-7.12.0' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

$idToken = getPostRequestJson()->idToken;

if (!$idToken) {

    postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
}

if ($deviceType == IOS || $deviceType == ANDROID) {

    $oauthClient = DEFAULT_DIR . FILES_DIR . 'firebase-oauth-client.json';
    if (!file_exists($oauthClient)) {
        writeToLog($logFile, "Error|Firebase OAuth ClientId file not exists=" . $oauthClient . "|#" . $sessionId);
    }

    $jsonContent = file_get_contents($oauthClient);
    if ($jsonContent === false) {
        writeToLog($logFile, "Error|Firebase OAuthClientId not ready|#" . $sessionId);
    }

    $jsonData = json_decode($jsonContent, true);
    if ($jsonData === null) {
        writeToLog($logFile, "Error|Firebase OAuthClientId data failed|#" . $sessionId);
    }

    $clientId = $jsonData['web']['client_id'];
    $client = new Google_Client(['client_id' => $clientId]);
    $payload = $client->verifyIdToken($idToken);

    if ($payload) {
        $googleUserId = $payload['sub'];
        $email = $payload['email'];
        $name = $payload['name'];
    } else {
        writeToLog($logFile, "Error|Invalid token|#" . $sessionId);
    }

} else {

    $serviceAccount = DEFAULT_DIR . FILES_DIR . 'firebase-service-account.json';
    if (!file_exists($serviceAccount)) {
        writeToLog($logFile, "Error|Firebase ServiceAccount file not exists=" . $serviceAccount . "|#" . $sessionId);
    }
    $factory = (new Factory)->withServiceAccount($serviceAccount);
    $auth = $factory->createAuth();

    try {
        $verifiedIdToken = $auth->verifyIdToken($idToken);
        $appleUserId = $verifiedIdToken->claims()->get('sub');
        $email = $verifiedIdToken->claims()->get('email');
        $name = $verifiedIdToken->claims()->get('name');
    } catch (FailedToVerifyToken $e) {
        writeToLog($logFile, "Error|Invalid token|#" . $sessionId . "|Error=" . $e->getMessage());
    }
}

if (!$appleUserId) {

    // Save the number of attempts of userAuth
    saveUserAttempt(ATTEMPT_TYPE_USER_AUTH);

    postErrorResponse($sessionId, $errorHttpAuthRequired, $errorAuthFailed);
}

$userAlreadyRegistered = $database->getIntValue("SELECT COUNT(*) FROM `object_options` oo WHERE oo.object_name = 'users' AND oo.name = 'appleUserId' AND oo.value = '$appleUserId'");

if ($userAlreadyRegistered == 1) {

    // Login
    $sqlRequest = "SELECT JSON_OBJECT ( 'userId', BIN_TO_UUID( u.user_id, true ), 'roleId', BIN_TO_UUID( u.role_id, true ), 'username', u.username, 'password', HEX(u.password), 'status', u.status, 'accessTokenCreated', UNIX_TIMESTAMP ( u.access_token_created ), 'accessToken', u.access_token, 'updateToken', u.update_token ) FROM `users` u, `object_options` oo WHERE u.user_id = oo.object_id AND oo.object_name = 'users' AND oo.name = 'appleUserId' AND oo.value = '$appleUserId'";

    $jsonResponse = $database->getStringValue($sqlRequest);

    $currentUser = json_decode($jsonResponse);

    // Check user status
    $currentUserStatus = $currentUser->{'status'};
    checkUserStatus($currentUserStatus);

    $currentUserUsername = $currentUser->{'username'};
    $accessToken = $currentUser->{'accessToken'};
    $updateToken = $currentUser->{'updateToken'};
    $tokenCreated = $currentUser->{'accessTokenCreated'};

    $username = $currentUserUsername;

    $isActualToken = isActualToken($tokenCreated);

    if (!$isActualToken) {

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

    // Send notification via Support service

    $results = '{"accessToken":"' . $accessToken . '", "updateToken":"' . $updateToken . '"}';

    $rowsCount = 1;
    
    postSuccessResponseData($sessionId, $results, $rowsCount);

} else {

    // Register
    // Check the number of attempts of userRegister
    checkUserAttempt(ATTEMPT_TYPE_USER_REGISTER);

    $firstName = getParam($name, ' ');
    $lastName = getValue($name, ' ');
    $username = $email;
    $password = getTokenOrPasswordData($googleUserId);

    if ($password) {
        $password = getUserPasswordHash($password);
    }    

    if (!$email || !$username || !$password) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userRoleName = USER_ROLE_NAME_CUSTOMER;
    $userRoleId = $database->getStringValue("SELECT BIN_TO_UUID( role_id, true) FROM `roles` WHERE name = '$userRoleName'");

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

        // User option Registration with Apple
        $registration = APPLE:
        $sqlRequest = "INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
        VALUES ( UUID_TO_BIN( '$userId', true ), 'users', 'registration', '$registration', 'string')";

        $database->query($sqlRequest);

        // User option Apple UserId
        $sqlRequest = "INSERT INTO `object_options` ( object_id, object_name, name, value, option_type )
        VALUES ( UUID_TO_BIN( $userId, true ), 'users', 'appleUserId', '$appleUserId', 'string')";

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
}

?>
