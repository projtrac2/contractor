<?php
include_once '../../projtrac-dashboard/resource/Database.php';
include_once '../../projtrac-dashboard/resource/utilities.php';
include_once("../../includes/system-labels.php");

require '../../vendor/autoload.php';
require '../../models/Connection.php';
require '../../models/Email.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(E_ALL);
ini_set('display_errors', 1);



$currentdate = date("Y-m-d");
session_start();
(!isset($_SESSION['MM_Contractor'])) ? header("location: index.php") : "";

$user_name = $_SESSION['MM_Contractor'];
$avatar = $_SESSION['avatar'];
$fullname = $_SESSION['contractor_name'];

$today = date('Y-m-d');
$mail = new Email();


// Validate CSRF token
function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}

function get_current_url()
{
    $path = $_SERVER['REQUEST_URI'];
    $paths = explode("/", $path);
    $url_path = isset($paths[2]) ? explode(".", $paths[2]) : explode(".", $paths[1]);
    return $url_path[0];
}
$current_page_url = get_current_url();

function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $message = "Error: [$errno] $errstr - $errfile:$errline";
    error_log($message . PHP_EOL, 3, "../log/error_log.log");
}

set_error_handler("customErrorHandler");

// Audit Trail
function logActivity($action, $outcome)
{
    global $db, $user_name, $current_page_url;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $sql = $db->prepare("INSERT INTO tbl_audit_log (user_id,user_type,page_url,action,outcome,ip_address) VALUES (:user_id,:user_type,:page_url,:action,:outcome,:ip_address)");
    $results = $sql->execute(array(':user_id' => $user_name, ":user_type" => 2, ":page_url" => $current_page_url, ":action" => $action, ':outcome' => $outcome, ':ip_address' => $ip_address));
    return $results;
}


logActivity("view", "true");
