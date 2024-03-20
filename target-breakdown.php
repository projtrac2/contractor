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
                                            <input type="hidden" name="task_id" value="<?php echo $msid ?>" class="tasks_id_header" />
                                            <input type="hidden" name="site_id" value="<?php echo $site_id ?>" class="sites_id_header" />
                                            <input type="hidden" name="outputs_id" value="<?php echo $output_id ?>" class="outputs_id_header" />
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
                                                    <input type="hidden" value="<?php echo $output_id ?>" class="output_id_header" />
                                                    <div class="peter-<?php echo $site_id . $msid ?>"></div>
                                                    <div class="table-responsive">
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
                            WAY POINT OUTPUT <?= $counter ?>: <?= $output ?>
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
                                $task_counter++;
                        ?>
                                <input type="hidden" name="task_id" value="<?php echo $msid ?>" class="tasks_id_header" />
                                <input type="hidden" name="site_id" value="<?php echo $site_id ?>" class="sites_id_header" />
                                <input type="hidden" name="outputs_id" value="<?php echo $output_id ?>" class="outputs_id_header" />
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
                                        <input type="hidden" value="<?php echo $output_id ?>" class="output_id_header" />
                                        <div class="peter-<?php echo $site_id . $msid ?>"></div>
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