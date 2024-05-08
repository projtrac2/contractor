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
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:5%">#</th>
                                                                <th style="width:65%">Subtask</th>
                                                                <th style="width:15%">Unit of Measure</th>
                                                                <th style="width:15%">Frequency</th>
                                                                <th style="width:15%">Action</th>
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

                                                                    $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id AND frequency_id IS NOT NULL");
                                                                    $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => $site_id, ":subtask_id" => $task_id));
                                                                    $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                                    $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                    $frequenc_id = ($totalRows_rsTask_Start_Dates > 0) ? $row_rsTask_Start_Dates['frequency_id'] : '';
                                                                    $frequency = get_frequency($frequenc_id);


                                                                    $query_rsTargetBreakdown = $db->prepare("SELECT * FROM  tbl_project_target_breakdown WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id");
                                                                    $query_rsTargetBreakdown->execute(array(':task_id' => $msid, ':site_id' => $site_id, ":subtask_id" => $task_id));
                                                                    $totalRows_rsTargetBreakdown = $query_rsTargetBreakdown->rowCount();
                                                                    $proceed[] = $totalRows_rsTargetBreakdown > 0 ? true : false;
                                                            ?>
                                                                    <tr id="row">
                                                                        <td style="width:5%"><?= $tcounter ?></td>
                                                                        <td style="width:65%"><?= $task_name ?></td>
                                                                        <td style="width:15%"><?= $unit_of_measure ?></td>
                                                                        <td style="width:15%"><?= $frequency ?></td>
                                                                        <td style="width:15%">
                                                                            <button type="button" onclick="get_subtasks_wbs(<?= $output_id ?>, <?= $site_id ?>, <?= $msid ?> , <?= $task_id ?>, <?= $subtask_frequency ?>)" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px">
                                                                                <span class="glyphicon  glyphicon-eye-open"></span>
                                                                            </button>
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
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                            <thead>
                                                <tr>
                                                    <th style="width:5%">#</th>
                                                    <th style="width:65%">Item</th>
                                                    <th style="width:15%">Unit of Measure</th>
                                                    <th style="width:15%">Frequency</th>
                                                    <th style="width:15%">Action</th>
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

                                                        $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id AND frequency_id IS NOT NULL");
                                                        $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => 0, ":subtask_id" => $task_id));
                                                        $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                        $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                        $frequenc_id = ($totalRows_rsTask_Start_Dates > 0) ? $row_rsTask_Start_Dates['frequency_id'] : '';
                                                        $frequency = get_frequency($frequenc_id);

                                                        $query_rsTargetBreakdown = $db->prepare("SELECT * FROM  tbl_project_target_breakdown WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id");
                                                        $query_rsTargetBreakdown->execute(array(':task_id' => $msid, ':site_id' => 0, ":subtask_id" => $task_id));
                                                        $totalRows_rsTargetBreakdown = $query_rsTargetBreakdown->rowCount();
                                                        $proceed[] = $totalRows_rsTargetBreakdown > 0 ? true : false;
                                                ?>
                                                        <tr id="row<?= $tcounter ?>">
                                                            <td style="width:5%"><?= $tcounter ?></td>
                                                            <td style="width:65%"><?= $task_name ?></td>
                                                            <td style="width:15%"><?= $unit_of_measure ?></td>
                                                            <td style="width:15%"><?= $frequency ?></td>
                                                            <td style="width:15%">
                                                                <button type="button" onclick="get_subtasks_wbs(<?= $output_id ?>, <?= $site_id ?>, <?= $msid ?> , <?= $task_id ?>, <?= $subtask_frequency ?>)" data-toggle="modal" data-target="#outputItemModals" data-backdrop="static" data-keyboard="false" class="btn btn-success btn-sm" style=" margin-top:-5px">
                                                                    <span class="glyphicon  glyphicon-eye-open"></span>
                                                                </button>
                                                            </td>
                                                        </tr>
                                            <?php
                                                    }
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
        ?>
</div>