<?php

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
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records
    
    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        $findUsername = getRequestValue('findUsername');
        if ($findUsername) {
            $findUsername = getWhereParam('username', true, $findUsername, true);
        }

        $userId = getRequestValue('userId');
        if ($userId && isValidUUID($userId)) {
            $userId = getWhereParam('user_id', false, 'UUID_TO_BIN( \'' . $userId . '\', true )');
        } else {
            $userId = "";
        }

        // First name | Last name
        $findName = getRequestValue('findName');
        if ($findName) {
            $findName = getWhereExpression("MATCH ( first_name, last_name ) AGAINST ( '" . $findName . "' )");
        }

        $email = getRequestValue('email');
        if ($email) {
            $email = getWhereParam('email', true, $email);
        }

        $phone = getRequestValue('phone');
        if ($phone) {
            $phone = getWhereParam('phone', true, $phone);
        }

        $status = getRequestValue('status');
        if ($status) {
            $status = getWhereParam('status', false, $status);
        }

        if (!$findUsername && !$userId && !$findName && !$email && !$phone && !$status) {
            $findUsername = getWhereParam('username', false, $currentUserUsername, true);
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $currentUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

            $findUsername = getWhereParam('username', false, $currentUserUsername, true);

            $userId = "";
            $findName = "";
            $email = "";
            $phone = "";
            $status = "";

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // Offset & Rows
        $offset = getRequestValue('offset');
        $rows = getRequestValue('rows');

        $offsetRows = "";

        if ($rows) {

            if ($offset) {
                $offsetRows = " LIMIT $offset, $rows";
            }
            else {
                $offsetRows = " LIMIT $rows";
            }
        }

        $sort = getRequestValue('sort');

        if ($sort) {
          $sort = " $sort";
        }

        $results = $database->getStringList(
        "SELECT JSON_OBJECT (
            'userId', BIN_TO_UUID( user_id, true ),
            'firstName', first_name,
            'lastName', last_name,
            'userEmail', email,
            'userPhone', phone,
            'hasPicture', picture IS NOT NULL,
            'pictureLink', get_picture_link( picture, 'users' ),
            'status', status,
            'activated', status = 1,
            'lastUpdatedDate', last_updated,
            'createdAtDate', created_at,
            'lastUpdated', UNIX_TIMESTAMP( last_updated ),
            'createdAt', UNIX_TIMESTAMP( created_at ),
            'userOptions', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '{',
                GROUP_CONCAT( '\"', `object_options`.name, '\": ',
                    IF( `object_options`.option_type = 'number' OR `object_options`.option_type = 'boolean',
                        `object_options`.value, CONCAT( '\"', `object_options`.value, '\"' )
                    )
                ),
            '}' )
            FROM `object_options`
            WHERE `users`.user_id = `object_options`.object_id AND `object_options`.object_name = 'users'
            ), '{}'), '$')
        ) as results
        FROM `users`" . $findUsername . $userId . $findName . $email . $phone . $status . "
        ORDER BY user_id" . $sort . $offsetRows);

        if ($results) {

            // Debug!
            // Send Push notification ({ "userId": <userId>, "sensorId": <sensorId>, "templateName": <templateName>, "data": <data>, "substitutions": <substitutions>, "schedule" = <schedule> })
            $dataObject = [
                "userId" => $currentUserId,
                "sensorId" => "",
                "templateName" => "testPushNotification",
                "data" => "userId = " . $currentUserId, //'{ "userId": "' . $currentUserId . '" }',
                "substitutions" => "",
                "schedule" => ""
            ];
            $adminToken = getAdminToken(UUID_ADMIN_ACCESS);
            sendRequest(SUPPORT_PUSH_NOTIFICATION, 'POST', $adminToken, $dataObject);

            $foundRows = $database->getIntValue("SELECT FOUND_ROWS()");
            postSuccessResponseData($sessionId, $results, $foundRows);

        } else {

            $databaseError = $database->getErrorMessage();

            if ($databaseError) {

                $errorDatabaseError['name'] = $databaseError;
                postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);

            } else {

                postSuccessResponse($sessionId, $successHttpNoContent);
            }
        }

    }  else {

        postErrorResponse($sessionId, $errorHttpForbidden, $errorUserPrivNotFound);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
