<?php

/*
 * Beta Orionis (Rigel) PHP Scripts
 * Rigel The White Blue Giant, The Leg of Orion, Osiris
 * Amon Ra Eye
 * API services (Auth, Main) dependency functions v.1.0.1
 * 3bit.app 2024
 */

/*
    Restore password hash with salt
 */

function getUserPasswordHash($password) {
    return sha1(PASSWORD_SALT . $password);
}

/*
    Get User Values
 */

function getUserValues() {

    global $database;

    global $username, $password;

    global $deviceType, $deviceNumber, $ipAddress, $userAgent, $timezoneOffset, $dstOffset, $timeOffset, $languageCode; //$mapProvider

    // Credentials
    $username = getRequestValue('username');
    $password = getRequestValue('password');

    if (!$username || !$password) {
      $credentials = getBasicCredentials();
      if ($credentials) {
        $decodedCredentials = base64_decode($credentials);
        $username = getParam($decodedCredentials, ':');
        $password = getValue($decodedCredentials, ':');
      }
    }

    if ($password) {
        $password = getUserPasswordHash($password);
    }

    // Device Type | Number | IPAddress | UserAgent
    $deviceType = getHeaderValue('DeviceType');
    $deviceNumber = getHeaderValue('DeviceNumber');
    $ipAddress = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ?  $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // Timezone Offset
    $timezoneOffset = getHeaderValue('TimezoneOffset'); // Number format

    if (!$timezoneOffset) {

        $timezoneOffset = getCookie('TimezoneOffset');
    }

    if (!$timezoneOffset) {

        $timezoneOffset = $database->getStringValue("SELECT value FROM `settings` WHERE name = 'timezoneOffset'");
    }

    // DST Offset
    $dstOffset = getHeaderValue('DSTOffset'); // Number format

    if (!$dstOffset) {

        $dstOffset = getCookie('DSTOffset');
    }

    if (!$dstOffset) {

        $dstOffset = $database->getStringValue("SELECT value FROM `settings` WHERE name = 'dstOffset'");
    }

    $timeOffset = 0;

    if (is_numeric($timezoneOffset) && is_numeric($dstOffset)) {

        $timeOffset = (int) $timezoneOffset + (int) $dstOffset;

        $sessionOffset = secondsToTimezone($timeOffset);

        // Session time offset
        if ($sessionOffset) {

            $database->query("SET @@session.time_zone = '$sessionOffset'");
        }
    }

    // Language Code
    $languageCode = getRequestValue('language');

    if (!$languageCode) {

        $languageCode = getHeaderValue('LanguageCode');
    }

    if (!$languageCode) {

        $languageCode = getCookie('LanguageCode');
    }

    // Map Provider
    //$mapProvider = getRequestValue('mapProvider');
}

/*
    Get User Access to Object
 */

function getUserAccessToObject($accessList, $objectName, $objectId) {

    $hasAccess = false;

    if ($accessList && count($accessList) > 0) {

        if (!$objectId) {
            $objectId = UUID_FULL_ACCESS;
        }

        foreach ($accessList as $userAccess) {

            if ($userAccess->{'objectName'} == $objectName &&
                $userAccess->{'objectId'} == $objectId) {

                $hasAccess = $userAccess;
            }
        }
    }

    return $hasAccess;
}

/*
    Get Public Access to Object Ids List
 */

function getPublicAccessToObjectIds($accessList, $accessType) {

    $idsList = "";

    if ($accessList && count($accessList) > 0) {

        $size = count($accessList);

        for ($i = 0; $i < $size; $i++) {

            $userAccess = $accessList[$i];

            if ($userAccess->{$accessType} == true) {

                $isLast = ($i == $size - 1) ? true : false;

                $idsList += 'UUID_TO_BIN( \'' . $userAccess->{'objectId'} . '\', true )' . ($isLast ? "" : ", ");
            }
        }
    }

    return $idsList;
}

/*
    Send Picture response
 */

