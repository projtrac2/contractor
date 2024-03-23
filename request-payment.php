<?php
require('includes/head.php');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if ($permission) {
    try {
        function get_unit_of_measure($unit)
        {
            global $db;
            $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
            $query_rsIndUnit->execute(array(":unit_id" => $unit));
            $row_rsIndUnit = $query_rsIndUnit->fetch();
            $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
            return $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
        }

        function get_payment_details($site_id, $subtask_id, $request_id)
        {
            global $db;
            $query_rsPayment_Requests = $db->prepare("SELECT * FROM tbl_contractor_payment_request_details WHERE site_id=:site_id AND subtask_id=:subtask_id AND payment_request_id=:payment_request_id");
            $query_rsPayment_Requests->execute(array(":site_id" => $site_id, ":subtask_id" => $subtask_id, ":payment_request_id" => $request_id));
            $totalRows_rsPayment_Requests = $query_rsPayment_Requests->rowCount();
            $row_rsPayment_Requests = $query_rsPayment_Requests->fetch();
            $units_no = $unit_cost = $total_cost = 0;
            if ($totalRows_rsPayment_Requests > 0) {
                $units_no = $row_rsPayment_Requests['units_no'];
                $unit_cost = $row_rsPayment_Requests['unit_cost'];
                $total_cost = $units_no * $unit_cost;
            }

            return array("units_no" => $units_no, "unit_cost" => $unit_cost, "total_cost" => $total_cost);
        }

        if (isset($_POST['store_remarks'])) {
            $request_id = $_POST['request_id'];
            $comments = $_POST['comments'];
            $projid = $_POST['projid'];
            $status = 1;
            $stage = 1;
            $invoice_path = '';
            $date_requested = date("Y-m-d");
            $msg = "Request created successfully";
            if (!empty($_FILES['invoice']['name'])) {
                $filename = basename($_FILES['invoice']['name']);
                $ext = substr($filename, strrpos($filename, '.') + 1);
                if (($ext != "exe") && ($_FILES["invoice"]["type"] != "application/x-msdownload")) {
                    $newname = time() . '_' . $projid . "_" . $stage . "_" . $filename;
                    $filepath = "./uploads/payments/" . $newname;
                    if (!file_exists($filepath)) {
                        if (move_uploaded_file($_FILES['invoice']['tmp_name'], $filepath)) {
                            $invoice_path = $newname;
                        } else {
                            $msg =  "file culd not be  allowed";
                        }
                    } else {
                        $msg = 'File you are uploading already exists, try another file!!';
                    }
                } else {
                    $msg = 'This file type is not allowed, try another file!!';
                }
            }

            $query_rsRequestDetails = $db->prepare("SELECT  SUM(units_no * unit_cost) as requested_amount FROM  tbl_contractor_payment_request_details WHERE payment_request_id=:request_id");
            $query_rsRequestDetails->execute(array(":request_id" => $request_id));
            $row_rsRequestDetails = $query_rsRequestDetails->fetch();
            $requested_amount = !is_null($row_rsRequestDetails['requested_amount']) ?  $row_rsRequestDetails['requested_amount'] : 0;

            $sql = $db->prepare("UPDATE tbl_contractor_payment_requests  SET requested_amount=:requested_amount,  status=1, stage=1, invoice=:invoice_path WHERE id=:request_id");
            $result = $sql->execute(array(":requested_amount" => $requested_amount, ":invoice_path" => $invoice_path, ":request_id" => $request_id));

            $sql = $db->prepare("INSERT INTO tbl_contractor_payment_request_comments (request_id,stage,status,comments,created_by,created_at) VALUES (:request_id,:stage,:status,:comments,:created_by,:created_at)");
            $result  = $sql->execute(array(":request_id" => $request_id, ":stage" => $stage, ":status" => $status, ":comments" => $comments, ":created_by" => $user_name, ":created_at" => $date_requested));

            $url = "payment?projid=" . base64_encode("projid54321{$projid}");
            $results = "<script type=\"text/javascript\">
            swal({
                    title: \"Success!\",
                    text: \" $msg\",
                    type: 'Success',
                    timer: 2000,
                    'icon':'success',
                showConfirmButton: false });
                setTimeout(function(){
                    window.location.href = '$url';
                }, 2000);
            </script>";
        }

        if (isset($_GET['request_id'])) {
            $encoded_request_id = $_GET['request_id'];
            $decode_request_id = base64_decode($encoded_request_id);
            $request_id_array = explode("projid54321", $decode_request_id);
            $request_id = $request_id_array[1];

            $query_rsPayement_requests =  $db->prepare("SELECT * FROM  tbl_contractor_payment_requests WHERE id=:request_id LIMIT 1");
            $query_rsPayement_requests->execute(array("request_id" => $request_id));
            $rows_rsPayement_requests = $query_rsPayement_requests->fetch();
            $total_rsPayement_requests = $query_rsPayement_requests->rowCount();

            if ($total_rsPayement_requests > 0) {
                $projid = $rows_rsPayement_requests['projid'];

                $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid = :projid");
                $query_rsProjects->execute(array(":projid" => $projid));
                $row_rsProjects = $query_rsProjects->fetch();
                $totalRows_rsProjects = $query_rsProjects->rowCount();
                if ($totalRows_rsProjects > 0) {
                    $implimentation_type = $row_rsProjects['projcategory'];
                    $projname = $row_rsProjects['projname'];
                    $projcode = $row_rsProjects['projcode'];
                    $projcost = $row_rsProjects['projcost'];

                    $redirect_url = 'payment.php?projid=' . base64_encode("projid54321{$projid}");
?>
                    <!-- start body  -->
                    <div class="container-fluid">
                        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                            <h4 class="contentheader">
                                <i class="fa fa-money" style="color:white"></i> Payment
                                <div class="btn-group" style="float:right">
                                    <div class="btn-group" style="float:right">
                                        <a type="button" id="outputItemModalBtnrow" href="<?= $redirect_url ?>" class="btn btn-warning pull-right">
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
                                                    <li class="list-group-item"><strong>Total Project Cost: </strong> Ksh. <?php echo number_format($projcost, 2); ?> </li>
                                                    <li class="list-group-item"><strong>Total Project Year Budget: </strong> Ksh. <?php echo number_format($projcost, 2); ?> </li>
                                                </ul>
                                            </div>
                                        </div>
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
                                        $edit = '2';
                                ?>
                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-list-ol" aria-hidden="true"></i> Site <?= $counter ?> : <?= $site ?>
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
                                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                                <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $output_counter ?> : <?= $output ?>
                                                            </legend>
                                                            <?php
                                                            $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                                                            $query_rsMilestone->execute(array(":output_id" => $output_id));
                                                            $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                                            if ($totalRows_rsMilestone > 0) {
                                                                while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                                    $milestone = $row_rsMilestone['milestone'];
                                                                    $msid = $row_rsMilestone['msid'];
                                                            ?>
                                                                    <div class="row clearfix">
                                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                            <div class="card-header">
                                                                                <div class="row clearfix">
                                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                        <ul class="list-group">
                                                                                            <li class="list-group-item list-group-item list-group-item-action active">Task: <?= $milestone ?></li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="table-responsive">
                                                                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th style="width:5%">#</th>
                                                                                            <th style="width:40%">Item </th>
                                                                                            <th style="width:25%">Unit of Measure</th>
                                                                                            <th style="width:10%">No. of Units</th>
                                                                                            <th style="width:10%">Unit Cost (Ksh)</th>
                                                                                            <th style="width:10%">Total Cost (Ksh)</th>
                                                                                            <th>Action</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php
                                                                                        $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE tasks=:task_id AND site_id=:site_id ");
                                                                                        $query_rsOther_cost_plan->execute(array(":task_id" => $msid, ':site_id' => $site_id));
                                                                                        $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();
                                                                                        if ($totalRows_rsOther_cost_plan > 0) {
                                                                                            $table_counter = 0;
                                                                                            while ($row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch()) {
                                                                                                $table_counter++;
                                                                                                $direct_cost_id = $row_rsOther_cost_plan['id'];
                                                                                                $subtask_id = $row_rsOther_cost_plan['subtask_id'];
                                                                                                $description = $row_rsOther_cost_plan['description'];
                                                                                                $unit = $row_rsOther_cost_plan['unit'];
                                                                                                $unit_of_measure = get_unit_of_measure($unit);

                                                                                                $request_details = get_payment_details($site_id, $subtask_id, $request_id);
                                                                                                $units_no = $request_details['units_no'];
                                                                                                $unit_cost = $request_details['unit_cost'];
                                                                                                $total_cost = $request_details['total_cost'];
                                                                                                if ($subtask_id == 0 || $units_no > 0) {
                                                                                        ?>
                                                                                                    <tr id="row">
                                                                                                        <td style="width:5%"><?= $table_counter ?></td>
                                                                                                        <td style="width:40%"><?= $description ?></td>
                                                                                                        <td style="width:25%"><?= $unit_of_measure . " " . $request_id ?></td>
                                                                                                        <td style="width:10%"><?= number_format($units_no) ?></td>
                                                                                                        <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                                        <td style="width:10%"><?= number_format($total_cost, 2) ?></td>
                                                                                                        <td style="width:10%">
                                                                                                            <?php
                                                                                                            if ($subtask_id == 0) {
                                                                                                            ?>
                                                                                                                <button type="button" data-toggle="modal" data-target="#addFormModal" data-backdrop="static" data-keyboard="false" onclick="add_budgetline(<?= $direct_cost_id ?>)" class="btn btn-success btn-sm" style="float:right; margin-top:-5px">
                                                                                                                    <?php echo $totalRows_rsPayment_Requests > 0 ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>' ?>
                                                                                                                </button>
                                                                                                            <?php
                                                                                                            }
                                                                                                            ?>
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
                                    ?>
                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $counter ?> : <?= $output ?>
                                            </legend>
                                            <?php
                                            $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                                            $query_rsMilestone->execute(array(":output_id" => $output_id));
                                            $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                            if ($totalRows_rsMilestone > 0) {
                                                while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                    $milestone = $row_rsMilestone['milestone'];
                                                    $msid = $row_rsMilestone['msid'];
                                                    $site_id = 0;
                                            ?>
                                                    <div class="row clearfix">
                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                            <div class="card-header">
                                                                <div class="row clearfix">
                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                        <ul class="list-group">
                                                                            <li class="list-group-item list-group-item list-group-item-action active">Task: <?= $milestone ?></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="width:5%">#</th>
                                                                            <th style="width:40%">Item</th>
                                                                            <th style="width:25%">Unit of Measure</th>
                                                                            <th style="width:10%">No. of Units</th>
                                                                            <th style="width:10%">Unit Cost (Ksh)</th>
                                                                            <th style="width:10%">Total Cost (Ksh)</th>
                                                                            <th style="width:10%">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE tasks=:task_id AND site_id=:site_id ");
                                                                        $query_rsOther_cost_plan->execute(array(":task_id" => $msid, ':site_id' => $site_id));
                                                                        $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();
                                                                        if ($totalRows_rsOther_cost_plan > 0) {
                                                                            $table_counter = 0;
                                                                            while ($row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch()) {
                                                                                $table_counter++;
                                                                                $direct_cost_id = $row_rsOther_cost_plan['id'];
                                                                                $subtask_id = $row_rsOther_cost_plan['subtask_id'];
                                                                                $description = $row_rsOther_cost_plan['description'];
                                                                                $unit = $row_rsOther_cost_plan['unit'];
                                                                                $unit_of_measure = get_unit_of_measure($unit);
                                                                                $request_details = get_payment_details($site_id, $subtask_id, $request_id);
                                                                                $units_no = $request_details['units_no'];
                                                                                $unit_cost = $request_details['unit_cost'];
                                                                                $total_cost = $request_details['total_cost'];
                                                                                if ($subtask_id == 0 || $units_no > 0) {
                                                                        ?>
                                                                                    <tr id="row">
                                                                                        <td style="width:5%"><?= $table_counter ?></td>
                                                                                        <td style="width:40%"><?= $description ?></td>
                                                                                        <td style="width:25%"><?= $unit_of_measure ?></td>
                                                                                        <td style="width:10%"><?= number_format($units_no) ?></td>
                                                                                        <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                        <td style="width:10%"><?= number_format($total_cost, 2) ?></td>
                                                                                        <td style="width:10%">
                                                                                            <?php
                                                                                            if ($subtask_id == 0) {
                                                                                            ?>
                                                                                                <button type="button" data-toggle="modal" data-target="#addFormModal" data-backdrop="static" data-keyboard="false" onclick="add_budgetline(<?= $direct_cost_id ?>)" class="btn btn-success btn-sm" style="float:right; margin-top:-5px">
                                                                                                    <?php echo $totalRows_rsPayment_Requests > 0 ? '<span class="glyphicon glyphicon-pencil"></span>' : '<span class="glyphicon glyphicon-plus"></span>' ?>
                                                                                                </button>
                                                                                            <?php
                                                                                            }
                                                                                            ?>
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
                                }
                                ?>
                                <fieldset class="scheduler-border" id="direct_cost">
                                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                        <i class="fa fa-calendar" aria-hidden="true"></i> Request Details
                                    </legend>
                                    <form role="form" id="form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
                                        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                                            <label for="invoice" class="control-label">Invoice Attachment:</label>
                                            <div class="form-line">
                                                <input type="file" name="invoice" value="" id="invoice" class="form-control" required>
                                            </div>
                                        </div>
                                        <div id="comment_section">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <label class="control-label">Remarks *:</label>
                                                <br>
                                                <div class="form-line">
                                                    <textarea name="comments" cols="" rows="7" class="form-control" id="comment" placeholder="Enter Comments if necessary" style="width:98%; color:#000; font-size:12px; font-family:Verdana, Geneva, sans-serif"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix" style="margin-top:5px; margin-bottom:5px">
                                            <div class="col-md-12 text-center">
                                                <input type="hidden" name="projid" value="<?= $projid ?>">
                                                <input type="hidden" name="request_id" value="<?= $request_id ?>">
                                                <input type="hidden" name="store_remarks" value="store_remarks">
                                                <button type="submit" class="btn btn-success">Request</button>
                                            </div>
                                        </div>
                                    </form>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <!-- add item -->
                    <div class="modal fade" id="addFormModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form class="form-horizontal" id="modal_form_submit1" action="" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header" style="background-color:#03A9F4">
                                        <h4 class="modal-title" style="color:#fff" align="center" id="addModal"><i class="fa fa-plus"></i> <span id="modal_info">Add Other Details</span></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="card">
                                            <div class="row clearfix">
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                    <div class="body" id="add_modal_form">
                                                        <fieldset class="scheduler-border">
                                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                                <i class="fa fa-calendar" aria-hidden="true"></i> Budgetline Details
                                                            </legend>
                                                            <div class="row clearfix">
                                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="budgetline_div">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="width:30%">Description </th>
                                                                                    <th style="width:15%">Unit</th>
                                                                                    <th style="width:10%">Remaining Units</th>
                                                                                    <th style="width:10%">No. of Units</th>
                                                                                    <th style="width:15%">Unit Cost</th>
                                                                                    <th style="width:15%">Total Cost</th>
                                                                                    <th style="width:5%">
                                                                                        <button type="button" name="addplus" id="addplus_financier" onclick="add_budget_costline(0);" class="btn btn-success btn-sm">
                                                                                            <span class="glyphicon glyphicon-plus">
                                                                                            </span>
                                                                                        </button>
                                                                                    </th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="_budget_lines_values_table">
                                                                                <tr></tr>
                                                                                <tr id="e_removeTr" class="text-center">
                                                                                    <td colspan="5">Add Budgetline Costlines</td>
                                                                                </tr>
                                                                            </tbody>
                                                                            <tfoot id="budget_line_foot">
                                                                                <tr>
                                                                                    <td colspan="1"><strong>Sub Total</strong></td>
                                                                                    <td colspan="1">
                                                                                        <input type="text" name="subtotal_amount1" value="" id="sub_total_amount" class="form-control" placeholder="Total sub-total" style="height:35px; width:99%; color:#000; font-size:12px; font-family:Verdana, Geneva, sans-serif" disabled>
                                                                                    </td>
                                                                                    <td colspan="1"> <strong>% Sub Total</strong></td>
                                                                                    <td colspan="2">
                                                                                        <input type="text" name="subtotal_percentage" value="%" id="subtotal_percentage" class="form-control" placeholder="% sub-total" style="height:35px; width:99%; color:#000; font-size:12px; font-family:Verdana, Geneva, sans-serif" disabled>
                                                                                    </td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- /modal-body -->
                                        <div class="modal-footer">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                                                <input type="hidden" name="projid" id="project_id" value="<?= $projid ?>">
                                                <input type="hidden" name="site_id" id="site_id" value="">
                                                <input type="hidden" name="output_id" id="output_id" value="">
                                                <input type="hidden" name="task_id" id="task_id" value="">
                                                <input type="hidden" name="amount_requested" id="amount_requested" value="">
                                                <input type="hidden" name="request_id" id="request_id" value="<?= $request_id ?>">
                                                <input type="hidden" name="implementation_type" id="implementation_type" value="">
                                                <input type="hidden" name="store" id="store" value="new">
                                                <button name="save" type="" class="btn btn-primary waves-effect waves-light" id="modal-form-submit" value="">
                                                    Save
                                                </button>
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
            } else {
                $results =  restriction();
                echo $results;
            }
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
<!-- <script src="assets/js/payment/amend.js"></script> -->


<script>
    var ajax_url = "ajax/payments/amend";
    $(document).ready(function() {
        $("#modal_form_submit1").submit(function(e) {
            e.preventDefault();
            var cost_type = $("#purpose1").val();
            $.ajax({
                type: "post",
                url: ajax_url,
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        success_alert("Record created successfully");
                    } else {
                        error_alert(" Error  occured please try again later!!");
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


    //function to put commas to the data
    function commaSeparateNumber(val) {
        while (/(\d+)(\d{3})/.test(val.toString())) {
            val = val.toString().replace(/(\d+)(\d{3})/, "$1" + "," + "$2");
        }
        return val;
    }

    const add_request_details = (details) => {
        var projid = details.projid;
        var site_id = details.site_id;
        var output_id = details.output_id;
        var task_id = details.task_id;
        var cost_type = details.cost_type;
        var request_id = details.request_id;
        $("#projid").val(projid);
        $("#site_id").val(site_id);
        $("#output_id").val(output_id);
        $("#task_id").val(task_id);
        $("#cost_type").val(cost_type);

        $.ajax({
            type: "get",
            url: ajax_url,
            data: {
                get_budget_lines: "get_browser_budget_lines",
                projid: projid,
                site_id: site_id,
                output_id: output_id,
                task_id: task_id,
                cost_type: cost_type,
                request_id: request_id,
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#_budget_lines_values_table").html(response.table_body);
                    calculate_subtotal();
                } else {
                    error_alert("Sorry error occured");
                }
            }
        });
    }

    function calculate_total_cost(rowno) {
        var unit_cost = $(`#unit_cost${rowno}`).val();
        var no_units = $(`#no_units${rowno}`).val();
        var h_no_units = $(`#h_no_units${rowno}`).val();
        var ceiling_amount = $("#ceiling_amount").val();

        unit_cost = (unit_cost != "") ? parseFloat(unit_cost) : 0;
        no_units = (no_units != "") ? parseFloat(no_units) : 0;
        h_no_units = (h_no_units != "") ? parseFloat(h_no_units) : 0;
        var total = 0;
        if (h_no_units >= no_units && no_units > 0) {
            if (ceiling_amount != "") {
                ceiling_amount = parseFloat(ceiling_amount);
                total = no_units * unit_cost;

                $(`#subtotal_amount${rowno}`).val(total);
                var total_amount = 0;
                $(`.subamount`).each(function() {
                    if ($(this).val() != "") {
                        total_amount = total_amount + parseFloat($(this).val());
                    }
                });
                var remainder = ceiling_amount - total_amount;
                if (remainder < 0) {
                    $(`#no_units${rowno}`).val("");
                    total = 0;
                }
            } else {
                error_alert("Error check the data");
                $(`#no_units${rowno}`).val("");
            }
        } else {
            error_alert("Sorry ensure that the number of units is less or equal to the number of units specified");
            $(`#no_units${rowno}`).val("");
        }
        $(`#subtotal_amount${rowno}`).val(total);
        $(`#subtotal_cost${rowno}`).html(commaSeparateNumber(total));
        calculate_subtotal(rowno);
    }

    function calculate_subtotal() {
        var ceiling_amount = $("#ceiling_amount").val();
        var total_amount = 0;
        $(`.sub`).each(function() {
            if ($(this).val() != "") {
                total_amount = total_amount + parseFloat($(this).val());
            }
        });
        var percentage = ((total_amount / ceiling_amount) * 100);
        $(`#sub_total_amount`).val(commaSeparateNumber(total_amount));
        $(`#subtotal_percentage`).val(percentage);
    }

    // function to add new rowfor financiers
    function add_budget_costline() {
        var stage = $("#stage").val();
        var purpose = $("#purpose1").val();
        var result = false;
        var task = '';
        result = false;
        var msg = "Please select a purpose first";
        if (purpose != '') {
            result = true;
            if (stage == '10') {
                if (purpose == 1) {
                    task = $("#tasks").val();
                    result = task != '' ? true : false;
                    msg = task == '' ? "Please select a task first" : '';
                }
            }
        }

        if (result) {
            $(`#e_removeTr`).remove(); //new change
            $rowno = $(`#_budget_lines_values_table tr`).length;
            $rowno += 1;
            $rowno = $rowno;
            $(`#_budget_lines_values_table tr:last`).after(`
        <tr id="budget_line_cost_line${$rowno}">
            <td>
                <select name="direct_cost_id[]" id="description${$rowno}" data-id="${$rowno}" onchange="get_costline_details(${$rowno})" class="form-control show-tick description" style="border:1px #CCC thin solid; border-radius:5px" data-live-search="false" required="required">
                    <option value="">.... Select from list ....</option>
                </select>
            </td>
            <td id="unit${$rowno}"> </td>
            <td>
                <input type="number" name="unit_cost[]" min="0" class="form-control " id="unit_cost${$rowno}" onchange="calculate_total_cost(${$rowno})" onkeyup="calculate_total_cost(${$rowno})">
            </td>
            <td>
                <input type="number" name="no_units[]" min="0" class="form-control " onchange="calculate_total_cost(${$rowno})" onkeyup="calculate_total_cost(${$rowno})" id="no_units${$rowno}">
            </td>
                <td>
                <input type="hidden" name="h_no_units[]" id="h_no_units${$rowno}">
                <input type="text" name="p_no_units[]" min="0" class="form-control " id="p_no_units${$rowno}" readonly>
            </td>
            <td>
                    <input type="hidden" name="subtotal_amount[]" id="subtotal_amount${$rowno}" class="subamount sub" value="">
                    <span id="subtotal_cost${$rowno}" style="color:red"></span>
            </td>
            <td style="width:2%">
                <button type="button" class="btn btn-danger btn-sm" id="delete" onclick="delete_budget_costline('budget_line_cost_line${$rowno}', '')">
                    <span class="glyphicon glyphicon-minus"></span>
                </button>
            </td>
        </tr>`);
            get_budgetline_details($rowno, purpose);
        } else {
            error_alert(msg);
        }
    }

    // function to delete financiers row
    function delete_budget_costline(rowno) {
        $("#" + rowno).remove();
        var check = $(`#_budget_lines_values_table tr`).length;
        if (check == 1) {
            $(`#_budget_lines_values_table`).html(`
        <tr></tr>
            <tr id="e_removeTr" class="text-center">
            <td colspan="7">Add Budgetline Costlines</td>
        </tr>`);
        }
        calculate_subtotal();
    }

    function get_budgetline_details(rowno, task_id, cost_type) {
        var projid = $("#project_id").val();
        var output_id = $("#output").val();
        var site_id = $("#site_id").val();
        var task_id = $("#tasks").val();
        var stage = $("#stage").val();
        var cost_type = $("#implementation_type").val();
        var purpose = $("#purpose1").val();

        if (projid != "") {
            $.ajax({
                type: "get",
                url: ajax_url,
                data: {
                    get_budgetline_info: "get_budgetline_info",
                    projid: projid,
                    output_id: output_id,
                    site_id: site_id,
                    task_id: task_id,
                    stage: stage,
                    purpose: purpose,
                    cost_type: cost_type,
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $(`#description${rowno}`).html(response.description);
                    } else {
                        console.log("Error please ensure that you use the correct project")
                    }
                }
            });
        } else {
            console.log("Ensure you have the correct project");
        }
    }
</script>