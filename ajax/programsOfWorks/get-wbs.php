
<?php
include '../controller.php';

function index($duration, $start_year)
{
    $f_start = '07-01';
    $f_end = '06-30';
    $startYears =  [];
    for ($i = 0; $i < $duration; $i++) {
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

    // get end date
    $l = $annually[count($annually) - 1][1][1];
    // get start date
    $s = $annually[0][0][0];
    $sInc = $s;
    while ($sInc <= $l) {
        $start = $sInc;
        $startFinancialMidPoint = strtotime('+3 months -1 day', strtotime($sInc));
        $date = date('Y-m-d', $startFinancialMidPoint);
        $quarterly[] = [$start, $date];
        $sInc = date('Y-m-d', strtotime('+3 months', strtotime($sInc)));
    }


    $startFinancial = $quarterly[0][0];
    $endFinancial = $quarterly[count($quarterly) - 1][1];
    $monthly = [];
    while ($startFinancial <= $endFinancial) {
        $startFinancialMidPoint = strtotime('+1 month -1 day', strtotime($startFinancial));
        $date = date('Y-m-d', $startFinancialMidPoint);
        $monthly[] = [$startFinancial, $date];
        $startFinancial = date('Y-m-d', strtotime('+1 day', strtotime($date)));
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

function get_unit_of_measure($unit)
{
    global $db;
    $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
    $query_rsIndUnit->execute(array(":unit_id" => $unit));
    $row_rsIndUnit = $query_rsIndUnit->fetch();
    $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
    return $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
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

function get_task_dates($task_id, $site_id)
{
    global $db;
    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    return $Rows_rsTask_Start_Dates;
}

function filter_head($contractor_start, $contractor_end, $start_date, $end_date, $task_id, $site_id)
{
    $response = false;
    $task_details = get_task_dates($task_id, $site_id);
    $task_start_date = !is_null($task_details['start_date']) ? $task_details['start_date'] : '';
    $task_end_date =  !is_null($task_details['end_date']) ? $task_details['end_date'] : '';

    if (
        ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
        ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
        ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
    ) {
        if ($task_start_date != '' && $task_end_date != '') {
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $response = true;
            }
        } else {
            $response = true;
        }
    }
    return $response;
}

function filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $site_id, $task_id, $subtask_id, $flag)
{
    $work_program = check_program_of_works($site_id, $task_id, $subtask_id);
    $task_start_date = $work_program ? $work_program['start_date'] : '';
    $task_end_date = $work_program ?  $work_program['end_date'] : '';
    $table = '';
    if ($flag == 2) {
        if (
            ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
            ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
            ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
        ) {
            $table .= '<td style="width:15%">' . $target . '</td>';
        } else {
            $table .= '<td style="width:15%">n/a</td>';
        }
    } else {
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
                $table .= '<td style="width:15%">' . $target . '</td>';
            } else {
                $table .= '<td style="width:15%">n/a</td>';
            }
        }
    }
    return $table;
}

function get_annual_table($startYears, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end)
{
    global $db;

    $head = $body = '';
    for ($i = 0; $i < count($startYears); $i++) {
        $start_date = $startYears[$i][0];
        $end_date = $startYears[$i][1];
        $formated_date_start = date('Y', strtotime($start_date));
        $formated_date_end = date('Y', strtotime($end_date));
        $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date, $task_id, $site_id);
        if ($response) {
            $head .= "<th>$formated_date_start / $formated_date_end Target</th>";
        }
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
            $body .=
                "<tr>
                <td style='width:5%'>$tcounter</td>
                <td style='width:40%'>$task_name</td>
                <td style='width:40%'>$unit_of_measure</td>";

            for ($i = 0; $i < count($startYears); $i++) {
                $start_date = $startYears[$i][0];
                $end_date = $startYears[$i][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $body .= filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $site_id, $task_id, $subtask_id, 1);
            }

            $breakdown = check_target_breakdown($site_id, $task_id, $subtask_id);
            $body .= '<td>';
            if ($work_program) {
                $body .=
                    '<button type="button" onclick="get_subtasks_wbs(' . $output_id . ', ' . $site_id . ', ' . $task_id . ', ' . $subtask_id . ')" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px" >';
                $body .= $breakdown ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>';
                $body .= '
                </button>';
            }
            $body .= '</td></tr>';
        }
    }

    return array('head' => $head, 'body' => $body);
}

