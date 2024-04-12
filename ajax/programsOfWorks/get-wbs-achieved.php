<?php
include '../controller.php';

function get_table_body($contractor_start, $contractor_end, $task_start_date, $task_end_date, $target, $start_date, $end_date, $counter, $extension_bool, $extension_start)
{
    $tr = '';
    if (
        ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
        ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
        ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
    ) {
        if (
            ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
            ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
            ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
        ) {
            $formated_date_start = date('d M Y', strtotime($start_date));
            $formated_date_end = date('d M Y', strtotime($end_date));
            if ($extension_bool) {
                if ($start_date >= $extension_start) {
                    $tr .=
                        '<tr>
                            <td>' . $counter . '</td>
                            <td>' . $formated_date_start . ' - ' .  $formated_date_end . '</td>
                            <td>
                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                            </td>
                        </tr>';
                    $counter++;
                }
            } else {
                $tr .=
                    '<tr>
                        <td>' . $counter . '</td>
                        <td>' . $formated_date_start . ' - ' .  $formated_date_end . '</td>
                        <td>
                            <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                            <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                            <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                        </td>
                    </tr>';

                $counter++;
            }
        }
    }
    return array('table_body' => $tr, "counter" => $counter);
}

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

function get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date)
{
    global $db;
    $stmt = $db->prepare('SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND task_id=:task_id AND subtask_id=:subtask_id AND created_at >=:start_date  AND created_at <=:end_date');
    $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id, ":start_date" => $start_date, ":end_date" => $end_date));
    $result = $stmt->fetch();
    $target = !is_null($result['achieved'])  ? $result['achieved'] : 0;
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

function filter_body($contractor_start, $contractor_end, $start_date, $end_date, $target, $achieved, $site_id, $task_id, $subtask_id, $flag)
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


    return $table;
}

function get_annual_table($startYears, $site_id, $task_id,  $frequency, $output_id, $contractor_start, $contractor_end, $subtask_id, $task_start_date, $task_end_date)
{
    $tr = '';
    $counter = 1;
    $hash = 1;
    $input_array = [];

    for ($i = 0; $i < count($startYears); $i++) {
        $start_date = $startYears[$i][0];
        $end_date = $startYears[$i][1];
        // $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $input_array[] = [$start_date, $end_date];
            }
        }
    }

    for ($i = 0; $i < count($input_array); $i++) {
        $start_date = $input_array[$i][0];
        $end_date = $input_array[$i][1];
        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $formated_date_start = date('d M Y', strtotime($start_date));
                $formated_date_end = date('d M Y', strtotime($end_date));
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
                if (count($input_array) == 1) {
                    $tr .=
                        '<tr>
                        <td>' . $counter . '</td>
                        <td> Annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                        <td>
                            
                            ' . $target . '
                            
                        </td>
                        <td>' . $achieved . '
                    </tr>';
                } else {
                    if ($start_date <= $task_start_date) {
                        $tr .=
                            '<tr>
                            <td>' . $counter . '</td>
                            <td> Annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                            <td>
                                
                                ' . $target . '
                            </td>
                            <td>' . $achieved . '
                        </tr>';
                    } else if ($end_date >= $task_end_date) {
                        $tr .=
                            '<tr>
                            <td>' . $counter . '</td>
                            <td> Annual ' . $hash . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                            <td>
                               
                                ' . $target . '
                            </td>
                            <td>' . $achieved . '
                        </tr>';
                    } else {
                        $tr .=
                            '<tr>
                            <td>' . $counter . '</td>
                            <td> Annual ' . $hash . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                            <td>
                                
                               ' . $target . '
                            </td>
                            <td>' . $achieved . '
                        </tr>';
                    }
                }

                $hash++;
                $counter++;
            }
        }
    }

    $table = '
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Annual</th>
                <th>Target</th>
                <th>Achieved</th>
            </tr>
        </thead>
        <tbody>
        ' . $tr . '
        </tbody>
    </table>';

    return $table;
}

