<?php
session_start();
if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
    session_unset();
    session_destroy();
    header("Location: index.php?action=$current_page_url");
    exit();
} else {
    session_regenerate_id(true);
    $_SESSION['last_timestamp'] = time();
}

// Generate CSRF token and store it in the session
(!isset($_SESSION['csrf_token'])) ? $_SESSION['csrf_token'] = bin2hex(random_bytes(32)) : '';
(!isset($_SESSION['MM_Contractor'])) ? header("location: index.php") : "";

$user_name = $_SESSION['MM_Contractor'];
$contractor_name = $_SESSION['contractor_name'];
$avatar = $_SESSION['avatar'];
