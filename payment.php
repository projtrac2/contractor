<?php
$decode_projid = (isset($_GET['projid']) && !empty($_GET["projid"])) ? base64_decode($_GET['projid']) : header("Location: projects");
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];

require('includes/head.php');
if ($permission) {

    $query_rsprojects =  $db->prepare("SELECT * FROM  tbl_projects WHERE projid = :projid");
    $query_rsprojects->execute(array(":projid" => $projid));
    $rows_rsprojects = $query_rsprojects->fetch();
    $total_rsprojects = $query_rsprojects->rowCount();

    $progid = $rows_rsprojects['progid'];
    $project_name = $rows_rsprojects['projname'];
    $payment_plan = $rows_rsprojects['payment_plan'];

    $payment_plan_name = "";
    if ($payment_plan == 1) {
        $payment_plan_name = "Milestone";
    } else if ($payment_plan == 2) {
        $payment_plan_name = "Task";
    } else if ($payment_plan == 3) {
        $payment_plan_name = "Work Measured";
    }


    $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid = :projid");
    $query_rsTender->execute(array(":projid" => $projid));
    $row_rsTender = $query_rsTender->fetch();
    $totalRows_rsTender = $query_rsTender->rowCount();
    $contract_no = $totalRows_rsTender > 0 ? $row_rsTender['contractrefno'] : '';

    $query_Procurement = $db->prepare("SELECT SUM(unit_cost * units_no) as total_cost FROM tbl_project_tender_details WHERE projid = :projid");
    $query_Procurement->execute(array(":projid" => $projid));
    $row_rsProcurement = $query_Procurement->fetch();
    $amount =  ($row_rsProcurement['total_cost'] != NULL) ? $row_rsProcurement['total_cost'] : 0;
?>
    <style>
        .modal-lg {
            max-width: 100% !important;
            width: 90%;
        }
    </style>
    <!-- start body  -->
    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-money" style="color:white"></i> Payment
                <div class="btn-group" style="float:right; margin-right:10px">
                    <input type="button" VALUE="Go Back to Projects Dashboard" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
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
                                    <li class="list-group-item list-group-item list-group-item-action active">Project Name: <?= $project_name ?> </li>
                                    <li class="list-group-item"><strong>Contract Number: </strong> <?= $contract_no ?> </li>
                                    <li class="list-group-item"><strong>Contract Cost: </strong> Ksh. <?php echo number_format($amount, 2); ?> </li>
                                    <li class="list-group-item"><strong>Payment Plan: </strong><?= $payment_plan_name; ?> </li>
                                    <input type="hidden" name="payment_plan" id="payment_plan" value="<?= $payment_plan ?>">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-header">
                        <ul class="nav nav-tabs" style="font-size:14px">
                            <li class="active">
                                <a data-toggle="tab" href="#menu1">
                                    <i class="fa fa-caret-square-o-up bg-deep-purple" aria-hidden="true"></i>
                                    Pending Requests &nbsp;
                                    <span class="badge bg-deep-purple"></span>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#menu2">
                                    <i class="fa fa-caret-square-o-right bg-indigo" aria-hidden="true"></i>
                                    Paid Requests &nbsp;
                                    <span class="badge bg-indigo"></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <!-- ============================================================== -->
                        <!-- Start Page Content -->
                        <!-- ============================================================== -->
                        <div class="tab-content">
                            <div id="menu1" class="tab-pane active">
                                <div style="color:#333; background-color:#EEE; width:100%; height:30px">
                                    <h4 style="width:100%"><i class="fa fa-hourglass-half fa-sm" style="font-size:25px;color:#6c0eb0"></i> New Requests</h4>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr class="bg-deep-purple">
                                                <th style="width:5%">#</th>
                                                <th style="width:45%">Project Name</th>
                                                <th style="width:20%">Requested Amount</th>
                                                <th style="width:20%">Date Requested</th>
                                                <th style="width:10%">Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query_rsPayement_reuests =  $db->prepare("SELECT * FROM  tbl_contractor_payment_requests WHERE status <> 3 AND contractor_id=:contractor_id AND projid=:projid");
                                            $query_rsPayement_reuests->execute(array(":contractor_id" => $user_name, ":projid" => $projid));
                                            $total_rsPayement_reuests = $query_rsPayement_reuests->rowCount();
                                            if ($total_rsPayement_reuests > 0) {
                                                $counter = 0;
                                                while ($rows_rsPayement_reuests = $query_rsPayement_reuests->fetch()) {
                                                    $costline_id = $rows_rsPayement_reuests['id'];
                                                    $projid = $rows_rsPayement_reuests['projid'];
                                                    $request_id = $rows_rsPayement_reuests['request_id'];
                                                    $payment_requested_date = $rows_rsPayement_reuests['created_at'];
                                                    $payment_status = $rows_rsPayement_reuests['status'];
                                                    $payment_stage = $rows_rsPayement_reuests['stage'];
                                                    $amount_paid = $rows_rsPayement_reuests['requested_amount'];
                                                    $payment_phase_id = $rows_rsPayement_reuests['item_id'];

                                                    $stage = "";
                                                    $status  = "Pending";
                                                    if ($payment_stage == 1) {
                                                        $stage = "Team Leader";
                                                    } else if ($payment_stage == 1) {
                                                        $status  = $payment_status == 1 ? "Pending" : "Rejected";
                                                        $stage = "CO Department";
                                                    } else if ($payment_stage == 2) {
                                                        $status  = $payment_status == 1 ? "Pending" : "Rejected";
                                                        $stage = "CO Finance";
                                                    } else if ($payment_stage == 3) {
                                                        $stage = "Director Finance";
                                                    }
                                                    $counter++;
                                            ?>
                                                    <tr class="">
                                                        <td style="width:5%"><?= $counter ?></td>
                                                        <td style="width:45%"><?= $project_name ?></td>
                                                        <td style="width:20%"><?= number_format($amount_paid, 2) ?></td>
                                                        <td style="width:20%"><?= date("d M Y", strtotime($payment_requested_date)) ?></td>
                                                        <td style="width:10%">
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    Options <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a type="button" data-toggle="modal" id="moreItemModalBtn" data-target="#moreItemModal" onclick="get_more_info(<?= $costline_id ?>)">
                                                                            <i class="fa fa-info"></i>More Info
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
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
                            <div id="menu2" class="tab-pane">
                                <div style="color:#333; background-color:#EEE; width:100%; height:30px">
                                    <h4 style="width:100%"><i class="fa fa-money" style="font-size:25px;color:indigo"></i> Paid Requests</h4>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                        <thead>
                                            <tr class="bg-indigo">
                                                <th style="width:5%">#</th>
                                                <th style="width:45%">Project Name</th>
                                                <th style="width:10%">Amount Paid</th>
                                                <th style="width:10%">Date Requested</th>
                                                <th style="width:10%">Paid By</th>
                                                <th style="width:10%">Date Paid</th>
                                                <th style="width:10%">Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query_rsPayement_reuests =  $db->prepare("SELECT r.*, d.created_at, d.created_by, d.date_paid,d.receipt  FROM tbl_contractor_payment_requests r INNER JOIN tbl_payments_disbursed d ON d.request_id = r.id WHERE status = 3 AND contractor_id=:contractor_id AND request_type=2 AND r.projid=:projid");
                                            $query_rsPayement_reuests->execute(array(":contractor_id" => $user_name, ":projid" => $projid));
                                            $total_rsPayement_reuests = $query_rsPayement_reuests->rowCount();
                                            if ($total_rsPayement_reuests > 0) {
                                                $counter = 0;
                                                while ($rows_rsPayement_reuests = $query_rsPayement_reuests->fetch()) {
                                                    $costline_id = $rows_rsPayement_reuests['id'];
                                                    $projid = $rows_rsPayement_reuests['projid'];
                                                    $request_id = $rows_rsPayement_reuests['request_id'];
                                                    $date_requested = $rows_rsPayement_reuests['created_at'];
                                                    $date_paid = $rows_rsPayement_reuests['date_paid'];
                                                    $created_by = $rows_rsPayement_reuests['created_by'];
                                                    $amount_paid = $rows_rsPayement_reuests['requested_amount'];
                                                    $receipt = $rows_rsPayement_reuests['receipt'];
                                                    $payment_phase_id = $rows_rsPayement_reuests['item_id'];
                                                    $payment_stage = $rows_rsPayement_reuests['stage'];
                                                    $get_user = $db->prepare("SELECT * FROM tbl_projteam2 p INNER JOIN users u ON u.pt_id = p.ptid WHERE u.userid=:user_id");
                                                    $get_user->execute(array(":user_id" => $created_by));
                                                    $count_user = $get_user->rowCount();
                                                    $user = $get_user->fetch();
                                                    $officer = $user['fullname'];

                                                    $query_rsprojects =  $db->prepare("SELECT * FROM  tbl_projects WHERE projid = :projid");
                                                    $query_rsprojects->execute(array('projid' => $projid));
                                                    $rows_rsprojects = $query_rsprojects->fetch();
                                                    $total_rsprojects = $query_rsprojects->rowCount();
                                                    $project_name = $rows_rsprojects['projname'];
                                                    $payment_plan = $rows_rsprojects['payment_plan'];
                                                    $counter++;


                                                    // contractor_name contract_no
                                                    $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid = :projid");
                                                    $query_rsTender->execute(array(":projid" => $projid));
                                                    $row_rsTender = $query_rsTender->fetch();
                                                    $totalRows_rsTender = $query_rsTender->rowCount();
                                                    $contract_no = $totalRows_rsTender > 0 ? $row_rsTender['contractrefno'] : '';

                                                    $query_rsContractor = $db->prepare("SELECT * FROM tbl_contractor WHERE contrid = :contrid");
                                                    $query_rsContractor->execute(array(":contrid" => $user_name));
                                                    $row_rsContractor = $query_rsContractor->fetch();
                                                    $totalRows_rsContractor = $query_rsContractor->rowCount();
                                                    $contractor_name = $totalRows_rsContractor > 0 ? $row_rsContractor['contractor_name'] : '';
                                            ?>
                                                    <tr class="">
                                                        <td style="width:5%"><?= $counter ?></td>
                                                        <td style="width:45%"><?= $project_name ?></td>
                                                        <td style="width:10%"><?= number_format($amount_paid, 2) ?></td>
                                                        <td style="width:10%"><?= date("Y-m-d", strtotime($date_requested)) ?></td>
                                                        <td style="width:10%"><?= $officer ?></td>
                                                        <td style="width:10%"><?= date("d M Y", strtotime($date_paid)) ?></td>
                                                        <td style="width:10%">
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    Options <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a type="button" data-toggle="modal" id="moreItemModalBtn" data-target="#moreItemModal" onclick="get_more_info(<?= $costline_id ?>)">
                                                                            <i class="fa fa-info"></i>More Info
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a type="button" href="http://34.74.197.215/mne/<?= $receipt ?>" download>
                                                                            <i class="fa fa-info"></i> Receipt
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
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
                        <!-- ============================================================== -->
                        <!-- End PAge Content -->
                        <!-- ============================================================== -->
                    </div>
                </div>
            </div>
        </div>

        <!-- end body  -->


        <!-- add item -->
        <div class="modal fade" id="moreItemModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
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
                                                    <?php
                                                    if ($payment_plan == 1) {
                                                    ?>
                                                        <div id="milestones">
                                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                                <label for="payment_phase" class="control-label">Payment Phase:</label>
                                                                <div class="form-line">
                                                                    <input type="text" name="payment_phase" value="" id="payment_phase" class="form-control" readonly>
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
                                                    <?php
                                                    } else if ($payment_plan == 2) {
                                                    ?>
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
                                                    <?php
                                                    } else if ($payment_plan == 3) {
                                                    ?>
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
                                                    <?php
                                                    }
                                                    ?>
                                                </div>
                                            </fieldset>
                                            <fieldset class="scheduler-border">
                                                <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                    <i class="fa fa-comment" aria-hidden="true"></i> Invoice & Remarks
                                                </legend>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="">
                                                    <label for="invoice" class="control-label">Invoice Attachment:</label>
                                                    <div class="form-line">
                                                        <div id="attachment_div"></div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <label class="control-label">Remarks *:</label>
                                                    <br>
                                                    <div class="form-line">
                                                        <p id="comments_div"></p>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- /modal-body -->
                            <div class="modal-footer">
                                <div class="col-md-12 text-center">
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