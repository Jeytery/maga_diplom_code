<?php

/*
    Insert Track Part
    Url: POST /service/api/v1/track/part
 */

getUserValues();

// Check the number of attempts of userAuth
checkUserAttempt(ATTEMPT_TYPE_USER_AUTH);

// Check the authentication
checkUserAuth();

// Get the privileges
$objectName = 'track_parts';
$objectId = 0;
getRoleAndUserPrivileges($objectName, $objectId);

// Check the authorization
$roleAccessAllowed = $currentUserRolePrivileges->{PRIV_CAN_INSERT} == true;

if ($roleAccessAllowed) {

    // { "trackId": <trackId>, "trackTypeId": <trackTypeId>, "name": <partName>, "description": <description>, "tags": <routeTags> }

    // TrackId
    $trackId = getPostRequestJson()->trackId;

    // TrackTypeId
    $trackTypeId = getPostRequestJson()->trackTypeId;

    // PartName
    $partName = getPostRequestJson()->name;

    // Description
    $description = getPostRequestJson()->description;

    // PartTags
    $partTags = getPostRequestJson()->tags;


    if (!$trackId || !isValidUUID($trackId) ||
        !$trackTypeId || !isValidUUID($trackTypeId) ||
        !$partName || !$description) {

        postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
    }

    // Check the trackId. The default rule: Only the track owner could set part to track
    $trackCount = $database->getIntValue("SELECT COUNT(track_id) FROM `tracks` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND track_id = UUID_TO_BIN( '$trackId', true )");

    if ($trackCount == 0) {

        postErrorResponse($sessionId, $errorHttpNotAcceptable, $errorDataNotAvailable);
    }

    $sqlRequest = "INSERT INTO `track_parts` ( track_id, track_type_id, name, description, tags ) VALUES ( UUID_TO_BIN( '$trackId', true ), UUID_TO_BIN( '$trackTypeId', true ), '$partName', '$description', '$partTags' )";

    $isCompleted = $database->query($sqlRequest);

    if ($isCompleted) {

        $trackPartId = $uuid;

        // Log to database

        $results = '{"trackPartId": "' . $trackPartId . '"}';

        $rowsCount = 1;

        postSuccessResponseData($sessionId, $results, $rowsCount);

    } else {

        $databaseError = $database->getErrorMessage();
        $errorDatabaseError['name'] = $databaseError;
        postErrorResponse($sessionId, $errorHttpInternalServerError, $errorDatabaseError);
    }

} else {

    postErrorResponse($sessionId, $errorHttpForbidden, $errorRolePrivNotFound);
}

?>
