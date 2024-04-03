<?php
include_once('./includes/controller.php');

$email = $_GET['email'];
if (isset($_POST['sign-in'])) {
    $otp_code = $_POST['otp_code'];
    $checkIfOtpExpired =  $contractor_auth->checkIfOptExpired($email, $otp_code);
    if ($checkIfOtpExpired) {
        $contractor = $contractor_auth->get_contractor($email);
        if ($contractor) {
            $_SESSION['MM_Contractor'] = $contractor->contrid;
            $_SESSION['avatar'] = $contractor->avatar;
            $_SESSION['contractor_name'] = $contractor->contractor_name;
            header("location: projects.php");
            return;
        }
    } else {
        header("location: otp.php?email=$email");
        return;
    }
}

if (isset($_POST['resend']) && $_POST['resend'] == "resend otp") {
    $mail_otp_code = $contractor_auth->otp($email);
    if ($mail_otp_code) {
        $_SESSION["successMessage"] = "Otp code has been resent to your email!";
        header("location: otp.php?email=$email");
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

        .m-alert {
            padding: 0.6vw;
            background-color: rgba(254, 242, 241, 0.7);
            border: 1px solid #ef4444;
            display: flex;
            gap: 1vw;
            align-content: center;
            align-items: center;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
        }


        .m-alert-danger {
            padding: 0.6vw;
            background-color: rgba(220, 252, 231, 0.7);
            border: 1px solid #16a34a;
            display: flex;
            gap: 1vw;
            align-content: center;
            align-items: center;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
        }


        #resend-btn:hover {
            cursor: pointer;
        }

        @media only screen and (max-height: 600px) {

            /* CSS rules for extra small devices */
            .m-padding {
                padding-top: 4vh;
            }
        }


        /* Small devices (portrait tablets and large phones, 600px and up) */
        @media only screen and (min-height: 601px) and (max-height: 900px) {

            /* CSS rules for small devices */
            .m-padding {
                padding-top: 10vh;
            }
        }

        /* Medium devices (landscape tablets, 900px and up) */
        @media only screen and (min-height: 901px) and (max-height: 1200px) {
            .m-padding {
                padding-top: 10vh;
            }
        }

        /* Large devices (laptops/desktops, 1200px and up) */
        @media only screen and (min-height: 1201px) {

            /* CSS rules for large devices */
            .m-padding {
                padding-top: 10vh;
            }
        }
    </style>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap-responsive.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.js"></script>
    <script src="https://kit.fontawesome.com/6557f5a19c.js" crossorigin="anonymous"></script>

</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 m-padding">
                <div style="margin-bottom: 8vh;">
                    <img src="./images/logo-proj.png" alt="" srcset="" width="500">
                </div>


                <div style="margin-bottom: 4vh;">
                    <h4 style="color: #003366;">OTP Verification</h4>
                    <p style="color: black;">Check email for otp code!</p>
                </div>
                <!-- inputs -->
                <form method="POST" id="loginusers">
                    <div style="margin-bottom: 4vh;">
                        <input name="otp_code" type="text" id="otp_code" placeholder="OTP Code" style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px; margin-bottom: 0px" required>
                        <p style="color: #dc2626;"></p>
                    </div>

                    <input type="hidden" name="sign-in" value="sign-in">



                    <div style="display: flex; gap: 2vw;">
                        <button id="submit-btn" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 16px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Sign In</button>
                    </div>
                </form>
                <form method="post" id="resend-form">
                    <input type="hidden" name="resend" value="resend otp">
                    <p style="color: black;">Didn't receive otp? <a type="button" id="resend-btn" style="color:#003366;">resend</a></p>
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

                if (!$('#otp_code').val()) {
                    $('#otp_code').next().text('field required');
                    return;
                } else {
                    $('#otp_code').next().text('');
                }


                $('#loginusers').submit();
            });


            $('#resend-btn').on('click', (e) => {
                e.preventDefault();

                $('#resend-form').submit();
            })
        })
    </script>
</body>

</html>


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
                                <p><img src="<?php //$company_settings->main_url . $company_settings->logo;
                                                ?>" style="height:100px; width:230px; margin-top:10px" class="imgdim" /></p>
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