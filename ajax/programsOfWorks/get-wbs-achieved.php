
<?php
include '../controller.php';

function index($prjDuration, $start_year)
{
    $f_start = '07-01';
    $f_end = '06-30';
    $startYears =  [];
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
            $endFinancialMidPoint = strtotime('-3 months +2 day', strtotime($endFinancial));
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

function check_target_breakdown($site_id, $task_id, $subtask_id)
{
    global $db;
    $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id = :task_id AND subtask_id = :subtask_id ');
    $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id));
    $stmt_result = $stmt->rowCount();
    return $stmt_result > 0 ? true : false;
}

function check_program_of_works($site_id, $task_id, $subtask_id)
{
    global $db;
    $query_rsWorkBreakdown = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
    $query_rsWorkBreakdown->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
    $row_rsWorkBreakdown = $query_rsWorkBreakdown->fetch();
    return $row_rsWorkBreakdown;
}

function get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency)
{
    global $db;
    $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND start_date=:start_date  AND end_date=:end_date AND frequency=:frequency');
    $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date, ":frequency" => $frequency));
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

function getStartAndEndDate($week, $year)
{
    $dto = new DateTime();
    $dto->setISODate($year, $week);
    $ret['week_start'] = $dto->format('Y-m-d');
    $dto->modify('+6 days');
    $ret['week_end'] = $dto->format('Y-m-d');
    return $ret;
}


function get_unit_of_measure($unit)
{
    global $db;
    $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
    $query_rsIndUnit->execute(array(":unit_id" => $unit));
    $row_rsIndUnit = $query_rsIndUnit->fetch();
    $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
    return $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
}

function get_table_body($task_start_date, $task_end_date, $target, $achieved, $start_date, $end_date)
{
    $contractor_start = '2023-08-01'; // 2023-07-01
    $contractor_end = '2024-06-30';
    $table = '';
    if (
        ($contractor_start >= $start_date && $contractor_end <= $end_date) ||
        ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
        ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
    ) {
        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
    } else {
        $table .= '<td style="width:15%">N/A</td><td style="width:15%">N/A</td>';
    }

    return $table;
}

