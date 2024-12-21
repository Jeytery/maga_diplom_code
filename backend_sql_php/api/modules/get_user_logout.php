<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Save to database: Make empty access token, update token
$sqlRequest = "UPDATE `users` SET access_token = NULL, update_token = '', access_token_created = '' WHERE username = '$currentUserUsername'";

$database->query($sqlRequest);

clearUserAttempts(ATTEMPT_TYPE_USER_AUTH);

// Log to database

postSuccessResponse($sessionId, $successHttpResetContent);

?>
