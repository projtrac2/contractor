<?php 
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