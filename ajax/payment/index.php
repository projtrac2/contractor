<?php
include '../controller.php';
try {

    function incrementalHash($len = 5)
    {
        $charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $base = strlen($charset);
        $result = '';

        $now = explode(' ', microtime())[1];
        while ($now >= $base) {
            $i = $now % $base;
            $result = $charset[$i] . $result;
            $now /= $base;
        }
        return substr($result, -5);
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

    function get_tender_details($projid, $site_id, $subtask_id)
    {
        global $db;
        $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid=:projid AND subtask_id=:subtask_id AND site_id=:site_id ");
        $query_rsProcurement->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
        $row_rsProcurement = $query_rsProcurement->fetch();
        return $row_rsProcurement;
    }

    if (isset($_GET['get_payment_phases'])) {
        $projid = $_GET['projid'];
        $query_rsPayment_plan = $db->prepare("SELECT * FROM tbl_project_payment_plan WHERE projid=:projid");
        $query_rsPayment_plan->execute(array(":projid" => $projid));
        $totalRows_rsPayment_plan = $query_rsPayment_plan->rowCount();

        $options = '<option value="">Select Payment Phase</option>';
        if ($totalRows_rsPayment_plan > 0) {
            while ($Rows_rsPayment_plan = $query_rsPayment_plan->fetch()) {
                $payment_plan_id = $Rows_rsPayment_plan['id'];
                $payment_plan = $Rows_rsPayment_plan['payment_plan'];

                $query_rsPayement_requests =  $db->prepare("SELECT * FROM tbl_contractor_payment_requests WHERE item_id=:item_id");
                $query_rsPayement_requests->execute(array(":item_id" => $payment_plan_id));
                $total_rsPayement_requests = $query_rsPayement_requests->rowCount();

                if ($total_rsPayement_requests == 0) {
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
                    $request_payment = in_array(false, $milestone_complete)  ? false : true;
                    if ($request_payment) {
                        $options .= '<option value="' . $payment_plan_id . '">' . $payment_plan . '</option>';
                    }
                }
            }
        }
        echo json_encode(array("success" => true, "payment_phases" => $options));
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

        echo json_encode(array("success" => true, "milestones" => $milestones, "request_percentage" => number_format($percentage, 2), "amount_request" => number_format($request_amount, 2), "request_amount" => $request_amount));
    }

    if (isset($_GET['get_task_based_tasks'])) {
        $projid = $_GET['projid'];
        $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
        $query_rsMilestone->execute(array(":output_id" => $output_id));
        $row_rsMilestone = $query_rsMilestone->fetch();
        $totalRows_rsMilestone = $query_rsMilestone->rowCount();

        if ($totalRows_rsMilestone > 0) {
        }

        $query_rsTask_Complete = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid AND complete=1");
        $query_rsTask_Complete->execute(array(':projid' => $projid));
        $total_rsTask_Complete = $query_rsTask_Complete->rowCount();
        $tasks = '';
        $plan_counter = 0;
        $task_amount = 0;

        if ($total_rsTask_Complete > 0) {
            while ($rows_rsTask_Complete = $query_rsTask_Complete->fetch()) {
                $subtask_id = $rows_rsTask_Complete['subtask_id'];
                $site_id = $rows_rsTask_Complete['site_id'];
                $output_id = $rows_rsTask_Complete['output_id'];

                $query_rsPayment =  $db->prepare("SELECT * FROM tbl_contractor_payment_requests r INNER JOIN tbl_contractor_payment_request_details d ON d.request_id=:r.request_id WHERE d.projid=:projid AND d.site_id=:site_id AND d.subtask_id=:subtask_id ");
                $query_rsPayment->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
                $totalRows_rsPayment = $query_rsPayment->rowCount();
                $Rows_rsPayment = $query_rsPayment->fetch();
                $task_complete = true;
                if ($totalRows_rsPayment > 0) {
                    $status =  $Rows_rsPayment['status'];
                    $task_complete = $status == 6  ? false : true;
                }

                if ($task_complete) {
                    $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid=:projid AND site_id=:site_id AND subtask_id=:subtask_id");
                    $query_rsProcurement->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
                    $row_rsProcurement = $query_rsProcurement->fetch();
                    $totalRows_rsProcurement = $query_rsProcurement->rowCount();
                    if ($totalRows_rsProcurement > 0) {
                        $procurement_cost = $row_rsProcurement['unit_cost'];
                        $description = $row_rsProcurement['description'];
                        $procurement_units = $row_rsProcurement['units_no'];
                        $unit = $row_rsProcurement['unit'];
                        $site_id = $row_rsProcurement['site_id'];
                        $output_id = $row_rsProcurement['outputid'];
                        $tender_id = $row_rsProcurement['id'];
                        $plan_counter++;


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
                        $unit_of_measure = get_measurement_unit($unit);

                        $task_amount += $procurement_total_cost;

                        $total_cost = $procurement_cost * $procurement_units;
                    }
                    $tasks .=
                        '<tr>
                        <input type="hidden" name="tender_id[]" value="' . $tender_id . '" />
                        <td> ' . $plan_counter . ' </td>
                        <td>' . $output . ' </td>
                        <td>' . $site . ' </td>
                        <td>' . $description . ' </td>
                        <td>' . number_format($procurement_units, 2) . '  ' . $unit_of_measure . '</td>
                        <td>' . number_format($procurement_cost, 2) . '</td>
                        <td>' . number_format($procurement_total_cost, 2) . '</td>
                    </tr>';
                }
            }
        }
        echo json_encode(array("success" => true, "tasks" => $tasks, "task_amount" => $task_amount));
    }

    if (isset($_GET['get_work_measurement_based'])) {
        $projid = $_GET['projid'];
        $query_rsTask_Complete = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid ");
        $query_rsTask_Complete->execute(array(':projid' => $projid));
        $total_rsTask_Complete = $query_rsTask_Complete->rowCount();
        $tasks = '';
        $procurement_total_cost = 0;
        if ($total_rsTask_Complete > 0) {
            $plan_counter = 0;
            while ($rows_rsTask_Complete = $query_rsTask_Complete->fetch()) {
                $subtask_id = $rows_rsTask_Complete['subtask_id'];
                $site_id = $rows_rsTask_Complete['site_id'];
                $output_id = $rows_rsTask_Complete['output_id'];
                $site_id = $rows_rsTask_Complete['site_id'];
                $task_id = $rows_rsTask_Complete['task_id'];

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
                    if ($request_units > 0) {
                        $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE projid=:projid AND subtask_id=:subtask_id AND site_id=:site_id ");
                        $query_rsProcurement->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
                        $row_rsProcurement = $query_rsProcurement->fetch();
                        $totalRows_rsProcurement = $query_rsProcurement->rowCount();
                        if ($totalRows_rsProcurement > 0) {
                            $plan_counter++;
                            $unit_cost = $row_rsProcurement['unit_cost'];
                            $units_no = $row_rsProcurement['units_no'];
                            $unit_id = $row_rsProcurement['unit'];

                            $description = $row_rsProcurement['description'];
                            $procurement_cost = $unit_cost * $request_units;
                            $procurement_total_cost += $procurement_cost;
                            $unit_of_measure = get_measurement_unit($unit_id);
                            $tasks .=
                                '<tr>
                                    <td> ' . $plan_counter . ' </td>
                                    <td>' . $description . ' </td>
                                    <td>' . number_format($units_no, 2) . '  ' . $unit_of_measure . '</td>
                                    <td>' . number_format($cummulative, 2) . '  ' . $unit_of_measure . '</td>
                                    <td>' . number_format($request_units, 2) . '  ' . $unit_of_measure . '</td>
                                    <td>' . number_format($unit_cost, 2) . '</td>
                                    <td>' . number_format($procurement_cost, 2) . '</td>
                                    <input type="hidden" name="output_id[]" id="output_id" value="' . $output_id . '">
                                    <input type="hidden" name="site_id[]" id="site_id" value="' . $site_id . '">
                                    <input type="hidden" name="task_id[]" id="task_id" value="' . $task_id . '">
                                    <input type="hidden" name="subtask_id[]" id="subtask_id" value="' . $subtask_id . '">
                                    <input type="hidden" name="request_units[]" id="request_units" value="' . $request_units . '">
                                    <input type="hidden" name="unit_cost[]" id="unit_cost" value="' . $unit_cost . '">
                                </tr>';
                        }
                    }
                }
            }
        }
        echo json_encode(array("success" => true, "tasks" => $tasks, "task_amount" => $procurement_total_cost, "request_amount" => number_format($procurement_total_cost, 2)));
    }

    function store_comments($payment_request_id, $stage, $status, $comments, $user_name, $created_at)
    {
        global $db;
        $sql = $db->prepare("INSERT INTO tbl_contractor_payment_request_comments (request_id,stage,status,comments,created_by,created_at) VALUES(:request_id,:stage,:status,:comments,:created_by,:created_at) ");
        $result = $sql->execute(array("request_id" => $payment_request_id, ":stage" => $stage, ":status" => $status, ":comments" => $comments, ":created_by" => $user_name, ":created_at" => $created_at));
        return $result;
    }

    if (isset($_POST['contractor_payment'])) {
        $projid = $_POST['projid'];
        $requested_amount = $_POST['requested_amount'];
        $comments = $_POST['comments'];
        $created_by = $_POST['user_name'];
        $created_at = date('Y-m-d');
        $payment_plan = $_POST['payment_plan'];
        $complete = $_POST['complete'];
        $status = 1;
        $stage = 1;
        $request_id = isset($_POST['request_id']) ? $_POST['request_id'] : incrementalHash() . $projid;
        $deleteQueryI = $db->prepare("DELETE FROM `tbl_contractor_payment_requests` WHERE request_id=:request_id");
        $resultsI = $deleteQueryI->execute(array(':request_id' => $request_id));

        $deleteQueryI = $db->prepare("DELETE FROM `tbl_contractor_payment_request_details` WHERE request_id=:request_id");
        $resultsI = $deleteQueryI->execute(array(':request_id' => $request_id));

        $filepath = '';
        if (!empty($_FILES['invoice']['name'])) {
            $filename = basename($_FILES['invoice']['name']);
            $ext = substr($filename, strrpos($filename, '.') + 1);
            if (($ext != "exe") && ($_FILES["invoice"]["type"] != "application/x-msdownload")) {
                $newname = time() . '_' . $projid . "_" . $stage . "_" . $filename;
                $filepath = "../../uploads/payments/" . $newname;
                if (!file_exists($filepath)) {
                    if (move_uploaded_file($_FILES['invoice']['tmp_name'], $filepath)) {
                        $fname = $newname;
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


        if ($payment_plan == 1) {
            $complete = 0;
            $item_id = $_POST['payment_phase'];
            $sql = $db->prepare("INSERT INTO tbl_contractor_payment_requests (projid,contractor_id,request_id,item_id,project_plan,requested_amount,status,stage,acceptance,invoice,created_at) VALUES(:projid,:contractor_id, :request_id,:item_id,:project_plan,:requested_amount,:status,:stage,:acceptance,:invoice,:created_at) ");
            $result = $sql->execute(array(":projid" => $projid, ":contractor_id" => $user_name, ":request_id" => $request_id, ":item_id" => $item_id, ":project_plan" => $payment_plan, ":requested_amount" => $requested_amount, ":status" => $status, ":stage" => $stage, ":acceptance" => $complete, ":invoice" => $filepath, ":created_at" => $created_at));
            $payment_request_id = $db->lastInsertId();
            store_comments($payment_request_id, $stage, $status, $comments, $user_name, $created_at);
        } else if ($payment_plan == 2) {
            if (isset($_POST['tender_id'])) {
                $item_id = 0;
                $sql = $db->prepare("INSERT INTO tbl_contractor_payment_requests (projid,contractor_id,request_id,item_id,project_plan,requested_amount,status,stage,acceptance,invoice,created_at) VALUES(:projid,:contractor_id, :request_id,:item_id,:project_plan,:requested_amount,:status,:stage,:acceptance,:invoice,:created_at) ");
                $result = $sql->execute(array(":projid" => $projid, ":contractor_id" => $user_name, ":request_id" => $request_id, ":item_id" => 0, ":project_plan" => $payment_plan, ":requested_amount" => $requested_amount, ":status" => $status, ":stage" => $stage, ":acceptance" => $complete, ":invoice" => $filepath, ":created_at" => $created_at));

                if ($result) {
                    $payment_request_id = $db->lastInsertId();
                    store_comments($payment_request_id, $stage, $status, $comments, $user_name, $created_at);
                    $total_tasks = count($_POST['tender_id']);
                    for ($i = 0; $i < $total_tasks; $i++) {
                        $plan_counter = 0;
                        $tender_id = $_POST['tender_id'][$i];
                        $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE id=:tender_id ");
                        $query_rsProcurement->execute(array(":tender_id" => $tender_id));
                        $row_rsProcurement = $query_rsProcurement->fetch();
                        $totalRows_rsProcurement = $query_rsProcurement->rowCount();
                        if ($totalRows_rsProcurement > 0) {
                            $unit_cost = $row_rsProcurement['unit_cost'];
                            $units_no = $row_rsProcurement['units_no'];
                            $unit = $row_rsProcurement['unit'];
                            $site_id = $row_rsProcurement['site_id'];
                            $output_id = $row_rsProcurement['outputid'];
                            $subtask_id = $row_rsProcurement['subtask_id'];
                            $task_id = $row_rsProcurement['tasks'];
                            $sql = $db->prepare("INSERT INTO tbl_contractor_payment_request_details (projid,output_id,site_id,task_id,subtask_id,tender_item_id,payment_request_id,request_id,unit_cost,units_no) VALUES(:projid,:output_id,:site_id,:task_id,:subtask_id,:tender_item_id,:payment_request_id,:request_id,:unit_cost,:units_no)");
                            $result = $sql->execute(array(":projid" => $projid, ":output_id" => $output_id, ":site_id" => $site_id, ":task_id" => $task_id, ":subtask_id" => $subtask_id, ":tender_item_id" => $tender_id, ":payment_request_id" => $payment_request_id, ":request_id" => $request_id, ":unit_cost" => $unit_cost, ":units_no" => $units_no));
                        }
                    }
                }
            }
        } else if ($payment_plan == 3) {
            if (isset($_POST['subtask_id'])) {
                $item_id = 0;
                $sql = $db->prepare("INSERT INTO tbl_contractor_payment_requests (projid,contractor_id,request_id,item_id,project_plan,requested_amount,status,stage,acceptance,invoice,created_at) VALUES(:projid,:contractor_id, :request_id,:item_id,:project_plan,:requested_amount,:status,:stage,:acceptance,:invoice,:created_at) ");
                $result = $sql->execute(array(":projid" => $projid, ":contractor_id" => $user_name, ":request_id" => $request_id, ":item_id" => 0, ":project_plan" => $payment_plan, ":requested_amount" => $requested_amount, ":status" => $status, ":stage" => $stage, ":acceptance" => $complete, ":invoice" => $filepath, ":created_at" => $created_at));

                if ($result) {
                    $payment_request_id = $db->lastInsertId();
                    store_comments($payment_request_id, $stage, $status, $comments, $user_name, $created_at);
                    $total_tasks = count($_POST['subtask_id']);
                    for ($i = 0; $i < $total_tasks; $i++) {
                        $plan_counter = 0;
                        $output_id = $_POST['output_id'][$i];
                        $site_id = $_POST['site_id'][$i];
                        $task_id = $_POST['task_id'][$i];
                        $subtask_id = $_POST['subtask_id'][$i];
                        $units_no = $_POST['request_units'][$i];
                        $unit_cost = $_POST['unit_cost'][$i];
                        $tender_id = 0;
                        $sql = $db->prepare("INSERT INTO tbl_contractor_payment_request_details (projid,output_id,site_id,task_id,subtask_id,tender_item_id,payment_request_id,request_id,unit_cost,units_no) VALUES(:projid,:output_id,:site_id,:task_id,:subtask_id,:tender_item_id,:payment_request_id,:request_id,:unit_cost,:units_no)");
                        $result = $sql->execute(array(":projid" => $projid, ":output_id" => $output_id, ":site_id" => $site_id, ":task_id" => $task_id, ":subtask_id" => $subtask_id, ":tender_item_id" => $tender_id, ":payment_request_id" => $payment_request_id, ":request_id" => $request_id, ":unit_cost" => $unit_cost, ":units_no" => $units_no));
                    }
                }
            }
        }
        return;
        echo json_encode(array("success" => true));
    }

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

    function get_task_based_details($projid)
    {
        global $db;
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
                $costlineid = $row_rsProcurement['costlineid'];
                $procurement_units = $row_rsProcurement['units_no'];
                $unit = $row_rsProcurement['unit'];
                $site_id = $row_rsProcurement['site_id'];
                $output_id = $row_rsProcurement['outputid'];
                $tender_id = $row_rsProcurement['id'];

                $query_rsTask_parameters = $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE id=:costlineid");
                $query_rsTask_parameters->execute(array(":costlineid" => $costlineid));
                $totalRows_rsTask_parameters = $query_rsTask_parameters->rowCount();
                $row_rsTask_parameters = $query_rsTask_parameters->fetch();
                $description =  ($totalRows_rsTask_parameters > 0) ? $row_rsTask_parameters['description'] : '';

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
                    <td>' . number_format($procurement_cost, 2) . '</td>
                    <td>' . number_format($procurement_total_cost, 2) . '</td>
                </tr>';
            }
        }
        return array("success" => true, "tasks" => $tasks, "task_amount" => $task_amount);
    }

    function get_work_based_details($payment_request_id)
    {
        global $db;
        $query_rsProcurement =  $db->prepare("SELECT * FROM tbl_contractor_payment_request_details WHERE payment_request_id=:payment_request_id ");
        $query_rsProcurement->execute(array(":payment_request_id" => $payment_request_id));
        $totalRows_rsProcurement = $query_rsProcurement->rowCount();

        $task_amount = 0;
        $tasks = '';
        if ($totalRows_rsProcurement > 0) {
            $plan_counter = 0;
            while ($row_rsProcurement = $query_rsProcurement->fetch()) {
                $plan_counter++;
                $procurement_cost = $row_rsProcurement['unit_cost'];
                $subtask_id = $row_rsProcurement['subtask_id'];
                $procurement_units = $row_rsProcurement['units_no'];
                $site_id = $row_rsProcurement['site_id'];
                $output_id = $row_rsProcurement['output_id'];

                $query_rsTask_parameters = $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE subtask_id=:subtask_id AND site_id=:site_id");
                $query_rsTask_parameters->execute(array(":subtask_id" => $subtask_id, ":site_id" => $site_id));
                $totalRows_rsTask_parameters = $query_rsTask_parameters->rowCount();
                $row_rsTask_parameters = $query_rsTask_parameters->fetch();
                $description =  ($totalRows_rsTask_parameters > 0) ? $row_rsTask_parameters['description'] : '';
                $unit_id =  ($totalRows_rsTask_parameters > 0) ? $row_rsTask_parameters['unit'] : '';
                $unit_of_measure = get_measurement_unit($unit_id);

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

                $task_amount += $procurement_total_cost;
                $tasks .=
                    '<tr>
                        <td> ' . $plan_counter . ' </td>
                        <td>' . $output . '</td>
                        <td>' . $site . '</td>
                        <td>' . $description . ' </td>
                        <td>' . number_format($procurement_units, 2) . '  ' . $unit_of_measure . '</td>
                        <td>' . number_format($procurement_cost, 2) . '</td>
                        <td>' . number_format($procurement_total_cost, 2) . '</td>
                    </tr>';
            }
        }
        return array("success" => true, "tasks" => $tasks, "task_amount" => number_format($task_amount, 2));
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
            } else if ($project_plan == 2) {
                $data  = get_task_based_details($projid);
            } else if ($project_plan == 3) {
                $data =  get_work_based_details($request_id);
            }
        }

        echo json_encode(array("details" => $data, "comments" => $comments, "attachment" => $attachment));
    }
} catch (PDOException $ex) {
    $result = flashMessage("An error occurred: " . $ex->getMessage());
    echo $ex->getMessage();
}
