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

if (isset($_POST['sign-in'])) {
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
            //$_SESSION['MM_Contractor'] = $contractor->contrid;
            if ($contractor->first_login) {
                header("location: set-new-password.php");
            } else {
               
                // $_SESSION['avatar'] = $contractor->avatar;
                // $_SESSION['contractor_name'] = $contractor->contractor_name;

                if (isset($_GET['action'])) {
                    $page_url = $_GET['action'];
                    header("location: $page_url");
                } else {
                    // send mail then redirect
                    $mail_otp_code = $contractor_auth->otp($email);
                    if ($mail_otp_code) {
                        header("location: otp.php?email=$email");
                    }
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
    <style>
        body {
            background-image: url('./images/back-14.jpg');
            background-repeat: no-repeat;
            background-size: 100% 100%;
            min-height: 100vh;
        }

        .m-footer {
            text-align: center;
            background-color: black;
            color: white;
            position: absolute;
            bottom: 0px;
            width: 100%;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
    <script src="https://kit.fontawesome.com/6557f5a19c.js" crossorigin="anonymous"></script>
</head>

<body>
    <!-- <p>&nbsp;</p>
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
    // include_once "includes/login-footer.php";
    ?> -->
    <div class="container">
        <div class="row">
            <div class="col-md-4" style="padding-top: 10vh;">
                <div style="margin-bottom: 8vh;">
                    <img src="./images/logo-proj.png" alt="" srcset="" width="500">
                </div>


                <!-- otp error -->
                <?php
                    if (isset($_SESSION["errorMessage"])) {
                ?>
                <div style="margin-bottom: 4vh; width: 40%;">
                    <div style="padding: 0.6vw; background-color: #fef2f2; border: 1px solid #ef4444; display:flex; gap: 1vw; align-content:center; align-items:center; border-radius: 5px; width: 100%; font-size: 16px;">
                        <i class="fa-solid fa-circle-exclamation" style="font-size: 26px; color: #dc2626; padding-left: 1vw"></i> 
                        <div>
                            <p style="margin: 0px; font-size: 1rem; line-height: 1.5rem; font-weight: bold; letter-spacing: 1px; color: #7f1d1d;">Danger Alert</p>
                            <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; letter-spacing: 0.6px;">Your login attempt failed. You may have entered a wrong username or wrong password.</p>
                        </div>
                    </div>
                </div>
                <!-- otp error -->
                <?php
                }
                unset($_SESSION["errorMessage"]);
                ?>
                <!-- inputs -->
                <form method="POST" id="loginusers">
                    <div style="margin-bottom: 4vh;"> 
                        <input name="email" type="email" id="email" placeholder="Email"  style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px;" required>
                        <p style="color: #dc2626;"></p>
                    </div>

                    <div style="margin-bottom: 4vh;">
                        <input name="password" type="password" id="password" placeholder="Password"  style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px;" required>
                        <p style="color: #dc2626;"></p>
                    </div>

                    <input type="hidden" name="sign-in" value="sign-in">

                    <div style="display: flex; gap: 2vw;">
                        <button id="submit-btn" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 16px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Sign In</button>
                        <a href="forgot-password.php"><button type="button" style="background-color: transparent; color: white; border: 1px solid #22c55e; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 16px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Forgot Password</button></a>
                    </div>
                </form>
            </div>
            <div class="col-md-8">

            </div>
        </div>
    </div>
    <div class="m-footer">
        <p>ProjTrac M&E - Your Best Result-Based Monitoring & Evaluation System .</p>
        <p>Copyright @ 2017 - 2024. ProjTrac Systems Ltd .</p>
    </div>

    <script>
        $(function() {
            console.log($('#email'));

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