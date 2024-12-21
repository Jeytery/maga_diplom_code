<?php

/*
  PHP-JWT https://github.com/firebase/php-jwt
 */

require_once DEFAULT_DIR . LIBRARIES_DIR . 'firebase-php-jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Token signature key JWT_SECRET_KEY ($secretKey)

// Token encode
function generateToken($userId, $roleId, $username, $tokenExpire) {

    $secretKey = JWT_SECRET_KEY;

    $tokenId    = base64_encode(random_bytes(32));
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $issuedAt + $tokenExpire;
    $serverName = '3bit.app';

    $token = [
        'iat'  => $issuedAt,
        'jti'  => $tokenId,
        'iss'  => $serverName,
        'nbf'  => $notBefore,
        'exp'  => $expire,
        'data' => [
            'userId' => $userId,
            'roleId' => $roleId,
            'username' => $username,
        ]
    ];

    return JWT::encode($token, $secretKey, 'HS256');
}

// Token decode
function validateToken($token) {

    $secretKey = JWT_SECRET_KEY;

    try {

        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        //if ($decoded->iss !== '3bit.app') {}
        return $decoded; //$decoded->data->userId;
    } catch (\Exception $e) {
        //global $logFile;
        //writeToLog($logFile, "validateToken error = " . $e);
        return null;
    }
}

?>
