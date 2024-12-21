<?php

$defaultServiceApiSubpath = DIRECTORY_SEPARATOR . "v1" . DIRECTORY_SEPARATOR;
$currentDir = dirname(__FILE__);

/*
    HTTP request methods:

    HTTP defines a set of request methods to indicate the desired action to be performed for a given resource.

    GET (select), POST (insert), PUT (update), DELETE (delete)

 */

$requestMethod = $_SERVER['REQUEST_METHOD'];

$pos = strpos($currentDir, $defaultServiceApiSubpath);

if ($pos !== false) {

    $moduleSubpath = substr($currentDir, $pos + 4);

    $moduleSubpath = str_replace(DIRECTORY_SEPARATOR, '_', $moduleSubpath);

    $moduleName = strtolower($requestMethod) . '_' . $moduleSubpath;
    $homeDir = substr($currentDir, 0, $pos);

    require_once $homeDir . DIRECTORY_SEPARATOR . 'index.php';
}

?>
