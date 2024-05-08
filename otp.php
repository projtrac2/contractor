<?php
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
                        // logActivity("otp code", "true");
                        header("location: projects.php");
                        return;
                    } else {
                        // logActivity("otp code", "false");
                        $_SESSION["successMessage"] = "Sorry your details are incorrect!";
                        header("location: otp.php");
                        return;
                    }
                } else {
                    // logActivity("otp code", "false");
                    $mail_otp_code = $contractor_auth->otp($contractor_email);
                    $_SESSION["successMessage"] = "Sorry Otp code has been expired a new code has been sent to your email!";
                    header("location: otp.php");
                    return;
                }
            } else {
                // logActivity("otp code", "false");
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