<?php
include '../controller.php';
try {
    function index($prjDuration, $start_year)
    {
        $f_start = '07-01';
        $f_end = '06-30';
        $st = $start_year;
        $startYears = [];
        for ($i = 0; $i < $prjDuration; $i++) {
            $m_start = $start_year . '-' . $f_start;
            $f_year_end = $start_year + 1 . '-' . $f_end;
            $startYears[] =  [$m_start, $f_year_end];
            $start_year++;
        }

        $annually = [];
        for ($i = 0; $i < count($startYears); $i++) {
            $startFinancial = $startYears[$i][0];
            $endFinancial = $startYears[$i][1];
            $startFinancialMidPoint = strtotime('+6 months -1 day', strtotime($startFinancial));
            $date = date('Y-m-d', $startFinancialMidPoint);
            $endFinancialMidPoint = strtotime('-6 months +2 day', strtotime($endFinancial));
            $datetwo = date('Y-m-d', $endFinancialMidPoint);
            $annually[] = [[$startFinancial, $date], [$datetwo, $endFinancial]];
        }

        $quarterly = [];
        for ($i = 0; $i < count($annually); $i++) {
            for ($t = 0; $t < count($annually[$i]); $t++) {
                $startFinancial = $annually[$i][$t][0];
                $endFinancial = $annually[$i][$t][1];
                $startFinancialMidPoint = strtotime('+3 months -1 day', strtotime($startFinancial));
                $date = date('Y-m-d', $startFinancialMidPoint);
                $endFinancialMidPoint = strtotime('-3 months ', strtotime($endFinancial));
                $datetwo = date('Y-m-d', $endFinancialMidPoint);
                $quarterly[] = [[$startFinancial, $date], [$datetwo, $endFinancial]];
            }
        }

        $monthly = [];
        for ($i = 0; $i < count($quarterly); $i++) {
            for ($t = 0; $t < count($quarterly[$i]); $t++) {
                $startFinancial = $quarterly[$i][$t][0];
                $endFinancial = $quarterly[$i][1][1];
                while ($startFinancial <= $endFinancial) {
                    $startFinancialMidPoint = strtotime('+1 month -1 day', strtotime($startFinancial));
                    $date = date('Y-m-d', $startFinancialMidPoint);
                    $monthly[] = [$startFinancial, $date];
                    $startFinancial = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                }
                break;
            }
        }
        return array("startYears" => $startYears, "annually" => $annually, "quarterly" => $quarterly, "monthly" => $monthly);
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

    function get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency)
    {
        global $db;
        $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND start_date=:start_date  AND end_date=:end_date AND frequency=:frequency');
        $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date, ":frequency" => $frequency));
        $stmt_result = $stmt->rowCount();
        $result = $stmt->fetch();
        $target = $stmt_result > 0 ? $result['target'] : '';
        return $target;
    }

    function get_table_body($task_start_date, $task_end_date, $target, $start_date, $end_date, $counter)
    {
        $tr = '';
        if (
            ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
            ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
            ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
        ) {
            $formated_date_start = date('d M Y', strtotime($start_date));
            $formated_date_end = date('d M Y', strtotime($end_date));
            $tr .=
                '<tr>
                        <td>' . $counter . '</td>
                        <td>' . $formated_date_start . ' - ' . $formated_date_end . '</td>
                        <td>
                            <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                            <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                            <input type="number" value="' . $target . '" class="form-control target_breakdown" placeholder="Enter Target" name="target[]" min="0" step="0.01"/>
                        </td>
                    </tr>';
            $counter++;
        }
        return array('table_body' => $tr, "counter" => $counter);
    }

    function get_annual_table($task_start_date, $task_end_date, $startYears, $subtask_id, $site_id, $task_id, $frequency)
    {
        $table_body = '';
        $counter = 1;
        for ($i = 0; $i < count($startYears); $i++) {
            $start_date = $startYears[$i][0];
            $end_date = $startYears[$i][1];
            $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
            $table_details = get_table_body($task_start_date, $task_end_date, $target, $start_date, $end_date, $counter);
            $counter = $table_details['counter'];
            $table_body .= $table_details['table_body'];
        }
        return $table_body;
    }

    function get_semiannual_table($task_start_date, $task_end_date, $annually, $subtask_id, $site_id, $task_id, $frequency)
    {
        $counter = 1;
        $table_body = '';
        for ($i = 0; $i < count($annually); $i++) {
            for ($t = 0; $t < count($annually[$i]); $t++) {
                $start_date = $annually[$i][$t][0];
                $end_date = $annually[$i][$t][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $table_details = get_table_body($task_start_date, $task_end_date, $target, $start_date, $end_date, $counter);
                $counter = $table_details['counter'];
                $table_body .= $table_details['table_body'];
            }
        }
        return $table_body;
    }

    function get_quarterly_table($task_start_date, $task_end_date, $quarterly, $subtask_id, $site_id, $task_id, $frequency)
    {
        $table_body = '';
        $counter = 1;
        for ($i = 0; $i < count($quarterly); $i++) {
            for ($t = 0; $t < count($quarterly[$i]); $t++) {
                $start_date = $quarterly[$i][$t][0];
                $end_date = $quarterly[$i][$t][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $table_details = get_table_body($task_start_date, $task_end_date, $target, $start_date, $end_date, $counter);
                $counter = $table_details['counter'];
                $table_body .= $table_details['table_body'];
            }
        }
        return $table_body;
    }

    function get_monthly_table($task_start_date, $task_end_date, $monthly, $subtask_id, $site_id, $task_id, $frequency)
    {
        $table_body = '';
        $counter = 1;
        for ($i = 0; $i < count($monthly); $i++) {
            $start_date = $monthly[$i][0];
            $end_date = $monthly[$i][1];
            $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
            $table_details = get_table_body($task_start_date, $task_end_date, $target, $start_date, $end_date, $counter);
            $counter = $table_details['counter'];
            $table_body .= $table_details['table_body'];
        }
        return $table_body;
    }

    function get_weekly_table($project_start_date, $project_end_date, $task_start_date, $task_end_date, $site_id, $task_id, $subtask_id, $frequency)
    {
        $table_body = '';
        $start_year = date('Y', strtotime($project_start_date));
        $end_year = date('Y', strtotime($project_end_date));
        $end_month = date('M', strtotime($project_end_date));
        $end_year = ($end_month >= 7 && $end_month <= 12) ? $end_year + 1 : $end_year;
        $duration = ($end_year - $start_year)  + 1;
        $start_year;
        $counter = 1;
        for ($j = 0; $j < $duration; $j++) {
            for ($i = 1; $i < 53; $i++) {
                $week_array = getStartAndEndDate($i, $start_year);
                $start_date =  date('Y-m-d', strtotime($week_array['week_start']));
                $end_date =  date('Y-m-d', strtotime($week_array['week_end']));
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $table_details = get_table_body($task_start_date, $task_end_date, $target, $start_date, $end_date, $counter);
                $counter = $table_details['counter'];
                $table_body .= $table_details['table_body'];
            }
            $start_year++;
        }

        return $table_body;
    }

    function get_daily_table($task_start_date, $task_end_date, $subtask_id, $site_id, $task_id, $frequency)
    {
        $hash = 1;
        $tr = '';
        $daily_task_start_date = $task_start_date;
        while ($daily_task_start_date <= $task_end_date) {
            $date = date('Y-m-d', strtotime($daily_task_start_date));
            $date_show = date('d M Y', strtotime($daily_task_start_date));
            $daily_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
            $target = get_target($site_id, $task_id, $subtask_id, $daily_task_start_date, $daily_task_start_date, $frequency);
            $tr .= '<tr>
                    <td>' . $hash . '</td>
                    <td>' . $date_show . '</td>
                    <td>
                        <input type="number" placeholder="Enter Target" value="' . $target . '" class="form-control yearly-target" name="yearly-target[]"  min="0" step="0.01"/>
                        <input type="hidden" value="' . $date . '" class="year" name="year[]" />
                    </td>
                </tr>';
            $hash++;
        }
        return $tr;
    }

    function get_duration($project_start_date, $project_end_date)
    {
        $date1 = new DateTime($project_start_date);
        $date2 = new DateTime($project_end_date);
        $interval = $date1->diff($date2);
        return $interval->y;
    }

    if (isset($_GET['get_wbs'])) {
        $site_id = $_GET['site_id'];
        $projid = $_GET['projid'];
        $task_id = $_GET['task_id'];
        $subtask_id = $_GET['subtask_id'];
        $output_id = $_GET['output_id'];
        $structure = $task_start_date = $task_end_date = $duration = $title = '';
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();
        if ($totalRows_rsProjects > 0) {
            $min_date = $row_rsProjects['projstartdate'];
            $max_date = $row_rsProjects['projenddate'];
            $frequency =  $row_rsProjects['activity_monitoring_frequency'];

            $duration_years = get_duration($min_date, $max_date);
            $proj_start_year = date('Y', strtotime($min_date));
            $duration_years =  ($duration_years == 0) ? 1 : $duration_years;

            $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
            $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
            $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
            $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
            if ($totalRows_rsTask_Start_Dates > 0) {
                $task_start_date = $row_rsTask_Start_Dates['start_date'];
                $task_end_date = $row_rsTask_Start_Dates['end_date'];
                $duration = $row_rsTask_Start_Dates['duration'];
                $details = index($duration_years, $proj_start_year);
                $startYears = $details['startYears'];
                $annually = $details['annually'];
                $quarterly = $details['quarterly'];
                $monthly = $details['monthly'];
                if ($frequency == 6) { // yearly
                    $structure = get_annual_table($task_start_date, $task_end_date, $startYears, $subtask_id, $site_id, $task_id, $frequency);
                    $title = 'Year';
                } else if ($frequency == 5) { // semi annual
                    $structure = get_semiannual_table($task_start_date, $task_end_date, $annually, $subtask_id, $site_id, $task_id, $frequency);
                    $title = 'Semi Annual';
                } else if ($frequency == 4) { // quarterly
                    $structure = get_quarterly_table($task_start_date, $task_end_date, $quarterly, $subtask_id, $site_id, $task_id, $frequency);
                    $title = 'Quarter';
                } else if ($frequency == 3) { // monthly
                    $structure = get_monthly_table($task_start_date, $task_end_date, $monthly, $subtask_id, $site_id, $task_id, $frequency);
                    $title = 'Month';
                } else if ($frequency == 2) { // weekly
                    $structure =  get_weekly_table($min_date, $max_date, $task_start_date, $task_end_date, $site_id, $task_id, $subtask_id, $frequency);
                    $title = 'Week';
                } else if ($frequency == 1) { // daily
                    $structure = get_daily_table($task_start_date, $task_end_date, $subtask_id, $site_id, $task_id, $frequency);
                    $title = 'Day';
                }
            }
        }

        $query_rsTask = $db->prepare("SELECT t.task, c.units_no FROM tbl_task t INNER JOIN tbl_project_direct_cost_plan c ON t.tkid=c.subtask_id WHERE t.msid=:task_id AND c.site_id=:site_id AND t.tkid=:subtask_id ");
        $query_rsTask->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
        $row_rsTask = $query_rsTask->fetch();
        $table = '
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>' . $title . '</th>
                    <th>Target</th>
                </tr>
            </thead>
            <tbody>
            ' . $structure . '
            </tbody>
        </table>';
        echo json_encode(array("success" => true, "structure" => $table, 'task' => $row_rsTask, "start_date" => $task_start_date, "end_date" => $task_end_date, "duration" => $duration));
    }

    if (isset($_POST['store_target'])) {
        $projid = $_POST['projid'];
        $output_id = $_POST['output_id'];
        $site_id = $_POST['site_id'];
        $task_id = $_POST['task_id'];
        $subtask_id = $_POST['subtask_id'];

        //   target
        $frequency = $_POST['frequency'];
        $targets = $_POST['target'];
        $start_dates = $_POST['start_date'];
        $end_dates = $_POST['end_date'];
        $counter = count($targets);
        $date = date('Y-m-d');

        for ($i = 0; $i < $counter; $i++) {
            $target = $targets[$i];
            $start_date = $start_dates[$i];
            $end_date = $end_dates[$i];

            $stmt = $db->prepare("DELETE FROM `tbl_project_target_breakdown` WHERE projid=:projid AND output_id=:output_id AND site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND start_date=:start_date  AND end_date=:end_date ");
            $results = $stmt->execute(array(':projid' => $projid, ":output_id" => $output_id, ":site_id" => $site_id, ":task_id" => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date));

            $sql = $db->prepare("INSERT INTO tbl_project_target_breakdown (projid, output_id, site_id, task_id, subtask_id,start_date,end_date, frequency, target, created_by, created_at) VALUES (:projid, :output_id, :site_id, :task_id, :subtask_id,:start_date,:end_date, :frequency, :target, :created_by, :created_at)");
            $results = $sql->execute(array(':projid' => $projid, ":output_id" => $output_id, ":site_id" => $site_id, ":task_id" => $task_id, ":subtask_id" => $subtask_id, ':start_date' => $start_date, ':end_date' => $end_date, ":frequency" => $frequency, ":target" => $target, ":created_by" => $user_name, ':created_at' => $date));
        }

        echo json_encode(array('success' => true));
    }
} catch (PDOException $ex) {
    $result = flashMessage("An error occurred: " . $ex->getMessage());
    echo $ex->getMessage();
}