function get_semiannual_table($startYears, $annually, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end)
{
    global $db;
    $head = $body = '';
    $new_months = [];
    for ($i = 0; $i < count($annually); $i++) {
        for ($t = 0; $t < count($annually[$i]); $t++) {
            $start_date = $annually[$i][$t][0];
            $end_date = $annually[$i][$t][1];
            // $formated_date_start = date('d M Y', strtotime($start_date));
            // $formated_date_end = date('d M Y', strtotime($end_date));
            // $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date, $task_id, $site_id);
            // if ($response) {
            //     $new_months[] = [[$start_date, $end_date]];
            //     $head .= "<th>$formated_date_start / $formated_date_end Target</th>";
            // }

            $task_details = get_task_dates($task_id, $site_id);
            $task_start_date = !is_null($task_details['start_date']) ? $task_details['start_date'] : '';
            $task_end_date =  !is_null($task_details['end_date']) ? $task_details['end_date'] : '';

            if (
                ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
                ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
            ) {
                if ($task_start_date != '' && $task_end_date != '') {
                    if (
                        ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                        ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                        ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                    ) {
                        $new_months[] = [$start_date, $end_date];
                    }
                }
            }
        }
    }


    $tr_years = $tr_months = '';
    for ($i=0; $i < count($startYears); $i++) { 
        $spans = 0;
        $task_start_date = $startYears[$i][0];
        $task_end_date = $startYears[$i][1];

        for ($t = 0; $t < count($new_months); $t++) {
            $start_date = $new_months[$t][0];
            $end_date = $new_months[$t][1];
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $spans++;
            }
        }

        if ($spans != 0) {
            $formated_head_start = date('Y', strtotime($task_start_date));
            $formated_head_end = date('Y', strtotime($task_end_date));
            $tr_years .= '<th colspan=' . $spans . '>' . $formated_head_start . ' / ' . $formated_head_end . '</th>';
            for ($t = 0; $t < count($new_months); $t++) {
                $start_date = $new_months[$t][0];
                $end_date = $new_months[$t][1];
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    $formated_date_start = date('F', strtotime($start_date));
                    $tr_months .= "<th>$start_date $end_date</th>";
                }
            }
        }

    }

    $head = '
    <tr>
        <th rowspan="2">#</th>
        <th rowspan="2">Subtask</th>
        <th rowspan="2">Unit of Measure</th>
        ' . $tr_years . '
        <th rowspan="2">Action</th>
    </tr>
    <tr>
    ' . $tr_months . '
    </tr>';


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

            $body .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            for ($i = 0; $i < count($new_months); $i++) {
                $start_date = $new_months[$i][0];
                $end_date = $new_months[$i][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $body .= filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $site_id, $task_id, $subtask_id, 2);
            }

            $breakdown = check_target_breakdown($site_id, $task_id, $subtask_id);
            $body .= '<td>';
            if ($work_program) {
                $body .=
                    '<button type="button" onclick="get_subtasks_wbs(' . $output_id . ', ' . $site_id . ', ' . $task_id . ', ' . $subtask_id . ')" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px" >';
                $body .= $breakdown ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>';
                $body .= '
                </button>';
            }
            $body .= '</td></tr>';
        }
    }
    return array('head' => $head, 'body' => $body);
}