function sendPictureResponse($picture, $pictureType, $objectName) {

    global $logFile;

    global $sessionId;

    global $httpResponces, $errorResponces;

    global $localFileSystem, $googleDrive, $typeBase64;

    // Picture data
    $pictureData = explode(';', $picture);
    $storageType = FILES_STORAGE; // fileStorage = LocalFileSystem
    $fileName = ''; // fileName = monkey.jpeg

    // Check the picture data
    foreach ($pictureData as $item) {

        $param = getParam($item, '=');
        $value = getValue($item, '=');

        $p = trim($param);

        if ($p == 'fileStorage') {

            $storageType = trim($value);

        } else if ($p == 'fileName') {

            $fileName = trim($value);
        }
    }

    writeToLog($logFile, "Info|Find picture|#" . $sessionId . "|" . "Storage type=" . $storageType . " filename=" . $fileName);

    // Check the picture for storage type: localFileSystem | googleDrive

    if ($storageType == $localFileSystem) {

        if ($pictureType == $typeBase64) {

            $fileContent = getBase64FileContent($fileName, $objectName);
            if ($fileContent) {

                $results = '{"picture":"' . $fileContent . '"}';
                $foundRows = 1;
                postSuccessResponseData($sessionId, $results, $foundRows);

            } else {

                //postErrorResponse($sessionId, $httpResponces["errorHttpNotFound"], $errorResponces["errorDataNotFound"]);
                postSuccessResponse($sessionId, $httpResponces["successHttpNoContent"]);
            }

        } else {

            // With file content
            //$fileContent = getFileContent($fileName, $objectName);
            //postSuccessResponseFileContent($sessionId, $fileName, $typeImageFile, $fileContent);

            // With filepath
            $filePath = getFileContentPath($fileName, $objectName);
            if (file_exists($filePath)) {

                postSuccessResponseFilepath($sessionId, $filePath);

            } else {

                //postErrorResponse($sessionId, $httpResponces["errorHttpNotFound"], $errorResponces["errorDataNotFound"]);
                postSuccessResponse($sessionId, $httpResponces["successHttpNoContent"]);
            }
        }

    } else if ($storageType == $googleDrive) {

        // TODO: implement response from Google Drive storage type
    }
}

/*
    Save Picture to storage
 */

function savePictureToStorage($picture, $pictureFilename, $objectName) {

    global $localFileSystem, $googleDrive;

    if (FILES_STORAGE == $localFileSystem) {

        $filePath = getFileContentPath($pictureFilename, $objectName);

        $fileContent = base64_decode($picture);

        file_put_contents($filePath, $fileContent); //LOCK_EX

    } else if (FILES_STORAGE == $googleDrive) {

        // TODO: implement response from Google Drive storage type
    }
}

/*
    Remove picture from strorage
 */

function removePictureFromStorage($objectName, $objectId, $fieldId) {

    global $database;

    global $localFileSystem, $googleDrive;

    $picture = $database->getStringValue("SELECT picture FROM `$objectName` WHERE $fieldId = $objectId");

    // Picture data
    $pictureData = explode(';', $picture);
    $storageType = FILES_STORAGE; // fileStorage = LocalFileSystem
    $fileName = ''; // fileName = monkey.jpeg

    // Check the picture data
    foreach ($pictureData as $item) {

        $param = getParam($item, '=');
        $value = getValue($item, '=');

        $p = trim($param);

        if ($p == 'fileStorage') {

            $storageType = trim($value);

        } else if ($p == 'fileName') {

            $fileName = trim($value);
        }
    }

    if (FILES_STORAGE == $localFileSystem) {

        $filePath = getFileContentPath($fileName, $objectName);

        if (file_exists($filePath)) {

            unlink($filePath);
        }

    } else if (FILES_STORAGE == $googleDrive) {

        // TODO: implement response from Google Drive storage type
    }
}

/*
    Get Picture link
 */
