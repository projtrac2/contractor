<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


function change_substage($projid)
{
    global $db;
    $query_rsActivity_Monitoring = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid AND complete=0");
    $query_rsActivity_Monitoring->execute(array(":projid" => $projid));
    $totalRows_rsActivity_Monitoring = $query_rsActivity_Monitoring->rowCount();
    $activity_monitoring = $totalRows_rsActivity_Monitoring > 0 ? false : true;

    $query_Output_Monitoring = $db->prepare("SELECT * FROM tbl_project_details WHERE projid = :projid AND complete =0");
    $query_Output_Monitoring->execute(array(":projid" => $projid));
    $total_Output_Monitoring = $query_Output_Monitoring->rowCount();
    $output_monitoring = $total_Output_Monitoring > 0 ? false : true;

    $query_rsIssues = $db->prepare("SELECT * FROM tbl_projissues WHERE projid = :projid AND status <> 7");
    $query_rsIssues->execute(array(":projid" => $projid));
    $total_rsIssues = $query_rsIssues->rowCount();
    $issues = $total_rsIssues > 0 ? false : true;

    return $activity_monitoring && $output_monitoring && $issues ? true : false;
}

function get_sub_achieved($site_id, $subtask_id)
{
    global $db;
    $sql = $db->prepare('SELECT * FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND subtask_id=:subtask_id');
    $sql->execute(array(':site_id' => $site_id, ":subtask_id" => $subtask_id));
    $row = $sql->fetch();
    return $row;
}

function get_subtask_achieved($site_id, $subtask_id, $breakdown_end_date)
{
    global $db;
    $sql = $db->prepare('SELECT * FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND subtask_id=:subtask_id AND created_at > :end_date ORDER BY id ASC LIMIT 1');
    $sql->execute(array(':site_id' => $site_id, ":subtask_id" => $subtask_id, ":end_date" => $breakdown_end_date));
    $row = $sql->fetch();
    $achieved = 0;
    if ($row) {
        $achieved_end_date = $row["created_at"];
        $stmt = $db->prepare('SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND subtask_id=:subtask_id AND created_at <=:end_date');
        $stmt->execute(array(':site_id' => $site_id, ":subtask_id" => $subtask_id, ":end_date" => $achieved_end_date));
        $result = $stmt->fetch();
        $achieved = !is_null($result['achieved'])  ? $result['achieved'] : 0;
    }
    return $achieved;
}

function get_cummulative_target($site_id, $subtask_id, $target_end_date)
{
    global $db;
    $sql_target = $db->prepare('SELECT SUM(target) as target FROM tbl_project_target_breakdown WHERE site_id=:site_id AND subtask_id=:subtask_id AND end_date <=:end_date');
    $sql_target->execute(array(':site_id' => $site_id, ":subtask_id" => $subtask_id, ":end_date" => $target_end_date));
    $rows_target_result = $sql_target->fetch();
    return !is_null($rows_target_result['target']) ? $rows_target_result['target'] : 0;
}

$query_rsMyP = $db->prepare("SELECT * FROM tbl_projects WHERE projstage = 9  AND projstatus <> 5 AND projid=49");
$query_rsMyP->execute();
$count_rsMyP = $query_rsMyP->rowCount();