function get_semiannual_table($annually, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $startYears, $subtask_id, $task_start_date, $task_end_date)
{
    $counter = 1;
    $hash = 1;
    $tr = '';
    $input_array = [];

    for ($i = 0; $i < count($annually); $i++) {
        for ($t = 0; $t < count($annually[$i]); $t++) {
            $start_date = $annually[$i][$t][0];
            $end_date = $annually[$i][$t][1];

            if (
                ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
                ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
            ) {
                if (
                    ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                    ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                    ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                ) {
                    $formated_date_start = date('d M Y', strtotime($start_date));
                    $formated_date_end = date('d M Y', strtotime($end_date));

                    $input_array[] = [$start_date, $end_date];
                }
            }
        }
    }

    $h = 1;
    for ($i = 0; $i < count($startYears); $i++) {
        $inner_years = [];

        $spans = 0;
        $task_start_date_f = $startYears[$i][0];
        $task_end_date_f = $startYears[$i][1];

        for ($t = 0; $t < count($input_array); $t++) {
            $start_date = $input_array[$t][0];
            $end_date = $input_array[$t][1];
            if (
                ($task_start_date_f >= $start_date && $task_start_date_f <= $end_date) ||
                ($task_end_date_f >= $start_date && $task_end_date_f <= $end_date) ||
                ($task_start_date_f <= $start_date && $task_end_date_f >= $start_date && $task_end_date_f >= $end_date)
            ) {
                $spans++;
                $inner_years[] = [$start_date, $end_date];
            }
        }


        if ($spans != 0) {
            $spans++;
            $formated_head_start = date('Y', strtotime($task_start_date_f));
            $formated_head_end = date('Y', strtotime($task_end_date_f));
            $tr .= '<tr ><td rowspan=' . $spans . '>' . $h . '</td><td rowspan=' . $spans . '>' . $formated_head_start . ' / ' . $formated_head_end . '</td></tr>';
            $h++;

            for ($b = 0; $b < count($inner_years); $b++) {
                $start_date = $inner_years[$b][0];
                $end_date = $inner_years[$b][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);

                if (
                    ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
                    ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                    ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
                ) {
                    if (
                        ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                        ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                        ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                    ) {
                        $formated_date_start = date('d M Y', strtotime($start_date));
                        $formated_date_end = date('d M Y', strtotime($end_date));

                        if (count($input_array) == 1) {
                            $tr .=
                                '<tr>
                                    <td> Semi annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                    <td>
                                        ' . $target . '
                                    </td>
                                    <td>' . $achieved . '</td>
                                </tr>';
                        } else {
                            if ($start_date <= $task_start_date) {
                                $tr .=
                                    '<tr>
                                    <td> Semi annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                    <td>
                                               ' . $target . '
                                            </td>
                                            <td>' . $achieved . '</td>
                                </tr>';
                            } else if ($end_date >= $task_end_date) {
                                $tr .=
                                    '<tr>
                                    <td> Semi annual ' . $hash . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                    <td>
                                        ' . $target . '
                                    </td>
                                    <td>' . $achieved . '</td>
                                </tr>';
                            } else {
                                $tr .=
                                    '<tr>
                                    <td> Semi annual ' . $hash . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                    <td>
                                        ' . $target . '
                                    </td>
                                    <td>' . $achieved . '</td>
                                </tr>';
                            }
                        }

                        $counter++;
                    }
                }
                $hash++;
            }
        }
    }

    $table = '
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Year</th>
                <th>Semi Annual</th>
                <th>Target</th>
                <th>Achieved</th>
            </tr>
        </thead>
        <tbody>
        ' . $tr . '
        </tbody>
    </table>';

    return $table;
}

