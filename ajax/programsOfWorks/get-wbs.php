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

    function get_annual_table($contractor_start, $contractor_end, $task_start_date, $task_end_date, $startYears, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start)
    {
        $tr = '';
        $counter = 1;
        $hash = 1;
        $input_array = [];

        for ($i = 0; $i < count($startYears); $i++) {
            $start_date = $startYears[$i][0];
            $end_date = $startYears[$i][1];
            $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
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
            $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
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
                            if (count($input_array) == 1) {
                                $tr .=
                                    '<tr>
                                    <td>' . $counter . '</td>
                                    <td> Annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                    <td>
                                        <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                        <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                        <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                    </td>
                                </tr>';
                            } else {
                                if ($start_date <= $task_start_date) {
                                    $tr .=
                                        '<tr>
                                        <td>' . $counter . '</td>
                                        <td> Annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                        <td>
                                            <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                            <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                            <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                        </td>
                                    </tr>';
                                } else if ($end_date >= $task_end_date) {
                                    $tr .=
                                        '<tr>
                                        <td>' . $counter . '</td>
                                        <td> Annual ' . $hash . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                        <td>
                                            <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                            <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                            <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                        </td>
                                    </tr>';
                                } else {
                                    $tr .=
                                        '<tr>
                                        <td>' . $counter . '</td>
                                        <td> Annual ' . $hash . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                        <td>
                                            <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                            <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                            <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                        </td>
                                    </tr>';
                                }
                            }
                            $counter++;
                            $hash++;
                        }
                    } else {
                        if (count($input_array) == 1) {
                            $tr .=
                                '<tr>
                                <td>' . $counter . '</td>
                                <td> Annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                <td>
                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                </td>
                            </tr>';
                        } else {
                            if ($start_date <= $task_start_date) {
                                $tr .=
                                    '<tr>
                                    <td>' . $counter . '</td>
                                    <td> Annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                    <td>
                                        <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                        <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                        <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                    </td>
                                </tr>';
                            } else if ($end_date >= $task_end_date) {
                                $tr .=
                                    '<tr>
                                    <td>' . $counter . '</td>
                                    <td> Annual ' . $hash . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                    <td>
                                        <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                        <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                        <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                    </td>
                                </tr>';
                            } else {
                                $tr .=
                                    '<tr>
                                    <td>' . $counter . '</td>
                                    <td> Annual ' . $hash . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                    <td>
                                        <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                        <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                        <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                    </td>
                                </tr>';
                            }
                        }

                        $hash++;

                        $counter++;
                    }
                }
            }
        }
        return $tr;
    }

    function get_semiannual_table($startYears, $contractor_start, $contractor_end, $task_start_date, $task_end_date, $annually, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start)
    {
        $counter = 1;
        $hash = 1;
        $tr = '';
        $input_array = [];

        for ($i = 0; $i < count($annually); $i++) {
            for ($t = 0; $t < count($annually[$i]); $t++) {
                $start_date = $annually[$i][$t][0];
                $end_date = $annually[$i][$t][1];
                $target = get_target($site_id, $task_id, $subtask_id, $start_date, $end_date, $frequency);
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
                                    if (count($input_array) == 1) {
                                        $tr .=
                                            '<tr>
                                                <td> Semi annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                    } else {
                                        if ($start_date <= $task_start_date) {
                                            $tr .=
                                                '<tr>
                                                <td> Semi annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        } else if ($end_date >= $task_end_date) {
                                            $tr .=
                                                '<tr>
                                                <td> Semi annual ' . $hash . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        } else {
                                            $tr .=
                                                '<tr>
                                                <td> Semi annual ' . $hash . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        }
                                    }

                                    $counter++;
                                }
                            } else {
                                if (count($input_array) == 1) {
                                    $tr .=
                                        '<tr>
                                            <td> Semi annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                } else {
                                    if ($start_date <= $task_start_date) {
                                        $tr .=
                                            '<tr>
                                            <td> Semi annual ' . $hash . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else if ($end_date >= $task_end_date) {
                                        $tr .=
                                            '<tr>
                                            <td> Semi annual ' . $hash . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else {
                                        $tr .=
                                            '<tr>
                                            <td> Semi annual ' . $hash . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    }
                                }

                                $counter++;
                            }
                        }
                    }
                    $hash++;
                }
            }
        }

        return $tr;
    }

    function get_quarterly_table($startYears, $contractor_start, $contractor_end, $task_start_date, $task_end_date, $quarterly, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start)
    {
        $table_body = '';
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
                                    if (count($input_array) == 1) {
                                        $tr .=
                                            '<tr>
                                                <td> Q' . $counter . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' .  date('d M Y', strtotime($task_end_date)) . ')</td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                    } else {
                                        if ($start_date <= $task_start_date) {
                                            $tr .=
                                                '<tr>
                                                <td> Q' . $counter . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' .  date('d M Y', strtotime($task_end_date)) . ')</td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        } else if ($end_date >= $task_end_date) {
                                            $tr .=
                                                '<tr>
                                                <td> Q' . $counter . ' (' . $formated_date_start . ' - ' .  date('d M Y', strtotime($task_end_date)) . ') </td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        } else {
                                            $tr .=
                                                '<tr>
                                                <td>Q' . $counter . ' (' . $formated_date_start . ' - ' .  $formated_date_end . ')</td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        }
                                    }
                                    $hash++;
                                }
                            } else {
                                if (count($input_array) == 1) {
                                    $tr .=
                                        '<tr>
                                            <td> Q' . $counter . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' .  date('d M Y', strtotime($task_end_date)) . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                } else {
                                    if ($start_date <= $task_start_date) {
                                        $tr .=
                                            '<tr>
                                            <td> Q' . $counter . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' .  $formated_date_end . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else if ($end_date >= $task_end_date) {
                                        $tr .=
                                            '<tr>
                                            <td> Q' . $counter . ' (' . $formated_date_start . ' - ' .  date('d M Y', strtotime($task_end_date)) . ') </td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else {
                                        $tr .=
                                            '<tr>
                                            <td>Q' . $counter . ' (' . $formated_date_start . ' - ' .  $formated_date_end . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $hash . '" onchange="calculate_total(' . $hash . ')" onkeyup="calculate_total(' . $hash . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    }
                                }

                                $hash++;
                            }
                        }
                    }
                    $counter++;
                }
            }
        }



        return $tr;
    }

    function get_monthly_table($startYears, $contractor_start, $contractor_end, $task_start_date, $task_end_date, $monthly, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start)
    {
        $table_body = '';
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
                            //2024-06-30
                            if ($extension_bool) {
                                if ($start_date >= $extension_start) {
                                    if (count($input_array) == 1) {
                                        $tr .=
                                            '<tr>
                                            <td> ' . date('M', strtotime($task_start_date)) . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else {
                                        if ($start_date <= $task_start_date) {
                                            $tr .=
                                                '<tr>
                                                <td> ' . date('M', strtotime($task_start_date)) . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        } else if ($end_date >= $task_end_date) {
                                            $tr .=
                                                '<tr>
                                                <td> ' . date('M', strtotime($start_date)) . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        } else {
                                            $tr .=
                                                '<tr>
                                                <td>' . date('M', strtotime($start_date)) . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                                <td>
                                                    <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                    <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                    <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                                </td>
                                            </tr>';
                                        }
                                    }

                                    $counter++;
                                }
                            } else {
                                if (count($input_array) == 1) {
                                    $tr .=
                                        '<tr>
                                        <td> ' . date('M', strtotime($task_start_date)) . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . date('d M Y', strtotime($task_end_date)) . ')</td>
                                        <td>
                                            <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                            <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                            <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                        </td>
                                    </tr>';
                                } else {
                                    if ($start_date <= $task_start_date) {
                                        $tr .=
                                            '<tr>
                                            <td> ' . date('M', strtotime($task_start_date)) . ' (' . date('d M Y', strtotime($task_start_date)) . ' - ' . $formated_date_end . ')</td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else if ($end_date >= $task_end_date) {
                                        $tr .=
                                            '<tr>
                                            <td> ' . date('M', strtotime($start_date)) . ' (' . $formated_date_start . ' - ' . date('d M Y', strtotime($task_end_date)) . ') </td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    } else {
                                        $tr .=
                                            '<tr>
                                            <td>' . date('M', strtotime($start_date)) . ' (' . $formated_date_start . ' - ' . $formated_date_end . ') </td>
                                            <td>
                                                <input type="hidden" value="' . $start_date . '" id="start_date" name="start_date[]" />
                                                <input type="hidden" value="' . $end_date . '" id="end_date" name="end_date[]" />
                                                <input type="number" value="' . $target . '" class="form-control target_breakdown  targets" placeholder="Enter Target" name="target[]" id="direct_cost_id' . $counter . '" onchange="calculate_total(' . $counter . ')" onkeyup="calculate_total(' . $counter . ')" min="0" step="0.01" required/>
                                            </td>
                                        </tr>';
                                    }

                                    $counter++;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $tr;
    }

    function get_weekly_table($contractor_start, $contractor_end, $project_start_date, $project_end_date, $task_start_date, $task_end_date, $site_id, $task_id, $subtask_id, $frequency, $extension_bool, $extension_start)
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



    function get_daily_table($contractor_start, $contractor_end, $task_start_date, $task_end_date, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start)
    {
        $hash = 1;
        $tr = '';
        $daily_task_start_date = $task_start_date;
        while ($daily_task_start_date <= $task_end_date) {
            $date = date('Y-m-d', strtotime($daily_task_start_date));
            $date_show = date('d M Y', strtotime($daily_task_start_date));

            $target = get_target($site_id, $task_id, $subtask_id, $daily_task_start_date, $daily_task_start_date, $frequency);
            if ($extension_bool) {
                $daily_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
                if ($daily_task_start_date >= $extension_start) {
                    $tr .= '<tr>
                        <td>' . $hash . ' </td>
                        <td>' . $date_show . '</td>
                        <td>
                            <input type="number" placeholder="Enter Target" value="' . $target . '" class="form-control yearly-target" name="yearly-target[]"  min="0" step="0.01"/>
                            <input type="hidden" value="' . $date . '" class="year" name="year[]" />
                        </td>
                    </tr>';
                    $hash++;
                }
            } else {

                $daily_task_start_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
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
        }
        return $tr;
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
        $projid = $_GET['projid'];
        $task_id = $_GET['task_id'];
        $subtask_id = $_GET['subtask_id'];
        $output_id = $_GET['output_id'];
        $frequency = $_GET['frequency'];
        $structure = $task_start_date = $task_end_date = $duration = $title = '';
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();
        if ($totalRows_rsProjects > 0) {
            $query_rsWorkBreakdown = $db->prepare("SELECT MIN(start_date) AS startdate,MAX(end_date) AS enddate FROM tbl_program_of_works WHERE projid=:projid");
            $query_rsWorkBreakdown->execute(array(':projid' => $projid));
            $row_rsWorkBreakdown = $query_rsWorkBreakdown->fetch();

            $min_date = $row_rsWorkBreakdown['startdate'];
            $max_date = $row_rsWorkBreakdown['enddate'];

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

                $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
                $query_rsTask_Start_Dates->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
                $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                if ($totalRows_rsTask_Start_Dates > 0) {
                    $task_start_date = $row_rsTask_Start_Dates['start_date'];
                    $task_end_date = $row_rsTask_Start_Dates['end_date'];
                    $duration = $row_rsTask_Start_Dates['duration'];
                    $extension_bool = false;
                    if ($frequency == 6) { // yearly
                        $extension_start = date('Y');
                        $structure = get_annual_table($contractor_start, $contractor_end, $task_start_date, $task_end_date, $startYears, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start);
                        $title = 'Year';
                    } else if ($frequency == 5) { // semi annual
                        $extension_start = date('Y');
                        $structure = get_semiannual_table($startYears, $contractor_start, $contractor_end, $task_start_date, $task_end_date, $annually, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start);
                        $title = 'Semi Annual';
                    } else if ($frequency == 4) { // quarterly
                        $extension_start = date('Y');
                        $structure = get_quarterly_table($startYears, $contractor_start, $contractor_end, $task_start_date, $task_end_date, $quarterly, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start);
                        $title = 'Quarter';
                    } else if ($frequency == 3) { // monthly
                        $extension_start = date('Y-m');
                        $structure = get_monthly_table($startYears, $contractor_start, $contractor_end, $task_start_date, $task_end_date, $monthly, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start);
                        $title = 'Month';
                    } else if ($frequency == 2) { // weekly
                        $extension_start = date('Y-m-d');
                        $structure =  get_weekly_table($contractor_start, $contractor_end, $min_date, $max_date, $task_start_date, $task_end_date, $site_id, $task_id, $subtask_id, $frequency, $extension_bool, $extension_start);
                        $title = 'Week';
                    } else if ($frequency == 1) { // daily
                        $extension_start = date('Y-m-d');
                        $structure = get_daily_table($contractor_start, $contractor_end, $task_start_date, $task_end_date, $subtask_id, $site_id, $task_id, $frequency, $extension_bool, $extension_start);
                        $title = 'Day';
                    }
                }
            }
        }

        $query_rsTask = $db->prepare("SELECT t.task, c.units_no, m.unit FROM tbl_task t INNER JOIN tbl_project_direct_cost_plan c ON t.tkid=c.subtask_id INNER JOIN tbl_measurement_units m ON m.id=t.unit_of_measure WHERE t.msid=:task_id AND c.site_id=:site_id AND t.tkid=:subtask_id ");
        $query_rsTask->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
        $row_rsTask = $query_rsTask->fetch();
        if ($frequency == 1 || $frequency == 6) {
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
        } else {

            $table = '
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Year</th>
                        <th>' . $title . '</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody>
                ' . $structure . '
                </tbody>
            </table>';
        }

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
            // avoid deleting find the record and update and if there is no record insert
            // this is due to the recent changes
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
