<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'key_privileges';
$objectId = null;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // $userId
    // $accessKey
    // $accessType

    $sort = getRequestValue('sort');

    if ($sort) {
      $sort = " $sort";
    }

    $results = $database->getString(
    "SELECT JSON_OBJECT (
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
        WHERE `users`.user_id = `key_privileges`.user_id
        ), '{}'), '$'),
        'accessKey', access_key,
        'accessType', access_type,
        'objectId', BIN_TO_UUID( object_id, true ),
        'status', status,
        'name', name,
        'description', description,
        'availableCount', available_count,
        'usedCount', used_count,
        'expiredTime', expired_time,
        'priceDiscount', price_discount,
        'createdAtDate', created_at,
        'createdAt', UNIX_TIMESTAMP( created_at )
    ) as results
    FROM `key_privileges` ORDER BY created_at" . $sort);

    if ($results) {

        $foundRows = 1;
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

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
