<?php
require 'vendor/autoload.php';
require 'models/Connection.php';
include "models/Auth.php";
include "models/Company.php";
require 'models/Email.php';

$company_details = new Company();
$company_settings = $company_details->get_company_details();
session_start();

if (isset($_POST['forgotpassword']) && $_POST['forgotpassword'] == "Forgot Password") {
    $email = $_POST['email'];
    $contractor_auth = new Auth();
    $contractor = $contractor_auth->get_contractor($email);
    if ($contractor) {
        $forgot = $contractor_auth->forgot_password($email);
        $_SESSION["errorMessage"] =  "Reset link has been sent to your email please use it to reset you password.";
        $_SESSION['type'] = "success";
        header("location:forgot-password.php");
        return;
    } else {
        $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong email address.";
        $_SESSION['type'] = "error";
        header("location:forgot-password.php");
        return;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="no-cache">
    <meta http-equiv="Expires" content="-1">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>Result-Based Monitoring &amp; Evaluation System</title>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />
    <style media="screen">
        body {
            background-color: #036;
        }

        #loginuser {
            margin-left: 10px;
            margin-right: 10px;
            border: 1px solid #999;
            border-radius: 5px;
            box-shadow: 5px 5px 5px #888888;
            max-width: 100%;
            min-width: 50%;
        }

        .form-signin {
            max-width: 350px;
            padding: 19px 29px 29px;
            margin: 0 auto 20px;
            background-color: #E8E8E8;
            border: 1px solid #e5e5e5;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            -moz-box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            box-shadow: 5px 5px 5px #CCCCCC;
            margin-top: 20px;
        }

        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
            font-family: Verdana, Geneva, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .form-signin input[type="text"] {
            font-size: 14px;
            height: auto;
            margin-bottom: 15px;
            padding: 7px 9px;
            background-image: url(images/user.png);
            background-repeat: no-repeat;
            background-position: 98%;
            font-family: Verdana, Geneva, sans-serif;
            color: #000;
        }

        .form-signin input[type="password"] {
            font-size: 14px;
            height: auto;
            margin-bottom: 15px;
            padding: 7px 9px;
            background-image: url(images/pwd.png);
            background-repeat: no-repeat;
            background-position: 98%;
            font-family: Verdana, Geneva, sans-serif;
            color: #000;
        }

        .loginbutton {
            width: 130px;
            height: 40px;
            font-family: Verdana, Geneva, sans-serif;
            font-size: 14px;
            color: #FFF;
            background-color: #06C;
            border: 5px solid #06C;
            border-radius: 3px;
            cursor: pointer;
            -webkit-appearance: button;
            font-weight: bold;
        }

        .errormsg {
            width: 80%;
            height: 30%;
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            border: #930 1px solid;
            padding-left: 10px;
            padding-right: 10px;
            color: #000;
            padding-top: 10px;
            background-color: #F9B6AC;
            text-align: center;
            margin-left: 10px;
            padding-bottom: 10px;
        }

        .success_msg {
            width: 80%;
            height: 30%;
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            border: #930 1px solid;
            padding-left: 10px;
            padding-right: 10px;
            color: #000;
            padding-top: 10px;
            background-color: #77FF33;
            text-align: center;
            margin-left: 10px;
            padding-bottom: 10px;
        }

        .loginbutton {
            width: 200px;
            height: 40px;
            font-family: Verdana, Geneva, sans-serif;
            font-size: 14px;
            color: #FFF;
            background-color: #06C;
            border: 5px solid #06C;
            border-radius: 3px;
            cursor: pointer;
            -webkit-appearance: button;
            font-weight: bold;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        #contentleft {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            float: left;
            width: 800px;
            padding-left: 5px;
            padding-right: 5px;
        }

        #footer {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            color: #999;
            height: 20px;
        }

        .row {
            margin-left: 0px;
            margin-top: 10px;
        }

        #content_area_cell {
            max-width: 100%;
            min-width: 70%;
            margin-left: 0px;
            background-color: #fff;
            padding-left: 5px;
        }

        .container-fluid1 {
            outline: 1px dashed #999;
        }

        .contenttitles {
            font-family: Candara;
            font-size: 28px;
            font-weight: bold;
            color: #036;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
            border-bottom-width: thin;
            border-bottom-style: dashed;
            border-bottom-color: #999;
            padding-left: 5px;
            padding-right: 5px;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
</head>

<body>
    <p>&nbsp;</p>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div align="center">
                    <div class="container-fluid1" id="content_area_cell">
                        <h3 align="center" class="contenttitles">ProjTrac Monitoring, Evaluation, And Reporting System</h3>
                        <p>&nbsp;</p>
                        <form action="" method="POST" class="form-signin" style="margin-bottom:10px" id="loginusers">
                            <div style="width:100%; height:auto; background-color:#036">
                                <p><img src="<?= $company_settings->main_url . $company_settings->logo; ?>" style="height:100px; width:230px; margin-top:10px" class="imgdim" /></p>
                            </div>
                            <br />
                            <?php
                            if (isset($_SESSION["errorMessage"])) {
                                $type = $_SESSION['type'];
                                if ($type == "error") {
                            ?>
                                    <div class='alert alert-error'>
                                        <p class="errormsg">
                                            <img src="images/error.png" alt="success_msg" />
                                            <?= $_SESSION["errorMessage"] ?>
                                        </p>
                                    </div>
                                <?php
                                } else {
                                ?>
                                    <div class='alert alert-success'>
                                        <p class="success_msg">
                                            <img src="assets/images/apply.gif" alt="success" />
                                            <?= $_SESSION["errorMessage"] ?>
                                        </p>
                                    </div>
                            <?php
                                }
                            }
                            unset($_SESSION["errorMessage"]);
                            ?>
                            <p>
                                <input name="email" type="email" class="input-block-level" id="username" placeholder="Enter your email address" required />
                            </p>
                            <p>
                                <input name="forgotpassword" type="submit" class="loginbutton" id="submit" value="Forgot Password" />
                            </p>
                            <a href="index.php">Go to login</a>
                        </form>
                        <p>&nbsp;</p>
                    </div>
                </div>
                <p>&nbsp;</p>
            </div>
        </div>
    </div>
    <?php
    include_once "includes/login-footer.php";
    ?>
</body>

</html>