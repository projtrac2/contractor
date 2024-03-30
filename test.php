<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $message = "Error: [$errno] $errstr - $errfile:$errline";
    error_log($message . PHP_EOL, 3, "error_log.txt");
}
set_error_handler("customErrorHandler");

include_once 'projtrac-dashboard/resource/Database.php';
include_once 'projtrac-dashboard/resource/utilities.php';
include_once("includes/system-labels.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<?php
try {
    $query_rsMyP =  $db->prepare("SELECT * FROM tbl_projects WHERE deted='0' AND projid = '$projid'");
    $query_rsMyP->execute();
    $row_rsMyP = $query_rsMyP->fetch();
} catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
}
?>

<body>

</body>

</html>