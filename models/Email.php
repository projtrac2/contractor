<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email
{
    protected $host;
    protected $smtp_auth;
    protected $username;
    protected $password;
    protected $smtp_secure;
    protected $port;
    protected $db;
    protected $sender;
    protected $url;
    protected $org;
    protected $org_email;
    protected $created_at;
    protected $contractor_url;

    public function __construct()
    {
        $conn = new Connection();
        $this->db = $conn->openConnection();
        $settings = $this->email_settings();
        $this->host = $settings->host;
        $this->smtp_auth = $settings->SMTPAuth;
        $this->username = $settings->username;
        $this->password = $settings->password;
        $this->smtp_secure = $settings->SMTPSecure;
        $this->port = $settings->port;
        $this->sender = $settings->username;
        $this->created_at = date('Y-m-d');

        $company_settings = $this->company_settings();
        if ($company_settings) {
            $this->url = $company_settings->main_url;
            $this->contractor_url = $company_settings->contractor_url;
            $this->org = $company_settings->company_name;
            $this->org_email = $company_settings->email_address;
        }
    }

    private function email_settings()
    {
        $query_settings = $this->db->prepare("SELECT * FROM tbl_email_settings");
        $query_settings->execute();
        $settings = $query_settings->fetch();
        $row_count = $query_settings->rowCount();
        return ($row_count > 0) ? $settings : false;
    }

    public function company_settings()
    {
        $query_url =  $this->db->prepare("SELECT * FROM tbl_company_settings");
        $query_url->execute();
        $row_url = $query_url->fetch();
        $count = $query_url->rowCount();
        return ($count > 0) ? $row_url : false;
    }

    public function get_contractor_details($contrid)
    {
        $query_rsUser = $this->db->prepare("SELECT * FROM tbl_contractor WHERE contrid=:contrid");
        $query_rsUser->execute(array(":contrid" => $contrid));
        $row_rsUser = $query_rsUser->fetch();
        $count_rsUser = $query_rsUser->rowCount();
        return  $count_rsUser > 0 ? $row_rsUser : false;
    }

    public function get_user_details($user_id)
    {
        $query_rsUser = $this->db->prepare("SELECT t.*, t.email AS email, tt.title AS ttitle, u.userid FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title WHERE userid = :user_id ORDER BY ptid ASC");
        $query_rsUser->execute(array(":user_id" => $user_id));
        $row_rsUser = $query_rsUser->fetch();
        $count_rsUser = $query_rsUser->rowCount();
        return  $count_rsUser > 0 ? $row_rsUser : false;
    }


    public function get_chief_officer($department_id)
    {
        $sql = $this->db->prepare("SELECT t.*, t.email AS email, tt.title AS ttitle, u.userid,u.password FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title  WHERE t.designation=6 AND  department=:department_id");
        $sql->execute(array(":department_id" => $department_id));
        $count_user = $sql->rowCount();
        $user = $sql->fetch();
        return $count_user > 0 ? $user : false;
    }

    public function get_director($directorate_id)
    {
        $sql = $this->db->prepare("SELECT t.*, t.email AS email, tt.title AS ttitle, u.userid,u.password FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title  WHERE t.designation=7 AND  directorate=:directorate");
        $sql->execute(array(":directorate" => $directorate_id));
        $count_user = $sql->rowCount();
        $user = $sql->fetch();
        return $count_user > 0 ? $user : false;
    }

    public function get_stand_in($user_id)
    {
        $query_rStandin = $this->db->prepare("SELECT * FROM tbl_project_team_leave  WHERE owner=:user_name AND status = 2");
        $query_rStandin->execute(array(":user_name" => $user_id));
        $row_rStandin = $query_rStandin->fetch();
        $total_rStandin = $query_rStandin->rowCount();
        return ($total_rStandin > 0) ? $this->get_user_details($row_rStandin->assignee) : false;
    }

    public function get_project_stand_in($user_id, $projid)
    {
        $query_rStandin = $this->db->prepare("SELECT * FROM tbl_project_team_leave  WHERE owner=:user_name AND status = 2 AND projid=:projid");
        $query_rStandin->execute(array(":user_name" => $user_id, ":projid" => $projid));
        $row_rStandin = $query_rStandin->fetch();
        $total_rStandin = $query_rStandin->rowCount();
        return ($total_rStandin > 0) ? $this->get_user_details($row_rStandin->assignee) : false;
    }


    public function get_date($projid, $stage_id)
    {
        $sql = $this->db->prepare("SELECT * FROM tbl_project_stage_actions WHERE projid=:projid AND stage=:stage_id AND sub_stage=0");
        $sql->execute(array(":projid" => $projid, ":stage_id" => $stage_id));
        $totalRows = $sql->rowCount();
        $Rows = $sql->fetch();
        return $totalRows > 0 ? $Rows->created_at : false;
    }

    public function get_members($projid, $workflow_stage, $substage_id)
    {
        $query_rsMember = $this->db->prepare("SELECT * FROM tbl_projmembers  WHERE projid =:projid AND stage=:workflow_stage AND sub_stage=:substage_id");
        $query_rsMember->execute(array(":projid" => $projid, ":workflow_stage" => $workflow_stage, ":substage_id" => $substage_id));
        $total_rsMember = $query_rsMember->rowCount();
        $rows_rsMember = $query_rsMember->fetch();
        return ($total_rsMember > 0) ? $this->get_user_details($rows_rsMember->responsible) : false;
    }

    public function get_team_leader($projid)
    {
        $query_rsMember = $this->db->prepare("SELECT * FROM tbl_projmembers  WHERE projid =:projid AND team_type=4 AND role=2");
        $query_rsMember->execute(array(":projid" => $projid));
        $total_rsMember = $query_rsMember->rowCount();
        $rows_rsMember = $query_rsMember->fetch();
        return ($total_rsMember > 0) ? $this->get_user_details($rows_rsMember->responsible) : false;
    }

    public function get_notification($stage_id)
    {
        $sql =  $this->db->prepare("SELECT * FROM tbl_notifications WHERE stage_id=:stage_id");
        $sql->execute(array(":stage_id" => $stage_id));
        $row = $sql->fetch();
        $count = $sql->rowCount();
        return ($count > 0) ? $row : false;
    }

    public function get_notifications($priority, $notification_group_id)
    {
        $sql =  $this->db->prepare("SELECT * FROM tbl_notifications WHERE priority=:priority AND notification_group_id=:notification_group_id");
        $sql->execute(array(":priority" => $priority, ":notification_group_id" => $notification_group_id));
        $row = $sql->fetch();
        $count = $sql->rowCount();
        return ($count > 0) ? $row : false;
    }

    function get_activity_monitoring_token($recipient_name, $email, $password)
    {
        $varMap = [];
        $token = array(
            'FIRST_NAME' => $recipient_name,
            'EMAIL' => $email,
            "PASSWORD" => $password,
        );

        $pattern = '[%s]';
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        return  $varMap;
    }

    function get_master_data_token($recipient_name, $action, $project, $deadline, $stage, $responsible_id)
    {
        $varMap = [];
        $responsible_id = $responsible_id;
        $responsible_name = $responsible_email = $responsible_contact = '';
        if ($responsible_id != '') {
            $responsible = $this->get_user_details($responsible_id);
            if ($responsible) {
                $responsible_name =  $responsible->ttitle . ". " . $responsible->fullname;
                $responsible_email = $responsible->email;
                $responsible_contact = $responsible->phone;
            }
        }

        $token = array(
            'FIRST_NAME' => $recipient_name,
            'ACTION' => $action,
            "PROJECT_NAME" => $project,
            "DEADLINE" => $deadline,
            "STAGE" => $stage,
            "RESPONSIBLE_NAME" => $responsible_name,
            "PHONE" => $responsible_contact,
            "EMAIL" => $responsible_email,
        );

        $pattern = '[%s]';
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        return  $varMap;
    }

    function get_auth_token($recipient_name, $email, $password)
    {
        $varMap = [];
        $token = array(
            'FIRST_NAME' => $recipient_name,
            'EMAIL' => $email,
            "PASSWORD" => $password,
        );

        $pattern = '[%s]';
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        return  $varMap;
    }

    function send_master_data_email($projid, $notification_type_id, $user_id)
    {
        $notification_group_id = 2;
        $responsible_id = '';
        $projid_hashed = base64_encode("projid54321{$projid}");
        $sql_projects = $this->db->prepare("SELECT * FROM `tbl_projects` p left join `tbl_programs` g on g.progid=p.progid WHERE projid=:projid");
        $sql_projects->execute(array(":projid" => $projid));
        $totalRows_projects = $sql_projects->rowCount();
        $Rows_projects = $sql_projects->fetch();
        $response = false;
        if ($totalRows_projects > 0) {
            $stage_id = $Rows_projects->projstage;
            $substage_id = $Rows_projects->proj_substage;
            $project = $Rows_projects->projname;
            $sector_id = $Rows_projects->projdept;
            $directorate_id = $Rows_projects->directorate;

            $sql = $this->db->prepare("SELECT * FROM tbl_project_workflow_stage WHERE priority = :priority ");
            $sql->execute(array(":priority" => $stage_id));
            $row = $sql->fetch();
            $rows = $sql->rowCount();

            if ($rows > 0) {
                $section_id = ($row->section_id  != 0) ? $row['section_id'] : $sector_id;
                $directorate_id = ($row->directorate_id != 0) ? $row['directorate_id'] : $directorate_id;
                $date = $this->get_date($projid, $stage_id);
                $notification = $this->get_notification($stage_id);

                if ($notification && $date) {
                    $notification_id = $notification->id;
                    $notifications = $notification->notification;
                    $page_url = $notification->page_url . $projid_hashed;
                    $duration = ($substage_id == 0 || $substage_id == 1) ? $notification->data_entry : $notification->approval;
                    $action = ($substage_id == 0 || $substage_id == 1) ? "Data Entry" : "Approval";
                    $due_date = date('Y-m-d', strtotime($date . ' + ' . $duration . ' days'));

                    if ($notification_type_id == 4) {
                        if ($substage_id == 0 || $substage_id == 2) {
                            $user = $this->get_chief_officer($section_id);
                            if ($user) {
                                $availability = $user->availability;
                                $user_id = $user->userid;
                                $fullname = $user->title . ' ' . $user->fullname;
                                $main_url = $availability == 1 ? $page_url : '';
                                $responsible = $this->get_director($directorate_id);
                                if ($responsible) {
                                    $responsible_id = $responsible->userid;
                                    if ($responsible->availability == 0) {
                                        $stand_in = $this->get_stand_in($responsible_id);
                                        $responsible_id = $stand_in ? $stand_in->userid : '';
                                    }
                                }

                                $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $main_url, 1);

                                if ($availability == 0) {
                                    $user = $this->get_stand_in($user_id);
                                    if ($user) {
                                        $user_id = $user->userid;
                                        $fullname = $user->title . ' ' . $user->fullname;
                                        $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                        $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 1);
                                    }
                                }
                            }
                        } else {
                            $user = $this->get_director($directorate_id);
                            if ($user) {
                                $availability = $user->availability;
                                $user_id = $user->userid;
                                $fullname = $user->title . ' ' . $user->fullname;
                                $main_url = $availability == 1 ? $page_url : '';
                                $responsible = $this->get_members($projid, $stage_id, $substage_id);
                                if ($responsible) {
                                    $responsible_id = $responsible->userid;
                                    if ($responsible->availability == 0) {
                                        $stand_in = $this->get_stand_in($responsible_id);
                                        $responsible_id = $stand_in ? $stand_in->userid : '';
                                    }
                                }
                                $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $main_url, 1);
                                if ($availability == 0) {
                                    $user = $this->get_stand_in($user_id);
                                    if ($user) {
                                        $user_id = $user->userid;
                                        $fullname = $user->title . ' ' . $user->fullname;
                                        $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                        $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 1);
                                    }
                                }
                            }
                        }
                    } else if ($notification_type_id == 5) {
                        if ($substage_id == 0 || $substage_id == 2) {
                            $user = $this->get_director($directorate_id);
                            if ($user) {
                                $availability = $user->availability;
                                $user_id = $user->userid;
                                $fullname = $user->title . ' ' . $user->fullname;
                                $main_url = $availability == 1 ? $page_url : '';

                                $responsible = $this->get_members($projid, $stage_id, $substage_id);
                                if ($responsible) {
                                    $responsible_id = $responsible->userid;
                                    if ($responsible->availability == 0) {
                                        $stand_in = $this->get_stand_in($responsible_id);
                                        $responsible_id = $stand_in ? $stand_in->userid : '';
                                    }
                                }

                                $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $main_url, 1);
                                if ($availability == 0) {
                                    $user = $this->get_stand_in($user_id);
                                    if ($user) {
                                        $availability = $user->availability;
                                        $user_id = $user->userid;
                                        $fullname = $user->title . ' ' . $user->fullname;
                                        $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                        $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 1);
                                    }
                                }
                            }
                        } else {
                            $user = $this->get_members($projid, $stage_id, $substage_id);
                            if ($user) {
                                $availability = $user->availability;
                                $user_id = $user->userid;
                                $fullname = $user->title . ' ' . $user->fullname;
                                $main_url = $availability == 1 ? $page_url : '';
                                $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $main_url, 1);
                            }
                        }
                    } else if ($notification_type_id == 6) {
                        if ($stage_id == 8 && $substage_id == 2) {
                            $user = $this->get_team_leader($projid);
                            if ($user) {
                                $availability = $user->availability;
                                $user_id = $user->userid;
                                $fullname = $user->title . ' ' . $user->fullname;
                                $main_url = $availability == 1 ? $page_url : '';
                                $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $main_url, 1);
                                if ($availability == 0) {
                                    $user = $this->get_stand_in($user_id);
                                    if ($user) {
                                        $user_id = $user->userid;
                                        $fullname = $user->title . ' ' . $user->fullname;
                                        $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                        $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 1);
                                    }
                                }
                            }
                        } else {
                            $user = $this->get_director($directorate_id);
                            if ($user) {
                                $availability = $user->availability;
                                $user_id = $user->userid;
                                $fullname = $user->title . ' ' . $user->fullname;
                                $main_url = $availability == 1 ? $page_url : '';
                                $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $main_url, 1);
                                if ($availability == 0) {
                                    $user = $this->get_stand_in($user_id);
                                    if ($user) {
                                        $user_id = $user->userid;
                                        $fullname = $user->title . ' ' . $user->fullname;
                                        $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                                        $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 1);
                                    }
                                }
                            }
                        }
                    } else {
                        $user = $this->get_user_details($user_id);
                        if ($user) {
                            $user_id = $user->userid;
                            $fullname = $user->title . ' ' . $user->fullname;
                            $token =  $this->get_master_data_token($fullname, $action, $project, $due_date, $notifications, $responsible_id);
                            $response = $this->get_template($token, $user_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, 1);
                        }
                    }
                }
            }
        }
        return $response;
    }

    function get_token($recipient_id, $title, $action, $responsible_id, $stage, $project_name, $due_date)
    {
        $user = $this->get_user_details($recipient_id);
        $recipient_name  = '';
        if ($user) {
            $recipient_name =  $user['ttitle'] . ". " . $user['fullname'];
        }

        $responsible_id = $responsible_id;
        $responsible_name = $responsible_email = $responsible_contact = '';
        if ($responsible_id != '') {
            $responsible = $this->get_user_details($responsible_id);
            $responsible_name =  $responsible['ttitle'] . ". " . $responsible['fullname'];
            $responsible_email = $responsible['email'];
            $responsible_contact = $responsible['phone'];
        }

        $token = array(
            'FIRST_NAME' => $recipient_name,
            'TITLE' => $title,
            "ACTION" => $action,
            "STAGE" => $stage,
            "RESPONSIBLE_NAME" => $responsible_name,
            'PHONE' => $responsible_contact,
            'EMAIL' => $responsible_email,
            'PROJECT_NAME' => $project_name,
            'DEADLINE' => $due_date,
        );

        $pattern = '[%s]';
        $varMap = [];
        foreach ($token as $key => $val) {
            $varMap[sprintf($pattern, $key)] = $val;
        }

        return  $varMap;
    }

    function get_template($token, $recipient_id, $notification_type_id, $notification_group_id, $notification_id, $page_url, $user_type)
    {
        $result = false;
        $query_email_templates =  $this->db->prepare("SELECT n.id,  t.title, t.content FROM tbl_notification_types n INNER JOIN tbl_notification_templates t ON t.notification_type_id = n.id WHERE t.notification_group_id=:notification_group_id AND notification_type_id=:notification_type AND n.status=1 LIMIT 1");
        $query_email_templates->execute(array(":notification_group_id" => $notification_group_id, ":notification_type" => $notification_type_id));
        $row_email_templates = $query_email_templates->fetch();
        $count = $query_email_templates->rowCount();
        if ($count > 0) {
            $content = strtr($row_email_templates->content, $token);
            $subject = strtr($row_email_templates->title, $token);
            $main_url = $user_type == 1 ? $this->url . $page_url : $this->contractor_url . $page_url;
            $details_link =  '<a href="' . $main_url . '" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Click Here</a>';
            $body = $this->email_body_template($subject, $content, $details_link);
            if ($recipient_id != '') {
                $user =  $user_type == 1 ?  $this->get_user_details($recipient_id) : $this->get_contractor_details($recipient_id);
                if ($user) {
                    $recipient_name = $user->fullname;
                    $recipient_email = $user->email;
                    $response =  $this->sendMail($subject, $body, $recipient_email, $recipient_name, []);
                    $status = $response ? 1 : 0;
                    $result =  $this->store_notification_status(array(":notification_type_id" => $notification_type_id, ":notification_group_id" => 1, ":notification_id" => $notification_id, ":item_id" => $recipient_id, ':user_id' => $recipient_id, ":title" => $subject, ":content" => $body, ":page_url" => $main_url, ":status" => $status, "created_at" => $this->created_at));
                }
            }
        }

        return $result;
    }

    public function notification_template($mail_details, $content_details)
    {
        $token = [];
        $notification_group_id = $mail_details['notification_group_id'];
        $notification_type_id = $mail_details['notification_type_id'];
        $notification_id = $mail_details['notification_id'];
        $recipient_id = $mail_details['recipient_id'];
        $item_id = $mail_details['item_id'];
        $page_url = $mail_details['page_url'];

        $user = $this->get_user_details($recipient_id);
        $recipient_name = $recipient = '';
        if ($user) {
            $recipient_name =  $user['ttitle'] . ". " . $user['fullname'];
            $recipient = $user['email'];
        }

        $responsible_id = $content_details['responsible_id'];
        $responsible_name = $responsible_email = $responsible_contact = '';
        if ($notification_type_id == 4 && $responsible_id != '') {
            $responsible = $this->get_user_details($responsible_id);
            $responsible_name =  $responsible['ttitle'] . ". " . $responsible['fullname'];
            $responsible_email = $responsible['email'];
            $responsible_contact = $responsible['phone'];
        }

        $token = array(
            'FIRST_NAME' => $recipient_name,
            'TITLE' => $content_details['title'],
            "ACTION" => $content_details['action'],
            "STAGE" => $content_details['stage'],
            "RESPONSIBLE_NAME" => $responsible_name,
            'PHONE' => $responsible_contact,
            'EMAIL' => $responsible_email,
            'PROJECT_NAME' => $content_details['project_name'],
            'DEADLINE' => $content_details['due_date'],
        );

        $query_email_templates =  $this->db->prepare("SELECT n.id,  t.title, t.content FROM tbl_notification_types n INNER JOIN tbl_notification_templates t ON t.notification_type_id = n.id WHERE t.notification_group_id=:notification_group_id AND notification_type_id=:notification_type AND n.status=1 LIMIT 1");
        $query_email_templates->execute(array(":notification_group_id" => $notification_group_id, ":notification_type" => $notification_type_id));
        $row_email_templates = $query_email_templates->fetch();
        $count = $query_email_templates->rowCount();

        if ($count > 0) {
            $pattern = '[%s]';
            foreach ($token as $key => $val) {
                $varMap[sprintf($pattern, $key)] = $val;
            }

            $content = strtr($row_email_templates->content, $varMap);
            $subject = strtr($row_email_templates->title, $varMap);
        }

        $main_url = $this->url . $page_url;
        $details_link =  '<a href="' . $main_url . '" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Click Here</a>';
        $body = $this->email_body_template($subject, $content, $details_link);
        return $this->sendMail($subject, $body, $recipient, $recipient_name, $recipient_id, [], $notification_type_id, $notification_group_id, $notification_id, $item_id);
    }

    public function email_body_template($title, $content, $link)
    {
        $query_email_templates =  $this->db->prepare("SELECT * FROM `tbl_email_templates` WHERE id=6");
        $query_email_templates->execute();
        $row_email_templates = $query_email_templates->fetch();
        $count = $query_email_templates->rowCount();
        if ($count > 0) {
            $token = array('TITLE' => $title, 'MESSAGE' => $content, 'LINK' => $link,);
            $pattern = '[%s]';
            foreach ($token as $key => $val) {
                $varMap[sprintf($pattern, $key)] = $val;
            }
            $template = strtr($row_email_templates->content, $varMap);
            return $template;
        } else {
            return false;
        }
    }

    public function sendMail($subject, $body, $recipient, $recipient_name, $attachments)
    {
        $results = false;
        $recipient = 'biwottech@gmail.com';
        try {
            $mail = new PHPMailer;
            // $mail->SMTPDebug = 2;
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = $this->smtp_auth;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = $this->smtp_secure;
            $mail->Port       = $this->port;
            $mail->setFrom($this->username, $this->org);
            $mail->addAddress($recipient, $recipient_name);

            if (count($attachments) > 0) {
                for ($i = 0; $i < count($attachments); $i++) {
                    $mail->addStringAttachment($attachments[$i], $attachments[$i]);
                }
            }

            $mail->isHTML(True);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $results = $mail->send();
        } catch (Exception $e) {
            $results = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }


        return $results;
    }

    public function store_notification_status($data)
    {
        $sql = $this->db->prepare("INSERT INTO `tbl_notification_status` (notification_type_id,notification_group_id,notification_id,item_id,user_id,title,page_url,content,status,created_at) VALUES(:notification_type_id,:notification_group_id,:notification_id,:item_id,:user_id,:title,:content,:page_url,:status,:created_at)");
        $results = $sql->execute($data);
        return $results;
    }
}
