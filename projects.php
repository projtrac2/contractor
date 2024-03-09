<?php
require('includes/head.php');
if ($permission) {
    try {
        $query_rsProjects = $db->prepare("SELECT p.*, s.sector, g.projsector, g.projdept, g.directorate FROM tbl_projects p inner join tbl_programs g ON g.progid=p.progid inner join tbl_sectors s on g.projdept=s.stid WHERE p.deleted='0' AND p.projcontractor = :projcontractor AND projstage >= 8  ORDER BY p.projid DESC");
        $query_rsProjects->execute(array(":projcontractor" => $user_name));
        $totalRows_rsProjects = $query_rsProjects->rowCount();


        function check_value_statuses($projid, $payment_plan)
        {
            global $db;
            $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid=:projid ");
            $query_rsProcurement->execute(array(":projid" => $projid));
            $totalRows_rsProcurement = $query_rsProcurement->rowCount();
            $results = false;
            if ($payment_plan == 1) {
                $result = array();
                $query_rsPayment_plan = $db->prepare("SELECT * FROM tbl_project_payment_plan WHERE projid=:projid");
                $query_rsPayment_plan->execute(array(":projid" => $projid));
                $totalRows_rsPayment_plan = $query_rsPayment_plan->rowCount();
                if ($totalRows_rsPayment_plan > 0) {
                    while ($Rows_rsPayment_plan = $query_rsPayment_plan->fetch()) {
                        $payment_plan_id = $Rows_rsPayment_plan['id'];
                        $query_rsProcurement_details =  $db->prepare("SELECT * FROM tbl_contractor_payment_requests WHERE item_id=:item_id ");
                        $query_rsProcurement_details->execute(array(":item_id" => $payment_plan_id));
                        $row_rsProcurement_details = $query_rsProcurement_details->fetch();
                        $totalRows_rsProcurement_details = $query_rsProcurement_details->rowCount();
                        $result[] = $totalRows_rsProcurement_details == 0 ? true : false;
                    }
                }
                $results =  in_array(true, $result) ? true : false;
            } else {
                $result = array();
                if ($totalRows_rsProcurement > 0) {
                    while ($row_rsProcurement = $query_rsProcurement->fetch()) {
                        $procurement_units = $row_rsProcurement['units_no'];
                        $tender_id = $row_rsProcurement['id'];
                        $query_rsProcurement_details =  $db->prepare("SELECT * FROM tbl_contractor_payment_request_details WHERE tender_item_id=:tender_id ");
                        $query_rsProcurement_details->execute(array(":tender_id" => $tender_id));
                        $row_rsProcurement_details = $query_rsProcurement_details->fetch();
                        $totalRows_rsProcurement_details = $query_rsProcurement_details->rowCount();
                        if ($totalRows_rsProcurement_details > 0) {
                            $procurement_units_no = $row_rsProcurement_details['units_no'];
                            $remaining = $procurement_units - $procurement_units_no;
                            $result[] = $remaining > 0 ? true : false;
                        } else {
                            $result[] = true;
                        }
                    }
                }
                $results =  in_array(true, $result) ? true : false;
            }
            return $results;
        }

        function work_measured($projid)
        {
            global $db;
            $query_rsTask_Complete = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid ");
            $query_rsTask_Complete->execute(array(':projid' => $projid));
            $total_rsTask_Complete = $query_rsTask_Complete->rowCount();
            $request_payment = [];
            if ($total_rsTask_Complete > 0) {
                while ($rows_rsTask_Complete = $query_rsTask_Complete->fetch()) {
                    $subtask_id = $rows_rsTask_Complete['subtask_id'];
                    $site_id = $rows_rsTask_Complete['site_id'];

                    $query_rsMilestone_cummulative =  $db->prepare("SELECT SUM(achieved) AS cummulative FROM tbl_project_monitoring_checklist_score WHERE subtask_id=:subtask_id AND site_id=:site_id ");
                    $query_rsMilestone_cummulative->execute(array(":subtask_id" => $subtask_id, ':site_id' => $site_id));
                    $row_rsMilestone_cummulative = $query_rsMilestone_cummulative->fetch();

                    if (!is_null($row_rsMilestone_cummulative['cummulative'])) {
                        $cummulative =  $row_rsMilestone_cummulative['cummulative'];
                        $query_rsPayment =  $db->prepare("SELECT SUM(d.units_no) AS requested_units FROM tbl_contractor_payment_requests r INNER JOIN tbl_contractor_payment_request_details d ON d.request_id=r.request_id WHERE d.projid=:projid AND d.site_id=:site_id AND d.subtask_id=:subtask_id  AND r.status<>6");
                        $query_rsPayment->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
                        $Rows_rsPayment = $query_rsPayment->fetch();

                        $requested_units = !is_null($Rows_rsPayment['requested_units']) ?   $Rows_rsPayment['requested_units'] : 0;
                        $request_units = $cummulative - $requested_units;
                        $request_payment[] = $request_units > 0 ? true : false;
                    }
                }
            }

            return in_array(true, $request_payment)  ? true : false;
        }

        function milestone_based($projid)
        {
            global $db;
            $query_rsPayment_plan = $db->prepare("SELECT * FROM tbl_project_payment_plan WHERE projid=:projid AND (requested_status=0 OR requested_status =4)");
            $query_rsPayment_plan->execute(array(":projid" => $projid));
            $totalRows_rsPayment_plan = $query_rsPayment_plan->rowCount();
            $request_payment = [];
            if ($totalRows_rsPayment_plan > 0) {
                while ($Rows_rsPayment_plan = $query_rsPayment_plan->fetch()) {
                    $payment_plan_id = $Rows_rsPayment_plan['id'];

                    $query_rsPayement_requests =  $db->prepare("SELECT * FROM tbl_contractor_payment_requests WHERE item_id=:item_id AND status=6");
                    $query_rsPayement_requests->execute(array(":item_id" => $payment_plan_id));
                    $total_rsPayement_requests = $query_rsPayement_requests->rowCount();

                    if ($total_rsPayement_requests > 0) {
                        $request_payment[] =  true;
                    } else {
                        $query_rsPayement_plan_details =  $db->prepare("SELECT * FROM tbl_project_payment_plan_details WHERE payment_plan_id =:payment_plan_id");
                        $query_rsPayement_plan_details->execute(array('payment_plan_id' => $payment_plan_id));
                        $total_rsPayement_plan_details = $query_rsPayement_plan_details->rowCount();
                        $milestone_complete = [];
                        if ($total_rsPayement_plan_details > 0) {
                            while ($Rows_rsPayment_plan_details = $query_rsPayement_plan_details->fetch()) {
                                $milestone_id = $Rows_rsPayment_plan_details['milestone_id'];

                                $query_rsChecked = $db->prepare("SELECT * FROM tbl_milestone_output_subtasks WHERE milestone_id=:milestone_id  AND complete=0 ");
                                $query_rsChecked->execute(array(":milestone_id" => $milestone_id));
                                $totalRows_rsChecked = $query_rsChecked->rowCount();
                                $milestone_complete[] = $totalRows_rsChecked > 0 ? false : true;
                            }
                        }
                        $request_payment[] = in_array(false, $milestone_complete)  ? false : true;
                    }
                }
            }
            return in_array(true, $request_payment) ? true : false;
        }

        function tasks_based($projid)
        {
            global $db;
            $query_rsTask_Complete = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid AND complete=1");
            $query_rsTask_Complete->execute(array(':projid' => $projid));
            $total_rsTask_Complete = $query_rsTask_Complete->rowCount();
            $task_complete = [];
            if ($total_rsTask_Complete > 0) {
                while ($rows_rsTask_Complete = $query_rsTask_Complete->fetch()) {
                    $subtask_id = $rows_rsTask_Complete['subtask_id'];
                    $site_id = $rows_rsTask_Complete['site_id'];

                    $query_rsPayment =  $db->prepare("SELECT * FROM tbl_contractor_payment_requests r INNER JOIN tbl_contractor_payment_request_details d ON d.request_id=r.request_id WHERE d.projid=:projid AND d.site_id=:site_id AND d.subtask_id=:subtask_id ");
                    $query_rsPayment->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
                    $totalRows_rsPayment = $query_rsPayment->rowCount();
                    $Rows_rsPayment = $query_rsPayment->fetch();
                    if ($totalRows_rsPayment > 0) {
                        $status =  $Rows_rsPayment['status'];
                        $task_complete[] = $status == 6  ? false : true;
                    } else {
                        $task_complete[] = true;
                    }
                }
            }

            return in_array(true, $task_complete)  ? false : true;
        }

        function check_for_payment($projid, $payment_plan)
        {
            $request_payment = false;
            if ($payment_plan == 1) {
                $request_payment = milestone_based($projid);
            } else if ($payment_plan == 2) {
                $request_payment = tasks_based($projid);
            } else if ($payment_plan == 3) {
                $request_payment = work_measured($projid);
            }
            return $request_payment;
        }

        function get_progress($progress, $projstatus)
        {

            $project_progress = '
            <div class="progress" style="height:20px; font-size:10px; color:black">
                <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
                    ' . $progress . '%
                </div>
            </div>';
            if ($progress == 100) {
                $project_progress = '
                <div class="progress" style="height:20px; font-size:10px; color:black">
                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
                    ' . $progress . '%
                    </div>
                </div>';
            } else if ($progress > 100) {
                if ($projstatus == 5) {
                    $project_progress = '
                    <div class="progress" style="height:20px; font-size:10px; color:black">
                        <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%; height:20px; font-size:10px; color:black">
                        ' . $progress . '%
                        </div>
                    </div>';
                } else {
                    $project_progress = '
                    <div class="progress" style="height:20px; font-size:10px; color:black">
                        <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; height:20px; font-size:10px; color:black">
                            ' . $progress . '%
                        </div>
                    </div>';
                }
            } else if ($progress <  100 && $projstatus == 5) {
                $project_progress = '
                <div class="progress" style="height:20px; font-size:10px; color:black">
                    <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
                    ' . $progress . '%
                    </div>
                </div>';
            }
            return $project_progress;
        }

        function get_status($projstatus)
        {
            global $db;
            $query_Projstatus =  $db->prepare("SELECT * FROM tbl_status WHERE statusid = :projstatus");
            $query_Projstatus->execute(array(":projstatus" => $projstatus));
            $row_Projstatus = $query_Projstatus->fetch();
            $total_Projstatus = $query_Projstatus->rowCount();
            $status = "";
            if ($total_Projstatus > 0) {
                $status_name = $row_Projstatus['statusname'];
                $status_class = $row_Projstatus['class_name'];
                $status = '<button type="button" class="' . $status_class . '" style="width:100%">' . $status_name . '</button>';
            }
            return $status;
        }
    } catch (PDOException $ex) {
        $results = flashMessage("An error occurred: " . $ex->getMessage());
    }
?>
    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:10px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-dashboard" style="color:white"></i> Projects
                <div class="btn-group" style="float:right">
                    <div class="btn-group" style="float:right">
                    </div>
                </div>
            </h4>
        </div>
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="manageItemTable">
                                <thead>
                                    <tr style="background-color:#0b548f; color:#FFF">
                                        <th style="width:5%" align="center">#</th>
                                        <th style="width:10%">Code</th>
                                        <th style="width:31%">Project </th>
                                        <th style="width:12%">Start Date</th>
                                        <th style="width:12%">End date</th>
                                        <th style="width:10%">Progress</th>
                                        <th style="width:10%">Status</th>
                                        <th style="width:10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($totalRows_rsProjects > 0) {
                                        $counter = 0;
                                        while ($row_rsProjects = $query_rsProjects->fetch()) {
                                            $counter++;
                                            $projid = $row_rsProjects['projid'];
                                            $projid_hashed = base64_encode("projid54321{$projid}");
                                            $implementation = $row_rsProjects['projcategory'];
                                            $sub_stage = $row_rsProjects['proj_substage'];
                                            $projstage = $row_rsProjects['projstage'];
                                            $projname = $row_rsProjects['projname'];
                                            $projcode = $row_rsProjects['projcode'];
                                            $projstatus = $row_rsProjects['projstatus'];
                                            $payment_plan = $row_rsProjects['payment_plan'];
                                            $project_start_date =  $row_rsProjects['projstartdate'];
                                            $project_end_date =  $row_rsProjects['projenddate'];
                                            $projcontractor =  $row_rsProjects['projcategory'];
                                            $proj_progress = calculate_project_progress($projid, $implementation);
                                            $progress = number_format(calculate_project_progress($projid, $implementation), 2);

                                            $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM tbl_program_of_works WHERE projid=:projid LIMIT 1");
                                            $query_rsTask_Start_Dates->execute(array(':projid' => $projid));
                                            $rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();

                                            $query_rsTender_start_Date = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid LIMIT 1");
                                            $query_rsTender_start_Date->execute(array(':projid' => $projid));
                                            $rows_rsTender_start_Date = $query_rsTender_start_Date->fetch();
                                            $total_rsTender_start_Date = $query_rsTender_start_Date->rowCount();
                                            $contractor_number = '';
                                            if ($total_rsTender_start_Date > 0) {
                                                $contractor_number = $rows_rsTender_start_Date['tenderno'];
                                                $project_start_date =  $rows_rsTender_start_Date['startdate'];
                                                $project_end_date =  $rows_rsTender_start_Date['enddate'];
                                                if (!is_null($rows_rsTask_Start_Dates['start_date'])) {
                                                    $project_start_date =  $rows_rsTask_Start_Dates['start_date'];
                                                    $project_end_date =  $rows_rsTask_Start_Dates['end_date'];
                                                }
                                            }

                                            $filter = false;

                                            if ($projstage == 8) {
                                                if ($sub_stage == 1) {
                                                    $filter = true;
                                                } else if ($sub_stage == 2) {
                                                    $filter = true;
                                                }
                                            } else {
                                                $filter = true;
                                                if ($sub_stage == 0) {
                                                } else if ($sub_stage == 1) {
                                                } else if ($sub_stage == 2) {
                                                }
                                            }

                                            $complete = $proj_progress  == 100 ? 1 : 0;
                                            $details = array("projid" => $projid, "payment_plan" => $payment_plan, "complete" => $complete);
                                            $payment_validation = check_for_payment($projid, $payment_plan);

                                            $query_rsPayement_reuests =  $db->prepare("SELECT * FROM  tbl_contractor_payment_requests WHERE status <> 3 AND contractor_id=:contractor_id AND projid=:projid");
                                            $query_rsPayement_reuests->execute(array(":contractor_id" => $user_name, ":projid" => $projid));
                                            $total_rsPayement_reuests = $query_rsPayement_reuests->rowCount();
                                            $activity = '';
                                            if ($filter) {
                                    ?>
                                                <tr>
                                                    <td align="center"><?= $counter ?></td>
                                                    <td><?= $projcode ?></td>
                                                    <td>
                                                        <div class="links" style="background-color:#9E9E9E; color:white; padding:5px;">
                                                            <a href="project-dashboard.php?proj=<?php echo $projid_hashed; ?>" style="color:#FFF; font-weight:bold"><?= $projname ?></a>
                                                        </div>
                                                    </td>
                                                    <td><?= $project_start_date ?></td>
                                                    <td><?= $project_end_date ?></td>
                                                    <td><?= get_progress($progress, $projstatus) ?></td>
                                                    <td><?= get_status($projstatus) ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                Options <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a type="button" href="project-team.php?proj=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                        <i class="fa fa-plus-square-o"></i> Team
                                                                    </a>
                                                                </li>
                                                                <?php
                                                                if ($projstage == 8) {
                                                                ?>
                                                                    <li>
                                                                        <a type="button" href="add-work-program.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                            <i class="fa fa-plus-square-o"></i> <?= $activity ?> Program of Works
                                                                        </a>
                                                                    </li>
                                                                <?php
                                                                } else {
                                                                ?>
                                                                    <li>
                                                                        <a type="button" href="project-timeline.php?proj=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                            <i class="fa fa-plus-square-o"></i> Timeline
                                                                        </a>
                                                                    </li>
                                                                    <?php
                                                                    if ($total_rsPayement_reuests > 0) {
                                                                    ?>
                                                                        <li>
                                                                            <a type="button" href="payment.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                <i class="fa fa-plus-square-o"></i> Payment Requests
                                                                            </a>
                                                                        </li>
                                                                        <?php
                                                                    }
                                                                    if ($payment_validation && $sub_stage == 3) {
                                                                        $request_payment = check_value_statuses($projid, $payment_plan);
                                                                        if ($request_payment) {
                                                                        ?>
                                                                            <li>
                                                                                <a type="button" data-toggle="modal" id="moreItemModalBtn" data-target="#addFormModal" onclick="get_details(<?= $projid ?>, <?= $payment_plan ?>, '<?= htmlspecialchars($projname) ?>', '<?= $contractor_number ?>', '<?= $complete ?>')">
                                                                                    <i class="fa fa-info"></i> Request Payment
                                                                                </a>
                                                                            </li>
                                                                <?php
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            </ul>
                                                        </div>
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
            </div>
        </div>
    </div>


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

<script src="assets/js/payment/index.js"></script>