<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'groups';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // Find by GroupId
    $groupId = getRequestValue('groupId');
    if ($groupId && isValidUUID($groupId)) {
        $findGroupId = $groupId;
        $groupId = getWhereParam('group_id', false, 'UUID_TO_BIN( \'' . $groupId . '\', true )');
    } else {
        $groupId = "";
    }

    $name = getRequestValue('name');
    if ($name) {
        $name = getWhereParam('name', true, $name);
    }

    $description = getRequestValue('description');
    if ($description) {
        $description = getWhereParam('description', true, $description);
    }

    $tag = getRequestValue('tag');
    if ($tag) {
        $tag = getWhereParam('tags', true, $tag);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, 0); // 0 Access to any records
    
    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        $userId = getRequestValue('userId');
        if ($userId && isValidUUID($userId)) {
            $userId = getWhereParam('user_id', false, 'UUID_TO_BIN( \'' . $userId . '\', true )');
        }

        $userAccessAllowed = true;

    } else { // Customer access

        if ($groupId) { // Find by GroupId

            $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $objectName, $findGroupId);

            if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

                $userId = "";

                $userAccessAllowed = true;
            }

        } else { // Find by UserId

            // Seek the groups object privileges
            $groupsIds = getUserAccessIdsToObject($objectName, "can_select");
            if ($groupsIds) {
                $groupId = getWhereExpression("group_id IN (" . $groupsIds . ")");
            }

            // Seek the groups of the user
            $userId = getWhereParam('user_id', false, 'UUID_TO_BIN( \'' . $currentUserId . '\', true )');

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
            'groupId', BIN_TO_UUID( group_id, true ),
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
            WHERE `users`.user_id = `groups`.user_id
            ), '[]'), '$'),
            -- Group Users
            'groupUsers', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '[', GROUP_CONCAT(
            JSON_OBJECT (
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
            ) ), ']' )
            FROM `users`, `groups`, `group_users`
            WHERE `groups`.group_id = `group_users`.group_id AND `users`.user_id = `group_users`.user_id
            ), '[]'), '$'),
            'name', name,
            'description', description,
            'tags', tags,
            'hasPicture', picture IS NOT NULL,
            'pictureLink', get_picture_link( picture, 'groups' ),
            'lastUpdatedDate', last_updated,
            'createdAtDate', created_at,
            'lastUpdated', UNIX_TIMESTAMP( last_updated ),
            'createdAt', UNIX_TIMESTAMP( created_at ),
            'groupOptions', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '{',
                GROUP_CONCAT( '\"', `object_options`.name, '\": ',
                    IF( `object_options`.option_type = 'number' OR `object_options`.option_type = 'boolean',
                        `object_options`.value, CONCAT( '\"', `object_options`.value, '\"' )
                    )
                ),
            '}' )
            FROM `object_options`
            WHERE `groups`.group_id = `object_options`.object_id AND `object_options`.object_name = 'groups'
            ),'{}'),'$')
        ) as results
        FROM `groups`" . $groupId . $name . $description . $tag . $userId . "
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