function get_quarterly_table($startYears, $quarterly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end)
{
    global $db;
    $head = $body = '';
    $new_months = [];
    for ($i = 0; $i < count($quarterly); $i++) {
        $start_date = $quarterly[$i][0];
        $end_date = $quarterly[$i][1];

        $task_details = get_task_dates($task_id, $site_id);
        $task_start_date = !is_null($task_details['start_date']) ? $task_details['start_date'] : '';
        $task_end_date =  !is_null($task_details['end_date']) ? $task_details['end_date'] : '';

        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            if ($task_start_date != '' && $task_end_date != '') {
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    $new_months[] = [$start_date, $end_date];
                }
            }
        }
        // $formated_date_start = date('d M Y', strtotime($start_date));
        // $formated_date_end = date('d M Y', strtotime($end_date));
        // $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date, $task_id, $site_id);
        // if ($response) {
        //     $new_months[] = [$start_date, $end_date];
        //     // $head .= "<th>$formated_date_start / $formated_date_end Target</th>";
        // }
    }

    $tr_years = $tr_months = '';
    for ($i=0; $i < count($startYears); $i++) { 
        $spans = 0;
        $task_start_date = $startYears[$i][0];
        $task_end_date = $startYears[$i][1];

        for ($t = 0; $t < count($new_months); $t++) {
            $start_date = $new_months[$t][0];
            $end_date = $new_months[$t][1];
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $spans++;
            }
        }

        if ($spans != 0) {
            $formated_head_start = date('Y', strtotime($task_start_date));
            $formated_head_end = date('Y', strtotime($task_end_date));
            $tr_years .= '<th colspan=' . $spans . '>' . $formated_head_start . ' / ' . $formated_head_end . '</th>';
            for ($t = 0; $t < count($new_months); $t++) {
                $start_date = $new_months[$t][0];
                $end_date = $new_months[$t][1];
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    $formated_date_start = date('F', strtotime($start_date));
                    $tr_months .= "<th>$start_date $end_date</th>";
                }
            }
        }

    }

    $head = '
    <tr>
        <th rowspan="2">#</th>
        <th rowspan="2">Subtask</th>
        <th rowspan="2">Unit of Measure</th>
        ' . $tr_years . '
        <th rowspan="2">Action</th>
    </tr>
    <tr>
    ' . $tr_months . '
    </tr>';

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
            $body .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            for ($i = 0; $i < count($new_months); $i++) {
                $start_date = $new_months[$i][0];
                $end_date = $new_months[$i][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $body .= filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $site_id, $task_id, $subtask_id, 2);
            }

            $breakdown = check_target_breakdown($site_id, $task_id, $subtask_id);
            $body .= '<td>';
            if ($work_program) {
                $body .=
                    '<button type="button" onclick="get_subtasks_wbs(' . $output_id . ', ' . $site_id . ', ' . $task_id . ', ' . $subtask_id . ')" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px" >';
                $body .= $breakdown ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>';
                $body .= '
                </button>';
            }
            $body .= '</td></tr>';
        }
    }
    return array('head' => $head, 'body' => $body);
}

