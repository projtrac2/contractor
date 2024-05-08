<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : "";
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];
$original_projid = $_GET['proj'];

require('includes/head.php');
if ($permission) {
    try {
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects WHERE deleted='0' and projid=:projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();

        $projname = $row_rsProjects['projname'];
        $projcode = $row_rsProjects['projcode'];
        $projfscyear = $row_rsProjects['projfscyear'];
        $projduration = $row_rsProjects['projduration'];
        $projcat = $row_rsProjects['projcategory'];
        $projstage = $row_rsProjects["projstage"];
        $percent2 = number_format(calculate_project_progress($projid, $projcat), 2);

        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();
        $start_date = $end_date = '';
        if ($totalRows_rsTender > 0) {
            $start_date = $row_rsTender['startdate'];
            $end_date = $row_rsTender['enddate'];
        }

        function get_achieved($site_id, $output_id, $task_id)
        {
            global $db;
            $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND output_id=:output_id AND subtask_id=:subtask_id");
            $query_Site_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id, ":subtask_id" => $task_id));
            $rows_site_score = $query_Site_score->rowCount();
            $row_site_score = $query_Site_score->fetch();
            return ($row_site_score['achieved'] != null) ? $row_site_score['achieved'] : 0;
        }

        function get_measurement_unit($unit_id)
        {
            global $db;
            $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
            $query_rsIndUnit->execute(array(":unit_id" => $unit_id));
            $row_rsIndUnit = $query_rsIndUnit->fetch();
            $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
            return  $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
        }