function get_annual_table($startYears, $site_id, $task_id, $frequency, $output_id)
{
    global $db;

    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    $new_months = [];

    $table =
        '<div class="table-responsive">
        <table class="table table-bordered js-basic-example dataTable" id="direct_table">
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">Subtask</th>
                <th rowspan="2">Unit of Measure</th>';
    $colspan = '';
    for ($i = 0; $i < count($startYears); $i++) {
        $task_st_date = $Rows_rsTask_Start_Dates['start_date'];
        $task_ed_date = $Rows_rsTask_Start_Dates['end_date'];
        $startFinancial = $startYears[$i][0];
        $endFinancial = $startYears[$i][1];
        $contractor_start = '2023-08-01'; // 2023-07-01
        $contractor_end = '2024-06-30';
        $formated_date_start = date('Y', strtotime($startFinancial));
        $formated_date_end = date('Y', strtotime($endFinancial));
        if (
            ($contractor_start >= $startFinancial && $contractor_end <= $endFinancial) ||
            ($contractor_end >= $startFinancial && $contractor_end <= $endFinancial) ||
            ($contractor_start <= $startFinancial && $contractor_end >= $startFinancial && $contractor_end >= $endFinancial)
        ) {
            if (
                ($task_st_date >= $startFinancial && $task_st_date <= $endFinancial) ||
                ($task_ed_date >= $startFinancial && $task_ed_date <= $endFinancial) ||
                ($task_st_date <= $startFinancial && $task_ed_date >= $startFinancial && $task_ed_date >= $endFinancial)
            ) {
                $table .= "<th colspan='2'>$formated_date_start / $formated_date_end</th>";
                $colspan .= "<th>Target</th><th>Achieved</th>";
            }
        }
    }

    $table .= "</tr><tr>$colspan</tr></thead><tbody>";
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
            $table .=
                "<tr>
                <td style='width:5%'>$tcounter</td>
                <td style='width:40%'>$task_name</td>
                <td style='width:40%'>$unit_of_measure</td>";

            for ($i = 0; $i < count($startYears); $i++) {
                $start_date = $startYears[$i][0];
                $end_date = $startYears[$i][1];
                $task_start_date = $work_program ? $work_program['start_date'] : '';
                $task_end_date = $work_program ?  $work_program['end_date'] : '';
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
                //$table .= get_table_body($task_start_date, $task_end_date, $target = 0, $achieved, $start_date, $end_date);
                if (
                    ($contractor_start >= $start_date && $contractor_end <= $end_date) ||
                    ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                    ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
                ) {
                    if (
                        ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                        ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                        ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                    ) {

                        if ($target && $achieved) {
                            $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
                        }

                        if ($target && !$achieved) {
                            $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . 0 . '</td>';
                        }

                        if (!$target && $achieved) {
                            $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . $achieved . '</td>';
                        }

                        if (!$target && !$achieved) {
                            $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . 0 . '</td>';
                        }
                        
                    } else {
                        $table .= '<td style="width:15%">n/a</td><td style="width:15%">n/a</td>';
                    }
                }
            }
            $table .= '</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_semiannual_table($annually, $site_id, $task_id, $frequency, $output_id)
{
    global $db;

    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    $new_months = [];

    $table =
        '<div class="table-responsive">
        <table class="table table-bordered js-basic-example dataTable" id="direct_table">
        <thead>
            <tr>
                <th rowspan=2>#</th>
                <th rowspan=2>Subtask</th>
                <th rowspan=2>Unit of Measure</th>';
    $colspan = '';
    for ($i = 0; $i < count($annually); $i++) {
        for ($t = 0; $t < count($annually[$i]); $t++) {
            $task_st_date = $Rows_rsTask_Start_Dates['start_date'];
            $task_ed_date = $Rows_rsTask_Start_Dates['end_date'];
            $contractor_start = '2023-07-01'; // 2023-07-01
            $contractor_end = '2024-06-30';
            $annual_start = $annually[$i][$t][0];
            $annual_end = $annually[$i][$t][1];
            $formated_date_start = date('d M Y', strtotime($annual_start));
            $formated_date_end = date('d M Y', strtotime($annual_end));

            if (
                ($contractor_start >= $annual_start && $contractor_end <= $annual_end) ||
                ($contractor_end >= $annual_start && $contractor_end <= $annual_end) ||
                ($contractor_start <= $annual_start && $contractor_end >= $annual_start && $contractor_end >= $annual_end)
            ) {
                if (
                    ($task_st_date >= $annual_start && $task_st_date <= $annual_end) ||
                    ($task_ed_date >= $annual_start && $task_ed_date <= $annual_end) ||
                    ($task_st_date <= $annual_start && $task_ed_date >= $annual_start && $task_ed_date >= $annual_end)
                ) {
                    $new_months[] = [[$annual_start, $annual_end]];
                    $table .= "<th colspan='2'>$formated_date_start  /  $formated_date_end</th>";
                    $colspan .= "<th>Target</th><th>Achieved</th>";
                }
            }
        }
    }
    $table .=
        "</tr>
        <tr>$colspan</tr>
    </thead>
    <tbody>";

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

            $table .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            for ($i = 0; $i < count($new_months); $i++) {
                $start_date = $new_months[$i][0][0];
                $end_date = $new_months[$i][0][1];
                // $start_date = $annually[$i][$t][0];
                // $end_date = $annually[$i][$t][1];
                $task_start_date = $work_program ? $work_program['start_date'] : '';
                $task_end_date = $work_program ?  $work_program['end_date'] : '';
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
                // $table .= get_table_body($task_start_date, $task_end_date, $target, $achieved, $start_date, $end_date);
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    if ($target && $achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if ($target && !$achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . 0 . '</td>';
                    }

                    if (!$target && $achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if (!$target && !$achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . 0 . '</td>';
                    }
                } else {
                    $table .= '<td style="width:15%">n/a</td><td style="width:15%">n/a</td>';
                }
            }
            $table .= '</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_quarterly_table($quarterly, $site_id, $task_id, $frequency, $output_id)
{
    global $db;

    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    $new_months = [];

    $table =
        '<div class="table-responsive">
            <table class="table table-bordered js-basic-example dataTable" id="direct_table">
            <thead>
                <tr>
                    <th rowspan=2>#</th>
                    <th rowspan=2>Subtask</th>
                    <th rowspan=2>Unit of Measure</th>';
    $colspan = '';
    for ($i = 0; $i < count($quarterly); $i++) {
        for ($t = 0; $t < count($quarterly[$i]); $t++) {
            $task_st_date = $Rows_rsTask_Start_Dates['start_date'];
            $task_ed_date = $Rows_rsTask_Start_Dates['end_date'];
            $contractor_start = '2023-08-01'; // 2023-07-01
            $contractor_end = '2024-06-30';
            $annual_start = $quarterly[$i][$t][0];
            $annual_end = $quarterly[$i][$t][1];
            $formated_date_start = date('d M Y', strtotime($annual_start));
            $formated_date_end = date('d M Y', strtotime($annual_end));
            if (
                ($contractor_start >= $annual_start && $contractor_end <= $annual_end) ||
                ($contractor_end >= $annual_start && $contractor_end <= $annual_end) ||
                ($contractor_start <= $annual_start && $contractor_end >= $annual_start && $contractor_end >= $annual_end)
            ) {
                if (
                    ($task_st_date >= $annual_start && $task_st_date <= $annual_end) ||
                    ($task_ed_date >= $annual_start && $task_ed_date <= $annual_end) ||
                    ($task_st_date <= $annual_start && $task_ed_date >= $annual_start && $task_ed_date >= $annual_end)
                ) {
                    $new_months[] = [[$annual_start, $annual_end]];
                    $table .= "<th colspan='2'>$formated_date_start - $formated_date_end Target</th>";
                    $colspan .= "<th>Target</th><th>Achieved</th>";
                }
            }
        }
    }
    $table .=
        "</tr>
        <tr>$colspan</tr>
            </thead>
        <tbody>";

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
            $table .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            for ($i = 0; $i < count($new_months); $i++) {
                $start_date = $new_months[$i][0][0];
                $end_date = $new_months[$i][0][1];

                $task_start_date = $work_program ? $work_program['start_date'] : '';
                $task_end_date = $work_program ?  $work_program['end_date'] : '';
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
          
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    if ($target && $achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if ($target && !$achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . 0 . '</td>';
                    }

                    if (!$target && $achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if (!$target && !$achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . 0 . '</td>';
                    }
                } else {
                    $table .= '<td style="width:15%">n/a</td><td style="width:15%">n/a</td>';
                }
            }
            $table .= '</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_monthly_table($monthly, $site_id, $task_id, $frequency, $output_id)
{
    global $db;

    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();


    $new_months = [];
    $table =
        '<div class="table-responsive">
            <table class="table table-bordered js-basic-example dataTable" id="direct_table">
            <thead>
                <tr>
                    <th rowspan=2>#</th>
                    <th rowspan=2>Subtask</th>
                    <th rowspan=2>Unit of Measure</th>';
    $colspan = '';
    for ($i = 0; $i < count($monthly); $i++) {

        $task_st_date = $Rows_rsTask_Start_Dates['start_date'];
        $task_ed_date = $Rows_rsTask_Start_Dates['end_date'];
        $annual_start = $monthly[$i][0];
        $annual_end = $monthly[$i][1];
        $contractor_start = '2023-08-01'; // 2023-07-01
        $contractor_end = '2024-06-30';

        if (
            ($contractor_start >= $annual_start && $contractor_end <= $annual_end) ||
            ($contractor_end >= $annual_start && $contractor_end <= $annual_end) ||
            ($contractor_start <= $annual_start && $contractor_end >= $annual_start && $contractor_end >= $annual_end)
        ) {

            if (
                ($task_st_date >= $annual_start && $task_st_date <= $annual_end) ||
                ($task_ed_date >= $annual_start && $task_ed_date <= $annual_end) ||
                ($task_st_date <= $annual_start && $task_ed_date >= $annual_start && $task_ed_date >= $annual_end)
            ) {
                $new_months[] = [$annual_start, $annual_end];
                $formated_date_start = date('M Y', strtotime($annual_start));
                $miakaMonthly[] = $annual_start . '/' . $annual_end;
                $table .= "<th colspan='2' style='width:10%'> $formated_date_start</th>";
                $colspan .= "<th>Target</th><th>Achieved</th>";
            }
        }
    }

    $table .=
        "</tr>
        <tr>$colspan</tr>
            </thead>
        <tbody>";
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
            $table .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            for ($i = 0; $i < count($new_months); $i++) {
                $start_date = $new_months[$i][0];
                $end_date = $new_months[$i][1];
                $annual_start = $new_months[$i][0];
                $annual_end = $new_months[$i][1];
                $task_start_date = $work_program ? $work_program['start_date'] : '';
                $task_end_date = $work_program ?  $work_program['end_date'] : '';
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
                //$table .= get_table_body($task_start_date, $task_end_date, $target = 0, $achieved = 0, $start_date, $end_date);
                // if (
                //     ($contractor_start >= $start_date && $contractor_end <= $end_date) ||
                //     ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                //     ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
                // ) {
                // check if there is target
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    if ($target && $achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if ($target && !$achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . 0 . '</td>';
                    }

                    if (!$target && $achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if (!$target && !$achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . 0 . '</td>';
                    }
                } else {
                    $table .= '<td style="width:15%">n/a</td><td style="width:15%">n/a</td>';
                }
                // }
            }
            $table .= '</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
}

function get_weekly_table($project_start_date, $project_end_date, $site_id, $task_id, $frequency, $output_id)
{
    global $db;
    $thead = $tbody = '';
    $start_year = date('Y', strtotime($project_start_date));
    $end_year = date('Y', strtotime($project_end_date));
    $end_month = date('M', strtotime($project_end_date));
    $end_year = ($end_month >= 7 && $end_month <= 12) ? $end_year + 1 : $end_year;
    $duration = ($end_year - $start_year)  + 1;


    $head_year = $start_year;
    $colspan = '';
    for ($j = 0; $j < $duration; $j++) {
        for ($i = 1; $i < 53; $i++) {
            $week_array = getStartAndEndDate($i, $head_year);
            $formated_date_start =  date('Y-m-d', strtotime($week_array['week_start']));
            $formated_date_end =  date('Y-m-d', strtotime($week_array['week_end']));
            $date =  $formated_date_start . " " . $formated_date_end;
            
            if ($project_start_date < $formated_date_end && $project_end_date >= $formated_date_start) {
                $thead .= "<th colspan='2' style='width:10%'> $date </th>";
                $colspan .= "<th>Target</th><th>Achieved</th>";
            }
        }
        $head_year++;
    }

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
            $tbody .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            $body_year = $start_year;
            for ($p = 0; $p < $duration; $p++) {
                for ($q = 1; $q < 53; $q++) {
                    $week_array = getStartAndEndDate($q, $body_year);
                    $start_date =  date('Y-m-d', strtotime($week_array['week_start']));
                    $end_date =  date('Y-m-d', strtotime($week_array['week_end']));
                    if ($project_start_date <= $end_date && $project_end_date >= $start_date) {
                        $task_start_date = $work_program ? $work_program['start_date'] : '';
                        $task_end_date = $work_program ?  $work_program['end_date'] : '';
                        $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                        $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
                        $tbody .= get_table_body($task_start_date, $task_end_date, $target, $achieved, $start_date, $end_date);
                    }
                }
                $body_year++;
            }
            $tbody .= '</td></tr>';
        }
    }

    $table =
        '<div class="table-responsive">
            <table class="table table-bordered js-basic-example dataTable" id="direct_table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subtask</th>
                    <th>Unit of Measure</th>
                    ' . $thead . '
                </tr>
                <tr>' . $colspan . '</tr>
            </thead>
            <tbody>
            ' . $tbody . '
            </tbody>
            </table>
        </div>';

    return $table;
}

function get_daily_table($project_start_date, $project_end_date, $site_id, $task_id, $frequency, $output_id)
{
    global $db;

    $contractor_start = $project_start_date; // 2023-07-01
    $contractor_end = $project_end_date;

    $con = true;
    if ($con) {
        $contractor_start = '2023-08-01'; // 2023-07-01
        $contractor_end = '2024-06-30';
    }
    // gets the task start dates
    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $res = $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    // $task_st_date = $Rows_rsTask_Start_Dates['start_date'];
    // $task_ed_date = $Rows_rsTask_Start_Dates['end_date'];
    if ($res) {
        $contractor_start = $Rows_rsTask_Start_Dates['start_date'];
        $contractor_end = $Rows_rsTask_Start_Dates['end_date'];
    }

    $colspan = '';
    $table =
        '<div class="table-responsive">
            <table class="table table-bordered js-basic-example dataTable" id="direct_table">
            <thead>
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Subtask</th>
                    <th rowspan="2">Unit of Measure</th>';
    // $daily_task_start_date = $project_start_date;
    // while ($daily_task_start_date <= $project_end_date) {
    //     $date = date('d M Y', strtotime($daily_task_start_date));
    //     $daily_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
    //     $table .= "<th>$date Target</th>";
    // }

    // loop through contractor
    $con_task_start_date = $contractor_start;
    while ($con_task_start_date <= $contractor_end) {
        $date = date('d M Y', strtotime($con_task_start_date));
        $con_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        $table .= "<th colspan='2'>$date</th>";
        $colspan .= "<th>Target</th><th>Achieved</th>";
    }

    // loop through task
    /**
     * [1 => []]
     */

    $sub_task_daily = [];
    $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid  ORDER BY parenttask");
    $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $task_id));
    $totalRows_rsTasks = $query_rsTasks->rowCount();
    while ($row_rsTasks = $query_rsTasks->fetch()) {
        $task_name = $row_rsTasks['task'];
        $subtask_id = $row_rsTasks['tkid'];
        $work_program = check_program_of_works($site_id, $task_id, $subtask_id);
        $task_start_date = $work_program ? $work_program['start_date'] : '';
        $task_end_date = $work_program ?  $work_program['end_date'] : '';
        $loop_task_start_date = $task_start_date;
        while ($loop_task_start_date <= $task_end_date) {
            $date = date('d M Y', strtotime($loop_task_start_date));
            $sub_task_daily[$subtask_id][$loop_task_start_date] = $loop_task_start_date;
            $loop_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }
    }


    $table .=
        "</tr>
        <tr>$colspan</tr>
            </thead>
            <tbody>";
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
            $table .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            $con_task_start_date = $contractor_start;
            $counter = 0;
            while ($con_task_start_date <= $contractor_end) {
                $exists = isset($sub_task_daily[$subtask_id][$con_task_start_date]);
                $date = date('Y-m-d', strtotime($con_task_start_date));
                $y = "$date";
                $frequency = 1;
                $target = get_target($site_id, $task_id, $subtask_id, $con_task_start_date, $con_task_start_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $con_task_start_date, $con_task_start_date);
                if ($exists) {
                    if ($target && $achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if ($target && !$achieved) {
                        $table .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . 0 . '</td>';
                    }

                    if (!$target && $achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if (!$target && !$achieved) {
                        $table .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . 0 . '</td>';
                    }
                } else {
                    $table .= '<td >N/A</td><td >N/A</td>';
                }

                $con_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                $counter++;
            }
            $table .= '</tr>';
        }
    }
    $table .= '</tbody></table></div>';
    return $table;
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
    $task_id = $_GET['task_id'];
    $output_id = $_GET['output_id'];
    $projid = $_GET['projid'];
    $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
    $query_rsProjects->execute(array(":projid" => $projid));
    $row_rsProjects = $query_rsProjects->fetch();
    $totalRows_rsProjects = $query_rsProjects->rowCount();
    $table = '';
    if ($totalRows_rsProjects > 0) {
        $min_date = $row_rsProjects['projstartdate'];
        $max_date = $row_rsProjects['projenddate'];

        $frequency = $row_rsProjects['activity_monitoring_frequency'];
        $duration_years = get_duration($min_date, $max_date);
        $proj_start_year = date('Y', strtotime($min_date));
        $duration_years = ($duration_years == 0) ? $duration_years = 1 : $duration_years;
        $details = index($duration_years, $proj_start_year);
        $startYears = $details['startYears'];
        $annually = $details['annually'];
        $quarterly = $details['quarterly'];
        $monthly = $details['monthly'];

        $frequency = 1;
        if ($frequency == 6) { // yearly
            $table = get_annual_table($startYears, $site_id, $task_id, $frequency, $output_id);
        } elseif ($frequency == 5) {
            $table = get_semiannual_table($annually, $site_id, $task_id, $frequency, $output_id);
        } else if ($frequency == 4) {
            $table = get_quarterly_table($quarterly, $site_id, $task_id, $frequency, $output_id);
        } else if ($frequency == 3) {
            $table = get_monthly_table($monthly, $site_id, $task_id, $frequency, $output_id);
        } else if ($frequency == 2) {
            $table = get_weekly_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id);
        } else if ($frequency == 1) {
            $table  = get_daily_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id);
        }
    }
    echo json_encode(array("success" => true, 'frequency' => $frequency, 'table' => $table, 'task_id' => $task_id));
}
