<?php

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'settings';
$objectId = null;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_SELECT} == true;

if ($roleAccessAllowed) {

    // $name

    $sort = getRequestValue('sort');

    if ($sort) {
      $sort = " $sort";
    }

    $results = $database->getStringList(
    "SELECT JSON_OBJECT (
        'name', name,
        'description', description,
        'value', value,
        'lastUpdatedDate', last_updated,
        'lastUpdated', UNIX_TIMESTAMP( last_updated )
    ) as results
    FROM `settings`
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

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
