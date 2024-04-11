<?php
include '../controller.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    function get_unit_of_measure($unit)
    {
        global $db;
        $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
        $query_rsIndUnit->execute(array(":unit_id" => $unit));
        $row_rsIndUnit = $query_rsIndUnit->fetch();
        $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
        return $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
    }

    function check_program_of_works($site_id, $task_id, $subtask_id)
    {
        global $db;
        $query_rsWorkBreakdown = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
        $query_rsWorkBreakdown->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
        $row_rsWorkBreakdown = $query_rsWorkBreakdown->fetch();
        return $row_rsWorkBreakdown;
    }

    function get_target($site_id, $task_id, $subtask_id, $start_date, $end_date)
    {
        global $db;
        $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND start_date=:start_date  AND end_date=:end_date');
        $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date));
        $stmt_result = $stmt->rowCount();
        $result = $stmt->fetch();
        $target = $stmt_result > 0 ? $result['target'] : 0;
        return $target;
    }

    function check_target_breakdown($site_id, $task_id, $subtask_id)
    {
        global $db;
        $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id = :task_id AND subtask_id = :subtask_id ');
        $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id));
        $stmt_result = $stmt->rowCount();
        return $stmt_result > 0 ? true : false;
    }

    function getStartAndEndDate($week, $year)
    {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        return $ret;
    }

    function filter_head($subtask_start_date, $subtask_end_date, $start_date, $end_date)
    {
        $response = false;
        if (
            ($subtask_start_date >= $start_date && $subtask_start_date <= $end_date) ||
            ($subtask_end_date >= $start_date && $subtask_end_date <= $end_date) ||
            ($subtask_start_date <= $start_date && $subtask_end_date >= $start_date && $subtask_end_date >= $end_date)
        ) {
            $response = true;
        }
        return $response;
    }

    function get_frequency_header($frequency, $start_date)
    {
        $header = '';
        if ($frequency == 1) {
            $header = date('d-m-Y', strtotime($start_date));
        } else if ($frequency == 2) {
            $week = date('W', strtotime($start_date));
            $header = "Week " . $week;
        } else if ($frequency == 3) {
            $header = date('F', strtotime($start_date));
        } else if ($frequency == 4) {
            $month = date('m', strtotime($start_date));
            if ($month >= 7 && $month <= 9) {
                $header = "Q1";
            } else if ($month >= 10 && $month <= 12) {
                $header = "Q2";
            } else if ($month >= 1 && $month <= 3) {
                $header = "Q3";
            } else if ($month >= 4 && $month <= 6) {
                $header = "Q4";
            }
        } else if ($frequency == 5) {
            $month = date('m', strtotime($start_date));
            if ($month >= 7 && $month <= 12) {
                $header = "Semi 1";
            } else if ($month >= 1  && $month <= 6) {
                $header = "Semi 2";
            }
        }

        return $header;
    }

    function get_financial_year($start_date)
    {
        $currentYear = date('Y', strtotime($start_date));
        $month = date('m', strtotime($start_date));
        $start_year = ($month >= 7 && $month <= 12) ? $currentYear : $currentYear - 1;
        $end_year = $start_year + 1;
        return $start_year . '/' . $end_year;
    }

    function get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $duration_details, $site_id, $subtask_id, $task_id, $frequency)
    {
        $body = '';
        $today = date('Y-m-d');
        $counter = 0;
        for ($j = 0; $j < count($annual_dates); $j++) {
            $annual_start_date = $annual_dates[$j][0];
            $annual_end_date = $annual_dates[$j][1];
            $table_body = $table_body1 = '';
            $details_count = count($duration_details);
            $span = 0;
            for ($i = 0; $i < $details_count; $i++) {
                $start_date = $duration_details[$i][0];
                $end_date = $duration_details[$i][1];
                if ($today <= $start_date || $today <= $end_date) {
                    $financial_year = get_financial_year($start_date);
                    $formated_date_start = date('d M Y', strtotime($start_date));
                    $formated_date_end = date('d M Y', strtotime($end_date));
                    $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                    $subtask_details = '';
                    if ($details_count == 1) {
                        $header = get_frequency_header($frequency, $subtask_start_date);
                        $subtask_details =  $header . ' (' . date('d M Y', strtotime($subtask_start_date)) . ' - ' . date('d M Y', strtotime($subtask_end_date)) . ')';
                        if ($frequency == 1) {
                            $subtask_details = $header;
                        }
                    } else {
                        if ($start_date <= $subtask_start_date) {
                            $header = get_frequency_header($frequency, $subtask_start_date);
                            $subtask_details = $header . ' (' . date('d M Y', strtotime($subtask_start_date)) . ' - ' . $formated_date_end . ')';
                            if ($frequency == 1) {
                                $subtask_details = $header;
                            }
                        } else if ($end_date >= $subtask_end_date) {
                            $header = get_frequency_header($frequency, $formated_date_start);
                            $subtask_details = $header . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($subtask_end_date)) . ')';
                            if ($frequency == 1) {
                                $subtask_details = $header;
                            }
                        } else {
                            $header = get_frequency_header($frequency, $formated_date_start);
                            $subtask_details = $header . ' (' . $formated_date_start . ' - ' . $formated_date_end . ')';
                            if ($frequency == 1) {
                                $subtask_details = $header;
                            }
                        }
                    }

                    if (
                        ($annual_start_date >= $start_date && $annual_start_date <= $end_date) ||
                        ($annual_end_date >= $start_date && $annual_end_date <= $end_date) ||
                        ($annual_start_date <= $start_date && $annual_end_date >= $start_date && $annual_end_date >= $end_date)
                    ) {
                        $span++;
                        if ($span == 1) {
                            $table_body1 .=
                                '
                            <td>' . $subtask_details . '</td>
                            <td>
                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                            </td> ';
                        } else {
                            $table_body .=
                                '<tr>
                            <td>' . $subtask_details . '</td>
                            <td>
                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                            </td>
                        </tr>';
                        }
                    }
                }
            }

            if ($table_body != '' || $table_body1 != '') {
                $counter++;
                $body .=
                    '<tr>
                        <td rowspan="' . $span . '">' . $counter . '</td>
                        <td rowspan="' . $span . '">' . $financial_year . '</td>
                        ' . $table_body1 . '
                    </tr>' . $table_body;
            }
        }

        return $body;
    }

    function get_structure($site_id, $subtask_id, $task_id, $frequency, $duration, $start_year, $subtask_start_date, $subtask_end_date)
    {
        $annual_dates =  [];
        $thead = '';
        for ($i = 0; $i < $duration; $i++) {
            $end_year = $start_year + 1;
            $start_date = $start_year .  '-07-01';
            $end_date = $end_year  . '-06-30';
            $response = filter_head($subtask_start_date, $subtask_end_date, $start_date, $end_date);
            if ($response) {
                $annual_dates[] =  [$start_date, $end_date];
                $thead .= "<th>$start_year/$end_year</th>";
            }
            $start_year++;
        }

        $semi_annual_dates = [];
        if (!empty($annual_dates)) {
            for ($i = 0; $i < count($annual_dates); $i++) {
                $startFinancial = $annual_dates[$i][0];
                $endFinancial = $annual_dates[$i][1];
                $startFinancialMidPoint = strtotime('+6 months -1 day', strtotime($startFinancial));
                $date = date('Y-m-d', $startFinancialMidPoint);
                $endFinancialMidPoint = strtotime('-6 months +2 day', strtotime($endFinancial));
                $datetwo = date('Y-m-d', $endFinancialMidPoint);

                $semi_annual_array = [[$startFinancial, $date], [$datetwo, $endFinancial]];

                if (!empty($semi_annual_array)) {

                    for ($t = 0; $t < count($semi_annual_array); $t++) {
                        $semi_details = $semi_annual_array[$t];
                        $start_date = $semi_details[0];
                        $end_date = $semi_details[1];
                        $response = filter_head($subtask_start_date, $subtask_end_date, $start_date, $end_date);
                        if ($response) {
                            $semi_annual_dates[] = [$start_date, $end_date];
                        }
                    }
                }
            }
        }

        $quarter_dates = [];
        if (!empty($semi_annual_dates)) {
            $l = $semi_annual_dates[count($semi_annual_dates) - 1][1]; // get end date
            $sInc = $semi_annual_dates[0][0]; // get start date
            while ($sInc <= $l) {
                $start_date = $sInc;
                $startFinancialMidPoint = strtotime('+3 months -1 day', strtotime($sInc));
                $end_date = date('Y-m-d', $startFinancialMidPoint);
                $response = filter_head($subtask_start_date, $subtask_end_date, $start_date, $end_date);
                if ($response) {
                    $quarter_dates[] = [$start_date, $end_date];
                }
                $sInc = date('Y-m-d', strtotime('+3 months', strtotime($sInc)));
            }
        }

        $startFinancial = $quarter_dates[0][0];
        $endFinancial = $quarter_dates[count($quarter_dates) - 1][1];
        $month_dates = [];
        while ($startFinancial <= $endFinancial) {
            $startFinancialMidPoint = strtotime('+1 month -1 day', strtotime($startFinancial));
            $date = date('Y-m-d', $startFinancialMidPoint);
            $response = filter_head($subtask_start_date, $subtask_end_date, $startFinancial, $date);
            if ($response) {
                $month_dates[] = [$startFinancial, $date];
            }
            $startFinancial = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        $week_dates = [];
        for ($j = 0; $j < $duration; $j++) {
            for ($i = 1; $i < 53; $i++) {
                $week_array = getStartAndEndDate($i, $start_year);
                $start_date =  date('Y-m-d', strtotime($week_array['week_start']));
                $end_date =  date('Y-m-d', strtotime($week_array['week_end']));
                $response = filter_head($subtask_start_date, $subtask_end_date, $start_date, $end_date);
                if ($response) {
                    $week_dates[] = [$start_date, $end_date];
                }
            }
        }

        $day_dates = [];
        $day_start_date = $subtask_start_date;
        $day_end_date = $subtask_end_date;
        while ($day_start_date <= $day_end_date) {
            $date = date('Y-m-d', strtotime($day_start_date));
            $day_dates[] = [$day_start_date, $day_start_date];
            $day_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        $table_body = $table_head = '';
        if ($frequency == 1) {
            $table_head = 'Day';
            $table_body = get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $day_dates, $site_id, $subtask_id, $task_id, $frequency);
        } else if ($frequency == 2) {
            $table_head = 'Week';
            $table_body = get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $week_dates, $site_id, $subtask_id, $task_id, $frequency);
        } else if ($frequency == 3) {
            $table_head = 'Month';
            $table_body = get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $month_dates, $site_id, $subtask_id, $task_id, $frequency);
        } else if ($frequency == 4) {
            $table_head = 'Quarter';
            $table_body = get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $quarter_dates, $site_id, $subtask_id, $task_id, $frequency);
        } else if ($frequency == 5) {
            $table_head = 'Semi';
            $table_body = get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $semi_annual_dates, $site_id, $subtask_id, $task_id, $frequency);
        } else if ($frequency == 6) {
            $table_head = 'Yearly';
            $table_body = get_table_body($annual_dates, $subtask_start_date, $subtask_end_date, $annual_dates, $site_id, $subtask_id, $task_id, $frequency);
        }

        return '
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Financial Year</th>
                        <th>' . $table_head . ' Dates</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody>
                ' . $table_body . '
                </tbody>
            </table>';
    }

    function get_task_dates($task_id, $site_id)
    {
        global $db;
        $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
        $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
        $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
        $task_start_date = $task_end_date = '';
        if ($Rows_rsTask_Start_Dates) {
            $task_start_date = $Rows_rsTask_Start_Dates['start_date'];
            $task_end_date =  $Rows_rsTask_Start_Dates['end_date'];
        }
        return array("task_start_date" => $task_start_date, "task_end_date" => $task_end_date);
    }

    function get_contract_dates($projid)
    {
        global $db;
        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();
        $contractor_start = $contractor_end = '';
        if ($totalRows_rsTender > 0) {
            $contractor_start = $row_rsTender['startdate'];
            $contractor_end = $row_rsTender['enddate'];
        }
        return array("contractor_start" => $contractor_start, "contractor_end" => $contractor_end);
    }

    function get_duration($min_date, $max_date)
    {
        $currentYear = date('Y', strtotime($min_date));
        $month = date('m', strtotime($min_date));
        $start_year = ($month >= 7 && $month <= 12) ? $currentYear : $currentYear - 1;
        $currentYear = date('Y', strtotime($max_date));
        $month = date('m', strtotime($max_date));
        $end_year = ($month >= 7 && $month <= 12) ? $currentYear : $currentYear - 1;
        $duration = ($end_year - $start_year) + 1;
        return array("duration" => $duration, "start_year" => $start_year);
    }

    if (isset($_GET['get_wbs'])) {
        $site_id = $_GET['site_id'];
        $task_id = $_GET['task_id'];
        $subtask_id = $_GET['subtask_id'];
        $projid = $_GET['projid'];
        $issue_id = $_GET['issue_id'];
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE projid = :projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();

        $subtask_end_date = $subtask_start_date = $subtask_duration = '';
        if ($totalRows_rsProjects > 0) {
            $frequency = $row_rsProjects['activity_monitoring_frequency'];
            $query_rsSubTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
            $query_rsSubTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
            $row_rsSubTask_Start_Dates = $query_rsSubTask_Start_Dates->fetch();
            $totalRows_rsSubTask_Start_Dates = $query_rsSubTask_Start_Dates->rowCount();
            if ($totalRows_rsSubTask_Start_Dates > 0) {
                $subtask_start_date = $row_rsSubTask_Start_Dates['start_date'];
                $subtask_end_date = $row_rsSubTask_Start_Dates['end_date'];
                $subtask_duration = $row_rsSubTask_Start_Dates['duration'];
                $duration_details = get_duration($subtask_start_date, $subtask_end_date);
                $duration = $duration_details['duration'];
                $start_year = $duration_details['start_year'];
                $table =  get_structure($site_id, $subtask_id, $task_id, $frequency, $duration, $start_year, $subtask_start_date, $subtask_end_date);
            }
        }

        $query_rsTask = $db->prepare("SELECT t.task, c.units_no, m.unit FROM tbl_task t INNER JOIN tbl_project_direct_cost_plan c ON t.tkid=c.subtask_id INNER JOIN tbl_measurement_units m ON m.id=t.unit_of_measure WHERE t.msid=:task_id AND c.site_id=:site_id AND t.tkid=:subtask_id ");
        $query_rsTask->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
        $row_rsTask = $query_rsTask->fetch();

        $stmt = $db->prepare('SELECT SUM(target) as target FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND (start_date >=:start_date  OR end_date <=:end_date)');
        $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $today, ":end_date" => $today));
        $result = $stmt->fetch();
        $target = !is_null($result['target']) > 0 ? $result['target'] : 0;

        $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments i INNER JOIN tbl_task t ON t.tkid=i.sub_task_id WHERE issueid=:issue_id AND site_id=:site_id AND sub_task_id=:subtask_id");
        $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
        $totalRows_Issues = $query_Issues->rowCount();
        $Rows_Issues = $query_Issues->fetch();

        $requested_units = $flag = 0;
        if ($totalRows_Issues > 0) {
            $requested_units = $Rows_Issues['units'];
            $flag = $Rows_Issues['flag'];
        }

        $total_units = $flag == 0 ? $target + $requested_units : $target;
        echo json_encode(array("success" => true, "structure" => $table, 'task' => $row_rsTask, "start_date" => $subtask_start_date, "end_date" => $subtask_end_date, "duration" => $subtask_duration, "targets" => $total_units, "requested_units" => $requested_units));
    }

    if (isset($_POST['store_target'])) {
        $msg = false;
        if (validate_csrf_token($_POST['csrf_token'])) {
            $projid = $_POST['projid'];
            $output_id = $_POST['output_id'];
            $site_id = $_POST['site_id'];
            $task_id = $_POST['task_id'];
            $subtask_id = $_POST['subtask_id'];
            $frequency = $_POST['frequency'];
            $targets = $_POST['target'];
            $start_dates = $_POST['start_date'];
            $end_dates = $_POST['end_date'];
            $issue_id = $_POST['issue_id'];
            $counter = count($targets);
            $date = date('Y-m-d');
            for ($i = 0; $i < $counter; $i++) {
                $target = $targets[$i];
                $start_date = $start_dates[$i];
                $end_date = $end_dates[$i];
                $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id = :task_id AND subtask_id = :subtask_id AND start_date=:start_date AND end_date=:end_date ');
                $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date));
                $stmt_result = $stmt->rowCount();
                $stmts_result = $stmt->fetch();

                if ($stmt_result == 0) {
                    $sql = $db->prepare("INSERT INTO tbl_project_target_breakdown (projid, output_id, site_id, task_id, subtask_id,start_date,end_date, frequency, target, created_by, created_at) VALUES (:projid, :output_id, :site_id, :task_id, :subtask_id,:start_date,:end_date, :frequency, :target, :created_by, :created_at)");
                    $results = $sql->execute(array(':projid' => $projid, ":output_id" => $output_id, ":site_id" => $site_id, ":task_id" => $task_id, ":subtask_id" => $subtask_id, ':start_date' => $start_date, ':end_date' => $end_date, ":frequency" => $frequency, ":target" => $target, ":created_by" => $user_name, ':created_at' => $date));
                } else {
                    $id = $stmts_result['id'];
                    $sql = $db->prepare("UPDATE tbl_project_target_breakdown SET start_date=:start_date,end_date=:end_date, target=:target WHERE id=:id");
                    $results = $sql->execute(array(':start_date' => $start_date, ':end_date' => $end_date, ":target" => $target, ":id" => $id));
                }

                $sql = $db->prepare("UPDATE tbl_project_adjustments SET flag=1 WHERE issueid=:issue_id AND site_id=:site_id AND sub_task_id=:sub_task_id");
                $results = $sql->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":sub_task_id" => $subtask_id));
            }
            $msg = true;
        }
        // logActivity("adjust Target Breakdown", "$msg");
        echo json_encode(array('success' => $msg));
    }

    if (isset($_POST['close_issue'])) {
        $results = false;
        if (validate_csrf_token($_POST['csrf_token'])) {
            $projid = $_POST['projid'];
            $issue_id = $_POST['issue_id'];
            $sql = $db->prepare("UPDATE tbl_projissues SET status=2 WHERE  projid=:projid AND id=:issue_id");
            $result  = $sql->execute(array(":projid" => $projid, ":issue_id" => $issue_id));
            $results =  $mail->send_master_data_email($projid, 6, '');
        }

        logActivity("submit", "$results");
        echo json_encode(array('success' => $result));
    }
} catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
}
