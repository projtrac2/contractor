<?php
require('includes/head.php');
include_once('workplan-structure.php');

if ($permission && isset($_GET['projid']) && !empty($_GET['projid'])) {
    try {
        $encoded_projid = $_GET['projid'];
        $decode_projid = base64_decode($encoded_projid);
        $projid_array = explode("projid54321", $decode_projid);
        $projid = $projid_array[1];

        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();

        if ($totalRows_rsProjects > 0) {
            $projname = $row_rsProjects['projname'];
            $projcode = $row_rsProjects['projcode'];
            $progid = $row_rsProjects['progid'];
            $start_date = $row_rsProjects['projstartdate'];
            $end_date = $row_rsProjects['projenddate'];
            $project_sub_stage = $row_rsProjects['proj_substage'];
            $workflow_stage = $row_rsProjects['projstage'];
            $frequency = $row_rsProjects['activity_monitoring_frequency'];
            $implementation_type = $row_rsProjects['projcategory'];
            $project_start_date = $row_rsProjects['projstartdate'];
            $project_end_date = $row_rsProjects['projenddate'];
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

            $contractor_start = $project_start_date;
            $contractor_end = $project_end_date;
            $contractor_details = get_contract_dates($projid);
            if ($implementation_type == 2) {
                $contractor_details =  get_contract_dates($projid);
                if ($contractor_details) {
                    $contractor_start = $contractor_details['contractor_start'];
                    $contractor_end = $contractor_details['contractor_end'];
                }
            }

            $duration_details = get_duration($project_start_date, $project_end_date);
            $duration = $duration_details['duration'];
            $start_year = $duration_details['start_year'];

            $query_Issues = $db->prepare("SELECT * FROM tbl_projissues WHERE projid=:projid AND (issue_area=2 OR issue_area=3) ORDER BY id DESC LIMIT 1");
            $query_Issues->execute(array(":projid" => $projid));
            $totalRows_Issues = $query_Issues->rowCount();
            $row_Issues = $query_Issues->fetch();
            $issue_id =  ($totalRows_Issues > 0) ? $row_Issues['id'] : '';

            $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments WHERE issueid=:issue_id AND projid=:projid ");
            $query_Issues->execute(array(":issue_id" => $issue_id, ":projid" => $projid));
            $totalRows_Issues = $query_Issues->rowCount();

            if ($totalRows_Issues > 0) {
?>
                <div class="container-fluid">
                    <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
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
                                                <li class="list-group-item list-group-item list-group-item-action active"> Project: <?= $projname ?> </li>
                                                <li class="list-group-item">
                                                    <strong> Code: </strong> <?= $projcode ?>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <strong> Start Date: </strong> <?= date('d M Y', strtotime($start_date)); ?>
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <strong> End Date: </strong><?= date('d M Y', strtotime($end_date)); ?>
                                                    <input type="hidden" name="project_start_date" id="project_start_date" value="<?= $start_date ?>">
                                                    <input type="hidden" name="project_end_date" id="project_end_date" value="<?= $end_date ?>">
                                                    <input type="hidden" name="projid" id="projid" value="<?= $projid ?>">
                                                </li>
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
                                                $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments WHERE issueid=:issue_id AND site_id=:site_id");
                                                $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id));
                                                $totalRows_Issues = $query_Issues->rowCount();
                                                $Rows_Issues = $query_Issues->fetch();
                                                if ($totalRows_Issues > 0) {
                                                    $counter++;
                                        ?>
                                                    <fieldset class="scheduler-border">
                                                        <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                            SITE <?= $counter ?> : <?= $site . " " . $site_id ?>
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


                                                                $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments i INNER JOIN tbl_task t ON t.tkid=i.sub_task_id WHERE issueid=:issue_id AND site_id=:site_id and outputid=:output_id");
                                                                $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":output_id" => $output_id));
                                                                $totalRows_Issues = $query_Issues->rowCount();
                                                                if ($total_Output && $totalRows_Issues > 0) {
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
                                                                                $task_id = $row_rsMilestone['msid'];

                                                                                $task_details = get_task_dates($task_id, $site_id);
                                                                                $task_start_date = $task_details['task_start_date'];
                                                                                $task_end_date = $task_details['task_end_date'];

                                                                                $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments i INNER JOIN tbl_task t ON t.tkid=i.sub_task_id WHERE issueid=:issue_id AND site_id=:site_id and outputid=:output_id AND msid=:task_id");
                                                                                $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":output_id" => $output_id, ":task_id" => $task_id));
                                                                                $totalRows_Issues = $query_Issues->rowCount();

                                                                                if ($totalRows_Issues) {
                                                                                    $table =  get_structure($site_id, $output_id, $task_id, $issue_id, $frequency, $duration, $start_year, $task_start_date, $task_end_date);
                                                                        ?>
                                                                                    <div class="row clearfix">
                                                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                            <div class="card-header">
                                                                                                <div class="row clearfix">
                                                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                                        <h5>
                                                                                                            <li> TASK <?= $task_counter ?>: <?= $milestone ?></li>
                                                                                                        </h5>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="table-responsive">
                                                                                                <?= $table ?>
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
                                            $counter = 0;
                                            $site_id = 0;
                                            while ($row_rsOutput = $query_Output->fetch()) {
                                                $output_id = $row_rsOutput['id'];
                                                $output = $row_rsOutput['indicator_name'];
                                                $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments i INNER JOIN tbl_task t ON t.tkid=i.sub_task_id WHERE issueid=:issue_id AND site_id=:site_id and outputid=:output_id");
                                                $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":output_id" => $output_id));
                                                $totalRows_Issues = $query_Issues->rowCount();
                                                if ($totalRows_Issues > 0) {
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
                                                                $task_id = $row_rsMilestone['msid'];
                                                                $task_details = get_task_dates($task_id, $site_id);
                                                                $task_start_date = $task_details['task_start_date'];
                                                                $task_end_date = $task_details['task_end_date'];
                                                                $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments i INNER JOIN tbl_task t ON t.tkid=i.sub_task_id WHERE issueid=:issue_id AND site_id=:site_id and outputid=:output_id AND msid=:task_id");
                                                                $query_Issues->execute(array(":issue_id" => $issue_id, ":site_id" => $site_id, ":output_id" => $output_id, ":task_id" => $task_id));
                                                                $totalRows_Issues = $query_Issues->rowCount();
                                                                if ($totalRows_Issues > 0) {
                                                                    $table = get_structure($site_id, $output_id, $task_id, $issue_id, $frequency, $duration, $start_year, $task_start_date, $task_end_date);
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
                                                                                            </ul>
                                                                                        </h5>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="table-responsive">
                                                                                <?= $table ?>
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
                                            }
                                        }
                                        ?>
                                        <form role="form" id="form_data" action="" method="post" autocomplete="off" enctype="multipart/form-data">
                                            <div class="row clearfix" style="margin-top:5px; margin-bottom:5px">
                                                <div class="col-md-12 text-center">
                                                    <?php
                                                    $query_Issues = $db->prepare("SELECT * FROM tbl_project_adjustments WHERE issueid=:issue_id AND projid=:projid and flag=0");
                                                    $query_Issues->execute(array(":issue_id" => $issue_id, ":projid" => $projid));
                                                    $totalRows_Issues = $query_Issues->rowCount();
                                                    if ($totalRows_Issues == 0) {
                                                        $token = $_SESSION['csrf_token'];
                                                        $issue_details = "{projid:$projid,project_name:'$projname',csrf_token:'$token', issue_id:$issue_id}";
                                                    ?>
                                                        <button type="button" class="btn btn-success" onclick="close_issue(<?= $issue_details ?>)">Submit</button>
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
                </div>
                <!-- Start Modal Item Edit -->
                <div class="modal fade" id="outputItemModals" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color:#03A9F4">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" style="color:#fff" align="center" id="modal-title">Add Program of Works Structure</h4>
                            </div>
                            <div class="modal-body">
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
                                                    <li class="list-group-item"><strong>Requested Units: </strong> <span id="requested_units"></span> </li>
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
                                                            <?= csrf_token_html(); ?>
                                                            <input type="hidden" name="user_name" id="user_name" value="<?= $user_name ?>">
                                                            <input type="hidden" name="store_target" id="store_target" value="">
                                                            <input type="hidden" name="projid" id="t_projid" value="<?= $projid ?>">
                                                            <input type="hidden" name="issue_id" id="issue_id" value="<?= $issue_id ?>">
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
        customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
    }
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>

<script src="assets/js/programofWorks/extend.js"></script>