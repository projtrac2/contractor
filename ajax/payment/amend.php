<?php
include '../controller.php';
try {

    function get_unit_cost($projid, $direct_cost_id)
    {
        global $db;
        $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid=:projid AND costlineid=:direct_cost_id ");
        $query_rsProcurement->execute(array(":projid" => $projid, ":direct_cost_id" => $direct_cost_id));
        $row_rsProcurement = $query_rsProcurement->fetch();
        $totalRows_rsProcurement = $query_rsProcurement->rowCount();
        return ($totalRows_rsProcurement > 0) ?  $row_rsProcurement['unit_cost'] : 0;
    }

    function get_measurement_unit($unit_id)
    {
        global $db;
        $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
        $query_rsIndUnit->execute(array(":unit_id" => $unit_id));
        $row_rsIndUnit = $query_rsIndUnit->fetch();
        $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
        return $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
    }

    function get_requested_units($projid, $request_id, $direct_cost_id)
    {
        global $db;
        $query_rsPayment =  $db->prepare("SELECT SUM(d.units_no) AS requested_units FROM tbl_contractor_payment_requests r INNER JOIN tbl_contractor_payment_request_details d ON r.id=d.request_id WHERE d.projid=:projid AND d.direct_cost_id=:direct_cost_id  AND r.status<>6 AND request_id<>:request_id");
        $query_rsPayment->execute(array(":projid" => $projid, ":direct_cost_id" => $direct_cost_id, ":request_id" => $request_id));
        $Rows_rsPayment = $query_rsPayment->fetch();
        return !is_null($Rows_rsPayment['requested_units']) ? $Rows_rsPayment['requested_units'] : 0;
    }

    function get_request_units($request_id, $direct_cost_id)
    {
        global $db;
        $query_rsPayement_plan_details =  $db->prepare("SELECT * FROM tbl_contractor_payment_request_details WHERE request_id =:request_id AND direct_cost_id=:direct_cost_id");
        $query_rsPayement_plan_details->execute(array('request_id' => $request_id, ":direct_cost_id" => $direct_cost_id));
        $row_rsPayement_plan_details = $query_rsPayement_plan_details->fetch();
        $total_rsPayement_plan_details = $query_rsPayement_plan_details->rowCount();
        return $total_rsPayement_plan_details > 0 ? $row_rsPayement_plan_details['units_no'] : 0;
    }

    if (isset($_GET['get_budget_lines'])) {
        $direct_cost_id = $_GET['direct_cost_id'];
        $request_id = $_GET['request_id'];
        $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE id=:direct_cost_id");
        $query_rsOther_cost_plan->execute(array(":direct_cost_id" => $direct_cost_id));
        $row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch();
        $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();
        $table_body = '';
        if ($totalRows_rsOther_cost_plan > 0) {
            $description = $row_rsOther_cost_plan['description'];
            $unit_of_measure = get_measurement_unit($row_rsOther_cost_plan['unit']);
            $planned_units = $row_rsOther_cost_plan['units_no'];
            $projid = $row_rsOther_cost_plan['projid'];
            $unit_cost = get_unit_cost($projid, $direct_cost_id);

            $request_units = get_request_units($request_id, $direct_cost_id);

            $total_cost = $request_units > 0 ?  $unit_cost * $request_units : 0;
            $requested_units = get_requested_units($projid, $request_id, $direct_cost_id);
            $remaining_units = $planned_units > 0 ? $planned_units - $requested_units : 0;

            $table_body =
                '<tr id="budget_line_cost_line1">
                <td> ' . $description . ' </td>
                <td> ' . $unit_of_measure . ' </td>
                <td>' . number_format($unit_cost, 2) . '</td>
                <td>
                    <input type="number" name="no_units" min="0" class="form-control" onchange="calculate_total_cost()" onkeyup="calculate_total_cost()" id="no_units" value="' . $request_units . '">
                </td>
                <td>' . $remaining_units . '</td>
                <td>
                    <span id="subtotal_cost" style="color:red">' . $total_cost . '</span>
                </td>
                <input type="hidden" name="unit_cost" min="0" class="form-control " id="unit_cost" value="' . $unit_cost . '">
                <input type="hidden" name="units_balance" id="units_balance" value="' . $remaining_units . '">
            </tr>';
        }
        echo json_encode(array("success" => true, "table_body" => $table_body));
    }

    if (isset($_POST['store'])) {
        $direct_cost_id = $_POST['direct_cost_id'];
        $request_id = $_POST['request_id'];
        $unit_cost = $_POST['unit_cost'];
        $units_no = $_POST['no_units'];
        $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE id=:direct_cost_id");
        $query_rsOther_cost_plan->execute(array(":direct_cost_id" => $direct_cost_id));
        $row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch();
        $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();

        if ($totalRows_rsOther_cost_plan > 0) {
            $projid = $row_rsOther_cost_plan['projid'];
            $site_id = $row_rsOther_cost_plan['site_id'];
            $output_id = $row_rsOther_cost_plan['outputid'];
            $task_id = $row_rsOther_cost_plan['tasks'];
            $subtask_id = 0;
            $query_rsPayment_Requests = $db->prepare("SELECT * FROM tbl_contractor_payment_request_details WHERE site_id=:site_id AND subtask_id=:subtask_id AND request_id=:request_id AND direct_cost_id=:direct_cost_id");
            $query_rsPayment_Requests->execute(array(":site_id" => $site_id, ":subtask_id" => $subtask_id, ":request_id" => $request_id, ":direct_cost_id" => $direct_cost_id));
            $totalRows_rsPayment_Requests = $query_rsPayment_Requests->rowCount();
            if ($totalRows_rsPayment_Requests > 0) {
                $sql = $db->prepare("UPDATE tbl_contractor_payment_request_details SET unit_cost=:unit_cost,units_no=:units_no WHERE request_id=:request_id AND direct_cost_id=:direct_cost_id");
                $results = $sql->execute(array(':unit_cost' => $unit_cost, ':units_no' => $units_no,  ":request_id" => $request_id, ":direct_cost_id" => $direct_cost_id));
            } else {
                $sql = $db->prepare("INSERT INTO tbl_contractor_payment_request_details (projid,output_id,site_id,task_id,subtask_id,direct_cost_id,request_id,unit_cost,units_no) VALUES(:projid,:output_id,:site_id,:task_id,:subtask_id,:direct_cost_id,:request_id,:unit_cost,:units_no)");
                $result = $sql->execute(array(":projid" => $projid, ":output_id" => $output_id, ":site_id" => $site_id, ":task_id" => $task_id, ":subtask_id" => $subtask_id, ":direct_cost_id" => $direct_cost_id, ":request_id" => $request_id,  ":unit_cost" => $unit_cost, ":units_no" => $units_no));
            }
        } else {
        }
        echo json_encode(array("success" => true));
    }
} catch (PDOException $ex) {
    customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
}
