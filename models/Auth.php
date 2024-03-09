<?php
class Auth
{
    protected $db;
    protected $today;
    protected $close_db;

    public function __construct()
    {
        $conn = new Connection();
        $this->db = $conn->openConnection();
        // $conn->closeConnection();
        $this->today = date('d-m-Y');
    }

    // calculate time and return in minutes
    private function calculate_time($created_at)
    {
        $date1 = strtotime($created_at);
        $date2 = strtotime(date("Y-m-d h:i:s"));
        $diff = abs($date2 - $date1);

        $years = floor($diff / (365 * 60 * 60 * 24));

        $months = floor(($diff - $years * 365 * 60 * 60 * 24)
            / (30 * 60 * 60 * 24));

        $days = floor(($diff - $years * 365 * 60 * 60 * 24 -
            $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        $hours = floor(($diff - $years * 365 * 60 * 60 * 24
            - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24)
            / (60 * 60));

        $minutes = floor(($diff - $years * 365 * 60 * 60 * 24
            - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
            - $hours * 60 * 60) / 60);

        return ((60 - $minutes) > 0) ? true : false;
    }

    private function generate_string($str_length)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $str_length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    private function send_mail($user_id, $fullname, $email, $notification_type_id, $page_url, $priority)
    {
        $notification_group_id = 7;
        $mail = new Email();
        $token = $mail->get_auth_token($fullname, $email, '');
        $notification = $mail->get_notifications($priority, $notification_group_id);
        $notification_id = $notification->id;
        return $mail->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 0);
    }


    // get contractor details from the database
    public function get_contractor($email)
    {
        $sql = $this->db->prepare("SELECT * FROM tbl_contractor WHERE email=:email");
        $sql->execute(array(":email" => $email));
        $count_contractor = $sql->rowCount();
        $contractor = $sql->fetch();
        return ($count_contractor > 0) ? $contractor : false;
    }

    // get contractor details from the database
    public function get_contractor_by_id($contrid)
    {
        $get_contractor = $this->db->prepare("SELECT * FROM tbl_contractor WHERE contrid=:contrid");
        $get_contractor->execute(array(":contrid" => $contrid));
        $count_contractor = $get_contractor->rowCount();
        $contractor = $get_contractor->fetch();
        return ($count_contractor > 0) ? $contractor : false;
    }

    // send mail to contractor and block if attempts reached limit
    public function suspicious_activity($email)
    {
        $user = $this->get_contractor($email);
        $mail_response = false;
        if ($user) {
            $sql = $this->db->prepare("UPDATE tbl_contractor SET  `disabled`=1 WHERE email=:email");
            $results = $sql->execute(array(":email" => $email));
            if ($results) {
                $mail_response = $this->send_mail($user->contrid, $user->fullname, $email, 10, "index.php", 3);
            }
        }
        return $mail_response;
    }


    // login functionality
    public function login($email, $password)
    {
        $contractor = $this->get_contractor($email);
        return ($contractor && (password_verify($password, $contractor->password))) ? $contractor : false;
    }

    // send reset link to contractor email
    public function forgot_password($email)
    {
        $contractor = $this->get_contractor($email);
        $response = false;
        if ($contractor) {
            $token = $this->generate_string(64);
            $create_reset_token = $this->db->prepare("INSERT INTO tbl_contractor_password_resets (`email`, `token`) VALUES (:email, :token)");
            $results = $create_reset_token->execute(array(":email" => $email, ":token" => $token));
            if ($results) {
                $page_url = "reset-password.php?token=$token";
                $mail_response = $this->send_mail($contractor->contrid, $contractor->contractor_name, $email, 8, $page_url, 2);
                $response = ($mail_response) ? true : false;
            }
        }
        return $response;
    }

    // Verify token when reseting pasword
    public function verify_token($token)
    {
        $get_contractor = $this->db->prepare("SELECT * FROM tbl_contractor_password_resets WHERE token=:token ORDER BY created_at DESC LIMIT 1");
        $get_contractor->execute(array(":token" => $token));
        $count_contractor = $get_contractor->rowCount();
        $token_data = $get_contractor->fetch();
        return ($count_contractor > 0 && $this->calculate_time($token_data->created_at))  ? true : false;
    }

    // Reset password
    public function reset_password($email, $token, $password)
    {
        $contractor = $this->get_contractor($email);
        $mail_response = false;
        if ($contractor) {
            $stored_token_verify = $this->verify_token($token);
            if ($stored_token_verify) {
                $sql = $this->db->prepare("UPDATE tbl_contractor SET  `password`=:password WHERE email=:email");
                $results = $sql->execute(array(":password" => password_hash($password, PASSWORD_DEFAULT), ":email" => $email));

                if ($results) {
                    $mail_response =  $this->send_mail($contractor->contrid, $contractor->contractor_name, $email, 22, "index.php", 2);
                }
            }
        }
        return $mail_response;
    }

    // for new contractor and those who would like to change their passwords
    public function change_password($contractor_id, $password)
    {
        $contractor = $this->get_contractor_by_id($contractor_id);
        $response = false;
        if ($contractor) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = $this->db->prepare("UPDATE tbl_contractor SET password=:password, first_login=0 WHERE contrid=:contractor_id");
            $results = $sql->execute(array(":password" => $password_hashed, ":contractor_id" => $contractor_id));
            if ($results) {
                $response = $this->send_mail($contractor->contrid, $contractor->fullname, $contractor->email, 22, "index.php", 2);
                $response = ($results) ? $contractor : false;
            }
        }
        return $response;
    }

    public function store_login_history($contractor_id)
    {
        $create_reset_token = $this->db->prepare("INSERT INTO tbl_contractor_login_history (`contractor_id` ) VALUES (:contractor_id)");
        $results = $create_reset_token->execute(array(":contractor_id" => $contractor_id));
        return ($results) ? true : false;
    }
}
