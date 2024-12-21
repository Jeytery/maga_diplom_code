<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$rolePrivObjectName = 'sensors';
$userPrivObjectName = 'users';
$objectId = 0;
getRoleAndUserPrivileges($rolePrivObjectName, $objectId, $userPrivObjectName);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // Find by UserId
    $userId = getRequestValue('userId');
    if ($userId && isValidUUID($userId)) {

        $findUserId = $userId;
        $isWhereFounded = true;
        $userId = getWhereParam('`users`.user_id', false, 'UUID_TO_BIN( \'' . $userId . '\', true )');

    } else {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $userPrivObjectName, 0); // 0 Access to any records
    
    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $userPrivObjectName, $findUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

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
            } else {
                $offsetRows = " LIMIT $rows";
            }
        }

        $sort = getRequestValue('sort');

        if ($sort) {
          $sort = " $sort";
        }

        $results = $database->getStringList(
        "SELECT JSON_OBJECT (
            'sensorId', BIN_TO_UUID( `sensors`.sensor_id, true ),
            'name', `sensors`.name,
            'serialNumber', `sensors`.serial_number,
            'deviceName', `sensors`.device_name,
            'phone', `sensors`.phone,
            'lastUpdatedDate', `sensors`.last_updated,
            'createdAtDate', `sensors`.created_at,
            'lastUpdated', UNIX_TIMESTAMP( `sensors`.last_updated ),
            'createdAt', UNIX_TIMESTAMP( `sensors`.created_at ),
            'sensorOptions', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '{',
                GROUP_CONCAT( '\"', `object_options`.name, '\": ',
                    IF( `object_options`.option_type = 'number' OR `object_options`.option_type = 'boolean',
                        `object_options`.value, CONCAT( '\"', `object_options`.value, '\"' )
                    )
                ),
            '}' )
            FROM `object_options`
            WHERE `sensors`.sensor_id = `object_options`.object_id AND `object_options`.object_name = 'sensors'
            ), '{}'), '$')
        ) as results            
        FROM `sensors`, `users`
        WHERE `sensors`.user_id = `users`.user_id" . $userId . "
        ORDER BY `sensors`.sensor_id" . $sort . $offsetRows);

        if ($results) {

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
