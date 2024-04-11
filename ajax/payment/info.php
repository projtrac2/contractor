<?php
include '../controller.php';
try {

    function get_milestone_based_details($projid, $payment_plan_id)
    {
        global $db;
        $query_rsPayment_plan = $db->prepare("SELECT * FROM tbl_project_payment_plan WHERE id=:payment_plan_id");
        $query_rsPayment_plan->execute(array(":payment_plan_id" => $payment_plan_id));
        $Rows_rsPayment_plan = $query_rsPayment_plan->fetch();
        $totalRows_rsPayment_plan = $query_rsPayment_plan->rowCount();
        $milestones = '';
        $payment_plan = '';
        if ($totalRows_rsPayment_plan > 0) {
            $payment_plan_id = $Rows_rsPayment_plan['id'];
            $payment_plan = $Rows_rsPayment_plan['payment_plan'];
            $percentage = $Rows_rsPayment_plan['percentage'];

            $query_rsProcurement =  $db->prepare("SELECT SUM(unit_cost*units_no) as budget FROM tbl_project_tender_details WHERE projid=:projid ");
            $query_rsProcurement->execute(array(":projid" => $projid));
            $row_rsProcurement = $query_rsProcurement->fetch();
            $budget = $row_rsProcurement['budget'] != null ? $row_rsProcurement['budget'] : 0;
            $request_amount = ($percentage / 100) * $budget;

            $query_rsPayment_plan_details = $db->prepare("SELECT * FROM tbl_project_payment_plan_details d INNER JOIN tbl_project_milestone m ON m.id =d.milestone_id  WHERE m.projid=:projid AND payment_plan_id=:payment_plan_id ");
            $query_rsPayment_plan_details->execute(array(":projid" => $projid, ":payment_plan_id" => $payment_plan_id));
            $totalRows_rsPayment_plan_details = $query_rsPayment_plan_details->rowCount();
            if ($totalRows_rsPayment_plan_details > 0) {
                $counter = 0;
                while ($Rows_rsPayment_plan_details = $query_rsPayment_plan_details->fetch()) {
                    $counter++;
                    $milestone =  $Rows_rsPayment_plan_details['milestone'];
                    $milestones .= '
                        <tr>
                            <td>' . $counter . '</td>
                            <td>' . $milestone . '</td>
                        </tr>';
                }
            }
        }

        return array("success" => true, "milestones" => $milestones, 'payment_plan' => $payment_plan, "request_percentage" => number_format($percentage, 2), "request_amount" => number_format($request_amount, 2));
    }

    if (isset($_GET['get_more_info'])) {
        $request_id = $_GET['request_id'];
        $query_rsPayement_reuests =  $db->prepare("SELECT * FROM  tbl_contractor_payment_requests WHERE id=:request_id");
        $query_rsPayement_reuests->execute(array(":request_id" => $request_id));
        $rows_rsPayement_reuests = $query_rsPayement_reuests->fetch();
        $total_rsPayement_reuests = $query_rsPayement_reuests->rowCount();
        $data = [];
        $attachment = $comments = '';
        if ($total_rsPayement_reuests > 0) {
            $payment_plan_id = $rows_rsPayement_reuests['item_id'];
            $project_plan = $rows_rsPayement_reuests['project_plan'];
            $projid = $rows_rsPayement_reuests['projid'];
            $invoice =  $rows_rsPayement_reuests['invoice'];
            $attachment = $invoice != '' ? '<a href="' . $invoice . '" download><i class="fa fa-download" aria-hidden="true"></i>Download</a>' : '';

            $query_rsPayement_reuests_comments =  $db->prepare("SELECT * FROM  tbl_contractor_payment_request_comments WHERE request_id=:request_id AND stage=1 LIMIT 1");
            $query_rsPayement_reuests_comments->execute(array(":request_id" => $request_id));
            $rows_rsPayement_reuests_comments = $query_rsPayement_reuests_comments->fetch();
            $total_rsPayement_reuests_comments = $query_rsPayement_reuests_comments->rowCount();
            $comments = $total_rsPayement_reuests_comments > 0 ? $rows_rsPayement_reuests_comments['comments'] : "";

            if ($project_plan == 1) {
                $data = get_milestone_based_details($projid, $payment_plan_id);
            } else {
            }
        }
        echo json_encode(array("details" => $data, "comments" => $comments, "attachment" => $attachment));
    }

    if (isset($_GET['get_payment_phase_milestones'])) {
        $projid = $_GET['projid'];
        $payment_plan_id = $_GET['payment_phase'];

        $query_rsProcurement =  $db->prepare("SELECT SUM(unit_cost*units_no) as budget FROM tbl_project_tender_details WHERE projid=:projid ");
        $query_rsProcurement->execute(array(":projid" => $projid));
        $row_rsProcurement = $query_rsProcurement->fetch();
        $totalRows_rsProcurement = $query_rsProcurement->rowCount();

        $query_rsPayment_plan = $db->prepare("SELECT * FROM tbl_project_payment_plan WHERE id=:payment_plan_id");
        $query_rsPayment_plan->execute(array(":payment_plan_id" => $payment_plan_id));
        $Rows_rsPayment_plan = $query_rsPayment_plan->fetch();
        $totalRows_rsPayment_plan = $query_rsPayment_plan->rowCount();

        $milestones = '';
        if ($totalRows_rsPayment_plan > 0) {
            $payment_plan_id = $Rows_rsPayment_plan['id'];
            $payment_plan = $Rows_rsPayment_plan['payment_plan'];
            $percentage = $Rows_rsPayment_plan['percentage'];

            $budget = $row_rsProcurement['budget'] != null ? $row_rsProcurement['budget'] : 0;
            $request_amount = ($percentage / 100) * $budget;

            $query_rsPayment_plan_details = $db->prepare("SELECT * FROM tbl_project_payment_plan_details d INNER JOIN tbl_project_milestone m ON m.id =d.milestone_id  WHERE m.projid=:projid AND payment_plan_id=:payment_plan_id ");
            $query_rsPayment_plan_details->execute(array(":projid" => $projid, ":payment_plan_id" => $payment_plan_id));
            $totalRows_rsPayment_plan_details = $query_rsPayment_plan_details->rowCount();
            if ($totalRows_rsPayment_plan_details > 0) {
                $counter = 0;
                while ($Rows_rsPayment_plan_details = $query_rsPayment_plan_details->fetch()) {
                    $counter++;
                    $milestone =  $Rows_rsPayment_plan_details['milestone'];
                    $milestones .= '
                    <tr>
                        <td>' . $counter . '</td>
                        <td>' . $milestone . '</td>
                    </tr>';
                }
            }
        }

        echo json_encode(array("success" => true, "milestones" => $milestones, "request_percentage" => number_format($percentage, 2), "request_amount" => $request_amount));
    }

    function get_task_based_tasks($projid, $request_id)
    {
        global $db;
        $details = '';
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
                $details .= '
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                        <i class="fa fa-list-ol" aria-hidden="true"></i> Site <?= $counter ?> : <?= $site ?>
                    </legend>';
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
                            $details .= '
                                <fieldset class="scheduler-border">
                                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                        <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $output_counter ?> : <?= $output ?>
                                    </legend>';

                            $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                            $query_rsMilestone->execute(array(":output_id" => $output_id));
                            $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                            if ($totalRows_rsMilestone > 0) {
                                while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                    $milestone = $row_rsMilestone['milestone'];
                                    $msid = $row_rsMilestone['msid'];
                                    $details .= '
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
                                                            <tbody>';
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

                                            $request_details = get_payment_details($request_id, $direct_cost_id);
                                            $units_no = $request_details['units_no'];
                                            $unit_cost = $request_details['unit_cost'];
                                            $total_cost = $request_details['total_cost'];
                                            if ($subtask_id == 0 || $units_no > 0) {
                                                $details .= '
                                                                            <tr id="row">
                                                                                <td style="width:5%"><?= $table_counter ?></td>
                                                                                <td style="width:40%"><?= $description ?></td>
                                                                                <td style="width:25%"><?= $unit_of_measure . " " . $request_id ?></td>
                                                                                <td style="width:10%"><?= number_format($units_no) ?></td>
                                                                                <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                <td style="width:10%"><?= number_format($total_cost, 2) ?></td>';

                                                $details .= '
                                                                            </tr>';
                                            }
                                        }
                                    }
                                    $details .= '
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>';
                                }
                            }
                            $details .= ' </fieldset>';
                        }
                    }
                }
                $details .= '</fieldset>';
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
                $details .= '
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                        <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $counter ?> : <?= $output ?>
                    </legend>';
                $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                $query_rsMilestone->execute(array(":output_id" => $output_id));
                $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                if ($totalRows_rsMilestone > 0) {
                    while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                        $milestone = $row_rsMilestone['milestone'];
                        $msid = $row_rsMilestone['msid'];
                        $site_id = 0;

                        $details .= '
                            <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="card-header">
                                        <div class="row clearfix">
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <ul class="list-group">
                                                    <li class="list-group-item list-group-item list-group-item-action active">Task: ' . $milestone . '</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table' . $output_id . '">
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
                                            <tbody>';

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
                                $request_details = get_payment_details($request_id, $direct_cost_id);
                                $units_no = $request_details['units_no'];
                                $unit_cost = $request_details['unit_cost'];
                                $total_cost = $request_details['total_cost'];
                                if ($subtask_id == 0 || $units_no > 0) {
                                    $details .= '
                                        <tr id="row">
                                            <td style="width:5%">' . $table_counter . '</td>
                                            <td style="width:40%">' . $description . '</td>
                                            <td style="width:25%">' . $unit_of_measure . '</td>
                                            <td style="width:10%">' . number_format($units_no) . '</td>
                                            <td style="width:10%">' . number_format($unit_cost, 2) . '</td>
                                            <td style="width:10%">' . number_format($total_cost, 2) . '</td>
                                        </tr>';
                                }
                            }
                        }
                        $details .= '
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>';
                    }
                }
                $details .= '</fieldset>';
            }
        }

        return $details;
    }
} catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
    var_dump($ex);
}
