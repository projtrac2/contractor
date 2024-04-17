<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : "";
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];
$original_projid = $_GET['proj'];

require('includes/head.php');
if ($permission) {
    try {
        $query_rsMyP =  $db->prepare("SELECT *, projstartdate AS sdate, projenddate AS edate, projcategory, progress FROM tbl_projects WHERE deleted='0' AND projid = '$projid'");
        $query_rsMyP->execute();
        $row_rsMyP = $query_rsMyP->fetch();
        $projname = $row_rsMyP['projname'];
        $projcode = $row_rsMyP['projcode'];
        $projfscyear = $row_rsMyP['projfscyear'];
        $projduration = $row_rsMyP['projduration'];
        $projcat = $row_rsMyP['projcategory'];
        $projstage = $row_rsMyP["projstage"];
        $percent2 = number_format(calculate_project_progress($projid, $projcat), 2);


        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();
        $start_date = $end_date = '';
        if ($totalRows_rsTender > 0) {
            $start_date = $row_rsTender['startdate'];
            $end_date = $row_rsTender['enddate'];
        }
    } catch (PDOException $ex) {
        $result = flashMessage("An error occurred: " . $ex->getMessage());
        echo $result;
    }
?>
    <style>
        @import "https://code.highcharts.com/dashboards/css/dashboards.css";
    </style>
    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-calendar" aria-hidden="true"></i> Timeline
                <div class="btn-group" style="float:right; margin-right:10px">
                    <input type="button" VALUE="Go Back to Projects Dashboard" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
                </div>
            </h4>
        </div>
        <div class="row clearfix">
            <div class="block-header">
                <?= $results; ?>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="header" style="padding-bottom:0px">
                        <div class="button-demo" style="margin-top:-15px">
                            <span class="label bg-black" style="font-size:17px"><img src="images/proj-icon.png" alt="Project Menu" title="Project Menu" style="vertical-align:middle; height:25px" />Menu</span>
                            <a href="project-dashboard.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; padding-left:-5px">Dashboard</a>
                            <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px; margin-left:-9px">Timelines</a>
                            <a href="project-progress.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Progress</a>
                            <a href="project-team.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Team</a>
                            <a href="project-contract.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Contract</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row clearfix">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px;">
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item list-group-item-action active">Project Name: <?= $projname ?> </li>
                                    <li class="list-group-item"><strong>Project Code: </strong> <?= $projcode ?> </li>
                                    <li class="list-group-item"><strong>Project Start Date: </strong> <?= date('d M Y', strtotime($start_date)); ?> </li>
                                    <li class="list-group-item"><strong>Project End Date: </strong><?= date('d M Y', strtotime($end_date)); ?> </li>
                                    <li class="list-group-item"><strong>Project Progress: </strong>
                                        <div class="progress" style="height:23px; margin-bottom:1px; margin-top:1px; color:black">
                                            <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="<?= $percent2 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent2 ?>%; margin:auto; padding-left: 10px; padding-top: 3px; text-align:left; color:black">
                                                <?= $percent2 ?>%
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="body">
                        <input type="hidden" name="projid" id="projid" value="<?= $projid ?>">
                        <ul class="nav nav-tabs" style="font-size:14px">
                            <li class="active">
                                <a data-toggle="tab" href="#menu1"><i class="fa fa-calendar bg-green" aria-hidden="true"></i> Time Schedule &nbsp;<span class="badge bg-green">|</span></a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#menu2"><i class="fa fa-list-alt bg-blue" aria-hidden="true"></i> Gantt Chart &nbsp;<span class="badge bg-blue">|</span></a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#menu3"><i class="fa fa-list-alt bg-blue" aria-hidden="true"></i> Target Breakdown &nbsp;<span class="badge bg-blue">|</span></a>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <div class="tab-content">
                            <div id="menu1" class="tab-pane fade in active">
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
                                ?>
                                        <fieldset class="scheduler-border">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                SITE  <?= $counter ?> : <?= $site ?>
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
                                                        //$output_id = $row_Output['id'];
                                                        $output = $row_Output['indicator_name'];
                                            ?>
                                                        <fieldset class="scheduler-border">
                                                            <legend class="scheduler-border" style="background-color:#f0f0f0; border-radius:3px">
                                                                OUTPUT <?= $output_counter ?> : <?= $output ?>
                                                            </legend>
                                                            <?php
                                                            $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id");
                                                            $query_rsMilestone->execute(array(":output_id" => $output_id));
                                                            $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                                            if ($totalRows_rsMilestone > 0) {
                                                                $task_counter = 0;
                                                                while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                                    $milestone = $row_rsMilestone['milestone'];
                                                                    $msid = $row_rsMilestone['msid'];

                                                                    $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id");
                                                                    $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => $site_id));
                                                                    $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                    $edit = $totalRows_rsTask_Start_Dates > 1 ? 1 : 0;
                                                                    $details = array("output_id" => $output_id, "site_id" => $site_id, 'task_id' => $msid, 'edit' => $edit);
                                                                    $task_counter++;
                                                            ?>
                                                                    <div class="row clearfix">
                                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                            <div class="card-header">
                                                                                <div class="row clearfix">
                                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                        <h5>
                                                                                            <u>
                                                                                                TASK <?= $task_counter ?>: <?= $milestone ?>
                                                                                            </u>
                                                                                        </h5>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="table-responsive">
                                                                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th style="width:5%">#</th>
                                                                                            <th style="width:55%">Subtask</th>
                                                                                            <th style="width:10%">Duration</th>
                                                                                            <th style="width:15%">Start Date</th>
                                                                                            <th style="width:15%">End Date</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php
                                                                                        $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid  ORDER BY parenttask");
                                                                                        $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $msid));
                                                                                        $totalRows_rsTasks = $query_rsTasks->rowCount();
                                                                                        if ($totalRows_rsTasks > 0) {
                                                                                            $tcounter = 0;
                                                                                            while ($row_rsTasks = $query_rsTasks->fetch()) {
                                                                                                $tcounter++;
                                                                                                $task_name = $row_rsTasks['task'];
                                                                                                $task_id = $row_rsTasks['tkid'];
                                                                                                $unit =  $row_rsTasks['unit_of_measure'];
                                                                                                $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                                                                                                $query_rsIndUnit->execute(array(":unit_id" => $unit));
                                                                                                $row_rsIndUnit = $query_rsIndUnit->fetch();
                                                                                                $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                                                                                                $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';

                                                                                                $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
                                                                                                $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => $site_id, ":subtask_id" => $task_id));
                                                                                                $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                                                                $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                                                $start_date = $end_date = $duration =  "";
                                                                                                if ($totalRows_rsTask_Start_Dates > 0) {
                                                                                                    $start_date = date("d M Y", strtotime($row_rsTask_Start_Dates['start_date']));
                                                                                                    $end_date = date("d M Y", strtotime($row_rsTask_Start_Dates['end_date']));
                                                                                                    $duration = number_format($row_rsTask_Start_Dates['duration']);
                                                                                                }
                                                                                        ?>
                                                                                                <tr id="row">
                                                                                                    <td style="width:5%"><?= $task_counter ?>.<?= $tcounter ?></td>
                                                                                                    <td style="width:55%"><?= $task_name ?></td>
                                                                                                    <td style="width:10%"><?= $duration ?> Days</td>
                                                                                                    <td style="width:15%"><?= $start_date ?></td>
                                                                                                    <td style="width:15%"><?= $end_date ?></td>
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
                                $outputs = '';
                                if ($total_Output > 0) {
                                    $outputs = '';
                                    if ($total_Output > 0) {
                                        $counter = 0;

                                        while ($row_rsOutput = $query_Output->fetch()) {
                                            $output_id = $row_rsOutput['id'];
                                            $output = $row_rsOutput['indicator_name'];
                                            $counter++;
                                            $site_id = 0;
                                        ?>
                                            <fieldset class="scheduler-border">
                                                <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                    AWAY POINT OUTPUT <?= $counter ?>: <?= $output ?>
                                                </legend>
                                                <?php
                                                $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ");
                                                $query_rsMilestone->execute(array(":output_id" => $output_id));
                                                $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                                if ($totalRows_rsMilestone > 0) {
                                                    $task_counter = 0;
                                                    while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                        $milestone = $row_rsMilestone['milestone'];
                                                        $msid = $row_rsMilestone['msid'];
                                                        $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id ");
                                                        $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => 0));
                                                        $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                        $edit = $totalRows_rsTask_Start_Dates > 1 ? 1 : 0;
                                                        $details = array("output_id" => $output_id, "site_id" => $site_id, 'task_id' => $msid, 'edit' => $edit);
                                                        $task_counter++;
                                                ?>
                                                        <div class="row clearfix">
                                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                <div class="card-header">
                                                                    <div class="row clearfix">
                                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                            <h5>
                                                                                <u>
                                                                                    TASK <?= $task_counter ?>: <?= $milestone ?>
                                                                                </u>
                                                                            </h5>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                        <thead>
                                                                            <tr>
                                                                                <th style="width:5%">#</th>
                                                                                <th style="width:55%">Item</th>
                                                                                <th style="width:10%">Duration</th>
                                                                                <th style="width:15%">Start Date</th>
                                                                                <th style="width:15%">End Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid  ORDER BY parenttask");
                                                                            $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $msid));
                                                                            $totalRows_rsTasks = $query_rsTasks->rowCount();
                                                                            if ($totalRows_rsTasks > 0) {
                                                                                $tcounter = 0;
                                                                                while ($row_rsTasks = $query_rsTasks->fetch()) {
                                                                                    $tcounter++;
                                                                                    $task_name = $row_rsTasks['task'];
                                                                                    $task_id = $row_rsTasks['tkid'];
                                                                                    $unit =  $row_rsTasks['unit_of_measure'];
                                                                                    $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                                                                                    $query_rsIndUnit->execute(array(":unit_id" => $unit));
                                                                                    $row_rsIndUnit = $query_rsIndUnit->fetch();
                                                                                    $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                                                                                    $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';

                                                                                    $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE task_id=:task_id AND site_id=:site_id AND subtask_id=:subtask_id ");
                                                                                    $query_rsTask_Start_Dates->execute(array(':task_id' => $msid, ':site_id' => 0, ":subtask_id" => $task_id));
                                                                                    $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                                                    $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                                                                    $start_date = $end_date = $duration =  "";
                                                                                    if ($totalRows_rsTask_Start_Dates > 0) {
                                                                                        $start_date = date("d M Y", strtotime($row_rsTask_Start_Dates['start_date']));
                                                                                        $end_date = date("d M Y", strtotime($row_rsTask_Start_Dates['end_date']));
                                                                                        $duration = number_format($row_rsTask_Start_Dates['duration']);
                                                                                    }
                                                                            ?>
                                                                                    <tr id="row<?= $tcounter ?>">
                                                                                        <td style="width:5%"><?= $task_counter ?>.<?= $tcounter ?></td>
                                                                                        <td style="width:55%"><?= $task_name ?></td>
                                                                                        <td style="width:10%"><?= $duration ?> Days</td>
                                                                                        <td style="width:15%"><?= $start_date ?> </td>
                                                                                        <td style="width:15%"><?= $end_date ?></td>
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
                            </div>
                            <div id="menu2" class="tab-pane fade">
                                <div class="row clearfix">
                                    <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 pull-right">
                                        <?php
                                        $query_Output = $db->prepare("SELECT * FROM tbl_project_sites p INNER JOIN tbl_output_disaggregation s ON s.output_site = p.site_id WHERE s.projid = :projid ");
                                        $query_Output->execute(array(":projid" => $projid));
                                        $total_Output = $query_Output->rowCount();
                                        if ($total_Output > 0) {
                                        ?>
                                            <label class="control-label">Project Sites:</label>
                                            <div class="form-line">
                                                <select name="site_id" id="site_id" onchange="get_data()" data-actions-box="true" class="form-control show-tick selectpicker" title="Choose Multipe" style="border:#CCC thin solid; border-radius:5px; width:98%; padding-left:50px" required>
                                                    <?php
                                                    while ($row_rsOutput = $query_Output->fetch()) {
                                                        $site_name = $row_rsOutput['site'];
                                                        $site_id = $row_rsOutput['site_id'];
                                                    ?>
                                                        <option value="<?= $site_id ?>"><?= $site_name ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div id="container-gantt"></div>
                                <div id="container"></div>
                            </div>
                            <div id="menu3" class="tab-pane fade">
                                <?php
                                include_once('./target-breakdown.php');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end body  -->
<?php
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');


function get_output_chart($projid)
{
    $series_arr = [];
    global $db, $projname;
    $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM tbl_program_of_works WHERE projid=:projid ");
    $query_rsTask_Start_Dates->execute(array(":projid" => $projid));
    $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
    if (!is_null($row_rsTask_Start_Dates['start_date']) && !is_null($row_rsTask_Start_Dates['end_date'])) {
        $series = new stdClass;
        $series->name = $projname;
        $series->data = [];

        $inner = new stdClass;
        $inner->name = $projname;
        $inner->id = 'p' . $projid;
        $inner->owner = 'owner';

        array_push($series->data, $inner);
        $query_Output = $db->prepare("SELECT * FROM tbl_project_details d INNER JOIN tbl_indicator i ON i.indid = d.indicator WHERE projid = :projid");
        $query_Output->execute(array(":projid" => $projid));
        $total_Output = $query_Output->rowCount();
        if ($total_Output > 0) {
            while ($row_rsOutput = $query_Output->fetch()) {
                $output_id = $row_rsOutput['id'];
                $output = $row_rsOutput['indicator_name'];
                $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                $query_rsMilestone->execute(array(":output_id" => $output_id));
                $totalRows_rsMilestone = $query_rsMilestone->rowCount();

                $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM tbl_program_of_works WHERE output_id=:output_id ");
                $query_rsTask_Start_Dates->execute(array(":output_id" => $output_id));
                $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                if (!is_null($row_rsTask_Start_Dates['start_date']) && !is_null($row_rsTask_Start_Dates['end_date'])) {
                    $start_date =  strtotime($row_rsTask_Start_Dates['start_date']) * 1000;
                    $end_date =  strtotime($row_rsTask_Start_Dates['end_date']) * 1000;

                    $m_outputs = new stdClass;
                    $m_outputs->name = $output;
                    $m_outputs->id = 'o' . $output_id;
                    $m_outputs->parent = 'p' . $projid;
                    $m_outputs->start = $start_date;
                    $m_outputs->end = $end_date;
                    $m_outputs->dependencies = '';

                    array_push($series->data, $m_outputs);
                    if ($totalRows_rsMilestone > 0) {
                        while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                            $milestone_name = $row_rsMilestone['milestone'];
                            $milestone_id = $row_rsMilestone['msid'];

                            $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM tbl_program_of_works WHERE task_id=:task_id ");
                            $query_rsTask_Start_Dates->execute(array(":task_id" => $milestone_id));
                            $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                            if (!is_null($row_rsTask_Start_Dates['start_date']) && !is_null($row_rsTask_Start_Dates['end_date'])) {
                                $start_date =  strtotime($row_rsTask_Start_Dates['start_date']) * 1000;
                                $end_date =  strtotime($row_rsTask_Start_Dates['end_date']) * 1000;

                                $m_tasks = new stdClass;
                                $m_tasks->id = 'm' . $milestone_id;
                                $m_tasks->name = $milestone_name;
                                $m_tasks->parent = 'o' . $output_id;
                                $m_tasks->start = $start_date;
                                $m_tasks->end = $end_date;

                                array_push($series->data, $m_tasks);

                                $query_rsTasks = $db->prepare("SELECT * FROM tbl_task WHERE outputid=:output_id AND msid=:msid");
                                $query_rsTasks->execute(array(":output_id" => $output_id, ":msid" => $milestone_id));
                                $totalRows_rsTasks = $query_rsTasks->rowCount();
                                if ($totalRows_rsTasks > 0) {
                                    while ($row_rsTasks = $query_rsTasks->fetch()) {
                                        $task_name = $row_rsTasks['task'];
                                        $task_id = $row_rsTasks['tkid'];
                                        $parent =  $row_rsTasks['parenttask'];

                                        $query_rsTask_Start_Dates = $db->prepare("SELECT start_date, end_date FROM tbl_program_of_works WHERE subtask_id=:subtask_id ");
                                        $query_rsTask_Start_Dates->execute(array(":subtask_id" => $task_id));
                                        $row_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                        $totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
                                        if ($totalRows_rsTask_Start_Dates > 0) {
                                            $start_date = strtotime($row_rsTask_Start_Dates['start_date']) * 1000;
                                            $end_date =  strtotime($row_rsTask_Start_Dates['end_date']) *  1000;
                                            $m_sub_tasks = new stdClass;
                                            $m_sub_tasks->name = $task_name;
                                            $m_sub_tasks->id = 't' . $task_id;
                                            $m_sub_tasks->parent = "m" . $milestone_id;
                                            $m_sub_tasks->dependency = $parent;
                                            $m_sub_tasks->start = $start_date;
                                            $m_sub_tasks->end = $end_date;
                                            array_push($series->data, $m_sub_tasks);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        array_push($series_arr, $series);
    }

    return $series_arr;
}
$data =  get_output_chart($projid);
?>
<script src="https://code.highcharts.com/gantt/highcharts-gantt.js"></script>
<script src="https://code.highcharts.com/gantt/modules/exporting.js"></script>
<script src="https://code.highcharts.com/gantt/modules/pattern-fill.js"></script>
<script src="https://code.highcharts.com/gantt/modules/accessibility.js"></script>
<script>
    const start_date = `<?= $start_date ?>`;
    const end_date = `<?= $end_date ?>`;
    const proj_start_date = new Date(start_date);
    const proj_end_date = new Date(end_date);
    var chart;
    const data = <?= json_encode($data) ?>;

    const gantt_chart = (data) => {
        var options = {
            chart: {
                events: {
                    load() {
                        let chart = this;
                        chart.xAxis[0].setExtremes(Date.UTC(proj_start_date.getFullYear(), proj_start_date.getMonth(), proj_start_date.getDay()), Date.UTC(proj_end_date.getFullYear(), proj_end_date.getMonth(), proj_end_date.getDay()))
                    }
                }
            },
            title: {
                text: 'Project Gantt Chart'
            },
            yAxis: {
                uniqueNames: true
            },
            navigator: {
                enabled: true,
                liveRedraw: true,
                series: {
                    type: 'gantt',
                    pointPlacement: 0.5,
                    pointPadding: 0.25,
                    accessibility: {
                        enabled: false
                    }
                },
                yAxis: {
                    min: 0,
                    max: 3,
                    reversed: true,
                    categories: []
                }
            },
            scrollbar: {
                enabled: true
            },
            rangeSelector: {
                enabled: true,
                selected: 0
            },
            accessibility: {
                point: {
                    descriptionFormat: '{yCategory}. ' +
                        '{#if completed}Task {(multiply completed.amount 100):.1f}% completed. {/if}' +
                        'Start {x:%Y-%m-%d}, end {x2:%Y-%m-%d}.'
                },
                series: {
                    descriptionFormat: '{name}'
                }
            },
            lang: {
                accessibility: {
                    axis: {
                        xAxisDescriptionPlural: 'The chart has a two-part X axis showing time in both week numbers and days.',
                        yAxisDescriptionPlural: 'The chart has one Y axis showing task categories.'
                    }
                }
            },
            series: data,
        };
        return Highcharts.ganttChart('container-gantt', options);
    }

    $(document).ready(function() {
        chart = gantt_chart(data);
    });

    function get_data() {
        var site_id = $("#site_id").val();
        var projid = $("#projid").val();
        if (projid != '' && site_id != '') {
            $.ajax({
                type: "get",
                url: "ajax/programsOfWorks/ganttchart.php",
                data: {
                    timeline_series: "timeline_series",
                    site_id: site_id,
                    projid: projid,
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        if (chart != null) {
                            chart.destroy();
                            chart = null;
                        }
                        var data = response.series;
                        chart = gantt_chart(data);
                    }
                }
            });

        }
    }


    $(function() {
        $('.achieved-btn').each((index, element) => {
            var projid = $("#projid").val();
            $(element).on('click', () => {
                $.ajax({
                    type: "get",
                    url: "ajax/programsOfWorks/get-wbs-achieved.php",
                    data: {
                        projid: projid,
                        site_id: $(element).next().val(),
                        output_id: $(element).next().next().val(),
                        task_id: $(element).next().next().next().val(),
                        subtask_id: $(element).next().next().next().next().val(),
                        subtask_start_date: $(element).next().next().next().next().next().val(),
                        subtask_end_date: $(element).next().next().next().next().next().next().val(),
                        get_wbs: 'get_wbs'
                    },
                    dataType: "json",
                    cache: false,
                    success: function(response) {
                        let tkid = $(element).val();
                        $(`#tasks_wbs_table_body`).html(response.table);
                    }
                });
            });
        });
    })
</script>