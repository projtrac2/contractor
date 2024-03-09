<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : header("Location: projects");
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];

$original_projid = $_GET['proj'];
require('includes/head.php');

if ($permission) {
    try {
        $query_rsMyP = $db->prepare("SELECT *, projstartdate AS sdate, projenddate AS edate, projcode FROM tbl_projects WHERE projid = :projid");
        $query_rsMyP->execute(array(":projid" => $projid));
        $row_rsMyP = $query_rsMyP->fetch();
        $count_rsMyP = $query_rsMyP->rowCount();
        if ($row_rsMyP) {
            $projstatusid = $row_rsMyP["projstatus"];
            $projcat = $row_rsMyP["projcategory"];
            $projdesc = $row_rsMyP["projdesc"];
            $projcode = $row_rsMyP["projcode"];
            $projcommunity = explode(",", $row_rsMyP['projcommunity']);
            $projlga = explode(",", $row_rsMyP['projlga']);
        }
        $projname = $row_rsMyP['projname'];

        $level1  = [];
        for ($i = 0; $i < count($projcommunity); $i++) {
            $query_rslga = $db->prepare("SELECT * FROM tbl_state WHERE id ='$projcommunity[$i]' ");
            $query_rslga->execute();
            $row_rslga = $query_rslga->fetch();
            $level1[] = $row_rslga['state'];
        }


        $level2  = [];
        for ($i = 0; $i < count($projlga); $i++) {
            $query_rslga = $db->prepare("SELECT * FROM tbl_state WHERE id ='$projlga[$i]'");
            $query_rslga->execute();
            $row_rslga = $query_rslga->fetch();
            $level2[] = $row_rslga['state'];
        }

        $query_prog = $db->prepare("SELECT progname FROM tbl_programs g inner join tbl_projects p on p.progid=g.progid WHERE p.projid = :projid");
        $query_prog->execute(array(":projid" => $projid));
        $row_prog = $query_prog->fetch();
        $progname = $row_prog["progname"];

        $query_proj_status = $db->prepare("SELECT statusname FROM tbl_status WHERE statusid = :statusid");
        $query_proj_status->execute(array(":statusid" => $projstatusid));
        $row_proj_status = $query_proj_status->fetch();
        if ($row_proj_status) {
            $projstatus = $row_proj_status["statusname"];
        }

        $currentdate = date("Y-m-d");
        $statusdate = date("Y-m-d H:i:s");

        if ($projcat == '2') {
            $query_rsContractDates =  $db->prepare("SELECT startdate, enddate, tenderamount FROM tbl_tenderdetails WHERE projid = :projid");
            $query_rsContractDates->execute(array(":projid" => $projid));
            $row_rsContractDates = $query_rsContractDates->fetch();
            $totalRows_rsContractDates = $query_rsContractDates->rowCount();

            $query_tenderdetails =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid = :projid");
            $query_tenderdetails->execute(array(":projid" => $projid));

            $totalRows_tenderdetails = $query_tenderdetails->rowCount();
            $tenderamount = 0;

            if ($totalRows_tenderdetails > 0) {
                while ($row_tenderdetails = $query_tenderdetails->fetch()) {
                    $unitcost = $row_tenderdetails["unit_cost"];
                    $unitsno = $row_tenderdetails["units_no"];
                    $itemcost = $unitcost * $unitsno;
                    $tenderamount = $tenderamount + $itemcost;
                }
            }

            $othercost = 0;
            $query_other_fin_lines =  $db->prepare("SELECT unit_cost, units_no FROM tbl_project_direct_cost_plan WHERE projid = :projid AND tasks=0");
            $query_other_fin_lines->execute(array(":projid" => $projid));
            while ($row_other_fin_lines = $query_other_fin_lines->fetch()) {
                $unitcost = $row_other_fin_lines["unit_cost"];
                $unitsno = $row_other_fin_lines["units_no"];
                $itemcost = $unitcost * $unitsno;
                $othercost = $othercost + $itemcost;
            }

            $consumed = 0;
            $query_consumed =  $db->prepare("SELECT SUM(amount_requested) AS consumed FROM tbl_payments_request WHERE status=3 AND projid = :projid");
            $query_consumed->execute(array(":projid" => $projid));
            $row_consumed = $query_consumed->fetch();

            if ($row_consumed) {
                $consumed = $row_consumed["consumed"];
            }

            $totalcost = $othercost + $tenderamount;

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
                if ($durationrate > 100) {
                    $durationrate = 100;
                }

                $duration = $duration->format('%a');
                $durationtodate = $durationtodate->format('%a');
                $durationtoenddate = $durationtoenddate->format('%a');

                $pjstdate = date("d M Y", strtotime($row_rsContractDates["startdate"]));
                $pjendate = date("d M Y", strtotime($row_rsContractDates["enddate"]));
            } else {
                $query_proj_tasks_dates =  $db->prepare("SELECT MIN(start_date) AS projstartdate, MAX(end_date) AS projenddate FROM tbl_task t left join tbl_program_of_works w on w.task_id=t.tkid WHERE t.projid = :projid");
                $query_proj_tasks_dates->execute(array(":projid" => $projid));
                $row_proj_tasks_dates = $query_proj_tasks_dates->fetch();

                $date1 = new DateTime($row_proj_tasks_dates["projstartdate"]);
                $date2 = new DateTime($row_proj_tasks_dates["projenddate"]);
                $date3 = new DateTime($currentdate);

                $duration = $date1->diff($date2);
                $durationtodate = $date1->diff($date3);
                $durationtoenddate = $date3->diff($date2);
                $durations = $duration->format('%a');
                $durationtodates = $durationtodate->format('%a');

                $durationrate = $durationtodates > 0  && $durations > 0 ? ($durationtodates / $durations) * 100 : 0;
                if ($durationrate > 100) {
                    $durationrate = 100;
                }

                $duration = $duration->format('%r%a');
                $durationtodate = $durationtodate->format('%r%a');
                $durationtoenddate = $durationtoenddate->format('%r%a');

                $pjstdate = date("d M Y", strtotime($row_proj_tasks_dates["projstartdate"]));
                $pjendate = date("d M Y", strtotime($row_proj_tasks_dates["projenddate"]));
            }

            $rate = $totalcost > 0 ? (($consumed / $totalcost) * 100) : 0;
            $projcost = number_format($totalcost, 2);
            $balance = $totalcost - $consumed;
            $balance = number_format($balance, 2);
            $consumed = number_format($consumed, 2);
        } else {
            $query_proj_tasks_dates =  $db->prepare("SELECT MIN(start_date) AS projstartdate, MAX(end_date) AS projenddate FROM tbl_task t left join tbl_program_of_works w on w.task_id=t.tkid WHERE t.projid = :projid");
            $query_proj_tasks_dates->execute(array(":projid" => $projid));
            $row_proj_tasks_dates = $query_proj_tasks_dates->fetch();

            $date1 = new DateTime($row_proj_tasks_dates["projstartdate"]);
            $date2 = new DateTime($row_proj_tasks_dates["projenddate"]);
            $date3 = new DateTime($currentdate);

            $duration = $date1->diff($date2);
            $durationtodate = $date1->diff($date3);
            $durationtodates = $durationtodate->format('%a');
            $durationtoenddate = $date3->diff($date2);

            $duration = $duration->format('%a');
            $durationtodate = $durationtodate->format('%a');
            $durationtoenddate = $durationtoenddate->format('%a');

            $durationrate = $durationtodates > 0  && $duration > 0 ? ($durationtodates / $duration) * 100 : 0;
            if ($durationrate > 100) {
                $durationrate = 100;
            }

            $pjstdate = date("d M Y", strtotime($row_proj_tasks_dates["projstartdate"]));
            $pjendate = date("d M Y", strtotime($row_proj_tasks_dates["projenddate"]));

            $othercost = 0;
            $query_other_fin_lines =  $db->prepare("SELECT unit_cost, units_no FROM tbl_project_direct_cost_plan WHERE projid = :projid");
            $query_other_fin_lines->execute(array(":projid" => $projid));
            while ($row_other_fin_lines = $query_other_fin_lines->fetch()) {
                $unitcost = $row_other_fin_lines["unit_cost"];
                $unitsno = $row_other_fin_lines["units_no"];
                $itemcost = $unitcost * $unitsno;
                $othercost = $othercost + $itemcost;
            }


            $consumed = 0;
            $query_consumed =  $db->prepare("SELECT SUM(amount_requested) AS consumed FROM tbl_payments_request WHERE status=3 AND projid = :projid");
            $query_consumed->execute(array(":projid" => $projid));
            $row_consumed = $query_consumed->fetch();
            if ($row_consumed) {
                $consumed = $row_consumed["consumed"];
            }

            $rate = $othercost > 0 ? (($consumed / $othercost) * 100) : 0;

            $projcost = number_format($othercost, 2);
            $balance = $othercost - $consumed;
            $balance = number_format($balance, 2);
            $consumed = number_format($consumed, 2);
        }

        if ($durationtoenddate > $duration) {
            $durationtoenddate = 0;
        }
        $percent2 = calculate_project_progress($projid, $projcat);
        $percentage_progress_remaining = 100 - $percent2;
        $percentage_duration_consumed = round($durationrate, 2);
        $percentage_duration_remaining = 100 - $percentage_duration_consumed;
        $rate_balance = 100 - $rate;
        $percent2 = number_format($percent2, 2);
    } catch (PDOException $ex) {
        $result = flashMessage("An error occurred: " . $ex->getMessage());
        echo $result;
    }
