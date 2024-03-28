<?php
require('includes/head.php');
if ($permission && isset($_GET['projid']) && !empty($_GET['projid'])) {
    try {
        $encoded_projid = $_GET['projid'];
        $decode_projid = base64_decode($encoded_projid);
        $projid_array = explode("projid54321", $decode_projid);
        $projid = $projid_array[1];

        $projid = 18;

        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();
        $approve_details = "";

        if ($totalRows_rsProjects > 0) {
            $projname = $row_rsProjects['projname'];
            $projcode = $row_rsProjects['projcode'];
            $progid = $row_rsProjects['progid'];
            $start_date = $row_rsProjects['projstartdate'];
            $end_date = $row_rsProjects['projenddate'];
            $project_sub_stage = $row_rsProjects['proj_substage'];
            $workflow_stage = $row_rsProjects['projstage'];

            $monitoring_frequency_id = $row_rsProjects['activity_monitoring_frequency'];
            $query_frequency = $db->prepare("SELECT * FROM tbl_datacollectionfreq WHERE status=1 AND fqid=:monitoring_frequency_id ");
            $query_frequency->execute(array(":monitoring_frequency_id" => $monitoring_frequency_id));
            $totalRows_frequency = $query_frequency->rowCount();
            $row_frequency = $query_frequency->fetch();
            $monitoring_frequency = ($totalRows_frequency > 0) ? $row_frequency['frequency'] : '';

            $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
            $query_rsTender->execute(array(":projid" => $projid));
            $row_rsTender = $query_rsTender->fetch();
            $totalRows_rsTender = $query_rsTender->rowCount();
            if ($totalRows_rsTender > 0) {
                $start_date = $row_rsTender['startdate'];
                $end_date = $row_rsTender['enddate'];
            }

            $query_Issues = $db->prepare("SELECT * FROM tbl_projissues WHERE projid=:projid AND (issue_area=2 || issue_area=3) ");
            $query_Issues->execute(array(":projid" => $projid));
            $totalRows_Issues = $query_Issues->rowCount();
            $row_Issues = $query_Issues->fetch();
            $issue_id =  ($totalRows_Issues > 0) ? $row_Issues['id'] : '';

            function get_issue_adjustments($site_id, $output_id, $subtask_id, $type)
            {
                global $db, $issue_id;
                $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments WHERE issue_id=:issue_id AND site_id=:site_id and subtask_id=:subtask_id");
                $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
                if ($type == 1) {
                    $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments WHERE issue_id=:issue_id AND site_id=:site_id ");
                    $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id));
                } else if ($type == 2) {
                    $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments i INNER JOIN tbl_task t ON t.tkid=i.subtask_id WHERE issue_id=:issue_id AND site_id=:site_id and outputid=:output_id");
                    $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":output_id" => $output_id));
                }
                $totalRows_Issues = $query_Issues->rowCount();
                $row_Issues = $query_Issues->fetch();
                return $totalRows_Issues ? $row_Issues : false;
            }
?>
            <div class="container-fluid">
                <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:10px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                    <h4 class="contentheader">
                        <i class="fa fa-columns" style="color:white"></i> Adjust Work Program
                        <div class="btn-group" style="float:right">
                            <div class="btn-group" style="float:right">
                                <a type="button" id="outputItemModalBtnrow" onclick="history.back()" class="btn btn-warning pull-right">
                                    Go Back
                                </a>
                            </div>
                        </div>
                    </h4>
                </div>
                <div class="row clearfix">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row clearfix">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item list-group-item-action active">Project Name: <?= $projname ?> </li>
                                            <li class="list-group-item"><strong>Project Code: </strong> <?= $projcode ?> </li>
                                            <li class="list-group-item"><strong>Contract Start Date: </strong> <?= date('d M Y', strtotime($start_date)); ?> </li>
                                            <li class="list-group-item"><strong>Contract End Date: </strong> <?= date('d M Y', strtotime($end_date)); ?> </li>
                                            <li class="list-group-item"><strong>Monitoring Frequency: </strong> <?= $monitoring_frequency; ?> </li>
                                            <input type="hidden" name="project_start_date" id="project_start_date" value="<?= $start_date ?>">
                                            <input type="hidden" name="project_end_date" id="project_end_date" value="<?= $end_date ?>">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="body">
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
                                            if (get_issue_adjustments($site_id, '', '', 1)) {
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

                                                            if ($total_Output && get_issue_adjustments($site_id, $output_id, '', 2)) {
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
        } else {
            $results =  restriction();
            echo $results;
        }
    } catch (PDOException $ex) {
        $results = flashMessage("An error occurred: " . $ex->getMessage());
    }
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>