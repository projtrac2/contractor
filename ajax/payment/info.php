<?php
include '../controller.php';
try {

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

    if (isset($_GET['get_task_based_tasks'])) {
        $projid = $_GET['projid'];
        $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid=:projid ");
        $query_rsProcurement->execute(array(":projid" => $projid));
        $totalRows_rsProcurement = $query_rsProcurement->rowCount();

        $task_amount = 0;
        $tasks = '';
        if ($totalRows_rsProcurement > 0) {
            $plan_counter = 0;
            while ($row_rsProcurement = $query_rsProcurement->fetch()) {
                $plan_counter++;
                $procurement_cost = $row_rsProcurement['unit_cost'];
                $description = $row_rsProcurement['description'];
                $procurement_units = $row_rsProcurement['units_no'];
                $unit = $row_rsProcurement['unit'];
                $site_id = $row_rsProcurement['site_id'];
                $output_id = $row_rsProcurement['outputid'];
                $tender_id = $row_rsProcurement['id'];

                $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE id = :output_id");
                $query_Output->execute(array(":output_id" => $output_id));
                $row_rsOutput = $query_Output->fetch();
                $total_Output = $query_Output->rowCount();
                $output = ($total_Output > 0) ?  $row_rsOutput['indicator_name'] : '';

                $site = 'N/A';
                if ($site_id != 0) {
                    $query_Sites = $db->prepare("SELECT * FROM tbl_project_sites WHERE site_id=:site_id");
                    $query_Sites->execute(array(":site_id" => $site_id));
                    $rows_sites = $query_Sites->rowCount();
                    $row_Sites = $query_Sites->fetch();
                    $site = $rows_sites > 0 ?  $row_Sites['site'] : "N/A";
                }

                $procurement_total_cost = $procurement_cost * $procurement_units;
                $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                $query_rsIndUnit->execute(array(":unit_id" => $unit));
                $row_rsIndUnit = $query_rsIndUnit->fetch();
                $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';

                $task_amount += $procurement_total_cost;
                $tasks .=
                    '<tr>
                    <input type="hidden" name="tender_id[]" value="' . $tender_id . '" />
                    <td> ' . $plan_counter . ' </td>
                    <td>' . $output . ' </td>
                    <td>' . $site . ' </td>
                    <td>' . $description . ' </td>
                    <td>' . number_format($procurement_units, 2) . '  ' . $unit_of_measure . '</td>
                    <td>' . number_format($procurement_cost,2) . '</td>
                    <td>' . number_format($procurement_total_cost, 2) . '</td>
                </tr>';
                $total_cost = $procurement_cost * $procurement_units;
            }
        }
        echo json_encode(array("success" => true, "tasks" => $tasks, "task_amount" => $task_amount));
    }
} catch (PDOException $ex) {
    $result = flashMessage("An error occurred: " . $ex->getMessage());
    echo $ex->getMessage();
}
