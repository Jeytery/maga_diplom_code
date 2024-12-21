<?php

/*
 * Beta Orionis (Rigel) PHP Scripts
 * Rigel The White Blue Giant, The Leg of Orion, Osiris
 * Amon Ra Eye
 * API constants v.1.0.1
 * 3bit.app 2024
 */

// Url
define('SUPPORT_PUSH_NOTIFICATION', 'https://3bit.app/b/support/api/v1/push/notification/');

// Credentials
define('DATABASE_HOSTNAME', 'localhost'); // Hostname
define('DATABASE_USERNAME', 'xbitoakr_beta_orionis'); // Username
define('DATABASE_PASSWORD', 'O04]nB(a}mlQ'); // Password
define('DATABASE_SCHEME', 'xbitoakr_beta_orionis_service'); // Database
define('JWT_SECRET_KEY', 'AA12xx24-0BsrYc73Mq21II'); // JWT Secret Key

// Password Salt
define('PASSWORD_SALT', 'Vmd693GhbnL6');

// UUID
define('UUID_FULL_ACCESS', '00000000-0000-0000-0000-000000000000'); // Full Access UUID
define('UUID_ADMIN_ACCESS', '5d7edae8-0b92-11ef-a8a3-1633d0f9fc3b'); // Admin Access UUID

// Attempt Types
define('ATTEMPT_TYPE_USER_AUTH', 'userAuth'); // User Auth
define('ATTEMPT_TYPE_USER_REGISTER', 'userRegister'); // User Register
define('ATTEMPT_TYPE_USER_TOKEN', 'userToken'); // User Token
define('ATTEMPT_TYPE_USER_PASSWORD_RECOVERY', 'userPasswordRecovery'); // User Password Recovery

// Notification Status
define('NOTIFICATION_STATUS_UNDEFINED', 0); // Undefined
define('NOTIFICATION_STATUS_SENT', 1); // Sent

// Notification Type
define('NOTIFICATION_PUSH', "PushNotification"); // Push
define('NOTIFICATION_EMAIL', "EmailNotification"); // Email
define('NOTIFICATION_SMS', "SmsNotification"); // Email

// Privileges (Roles | Users)
define('PRIV_CAN_SELECT', 'canSelect'); // Can Select
define('PRIV_CAN_INSERT', 'canInsert'); // Can Insert
define('PRIV_CAN_UPDATE', 'canUpdate'); // Can Update
define('PRIV_CAN_DELETE', 'canDelete'); // Can Delete

// User Role
define('USER_ROLE_NAME_ADMIN', 'admin'); // Admin
define('USER_ROLE_NAME_CUSTOMER', 'customer'); // Customer
define('USER_ROLE_NAME_ANONYMOUS', 'anonymous'); // Anonymous

// User Status
define('USER_STATUS_NOT_ACTIVATED', 0); // Not activated
define('USER_STATUS_ACTIVATED', 1); // Activated
define('USER_STATUS_BANNED', -1); // Banned
define('USER_STATUS_TEMPORARY_BANNED', -2); // Temporary banned
define('USER_STATUS_AUTH_DISABLED', -10); // Auth disabled
define('USER_STATUS_REMOVED', -11); // Removed

// Success Http Codes
define('SUCCESS_HTTP_CODE_OK', 200);
define('SUCCESS_HTTP_CODE_CREATED', 201);
define('SUCCESS_HTTP_CODE_ACCEPTED', 202);
define('SUCCESS_HTTP_CODE_NON_AUTHOR_INFO', 203);
define('SUCCESS_HTTP_CODE_NO_CONTENT', 204);
define('SUCCESS_HTTP_CODE_RESET_CONTENT', 205);

define('SUCCESS_HTTP_NAME_OK', "Ok");
define('SUCCESS_HTTP_NAME_CREATED', "Created");
define('SUCCESS_HTTP_NAME_ACCEPTED', "Accepted");
define('SUCCESS_HTTP_NAME_NON_AUTHOR_INFO', "Non-Authoritative Information");
define('SUCCESS_HTTP_NAME_NO_CONTENT', "No Content");
define('SUCCESS_HTTP_NAME_RESET_CONTENT', "Reset Content");

// Error Http Codes
define('ERROR_HTTP_CODE_BAD_REQUEST', 400);
define('ERROR_HTTP_CODE_AUTH_REQUIRED', 401);
define('ERROR_HTTP_CODE_FORBIDDEN', 403);
define('ERROR_HTTP_CODE_NOT_FOUND', 404);
define('ERROR_HTTP_CODE_NOT_ACCEPTABLE', 406);
define('ERROR_HTTP_CODE_INTERNAL_SERVER_ERROR', 500);
define('ERROR_HTTP_CODE_SERVICE_UNAVAILABLE', 503);