function get_monthly_table($startYears, $monthly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end)
{
    global $db;
    $head = $body = '';


    $new_months = [];
    for ($i = 0; $i < count($monthly); $i++) {
        $start_date = $monthly[$i][0];
        $end_date = $monthly[$i][1];

        $task_details = get_task_dates($task_id, $site_id);
        $task_start_date = !is_null($task_details['start_date']) ? $task_details['start_date'] : '';
        $task_end_date =  !is_null($task_details['end_date']) ? $task_details['end_date'] : '';
        
        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            if ($task_start_date != '' && $task_end_date != '') {
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    $new_months[] = [$start_date, $end_date];
                }
            }
        }
        // $response = filter_head($contractor_start, $contractor_end, $start_date, $end_date, $task_id, $site_id);
        // if ($response) {
        //     $new_months[] = [$start_date, $end_date];
        //     $head .= "<th>$formated_date_start</th>";
        // }
    }



    // get years  
    $tr_years = $tr_months = '';
    for ($i = 0; $i < count($startYears); $i++) {
        $spans = 0;
        $task_start_date = $startYears[$i][0];
        $task_end_date = $startYears[$i][1];

        for ($t = 0; $t < count($new_months); $t++) {
            $start_date = $new_months[$t][0];
            $end_date = $new_months[$t][1];
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $spans++;
            }
        }


        if ($spans != 0) {
            $formated_head_start = date('Y', strtotime($task_start_date));
            $formated_head_end = date('Y', strtotime($task_end_date));
            $tr_years .= '<th colspan=' . $spans . '>' . $formated_head_start . ' / ' . $formated_head_end . '</th>';
            // loop below insert here
            for ($t = 0; $t < count($new_months); $t++) {
                $start_date = $new_months[$t][0];
                $end_date = $new_months[$t][1];
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    $formated_date_start = date('F', strtotime($start_date));
                    $tr_months .= "<th>$formated_date_start</th>";
                }
            }
        }
    }


    $head = '
    <tr>
        <th rowspan="2">#</th>
        <th rowspan="2">Subtask</th>
        <th rowspan="2">Unit of Measure</th>
        ' . $tr_years . '
        <th rowspan="2">Action</th>
    </tr>
    <tr>
    ' . $tr_months . '
    </tr>';

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

            $body .=
                "<tr>
                    <td style='width:5%'>$tcounter</td>
                    <td style='width:40%'>$task_name</td>
                    <td style='width:40%'>$unit_of_measure</td>";
            for ($i = 0; $i < count($new_months); $i++) {
                $start_date = $new_months[$i][0];
                $end_date = $new_months[$i][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $body .= filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $site_id, $task_id, $subtask_id, 2);
            }

            $breakdown = check_target_breakdown($site_id, $task_id, $subtask_id);
            $body .= '<td style="width:10%">';
            if ($work_program) {
                $body .= '<button type="button" onclick="get_subtasks_wbs(' . $output_id . ', ' . $site_id . ', ' . $task_id . ', ' . $subtask_id . ')" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px" >';
                $body .= $breakdown ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>';
                $body .= '</button>';
            }
            $body .= '</td></tr>';
        }
    }
    return array('head' => $head, 'body' => $body);
}

function get_weekly_table($project_start_date, $project_end_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end)
{
    global $db;
    $start_year = date('Y', strtotime($project_start_date));
    $end_year = date('Y', strtotime($project_end_date));
    $end_month = date('M', strtotime($project_end_date));
    $end_year = ($end_month >= 7 && $end_month <= 12) ? $end_year + 1 : $end_year;
    $duration = ($end_year - $start_year) + 1;
    $head = $body = '';
    $head_year = $start_year;
    for ($j = 0; $j < $duration; $j++) {
        for ($i = 1; $i < 53; $i++) {
            $week_array = getStartAndEndDate($i, $head_year);
            $formated_date_start =  date('Y-m-d', strtotime($week_array['week_start']));
            $formated_date_end =  date('Y-m-d', strtotime($week_array['week_end']));
            $date =  $formated_date_start . " " . $formated_date_end;
            if ($project_start_date < $formated_date_end && $project_end_date >= $formated_date_start) {
                $response = filter_head($contractor_start, $contractor_end, $formated_date_start, $formated_date_end, $task_id, $site_id);
                if ($response) {
                    $head .= "<th style='width:10%'> $date Target</th>";
                }
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
            $body .=
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
                        $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                        $body .= filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $site_id, $task_id, $subtask_id, 2);
                    }
                }
                $body_year++;
            }
            $breakdown = check_target_breakdown($site_id, $task_id, $subtask_id);
            $body .= '<td style="width:10%">';
            if ($work_program) {
                $body .= '<button type="button" onclick="get_subtasks_wbs(' . $output_id . ', ' . $site_id . ', ' . $task_id . ', ' . $subtask_id . ')" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px" >';
                $body .= $breakdown ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>';
                $body .= '</button>';
            }
            $body .= '</td></tr>';
        }
    }

    return array('head' => $head, 'body' => $body);
}

