<?php

/*
 * Beta Orionis (Rigel) PHP Scripts
 * Rigel The White Blue Giant, The Leg of Orion, Osiris
 * Amon Ra Eye
 * API index v.1.0.1
 * 3bit.app 2024
 */

/*  Global variables:
    - When start API: $logFile, $database, $sessionId
    - When HTTP request get values: $username, $password, $deviceType, $deviceNumber, $ipAddress, $userAgent, $timezoneOffset, $dstOffset, $timeOffset, $languageCode, $mapProvider
    - When Authentication: $currentSessionTime, $currentUser, $currentUserUsername, $currentUserPassword, $currentUserStatus
    - When Authorization: $currentUserId, $currentRoleId
    - When Role and User privileges: $currentUserRolePrivileges, $currentUserObjectPrivileges, $currentPublicObjectPrivileges
    - When make SQL query: $isWhereFounded
 */

// Files
$fileDependencyFunctions = 'dependency.php';
$fileFunctions = 'functions.php';
$fileRepository = 'repository.php';
$fileConstants = 'constants.php';
$fileUuid = 'uuid.php';
$fileJwt = 'jwt.php';
$fileFcm = 'fcm.php';

// Options
$deviceFileSystem = 'DeviceFileSystem'; // Located on device: phone | tablet
$localFileSystem = 'LocalFileSystem'; // Located on server
$googleDrive = "GoogleDrive"; // Located on google drive
$typeBase64 = "base64";
$typeImageFile = "image";
$pictureFilenameSize = 32;
$pictureLocalFilepath = "/b/service/pic/";

// Define
define('DEFAULT_DIR', dirname(__FILE__)); // ../service/api
define('API_DIR', DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR);
define('PICTURES_DIR', DIRECTORY_SEPARATOR . 'pic' . DIRECTORY_SEPARATOR);
define('MODULES_DIR', DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR);
define('INCLUDES_DIR', DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR);
define('LIBRARIES_DIR', DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR);
define('FILES_DIR', DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR);
define('DATABASE_DIR', DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR);
define('FILES_STORAGE', $localFileSystem); // LocalFileSystem | GoogleDrive
define('APPLE', 'apple');
define('GOOGLE', 'google');
define('APP', 'app');
define('IOS', 'ios');
define('ANDROID', 'android');

// Activate | Deactivate logFile: true | false
$needLogForDebug = true;
$logFilename = 'service.log';

// JWT token
$useJwtToken = true;

// Parse Post Json format data
$parseJsonData = true;

// Basic Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

if (function_exists('header_remove')) {
    header_remove('X-Powered-By'); // PHP 5.3+
} else {
    @ini_set('expose_php', 'off');
}

