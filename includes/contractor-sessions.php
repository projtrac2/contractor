<?php
require 'vendor/autoload.php';
require 'models/Connection.php';
include "models/Auth.php";
include "models/Company.php";
require 'models/Email.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$contractor_auth = new Auth();
$company_details = new Company();

$company_settings = $company_details->get_company_details();
$inactivity_time = 15 * 60;

$user_name = $contractor_email = $contractor_name = $avatar = '';
$auth_urls = array("index", "forgot-password", "reset-password");

session_start();
// Generate CSRF token and store it in the session
(!isset($_SESSION['csrf_token'])) ? $_SESSION['csrf_token'] =  bin2hex(random_bytes(32)) : '';


function get_current_url()
{
    $path = $_SERVER['REQUEST_URI'];
    $paths = explode("/", $path);
    $url_path = isset($paths[2]) ? explode(".", $paths[2]) : explode(".", $paths[1]);
    return $url_path[0];
}
$current_page_url = get_current_url();


// Function to generate HTML with CSRF token input
function csrf_token_html()
{
    return   '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

// Validate CSRF token
function validate_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}


if (isset($_SESSION['MM_Contractor'])) { // projects / dashboard
    if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
        session_unset();
        session_destroy();
        header("Location: index.php?action=$current_page_url");
        exit();
    } else {
        session_regenerate_id(true);
        $_SESSION['last_timestamp'] = time();
    }

    $user_name = $_SESSION['MM_Contractor'];
    $contractor_name = $_SESSION['contractor_name'];
    $avatar = $_SESSION['avatar'];
} else if (isset($_SESSION['MM_Contractor_First_Login'])) {  // set new password
    if ($current_page_url != 'otp') {
        header("location: set-new-password.php");
    }
    $user_name = $_SESSION['MM_Contractor_First_Login'];
} else if (isset($_SESSION['MM_Contractor_Email'])) {
    if ($current_page_url != 'otp') {
        header("location: otp.php");
    }
    $contractor_email = $_SESSION['MM_Contractor_Email'];
} else {
    if (!in_array($current_page_url, $auth_urls)) {
        header("location: index.php");
    }
}
