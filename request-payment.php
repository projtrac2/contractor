<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : "";
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];
$original_projid = $_GET['proj'];

require('includes/head.php');
if ($permission) {
    try { 
        $query_rsMyP =  $db->prepare("SELECT *, projcost, projstartdate AS sdate, projenddate AS edate, projcategory, progress FROM tbl_projects WHERE deleted='0' AND projid = '$projid'");
        $query_rsMyP->execute();
        $row_rsMyP = $query_rsMyP->fetch();
        $implementation_type = $row_rsMyP["projcategory"];
        $projname = $row_rsMyP['projname'];
        $projcode = $row_rsMyP['projcode'];
        $projcost = $row_rsMyP['projcost'];
        $projfscyear = $row_rsMyP['projfscyear'];
        $projduration = $row_rsMyP['projduration'];
        $projcat = $row_rsMyP['projcategory'];
        $projstage = $row_rsMyP["projstage"];
        $payment_plan = $row_rsMyP['payment_plan'];
        $percent2 = number_format(calculate_project_progress($projid, $projcat), 2);

        $query_rsOutputs = $db->prepare("SELECT p.output as  output, o.id as opid, p.indicator, o.budget as budget, o.total_target FROM tbl_project_details o INNER JOIN tbl_progdetails p ON p.id = o.outputid WHERE projid = :projid");
        $query_rsOutputs->execute(array(":projid" => $projid));
        $row_rsOutputs = $query_rsOutputs->fetch();
        $totalRows_rsOutputs = $query_rsOutputs->rowCount();

        $query_rsYear =  $db->prepare("SELECT * FROM tbl_fiscal_year where id ='$projfscyear'");
        $query_rsYear->execute();
        $row_rsYear = $query_rsYear->fetch();

        $starting_year = $row_rsYear ? $row_rsYear['yr'] : false;
        $start_date = $starting_year . "-07-01";
        $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $projduration . ' days'));
        if ($projcat == 2) {
            $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
            $query_rsTender->execute(array(":projid" => $projid));
            $row_rsTender = $query_rsTender->fetch();
            $totalRows_rsTender = $query_rsTender->rowCount();
            if ($totalRows_rsTender > 0) {
                $start_date = $row_rsTender['startdate'];
                $end_date = $row_rsTender['enddate'];
            }
        }


        function get_task_compliance($state_id, $site_id, $task_id)
        {
            global $db;
            $compliance = [];
            $query_rsSpecifions = $db->prepare("SELECT * FROM tbl_project_specifications WHERE task_id=:task_id");
            $query_rsSpecifions->execute(array(":task_id" => $task_id));
            $totalRows_rsSpecifions = $query_rsSpecifions->rowCount();
            if ($totalRows_rsSpecifions > 0) {
                while ($row_rsSpecifions = $query_rsSpecifions->fetch()) {
                    $specification_id = $row_rsSpecifions['id'];
                    $query_rsCompliance = $db->prepare("SELECT * FROM tbl_project_inspection_specification_compliance WHERE state_id=:state_id AND site_id=:site_id AND specification_id=:specification_id  ORDER BY id DESC LIMIT 1");
                    $query_rsCompliance->execute(array(":state_id" => $state_id, ":site_id" => $site_id, ":specification_id" => $specification_id));
                    $Rows_rsCompliance = $query_rsCompliance->fetch();
                    $totalRows_rsCompliance = $query_rsCompliance->rowCount();
                    $compliance[] = ($totalRows_rsCompliance > 0) ? $Rows_rsCompliance['compliance'] : 0;
                }
            }

            $task_compliance = "";
            if (in_array(1, $compliance)) {
                $task_compliance = "Compliant";
            } else if (in_array(2, $compliance)) {
                $task_compliance = "Non-Compliant";
            } else if (in_array(2, $compliance)) {
                $task_compliance = "On-Track";
            }
            return $task_compliance;
        }

    } catch (PDOException $ex) {
        $result = flashMessage("An error occurred: " . $ex->getMessage());
        echo $result;
    }