?>
        <div class="container-fluid">
            <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                <h4 class="contentheader">
                    <i class="fa fa-users" style="color:white"></i> Progress
                    <div class="btn-group" style="float:right; margin-right:10px">
                        <input type="button" VALUE="Go Back" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
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
                                <a href="project-dashboard.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; padding-left:-5px">Dashboard</a>
                                <a href="project-timeline.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Timelines</a>
                                <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px; margin-left:-9px">Progress</a>
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
                                        <li class="list-group-item list-group-item list-group-item-action active"> Project: <?= $projname ?> </li>
                                        <li class="list-group-item">
                                            <strong> Code: </strong> <?= $projcode ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <strong> Start Date: </strong> <?= date('d M Y', strtotime($start_date)); ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <strong> End Date: </strong><?= date('d M Y', strtotime($end_date)); ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="header">
                            <h2>
                                USE BELOW SELECTION TO FILTER THE RECORDS BY DATE RANGE
                            </h2>
                            <div class="row clearfix">
                                <form id="searchform" name="searchform" method="get" style="margin-top:10px" action="">
                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                        <label class="control-label">From *:</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" onchange="get_records(<?= $projid ?>)">
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                        <label class="control-label">To *:</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" onchange="get_records(<?= $projid ?>)">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="body">
                            <div class="row clearfix" id="filter_data">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <?php
                                    $query_Sites = $db->prepare("SELECT * FROM tbl_project_sites WHERE projid=:projid");
                                    $query_Sites->execute(array(":projid" => $projid));
                                    $rows_sites = $query_Sites->rowCount();
                                    if ($rows_sites > 0) {
                                        $counter = 0;
                                        while ($row_Sites = $query_Sites->fetch()) {
                                            $site_id = $row_Sites['site_id'];
                                            $site = $row_Sites['site'];
                                            $query_Site_score = $db->prepare("SELECT * FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id");
                                            $query_Site_score->execute(array(":site_id" => $site_id));
                                            $rows_site_score = $query_Site_score->rowCount();
                                            if ($rows_site_score > 0) {
                                                $counter++;
                                    ?>
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                        <i class="fa fa-list-ol" aria-hidden="true"></i> Site <?= $counter ?> :
                                                    </legend>
                                                    <div class="card-header">
                                                        <div class="row clearfix">
                                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                <ul class="list-group">
                                                                    <li class="list-group-item list-group-item list-group-item-action active">Site : <?= $site ?></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    $query_Site_Output = $db->prepare("SELECT * FROM tbl_output_disaggregation  WHERE output_site=:site_id");
                                                    $query_Site_Output->execute(array(":site_id" => $site_id));
                                                    $rows_Site_Output = $query_Site_Output->rowCount();
                                                    if ($rows_Site_Output > 0) {
                                                        $output_counter = 0;
                                                        while ($row_Site_Output = $query_Site_Output->fetch()) {
                                                            $output_counter++;
                                                            $output_id = $row_Site_Output['outputid'];
                                                            $query_output_score = $db->prepare("SELECT * FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND output_id=:output_id");
                                                            $query_output_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
                                                            $rows_output_score = $query_output_score->rowCount();
                                                            if ($rows_output_score > 0) {
                                                                $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE id = :outputid");
                                                                $query_Output->execute(array(":outputid" => $output_id));
                                                                $row_Output = $query_Output->fetch();
                                                                $total_Output = $query_Output->rowCount();
                                                                if ($total_Output) {
                                                                    $output_id = $row_Output['id'];
                                                                    $output = $row_Output['indicator_name'];
                                                                    $query_rsTargetUsed = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_monitoringoutput WHERE output_id=:output_id");
                                                                    $query_rsTargetUsed->execute(array(":output_id" => $output_id));
                                                                    $Rows_rsTargetUsed = $query_rsTargetUsed->fetch();
                                                                    $output_achieved = $Rows_rsTargetUsed['achieved'] != null ? $Rows_rsTargetUsed['achieved'] : 0;
                                                    ?>
                                                                    <fieldset class="scheduler-border">
                                                                        <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                                            <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $counter ?> :
                                                                        </legend>
                                                                        <div class="row clearfix">
                                                                            <div class="card-header">
                                                                                <div class="row clearfix">
                                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                        <ul class="list-group">
                                                                                            <li class="list-group-item list-group-item list-group-item-action active">Output : <?= $output ?></li>
                                                                                            <li class="list-group-item">Achieved : <?= number_format($output_achieved, 2) ?></li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                <div class="table-responsive">
                                                                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th style="width:5%">#</th>
                                                                                                <th style="width:30%">Item</th>
                                                                                                <th style="width:15%">Target</th>
                                                                                                <th style="width:20%">Achieved</th>
                                                                                                <th style="width:10%">Ksh.&nbsp;&nbsp;Unit Price</th>
                                                                                                <th style="width:15%">Ksh.&nbsp;&nbsp;Cost</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php
                                                                                            $query_rsOther_cost_plan_budget =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE site_id=:site_id AND outputid=:output_id AND cost_type=1");
                                                                                            $query_rsOther_cost_plan_budget->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
                                                                                            $rows_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->rowCount();

                                                                                            if ($rows_rsOther_cost_plan_budget > 0) {
                                                                                                $tcounter = 0;
                                                                                                while ($row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch()) {
                                                                                                    $subtask_id = $row_rsOther_cost_plan_budget['subtask_id'];
                                                                                                    $units_no = $row_rsOther_cost_plan_budget['units_no'];
                                                                                                    $unit_cost = $row_rsOther_cost_plan_budget['unit_cost'];
                                                                                                    $unit_id = $row_rsOther_cost_plan_budget['unit'];
                                                                                                    $description = $row_rsOther_cost_plan_budget['description'];
                                                                                                    $achieved = get_achieved($site_id, $output_id, $subtask_id);
                                                                                                    $measurement_unit =  get_measurement_unit($unit_id);
                                                                                                    $cost = $achieved * $unit_cost;
                                                                                                    $tcounter++;
                                                                                            ?>
                                                                                                    <tr id="row<?= $tcounter ?>">
                                                                                                        <td style="width:5%"><?= $tcounter ?></td>
                                                                                                        <td style="width:35%"><?= $description ?></td>
                                                                                                        <td style="width:35%"><?= $measurement_unit ?></td>
                                                                                                        <td style="width:20%"><?= number_format($units_no, 2)  ?></td>
                                                                                                        <td style="width:10%"><?= number_format($achieved, 2) ?></td>
                                                                                                        <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                                        <td style="width:10%"><?= number_format($cost, 2) ?></td>
                                                                                                    </tr>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </fieldset>
                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </fieldset>
                                                <?php
                                            }
                                        }
                                    }

                                    $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE indicator_mapping_type<>1 AND projid = :projid");
                                    $query_Output->execute(array(":projid" => $projid));
                                    $total_Output = $query_Output->rowCount();
                                    $outputs = '';
                                    if ($total_Output > 0) {
                                        $outputs = '';
                                        if ($total_Output > 0) {
                                            $counter = 0;
                                            $site_id = 0;
                                            while ($row_rsOutput = $query_Output->fetch()) {
                                                $output_id = $row_rsOutput['id'];
                                                $output = $row_rsOutput['indicator_name'];
                                                $query_output_score = $db->prepare("SELECT * FROM tbl_project_monitoring_checklist_score WHERE site_id=:site_id AND output_id=:output_id");
                                                $query_output_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
                                                $rows_output_score = $query_output_score->rowCount();
                                                if ($rows_output_score > 0) {
                                                    $counter++;
                                                    $query_rsTargetUsed = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_monitoringoutput WHERE output_id=:output_id");
                                                    $query_rsTargetUsed->execute(array(":output_id" => $output_id));
                                                    $Rows_rsTargetUsed = $query_rsTargetUsed->fetch();
                                                    $output_achieved = $Rows_rsTargetUsed['achieved'] != null ? $Rows_rsTargetUsed['achieved'] : 0;
                                                ?>
                                                    <fieldset class="scheduler-border">
                                                        <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                            <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $counter ?> : <?= $output ?>
                                                        </legend>
                                                        <div class="card-header">
                                                            <div class="row clearfix">
                                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <ul class="list-group">
                                                                        <li class="list-group-item list-group-item list-group-item-action active">Output : <?= $output ?></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%">#</th>
                                                                        <th style="width:30%">Item</th>
                                                                        <th style="width:30%">Unit of Measure </th>
                                                                        <th style="width:15%">Target</th>
                                                                        <th style="width:20%">Achieved</th>
                                                                        <th style="width:10%">Ksh.&nbsp;&nbsp;Unit Price</th>
                                                                        <th style="width:15%">Ksh.&nbsp;&nbsp;Cost</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $query_rsOther_cost_plan_budget =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE site_id=:site_id AND outputid=:output_id AND cost_type=1");
                                                                    $query_rsOther_cost_plan_budget->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
                                                                    $rows_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->rowCount();
                                                                    if ($rows_rsOther_cost_plan_budget > 0) {
                                                                        $tcounter = 0;
                                                                        while ($row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch()) {
                                                                            $subtask_id = $row_rsOther_cost_plan_budget['subtask_id'];
                                                                            $units_no = $row_rsOther_cost_plan_budget['units_no'];
                                                                            $unit_cost = $row_rsOther_cost_plan_budget['unit_cost'];
                                                                            $unit_id = $row_rsOther_cost_plan_budget['unit'];
                                                                            $description = $row_rsOther_cost_plan_budget['description'];
                                                                            $achieved = get_achieved($site_id, $output_id, $subtask_id);
                                                                            $measurement_unit =  get_measurement_unit($unit_id);
                                                                            $cost = $achieved * $unit_cost;
                                                                            $tcounter++;
                                                                    ?>
                                                                            <tr id="row<?= $tcounter ?>">
                                                                                <td style="width:5%"><?= $tcounter ?></td>
                                                                                <td style="width:35%"><?= $description ?></td>
                                                                                <td style="width:35%"><?= $measurement_unit ?></td>
                                                                                <td style="width:20%"><?= number_format($units_no, 2)  ?></td>
                                                                                <td style="width:10%"><?= number_format($achieved, 2) ?></td>
                                                                                <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                <td style="width:10%"><?= number_format($cost, 2) ?></td>
                                                                            </tr>
                                                                    <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </fieldset>
                                    <?php
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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