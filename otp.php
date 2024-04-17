<?php
<<<<<<< HEAD
include_once('includes/auth-head.php');
if (isset($_SESSION['MM_Contractor_Email'])) {
    try {
        if (isset($_POST['sign-in'])) {
            if (validate_csrf_token($_POST['csrf_token'])) {
                $otp_code = $_POST['otp_code'];
                $checkIfOtpExpired =  $contractor_auth->checkIfOptExpired($contractor_email, $otp_code);
                if ($checkIfOtpExpired) {
                    $contractor = $contractor_auth->get_contractor($contractor_email);
                    if ($contractor) {
                        $_SESSION['MM_Contractor_Email'] = null;
                        $_SESSION['MM_Contractor'] = $contractor->contrid;
                        $_SESSION['avatar'] = $contractor->avatar;
                        $_SESSION['contractor_name'] = $contractor->contractor_name;
                        logActivity("otp code", "true");
                        header("location: projects.php");
                        return;
                    } else {
                        logActivity("otp code", "false");
                        $_SESSION["successMessage"] = "Sorry your details are incorrect!";
                        header("location: otp.php");
                        return;
                    }
                } else {
                    logActivity("otp code", "false");
                    $mail_otp_code = $contractor_auth->otp($contractor_email);
                    $_SESSION["successMessage"] = "Sorry Otp code has been expired a new code has been sent to your email!";
                    header("location: otp.php");
                    return;
                }
            } else {
                logActivity("otp code", "false");
                $_SESSION["successMessage"] = "Sorry try again later!";
                header("location: index.php");
                return;
            }
        }

        if (isset($_POST['resend']) && $_POST['resend'] == "resend otp") {
            if (validate_csrf_token($_POST['csrf_token'])) {
                $mail_otp_code = $contractor_auth->otp($contractor_email);
                $_SESSION['MM_Contractor_Email'] = $contractor_email;
                logActivity("resend otp code", "false");
                if ($mail_otp_code) {
                    $_SESSION["successMessage"] = "Otp code has been resent to your email!";
                    header("location: otp.php");
                    return;
                } else {
                    $_SESSION["successMessage"] = "Sorry OTP could not be sent please try again later!";
                    header("location: otp.php");
                    return;
                }
            } else {
                $_SESSION["successMessage"] = "Sorry try again later!";
                header("location: index.php");
                return;
            }
        }
?>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 m-padding">
                    <div style="margin-bottom: 8vh;">
                        <img src="./images/logo-proj.png" alt="" srcset="" width="500">
                    </div>
                    <div style="margin-bottom: 4vh;">
                        <h4 style="color: #003366;">OTP Verification</h4>
                        <p style="color: black;">Check email for otp code!</p>
                    </div>
                    <!-- inputs -->
                    <form method="POST" id="otp_form">
                        <div style="margin-bottom: 4vh;">
                            <input name="otp_code" type="text" id="otp_code" placeholder="OTP Code" style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px; margin-bottom: 0px" required>
                            <p style="color: #dc2626;"></p>
                        </div>
                        <?= csrf_token_html(); ?>
                        <input type="hidden" name="sign-in" value="sign-in">
                        <div style="display: flex; gap: 2vw;">
                            <button id="submit_otp" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 16px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Sign In</button>
                        </div>
                    </form>
                    <form method="post" id="resend-form">
                        <input type="hidden" name="resend" value="resend otp">
                        <?= csrf_token_html(); ?>
                        <p style="color: black;">Didn't receive otp? <a type="submit" id="resend-btn" style="color:#003366;">resend</a></p>
                    </form>
                </div>
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                </div>
            </div>
        </div>
<?php
        include_once('includes/auth-footer.php');
    } catch (PDOException $ex) {
        customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
    }
} else {
}
?>
=======
include_once('./includes/controller.php');


if (isset($_POST['sign-in'])) {
    // check if password has expired
    $email = $_GET['email'];
    $otp_code = $_POST['otp_code'];
    $checkIfOtpExpired =  $contractor_auth->checkIfOptExpired($email, $otp_code);
    if ($checkIfOtpExpired) {
        // weka sessions
        $contractor = $contractor_auth->get_contractor($email);
        if ($contractor) {
            # code...
            $_SESSION['MM_Contractor'] = $contractor->contrid;
            $_SESSION['avatar'] = $contractor->avatar;
            $_SESSION['contractor_name'] = $contractor->contractor_name;
            header("location: projects.php");
        }
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
                                <p><img src="<?php //$company_settings->main_url . $company_settings->logo; ?>" style="height:100px; width:230px; margin-top:10px" class="imgdim" /></p>
                            </div>
                            <br />
                            <p>
                                <label for="password">Opt</label>
                                <input name="otp_code" type="text" class="input-block-level" id="password" placeholder="Enter otp code" required />
                            </p>
                            <p>
                                <input name="submit" type="submit" class="loginbutton" id="submit" value="Sign In" />
                            </p>
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
                            <p style="margin: 0px; font-size: 0.875rem; line-height: 1.25rem; letter-spacing: 0.6px;"><?= $_SESSION["errorMessage"] ?></p>
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
                        <input name="otp_code" type="text" id="otp_code" placeholder="OTP Code"  style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px;" required>
                        <p style="color: #dc2626;"></p>
                    </div>

                    <input type="hidden" name="sign-in" value="sign-in">

                    <div style="display: flex; gap: 2vw;">
                        <button id="submit-btn" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 16px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Sign In</button>
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

            $('#submit-btn').on('click', (e) => {
                e.preventDefault();

                if (!$('#otp_code').val()) {
                    $('#otp_code').next().text('field required');
                    return;
                } else {
                    $('#otp_code').next().text('');
                }

                
                $('#loginusers').submit();
            })
        })
    </script>
</body>

</html>
>>>>>>> 8d8ba83c9ea1020d974b347a8c8f191a7a1c448f
