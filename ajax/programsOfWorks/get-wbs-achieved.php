<?php
include '../controller.php';
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

    function get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date)
    {
        global $db;
        $stmt = $db->prepare('SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND created_at >=:start_date  AND created_at <=:end_date');
        $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date));
        $result = $stmt->fetch();
        $target = !is_null($result['achieved'])  ? $result['achieved'] : 0;
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

    function filter_head($contractor_start, $contractor_end, $start_date, $end_date)
    {
        $response = false;
        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            $response = true;
        }
        return $response;
    }

    function filter_body($start_date, $end_date, $target, $site_id, $task_id, $subtask_id)
    {
        $work_program = check_program_of_works($site_id, $task_id, $subtask_id);
        $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
        $table = '';
        if ($work_program) {
            $table .=
                '<td style="width:15%">' . $target . '</td>
                    <td style="width:15%">' . $achieved . '</td>';
        } else {
            $table .=
                '<td style="width:15%">n/a</td>
                <td style="width:15%">n/a</td>';
        }
        return $table;
    }

    function get_table_body($site_id, $output_id, $task_id, $duration_details)
    {
        global $db;
        $body = '';
        $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid  ORDER BY parenttask");
        $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $task_id));
        $totalRows_rsTasks = $query_rsTasks->rowCount();
        if ($totalRows_rsTasks > 0) {
            $tcounter = 0;
            while ($row_rsTasks = $query_rsTasks->fetch()) {
                $tcounter++;
                $task_name = $row_rsTasks['task'];
                $subtask_id = $row_rsTasks['tkid'];
                $unit =  $row_rsTasks['unit_of_measure'];
                $unit_of_measure = get_unit_of_measure($unit);
                $work_program = check_program_of_works($site_id, $task_id, $subtask_id);
                $breakdown = check_target_breakdown($site_id, $task_id, $subtask_id);
                $body .=
                    "<tr>
                <td style='width:5%'>$tcounter</td>
                <td style='width:40%'>$task_name</td>
                <td style='width:40%'>$unit_of_measure</td>";
                $duration = count($duration_details);
                for ($i = 0; $i < $duration; $i++) {
                    $start_date = $duration_details[$i][0];
                    $end_date = $duration_details[$i][1];
                    $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date);
                    $body .= filter_body($start_date, $end_date, $target, $site_id, $task_id, $subtask_id, 1);
                }
                $body .= '</tr>';
            }
        }
        return $body;
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
            if ($month == 7) {
                $header = "Q1";
            } else if ($month == 9) {
                $header = "Q2";
            } else if ($month == 1) {
                $header = "Q3";
            } else if ($month == 4) {
                $header = "Q4";
            }
        } else if ($frequency == 5) {
            $month = date('m', strtotime($start_date));
            if ($month == 7) {
                $header = "Semi 1";
            } else if ($month == 1) {
                $header = "Semi 2";
            }
        }
        return $header;
    }

    function get_header($annual_dates, $duration_details, $frequency, $thead)
    {
        $table_head = $tyears  = $tr_span = '';
        if ($frequency != 6) {
            for ($i = 0; $i < count($annual_dates); $i++) {
                $spans = 0;
                $annual_start_date = $annual_dates[$i][0];
                $annual_end_date = $annual_dates[$i][1];
                for ($t = 0; $t < count($duration_details); $t++) {
                    $start_date = $duration_details[$t][0];
                    $end_date = $duration_details[$t][1];
                    if (
                        ($annual_start_date >= $start_date && $annual_start_date <= $end_date) ||
                        ($annual_end_date >= $start_date && $annual_end_date <= $end_date) ||
                        ($annual_start_date <= $start_date && $annual_end_date >= $start_date && $annual_end_date >= $end_date)
                    ) {
                        $header = get_frequency_header($frequency, $start_date);
                        $table_head .= "<th colspan='2'>$header</th>";
                        $tr_span .= '<td>Target</td> <td>Acheived</td>';
                        $spans++;
                    }
                }

                if ($spans != 0) {
                    $formated_head_start = date('Y', strtotime($annual_start_date));
                    $formated_head_end = date('Y', strtotime($annual_end_date));
                    $span = $spans * 2;
                    $tyears .= '<th colspan=' . $span . '>' . $formated_head_start . ' / ' . $formated_head_end . '</th>';
                }
            }

            $head = '
            <tr>
                <th rowspan="3">#</th>
                <th rowspan="3">Subtask</th>
                <th rowspan="3">Unit of Measure</th>
                ' . $tyears . '
            </tr>
            <tr>
            ' . $table_head . '
            </tr>
            <tr>' . $tr_span . '</tr>';
        } else {
            $table_head .= $thead;
            $head = '
            <tr>
                <th>#</th>
                <th>Subtask</th>
                <th>Unit of Measure</th>
                ' . $thead . '
            </tr>';
        }
        return $head;
    }

    function get_structure($site_id, $output_id, $task_id, $frequency, $duration, $start_year, $project_start_date, $project_end_date, $contractor_start, $contractor_end)
    {
        $annual_dates =  [];
        $thead = '';
        for ($i = 0; $i < $duration; $i++) {
            $end_year = $start_year + 1;
            $start_date = $start_year .  '-07-01';
            $end_date = $end_year  . '-06-30';
            $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date);
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
                        $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date);
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
                $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date);
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
            $response = filter_head($contractor_start, $contractor_end, $startFinancial, $date);
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
                if ($project_start_date < $end_date && $project_end_date >= $start_date) {
                    $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date);
                    if ($response) {
                        $week_dates[] = [$start_date, $end_date];
                    }
                }
            }
        }

        $day_dates = [];
        $day_start_date = $project_start_date;
        $day_end_date = $project_end_date;
        if ($contractor_start != '' && $contractor_end != '') {
            $day_start_date = $contractor_start;
            $day_end_date = $contractor_end;
        }

        while ($day_start_date <= $day_end_date) {
            $date = date('Y-m-d', strtotime($day_start_date));
            $response = filter_head($contractor_start, $contractor_end, $day_start_date, $day_start_date);
            if ($response) {
                $day_dates[] = [$day_start_date, $day_start_date];
            }
            $day_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }

        $table_head = $table_body = '';
        if ($frequency == 1) {
            $table_head = get_header($annual_dates, $day_dates, $frequency, $thead);
            $table_body = get_table_body($site_id, $output_id, $task_id, $day_dates);
        } else if ($frequency == 2) {
            $table_head = get_header($annual_dates, $week_dates, $frequency, $thead);
            $table_body = get_table_body($site_id, $output_id, $task_id, $week_dates);
        } else if ($frequency == 3) {
            $table_head = get_header($annual_dates, $month_dates, $frequency, $thead);
            $table_body = get_table_body($site_id, $output_id, $task_id, $month_dates);
        } else if ($frequency == 4) {
            $table_head = get_header($annual_dates, $quarter_dates, $frequency, $thead);
            $table_body = get_table_body($site_id, $output_id, $task_id, $quarter_dates);
        } else if ($frequency == 5) {
            $table_head = get_header($annual_dates, $semi_annual_dates, $frequency, $thead);
            $table_body = get_table_body($site_id, $output_id, $task_id, $semi_annual_dates);
        } else if ($frequency == 6) {
            $table_head = get_header($annual_dates, $annual_dates, $frequency, $thead);
            $table_body = get_table_body($site_id, $output_id, $task_id, $annual_dates);
        }

        return
            '<div class="table-responsive">
                <table class="table table-bordered js-basic-example dataTable" id="direct_table">
                    <thead>
                    ' . $table_head . '
                    </thead>
                    <tbody>
                    ' . $table_body . '
                    </tbody>
                </table>
            </div>';
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
        $output_id = $_GET['output_id'];
        $projid = $_GET['projid'];
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE projid = :projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();
        if ($totalRows_rsProjects > 0) {
            $frequency = $row_rsProjects['activity_monitoring_frequency'];
            $implementation_type = $row_rsProjects['projcategory'];
            $project_start_date = $row_rsProjects['projstartdate'];
            $project_end_date = $row_rsProjects['projenddate'];
            $contractor_start = $project_start_date;
            $contractor_end = $project_end_date;
            $contractor_details = get_contract_dates($projid);
            if ($implementation_type == 2) {
                $contractor_details =  get_contract_dates($projid);
                if ($contractor_details) {
                    $contractor_start = $contractor_details['contractor_start'];
                    $contractor_end = $contractor_details['contractor_end'];

                    $project_start_date = $contractor_details['contractor_start'];
                    $project_end_date = $contractor_details['contractor_end'];
                }
            }
        }


        $query_rsWorkBreakdown = $db->prepare("SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM tbl_program_of_works WHERE projid=:projid");
        $query_rsWorkBreakdown->execute(array(':projid' => $projid));
        $row_rsWorkBreakdown = $query_rsWorkBreakdown->fetch();
        if (!is_null($row_rsWorkBreakdown['start_date']) && !is_null($row_rsWorkBreakdown['end_date'])) {
            $project_start_date = $row_rsWorkBreakdown['start_date'];
            $project_end_date = $row_rsWorkBreakdown['end_date'];

            $contractor_start = $row_rsWorkBreakdown['start_date'];
            $contractor_end = $row_rsWorkBreakdown['end_date'];
        }

        $duration_details = get_duration($project_start_date, $project_end_date);
        $duration = $duration_details['duration'];
        $start_year = $duration_details['start_year'];
        $table =  get_structure($site_id, $output_id, $task_id, $frequency, $duration, $start_year, $project_start_date, $project_end_date, $contractor_start, $contractor_end);
        echo json_encode(array("success" => true, 'table' => $table));
    }
} catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
}
