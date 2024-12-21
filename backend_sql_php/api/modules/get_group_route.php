<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$rolePrivObjectName = 'tracks';
$userPrivObjectName = 'groups';
$objectId = 0;
getRoleAndUserPrivileges($rolePrivObjectName, $objectId, $userPrivObjectName);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // Find by GroupId
    $groupId = getRequestValue('groupId');
    if ($groupId && isValidUUID($groupId)) {

        $findGroupId = $groupId;
        $isWhereFounded = true;
        $groupId = getWhereParam('`groups`.group_id', false, 'UUID_TO_BIN( \'' . $groupId . '\', true )');

    } else {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $userPrivObjectName, 0); // 0 Access to any records
    
    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $userPrivObjectName, $findGroupId);

        if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

            $userAccessAllowed = true;
        }
    }

    if ($userAccessAllowed) {

        // LanguageCode
        $isWhereFounded = true;
        $languageCode = getWhereParam('`object_translations`.language_code', false, $languageCode, true);

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
            'routeId', BIN_TO_UUID( `routes`.route_id, true ),
            -- Route Type
            'routeType', JSON_EXTRACT( IFNULL( (
            SELECT JSON_OBJECT (
                'routeTypeId', BIN_TO_UUID( `route_types`.route_type_id, true ),
                'name', `route_types`.name,
                'description', `route_types`.description,
                'lastUpdatedDate', `route_types`.last_updated,
                'lastUpdated', UNIX_TIMESTAMP( `route_types`.last_updated ),
                -- Route Type Translations
                'routeTypeTranslations', JSON_EXTRACT( IFNULL ( (
                SELECT CONCAT( '{',
                    GROUP_CONCAT( '\"', `object_translations`.name, '\": \"', `object_translations`.value, '\"' ),
                '}' )
                FROM `object_translations`
                WHERE `route_types`.route_type_id = `object_translations`.object_id AND `object_translations`.object_name = 'route_types'" . $languageCode . "
                ), '{}'), '$')
            )
            FROM `route_types`
            WHERE `route_types`.route_type_id = `routes`.route_type_id
            ), '{}'), '$'),
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
            WHERE `users`.user_id = `routes`.user_id
            ), '{}'), '$'),
            -- Group
            'group', JSON_EXTRACT( IFNULL( (
            SELECT JSON_OBJECT (
                'groupId', BIN_TO_UUID( `groups`.group_id, true ),
                'name', `groups`.name,
                'description', `groups`.description,
                'tags', `groups`.tags,
                'hasPicture', `groups`.picture IS NOT NULL,
                'pictureLink', get_picture_link( `groups`.picture, 'groups' ),
                'lastUpdatedDate', `groups`.last_updated,
                'lastUpdated', UNIX_TIMESTAMP( `groups`.last_updated ),
                'createdAtDate', `groups`.created_at,
                'createdAt', UNIX_TIMESTAMP( `groups`.created_at ),
                -- Group Translations
                'groupTranslations', JSON_EXTRACT( IFNULL ( (
                SELECT CONCAT( '{',
                    GROUP_CONCAT( '\"', `object_translations`.name, '\": \"', `object_translations`.value, '\"' ),
                '}' )
                FROM `object_translations`
                WHERE `groups`.group_id = `object_translations`.object_id AND `object_translations`.object_name = 'groups'" . $languageCode . "
                ), '{}'), '$')
            )
            FROM `groups`
            WHERE `groups`.group_id = `routes`.group_id
            ), '{}'), '$'),
            'name', `routes`.name,
            'description', `routes`.description,
            'tags', `routes`.tags,
            'hasPicture', `routes`.picture IS NOT NULL,
            'pictureLink', get_picture_link( `routes`.picture, 'routes' ),
            'startedDate', `routes`.started,
            'finishedDate', `routes`.finished,
            'lastUpdatedDate', `routes`.last_updated,
            'started', UNIX_TIMESTAMP( `routes`.started ),
            'finished', UNIX_TIMESTAMP( `routes`.finished ),
            'lastUpdated', UNIX_TIMESTAMP( `routes`.last_updated ),
            -- Route Parts
            'routeParts', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '[', GROUP_CONCAT(
            JSON_OBJECT (
                'routePartId', BIN_TO_UUID( `route_parts`.route_part_id, true ),
                'routeId', BIN_TO_UUID( `route_parts`.route_id, true ),
                 -- Track Type
                'trackType', JSON_EXTRACT( IFNULL( (
                SELECT JSON_OBJECT (
                    'trackTypeId', BIN_TO_UUID( `track_types`.track_type_id, true ),
                    'name', `track_types`.name,
                    'description', `track_types`.description,
                    'lastUpdatedDate', `track_types`.last_updated,
                    'lastUpdated', UNIX_TIMESTAMP(`track_types`.last_updated),
                    -- Track Type Translations
                    'trackTypeTranslations', JSON_EXTRACT( IFNULL ( (
                    SELECT CONCAT( '{',
                        GROUP_CONCAT( '\"', `object_translations`.name, '\": \"', `object_translations`.value, '\"' ),
                    '}' )
                    FROM `object_translations`
                    WHERE `track_types`.track_type_id = `object_translations`.object_id AND `object_translations`.object_name = 'track_types'" . $languageCode . "
                    ), '{}'), '$')
                )
                FROM `track_types`
                WHERE `track_types`.track_type_id = `route_parts`.track_type_id
                ), '{}'), '$'),
                'name', `route_parts`.name,
                'description', `route_parts`.description,
                'tags', `route_parts`.tags,
                'hasPicture', `route_parts`.picture IS NOT NULL,
                'pictureLink', get_picture_link( `route_parts`.picture, 'route_parts' ),
                'latitude', `route_parts`.latitude,
                'longitude', `route_parts`.longitude,
                'lastUpdatedDate', `route_parts`.last_updated,
                'lastUpdated', UNIX_TIMESTAMP( `route_parts`.last_updated ),
                -- Track Type Translations
                'routePartTranslations', JSON_EXTRACT( IFNULL ( (
                SELECT CONCAT( '{',
                    GROUP_CONCAT( '\"', `object_translations`.name, '\": \"', `object_translations`.value, '\"' ),
                '}' )
                FROM `object_translations`
                WHERE `route_parts`.route_part_id = `object_translations`.object_id AND `object_translations`.object_name = 'route_parts'" . $languageCode . "
                ), '{}'), '$')
            ) ), ']' )
            FROM `route_parts`
            WHERE `route_parts`.route_id = `routes`.route_id
            ), '[]'), '$'),
            -- Route Options
            'routeOptions', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '{',
                GROUP_CONCAT( '\"', `object_options`.name, '\": ',
                    IF( `object_options`.option_type = 'number' OR `object_options`.option_type = 'boolean',
                        `object_options`.value, CONCAT( '\"', `object_options`.value, '\"' )
                    )
                ),
            '}' )
            FROM `object_options`
            WHERE `routes`.route_id = `object_options`.object_id AND `object_options`.object_name = 'routes'
            ), '{}'), '$'),
            -- Route Translations
            'routeTranslations', JSON_EXTRACT( IFNULL ( (
            SELECT CONCAT( '{',
                GROUP_CONCAT( '\"', `object_translations`.name, '\": \"', `object_translations`.value, '\"' ),
            '}' )
            FROM `object_translations`
            WHERE route_id = `object_translations`.object_id AND `object_translations`.object_name = 'routes'" . $languageCode . "
            ), '{}'), '$')
        ) as results
        FROM `routes`, `groups`
        WHERE `routes`.group_id = `groups`.group_id" . $groupId . "
        ORDER BY `routes`.created_at" . $sort . $offsetRows);

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
