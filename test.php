<?php
require 'vendor/autoload.php';
require 'Models/Connection.php';
include "Models/Auth.php";
include "Models/Company.php";
require 'Models/Email.php';


//code...

session_start();

if (isset($_SESSION['MM_Username'])) header("location:dashboard.php");

//check if can login again
if (isset($_SESSION['attempt_again'])) {
    $now = time();
    if ($now >= $_SESSION['attempt_again']) {
        unset($_SESSION['attempt']);
        unset($_SESSION['attempt_again']);
    }
}

$user_auth = new Auth();
$company_details = new Company();
$company_settings = $company_details->get_company_details();

if (isset($_POST['sign-in'])) {
    //set login attempt if not set
    if (!isset($_SESSION['attempt'])) {
        $_SESSION['attempt'] = 0;
    }
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = $user_auth->get_user($email);

    //check if there are 3 attempts already
    if ($_SESSION['attempt'] == 3) {
        $_SESSION['errorMessage'] = 'Attempt limit reached';
        $user_auth->suspicious_activity($email);
        header("location:index.php");
        return;
    } else {
        if ($user) {
            //unset our attempt
            unset($_SESSION['attempt']);
            if ($user->first_login) {
                header("location: set-new-password.php");
            } else {
                if (isset($_GET['action'])) {
                    $page_url = $_GET['action'];
                    header("location: $page_url");
                    return;
                } else {
                    $mail_otp_code = $user_auth->otp($email);
                    $mail_otp_code = true;
                    if ($mail_otp_code) {
                        header("location: otp.php?email=$email");
                        return;
                    } else {
                        $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong username or wrong password.";
                        header("location:index.php");
                        return;
                    }
                }
            }
        } else {
            //this is where we put our 3 attempt limit
            $_SESSION['attempt'] += 1;
            //set the time to allow login if third attempt is reach
            if ($_SESSION['attempt'] == 3) {
                $_SESSION['attempt_again'] = time() + (5 * 60);
                //note 5*60 = 5mins, 60*60 = 1hr, to set to 2hrs change it to 2*60*60
            }

            $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong username or wrong password.";
            header("location:index.php");
            return;
        }
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="no-cache">
    <meta http-equiv="Expires" content="-1">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>Result-Based Monitoring &amp; Evaluation System</title>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
    <script src="https://kit.fontawesome.com/6557f5a19c.js" crossorigin="anonymous"></script>

</head>

<body>
    <div class="m-footer">
        <p>ProjTrac M&E - Your Best Result-Based Monitoring & Evaluation System .</p>
        <p>Copyright @ 2017 - 2024. ProjTrac Systems Ltd .</p>
    </div>

    <?php
    if (isset($_SESSION["errorMessage"])) {
    ?>
        <div style="position:absolute; bottom: 12vh; right: 2vw; width: 35%;">
            <div class="m-alert">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 26px; color: #dc2626; padding-left: 1vw"></i>
                <div>
                    <p style="margin: 0px; font-size: 1rem; line-height: 1.5rem; font-weight: bold; letter-spacing: 1px; color: #7f1d1d;">Danger Alert</p>
                    <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; letter-spacing: 0.6px;"><?= $_SESSION["errorMessage"] ?></p>
                </div>
            </div>
        </div>
    <?php
    }
    unset($_SESSION["errorMessage"]);
    ?>


    <?php
    if (isset($_SESSION["successMessage"])) {
    ?>
        <div style="position:absolute; bottom: 12vh; right: 2vw; width: 35%;">
            <div class="m-alert-danger">
                <i class="fa-solid fa-circle-check" style="font-size: 26px; color: #16a34a; padding-left: 1vw"></i>
                <div>
                    <p style="margin: 0px; font-size: 1rem; line-height: 1.5rem; font-weight: bold; letter-spacing: 1px; color: #052e16;">Success Alert</p>
                    <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; letter-spacing: 0.6px;"><?= $_SESSION["successMessage"] ?></p>
                </div>
            </div>
        </div>
    <?php
    }
    unset($_SESSION["successMessage"]);
    ?>

    <script>
        $(function() {
            $('#submit-btn').on('click', (e) => {
                e.preventDefault();

                if (!$('#email').val()) {
                    $('#email').next().text('field required');
                    return;
                } else {
                    $('#email').next().text('');
                }

                if (!$('#password').val()) {
                    $('#password').next().text('field required');
                    return;
                } else {
                    $('#password').next().text('');
                }
                console.log($('#loginusers').submit());
                $('#loginusers').submit();
            })
        })
    </script>

</body>

</html>