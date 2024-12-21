<?php

// Access Key
$accessKey = getRequestValue('accessKey');

if (!$accessKey) {

    postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
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
    'countOfUse', count_of_use ,
    'wasUsedCount', was_used_count,
    'expiredTime', expired_time,
    'priceDiscount', price_discount,
    'createdAtDate', created_at,
    'createdAt', UNIX_TIMESTAMP( created_at )
) as results
FROM `key_privileges` WHERE access_key = '$accessKey'");

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

?>
