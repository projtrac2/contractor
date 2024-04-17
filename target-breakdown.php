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
                                                                                        <h5>
                                                                                            <u>
                                                                                                TASK <?= $task_counter ?>: <?= $milestone ?>
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
                                                                                            <th style="width:55%">Subtask</th>
                                                                                            <th style="width:10%">Duration</th>
                                                                                            <th style="width:15%">Start Date</th>
                                                                                            <th style="width:15%">End Date</th>
                                                                                            <th>Action </th>
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
                                                                                                    <td style="width:10%"><?= $duration ?> Days</td>
                                                                                                    <td style="width:15%"><?= $start_date ?></td>
                                                                                                    <td style="width:15%"><?= $end_date ?></td>
                                                                                                    <td>
                                                                                                        <button data-toggle="modal" data-target="#outputItemModals" class="achieved-btn btn btn-success btn-sm"><i style="font-size: 14px;" class="fa fa-eye" aria-hidden="true"></i></button>
                                                                                                        <input type="hidden" value="<?= $site_id ?>" />
                                                                                                        <input type="hidden" value="<?= $output_id ?>" />
                                                                                                        <input type="hidden" value="<?= $msid ?>" />
                                                                                                        <input type="hidden" value="<?= $task_id ?>" />
                                                                                                        <input type="hidden" value="<?= $start_date ?>" />
                                                                                                        <input type="hidden" value="<?= $end_date ?>" />
                                                                                                    </td>
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
                                                                            <h5>
                                                                                <u>
                                                                                    TASK <?= $task_counter ?>: <?= $milestone ?>
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
                                                                                <th style="width:55%">Item</th>
                                                                                <th style="width:10%">Duration</th>
                                                                                <th style="width:15%">Start Date</th>
                                                                                <th style="width:15%">End Date</th>
                                                                                <th>Action</th>
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
                                                                                    
                                                                                    <tr id="row<?= $tcounter ?>">
                                                                                        <td style="width:5%"><?= $task_counter ?>.<?= $tcounter ?></td>
                                                                                        <td style="width:55%"><?= $task_name ?></td>
                                                                                        <td style="width:10%"><?= $duration ?> Days</td>
                                                                                        <td style="width:15%"><?= $start_date ?> </td>
                                                                                        <td style="width:15%"><?= $end_date ?></td>
                                                                                        <td>
                                                                                            <button data-toggle="modal" data-target="#outputItemModals" class="achieved-btn btn btn-success btn-sm"><i style="font-size: 14px;" class="fa fa-eye" aria-hidden="true"></i></button>
                                                                                            <input type="hidden" value="<?= $site_id ?>" />
                                                                                            <input type="hidden" value="<?= $output_id ?>" />
                                                                                            <input type="hidden" value="<?= $msid ?>" />
                                                                                            <input type="hidden" value="<?= $task_id ?>" />
                                                                                            <input type="hidden" value="<?= $start_date ?>" />
                                                                                            <input type="hidden" value="<?= $end_date ?>" />
                                                                                        </td>
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


            <div class="modal fade" id="outputItemModals" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color:#03A9F4">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" style="color:#fff" align="center" id="modal-title">Add Program of Works Structure</h4>
                        </div>
                        <div class="modal-body" style="max-height:450px; overflow:auto;">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row clearfix">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <ul class="list-group">
                                                <li class="list-group-item list-group-item list-group-item-action active">Subtask: <span id="subtask_name"></span> </li>
                                                <li class="list-group-item"><strong>Start Date: </strong> <span id="subtask_start_date"></span> </li>
                                                <li class="list-group-item"><strong>Duration: </strong> <span id="subtask_duration"></span> days</li>
                                                <li class="list-group-item"><strong>End Date: </strong> <span id="subtask_end_date"></span> </li>
                                                <li class="list-group-item"><strong>Target: </strong> <span id="subtask_target"></span> </li>
                                                <input type="hidden" name="total_target" id="total_target" value="">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="row clearfix">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="body">
                                            <form class="form-horizontal" id="add_project_frequency" action="" method="POST">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 clearfix" style="margin-top:5px; margin-bottom:5px">
                                                    <div class="table-responsive">
                                                        <div id="tasks_wbs_table_body">

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                                                        <input type="hidden" name="user_name" id="user_name" value="<?= $user_name ?>">
                                                        <input type="hidden" name="store_target" id="store_target" value="">
                                                        <input type="hidden" name="projid" id="t_projid" value="<?= $projid ?>">
                                                        <input type="hidden" name="output_id" id="t_output_id" value="">
                                                        <input type="hidden" name="site_id" id="t_site_id" value="">
                                                        <input type="hidden" name="task_id" id="t_task_id" value="">
                                                        <input type="hidden" name="frequency" id="frequency" value="<?= $monitoring_frequency_id ?>">
                                                        <input type="hidden" name="subtask_id" id="t_subtask_id" value="">
                                                        <input type="hidden" name="today" id="today" value="<?= date('Y-m-d') ?>">
                                                        <input name="submtt" type="submit" class="btn btn-primary waves-effect waves-light" id="tag-form-submit-frequency" value="Save" />
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