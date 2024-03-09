<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


session_start();
if (isset($_SESSION['MM_Contractor']))
    header("location:projects.php");

require 'vendor/autoload.php';
require 'models/Connection.php';
include "models/Auth.php";
include "models/Company.php";
require 'models/Email.php';

$contractor_auth = new Auth();
$company_details = new Company();
$company_settings = $company_details->get_company_details();