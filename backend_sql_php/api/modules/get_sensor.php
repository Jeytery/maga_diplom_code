<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'sensors';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // Find by SensorId
    $sensorId = getRequestValue('sensorId');
    if ($sensorId && isValidUUID($sensorId)) {
        $findSensorId = $sensorId;
        $sensorId = getWhereParam('sensor_id', false, 'UUID_TO_BIN( \'' . $sensorId . '\', true )');
    } else {
        $sensorId = "";
    }

    $sensorName = getRequestValue('name');
    if ($sensorName) {
        $sensorName = getWhereParam('name', true, $sensorName);
    }

    $deviceName = getRequestValue('deviceName');
    if ($deviceName) {
        $deviceName = getWhereParam('deviceName', true, $deviceName);
    }

    $phone = getRequestValue('phone');
    if ($phone) {
        $phone = getWhereParam('deviceName', true, $phone);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records
    
    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        $userId = getRequestValue('userId');
        if ($userId && isValidUUID($userId)) {
            $userId = getWhereParam('user_id', false, 'UUID_TO_BIN( \'' . $userId . '\', true )');
        } else {
            $userId = "";
        }

        $userAccessAllowed = true;

    } else { // Customer access

        if ($sensorId) { // Find by SensorId

            $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $findSensorId);

            if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

                $userAccessAllowed = true;
            }

        } else { // Find by UserId

            // Seek the sensors object provileges
            $sensorsIds = getUserAccessIdsToObject($objectName, "can_select");
            if ($sensorsIds) {
                $sensorId = getWhereExpression("sensor_id IN (" . $sensorsIds . ")");
            }

            $userAccessAllowed = true;
        }

        // Seek the sensors of the user
        $userId = getWhereParam('user_id', false, 'UUID_TO_BIN( \'' . $currentUserId . '\', true )');
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
            'sensorId', BIN_TO_UUID( sensor_id, true ),
            -- User
            'user', JSON_EXTRACT( IFNULL( (
            SELECT JSON_OBJECT (
                'userId', BIN_TO_UUID( `users`.user_id, true ),
                'firstName', `users`.first_name,
                'lastName', `users`.last_name,
                'userEmail', `users`.email,
                'userPhone', `users`.phone,
                'hasPicture', `users`.picture IS NOT NULL,
                'pictureLink', get_picture_link( `users`.picture, 'users' ),
                'status', `users`.status,
                'activated', `users`.status = 1,
                'lastUpdatedDate', `users`.last_updated,
                'createdAtDate', `users`.created_at,
                'lastUpdated', UNIX_TIMESTAMP( `users`.last_updated ),
                'createdAt', UNIX_TIMESTAMP( `users`.created_at )
            )
            FROM `users`
            WHERE `users`.user_id = `sensors`.user_id
            ), '{}'), '$'),
            'name', name,
            'serialNumber', serial_number,
            'deviceName', device_name,
            'phone', phone,
            'lastUpdatedDate', last_updated,
            'createdAtDate', created_at,
            'lastUpdated', UNIX_TIMESTAMP( last_updated ),
            'createdAt', UNIX_TIMESTAMP( created_at ),
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
        FROM `sensors`" . $sensorId . $sensorName . $deviceName . $phone . $userId . "
        ORDER BY created_at" . $sort . $offsetRows);

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
