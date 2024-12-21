<?php

$username = getRequestValue('username');

$sqlRequest = "SELECT BIN_TO_UUID( user_id, true) FROM `users` WHERE username = '$username'";

$userId = $database->getStringValue($sqlRequest);

if ($userId) {

    postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorUserUsernameAlreadyExist);

} else {

    postSuccessResponse($sessionId, $successHttpAccepted);
}

?>
