<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include_once 'projtrac-dashboard/resource/Database.php';
include_once 'projtrac-dashboard/resource/utilities.php';
include_once("includes/system-labels.php");
include_once('./models/Email.php');
include_once('./models/Connection.php');
include_once('./vendor/autoload.php');

$mail = new Email();
$projid = 2;

$results =  $mail->send_master_data_email($projid, 6, '', 1);
var_dump($results);
