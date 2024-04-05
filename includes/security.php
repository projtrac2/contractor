<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $message = "Error: [$errno] $errstr - $errfile:$errline";
    error_log($message . PHP_EOL, 3, "error_log.log");
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


// logActivity("view", "true");
