<?php 
$token = (isset($_GET['token']) && !empty($_GET['token'])) ? $_GET['token'] : header("location:index.php");
require 'vendor/autoload.php';
require 'models/Connection.php';
include "models/Auth.php";
include "models/Company.php";
require 'models/Email.php';

$user_auth = new Auth();
$verified = ($token != "" && !empty($token)) ? $verified = $user_auth->verify_token($token) : false;

$company_details = new Company();
$company_settings = $company_details->get_company_details();
session_start();
if (isset($_POST['resetpassword']) && $_POST['resetpassword'] == "Reset Password") {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $token = $_POST['token'];
  $user = $user_auth->get_contractor($email);


  if ($user && $confirm_password === $password) {
    $verify = $user_auth->verify_token($token);
    if ($verify) {
      $reset = $user_auth->reset_password($email, $token, $password);
      if ($reset) {
        $_SESSION["successMessage"] =  "Successfully reset password";
        header("location:index.php");
      } else {
        $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong email address.";
        header("location:reset-password.php?token=$token");
        return;
      }
    } else {
      $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong email address.";
      header("location:reset-password.php?token=$token");
      return;
    }
  } else {
    $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong email address.";
    header("location:reset-password.php?token=$token");
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
      <div class="col-md-4 m-padding" style="">
        <div style="margin-bottom: 4vh;">
          <img src="./images/logo-proj.png" alt="" srcset="" width="400">
        </div>
        <?php
          if ($verified) {
        ?>
        <!-- inputs -->
        <form method="POST" id="loginusers">
          <div style="margin-bottom: 4vh;">
            <input name="email" type="email" id="email" placeholder="Email" style="color:black; padding: 0.4vw; border-radius: 5px; border: none; width: 40%; font-size: 14px;" required>
            <p style="color: #dc2626;"></p>
          </div>

          <div style="margin-bottom: 4vh;">
            <input name="password" type="password" id="password" placeholder="Enter new password" style="color:black; padding: 0.4vw; border-radius: 5px; border: none; width: 40%; font-size: 14px;" required>
            <p style="color: #dc2626;"></p>
          </div>

          <div style="margin-bottom: 4vh;">
            <input name="confirm_password" type="password" id="confirm_password" placeholder="Confirm new password" style="color:black; padding: 0.4vw; border-radius: 5px; border: none; width: 40%; font-size: 14px;" required>
            <p style="color: #dc2626;"></p>
          </div>

          <input type="hidden" name="token" value="<?= $token ?>">
          <input type="hidden" name="resetpassword" value="Reset Password">

          <div style="display: flex; gap: 2vw;">
            <button id="submit-btn" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Reset Password</button>
            <a href="forgot-password.php"><button type="button" style="background-color: transparent; color: white; border: 1px solid #22c55e; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Forgot Password</button></a>
          </div>
        </form>
        <?php
        } else {
        ?>
          <h1>Sorry Your Token Has expired please !!!!</h1>
        <?php
        }
        ?>

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

                if (!$('#confirm_password').val()) {
                    $('#confirm_password').next().text('field required');
                    return;
                } else {
                    $('#confirm_password').next().text('');
                }

                $('#loginusers').submit();
            })
        })
    </script>
</body>

</html>


<!-- <p>&nbsp;</p> -->
  <!-- <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div align="center">
          <div class="container-fluid1" id="content_area_cell">
            <h3 align="center" class="contenttitles">ProjTrac Monitoring, Evaluation, And Reporting System</h3>
            <p>&nbsp;</p>
            <?php
            // if ($verified) {
            ?>
              <form action="" method="POST" class="form-signin" style="margin-bottom:10px" id="loginusers">
                <div style="width:100%; height:auto; background-color:#036">
                  <p><img src="<?php // $company_settings->main_url . $company_settings->logo;
                                ?>" style="height:100px; width:230px; margin-top:10px" class="imgdim" /></p>
                </div>
                <br />
                <?php
                // if (isset($_SESSION["errorMessage"])) {
                ?>
                  <div class='alert alert-danger'>
                    <p class="errormsg">
                      <img src="images/error.png" alt="errormsg" />
                      Your login attempt failed. You may have entered a wrong username or wrong password.
                    </p>
                  </div>
                <?php
                // }
                // unset($_SESSION["errorMessage"]);
                ?>
                <p>
                  <input name="email" type="email" class="input-block-level" id="username" placeholder="Enter your email address" required />
                  <label for="password"></label>
                  <input name="password" type="password" class="input-block-level" id="password" placeholder="Enter new password" required />
                  <label for="password"></label>
                  <input name="confirm_password" type="password" class="input-block-level" id="password" placeholder="Confirm new password" required />
                </p>
                <p>
                  <input type="hidden" name="token" value="<?= $token ?>">
                  <input name="resetpassword" type="submit" class="loginbutton" id="submit" value="Reset Password" />
                </p>
                <a href="forgot-password.php">Forgot your password?</a>
              </form>
              <p>&nbsp;</p>
            <?php
            // } else {
            ?>
              <h1>Sorry Your Token Has expired please !!!!</h1>
            <?php
            // }
            ?>
          </div>
        </div>
        <p>&nbsp;</p>
      </div>
    </div>
  </div>
  <?php
  // include_once "includes/login-footer.php";
  ?> -->