function get_quarterly_table($quarterly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $startYears, $subtask_id, $task_start_date, $task_end_date)
{
    $counter = 1;
    $hash = 1;
    $tr = '';

    $input_array = [];


    for ($i = 0; $i < count($quarterly); $i++) {
        $start_date = $quarterly[$i][0];
        $end_date = $quarterly[$i][1];

        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $formated_date_start = date('d M Y', strtotime($start_date));
                $formated_date_end = date('d M Y', strtotime($end_date));

                $input_array[] = [$start_date, $end_date];
            }
        }
    }

    $h = 1;

    for ($i = 0; $i < count($startYears); $i++) {
        $inner_years = [];

        $spans = 0;

        $task_start_date_f = $startYears[$i][0];
        $task_end_date_f = $startYears[$i][1];

        for ($t = 0; $t < count($input_array); $t++) {
            $start_date = $input_array[$t][0];
            $end_date = $input_array[$t][1];
            if (
                ($task_start_date_f >= $start_date && $task_start_date_f <= $end_date) ||
                ($task_end_date_f >= $start_date && $task_end_date_f <= $end_date) ||
                ($task_start_date_f <= $start_date && $task_end_date_f >= $start_date && $task_end_date_f >= $end_date)
            ) {
                $spans++;
                $inner_years[] = [$start_date, $end_date];
            }
        }

        if ($spans != 0) {
            $formated_head_start = date('Y', strtotime($task_start_date_f));
            $formated_head_end = date('Y', strtotime($task_end_date_f));
            $spans++;
            $tr .= '<tr ><td rowspan=' . $spans . '>' . $h . '</td><td rowspan=' . $spans . '>' . $formated_head_start . ' / ' . $formated_head_end . '</td></tr>';
            $h++;

            for ($b = 0; $b < count($inner_years); $b++) {
                $start_date = $inner_years[$b][0];
                $end_date = $inner_years[$b][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);

                if (
                    ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
                    ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                    ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
                ) {
                    if (
                        ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                        ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                        ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                    ) {
                        $formated_date_start = date('d M Y', strtotime($start_date));
                        $formated_date_end = date('d M Y', strtotime($end_date));

                        if (count($input_array) == 1) {
                            $tr .=
                                '<tr>
                                            <td> Q' . $counter . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' .  date('d M Y', strtotime($task_end_date)) . ')</td>
                                            <td>
                                               ' . $target . '
                                            </td>
                                            <td>' . $achieved . '</td>
                                        </tr>';
                        } else {
                            if ($start_date <= $task_start_date) {
                                $tr .=
                                    '<tr>
                                            <td> Q' . $counter . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' .  $formated_date_end . ')</td>
                                            <td>
                                               ' . $target . '
                                            </td>
                                            <td>' . $achieved . '</td>
                                        </tr>';
                            } else if ($end_date >= $task_end_date) {
                                $tr .=
                                    '<tr>
                                            <td> Q' . $counter . ' (' . $formated_date_start . ' - ' .  date('d M Y', strtotime($task_end_date)) . ') </td>
                                            <td>
                                               ' . $target . '
                                            </td>
                                            <td>' . $achieved . '</td>
                                        </tr>';
                            } else {
                                $tr .=
                                    '<tr>
                                            <td>Q' . $counter . ' (' . $formated_date_start . ' - ' .  $formated_date_end . ')</td>
                                            <td>
                                               ' . $target . '
                                            </td>
                                            <td>' . $achieved . '</td>
                                        </tr>';
                            }
                        }

                        $hash++;
                    }
                }
                $counter++;
            }
        }
    }



    $table = '
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Year</th>
                <th>Quarter</th>
                <th>Target</th>
                <th>Achieved</th>
            </tr>
        </thead>
        <tbody>
        ' . $tr . '
        </tbody>
    </table>';

    return $table;
}

