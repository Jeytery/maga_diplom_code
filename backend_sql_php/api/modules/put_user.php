<?php

/*
    Update User
    Url: PUT /service/api/v1/user
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'users';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_UPDATE} == true;

if ($roleAccessAllowed) {

    // { "firstName": <firstname>, "lastName": <lastname>, "email": <email>, "phone": <phone> }

    // FirstName
    $firstName = getPostRequestJson()->firstName;

    // LastName
    $lastName = getPostRequestJson()->lastName;

    // Email
    $email = getPostRequestJson()->email;

    // Phone
    $phone = getPostRequestJson()->phone;

    if (!$email || !isValidEmail($email)) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records

    if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) { // Admin access

        // UserId { "userId": <userId> }
        $userId = getPostRequestJson()->userId;

        // RoleId { "roleId": <roleId> }
        $roleId = getPostRequestJson()->roleId;
        if ($roleId && isValidUUID($roleId)) {
            $roleId = ", role_id = UUID_TO_BIN( '" . $roleId . "', true )";
        }

        // Status { "status": <status> } [0, 1, -1, -2, -10, -11]
        $status = getPostRequestJson()->status;
        if ($status) {
            $status = ", status = " . $status;
        }

        // Admin should provide the Id of the user for update
        if (!$userId || !isValidUUID($userId)) {

            postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $currentUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_UPDATE}) {

            $userId = $currentUserId;

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Update
        $sqlRequest = "UPDATE `users` SET first_name = '$firstName', last_name = '$lastName', email = '$email', phone = '$phone'" . $roleId . $status . " WHERE user_id = UUID_TO_BIN( '$userId', true )";

        $isCompleted = $database->query($sqlRequest);

        if ($isCompleted) {

            // Log to database

            // Send notification via Support scheme

            postSuccessResponse($sessionId, $successHttpAccepted);

        } else {

            $databaseError = $database->getErrorMessage();
            $errorDatabaseError['name'] = $databaseError;
            postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
        }

    } else {

        postErrorResponse($sessionId, $errorHttpForbidden, $errorUserPrivNotFound);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