function get_daily_table($project_start_date, $project_end_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end)
{
    global $db;
    // gets the task start dates
    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) AS start_date, MAX(end_date) AS end_date FROM `tbl_program_of_works` WHERE task_id=:task_id AND site_id=:site_id");
    $res = $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id));
    $Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    if ($res) {
        $contractor_start = $Rows_rsTask_Start_Dates['start_date'];
        $contractor_end = $Rows_rsTask_Start_Dates['end_date'];
    }

    $colspan = $head = $body = '';
    $con_task_start_date = $contractor_start;
    while ($con_task_start_date <= $contractor_end) {
        $date = date('d M Y', strtotime($con_task_start_date));
        $con_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        $head .= "<th>$date</th>";
        $colspan .= "<th>Target</th><th>Achieved</th>";
    }

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
            $body .=
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
                        $body .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if ($target && !$achieved) {
                        $body .= '<td style="width:15%">' . $target . '</td><td style="width:15%">' . 0 . '</td>';
                    }

                    if (!$target && $achieved) {
                        $body .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . $achieved . '</td>';
                    }

                    if (!$target && !$achieved) {
                        $body .= '<td style="width:15%">' . 0 . '</td><td style="width:15%">' . 0 . '</td>';
                    }
                } else {
                    $body .= '<td >N/A</td><td >N/A</td>';
                }

                $con_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                $counter++;
            }
            $body .= '</tr>';
        }
    }
    return array('head' => $head, 'colspan' => $colspan, 'body' => $body);
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
    $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
    $query_rsProjects->execute(array(":projid" => $projid));
    $row_rsProjects = $query_rsProjects->fetch();
    $totalRows_rsProjects = $query_rsProjects->rowCount();
    $frequency = '';

    $table_details = array('head' => '', 'colspan' => '', 'body' => '');
    if ($totalRows_rsProjects > 0) {
        $query_rsWorkBreakdown = $db->prepare("SELECT MIN(start_date) AS startdate,MAX(end_date) AS enddate FROM tbl_program_of_works WHERE projid=:projid");
        $query_rsWorkBreakdown->execute(array(':projid' => $projid));
        $row_rsWorkBreakdown = $query_rsWorkBreakdown->fetch();

        $min_date = $row_rsWorkBreakdown['startdate'];
        $max_date = $row_rsWorkBreakdown['enddate'];
        $frequency = $row_rsProjects['activity_monitoring_frequency'];
        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();
        $contractor_start = $end_date = '';
        if ($totalRows_rsTender > 0) {
            $contractor_start = $row_rsWorkBreakdown['startdate'];
            $contractor_end = $row_rsWorkBreakdown['enddate'];

            $date_details = get_duration($min_date, $max_date);
            $details = index($date_details['duration'], $date_details['start_year'], $contractor_start, $contractor_end, $task_id, $site_id);
            $startYears = $details['startYears'];
            $annually = $details['annually'];
            $quarterly = $details['quarterly'];
            $monthly = $details['monthly'];
            $frequency = 3;
            if ($frequency == 6) {
                $table_details = get_annual_table($startYears, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end);
            } elseif ($frequency == 5) {
                $table_details = get_semiannual_table($startYears, $annually, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end);
            } else if ($frequency == 4) {
                $table_details = get_quarterly_table($startYears,$quarterly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end);
            } else if ($frequency == 3) {
                $table_details = get_monthly_table($startYears, $monthly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end);
            } else if ($frequency == 2) {
                $table_details = get_weekly_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end);
            } else if ($frequency == 1) {
                $table_details  = get_daily_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end);
            }
        }
    }

    $table =
        '<div class="table-responsive">
            <table class="table table-bordered js-basic-example dataTable" id="direct_table">
                <thead>
                
                ' . $table_details['head'] . '
                   
                </thead>
                <tbody>
                ' . $table_details['body'] . '
                </tbody>
            </table>
        </div>';

    echo json_encode(array("success" => true, 'frequency' => $frequency, 'table' => $table, 'task_id' => $task_id));
}