function getPictureLink($picture, $objectName) { //fileStorage=LocalFileSystem; fileName=users_2_rXneEvVqHZgHIpJKWpXMMbAdBoyjleBD

    global $logFile;

    global $sessionId;

    global $localFileSystem, $googleDrive, $pictureLocalFilepath;

    $pictureLink = null;

    if (!$picture) return $pictureLink;

    // Picture data
    $pictureData = explode(';', $picture);
    $storageType = FILES_STORAGE; // fileStorage = LocalFileSystem
    $fileName = ''; // fileName = monkey.jpeg

    // Check the picture data
    foreach ($pictureData as $item) {

        $param = getParam($item, '=');
        $value = getValue($item, '=');

        $p = trim($param);

        if ($p == 'fileStorage') {

            $storageType = trim($value);

        } else if ($p == 'fileName') {

            $fileName = trim($value);
        }
    }

    writeToLog($logFile, "Info|Find picture|#" . $sessionId . "|" . "Storage type=" . $storageType . " filename=" . $fileName);

    // Check the picture for storage type: localFileSystem | googleDrive

    if ($storageType == $localFileSystem) {

        $pictureLink = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER['HTTP_HOST'] . $pictureLocalFilepath . $objectName . "/" . $fileName;

    } else if ($storageType == $googleDrive) {

        // TODO: implement response from Google Drive storage type
    }

    return $pictureLink;
}

/*
    Check User Attempts by AttemptType
    Dependency: Auth Service
 */

function checkUserAttempt($attemptType) {

    global $database;

    global $sessionId;

    global $httpResponces, $errorResponces;

    global $ipAddress, $deviceNumber, $username;

    $attemptsCount = $database->getStringValue("SELECT value FROM `settings` WHERE name = CONCAT('$attemptType', 'Attempts')");

    if (!$attemptsCount) {

        $attemptsCount = 3;
    }

    $attemptsHours = $database->getStringValue("SELECT value FROM `settings` WHERE name = CONCAT('$attemptType', 'AttemptsByHours')");

    if (!$attemptsHours) {

        $attemptsHours = 1;
    }

    if (!$username) { // Check by IP address or Device number

        $sqlRequest = "SELECT COUNT(*) FROM `attempts` WHERE (ip_address = '$ipAddress' OR device_number = '$deviceNumber') AND attempt_type = '$attemptType' AND created_at >= DATE_SUB(NOW(), INTERVAL '$attemptsHours' HOUR)";

    } else { // Check by username

        $sqlRequest = "SELECT COUNT(*) FROM `attempts` WHERE username = '$username' AND attempt_type = '$attemptType' AND created_at >= DATE_SUB(NOW(), INTERVAL '$attemptsHours' HOUR)";
    }

    $count = $database->getStringValue($sqlRequest);

    if ($count >= $attemptsCount) {

        // Oh! Critical activity from remote
        postErrorResponse($sessionId, $httpResponces["errorHttpForbidden"], $errorResponces["errorTooManyAttempts"]);
    }
}

/*
    Save User Attempt by Username, IPAddress, AttemptType, DeviceType, DeviceNumber, UserAgent
    Dependency: Auth Service
 */

function saveUserAttempt($attemptType) {

    global $database;

    global $username, $ipAddress, $deviceType, $deviceNumber, $userAgent;

    $sqlRequest = "INSERT INTO `attempts` ( username, ip_address, attempt_type, device_type, device_number, user_agent ) VALUES ( '$username', '$ipAddress', '$attemptType', '$deviceType', '$deviceNumber', '$userAgent' )";

    $database->query($sqlRequest);
}

/*
    Clear User Attempt
    Dependency: Auth Service

 */

function clearUserAttempts($attemptType) {

    global $database;

    global $username, $ipAddress, $deviceType, $deviceNumber, $userAgent;

    if (!$username) { // Clear by IP address or Device number

        $sqlRequest = "DELETE FROM `attempts` WHERE (ip_address = '$ipAddress' OR device_number = '$deviceNumber') AND attempt_type = '$attemptType' ";

    } else { // Clear by username

        $sqlRequest = "DELETE FROM `attempts` WHERE (ip_address = '$ipAddress' OR username = '$username') AND attempt_type = '$attemptType'";
    }

    $database->query($sqlRequest);
}

/*
    Check User Status
    Dependency: Auth Service
 */

