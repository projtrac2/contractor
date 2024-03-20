<?php
include '../controller.php';
try {

    function get_site_achieved($site_id, $start_date, $end_date)
    {
        global $db;
        $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id ");
        $query_Site_score->execute(array(":site_id" => $site_id));
        if ($start_date != "") {
            $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND created_at>=:start_date ");
            $query_Site_score->execute(array(":site_id" => $site_id, ":start_date" => $start_date));
            if ($end_date != '') {
                $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND created_at>=:start_date AND created_at <= :end_date");
                $query_Site_score->execute(array(":site_id" => $site_id, ":start_date" => $start_date, ":end_date" => $end_date));
            }
        }
        $row_site_score = $query_Site_score->fetch();

        return  !is_null($row_site_score['achieved']) ? true : false;
    }

    function get_output_achieved($site_id, $output_id, $start_date, $end_date)
    {
        global $db;
        $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND output_id=:output_id");
        $query_Site_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
        if ($start_date != "") {
            $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND output_id=:output_id AND created_at>=:start_date ");
            $query_Site_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id, ":start_date" => $start_date));
            if ($end_date != '') {
                $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND output_id=:output_id AND created_at>=:start_date AND created_at <= :end_date");
                $query_Site_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id, ":start_date" => $start_date, ":end_date" => $end_date));
            }
        }
        $row_site_score = $query_Site_score->fetch();

        return  !is_null($row_site_score['achieved']) ? true : false;
    }

    function get_achieved($site_id, $output_id, $task_id)
    {
        global $db;
        $query_Site_score = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_project_monitoring_checklist_score where site_id=:site_id AND output_id=:output_id AND subtask_id=:subtask_id");
        $query_Site_score->execute(array(":site_id" => $site_id, ":output_id" => $output_id, ":subtask_id" => $task_id));
        $rows_site_score = $query_Site_score->rowCount();
        $row_site_score = $query_Site_score->fetch();
        return ($row_site_score['achieved'] != null) ? $row_site_score['achieved'] : 0;
    }

    function get_measurement_unit($unit_id)
    {
        global $db;
        $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
        $query_rsIndUnit->execute(array(":unit_id" => $unit_id));
        $row_rsIndUnit = $query_rsIndUnit->fetch();
        $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
        return  $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
    }

    if (isset($_GET['get_filter_record'])) {
        $projid = $_GET['projid'];
        $implimentation_type = 2;
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : "";
        $end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ?  $_GET['end_date'] : '';
        $success = true;
        $data = '
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">';
        $query_Sites = $db->prepare("SELECT * FROM tbl_project_sites WHERE projid=:projid");
        $query_Sites->execute(array(":projid" => $projid));
        $rows_sites = $query_Sites->rowCount();
        if ($rows_sites > 0) {
            $counter = 0;
            while ($row_Sites = $query_Sites->fetch()) {
                $site_id = $row_Sites['site_id'];
                $site = $row_Sites['site'];
                $counter++;


                if (get_site_achieved($site_id, $start_date, $end_date)) {
                    $data .= '
                    <fieldset class="scheduler-border">
						<legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                        <i class="fa fa-list-ol" aria-hidden="true"></i> Site:' . $counter . ' :
                    </legend>
                    <div class="card-header">
                        <div class="row clearfix">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item list-group-item-action active">Site : ' . $site . '</li>
                                </ul>
                            </div>
                        </div>
                    </div>';

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

                                if (get_output_achieved($site_id, $output_id, $start_date, $end_date)) {
                                    $query_rsTargetUsed = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_monitoringoutput WHERE output_id=:output_id");
                                    $query_rsTargetUsed->execute(array(":output_id" => $output_id));
                                    $Rows_rsTargetUsed = $query_rsTargetUsed->fetch();
                                    $output_achieved = $Rows_rsTargetUsed['achieved'] != null ? $Rows_rsTargetUsed['achieved'] : 0;

                                    $data .= '
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                            <i class="fa fa-list-ol" aria-hidden="true"></i> Output  ' . $counter . ':
                                        </legend>
                                        <div class="row clearfix">
                                            <div class="card-header">
                                                <div class="row clearfix">
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <ul class="list-group">
                                                            <li class="list-group-item list-group-item list-group-item-action active">Output :  ' . $output . '</li>
                                                            <li class="list-group-item">Achieved :  ' . number_format($output_achieved, 2) . '</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table' . $output_id . '">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:5%">#</th>
                                                                <th style="width:40%">Item</th>
                                                                <th style="width:25%">Achieved</th>
                                                                <th style="width:10%">Unit Cost (Ksh)</th>
                                                                <th style="width:10%">Total Cost (Ksh)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>';
                                    $query_rsOther_cost_plan_budget =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE site_id=:site_id AND outputid=:output_id AND cost_type=1");
                                    $query_rsOther_cost_plan_budget->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
                                    $rows_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->rowCount();
                                    if ($rows_rsOther_cost_plan_budget > 0) {
                                        $tcounter = 0;
                                        while ($row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch()) {
                                            $subtask_id = $row_rsOther_cost_plan_budget['subtask_id'];
                                            $units_no = $row_rsOther_cost_plan_budget['units_no'];
                                            $unit_cost = $row_rsOther_cost_plan_budget['unit_cost'];
                                            $unit_id = $row_rsOther_cost_plan_budget['unit'];
                                            $description = $row_rsOther_cost_plan_budget['description'];
                                            $achieved = get_achieved($site_id, $output_id, $subtask_id);
                                            if ($achieved > 0) {
                                                $measurement_unit =  get_measurement_unit($unit_id);
                                                $cost = $achieved * $unit_cost;
                                                $tcounter++;
                                                $data .= '
                                                <tr id="row' . $tcounter . '">
                                                    <td style="width:5%">' . $tcounter . '</td>
                                                    <td style="width:35%">' . $description . '</td>
                                                    <td style="width:35%">' . $measurement_unit . '</td>
                                                    <td style="width:20%">' . number_format($units_no, 2)  . '</td>
                                                    <td style="width:10%">' . number_format($achieved, 2) . '</td>
                                                    <td style="width:10%">' . number_format($cost, 2) . '</td>
                                                </tr>';
                                            }
                                        }
                                    }
                                    $data .= '
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>';
                                }
                            }
                        }
                    }
                    $data .= '</fieldset>';
                }
            }
        }


        $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE indicator_mapping_type<>1 AND projid = :projid");
        $query_Output->execute(array(":projid" => $projid));
        $total_Output = $query_Output->rowCount();
        $outputs = '';
        if ($total_Output > 0) {
            $outputs = '';
            if ($total_Output > 0) {
                $counter = 0;
                $site_id = 0;
                while ($row_rsOutput = $query_Output->fetch()) {
                    $output_id = $row_rsOutput['id'];
                    $output = $row_rsOutput['indicator_name'];
                    if (get_output_achieved($site_id, $output_id, $start_date, $end_date)) {
                        $counter++;

                        $query_rsTargetUsed = $db->prepare("SELECT SUM(achieved) as achieved FROM tbl_monitoringoutput WHERE output_id=:output_id");
                        $query_rsTargetUsed->execute(array(":output_id" => $output_id));
                        $Rows_rsTargetUsed = $query_rsTargetUsed->fetch();
                        $output_achieved = $Rows_rsTargetUsed['achieved'] != null ? $Rows_rsTargetUsed['achieved'] : 0;
                        $data .= '
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                <i class="fa fa-list-ol" aria-hidden="true"></i> Output ' . $counter . ' : ' . $output . '
                            </legend>
                            <div class="card-header">
                                <div class="row clearfix">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <ul class="list-group">
                                            <li class="list-group-item list-group-item list-group-item-action active">Output : ' . $output . '</li>
                                            <li class="list-group-item">Achieved : ' . number_format($output_achieved, 2) . '</li>
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
                                            <th style="width:25%">Achieved</th>
                                            <th style="width:10%">Unit Cost (Ksh)</th>
                                            <th style="width:10%">Total Cost (Ksh)</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        $query_rsOther_cost_plan_budget =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan WHERE site_id=:site_id AND outputid=:output_id AND cost_type=1");
                        $query_rsOther_cost_plan_budget->execute(array(":site_id" => $site_id, ":output_id" => $output_id));
                        $rows_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->rowCount();
                        if ($rows_rsOther_cost_plan_budget > 0) {
                            $tcounter = 0;
                            while ($row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch()) {
                                $subtask_id = $row_rsOther_cost_plan_budget['subtask_id'];
                                $units_no = $row_rsOther_cost_plan_budget['units_no'];
                                $unit_cost = $row_rsOther_cost_plan_budget['unit_cost'];
                                $unit_id = $row_rsOther_cost_plan_budget['unit'];
                                $description = $row_rsOther_cost_plan_budget['description'];
                                $achieved = get_achieved($site_id, $output_id, $subtask_id);
                                if ($achieved > 0) {
                                    $measurement_unit =  get_measurement_unit($unit_id);
                                    $cost = $achieved * $unit_cost;
                                    $tcounter++;
                                    $data .= '
                                    <tr id="row' . $tcounter . '">
                                        <td style="width:5%">' . $tcounter . '</td>
                                        <td style="width:35%">' . $description . '</td>
                                        <td style="width:35%">' . $measurement_unit . '</td>
                                        <td style="width:20%">' . number_format($units_no, 2)  . '</td>
                                        <td style="width:10%">' . number_format($achieved, 2) . '</td>
                                        <td style="width:10%">' . number_format($cost, 2) . '</td>
                                    </tr>';
                                }
                            }
                        }
                        $data .= '
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>';
                    }
                }
            }
        }

        $data .= '</div>';
        echo json_encode(array("success" => $success, "data" => $data));
    }
} catch (PDOException $ex) {
    $result = flashMessage("An error occurred: " . $ex->getMessage());
    echo $ex->getMessage();
}
