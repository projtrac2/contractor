<?php 
include_once '../../projtrac-dashboard/resource/Database.php';
include_once '../../projtrac-dashboard/resource/utilities.php';
include_once("../../includes/system-labels.php");

require '../../vendor/autoload.php';
require '../../models/Connection.php';
require '../../models/Email.php';


$currentdate = date("Y-m-d");
session_start();
(!isset($_SESSION['MM_Contractor'])) ? header("location: index.php") : "";

$user_name = $_SESSION['MM_Contractor'];
$avatar = $_SESSION['avatar'];
$fullname = $_SESSION['contractor_name'];

$today = date('Y-m-d');
$mail = new Email();