// Error Http Names
define('ERROR_HTTP_NAME_BAD_REQUEST', "Bad request");
define('ERROR_HTTP_NAME_AUTH_REQUIRED', "Authorization Required");
define('ERROR_HTTP_NAME_FORBIDDEN', "Forbidden");
define('ERROR_HTTP_NAME_NOT_FOUND', "Not Found");
define('ERROR_HTTP_NAME_NOT_ACCEPTABLE', "Not Acceptable");
define('ERROR_HTTP_NAME_INTERNAL_SERVER_ERROR', "Internal Server Error");
define('ERROR_HTTP_NAME_SERVICE_UNAVAILABLE', "Service Unavailable");

// Error Codes
define('ERROR_CODE_CONTENT_NOT_FOUND', -1001);
define('ERROR_CODE_MODULE_NAME_NOT_FOUND', -1002);
define('ERROR_CODE_MODULE_NOT_FOUND', -1003);
define('ERROR_CODE_DATABASE_CONNECTION_FAILED', -1004);
define('ERROR_CODE_AUTH_FAILED', -1005);
define('ERROR_CODE_USER_NOT_ACTIVATED', -1006);
define('ERROR_CODE_ACCESS_TOKEN_EXPIRED', -1007);
define('ERROR_CODE_INVALID_ACCESS_TOKEN', -1008);
define('ERROR_CODE_INVALID_USERNAME_OR_PASSWORD', -1009);
define('ERROR_CODE_INVALID_UPDATE_TOKEN', -1010);
define('ERROR_CODE_SOMETHING_WENT_WRONG', -1011);
define('ERROR_CODE_USER_BANNED', -1012);
define('ERROR_CODE_USER_TEMPORARY_BANNED', -1013);
define('ERROR_CODE_ROLE_PRIV_NOT_FOUND', -1014);
define('ERROR_CODE_USER_PRIV_NOT_FOUND', -1015);
define('ERROR_CODE_USER_REMOVED', -1016);
define('ERROR_CODE_USER_EMAIL_ALREADY_EXIST', -1017);
define('ERROR_CODE_USER_USERNAME_ALREADY_EXIST', -1018);
define('ERROR_CODE_TOO_MANY_ATTEMPTS', -1019);
define('ERROR_CODE_DATA_NOT_FOUND', -1020);
define('ERROR_CODE_DATA_NOT_AVAILABLE', -1021);
define('ERROR_CODE_DATABASE_ERROR', -1100);

// Error Names
define('ERROR_NAME_CONTENT_NOT_FOUND', "Content not found");
define('ERROR_NAME_MODULE_NAME_NOT_FOUND', "Module name not found");
define('ERROR_NAME_MODULE_NOT_FOUND', "Module not found");
define('ERROR_NAME_DATABASE_CONNECTION_FAILED', "Database connection failed");
define('ERROR_NAME_AUTH_FAILED', "Authorization failed");
define('ERROR_NAME_USER_NOT_ACTIVATED', "User not activated");
define('ERROR_NAME_ACCESS_TOKEN_EXPIRED', "Access token expired");
define('ERROR_NAME_INVALID_ACCESS_TOKEN', "Invalid access token");
define('ERROR_NAME_INVALID_USERNAME_OR_PASSWORD', "Invalid username or password");
define('ERROR_NAME_INVALID_UPDATE_TOKEN', "Invalid update token");
define('ERROR_NAME_SOMETHING_WENT_WRONG', "Something went wrong! Try again later");
define('ERROR_NAME_USER_BANNED', "User has been banned");
define('ERROR_NAME_USER_TEMPORARY_BANNED', "User temporary banned");
define('ERROR_NAME_ROLE_PRIV_NOT_FOUND', "Role privilege not found");
define('ERROR_NAME_USER_PRIV_NOT_FOUND', "User privilege not found");
define('ERROR_NAME_USER_REMOVED', "User ash been removed");
define('ERROR_NAME_USER_EMAIL_ALREADY_EXIST', "User email already exist");
define('ERROR_NAME_USER_USERNAME_ALREADY_EXIST', "User username already exist");
define('ERROR_NAME_TOO_MANY_ATTEMPTS', "Too many attempts, no more allowed");
define('ERROR_NAME_DATA_NOT_AVAILABLE', "Data not available");
define('ERROR_NAME_DATA_NOT_FOUND', "Data not found");