// Post success response data (http code 200)
function postSuccessResponseData($sessionId, $results, $foundRows) {
    global $database;
    if ($database) {
        $database->close();
    }

    global $logFile;
    if ($logFile) {
        writeToLog($logFile, "Info|Send response|#" . $sessionId . "|Rows=" . $foundRows);
        writeToLog($logFile, "Info|Session deactivated|#" . $sessionId);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo '{"results": [' . $results . '], "rows": ' . $foundRows . ', "sessionId": "' . $sessionId . '", "statusMessage": "Success", "statusCode": 0, "status": "success"}';
    exit();
}

// Post success response file (http code 200)
function postSuccessResponseFileContent($sessionId, $filename, $filetype, $fileContent) {
    global $database;
    if ($database) {
        $database->close();
    }

    $filesize = strlen($fileContent);
    $fileExtension = strtolower(substr(strrchr($filename, "."), 1));

    header('Content-Type: ' . $filetype . '/' . $fileExtension);
    header('Content-Disposition: inline; filename="' . $filename .'"');
    header('Content-Length: ' . $filesize);

    global $logFile;
    if ($logFile) {
        writeToLog($logFile, "Info|Read from storage|#" . $sessionId . "|Filename=" . $filename . " Filesize=" . $filesize);
        writeToLog($logFile, "Info|Session deactivated|#" . $sessionId);
    }

    // Clear white space before
    ob_clean();
    flush();
    echo $fileContent;
    exit();
}

function postSuccessResponseFilepath($sessionId, $filePath) {
    global $database;
    if ($database) {
        $database->close();
    }

    $fileMimeType = mime_content_type($filePath);
    header('Content-Type: ' . $fileMimeType);
    header('Content-Transfer-Encoding: binary');

    /*
    $filesize = getimagesize($filePath);
    $fp = fopen($filePath, "rb");
    header("Content-type: {$filesize['mime']}");
    header('Content-Transfer-Encoding: binary');
     */

    global $logFile;
    if ($logFile) {
        writeToLog($logFile, "Info|Read from storage|#" . $sessionId . "|Filepath=" . $filePath);
        writeToLog($logFile, "Info|Session deactivated|#" . $sessionId);
    }


    // Clear white space before
    ob_clean();
    flush();
    readfile($filePath);
    //fpassthru($fp);
    exit();
}

/** 200 - OK, 201 - Created (Insert), 202 - Accepted (Update), 203 - Non-Authoritative Information, 204 - No Content (Select), 205 - Reset Content (Delete) **/
// Post success response (http codes: 201 - 205)
function postSuccessResponseWith($sessionId, $httpSuccessCode, $httpSuccessMessage) {
    global $database;
    if ($database) {
        $database->close();
    }

    global $logFile;
    if ($logFile) {
        writeToLog($logFile, "Success|HttpCode=" . $httpSuccessCode . "|HttpMessage=" . $httpSuccessMessage . "|#" . $sessionId);
        writeToLog($logFile, "Info|Session deactivated|#" . $sessionId);
    }

    header('Content-Type: application/json; charset=utf-8');
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    header($protocol . ' ' . $httpSuccessCode . ' ' . $httpSuccessMessage, true, $httpSuccessCode);
    echo '{"sessionId": "' . $sessionId . '", "statusMessage": "' . $httpSuccessMessage . '", "statusCode": ' . $httpSuccessCode . ', "status": "success"}';
    exit();
}

function postSuccessResponse($sessionId, $httpSuccess) {
    postSuccessResponseWith($sessionId, $httpSuccess['code'], $httpSuccess['name']);
}

/** 400 - Bad request, 401 - Authorization Required, 403 - Forbidden, 404 - Not Found, 405 - Method Not Allowed, 406 - Not Acceptable, 500 - Internal Server Error, 503 - Service Unavailable **/
// Post error response (http codes)
function postErrorResponseWith($sessionId, $httpErrorCode, $httpErrorMessage, $errorCode, $errorMessage) {
    global $database;
    if ($database) {
        $database->close();
    }

    global $logFile;
    if ($logFile) {
        writeToLog($logFile, "Error|HttpCode=" . $httpErrorCode . "|HttpMessage=" . $httpErrorMessage . "|" . $errorCode . " " . $errorMessage . "|#" . $sessionId);
        writeToLog($logFile, "Info|Session deactivated|#" . $sessionId);
    }

    header('Content-Type: application/json; charset=utf-8');
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    header($protocol . ' ' . $httpErrorCode . ' ' . $httpErrorMessage, true, $httpErrorCode);
    echo '{"sessionId": "' . $sessionId . '", "statusMessage": "' . $errorMessage . '", "statusCode": ' . $errorCode . ', "status": "error"}';
    exit();
}

function postErrorResponse($sessionId, $httpError, $error) {
    postErrorResponseWith($sessionId, $httpError['code'], $httpError['name'], $error['code'], $error['name']);
}

// Get header Authorization
function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

// Get access token from header
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Get username:password from header
function getBasicCredentials() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the username:password from the header
    if (!empty($headers)) {
        if (preg_match('/Basic\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Get header parameters
function getHeaderValue($keyName) {
    global $logFile, $sessionId;
    $keyValue = '';
    if (isset($_SERVER[$keyName])) {
        $keyValue = trim($_SERVER[$keyName]);
        writeToLog($logFile, "Info|Header defined|#" . $sessionId . "|" . $keyName . "=" . $keyValue);
    } else if (isset($_SERVER['HTTP_' . strtoupper($keyName)])) { //Nginx or fast CGI
        $keyValue = trim($_SERVER["HTTP_" . strtoupper($keyName)]);
        writeToLog($logFile, "Info|Header defined|#" . $sessionId . "|" . $keyName . "=" . $keyValue);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        foreach ($requestHeaders as $name => $value) {
            if (strtoupper($name) === strtoupper($keyName)) {
                $keyValue = str_replace("'", "`", $value);
                writeToLog($logFile, "Info|Header defined|#" . $sessionId . "|" . $keyName . "=" . $keyValue);
                break;
            }
        }
    }
    return $keyValue;
}

// Get request value by name
function getRequestValue($keyName) {
    global $postRequestBody;
    $keyValue = '';
    foreach ($_REQUEST as $apiFunctionName => $apiFunctionValues) {
        if ($apiFunctionName === $keyName) {
            $keyValue = $apiFunctionValues;
            break;
        }
    }
    if (!$keyValue && $postRequestBody) {
        foreach ($postRequestBody as $name => $value) {
            if ($name === $keyName) {
                $keyValue = str_replace("'", "`", $value); /* \\\' */
                break;
            }
        }
    }
    return $keyValue;
}

// Get Post request body ( name1 = value1 & name2 = value2 )
function getPostRequestBody() {
    global $postRequestBody;
    return $postRequestBody;
}

// Get Post request json { "name1": value1, "name2": value2 }
function getPostRequestJson() {
    global $postRequestJson;
    return $postRequestJson;
}

// Get cookie
function getCookie($cookieName) {
    $cookie = '';
    if (isset($_COOKIE[$cookieName])) {
        $cookie = $_COOKIE[$cookieName];
    }
    return $cookie;
}

// Write to log
$logFile = ($needLogForDebug == true ? DEFAULT_DIR . DIRECTORY_SEPARATOR . $logFilename : null);

function writeToLog($filename, $logData) {
    $logData = date(DATE_RFC822) . "|" . $logData . PHP_EOL;
    file_put_contents($filename, $logData, FILE_APPEND | LOCK_EX);
}

// Global error (Debug)
/*
set_exception_handler(function ($e) {
    echo "Global Error: " . $e->getMessage();
    exit;
});
 */

// Get started API
$requestURI = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$queryString = $_SERVER['QUERY_STRING'];
$remoteAddr = $_SERVER['REMOTE_ADDR'];

$postRequestBody = '';
$postRequestJson = '';
$requestBody = '';
$isContentFounded = isset($moduleName);

if ($requestMethod == 'GET' && count($_REQUEST) > 0) {
    $isContentFounded = true;
} else if ($requestMethod == 'POST' || $requestMethod == 'PUT' || $requestMethod == 'DELETE') {
    $requestBody = file_get_contents('php://input');
    if ($requestBody) {
        if ($parseJsonData) {
            $postRequestJson = json_decode($requestBody);
        } else {
            parse_str($requestBody, $postRequestBody); // example: $username = $postRequestBody['username'];
        }
        $isContentFounded = true;
    }
}

// Required uuid
require_once DEFAULT_DIR . INCLUDES_DIR . $fileUuid;

// Required constants
require_once DEFAULT_DIR . INCLUDES_DIR . $fileConstants;

// Session Id
// 10-digit number
//$sessionId = crc32(mt_rand() + time());
// 9-digit number
//$sessionId = mt_rand();
// UniqueId
//$sessionId = uniqid();
// UUID
$generator = new UUID();
$sessionId = $generator->v4();

if ($isContentFounded) {
    if ($queryString != '' && $requestBody != '') {
        $paramString = $queryString . "|" . $requestBody;
    } else if ($queryString!='') {
        $paramString = $queryString;
    } else {
        $paramString = $requestBody;
    }
    if ($logFile) {
        if (strlen($paramString) > 1024) {
            $paramString = substr($paramString, 0, 1024) . " ..";
        }
        writeToLog($logFile, "Info|Session activated|#" . $sessionId . "|" . $requestMethod . "|" . $requestURI . "|" . $paramString . "|" . $remoteAddr);
    }

    if (!isset($moduleName)) {
        $moduleName = getRequestValue('module');
    }

    if ($moduleName) {
        $moduleFilename = DEFAULT_DIR . MODULES_DIR . $moduleName . '.php';
        if (file_exists($moduleFilename)) {
            if ($logFile) {
                writeToLog($logFile, "Info|Service registered|#" . $sessionId . "|" . $moduleName);
            }

            // Required functions and database repository class
            require_once DEFAULT_DIR . INCLUDES_DIR . $fileDependencyFunctions;

            require_once DEFAULT_DIR . INCLUDES_DIR . $fileFunctions;

            require_once DEFAULT_DIR . DATABASE_DIR . $fileRepository;
            $database = new Repository($logFile, $sessionId, false);

            /*
              Highly recommended make the new database user wuth restricted priviledges (for Select, Execute and Show View grants)
              for secure database transactions on Insert, Update or Delete content data. Do not use auto-function for create user.
              Make a new user by phpMyAdmin | MySQL® Database Wizard tool on hosting and restrict priviledges (Select, Execute, Show View set On)
             */

            // Define connection parameters
            $dbHostname = DATABASE_HOSTNAME;
            $dbUsername = DATABASE_USERNAME;
            $dbPassword = DATABASE_PASSWORD;
            $dbDatabase = DATABASE_SCHEME;
            //$dbCharset = 'utf8';
            //$dbCollate = '';

            if ($database->connect($dbHostname, $dbUsername, $dbPassword, $dbDatabase /*,$dbCharset, $dbCollate*/)) {
                require_once $moduleFilename;
            } else {
                // Replace database error with $database->getErrorMessage()
                postErrorResponse($sessionId, errorHttpServiceUnavailable, $errorDatabaseConnectionFailed);
            }
        } else {

            if ($logFile) {
                writeToLog($logFile, "Error|Module not found|#" . $sessionId . "|" . $moduleFilename);
            }

            postErrorResponse($sessionId, $errorHttpNotFound, $errorModuleNotFound);
        }
    } else {
        postErrorResponse($sessionId, $errorHttpNotFound, $errorModuleNameNotFound);
    }
} else {
    postErrorResponse($sessionId, $errorHttpBadRequest, $errorContentNotFound);
}

?>