function checkUserStatus($status) {

    global $sessionId;

    global $httpResponces, $errorResponces;

    // Not activated
    if ($status == USER_STATUS_NOT_ACTIVATED) {

        postErrorResponse($sessionId, $httpResponces["errorHttpForbidden"], $errorResponces["errorUserNotActivated"]);
    }

    // Banned
    if ($status == USER_STATUS_BANNED) {

        postErrorResponse($sessionId, $httpResponces["errorHttpForbidden"], $errorResponces["errorUserBanned"]);
    }

    // Temporary banned
    if ($status == USER_STATUS_TEMPORARY_BANNED) {

        postErrorResponse($sessionId, $httpResponces["errorHttpForbidden"], $errorResponces["errorUserTemporaryBanned"]);
    }

    // Auth disabled
    if ($status == USER_STATUS_AUTH_DISABLED) {

        postErrorResponse($sessionId, $httpResponces["errorHttpAuthRequired"], $errorResponces["errorAuthFailed"]);
    }

    // Removed
    if ($status == USER_STATUS_REMOVED) {

        postErrorResponse($sessionId, $httpResponces["errorHttpNotFound"], $errorResponces["errorUserRemoved"]);
    }
}

/*
    Check User Authorization
    Dependency: Auth Service
 */

function checkUserAuth() {

    global $database;

    global $sessionId;

    global $httpResponces, $errorResponces;

    global $currentSessionTime;

    global $currentUser;

    global $currentUserId, $currentRoleId, $currentUserUsername, $currentUserPassword, $currentUserStatus;

    global $username, $password;

    $token = getBearerToken();

    if (!$token) {

        $token = getRequestValue('token'); // Request access token
    }

    if ($token) {

        // Decrypt accees token (Authorization token is encoded data, access token is decoded data)
        $sqlRequest = "SELECT JSON_OBJECT ( 'userId', BIN_TO_UUID( user_id, true ), 'roleId', BIN_TO_UUID( role_id, true ), 'username', username, 'password', HEX(password), 'status', status, 'accessTokenCreated', UNIX_TIMESTAMP( access_token_created ) ) FROM `users` WHERE access_token = '$token'";

        $jsonResponse = $database->getStringValue($sqlRequest);

        $currentUser = json_decode($jsonResponse);

    } else if ($username) {

        $sqlRequest = "SELECT JSON_OBJECT ( 'userId', BIN_TO_UUID( user_id, true ), 'roleId', BIN_TO_UUID( role_id, true ), 'username', username, 'password', HEX(password), 'status', status, 'accessTokenCreated', UNIX_TIMESTAMP( access_token_created ) ) FROM `users` WHERE username = '$username' AND password = UNHEX('$password')";

        $jsonResponse = $database->getStringValue($sqlRequest);

        $currentUser = json_decode($jsonResponse);

    } else {

        postErrorResponse($sessionId, $httpResponces["errorHttpAuthRequired"], $errorResponces["errorAuthFailed"]);
    }

    if ($currentUser) {

        $currentUserId = $currentUser->{'userId'};
        $currentRoleId = $currentUser->{'roleId'};
        $currentUserUsername = $currentUser->{'username'};
        $currentUserPassword = $currentUser->{'password'};
        $currentUserStatus = $currentUser->{'status'};

        // Check user status
        checkUserStatus($currentUserStatus);

        // Check the token timelife
        $currentSessionTime = time();
        $tokenCreated = $currentUser->{'accessTokenCreated'};

        $isActualToken = false;

        if ($token) {

            // Restore basic credentials
            $username = $currentUserUsername;
            $password = $currentUserPassword;

            $isActualToken = isActualToken($tokenCreated);

        } else {

            $isActualToken = true;
        }

        // Token has been expired
        if (!$isActualToken) {

            postErrorResponse($sessionId, $httpResponces["errorHttpAuthRequired"], $errorResponces["errorAccessTokenExpired"]);
        }

    } else {

        // Save the number of attempts of userAuth
        saveUserAttempt(ATTEMPT_TYPE_USER_AUTH);

        if ($token) {

            postErrorResponse($sessionId, $httpResponces["errorHttpAuthRequired"], $errorResponces["errorInvalidAccessToken"]);

        } else if ($username) {

            postErrorResponse($sessionId, $httpResponces["errorHttpAuthRequired"], $errorResponces["errorInvalidUsernameOrPassword"]);
        }
    }
}

