<?php
include_once('./includes/contractor-sessions.php');
if ((isset($_GET['token']) && !empty($_GET['token']))) {
  try {
    $token =  $_GET['token'];
    $verified = ($token != "" && !empty($token)) ? $verified = $user_auth->verify_token($token) : false;
    if ($verified) {
      if (isset($_POST['resetpassword']) && $_POST['resetpassword'] == "Reset Password") {
        if (validate_csrf_token($_POST['csrf_token'])) {
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
            <div style="margin-bottom: 4vh;">
              <img src="./images/logo-proj.png" alt="" srcset="" width="400">
            </div>
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
              <?= csrf_token_html(); ?>
              <input type="hidden" name="resetpassword" value="Reset Password">
              <div style="display: flex; gap: 2vw;">
                <button id="submit-btn" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Reset Password</button>
                <a href="forgot-password.php"><button type="button" style="background-color: transparent; color: white; border: 1px solid #22c55e; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Forgot Password</button></a>
              </div>
            </form>
          </div>
          <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 m-padding">
          </div>
        </div>
      </div>
<?php
    } else {
    }
    include_once('includes/auth-footer.php');
  } catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
  }
} else {
}
?>