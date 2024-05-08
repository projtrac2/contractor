<?php
require('includes/head.php');
if ($permission && isset($_GET['projid']) && !empty($_GET['projid'])) {
    try {
        $encoded_projid = $_GET['projid'];
        $decode_projid = base64_decode($encoded_projid);
        $projid_array = explode("projid54321", $decode_projid);
        $projid = $projid_array[1];
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid AND p.projstage=17 AND proj_substage < 4");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();
        $approve_details = "";

        if ($totalRows_rsProjects > 0) {
            $projname = $row_rsProjects['projname'];
            $projcode = $row_rsProjects['projcode'];
            $progid = $row_rsProjects['progid'];
            $projcategory = $row_rsProjects['projcategory'];
            $project_sub_stage = $row_rsProjects['proj_substage'];
            $workflow_stage = $row_rsProjects['projstage'];

            $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
            $query_rsTender->execute(array(":projid" => $projid));
            $row_rsTender = $query_rsTender->fetch();
            $totalRows_rsTender = $query_rsTender->rowCount();
            if ($totalRows_rsTender > 0) {
                $start_date = $row_rsTender['startdate'];
                $end_date = $row_rsTender['enddate'];
?>
                <!-- start body  -->
                <div class="container-fluid">
                    <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                        <h4 class="contentheader">
                            <i class="fa fa-columns" style="color:white"></i> Program of Works
                            <div class="btn-group" style="float:right">
                                <div class="btn-group" style="float:right">
                                    <a type="button" id="outputItemModalBtnrow" onclick="history.back()" class="btn btn-warning pull-right">
                                        Go Back
                                    </a>
                                </div>
                            </div>
                        </h4>
                    </div>
                    <div class="card">
                        <div class="row clearfix">
                            <div class="block-header">
                                <?= $results; ?>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="card-header">
                                    <div class="row clearfix">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <ul class="list-group">
                                                <li class="list-group-item list-group-item list-group-item-action active">Project Name: <?= $projname ?> </li>
                                                <li class="list-group-item"> </li>
                                                <li class="list-group-item"><strong>Project Code: </strong> <?= $projcode ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Contract Start Date: </strong> <?= date('d M Y', strtotime($start_date)); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Contract End Date: </strong> <?= date('d M Y', strtotime($end_date)); ?></li>
                                                <input type="hidden" name="project_start_date" id="project_start_date" value="<?= $start_date ?>">
                                                <input type="hidden" name="project_end_date" id="project_end_date" value="<?= $end_date ?>">
                                            </ul>
                                        </div>
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
                                                            $output_id = $row_Output['id'];
                                                            $output = $row_Output['indicator_name'];
                                                ?>
                                                            <fieldset class="scheduler-border">
                                                                <legend class="scheduler-border" style="background-color:#f0f0f0; border-radius:3px">
                                                                    OUTPUT <?= $output_counter ?> : <?= $output ?>
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
                                                                                            <h5>
                                                                                                <ul>
                                                                                                    TASK <?= $task_counter ?>: <?= $milestone ?> <?php var_dump($Rows_rsTask_Start_Dates) ?>
                                                                                                    <div class="btn-group" style="float:right">
                                                                                                        <div class="btn-group" style="float:right">
                                                                                                            <button type="button" data-toggle="modal" data-target="#outputItemModal" data-backdrop="static" data-keyboard="false" onclick="get_tasks(<?= htmlspecialchars(json_encode($details)) ?>)" class="btn btn-success btn-sm" style="float:right; margin-top:-5px">
                                                                                                                <?php echo $totalRows_rsTask_Start_Dates > 0 ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>' ?>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </ul>
                                                                                            </h5>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="table-responsive">
                                                                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th style="width:5%">#</th>
                                                                                                <th style="width:40%">Subtask</th>
                                                                                                <th style="width:15%">Unit of Measure</th>
                                                                                                <th style="width:10%">Duration</th>
                                                                                                <th style="width:15%">Start Date</th>
                                                                                                <th style="width:15%">End Date</th>
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
                                                                                                    $subtask_frequency = $row_rsTasks['frequency_id'];
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
                                                                                                        <td style="width:5%"><?= $tcounter ?></td>
                                                                                                        <td style="width:40%"><?= $task_name ?></td>
                                                                                                        <td style="width:15%"><?= $unit_of_measure ?></td>
                                                                                                        <td style="width:10%"><?= $duration ?> Days</td>
                                                                                                        <td style="width:15%"><?= $start_date ?></td>
                                                                                                        <td style="width:15%"><?= $end_date ?></td>
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
                                                    OUTPUT <?= $counter ?>: <?= $output ?>
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
                                                                            <h5>
                                                                                <ul>
                                                                                    TASK <?= $task_counter ?>: <?= $milestone ?>
                                                                                    <div class="btn-group" style="float:right">
                                                                                        <div class="btn-group" style="float:right">
                                                                                            <button type="button" data-toggle="modal" data-target="#outputItemModal" data-backdrop="static" data-keyboard="false" onclick="get_tasks(<?= htmlspecialchars(json_encode($details)) ?>)" class="btn btn-success btn-sm" style="float:right; margin-top:-5px">
                                                                                                <?php echo $totalRows_rsTask_Start_Dates > 0 ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>' ?>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </ul>
                                                                            </h5>
                                                                        </div>
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="width:5%">#</th>
                                                                                    <th style="width:40%">Item</th>
                                                                                    <th style="width:15%">Unit of Measure</th>
                                                                                    <th style="width:10%">Duration</th>
                                                                                    <th style="width:15%">Start Date</th>
                                                                                    <th style="width:15%">End Date</th>
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
                                                                                        $subtask_frequency = $row_rsTasks['frequency_id'];
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
                                                                                        <tr id="row<?= $tcounter ?>">
                                                                                            <td style="width:5%"><?= $tcounter ?></td>
                                                                                            <td style="width:40%"><?= $task_name ?></td>
                                                                                            <td style="width:15%"><?= $unit_of_measure ?></td>
                                                                                            <td style="width:10%"><?= $duration ?> Days</td>
                                                                                            <td style="width:15%"><?= $start_date ?> </td>
                                                                                            <td style="width:15%"><?= $end_date ?></td>
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
                                                        </div>
                                            <?php
                                                    }
                                                }
                                            }
                                            ?>
                                            </fieldset>
                                        <?php
                                    }
                                        ?>

                                        <form role="form" id="form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
                                            <?= csrf_token_html(); ?>
                                            <div class="row clearfix" style="margin-top:5px; margin-bottom:5px">
                                                <div class="col-md-12 text-center">
                                                    <?php
                                                    function validate()
                                                    {
                                                        global $db, $projid;
                                                        $proceed = [];
                                                        $query_Sites = $db->prepare("SELECT * FROM tbl_project_sites WHERE projid=:projid");
                                                        $query_Sites->execute(array(":projid" => $projid));
                                                        $rows_sites = $query_Sites->rowCount();
                                                        if ($rows_sites > 0) {
                                                            while ($row_Sites = $query_Sites->fetch()) {
                                                                $site_id = $row_Sites['site_id'];
                                                                $query_Site_Output = $db->prepare("SELECT * FROM tbl_output_disaggregation  WHERE output_site=:site_id");
                                                                $query_Site_Output->execute(array(":site_id" => $site_id));
                                                                $rows_Site_Output = $query_Site_Output->rowCount();
                                                                if ($rows_Site_Output > 0) {
                                                                    while ($row_Site_Output = $query_Site_Output->fetch()) {
                                                                        $output_id = $row_Site_Output['outputid'];
                                                                        $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id");
                                                                        $query_rsTasks->execute(array(":output_id" => $output_id));
                                                                        $totalRows_rsTasks = $query_rsTasks->rowCount();
                                                                        if ($totalRows_rsTasks > 0) {
                                                                            while ($row_rsTasks = $query_rsTasks->fetch()) {
                                                                                $task_id = $row_rsTasks['tkid'];
                                                                                $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE site_id=:site_id AND subtask_id=:subtask_id ");
                                                                                $query_rsTask_Start_Dates->execute(array(':site_id' => $site_id, ":subtask_id" => $task_id));
                                                                                $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                                $proceed[] = $totalRows_rsTask_Start_Dates > 0 ? true : false;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE indicator_mapping_type<>1 AND projid = :projid");
                                                        $query_Output->execute(array(":projid" => $projid));
                                                        $total_Output = $query_Output->rowCount();
                                                        if ($total_Output > 0) {
                                                            while ($row_rsOutput = $query_Output->fetch()) {
                                                                $output_id = $row_rsOutput['id'];
                                                                $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id");
                                                                $query_rsTasks->execute(array(":output_id" => $output_id));
                                                                $totalRows_rsTasks = $query_rsTasks->rowCount();
                                                                if ($totalRows_rsTasks > 0) {
                                                                    while ($row_rsTasks = $query_rsTasks->fetch()) {
                                                                        $task_id = $row_rsTasks['tkid'];
                                                                        $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE site_id=:site_id AND subtask_id=:subtask_id ");
                                                                        $query_rsTask_Start_Dates->execute(array(':site_id' => 0, ":subtask_id" => $task_id));
                                                                        $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                        $proceed[] = $totalRows_rsTask_Start_Dates > 0 ? true : false;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        return !empty($proceed) && !in_array(false, $proceed) ? true : false;
                                                    }

                                                    if (validate()) {
                                                        $data_entry_details =
                                                            "{
                                                                get_edit_details: 'details',
                                                                projid:$projid,
                                                                workflow_stage:$workflow_stage,
                                                                project_name:'$projname',
                                                                sub_stage:'2',
                                                                stage_id:1,
                                                            }";
                                                    ?>
                                                        <button type="button" onclick="proceed(<?= $data_entry_details ?>)" class="btn btn-success"><?= $project_sub_stage > 1 ? "Amend" : 'Proceed' ?>Proceed</button>
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Start Modal Item Edit -->
                <div class="modal fade" id="outputItemModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color:#03A9F4">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" style="color:#fff" align="center" id="modal-title">Add Program of Works</h4>
                            </div>
                            <div class="modal-body">
                                <div class="card">
                                    <div class="row clearfix">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <div class="body">
                                                <form class="form-horizontal" id="add_output" action="" method="POST">
                                                    <?= csrf_token_html(); ?>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 clearfix" style="margin-top:5px; margin-bottom:5px">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered" id="files_table">
                                                                <thead>
                                                                    <tr>
                                                                        <th style="width: 5%;">#</th>
                                                                        <th style="width:55%">Subtask *</th>
                                                                        <th style="width:10%">Start Date *</th>
                                                                        <th style="width:20%">Duration (Days) *</th>
                                                                        <th style="width:10%">End Date *</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="tasks_table_body">
                                                                    <tr></tr>
                                                                    <tr id="removeTr" align="center">
                                                                        <td colspan="4">Add Program of Works</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                                                            <input type="hidden" name="user_name" id="user_name" value="<?= $user_name ?>">
                                                            <input type="hidden" name="projid" id="projid" value="<?= $projid ?>">
                                                            <input type="hidden" name="store_tasks" id="store_tasks" value="">
                                                            <input type="hidden" name="output_id" id="output_id" value="">
                                                            <input type="hidden" name="task_id" id="task_id" value="">
                                                            <input type="hidden" name="site_id" id="site_id" value="">
                                                            <input type="hidden" name="today" id="today" value="<?= date('Y-m-d') ?>">
                                                            <input name="submtt" type="submit" class="btn btn-primary waves-effect waves-light" id="tag-form-submit" value="Save" />
                                                            <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal"> Cancel</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- /modal-body -->
                        </div>
                        <!-- /modal-content -->
                    </div>
                    <!-- /modal-dailog -->
                </div>
<?php
            } else {
                $results =  restriction();
                echo $results;
            }
        } else {
            $results =  restriction();
            echo $results;
        }
    } catch (PDOException $ex) {
        var_dump($ex);
        customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
    }
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>

<script>
    const redirect_url = "projects.php";
</script>
<script src="assets/js/programofWorks/index.js"></script>