function isActualToken($tokenCreated) {

    global $database;
    
    global $currentSessionTime;

    global $tokenExpiredTime;

    $actualToken = false;

    if ($tokenCreated) {
        // Token expired time (in seconds)
        $tokenExpiredTime = $database->getStringValue("SELECT value FROM `settings` WHERE name = 'accessTokenExpiredTime'");

        if (!$tokenExpiredTime) {
            $tokenExpiredTime = 31536000; // 1 year seconds == 31536000 | 900 sec. == 15 min.
        }

        if ($currentSessionTime - $tokenCreated < $tokenExpiredTime) {
            $actualToken = true;
        }
    }

    return $actualToken;
}

function getTokenExpiredTime() {

    global $database;
    
    global $tokenExpiredTime;

    // Token expired time (in seconds)
    $tokenExpiredTime = $database->getStringValue("SELECT value FROM `settings` WHERE name = 'accessTokenExpiredTime'");

    if (!$tokenExpiredTime) {
        $tokenExpiredTime = 31536000; // 1 year seconds == 31536000 | 900 sec. == 15 min.
    }

    return $tokenExpiredTime;
}

/*
    Get User Access to Object Privileges by objectName And to Object Id by objectId
    objectName for role and user privileges both, when defined userPrivObjectName,
    then objectName for role privilege only.
    Dependency: Auth Service
 */

function getRoleAndUserPrivileges($objectName, $objectId, $userPrivObjectName = null) {

    global $database;

    global $currentUserId, $currentRoleId;

    global $currentUserRolePrivileges, $currentUserObjectPrivileges;

    $currentUserRolePrivileges = [];

    $currentUserObjectPrivileges = [];

    if ($currentUserId && $objectName) {

        $sqlRequest = "SELECT JSON_OBJECT ( 'roleName', r.name, 'objectName', rp.object_name, 'canSelect', rp.can_select = 1, 'canInsert', rp.can_insert = 1, 'canUpdate', rp.can_update = 1, 'canDelete', rp.can_delete = 1 ) FROM `roles` r, `role_privileges` rp WHERE r.role_id = UUID_TO_BIN( '$currentRoleId', true ) AND r.role_id = rp.role_id AND rp.object_name = '$objectName'";

        $jsonResponse = $database->getStringValue($sqlRequest);

        $currentUserRolePrivileges = json_decode($jsonResponse);
        
        if ($userPrivObjectName) {

            $objectName = $userPrivObjectName;
        }

        if ($objectId || $objectId == 0) { // Array of privileges to objectIds (Id Or 0)

            $sqlRequest = "SELECT CONCAT( '[', GROUP_CONCAT( JSON_OBJECT ( 'objectId', BIN_TO_UUID( object_id, true ), 'objectName', object_name,  'canSelect', can_select = 1, 'canInsert', can_insert = 1, 'canUpdate', can_update = 1, 'canDelete', can_delete = 1 ) ), ']') FROM `user_privileges` WHERE user_id = UUID_TO_BIN( '$currentUserId', true ) AND object_name = '$objectName'" . ( $objectId == 0 ? "" : " AND object_id = UUID_TO_BIN( '$objectId', true )" );

            $jsonResponse = $database->getStringValue($sqlRequest);

            $currentUserObjectPrivileges = json_decode($jsonResponse);
        }
    }

    return array($currentUserRolePrivileges, $currentUserObjectPrivileges);
}

/*
    Get Public Access to Object Privileges by objectName
    Dependency: Auth Service
 */

