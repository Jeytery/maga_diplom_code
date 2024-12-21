<?php

$email = getRequestValue('email');

$sqlRequest = "SELECT BIN_TO_UUID( user_id, true) FROM `users` WHERE email = '$email'";

$userId = $database->getStringValue($sqlRequest);

if ($userId) {

    postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorUserEmailAlreadyExist);

} else {

    postSuccessResponse($sessionId, $successHttpAccepted);
}

?>
