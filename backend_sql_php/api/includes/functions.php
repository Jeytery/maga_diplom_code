<?php

/*
 * Beta Orionis (Rigel) PHP Scripts
 * Rigel The White Blue Giant, The Leg of Orion, Osiris
 * Amon Ra Eye
 * API functions v.1.0.0
 * 3bit.app 2024
 */

// Tokens and password helpers
function getBase64EncodedData($encode, $data) {
    $ret_val = '';
    $encode_size = strlen($encode);
    $data_size = strlen($data);
    if ($encode_size === 0 || $data_size === 0) {
        return $ret_val;
    }
    for ($i = 0; $i < $data_size; $i++) {
        $ret_val .= (chr(ord($data[$i]) ^ ord($encode[$i % $encode_size])));
    }
    return base64_encode( $ret_val );
}

function getBase64DecodedData($decode, $encoded_data) {
    $ret_val = '';
    $decode_size = strlen($decode);
    $data = base64_decode($encoded_data);
    $data_size = strlen($data);
    if ($decode_size === 0 || $data_size === 0) {
        return $ret_val;
    }
    for ($i = 0; $i < $data_size; $i++ ) {
        $ret_val .= (chr(ord($data[$i]) ^ ord($decode[$i % $decode_size])));
    }
    return $ret_val;
}

function getTokenOrPasswordData($salt = "I6g5NM3L47", $length = 16) {
    return substr(str_shuffle(strtolower(sha1($salt . rand() . time()))), 0, $length);
}

// SQL queries helpers
$isWhereFounded = false;

function getWhereExpression($expression) {
    $whereParam = '';
    if ($expression) {
        $whereParam = getWhereOrAndKeyword();
        $whereParam .= $expression;
    }
    return $whereParam;
}

function getWhereParam($paramName, $useLike, $paramValue, $isChars = false) {
    $whereParam = '';
    if ($paramName) {
        $whereParam = getWhereOrAndKeyword();
        $whereParam .= $paramName . ($useLike == true ? " LIKE '" . $paramValue . "'" : ($isChars == false ? " = " . $paramValue : " = '" . $paramValue . "'"));
    }
    return $whereParam;
}

function getWhereBetweenParam($paramName, $paramValue1, $paramValue2) {
    $whereParam = '';
    if ($paramName && $paramValue1 && $paramValue2) {
        $whereParam = getWhereOrAndKeyword();
        $whereParam .= $paramName . " BETWEEN '" . $paramValue1 . "' AND '" . $paramValue2 . "'";
    }
    return $whereParam;
}

function getWhereOrAndKeyword() {
    global $isWhereFounded;
    $whereParam = '';
    if ($isWhereFounded) {
        $whereParam = " AND ";
    } else {
        $whereParam = " WHERE ";
        $isWhereFounded = true;
    }
    return $whereParam;
}

// Get param ( <param> = <value>)
function getParam($paramValue, $delim) {
    $param = '';
    $pos = strpos($paramValue, $delim);
    if ($pos > 0) {
        $param = substr($paramValue, 0, $pos);
    }
    return $param;
}

// Get value ( <param> = <value>)
function getValue($paramValue, $delim) {
    $value = '';
    $pos = strrpos($paramValue, $delim);
    if ($pos > 0) {
        $value = substr($paramValue, $pos + 1);
    }
    return $value;
}

// Randoms

function getRandomString($length) {
    $keys = array_merge(range('a', 'z'), range('A', 'Z'));
    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }
    return $key;
}

// Files helpers

function getFileContentPath($filename, $objectName) {
    $pos = strpos(DEFAULT_DIR . DIRECTORY_SEPARATOR, API_DIR);
    $subpath = substr(DEFAULT_DIR, 0, $pos);
    $filepath = $subpath . PICTURES_DIR . $objectName . DIRECTORY_SEPARATOR . $filename;
    return $filepath;
}

function getFileContent($filename, $objectName) {
    $pos = strpos(DEFAULT_DIR . DIRECTORY_SEPARATOR, API_DIR);
    $subpath = substr(DEFAULT_DIR, 0, $pos);
    $filepath = $subpath . PICTURES_DIR . $objectName . DIRECTORY_SEPARATOR . $filename;
    $data = file_get_contents($filepath);
    return $data;
}

function getBase64FileContent($filename, $objectName) {
    $data = getFileContent($filename, $objectName);
    return base64_encode($data);
}

// Timezone helpers

function minutesToTimezone($minutesOffset) {
  if ($minutesOffset === 0) {
    return '+0:00';
  }

  $hours = intval($minutesOffset / 60);
  $minutes = abs($minutesOffset % 60);

  $direction = $minutesOffset > 0 ? '+' : '-';
  $timezone = $direction . str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);

  return $timezone;
}

function secondsToTimezone($secondsOffset) {
  $minutesOffset = intval($secondsOffset / 60);
  return minutesToTimezone($minutesOffset);
}

// UUID helpers

function isValidUUID($uuid) {
    return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
}

// Email helpers
/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function isValidEmail($email) {
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex) {
      $isValid = false;
   } else {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64) {
         // local part length exceeded
         $isValid = false;
      } else if ($domainLen < 1 || $domainLen > 255) {
         // domain part length exceeded
         $isValid = false;
      } else if ($local[0] == '.' || $local[$localLen-1] == '.') {
         // local part starts or ends with '.'
         $isValid = false;
      } else if (preg_match('/\\.\\./', $local)) {
         // local part has two consecutive dots
         $isValid = false;
      } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
         // character not valid in domain part
         $isValid = false;
      } else if (preg_match('/\\.\\./', $domain)) {
         // domain part has two consecutive dots
         $isValid = false;
      } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local))) {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local))) {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

// Networks helpers

/*
    Send network data
    Url - Link to service
    RequestType - 'GET' | 'POST'
    Data - Data to send
    Token - Access token)
 */

function sendRequest($url, $requestType, $token, $data = null) {

    global $logFile;

    global $sessionId;

    $jsonData = ($requestType === 'POST' && $data) ? json_encode($data) : null;

    $connection = curl_init();
    curl_setopt_array($connection, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $requestType,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Authorization: Bearer " . $token
        )
    ) );

    $response = curl_exec($connection);
    $error = curl_error($connection);
    curl_close($connection);

    if ($error) {
        writeToLog($logFile, "Error|Send resuest|#" . $sessionId . "|" . "Url=" . $url . " data=" . $jsonData . " error=" . $error);
    } else {
        writeToLog($logFile, "Success|Send resuest|#" . $sessionId . "|" . "Url=" . $url . " data=" . $jsonData . " response=" . $response);
    }
}

?>
