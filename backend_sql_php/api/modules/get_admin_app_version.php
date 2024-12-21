<?php

$results = $database->getStringList("SELECT CONCAT( '{', GROUP_CONCAT( '\"', name, '\": \"', value, '\"' ), '}' )
    FROM `settings` WHERE name = 'iosAppVersion' OR name = 'androidAppVersion' OR name = 'iosAppUpdate' OR name = 'androidAppUpdate'
    ORDER BY name" . $sort);

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

?>