function get_monthly_table($monthly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $startYears, $subtask_id, $task_start_date, $task_end_date)
{
    $counter = 1;
    $count_months = count($monthly);
    $tr = '';

    $input_array = [];


    for ($i = 0; $i < $count_months; $i++) {
        $start_date = $monthly[$i][0];
        $end_date = $monthly[$i][1];
        if (
            ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
            ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
            ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
        ) {
            if (
                ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
            ) {
                $formated_date_start = date('d M Y', strtotime($start_date));
                $formated_date_end = date('d M Y', strtotime($end_date));

                $input_array[] = [$start_date, $end_date];
            }
        }
    }

    $tr_years = '';
    $h = 1;

    for ($i = 0; $i < count($startYears); $i++) {
        $inner_years = [];

        $spans = 0;
        $task_start_date_f = $startYears[$i][0];
        $task_end_date_f = $startYears[$i][1];
        for ($t = 0; $t < count($input_array); $t++) {
            $start_date = $input_array[$t][0];
            $end_date = $input_array[$t][1];
            if (
                ($task_start_date_f >= $start_date && $task_start_date_f <= $end_date) ||
                ($task_end_date_f >= $start_date && $task_end_date_f <= $end_date) ||
                ($task_start_date_f <= $start_date && $task_end_date_f >= $start_date && $task_end_date_f >= $end_date)
            ) {
                $spans++;
                $inner_years[] = [$start_date, $end_date];
            }
        }

        if ($spans != 0) {
            $formated_head_start = date('Y', strtotime($task_start_date_f));
            $formated_head_end = date('Y', strtotime($task_end_date_f));
            $spans++;
            $tr .= '<tr ><td rowspan=' . $spans . '>' . $h . '</td><td rowspan=' . $spans . '>' . $formated_head_start . ' / ' . $formated_head_end . '</td></tr>';
            $h++;
            for ($b = 0; $b < count($inner_years); $b++) {
                $start_date = $inner_years[$b][0];
                $end_date = $inner_years[$b][1];

                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
                $achieved = get_achieved($site_id, $task_id, $subtask_id, $start_date, $end_date);
                if (
                    ($contractor_start >= $start_date && $contractor_start <= $end_date) ||
                    ($contractor_end >= $start_date && $contractor_end <= $end_date) ||
                    ($contractor_start <= $start_date && $contractor_end >= $start_date && $contractor_end >= $end_date)
                ) {
                    if (
                        ($task_start_date >= $start_date && $task_start_date <= $end_date) ||
                        ($task_end_date >= $start_date && $task_end_date <= $end_date) ||
                        ($task_start_date <= $start_date && $task_end_date >= $start_date && $task_end_date >= $end_date)
                    ) {
                        $formated_date_start = date('d M Y', strtotime($start_date));
                        $formated_date_end = date('d M Y', strtotime($end_date));
                        if (count($input_array) == 1) {
                            $tr .=
                                '<tr>
                                <td> ' . date('M', strtotime($task_start_date)) . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                <td>
                                    ' . $target . '
                                </td>
                                <td>' . $achieved . '</td>
                            </tr>';
                        } else {
                            if ($start_date <= $task_start_date) {
                                $tr .=
                                    '<tr>
                                    <td> ' . date('M', strtotime($task_start_date)) . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                    <td>' . $target . '
                                    </td>
                                    <td>' . $achieved . '</td>
                                </tr>';
                            } else if ($end_date >= $task_end_date) {
                                $tr .=
                                    '<tr>
                                    <td> ' . date('M', strtotime($start_date)) . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                    <td>
                                        
                                       ' . $target . '
                                    </td>
                                    <td>' . $achieved . '</td>
                                </tr>';
                            } else {
                                $tr .=
                                    '<tr>
                                    <td>' . date('M', strtotime($start_date)) . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                    <td>
                                        
                                        ' . $target . '
                                    </td>
                                    <td>' . $achieved . '</td>
                                </tr>';
                            }

                            $counter++;
                        }
                    }
                }
            }
        }
    }

    $table = '
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Year</th>
                <th>Month</th>
                <th>Target</th>
                <th>Achieved</th>
            </tr>
        </thead>
        <tbody>
        ' . $tr . '
        </tbody>
    </table>';

    return $table;
}

function get_weekly_table($project_start_date, $project_end_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $subtask_id, $task_start_date, $task_end_date, $extension_bool, $extension_start)
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
            $table_details = get_table_body($contractor_start, $contractor_end, $task_start_date, $task_end_date, $target, $start_date, $end_date, $counter, $extension_bool, $extension_start);
            $counter = $table_details['counter'];
            $table_body .= $table_details['table_body'];
        }
        $start_year++;
    }

    return $table_body;
}