if ($count_rsMyP > 0) {
    while ($row_rsMyP = $query_rsMyP->fetch()) {
        $projid = $row_rsMyP['projid'];
        $status_id = $row_rsMyP['projstatus'];
        $stage_id = $row_rsMyP['projstage'];
        $substage_id = $row_rsMyP['proj_substage'];
        $implimentation_type =  $row_rsMyP['projcategory'];
        $updated_at =  $row_rsMyP['date_updated'];
        $start_date =  date('Y-m-d', strtotime($row_rsMyP['projstartdate']));
        $end_date =  date('Y-m-d', strtotime($row_rsMyP['projenddate']));

        if ($implimentation_type == 2) {
            $query_rsContractDates =  $db->prepare("SELECT startdate, enddate, tenderamount FROM tbl_tenderdetails WHERE projid = :projid");
            $query_rsContractDates->execute(array(":projid" => $projid));
            $row_rsContractDates = $query_rsContractDates->fetch();
            $totalRows_rsContractDates = $query_rsContractDates->rowCount();
            if ($totalRows_rsContractDates > 0) {
                $start_date = date('Y-m-d', strtotime($row_rsContractDates["startdate"]));
                $end_date = date('Y-m-d', strtotime($row_rsContractDates["enddate"]));
            }
        }

        $complete = change_substage($projid);
        if ($complete) {
            $status_id = 5;
            $substage_id = $implimentation_type == 2 ? 1 : 6;
        } else if ($today > $end_date) {
            $status_id = 5;
            $subtask_status_id = 11;
            $sql = $db->prepare("UPDATE tbl_program_of_works SET status=:status WHERE  projid=:projid AND complete=0");
            $sql->execute(array(":status" => $subtask_status_id, ":projid" => $projid));
        } else {
            $status = [];
            $query_rsWorkBreakdown = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid");
            $query_rsWorkBreakdown->execute(array(':projid' => $projid));
            $rows_rsWorkBreakdown = $query_rsWorkBreakdown->rowCount();
            if ($rows_rsWorkBreakdown > 0) {
                while ($row_rsWorkBreakdown = $query_rsWorkBreakdown->fetch()) {
                    $id = $row_rsWorkBreakdown['id'];
                    $site_id = $row_rsWorkBreakdown['site_id'];
                    $subtask_id = $row_rsWorkBreakdown['subtask_id'];
                    $subtask_status_id = $row_rsWorkBreakdown['status'];
                    $complete = $row_rsWorkBreakdown['complete'];
                    $subtask_end_date = $row_rsWorkBreakdown['end_date'];
                    $subtask_start_date = $row_rsWorkBreakdown['start_date'];
                    $subtask_achieved = get_sub_achieved($site_id, $subtask_id);

                    if (!$complete) {
                        if ($today < $subtask_start_date) {
                            $subtask_status_id = $subtask_achieved ? 4 : 3;
                        } else if ($today > $subtask_start_date && $today < $subtask_end_date) {
                            $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE projid=:projid AND start_date <= :today AND end_date >= :today');
                            $stmt->execute(array(':projid' => $projid, ":today" => $today));
                            $stmt_result = $stmt->rowCount();
                            $result = $stmt->fetch();
                            if ($stmt_result > 0) {
                                $breakdown_start_date = $result['start_date'];
                                $breakdown_end_date = $result['end_date'];
                                $target_end_date = date('Y-m-d', strtotime($breakdown_start_date . ' - 1 days'));
                                $achieved = get_subtask_achieved($site_id, $subtask_id, $target_end_date);
                                $target = get_cummulative_target($site_id, $subtask_id, $target_end_date);
                                $subtask_status_id = ($achieved >= $target) ? 4 : 11;
                            } else {
                                $subtask_status_id = 4;
                            }
                        } else if ($today > $subtask_end_date) {
                            $achieved = get_subtask_achieved($site_id, $subtask_id, $subtask_end_date);
                            $stmt = $db->prepare('SELECT SUM(target) as target FROM tbl_project_target_breakdown WHERE site_id=:site_id AND subtask_id=:subtask_id AND end_date <= :end_date ORDER BY id ASC LIMIT 1');
                            $stmt->execute(array(':site_id' => $site_id, ":subtask_id" => $subtask_id, ":end_date" => $subtask_end_date));
                            $stmt_result = $stmt->rowCount();
                            $result = $stmt->fetch();
                            $target = !is_null($result['target']) ? $result['target'] : 0;
                            $subtask_status_id == ($achieved >= $target) ? 4 : 11;
                        }
                    }

                    $status[] = $subtask_status_id;
                    $sql = $db->prepare("UPDATE tbl_program_of_works SET status=:status WHERE  id=:id");
                    $sql->execute(array(":status" => $subtask_status_id, ":id" => $id));
                }



                if (!empty($status)) {
                    if (in_array(11, $status)) {
                        $status_id = 11;
                    } else if (in_array(4, $status)) {
                        $status_id = 4;
                    } else if (in_array(3, $status)) {
                        $status_id = 3;
                    } else if (in_array(5, $status)) {
                        $status_id = 5;
                    }
                }
            }
        }

        $sql = $db->prepare("UPDATE tbl_projects SET projstatus=:projstatus, proj_substage=:substage_id WHERE  projid=:projid");
        $result  = $sql->execute(array(":projstatus" => $status_id, ":substage_id" => $substage_id, ":projid" => $projid));
    }
}
