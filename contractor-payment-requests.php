<?php
require('includes/head.php');
if ($permission) {
    try {

?>
        <!-- start body  -->
        <section class="content">
            <div class="container-fluid">
                <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:10px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                    <h4 class="contentheader">
                        <?= $icon ?>
                        <?= $pageTitle ?>
                        <div class="btn-group" style="float:right">
                            <div class="btn-group" style="float:right">
                            </div>
                        </div>
                    </h4>
                </div>
                <div class="row clearfix">
                    <div class="block-header">
                        <?= $results; ?>
                        <div class="header" style="padding-bottom:0px">
                            <div class="button-demo" style="margin-top:-15px">
                                <span class="label bg-black" style="font-size:18px"><img src="images/proj-icon.png" alt="Project Menu" title="Project Menu" style="vertical-align:middle; height:25px" /> Menu</span>
                                <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px">Contractor</a>
                                <a href="inhouse-payment-requests.php" class="btn bg-light-blue waves-effect" style="margin-top:10px">In House</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="card">
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
                                    <div id="menu1" class="tab-pane fade in active">
                                        <div style="color:#333; background-color:#EEE; width:100%; height:30px">
                                            <h4 style="width:100%"><i class="fa fa-hourglass-half fa-sm" style="font-size:25px;color:#6c0eb0"></i> New Requests</h4>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                                <thead>
                                                    <tr class="bg-deep-purple">
                                                        <th style="width:5%">#</th>
                                                        <th style="width:35%">Project Name</th>
                                                        <th style="width:10%">Requested Amount</th>
                                                        <th style="width:10%">Date Requested</th>
                                                        <th style="width:10%">Payment Plan</th>
                                                        <th style="width:10%">Stage</th>
                                                        <th style="width:10%">Status</th>
                                                        <th style="width:10%">More Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $query_rsPayement_reuests =  $db->prepare("SELECT * FROM  tbl_contractor_payment_requests WHERE status <> 3 ");
                                                    $query_rsPayement_reuests->execute();
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
                                                            $contractor_id = $rows_rsPayement_reuests['contractor_id'];

                                                            $query_rsprojects =  $db->prepare("SELECT * FROM  tbl_projects WHERE projid = :projid");
                                                            $query_rsprojects->execute(array(":projid" => $projid));
                                                            $rows_rsprojects = $query_rsprojects->fetch();
                                                            $total_rsprojects = $query_rsprojects->rowCount();
                                                            if ($total_rsprojects > 0) {

                                                                $progid = $rows_rsprojects['progid'];
                                                                $project_name = $rows_rsprojects['projname'];
                                                                $payment_plan = $rows_rsprojects['payment_plan'];
                                                                $contrid = $rows_rsprojects['projcontractor'];
                                                                $stage = "";
                                                                $status  = "Pending";

                                                                if ($payment_stage == 1) {
                                                                    $stage = "Team Leader";
                                                                } else if ($payment_stage == 2) {
                                                                    $stage = "Inspection and Acceptance";
                                                                } else if ($payment_stage == 3) {
                                                                    $stage = "CO Department";
                                                                } else if ($payment_stage == 4) {
                                                                    $status  = "Pending";
                                                                    $stage = "CO Finance";
                                                                } else if ($payment_stage == 5) {
                                                                    $stage = "Director Finance";
                                                                }


                                                                $payment_plan_name = "";
                                                                if ($payment_plan == 1) {
                                                                    $payment_plan_name = "Milestone";
                                                                } else if ($payment_plan == 2) {
                                                                    $payment_plan_name = "Task";
                                                                } else if ($payment_plan == 3) {
                                                                    $payment_plan_name = "Work Measured";
                                                                }

                                                                // contractor_name contract_no
                                                                $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid = :projid");
                                                                $query_rsTender->execute(array(":projid" => $projid));
                                                                $row_rsTender = $query_rsTender->fetch();
                                                                $totalRows_rsTender = $query_rsTender->rowCount();
                                                                $contract_no = $totalRows_rsTender > 0 ? $row_rsTender['contractrefno'] : '';

                                                                $query_rsContractor = $db->prepare("SELECT * FROM tbl_contractor WHERE contrid = :contrid");
                                                                $query_rsContractor->execute(array(":contrid" => $contractor_id));
                                                                $row_rsContractor = $query_rsContractor->fetch();
                                                                $totalRows_rsContractor = $query_rsContractor->rowCount();
                                                                $contractor_name = $totalRows_rsContractor > 0 ? $row_rsContractor['contractor_name'] : '';

                                                                $project_details = "{
                                                                project_name:'$project_name',
                                                                projid: '$projid',
                                                                payment_plan: '$payment_plan',
                                                                request_id: '$costline_id',
                                                                contractor_name:'$contractor_name',
                                                                contract_no:'$contract_no',
                                                                stage:'$payment_stage',
                                                            }";
                                                                $counter++;
                                                    ?>
                                                                <tr class="">
                                                                    <td style="width:5%"><?= $counter ?></td>
                                                                    <td style="width:35%"><?= $project_name ?></td>
                                                                    <td style="width:10%"><?= number_format($amount_paid, 2) ?></td>
                                                                    <td style="width:10%"><?= date("Y-m-d", strtotime($payment_requested_date)) ?></td>
                                                                    <td style="width:10%"><?= $payment_plan_name ?></td>
                                                                    <td style="width:10%"><?= $stage ?></td>
                                                                    <td style="width:10%"><?= $status ?></td>
                                                                    <td style="width:10%">
                                                                        <div class="btn-group">
                                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                Options <span class="caret"></span>
                                                                            </button>
                                                                            <ul class="dropdown-menu">
                                                                                <li>
                                                                                    <a type="button" data-toggle="modal" id="moreItemModalBtn" data-target="#approve_data" onclick="get_details(<?= $project_details ?>, 1)">
                                                                                        <i class="fa fa-info"></i>More Info
                                                                                    </a>
                                                                                </li>
                                                                                <?php
                                                                                if ($payment_stage == 1) {
                                                                                ?>
                                                                                    <li>
                                                                                        <a type="button" data-toggle="modal" id="moreItemModalBtn" data-target="#approve_data" onclick="get_details(<?= $project_details ?>, 2)">
                                                                                            <i class="fa fa-info"></i> Approve
                                                                                        </a>
                                                                                    </li>
                                                                                <?php
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
                                                        <th style="width:10%">Other Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $query_rsPayement_reuests =  $db->prepare("SELECT r.*, d.created_at, d.created_by, d.date_paid,d.receipt  FROM tbl_contractor_payment_requests r INNER JOIN tbl_payments_disbursed d ON d.request_id = r.id WHERE status = 3 AND request_type=2");
                                                    $query_rsPayement_reuests->execute();
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


                                                            $query_rsPayement =  $db->prepare("SELECT SUM(requested_amount) requested_amount FROM tbl_contractor_payment_requests WHERE request_id=:request_id");
                                                            $query_rsPayement->execute(array(":request_id" => $request_id));
                                                            $rows_rsPayement = $query_rsPayement->fetch();
                                                            $total_rsPayement = $query_rsPayement->rowCount();
                                                            $amount_paid = $rows_rsPayement['requested_amount']  != null ? $rows_rsPayement['requested_amount'] : 0;

                                                            $get_user = $db->prepare("SELECT * FROM tbl_projteam2 p INNER JOIN users u ON u.pt_id = p.ptid WHERE u.userid=:user_id");
                                                            $get_user->execute(array(":user_id" => $created_by));
                                                            $count_user = $get_user->rowCount();
                                                            $user = $get_user->fetch();
                                                            $officer = $user['fullname'];

                                                            $query_rsprojects =  $db->prepare("SELECT * FROM  tbl_projects WHERE projid = :projid");
                                                            $query_rsprojects->execute(array('projid' => $projid));
                                                            $rows_rsprojects = $query_rsprojects->fetch();
                                                            $total_rsprojects = $query_rsprojects->rowCount();

                                                            if ($total_rsprojects > 0) {
                                                                $contractor_id = $rows_rsprojects['projcontractor'];
                                                                $progid = $rows_rsprojects['progid'];
                                                                $project_name = $rows_rsprojects['projname'];
                                                                $payment_plan = $rows_rsprojects['payment_plan'];

                                                                // contractor_name contract_no
                                                                $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid = :projid");
                                                                $query_rsTender->execute(array(":projid" => $projid));
                                                                $row_rsTender = $query_rsTender->fetch();
                                                                $totalRows_rsTender = $query_rsTender->rowCount();
                                                                $contract_no = $totalRows_rsTender > 0 ? $row_rsTender['contractrefno'] : '';

                                                                $query_rsContractor = $db->prepare("SELECT * FROM tbl_contractor WHERE contrid = :contrid");
                                                                $query_rsContractor->execute(array(":contrid" => $contractor_id));
                                                                $row_rsContractor = $query_rsContractor->fetch();
                                                                $totalRows_rsContractor = $query_rsContractor->rowCount();
                                                                $contractor_name = $totalRows_rsContractor > 0 ? $row_rsContractor['contractor_name'] : '';



                                                                $project_details = "{
                                                                project_name:'$project_name',
                                                                projid: '$projid',
                                                                payment_plan: '$payment_plan',
                                                                request_id: '$costline_id',
                                                                contractor_name:'$contractor_name',
                                                                contract_no:'$contract_no',
                                                            }";

                                                                $counter++;
                                                    ?>
                                                                <tr class="">
                                                                    <td style="width:5%"><?= $counter ?></td>
                                                                    <td style="width:45%"><?= $project_name ?></td>
                                                                    <td style="width:10%"><?= number_format($amount_paid, 2) ?></td>
                                                                    <td style="width:10%"><?= date("Y-m-d", strtotime($date_requested)) ?></td>
                                                                    <td style="width:10%"><?= $officer ?></td>
                                                                    <td style="width:10%"><?= date("Y-m-d", strtotime($date_paid)) ?></td>
                                                                    <td style="width:10%">
                                                                        <div class="btn-group">
                                                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                Options <span class="caret"></span>
                                                                            </button>
                                                                            <ul class="dropdown-menu">
                                                                                <li>
                                                                                    <a type="button" data-toggle="modal" id="moreItemModalBtn" data-target="#approve_data" onclick="get_details(<?= $costline_id ?>)">
                                                                                        <i class="fa fa-info"></i>More Info
                                                                                    </a>
                                                                                </li>
                                                                                <li>
                                                                                    <a type="button" href="<?= $receipt ?>" download>
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
        </section>
        <!-- end body  -->

        <!-- add item -->
        <!-- add item -->
        <div class="modal fade" id="approve_data" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background-color:#03A9F4">
                        <h4 class="modal-title" style="color:#fff" align="center" id="addModal"><i class="fa fa-plus"></i> <span id="modal_info">Payment Request Details</span></h4>
                    </div>
                    <form class="form-horizontal" id="modal_form_submit" action="" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="col-md-12">
                                <fieldset class="scheduler-border">
                                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                        <i class="fa fa-comment" aria-hidden="true"></i> Request Details
                                    </legend>
                                    <div class="row clearfix" style="margin-top:5px; margin-bottom:5px">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            <label for="outcomeIndicator" class="control-label">Project Name:</label>
                                            <div class="form-line">
                                                <input type="text" name="project_name" value="" id="project_name" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                                            <label for="tasks" class="control-label">Contractor:</label>
                                            <div class="form-line">
                                                <input type="text" name="contractor_name" value="" id="contractor_name" class="form-control" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                            <label for="location" class="control-label">Contract Number:</label>
                                            <div class="form-line">
                                                <input type="text" name="contractor_number" value="" id="contractor_number" class="form-control" readonly>
                                            </div>
                                        </div>

                                        <div id="milestones" style="margin-top:15px; margin-bottom:15px">
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
                                                    <input type="text" name="request_amount" value="" id="request_amount" class="form-control" readonly>
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
                                        <div id="tasks" style="margin-top:15px; margin-bottom:15px">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="table-responsive" style="margin-top:15px; margin-bottom:15px">
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
                                    </div>
                                    <div id="comments_div"></div>
                                </fieldset>
                                <fieldset class="scheduler-border" id="project_approve_div">
                                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                        <i class="fa fa-comment" aria-hidden="true"></i> Remarks
                                    </legend>
                                    <div id="comment_section">
                                        <div class="col-md-12">
                                            <label class="control-label">Remarks *:</label>
                                            <br />
                                            <div class="form-line">
                                                <textarea name="comments" cols="" rows="7" class="form-control" id="comment" placeholder="Enter Comments if necessary" style="width:98%; color:#000; font-size:12px; font-family:Verdana, Geneva, sans-serif"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <!-- /modal-body -->
                        </div> <!-- /modal-content -->
                        <div class="modal-footer">
                            <div class="col-md-12 text-center">
                                <input type="hidden" name="projid" id="projid" value="">
                                <input type="hidden" name="payment_plan" id="payment_plan" value="">
                                <input type="hidden" name="stage" id="stage" value="1">
                                <input type="hidden" name="request_id" id="request_id" value="">
                                <input type="hidden" name="user_name" id="username" value="<?= $user_name ?>">
                                <input type="hidden" name="approve_contractor_payment" id="approve_contractor_payment" value="new">
                                <button name="save" type="" class="btn btn-primary waves-effect waves-light" id="modal-form-submit" value="">Approve</button>
                                <button type="button" class="btn btn-warning waves-effect waves-light" data-dismiss="modal"> Cancel</button>
                            </div>
                        </div> <!-- /modal-footer -->
                    </form>
                </div> <!-- /modal-dailog -->
            </div>
        </div>
        <!-- End add item -->
        <!-- End add item -->
<?php
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
    const ajax_url = "ajax/payments/contractor";
    $(document).ready(function() {
        $("#modal_form_submit").submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: "post",
                url: ajax_url,
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        success_alert("Approved payment successfully");
                    } else {
                        error_alert("Approval error !!");
                    }
                    $(".modal").each(function() {
                        $(this).modal("hide");
                        $(this)
                            .find("form")
                            .trigger("reset");
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                }
            });
        });
    });

    function set_details(details) {
        $("#project_name").val(details.project_name);
        $("#contractor_name").val(details.contractor_name);
        $("#contractor_number").val(details.contract_no);
        $("#request_id").val(details.request_id);
        $("#projid").val(details.projid);
        $("#payment_plan").val(details.payment_plan);
        $("#project_plan").val(details.project_plan);
        $("#stage").val(details.stage);

        $("#milestones").hide();
        $("#tasks").hide();
        var payment_plan = details.payment_plan;

        if (payment_plan == "1") {
            $("#milestones").show();
        } else if (payment_plan == "2") {
            $("#tasks").show();
        } else {
            console.log("payment plan for work measured");
        }
    }

    function get_details(details, plan) {
        set_details(details);
        $("#project_approve_div").hide();
        $("#modal-form-submit").hide();
        if (plan == 2) {
            $("#project_approve_div").show();
            $("#modal-form-submit").show();
        }

        if (details.request_id != "") {
            $.ajax({
                type: "get",
                url: ajax_url,
                data: {
                    get_more_info: "get_more_info",
                    request_id: details.request_id,
                },
                dataType: "json",
                success: function(response) {
                    if (response.details.success) {
                        $("#comments_div").html(response.comments);
                        if (details.payment_plan == '1') {
                            $("#milestone_table").html(response.details.milestones);
                            $("#request_amount").val(response.details.request_amount);
                            $("#request_percentage").val(response.details.request_percentage);
                            $("#requested_amount").val(response.details.request_amount);
                            $("#payment_phase").html(response.details.payment_phases);
                        } else if (details.payment_plan == '2') {
                            $("#tasks_table").html(response.details.tasks);
                            $("#subtotal").html(response.details.task_amount);
                            $("#requested_amount").val(response.details.task_amount);
                        }
                    } else {
                        // sweet_alert("No data found !!!")
                    }
                }
            });
        } else {
            console.log("Ensure that the request is correct");
        }
    }
</script>