// Http Success | Error
$successHttpOk = array("code" => SUCCESS_HTTP_CODE_OK, "name" => SUCCESS_HTTP_NAME_OK);
$successHttpCreated = array("code" => SUCCESS_HTTP_CODE_CREATED, "name" => SUCCESS_HTTP_NAME_CREATED);
$successHttpAccepted = array("code" => SUCCESS_HTTP_CODE_ACCEPTED, "name" => SUCCESS_HTTP_NAME_ACCEPTED);
$successHttpNonAuthorInfo = array("code" => SUCCESS_HTTP_CODE_NON_AUTHOR_INFO, "name" => SUCCESS_HTTP_NAME_NON_AUTHOR_INFO);
$successHttpNoContent = array("code" => SUCCESS_HTTP_CODE_NO_CONTENT, "name" => SUCCESS_HTTP_NAME_NO_CONTENT);
$successHttpResetContent = array("code" => SUCCESS_HTTP_CODE_RESET_CONTENT, "name" => SUCCESS_HTTP_NAME_RESET_CONTENT);

$errorHttpBadRequest = array("code" => ERROR_HTTP_CODE_BAD_REQUEST, "name" => ERROR_HTTP_NAME_BAD_REQUEST);
$errorHttpAuthRequired = array("code" => ERROR_HTTP_CODE_AUTH_REQUIRED, "name" => ERROR_HTTP_NAME_AUTH_REQUIRED);
$errorHttpForbidden = array("code" => ERROR_HTTP_CODE_FORBIDDEN, "name" => ERROR_HTTP_NAME_FORBIDDEN);
$errorHttpNotFound = array("code" => ERROR_HTTP_CODE_NOT_FOUND, "name" => ERROR_HTTP_NAME_NOT_FOUND);
$errorHttpNotAcceptable = array("code" => ERROR_HTTP_CODE_NOT_ACCEPTABLE, "name" => ERROR_HTTP_NAME_NOT_ACCEPTABLE);
$errorHttpInternalServerError = array("code" => ERROR_HTTP_CODE_INTERNAL_SERVER_ERROR, "name" => ERROR_HTTP_NAME_INTERNAL_SERVER_ERROR);
$errorHttpServiceUnavailable = array("code" => ERROR_HTTP_CODE_SERVICE_UNAVAILABLE, "name" => ERROR_HTTP_NAME_SERVICE_UNAVAILABLE);

$httpResponces = array(
    "successHttpOk" => $successHttpOk,
    "successHttpCreated" => $successHttpCreated,
    "successHttpAccepted" => $successHttpAccepted,
    "successHttpNonAuthorInfo" => $successHttpNonAuthorInfo,
    "successHttpNoContent" => $successHttpNoContent,
    "successHttpResetContent" => $successHttpResetContent,
    "errorHttpBadRequest" => $errorHttpBadRequest,
    "errorHttpAuthRequired" => $errorHttpAuthRequired,
    "errorHttpForbidden" => $errorHttpForbidden,
    "errorHttpNotFound" => $errorHttpNotFound,
    "errorHttpNotAcceptable" => $errorHttpNotAcceptable,
    "errorHttpInternalServerError" => $errorHttpInternalServerError,
    "errorHttpServiceUnavailable" => $errorHttpServiceUnavailable
);

