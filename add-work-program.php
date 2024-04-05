<?php
require('includes/head.php');
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
                            <div class="card-header">
                                <ul class="nav nav-tabs" style="font-size:14px">
                                    <li class="active">
                                        <a data-toggle="tab" href="#wb"><i class="fa fa-caret-square-o-down bg-deep-orange" aria-hidden="true"></i> Work Program&nbsp;<span class="badge bg-orange">|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#wbs"><i class="fa fa-caret-square-o-up bg-blue" aria-hidden="true"></i> Target Breakdown&nbsp;<span class="badge bg-blue">|</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <div class="tab-content">
                                    <div id="wb" class="tab-pane fade in active">
                                        <?php
                                        include_once('./work-program.php');
                                        ?>
                                    </div>
                                    <div id="wbs" class="tab-pane fade">
                                        <?php
                                        include_once('./target-breakdown.php');
                                        ?>
                                    </div>
                                </div>
                                <?php
                                $query_Comments = $db->prepare("SELECT * FROM tbl_program_of_work_comments WHERE projid = :projid ORDER BY id DESC");
                                $query_Comments->execute(array(":projid" => $projid));
                                $total_Comments = $query_Comments->rowCount();
                                if ($total_Comments > 0) {
                                    while ($row_comment = $query_Comments->fetch()) {
                                        $comment = $row_comment['comments'];
                                        $created_by = $row_comment['created_by'];
                                        $created_at = $row_comment['created_at'];
                                        $query_rsPMbrs = $db->prepare("SELECT t.*, t.email AS email, tt.title AS ttitle, u.userid FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title WHERE userid = :user_id ORDER BY ptid ASC");
                                        $query_rsPMbrs->execute(array(":user_id" => $created_by));
                                        $row_rsPMbrs = $query_rsPMbrs->fetch();
                                        $count_row_rsPMbrs = $query_rsPMbrs->rowCount();
                                        $created_by = $count_row_rsPMbrs > 0 ?  $row_rsPMbrs['ttitle'] . ". " . $row_rsPMbrs['fullname'] : "";
                                ?>
                                        <div class="row clearfix">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <ul class="list-group">
                                                    <li class="list-group-item"><strong>Comment: </strong> <?= $comment ?> </li>
                                                    <li class="list-group-item"><strong>Comment Date: </strong> <?= date('d M Y', strtotime($created_at)); ?> </li>
                                                    <li class="list-group-item">Comments By: <?= $created_by ?> </li>
                                                </ul>
                                            </div>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                                <form role="form" id="form_data" action="" method="post" autocomplete="off" enctype="multipart/form-data">
                                    <div class="row clearfix" style="margin-top:5px; margin-bottom:5px">
                                        <div class="col-md-12 text-center">
                                            <?php
                                            function validate_tasks()
                                            {
                                                global $projid, $db;
                                                $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE projid=:projid AND cost_type	=1");
                                                $query_rsOther_cost_plan->execute(array(":projid" => $projid));
                                                $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();
                                                $result = [];
                                                if ($totalRows_rsOther_cost_plan > 0) {
                                                    while ($row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch()) {
                                                        $site_id = $row_rsOther_cost_plan['site_id'];
                                                        $subtask_id = $row_rsOther_cost_plan['subtask_id'];
                                                        $task_id = $row_rsOther_cost_plan['tasks'];
                                                        if ($subtask_id != 0) {
                                                            $stmt = $db->prepare('SELECT * FROM tbl_project_target_breakdown WHERE site_id=:site_id AND task_id= :task_id AND subtask_id= :subtask_id ');
                                                            $stmt->execute(array(':site_id' => $site_id, ':task_id' => $task_id, ":subtask_id" => $subtask_id));
                                                            $stmt_result = $stmt->rowCount();

                                                            $query_rsWorkBreakdown = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
                                                            $query_rsWorkBreakdown->execute(array(':task_id' => $task_id, ':site_id' => $site_id, ":subtask_id" => $subtask_id));
                                                            $row_rsWorkBreakdown = $query_rsWorkBreakdown->rowCount();
                                                            $result[] =  $stmt_result > 0 && $row_rsWorkBreakdown > 0  ? true : false;
                                                        }
                                                    }
                                                }
                                                return !empty($result) && !in_array(false, $result) ? $result : false;
                                            }

                                            $proceed = validate_tasks();
                                            if ($proceed) {
                                                $approve_details = "{projid:$projid,project_name:'$projname',}";
                                            ?>
                                                <?= csrf_token_html(); ?>
                                                <input type="hidden" name="projid" value="<?= $projid ?>">
                                                <input type="hidden" name="update_project_substage" id="update_project_substage">
                                                <button type="button" class="btn btn-success" onclick="approve_project(<?= $approve_details ?>)">Submit</button>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </form>
                                <?php
                                ?>
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
                        <div class="modal-body" style="max-height:450px; overflow:auto;">
                            <div class="card">
                                <div class="row clearfix">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="body">
                                            <form class="form-horizontal" id="add_output" action="" method="POST">
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
                                                        <?= csrf_token_html(); ?>
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

            <!-- Start Modal Item Edit -->
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
                                                        <?= csrf_token_html(); ?>
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
<?php
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

<script>
    const redirect_url = "projects.php";
</script>
<script src="assets/js/programofWorks/index.js"></script>