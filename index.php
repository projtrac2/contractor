<?php
include_once('./includes/controller.php');

//check if can login again
if (isset($_SESSION['attempt_again'])) {
    $now = time();
    if ($now >= $_SESSION['attempt_again']) {
        unset($_SESSION['attempt']);
        unset($_SESSION['attempt_again']);
    }
}

if (isset($_POST['submit'])) {
    //set login attempt if not set
    if (!isset($_SESSION['attempt'])) {
        $_SESSION['attempt'] = 0;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];
    $contractor = $contractor_auth->login($email, $password);
    
    //check if there are 3 attempts already
    // if ($_SESSION['attempt'] == $company_settings->login_attempts) {
    //     $_SESSION['errorMessage'] = 'Attempt limit reached';
    //     $contractor_auth->suspicious_activity($email);
    //     header("location:index.php");
    //     return;
    // } else {
        if ($contractor) {
            //unset our attempt
            unset($_SESSION['attempt']);
            $_SESSION['MM_Contractor'] = $contractor->contrid;
            if ($contractor->first_login) {
                header("location: set-new-password.php");
            } else {
               
                $_SESSION['avatar'] = $contractor->avatar;
                $_SESSION['contractor_name'] = $contractor->fullname;

                if (isset($_GET['action'])) {
                    $page_url = $_GET['action'];
                    header("location: $page_url");
                } else {
                    header("location: projects.php");
                }
            }
        } else {
            $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong username or wrong password.";
            //this is where we put our 3 attempt limit
            $_SESSION['attempt'] += 1;
            //set the time to allow login if third attempt is reach
            if ($_SESSION['attempt'] == 3) {
                $_SESSION['attempt_again'] = time() + (5 * 60);
                //note 5*60 = 5mins, 60*60 = 1hr, to set to 2hrs change it to 2*60*60
            }
            header("location:index.php");
            return;
        }
    // }
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

        .form-signin input[type="email"] {
            font-size: 14px;
            height: auto;
            margin-bottom: 15px;
            padding: 7px 9px;
            background-image: url(images/email.png);
            background-repeat: no-repeat;
            background-position: 98%;
            font-family: Verdana, Geneva, sans-serif;
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
                            ?>
                                <div class='alert alert-danger'>
                                    <p class="errormsg">
                                        <img src="images/error.png" alt="errormsg" />
                                        Your login attempt failed. You may have entered a wrong username or wrong password.
                                    </p>
                                </div>
                            <?php
                            }
                            unset($_SESSION["errorMessage"]);
                            ?>
                            <p>
                                <input name="email" type="email" class="input-block-level" id="username" placeholder="Enter your email address" required />
                                <label for="password"></label>
                                <input name="password" type="password" class="input-block-level" id="password" placeholder="Enter your password" required />
                            </p>
                            <p>
                                <input name="submit" type="submit" class="loginbutton" id="submit" value="Sign In" />
                            </p>
                            <a href="forgot-password.php">Forgot your password?</a>
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