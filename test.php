<?php
function index($prjDuration, $start_year)
{
    $f_start = '07-01';
    $f_end = '06-30';
    $st = $start_year;
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

function get_monthly_table($monthly)
{
    for ($i = 0; $i < count($monthly); $i++) {
        $annual_start = $monthly[$i][0];
        $annual_end = $monthly[$i][1];
        $formated_date_start = date('M Y', strtotime($annual_start));
        $miakaMonthly[] = $annual_start . '/' . $annual_end;
    }
}
