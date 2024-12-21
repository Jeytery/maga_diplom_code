<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Types privileges not found

// LanguageCode
$isWhereFounded = true;
$languageCode = getWhereParam('`object_translations`.language_code', false, $languageCode, true);

$sort = getRequestValue('sort');

if ($sort) {
  $sort = " $sort";
}

$results = $database->getStringList(
"SELECT JSON_OBJECT (
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
) as results
FROM `track_types`
ORDER BY `track_types`.last_updated" . $sort);

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
