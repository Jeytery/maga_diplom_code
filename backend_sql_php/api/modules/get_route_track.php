<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$rolePrivObjectName = 'tracks';
$userPrivObjectName = 'routes';
$objectId = 0;
getRoleAndUserPrivileges($rolePrivObjectName, $objectId, $userPrivObjectName);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // Find by RouteId
    $routeId = getRequestValue('routeId');
    if ($routeId && isValidUUID($routeId)) {
        $findRouteId = $routeId;
        $isWhereFounded = true;
        $routeId = getWhereParam('`routes`.route_id', false, 'UUID_TO_BIN( \'' . $routeId . '\', true )');
    } else {
        $routeId = "";
    }

    $userAccessAllowed = false;

    $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $userPrivObjectName, 0); // 0 Access to any records
    
    if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) { // Admin access

        $userAccessAllowed = true;

    } else { // Customer access

        if ($routeId) { // Find by RouteId

            $userAccess = getUserAccessToObject($currentUserObjectPrivileges, $userPrivObjectName, $findRouteId);

            if ($userAccess != false && $userAccess->{PRIV_CAN_SELECT}) {

                $userAccessAllowed = true;
            }

        } else { // Find by User Routes

            // Seek the routes object privileges
            $routesIds = getUserAccessIdsToObject($userPrivObjectName, "can_select");
            if ($routesIds) {

                $isWhereFounded = true;
                $routeId = getWhereExpression("`routes`.route_id IN (" . $routesIds . ")");

                $userAccessAllowed = true;
            }
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
            'trackId', BIN_TO_UUID( `tracks`.track_id, true ),
            'sensorId', BIN_TO_UUID( `tracks`.sensor_id, true ),
            'latitude', `tracks`.latitude,
            'longitude', `tracks`.longitude,
            'time', `tracks`.time,
            'altitude', `tracks`.altitude,
            'accuracy', `tracks`.accuracy,
            'bearing', `tracks`.bearing,
            'speed', `tracks`.speed,
            'satellites', `tracks`.satellites,
            'timeOffset', `tracks`.time_offset,
            'battery', `tracks`.battery,
            'createdAtDate', `tracks`.created_at,
            'createdAt', UNIX_TIMESTAMP( `tracks`.created_at ),
            -- Track Parts
            'trackParts', JSON_EXTRACT( IFNULL( (
            SELECT CONCAT( '[', GROUP_CONCAT(
            JSON_OBJECT (
                'trackPartId', BIN_TO_UUID( `track_parts`.track_part_id, true ),
                'trackId', BIN_TO_UUID( `track_parts`.track_id, true ),
                 -- Track Type
                'trackType', JSON_EXTRACT( IFNULL( (
                SELECT JSON_OBJECT (
                    'trackTypeId', BIN_TO_UUID( `track_types`.track_type_id, true ),
                    'name', `track_types`.name,
                    'description', `track_types`.description,
                    'lastUpdatedDate', `track_types`.last_updated,
                    'lastUpdated', UNIX_TIMESTAMP( `track_types`.last_updated ),
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
                WHERE `track_types`.track_type_id = `track_parts`.track_type_id
                ), '{}'), '$'),
                'name', `track_parts`.name,
                'description', `track_parts`.description,
                'tags', `track_parts`.tags,
                'hasPicture', `track_parts`.picture IS NOT NULL,
                'pictureLink', get_picture_link( `track_parts`.picture, 'track_parts' ),
                'lastUpdatedDate', `track_parts`.last_updated,
                'lastUpdated', UNIX_TIMESTAMP( `track_parts`.last_updated ),
                -- Track Type Translations
                'trackPartTranslations', JSON_EXTRACT( IFNULL ( (
                SELECT CONCAT( '{',
                    GROUP_CONCAT( '\"', `object_translations`.name, '\": \"', `object_translations`.value, '\"' ),
                '}' )
                FROM `object_translations`
                WHERE `track_parts`.track_part_id = `object_translations`.object_id AND `object_translations`.object_name = 'track_parts'" . $languageCode . "
                ), '{}'), '$')
            ) ), ']' )
            FROM `track_parts`
            WHERE `track_parts`.track_id = `tracks`.track_id
            ), '[]'), '$')
        ) as results
        FROM `routes`, `tracks`, `users`, `sensors`
        WHERE `routes`.user_id = `users`.user_id AND `users`.user_id = `sensors`.user_id AND `sensors`.sensor_id = `tracks`.sensor_id AND
        FROM_UNIXTIME(`tracks`.time) BETWEEN `routes`.started AND `routes`.finished" . $routeId . "
        ORDER BY `tracks`.created_at" . $sort . $offsetRows);

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