function get_daily_table($project_start_date, $project_end_date, $site_id, $task_id, $frequency, $output_id, $subtask_id, $task_start_date, $task_end_date)
{
    $hash = 1;
    $tr = '';
    $daily_task_start_date = $task_start_date;
    while ($daily_task_start_date <= $task_end_date) {
        $date = date('Y-m-d', strtotime($daily_task_start_date));
        $date_show = date('d M Y', strtotime($daily_task_start_date));

        $target = get_target($site_id, $task_id, $subtask_id, $daily_task_start_date, $daily_task_start_date, $frequency);
        $achieved = get_achieved($site_id, $task_id, $subtask_id, $daily_task_start_date, $daily_task_start_date);
        $daily_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        $tr .= '<tr>
                <td>' . $hash . '</td>
                <td>' . $date_show . '</td>
                <td>
                    ' . $target . '
                </td>
                <td>' . $achieved . '</td>
            </tr>';
        $hash++;
    }

    $table = '
        <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Day</th>
                <th>Target</th>
                <th>Achieved</th>
            </tr>
        </thead>
        <tbody>
        ' . $tr . '
        </tbody>
    </table>';

    return $table;
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
    $subtask_id = $_GET['subtask_id'];
    $subtask_start_date = $_GET['subtask_start_date'];
    $subtask_end_date = $_GET['subtask_end_date'];
    $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
    $query_rsProjects->execute(array(":projid" => $projid));
    $row_rsProjects = $query_rsProjects->fetch();
    $totalRows_rsProjects = $query_rsProjects->rowCount();
    $frequency = '';

    $table_details = array('head' => '', 'colspan' => '', 'body' => '');
    if ($totalRows_rsProjects > 0) {
        $min_date = $row_rsProjects['projstartdate'];
        $max_date = $row_rsProjects['projenddate'];
        $frequency = $row_rsProjects['activity_monitoring_frequency'];
        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();
        $contractor_start = $end_date = '';
        if ($totalRows_rsTender > 0) {
            $contractor_start = $row_rsTender['startdate'];
            $contractor_end = $row_rsTender['enddate'];
            $date_details = get_duration($min_date, $max_date);
            $details = index($date_details['duration'], $date_details['start_year'], $contractor_start, $contractor_end, $task_id, $site_id);
            $startYears = $details['startYears'];
            $annually = $details['annually'];
            $quarterly = $details['quarterly'];
            $monthly = $details['monthly'];

            $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
            $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
            $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
            $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();

            if ($totalRows_rsTask_Start_Dates > 0) {
                $task_start_date = $row_rsTask_Start_Dates['start_date'];
                $task_end_date = $row_rsTask_Start_Dates['end_date'];
                $frequency = 1;
                if ($frequency == 6) {
                    $table_details = get_annual_table($startYears, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $subtask_id, $task_start_date, $task_end_date);
                } elseif ($frequency == 5) {
                    $table_details = get_semiannual_table($annually, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $startYears, $subtask_id, $task_start_date, $task_end_date);
                } else if ($frequency == 4) {
                    $table_details = get_quarterly_table($quarterly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $startYears, $subtask_id, $task_start_date, $task_end_date);
                } else if ($frequency == 3) {
                    $table_details = get_monthly_table($monthly, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $startYears, $subtask_id, $task_start_date, $task_end_date);
                } else if ($frequency == 2) {
                    $extension_bool = false;
                    $extension_start = '2023-04-11';
                    $table_details = get_weekly_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $subtask_id, $task_start_date, $task_end_date, $extension_bool, $extension_start);
                } else if ($frequency == 1) {
                    $table_details = get_daily_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id, $subtask_id, $task_start_date, $task_end_date);
                    //$table_details  = get_daily_table($min_date, $max_date, $site_id, $task_id, $frequency, $output_id, $contractor_start, $contractor_end, $subtask_id);
                }
            }
        }
    }




    echo json_encode(array("success" => true, 'frequency' => $frequency, 'table' => $table_details, 'task_id' => $task_id, 'site_id' => $site_id));
}
