<?php
require('includes/head.php');
$original_projid = $_GET['proj'];
if ($permission && (isset($_GET['proj']) && !empty($_GET["proj"]))) {
    $decode_projid =  base64_decode($_GET['proj']);
    $projid_array = explode("projid54321", $decode_projid);
    $projid = $projid_array[1];
    try {
        $query_rsMyP = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid='$projid'");
        $query_rsMyP->execute();
        $row_rsMyP = $query_rsMyP->fetch();
        $count_rsMyP = $query_rsMyP->rowCount();
        $projcode = $projname = $projcat = $progname = $projdesc = "";
        $projlga = $projcommunity = [];
        $currentdate = date("Y-m-d");

        if ($count_rsMyP > 0) {
            $projcat = $row_rsMyP["projcategory"];
            $projdesc = $row_rsMyP["projdesc"];
            $projcode = $row_rsMyP["projcode"];
            $projcommunity = explode(",", $row_rsMyP['projcommunity']);
            $projlga = explode(",", $row_rsMyP['projlga']);
            $progname = $row_rsMyP["progname"];
            $projname = $row_rsMyP['projname'];
        }

        function get_budget()
        {
            global $db, $projid, $user_name;
            $query_Procurement = $db->prepare("SELECT SUM(unit_cost * units_no) as total_cost FROM tbl_project_tender_details WHERE projid = :projid ");
            $query_Procurement->execute(array(":projid" => $projid));
            $row_rsProcurement = $query_Procurement->fetch();
            $budget = ($row_rsProcurement['total_cost'] != NULL) ? $row_rsProcurement['total_cost'] : 0;

            $query_rsPayement_reuests =  $db->prepare("SELECT SUM(requested_amount) as expense FROM  tbl_contractor_payment_requests WHERE status = 3 AND contractor_id=:contractor_id AND projid=:projid");
            $query_rsPayement_reuests->execute(array(":contractor_id" => $user_name, ":projid" => $projid));
            $rows_rsPayement_reuests = $query_rsPayement_reuests->fetch();
            $expense = !is_null($rows_rsPayement_reuests['expense']) ? $rows_rsPayement_reuests['expense'] : 0;
            $balance = $budget - $expense;
            return array("budget" => $budget, "expense" => $expense, "balance" => $balance);
        }

        function get_state($states)
        {
            global $db;
            $state  = [];
            for ($i = 0; $i < count($states); $i++) {
                $query_rslga = $db->prepare("SELECT * FROM tbl_state WHERE id =:state_id ");
                $query_rslga->execute(array(":state_id" => $states[$i]));
                $row_rslga = $query_rslga->fetch();
                $state[] = $row_rslga['state'];
            }
            return $state;
        }

        function get_duration()
        {
            global $db, $projid, $currentdate;
            $query_rsContractDates =  $db->prepare("SELECT startdate, enddate, tenderamount FROM tbl_tenderdetails WHERE projid = :projid");
            $query_rsContractDates->execute(array(":projid" => $projid));
            $row_rsContractDates = $query_rsContractDates->fetch();
            $totalRows_rsContractDates = $query_rsContractDates->rowCount();
            if ($totalRows_rsContractDates > 0) {
                $date1 = new DateTime($row_rsContractDates["startdate"]);
                $date2 = new DateTime($row_rsContractDates["enddate"]);
                $date3 = new DateTime($currentdate);
                $duration = $date1->diff($date2);
                $durations = $duration->format('%a');
                $durationtodate = $date1->diff($date3);
                $durationtodates = $durationtodate->format('%a');
                $durationtoenddate = $date3->diff($date2);
                $durationrate = $durationtodates > 0  && $durations > 0 ? ($durationtodates / $durations) * 100 : 0;
                $durationrate = ($durationrate > 100) ?  100 : $durationrate;
                $duration = $duration->format('%a');
                $durationtodate = $durationtodate->format('%a');
                $durationtoenddate = $durationtoenddate->format('%a');

                $pjstdate = date("d M Y", strtotime($row_rsContractDates["startdate"]));
                $pjendate = date("d M Y", strtotime($row_rsContractDates["enddate"]));
                $durationtoenddate = ($durationtoenddate > $duration) ? 0 : $durationtoenddate;
            }
            $percentage_duration_consumed = round($durationrate, 2);
            $percentage_duration_remaining = 100 - $percentage_duration_consumed;
            return array("durationtoenddate" => $durationtoenddate, "durationtodate" => $durationtodate, "duration" => $duration, "percentage_duration_consumed" => $percentage_duration_consumed, "percentage_duration_remaining" => $percentage_duration_remaining);
        }


        $level1 = get_state($projcommunity);
        $level2 = get_state($projlga);
        $payment_details =  get_budget();
        $balance = number_format($payment_details['balance'], 2);
        $consumed = $payment_details['expense'];
        $projcost = $payment_details['budget'];
        $rate = $projcost > 0 && $consumed > 0 ? (($consumed / $projcost) * 100) : 0;
        $projcost = number_format($projcost, 2);
        $consumed = number_format($consumed, 2);
        $rate_balance = 100 - $rate;
        $percent2 = calculate_project_progress($projid, $projcat);
        $percentage_progress_remaining = 100 - $percent2;
        $percent2 = number_format($percent2, 2);
        $duration_details = get_duration();
        $durationtoenddate = $duration_details['durationtoenddate'];
        $durationtodate = $duration_details['durationtodate'];
        $duration = $duration_details['duration'];
        $percentage_duration_consumed = $duration_details['percentage_duration_consumed'];
        $percentage_duration_remaining = $duration_details['percentage_duration_remaining'];

?>
        <!-- start body  -->
        <div class="container-fluid">
            <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                <h4 class="contentheader">
                    <i class="fa fa-dashboard" style="color:white"></i> Dashboard
                    <div class="btn-group" style="float:right; margin-right:10px">
                        <input type="button" VALUE="Go Back to Projects Dashboard" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
                    </div>
                </h4>
            </div>
            <div class="row clearfix">
                <div class="block-header">
                    <?= $results; ?>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="header" style="padding-bottom:0px">
                            <div class="button-demo" style="margin-top:-15px">
                                <span class="label bg-black" style="font-size:17px"><img src="images/proj-icon.png" alt="Project Menu" title="Project Menu" style="vertical-align:middle; height:25px" />Menu</span>

                                <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px; padding-left:-5px">Dashboard</a>
                                <a href="project-timeline.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Timelines</a>
                                <a href="project-progress.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Progress</a>
                                <a href="project-team.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Team</a>
                                <a href="project-contract.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Contract</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px;">
                                    <ul class="list-group">
                                        <li class="list-group-item list-group-item list-group-item-action active">Project: <?= $projname ?> </li>
                                        <li class="list-group-item"><strong>Code: </strong> <?= $projcode ?> </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="body">
                            <div class="row clearfix">
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                    <figure class="highcharts-figure">
                                        <div id="highcharts-progress"></div>
                                    </figure>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                    <figure class="highcharts-figure">
                                        <div id="highcharts-time"></div>
                                    </figure>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                    <figure class="highcharts-figure">
                                        <div id="highcharts-funds"></div>
                                    </figure>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                        <li class="list-group-item">
                                            <label>
                                                <strong>PROJECT LOCATION</strong>
                                            </label>
                                            <div>
                                                <strong><?= $level1label ?>:</strong> <?php echo implode(",", $level1); ?>
                                            </div>
                                            <hr style="border-top: 1px dashed red;">
                                            <div>
                                                <strong><?= $level2label ?>:</strong> <?php echo implode(",", $level2); ?>
                                            </div>
                                            <hr style="border-top: 1px dashed red;">
                                        </li>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                        <li class="list-group-item">
                                            <label>
                                                <strong>PROJECT TIMELINES</strong>
                                            </label>
                                            <div>
                                                <strong>Duration Assigned: </strong><?= $duration ?> Days
                                            </div>
                                            <hr style="border-top: 1px dashed red;">
                                            <div>
                                                <strong>Duration Consumed: </strong><?= $durationtodate ?> Days
                                            </div>
                                            <hr style="border-top: 1px dashed red;">
                                            <div>
                                                <strong>Remaining Duration: </strong><?= $durationtoenddate ?> Days
                                            </div>
                                        </li>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                        <li class="list-group-item">
                                            <label>
                                                <strong>PROJECT FUNDS</strong>
                                            </label>
                                            <div>
                                                <strong>Budget Allocated: </strong>Ksh.<?php echo $projcost; ?>
                                            </div>
                                            <hr style="border-top: 1px dashed red;">
                                            <div>
                                                <strong>Budget Consumed: </strong>Ksh.<?php echo $consumed; ?>
                                            </div>
                                            <hr style="border-top: 1px dashed red;">
                                            <div>
                                                <strong>Budget Balance: </strong>Ksh.<?php echo $balance; ?>
                                            </div>
                                        </li>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end body  -->
<?php
    } catch (PDOException $ex) {
        customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
    }
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script>
    Highcharts.chart('highcharts-progress', {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Project % Progress',
            align: 'left'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentage',
            data: [{
                    name: 'Achieved',
                    y: <?= $percent2 ?>,
                    sliced: true,
                    selected: true
                },
                ['Pending', <?= $percentage_progress_remaining ?>]
            ],
            colors: [
                '#db03fc',
                '#03e3fc'
            ]
        }]
    });



    Highcharts.chart('highcharts-time', {
        colors: ['#FF9655', '#FFF263', '#24CBE5', '#64E572', '#50B432', '#ED561B', '#DDDF00', '#6AF9C4'],
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Project % Time Consumed',
            align: 'left'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentage',
            data: [{
                    name: 'Consumed',
                    y: <?= $percentage_duration_consumed ?>,
                    sliced: true,
                    selected: true
                },
                ['Pending', <?= $percentage_duration_remaining ?>]
            ],
            colors: [
                '#50B432',
                '#FFF263'
            ]
        }]
    });


    Highcharts.chart('highcharts-funds', {
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Project % Funds Consumed',
            align: 'left'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Percentage',
            data: [{
                    name: 'Consumed',
                    y: <?= $rate ?>,
                    sliced: true,
                    selected: true
                },
                ['Pending', <?= $rate_balance ?>]
            ],
            colors: [
                '#6AF9C4',
                '#CB2326'
            ]
        }]
    });
</script>