// Error
$errorContentNotFound = array("code" => ERROR_CODE_CONTENT_NOT_FOUND, "name" => ERROR_NAME_CONTENT_NOT_FOUND);
$errorModuleNameNotFound = array("code" => ERROR_CODE_MODULE_NAME_NOT_FOUND, "name" => ERROR_NAME_MODULE_NAME_NOT_FOUND);
$errorModuleNotFound = array("code" => ERROR_CODE_MODULE_NOT_FOUND, "name" => ERROR_NAME_MODULE_NOT_FOUND);
$errorDatabaseConnectionFailed = array("code" => ERROR_CODE_DATABASE_CONNECTION_FAILED, "name" => ERROR_NAME_DATABASE_CONNECTION_FAILED);
$errorAuthFailed = array("code" => ERROR_CODE_AUTH_FAILED, "name" => ERROR_NAME_AUTH_FAILED);
$errorUserNotActivated = array("code" => ERROR_CODE_USER_NOT_ACTIVATED, "name" => ERROR_NAME_USER_NOT_ACTIVATED);
$errorAccessTokenExpired = array("code" => ERROR_CODE_ACCESS_TOKEN_EXPIRED, "name" => ERROR_NAME_ACCESS_TOKEN_EXPIRED);
$errorInvalidAccessToken = array("code" => ERROR_CODE_INVALID_ACCESS_TOKEN, "name" => ERROR_NAME_INVALID_ACCESS_TOKEN);
$errorInvalidUsernameOrPassword = array("code" => ERROR_CODE_INVALID_USERNAME_OR_PASSWORD, "name" => ERROR_NAME_INVALID_USERNAME_OR_PASSWORD);
$errorInvalidUpdateToken = array("code" => ERROR_CODE_INVALID_UPDATE_TOKEN, "name" => ERROR_NAME_INVALID_UPDATE_TOKEN);
$errorSomethingWentWrong = array("code" => ERROR_CODE_SOMETHING_WENT_WRONG, "name" => ERROR_NAME_SOMETHING_WENT_WRONG);
$errorUserBanned = array("code" => ERROR_CODE_USER_BANNED, "name" => ERROR_NAME_USER_BANNED);
$errorUserTemporaryBanned = array("code" => ERROR_CODE_USER_TEMPORARY_BANNED, "name" => ERROR_NAME_USER_TEMPORARY_BANNED);
$errorRolePrivNotFound = array("code" => ERROR_CODE_ROLE_PRIV_NOT_FOUND, "name" => ERROR_NAME_ROLE_PRIV_NOT_FOUND);
$errorUserPrivNotFound = array("code" => ERROR_CODE_USER_PRIV_NOT_FOUND, "name" => ERROR_NAME_USER_PRIV_NOT_FOUND);
$errorUserRemoved = array("code" => ERROR_CODE_USER_REMOVED, "name" => ERROR_NAME_USER_REMOVED);
$errorUserEmailAlreadyExist = array("code" => ERROR_CODE_USER_EMAIL_ALREADY_EXIST, "name" => ERROR_NAME_USER_EMAIL_ALREADY_EXIST);
$errorUserUsernameAlreadyExist = array("code" => ERROR_CODE_USER_USERNAME_ALREADY_EXIST, "name" => ERROR_NAME_USER_USERNAME_ALREADY_EXIST);
$errorTooManyAttempts = array("code" => ERROR_CODE_TOO_MANY_ATTEMPTS, "name" => ERROR_NAME_TOO_MANY_ATTEMPTS);
$errorDataNotFound = array("code" => ERROR_CODE_DATA_NOT_FOUND, "name" => ERROR_NAME_DATA_NOT_FOUND);
$errorDataNotAvailable = array("code" => ERROR_CODE_DATA_NOT_AVAILABLE, "name" => ERROR_NAME_DATA_NOT_AVAILABLE);
$errorDatabaseError = array("code" => ERROR_CODE_DATABASE_ERROR, "name" => "");

$errorResponces = array(
    "errorContentNotFound" => $errorContentNotFound,
    "errorModuleNameNotFound" => $errorModuleNameNotFound,
    "errorModuleNotFound" => $errorModuleNotFound,
    "errorDatabaseConnectionFailed" => $errorDatabaseConnectionFailed,
    "errorAuthFailed" => $errorAuthFailed,
    "errorUserNotActivated" => $errorUserNotActivated,
    "errorAccessTokenExpired" => $errorAccessTokenExpired,
    "errorInvalidAccessToken" => $errorInvalidAccessToken,
    "errorInvalidUsernameOrPassword" => $errorInvalidUsernameOrPassword,
    "errorInvalidUpdateToken" => $errorInvalidUpdateToken,
    "errorSomethingWentWrong" => $errorSomethingWentWrong,
    "errorUserBanned" => $errorUserBanned,
    "errorUserTemporaryBanned" => $errorUserTemporaryBanned,
    "errorRolePrivNotFound" => $errorRolePrivNotFound,
    "errorUserPrivNotFound" => $errorUserPrivNotFound,
    "errorUserRemoved" => $errorUserRemoved,
    "errorUserEmailAlreadyExist" => $errorUserEmailAlreadyExist,
    "errorUserUsernameAlreadyExist" => $errorUserUsernameAlreadyExist,
    "errorTooManyAttempts" => $errorTooManyAttempts,
    "errorDataNotFound" => $errorDataNotFound,
    "errorDataNotAvailable" => $errorDataNotAvailable,
    "errorDatabaseError" => $errorDatabaseError
);

?>
