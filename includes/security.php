<?php

// Audit Trail
function logActivity($action, $outcome)
{
    global $db, $user_name, $current_page_url;
    $today = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $sql = $db->prepare("INSERT INTO tbl_audit_log (user_id,user_type,page_url,action,outcome,ip_address) VALUES (:user_id,:user_type,:page_url,:action,:outcome,:ip_address)");
    $results = $sql->execute(array(':user_id' => $user_name, ":user_type" => 2, ":page_url" => $current_page_url, ":action" => $action, ':outcome' => $outcome, ':ip_address' => $ip_address));
    return $results;
}

// Function to generate HTML with CSRF token input
function csrf_token_html()
{
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// Validate CSRF token
function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}

csrf_token_html();
logActivity("view", "true");