?>
    <style>
        @import "https://code.highcharts.com/dashboards/css/dashboards.css";
    </style>
    <link href="projtrac-dashboard/plugins/nestable/jquery-nestable.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/strategicplan/view-strategic-plan-framework.css">
    <script src="https://code.highcharts.com/gantt/highcharts-gantt.js"></script>
    <script src="https://code.highcharts.com/gantt/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/gantt/modules/pattern-fill.js"></script>
    <script src="https://code.highcharts.com/gantt/modules/accessibility.js"></script>

    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-calendar" aria-hidden="true"></i> Payment Request
                <div class="btn-group" style="float:right; margin-right:10px">
                    <input type="button" VALUE="Go Back" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
                </div>
            </h4>
        </div>
        <div class="row clearfix">
            <div class="block-header">
                <?= $results; ?>
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
                    <div class="row clearfix" style="border:1px solid #f0f0f0; border-radius:3px; margin-left:3px; margin-right:3px">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="margin-top:15px; margin-bottom:15px">
                            <strong>Project Code: </strong> <?= $projcode ?>
                        </div>
                    </div>
                    <div class="body">
                        <?php
                        $query_Sites = $db->prepare("SELECT * FROM tbl_project_sites WHERE projid=:projid");
                        $query_Sites->execute(array(":projid" => $projid));
                        $rows_sites = $query_Sites->rowCount();
                        if ($rows_sites > 0) {
                            $counter = 0;
                            while ($row_Sites = $query_Sites->fetch()) {
                                $site_id = $row_Sites['site_id'];
                                $site = $row_Sites['site'];
                                $counter++;
                        ?>
                                <fieldset class="scheduler-border">
                                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                        SITE <?= $counter ?> : <?= $site ?>
                                    </legend>
                                    <?php
                                    $query_Site_Output = $db->prepare("SELECT * FROM tbl_output_disaggregation  WHERE output_site=:site_id");
                                    $query_Site_Output->execute(array(":site_id" => $site_id));
                                    $rows_Site_Output = $query_Site_Output->rowCount();
                                    if ($rows_Site_Output > 0) {
                                        $output_counter = 0;
                                        while ($row_Site_Output = $query_Site_Output->fetch()) {
                                            $output_counter++;
                                            $output_id = $row_Site_Output['outputid'];
                                            $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE id = :outputid");
                                            $query_Output->execute(array(":outputid" => $output_id));
                                            $row_Output = $query_Output->fetch();
                                            $total_Output = $query_Output->rowCount();
                                            if ($total_Output) {
                                                //$output_id = $row_Output['id'];
                                                $output = $row_Output['indicator_name'];
                                    ?>
                                                <fieldset class="scheduler-border">
                                                    <legend class="scheduler-border" style="background-color:#f0f0f0; border-radius:3px">
                                                        OUTPUT <?= $output_counter ?> : <?= $output ?>
                                                    </legend>
                                                    <?php
                                                    $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id");
                                                    $query_rsMilestone->execute(array(":output_id" => $output_id));
                                                    $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                                    if ($totalRows_rsMilestone > 0) {
                                                        $task_counter = 0;
                                                        while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                            $milestone = $row_rsMilestone['milestone'];
                                                            $msid = $row_rsMilestone['msid'];

                                                            $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id");
                                                            $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => $site_id));
                                                            $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                            $edit = $totalRows_rsTask_Start_Dates > 1 ? 1 : 0;
                                                            $details = array("output_id" => $output_id, "site_id" => $site_id, 'task_id' => $msid, 'edit' => $edit);
                                                            $task_counter++;
                                                    ?>
                                                            <div class="row clearfix">
                                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <div class="card-header">
                                                                        <div class="row clearfix">
                                                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                <h5><u>
                                                                                        TASK <?= $task_counter ?>: <?= $milestone ?>
                                                                                        <div class="btn-group" style="float:right">
                                                                                            <div class="btn-group" style="float:right">
                                                                                                <button type="button" data-toggle="modal" data-target="#outputItemModal" data-backdrop="static" data-keyboard="false" onclick="get_tasks(<?= htmlspecialchars(json_encode($details)) ?>)" class="btn btn-success btn-sm" style="float:right; margin-top:-5px">
                                                                                                    <?php echo $totalRows_rsTask_Start_Dates > 0 ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>' ?>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </u>
                                                                                </h5>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="width:5%">#</th>
                                                                                    <th style="width:40%">Item</th>
                                                                                    <th style="width:10%">Units Planned</th>
                                                                                    <th style="width:15%">Units Requested</th>
                                                                                    <th style="width:15%">Unit Cost (Ksh.)</th>
                                                                                    <th style="width:15%">Subtotal Cost</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php
                                                                                $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid  ORDER BY parenttask");
                                                                                $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $msid));
                                                                                $totalRows_rsTasks = $query_rsTasks->rowCount();
                                                                                if ($totalRows_rsTasks > 0) {
                                                                                    $tcounter = 0;
                                                                                    while ($row_rsTasks = $query_rsTasks->fetch()) {
                                                                                        $tcounter++;
                                                                                        $task_name = $row_rsTasks['task'];
                                                                                        $task_id = $row_rsTasks['tkid'];
                                                                                        $unit =  $row_rsTasks['unit_of_measure'];
                                                                                        $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                                                                                        $query_rsIndUnit->execute(array(":unit_id" => $unit));
                                                                                        $row_rsIndUnit = $query_rsIndUnit->fetch();
                                                                                        $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                                                                                        $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';

                                                                                        $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
                                                                                        $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => $site_id, ":subtask_id" => $task_id));
                                                                                        $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                                                        $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                                        $start_date = $end_date = $duration =  "";
                                                                                        if ($totalRows_rsTask_Start_Dates > 0) {
                                                                                            $start_date = date("d M Y", strtotime($row_rsTask_Start_Dates['start_date']));
                                                                                            $end_date = date("d M Y", strtotime($row_rsTask_Start_Dates['end_date']));
                                                                                            $duration = number_format($row_rsTask_Start_Dates['duration']);
                                                                                        }
                                                                                ?>
                                                                                        <tr id="row">
                                                                                            <td style="width:5%"><?= $task_counter ?>.<?= $tcounter ?></td>
                                                                                            <td style="width:55%"><?= $task_name ?></td>
                                                                                            <td style="width:10%"><?= number_format(0, 2) ?></td>
                                                                                            <td style="width:15%"><?= number_format(0, 2) ?></td>
                                                                                            <td style="width:15%"><?= number_format(0, 2) ?></td>
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
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </fieldset>
                                    <?php
                                            }
                                        }
                                    }
                                    ?>
                                </fieldset>
                                <?php
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

                                while ($row_rsOutput = $query_Output->fetch()) {
                                    $output_id = $row_rsOutput['id'];
                                    $output = $row_rsOutput['indicator_name'];
                                    $counter++;
                                    $site_id = 0;
                                ?>
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                            AWAY POINT OUTPUT <?= $counter ?>: <?= $output ?>
                                        </legend>
                                        <?php
                                        $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ");
                                        $query_rsMilestone->execute(array(":output_id" => $output_id));
                                        $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                        if ($totalRows_rsMilestone > 0) {
                                            $task_counter = 0;
                                            while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                $milestone = $row_rsMilestone['milestone'];
                                                $msid = $row_rsMilestone['msid'];
                                                $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id ");
                                                $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => 0));
                                                $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                $edit = $totalRows_rsTask_Start_Dates > 1 ? 1 : 0;
                                                $details = array("output_id" => $output_id, "site_id" => $site_id, 'task_id' => $msid, 'edit' => $edit);
                                                $task_counter++;
                                        ?>
                                                <div class="row clearfix">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="card-header">
                                                            <div class="row clearfix">
                                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                    <h5><u>
                                                                            TASK <?= $task_counter ?>: <?= $milestone ?>
                                                                            <div class="btn-group" style="float:right">
                                                                                <div class="btn-group" style="float:right">
                                                                                    <button type="button" data-toggle="modal" data-target="#outputItemModal" data-backdrop="static" data-keyboard="false" onclick="get_tasks(<?= htmlspecialchars(json_encode($details)) ?>)" class="btn btn-success btn-sm" style="float:right; margin-top:-5px">
                                                                                        <?php echo $totalRows_rsTask_Start_Dates > 0 ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>' ?>
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </u>
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%">#</th>
                                                                        <th style="width:40%">Item</th>
                                                                        <th style="width:10%">Units Planned</th>
                                                                        <th style="width:15%">Units Requested</th>
                                                                        <th style="width:15%">Unit Cost</th>
                                                                        <th style="width:15%">Subtotal Cost</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid  ORDER BY parenttask");
                                                                    $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $msid));
                                                                    $totalRows_rsTasks = $query_rsTasks->rowCount();
                                                                    if ($totalRows_rsTasks > 0) {
                                                                        $tcounter = 0;
                                                                        while ($row_rsTasks = $query_rsTasks->fetch()) {
                                                                            $tcounter++;
                                                                            $task_name = $row_rsTasks['task'];
                                                                            $task_id = $row_rsTasks['tkid'];
                                                                            $unit =  $row_rsTasks['unit_of_measure'];
                                                                            $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                                                                            $query_rsIndUnit->execute(array(":unit_id" => $unit));
                                                                            $row_rsIndUnit = $query_rsIndUnit->fetch();
                                                                            $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                                                                            $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';

                                                                            $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
                                                                            $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => 0, ":subtask_id" => $task_id));
                                                                            $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                                            $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                            $start_date = $end_date = $duration =  "";
                                                                            if ($totalRows_rsTask_Start_Dates > 0) {
                                                                                $start_date = date("d M Y", strtotime($row_rsTask_Start_Dates['start_date']));
                                                                                $end_date = date("d M Y", strtotime($row_rsTask_Start_Dates['end_date']));
                                                                                $duration = number_format($row_rsTask_Start_Dates['duration']);
                                                                            }
                                                                    ?>
                                                                            <tr id="row">
                                                                                <td style="width:5%"><?= $task_counter ?>.<?= $tcounter ?></td>
                                                                                <td style="width:55%"><?= $task_name ?></td>
                                                                                <td style="width:10%"><?= number_format(0, 2) ?></td>
                                                                                <td style="width:15%"><?= number_format(0, 2) ?></td>
                                                                                <td style="width:15%"><?= number_format(0, 2) ?></td>
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
                                        <?php
                                            }
                                        }
                                        ?>
                                    </fieldset>
                        <?php
                                }
                            }
                        }
                        ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end body  -->

    <!-- add item -->
    <div class="modal fade" id="addFormModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="form-horizontal" id="modal_form_submit" action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-header" style="background-color:#03A9F4">
                        <h4 class="modal-title" style="color:#fff" align="center" id="addModal"><i class="fa fa-plus"></i> <span id="modal_info">Payment Request</span></h4>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="body" id="add_modal_form">
                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-calendar" aria-hidden="true"></i> Request Details
                                            </legend>
                                            <div class="row clearfix" style="margin-top:5px; margin-bottom:5px">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <label for="project_name" class="control-label">Project *:</label>
                                                    <div class="form-line">
                                                        <input type="text" name="project_name" value="" id="project_name" class="form-control" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                    <label for="contractor_number" class="control-label">Contract Number:</label>
                                                    <div class="form-line">
                                                        <input type="text" name="contractor_number" value="" id="contractor_number" class="form-control" readonly>
                                                    </div>
                                                </div>
                                                <div id="milestones">
                                                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                        <label for="payment_phase" class="control-label">Payment Phase:</label>
                                                        <div class="form-line">
                                                            <select name="payment_phase" id="payment_phase" onchange="get_payment_plan_milestones()" class="form-control show-tick" style="border:1px #CCC thin solid; border-radius:5px" data-live-search="false">
                                                                <option value="">.... Select from list ....</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                        <label for="request_percentage" class="control-label">Percentage:</label>
                                                        <div class="form-line">
                                                            <input type="text" name="request_percentage" value="" id="request_percentage" class="form-control" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                        <label for="request_amount" class="control-label">Request Amount:</label>
                                                        <div class="form-line">
                                                            <input type="text" name="amount_request" value="" id="amount_request" class="form-control" readonly>
                                                            <input type="hidden" name="request_amount" value="" id="request_amount" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%"># </th>
                                                                        <th style="width:95%">Milestone</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="milestone_table">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="tasks">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%"># </th>
                                                                        <th style="width:20%">Output</th>
                                                                        <th style="width:20%">Site</th>
                                                                        <th style="width:25%">Subtask</th>
                                                                        <th style="width:10%">Units No.</th>
                                                                        <th style="width:10%">Unit Cost</th>
                                                                        <th style="width:10%">Cost</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="tasks_table">
                                                                    <tr></tr>
                                                                    <tr id="removeTr" class="text-center">
                                                                        <td colspan="5">Add Tasks</td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot id="tasks_foot">
                                                                    <tr>
                                                                        <td colspan="6"><strong>Total</strong></td>
                                                                        <td id="subtotal"></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="work_measured">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width:5%"># </th>
                                                                        <th style="width:25%">Subtask</th>
                                                                        <th style="width:10%">Target Units No.</th>
                                                                        <th style="width:10%">Achieved Units No.</th>
                                                                        <th style="width:10%">Request Units No.</th>
                                                                        <th style="width:10%">Unit Cost</th>
                                                                        <th style="width:10%">Cost</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="work_measured_table">
                                                                    <tr></tr>
                                                                    <tr id="removeTr" class="text-center">
                                                                        <td colspan="5">Add Tasks</td>
                                                                    </tr>
                                                                </tbody>
                                                                <tfoot id="tasks_foot">
                                                                    <tr>
                                                                        <td colspan="6"><strong>Total</strong></td>
                                                                        <td id="subtotal1"></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-comment" aria-hidden="true"></i> Invoice & Remarks
                                            </legend>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="invoice_div">
                                                <label for="invoice" class="control-label">Invoice Attachment:</label>
                                                <div class="form-line">
                                                    <input type="file" name="invoice" value="" id="invoice" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <label class="control-label">Remarks *:</label>
                                                <br>
                                                <div class="form-line">
                                                    <textarea name="comments" cols="" rows="7" class="form-control" id="comment" placeholder="Enter Comments if necessary" style="width:98%; color:#000; font-size:12px; font-family:Verdana, Geneva, sans-serif"></textarea>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- /modal-body -->
                        <div class="modal-footer">
                            <div class="col-md-12 text-center">
                                <input type="hidden" name="projid" id="projid" value="">
                                <input type="hidden" name="payment_plan" id="payment_plan" value="">
                                <input type="hidden" name="requested_amount" id="requested_amount" value="">
                                <input type="hidden" name="complete" id="complete" value="">
                                <input type="hidden" name="user_name" id="username" value="<?= $user_name ?>">
                                <input type="hidden" name="contractor_payment" id="contractor_payment" value="new">
                                <button name="save" type="" class="btn btn-primary waves-effect waves-light" id="modal-form-submit" value="">Save</button>
                                <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal"> Cancel</button>
                            </div>
                        </div> <!-- /modal-footer -->
                </form> <!-- /.form -->
            </div> <!-- /modal-content -->
        </div> <!-- /modal-dailog -->
    </div>
    <!-- End add item -->
<?php
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>