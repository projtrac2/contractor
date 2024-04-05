<?php
include_once('./includes/contractor-sessions.php');
try {
    $company_details = new Company();
    $company_settings = $company_details->get_company_details();
    if (isset($_POST['forgotpassword']) && $_POST['forgotpassword'] == "Forgot Password") {
        $email = $_POST['email'];
        $contractor_auth = new Auth();
        $contractor = $contractor_auth->get_contractor($email);
        if ($contractor) {
            $forgot = $contractor_auth->forgot_password($email);
            $_SESSION["successMessage"] =  "Reset link has been sent to your email please use it to reset you password.";
            header("location:forgot-password.php");
            return;
        } else {
            $_SESSION["errorMessage"] =  "Your login attempt failed. You may have entered a wrong email address.";
            header("location:forgot-password.php");
            return;
        }
    }
    include_once('includes/auth-head.php');
?>

    <div class="container">
        <div class="row">
            <div class="col-md-4" style="padding-top: 8vh;">
                <div style="margin-bottom: 6vh;">
                    <img src="./images/logo-proj.png" alt="" srcset="" width="400">
                </div>
                <div style="margin-bottom: 4vh;">
                    <h4 style="color: #003366;">Forgot your password ?</h4>
                    <p style="color: #808080;">Enter your email to reset it!</p>
                </div>
                <!-- inputs -->
                <form method="POST" id="loginusers">
                    <div style="margin-bottom: 4vh;">
                        <input name="email" type="email" id="email" placeholder="Email" style="color:black; padding: 0.6vw; border-radius: 5px; border: none; width: 40%; font-size: 16px;" required>
                        <p style="color: #dc2626;"></p>
                    </div>
                    <input type="hidden" name="forgotpassword" value="Forgot Password">
                    <div style="display: flex; gap: 2vw;">
                        <button id="submit-btn" type="button" style="background-color: #22c55e; color: white; border: none; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 0.5px; border-radius: 5px;">Forgot Password</button>
                        <a href="index.php"><button type="button" style="background-color: transparent; color: white; border: 1.5px solid #003366; padding-left: 2vw; padding-right: 2vw; padding-top: 0.5vw; padding-bottom: 0.5vw; font-size: 14px; font-weight: 600; letter-spacing: 0.5px; border-radius: 5px;">Go To Login</button></a>
                    </div>
                </form>
            </div>
            <div class="col-md-8">
            </div>
        </div>
    </div>
<?php
    include_once('includes/auth-footer.php');
} catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
}
?>