?>
    <!-- JQuery Nestable Css -->
    <link href="projtrac-dashboard/plugins/nestable/jquery-nestable.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/strategicplan/view-strategic-plan-framework.css">
    <link rel="stylesheet" href="css/highcharts.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

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
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <h4>
                        <div class="col-lg-10 col-md-10 col-sm-12 col-xs-12" style="font-size:15px; background-color:#CDDC39; border:#CDDC39 thin solid; border-radius:5px; margin-bottom:2px; height:25px; padding-top:2px; vertical-align:center">
                            Project Name: <font color="white"><?php echo $projname; ?></font>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" style="font-size:15px; background-color:#CDDC39; border-radius:5px; height:25px; margin-bottom:2px">
                            <div class="progress" style="height:23px; margin-bottom:1px; margin-top:1px; color:black">
                                <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="<?= $percent2 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent2 ?>%; margin:auto; padding-left: 10px; padding-top: 3px; text-align:left; color:black">
                                    <?= $percent2 ?>%
                                </div>
                            </div>
                        </div>
                    </h4>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row clearfix">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px;">
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item list-group-item-action active">Program Name:<?= $progname ?> </li>
                                    <li class="list-group-item"><strong>Project Code: </strong> <?= $projcode ?> </li>
                                    <li class="list-group-item"><strong>Project Description: </strong> <?= $projdesc ?> </li>
                                    <input type="hidden" name="percent2" id="percent2" value="<?= $percent2 ?>">
                                    <input type="hidden" name="percentage_progress_remaining" id="percentage_progress_remaining" value="<?= $percentage_progress_remaining ?>">
                                    <input type="hidden" name="percentage_duration_consumed" id="percentage_duration_consumed" value="<?= $percentage_duration_consumed ?>">
                                    <input type="hidden" name="percentage_duration_remaining" id="percentage_duration_remaining" value="<?= $percentage_duration_remaining ?>">
                                    <input type="hidden" name="rate" id="rate" value="<?= $rate ?>">
                                    <input type="hidden" name="rate_balance" id="rate_balance" value="<?= $rate_balance ?>">
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
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>

<!-- Jquery Nestable -->
<script src="assets/projtrac-dashboard/plugins/nestable/jquery.nestable.js"></script>
<script src="assets/projtrac-dashboard/js/pages/ui/sortable-nestable.js"></script>