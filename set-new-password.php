<?php
include_once('./includes/contractor-sessions.php');
if ((isset($_SESSION['MM_Contractor_First_Login']) && !empty($_SESSION['MM_Contractor_First_Login']))) {
    try {
        if (isset($_POST['setpass']) && $_POST['setpass'] == "setpassword") {
            if (validate_csrf_token($_POST['csrf_token'])) {
                $confirm_password = $_POST['confirm_password'];
                $password = $_POST['password'];
                if ($confirm_password === $password) {
                    $contractor_id = $_SESSION['MM_Contractor_First_Login'];
                    $contractor = $contractor_auth->change_password($contractor_id, $password);
                    if ($contractor) {
                        $_SESSION['avatar'] = $contractor->avatar;
                        $_SESSION['contractor_name'] = $contractor->contractor_name;
                        $_SESSION["success"] =  "Successfully changed  password";
                        header("location: projects.php");
                    } else {
                        $_SESSION["errorMessage"] =  "Error changing your password";
                        header("location:set-new-password.php");
                        return;
                    }
                } else {
                    $_SESSION["errorMessage"] =  "Check the passwords they do not match";
                    header("location:set-new-password.php");
                    return;
                }
            } else {
                $_SESSION["successMessage"] = "Sorry try again later!";
                header("location: index.php");
                return;
            }
        }
        include_once('includes/auth-head.php');
?>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 m-padding">
                    <div style="margin-bottom: 8vh;">
                        <img src="./images/logo-proj.png" alt="" srcset="" width="500">
                    </div>
                    <div style="margin-bottom: 4vh;">
                        <h4 style="color: #003366;"> Login</h4>
                        <p style="color: black;"> Welcome this is your first time to login, change password.</p>
                    </div>
                    <form method="POST" id="loginusers">
                        <div style="margin-bottom: 4vh;">
                            <input name="password" type="password" id="password" placeholder="New Password" style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px;" required>
                            <p style="color: #dc2626;"></p>
                        </div>
                        <div style="margin-bottom: 4vh;">
                            <input name="confirm_password" type="password" id="confirm_password" placeholder="Confirm Password" style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px;" required>
                            <p style="color: #dc2626;"></p>
                        </div>
                        <input type="hidden" name="sign-in" value="sign-in">
                        <input type="hidden" name="setpass" value="setpassword">
                        <?= csrf_token_html(); ?>
                        <input type="hidden" name="token" value="<?= $token ?>">
                        <div style="display: flex; gap: 2vw;">
                            <button type="submit" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Reset Password</button>
                            <a href="index.php">
                                <button type="button" style="background-color: transparent; color: white; border: 1px solid #22c55e; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 1px; border-radius: 5px;">Sign In</button>
                            </a>
                        </div>
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