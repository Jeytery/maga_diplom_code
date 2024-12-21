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

        if (!$findUsername && !$userId) {
            $findUsername = getWhereParam('username', false, $currentUserUsername, true);
        }

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $currentUserId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

            $findUsername = getWhereParam('username', false, $currentUserUsername, true);

            $userId = "";
            $findName = "";

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

        $results = $database->getStringList( // userPrivileges | userRole
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
            ), '{}'), '$'),
            -- User Role
            'userRole', JSON_EXTRACT( IFNULL( (
            SELECT JSON_OBJECT (
                'roleId', BIN_TO_UUID( `roles`.role_id, true ),
                'name', `roles`.name,
                -- Role Privileges
                'rolePrivileges', JSON_EXTRACT( IFNULL( (
                SELECT CONCAT( '[', GROUP_CONCAT(
                JSON_OBJECT (
                    'objectName', `role_privileges`.object_name,
                    'canSelect', `role_privileges`.can_select = 1,
                    'canInsert', `role_privileges`.can_insert = 1,
                    'canUpdate', `role_privileges`.can_update = 1,
                    'canDelete', `role_privileges`.can_delete = 1
                ) ), ']' )
                FROM `roles`, `role_privileges`
                WHERE `role_privileges`.role_id = `roles`.role_id
                ), '[]'), '$')
            )
            FROM `roles`
            WHERE `users`.role_id = `roles`.role_id
            ), '{}'), '$'),
            -- User Privileges
            'userPrivileges', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '[', GROUP_CONCAT(
            JSON_OBJECT (
                'objectId', BIN_TO_UUID( `user_privileges`.object_id, true ),
                'objectName', `user_privileges`.object_name,
                'canSelect', `user_privileges`.can_select = 1,
                'canInsert', `user_privileges`.can_insert = 1,
                'canUpdate', `user_privileges`.can_update = 1,
                'canDelete', `user_privileges`.can_delete = 1
            ) ), ']' )
            FROM `user_privileges`
            WHERE `users`.user_id = `user_privileges`.user_id
            ), '[]'), '$')
        ) as results
        FROM `users`" . $findUsername . $userId . "
        ORDER BY user_id" . $sort . $offsetRows);

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