function getRoleAndPublicPrivileges($objectName) {

    global $database;

    global $currentUserId, $currentRoleId;

    global $currentUserRolePrivileges, $currentPublicObjectPrivileges;

    $currentUserRolePrivileges = [];

    $currentPublicObjectPrivileges = [];

    if ($currentUserId && $objectName) {

        $sqlRequest = "SELECT JSON_OBJECT ( 'roleName', r.name, 'objectName', rp.object_name, 'canSelect', rp.can_select = 1, 'canInsert', rp.can_insert = 1, 'canUpdate', rp.can_update = 1, 'canDelete', rp.can_delete = 1 ) FROM `roles` r, `role_privileges` rp WHERE r.role_id = UUID_TO_BIN( '$currentRoleId', true ) AND r.role_id = rp.role_id AND rp.object_name = '$objectName'";

        $jsonResponse = $database->getStringValue($sqlRequest);

        $currentUserRolePrivileges = json_decode($jsonResponse);

        $sqlRequest = "SELECT CONCAT( '[', GROUP_CONCAT( JSON_OBJECT ( 'objectId', BIN_TO_UUID( object_id, true ), 'objectName', object_name,  'canSelect', can_select = 1, 'canInsert', can_insert = 1, 'canUpdate', can_update = 1, 'canDelete', can_delete = 1 ) ), ']') FROM `user_privileges` WHERE user_id = 0 AND object_name = '$objectName'";

        $jsonResponse = $database->getStringValue($sqlRequest);

        $currentPublicObjectPrivileges = json_decode($jsonResponse);
    }

    return array($currentUserRolePrivileges, $currentPublicObjectPrivileges);
}

/*
    Get User Access Object Ids List by objectName And objectPrivilege (can_select, can_update, can_insert, can_delete)
    Dependency: Auth Service
    Ids List format: <UUID_TO_BIN( uuid1, true ), UUID_TO_BIN( uuid2, true ), ... UUID_TO_BIN( uuidN, true)>
 */

function getUserAccessIdsToObject($objectName, $objectPrivilege) {

    global $database;

    global $username;

    $objectIds = '';

    if ($username && $objectName) {

        $sqlRequest = "SELECT GROUP_CONCAT( CONCAT( 'UUID_TO_BIN( \'', BIN_TO_UUID( up.object_id, true ), '\', true )') ) FROM `users` u, `user_privileges` up WHERE u.username = '$username' AND u.user_id = up.user_id AND up.object_name = '$objectName' AND up." . $objectPrivilege . " = 1";

        $objectIds = $database->getStringValue($sqlRequest);
    }

    return $objectIds;
}

/*
    Is User Access To Route In Group
    Return: "1" (Yes) Or "0" (No) string
    Dependency: Main Service
 */

function isUserAccessToRouteInGroup($userId, $routeId) {

    global $database;

    $hasAccess = false;

    if ($userId && $routeId) {

        $sqlRequest = "SELECT COUNT(u.user_id) FROM `routes` r, `groups` g, `group_users` gu, `users` u WHERE r.group_id = g.group_id AND gu.user_id = u.user_id AND gu.group_id = g.group_id AND r.route_id = UUID_TO_BIN( '$routeId', true ) AND u.user_id = UUID_TO_BIN( '$userId', true )";

        $hasAccess = $database->getStringValue($sqlRequest);
    }

    return $hasAccess;
}

/*
    Is User In Group
    Return: "1" (Yes) Or "0" (No) string
    Dependency: Main Service
 */

function isUserInGroup($userId, $groupId) {

    global $database;

    $userInGroup = false;

    if ($userId && $groupId) {

        $sqlRequest = "SELECT COUNT(u.user_id) FROM `groups` g, `group_users` gu, `users` u WHERE gu.user_id = u.user_id AND gu.group_id = g.group_id AND u.user_id = UUID_TO_BIN( '$userId', true ) AND g.group_id = UUID_TO_BIN( '$groupId', true )";

        $userInGroup = $database->getStringValue($sqlRequest);
    }

    return $userInGroup;
}

function getAdminToken($adminId) {

    global $database;

    $adminToken = $database->getStringValue("SELECT access_token FROM `users` WHERE user_id = UUID_TO_BIN( '$adminId', true )");

    return $adminToken;
}

?>
