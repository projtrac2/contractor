<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : header("Location: projects");
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];

$original_projid = $_GET['proj'];
require('includes/head.php');

if ($permission) {
    try {
        $query_rsMyP = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid='$projid'");
        $query_rsMyP->execute();
        $row_rsMyP = $query_rsMyP->fetch();
        $count_rsMyP = $query_rsMyP->rowCount();
        $projcode = $projname = $projcat = $progname = $projdesc = "";
        $projlga = $projcommunity = [];
        $currentdate = date("Y-m-d");

        if ($count_rsMyP > 0) {
            $projcategory = $row_rsMyP["projcategory"];
            $projdesc = $row_rsMyP["projdesc"];
            $projcode = $row_rsMyP["projcode"];
            $projcommunity = explode(",", $row_rsMyP['projcommunity']);
            $projlga = explode(",", $row_rsMyP['projlga']);
            $progname = $row_rsMyP["progname"];
            $projname = $row_rsMyP['projname'];
        }

        $percent2 = calculate_project_progress($projid, $projcategory);

        $query_project_issues = $db->prepare("SELECT * FROM tbl_projissues WHERE projid = :projid");
        $query_project_issues->execute(array(":projid" => $projid));
        $totalRows_project_issues = $query_project_issues->rowCount();

        function get_inspection_status($status_id)
        {
            global $db;
            $sql = $db->prepare("SELECT * FROM tbl_issue_status WHERE statuskey = :status_id");
            $sql->execute(array(":status_id" => $status_id));
            $row = $sql->fetch();
            $rows_count = $sql->rowCount();
            return ($rows_count > 0) ? $row['status'] : "";
        }

?>
    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-tasks" style="color:white"></i> Issues
                <div class="btn-group" style="float:right; margin-right:10px">
                    <input type="button" VALUE="Go Back to Projects Dashboard" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
                </div>
            </h4>
        </div>
        <div class="row clearfix">
            <div class="block-header">
                <?= $results; ?>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row clearfix">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px;">
                                <ul class="list-group">
                                    <li class="list-group-item list-group-item list-group-item-action active">Project Name: <?= $projname ?> </li>
                                    <li class="list-group-item"><strong>Project Code: </strong> <?= $projcode ?> </li>
                                    <li class="list-group-item"><strong>Project Description: </strong> <?= $projdesc ?> </li>
                                    <li class="list-group-item"><strong>Program Name: </strong> <?= $progname ?> </li>
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover js-basic-example">
                                <thead>
                                    <tr>
                                        <th style="width:3%">#</th>
                                        <th style="width:37%">Issue</th>
                                        <th style="width:10%">Category</th>
                                        <th style="width:10%">Issue Area</th>
                                        <th style="width:10%">Impact</th>
                                        <th style="width:10%">Priority</th>
                                        <th style="width:10%">Resolution</th>
                                        <th style="width:10%">Issue Date</th>
                                    </tr>
                                </thead>
                                <tbody id="previous_issues_table">
                                    <?php
                                    $query_allrisks = $db->prepare("SELECT c.catid, c.category, i.id AS issueid, i.issue_description, i.issue_area, i.issue_impact, i.issue_priority, i.date_created, i.status FROM tbl_projrisk_categories c left join tbl_projissues i on c.catid = i.risk_category WHERE projid = :projid");
                                    $query_allrisks->execute(array(":projid" => $projid));
                                    $totalrows_allrisks = $query_allrisks->rowCount();

                                    $query_project =  $db->prepare("SELECT projcategory FROM tbl_projects WHERE projid=:projid");
                                    $query_project->execute(array(":projid" => $projid));
                                    $row_project = $query_project->fetch();
                                    $projcategory = $row_project["projcategory"];

                                    $issues_body = '<input type="hidden" value="0" id="clicked">';
                                    if ($totalrows_allrisks > 0) {
                                        $count =  0;
                                        $success = true;

                                        while ($rows_allrisks = $query_allrisks->fetch()) {
                                            $count++;
                                            $issueid = $rows_allrisks["issueid"];
                                            $issueareaid = $rows_allrisks["issue_area"];
                                            $category = $rows_allrisks["category"];
                                            $issue = $rows_allrisks["issue_description"];
                                            $impactid = $rows_allrisks["issue_impact"];
                                            $priorityid = $rows_allrisks["issue_priority"];
                                            $status_id = $rows_allrisks["status"];
                                            $issuedate = $rows_allrisks["date_created"];
                                            $status = get_inspection_status($status_id);

                                            $query_risk_impact =  $db->prepare("SELECT * FROM tbl_risk_impact WHERE id=:impactid");
                                            $query_risk_impact->execute(array(":impactid" => $impactid));
                                            $row_risk_impact = $query_risk_impact->fetch();
                                            $impact = $row_risk_impact["description"];

                                            if ($priorityid == 1) {
                                                $priority = "High";
                                            } elseif ($priorityid == 2) {
                                                $priority = "Medium";
                                            } else {
                                                $priority = "Low";
                                            }

                                            $issues_scope = '';
                                            $textclass = "";
                                            if ($issueareaid == 2) {
                                                $issue_area = "Scope";
                                                $textclass = "text-primary";
                                                $leftjoin = $projcategory == 2 ? "left join tbl_project_tender_details c on c.subtask_id=a.sub_task_id" : "left join tbl_project_direct_cost_plan c on c.subtask_id=a.sub_task_id";
                                                $query_site_id = $db->prepare("SELECT site_id FROM tbl_project_adjustments WHERE issueid=:issueid GROUP BY site_id");
                                                $query_site_id->execute(array(":issueid" => $issueid));
                                                $sites = 0;
                                                while ($rows_site_id = $query_site_id->fetch()) {
                                                    $sites++;
                                                    $site_id = $rows_site_id['site_id'];
                                                    //$allsites = '';

                                                    if ($site_id == 0) {
                                                        $allsites = '<tr class="adjustments ' . $issueid . '" style="background-color:#a9a9a9">
                                                                    <th colspan="8">Site ' . $sites . ': Way Point Sub Tasks</th>
                                                                </tr>';
                                                    } else {
                                                        $query_site = $db->prepare("SELECT site FROM tbl_project_sites WHERE site_id=:site_id");
                                                        $query_site->execute(array(":site_id" => $site_id));
                                                        $row_site = $query_site->fetch();
                                                        $site = $row_site['site'];

                                                        $allsites = '<tr class="adjustments ' . $issueid . '" style="background-color:#a9a9a9">
                                                                        <th colspan="8">Site ' . $sites . ': ' . $site . '</th>
                                                                    </tr>';
                                                    }

                                                    $query_adjustments = $db->prepare("SELECT t.task, a.units, a.timeline, c.unit_cost, u.unit FROM tbl_project_adjustments a left join tbl_projissues i on i.id = a.issueid left join tbl_task t on t.tkid=a.sub_task_id " . $leftjoin . " left join tbl_measurement_units u on u.id=c.unit WHERE i.projid = :projid and issueid = :issueid and a.site_id=:site_id GROUP BY a.id");
                                                    $query_adjustments->execute(array(":projid" => $projid, ":issueid" => $issueid, ":site_id" => $site_id));

                                                    $issues_scope .= $allsites . '
                                                            <tr class="adjustments ' . $issueid . '" style="background-color:#cccccc">
                                                                <th>#</th>
                                                                <th colspan="3">Sub-Task</th>
                                                                <th>Requesting Units</th>
                                                                <th>Additional Days</th>
                                                                <th colspan="2">Additional Cost</th>
                                                            </tr>';
                                                    $scopecount = 0;
                                                    while ($row_adjustments = $query_adjustments->fetch()) {
                                                        $scopecount++;
                                                        $subtask = $row_adjustments["task"];
                                                        $units =  number_format($row_adjustments["units"]) . " " . $row_adjustments["unit"];
                                                        $totalcost = $row_adjustments["unit_cost"] * $row_adjustments["units"];
                                                        $timeline = $row_adjustments["timeline"] . " days";
                                                        $issues_scope .=
                                                            '<tr class="adjustments ' . $issueid . '" style="background-color:#e5e5e5">
                                                                <td>' . $count . '.' . $sites . '.' . $scopecount . '</td>
                                                                <td colspan="3">' . $subtask . '</td>
                                                                <td>' . $units . ' </td>
                                                                <td>' . $timeline . '</td>
                                                                <td colspan="2">' . number_format($totalcost, 2) . ' </td>
                                                            </tr>';
                                                    }
                                                }
                                            } elseif ($issueareaid == 3) {
                                                $issue_area = "Schedule";
                                                $textclass = "text-primary";
                                                $leftjoin = $projcategory == 2 ? "left join tbl_project_tender_details c on c.subtask_id=a.sub_task_id" : "left join tbl_project_direct_cost_plan c on c.subtask_id=a.sub_task_id";

                                                $query_site_id = $db->prepare("SELECT site_id FROM tbl_project_adjustments WHERE issueid=:issueid GROUP BY site_id");
                                                $query_site_id->execute(array(":issueid" => $issueid));

                                                $sites = 0;
                                                while ($rows_site_id = $query_site_id->fetch()) {
                                                    $sites++;
                                                    $site_id = $rows_site_id['site_id'];
                                                    $allsites = '';

                                                    if ($site_id == 0) {
                                                        $allsites =
                                                            '<tr class="adjustments ' . $issueid . '" style="background-color:#a9a9a9">
                                                                <th colspan="8">Site ' . $sites . ': Way Point Sub Tasks</th>
                                                            </tr>';
                                                    } else {
                                                        $query_site = $db->prepare("SELECT site FROM tbl_project_sites WHERE site_id=:site_id");
                                                        $query_site->execute(array(":site_id" => $site_id));
                                                        $row_site = $query_site->fetch();
                                                        $site = $row_site['site'];

                                                        $allsites =
                                                            '<tr class="adjustments ' . $issueid . '" style="background-color:#a9a9a9">
                                                                <th colspan="8">Site ' . $sites . ': ' . $site . '</th>
                                                            </tr>';
                                                    }

                                                    $query_adjustments = $db->prepare("SELECT t.task, a.units, a.timeline, c.unit_cost, u.unit FROM tbl_project_adjustments a left join tbl_projissues i on i.id = a.issueid left join tbl_task t on t.tkid=a.sub_task_id " . $leftjoin . " left join tbl_measurement_units u on u.id=c.unit WHERE i.projid = :projid and issueid = :issueid and a.site_id=:site_id GROUP BY a.id");
                                                    $query_adjustments->execute(array(":projid" => $projid, ":issueid" => $issueid, ":site_id" => $site_id));

                                                    $issues_scope .= $allsites . '
                                                            <tr class="adjustments ' . $issueid . '" style="background-color:#cccccc">
                                                                <th>#</th>
                                                                <th colspan="5">Sub-Task</th>
                                                                <th colspan="2">Additional Days</th>
                                                            </tr>';
                                                    $scopecount = 0;
                                                    while ($row_adjustments = $query_adjustments->fetch()) {
                                                        $scopecount++;
                                                        $subtask = $row_adjustments["task"];
                                                        $units =  number_format($row_adjustments["units"]) . " " . $row_adjustments["unit"];
                                                        $totalcost = $row_adjustments["unit_cost"] * $row_adjustments["units"];
                                                        $timeline = $row_adjustments["timeline"] . " days";
                                                        $issues_scope .=
                                                            '<tr class="adjustments ' . $issueid . '" style="background-color:#e5e5e5">
                                                                <td>' . $count . '.' . $sites . '.' . $scopecount . '</td>
                                                                <td colspan="5">' . $subtask . '</td>
                                                                <td colspan="2">' . $timeline . '</td>
                                                            </tr>';
                                                    }
                                                }
                                            } else {
                                                $issue_area = "Cost";
                                                $textclass = "text-primary";
                                                $leftjoin = $projcategory == 2 ? "left join tbl_project_tender_details c on c.subtask_id=a.sub_task_id" : "left join tbl_project_direct_cost_plan c on c.subtask_id=a.sub_task_id";

                                                $query_site_id = $db->prepare("SELECT site_id FROM tbl_project_adjustments WHERE issueid=:issueid GROUP BY site_id");
                                                $query_site_id->execute(array(":issueid" => $issueid));

                                                $sites = 0;
                                                while ($rows_site_id = $query_site_id->fetch()) {
                                                    $sites++;
                                                    $site_id = $rows_site_id['site_id'];
                                                    //$allsites = '';

                                                    if ($site_id == 0) {
                                                        $allsites =
                                                            '<tr class="adjustments ' . $issueid . '" style="background-color:#a9a9a9">
                                                                <th colspan="8">Site ' . $sites . ': Way Point Sub Tasks</th>
                                                            </tr>';
                                                    } else {
                                                        $query_site = $db->prepare("SELECT site FROM tbl_project_sites WHERE site_id=:site_id");
                                                        $query_site->execute(array(":site_id" => $site_id));
                                                        $row_site = $query_site->fetch();
                                                        $site = $row_site['site'];

                                                        $allsites =
                                                            '<tr class="adjustments ' . $issueid . '" style="background-color:#a9a9a9">
                                                                <th colspan="8">Site ' . $sites . ': ' . $site . '</th>
                                                            </tr>';
                                                    }

                                                    $query_adjustments = $db->prepare("SELECT t.task, a.units, a.timeline, a.cost, u.unit FROM tbl_project_adjustments a left join tbl_projissues i on i.id = a.issueid left join tbl_task t on t.tkid=a.sub_task_id " . $leftjoin . " left join tbl_measurement_units u on u.id=c.unit WHERE i.projid = :projid and issueid = :issueid and a.site_id=:site_id GROUP BY a.id");
                                                    $query_adjustments->execute(array(":projid" => $projid, ":issueid" => $issueid, ":site_id" => $site_id));

                                                    $issues_scope .= $allsites . '
                                                        <tr class="adjustments ' . $issueid . '" style="background-color:#cccccc">
                                                            <th>#</th>
                                                            <th colspan="3">Sub-Task</th>
                                                            <th colspan="2">Measurement Unit</th>
                                                            <th colspan="2">Additional Cost</th>
                                                        </tr>';
                                                    $scopecount = 0;
                                                    while ($row_adjustments = $query_adjustments->fetch()) {
                                                        $scopecount++;
                                                        $subtask = $row_adjustments["task"];
                                                        $unit =  $row_adjustments["unit"];
                                                        $cost = $row_adjustments["cost"];

                                                        $issues_scope .= '<tr class="adjustments ' . $issueid . '" style="background-color:#e5e5e5">
                                                            <td>' . $count . '.' . $sites . '.' . $scopecount . '</td>
                                                            <td colspan="3">' . $subtask . '</td>
                                                            <td colspan="2">' . $unit . ' </td>
                                                            <td colspan="2">' . number_format($cost, 2) . ' </td>
                                                        </tr>';
                                                    }
                                                }
                                            }
                                            $issues_body .= '
                                            <tr id="s_row">
                                                <td>' . $count . '</td>
                                                <td class="' . $textclass . '"><div onclick="adjustedscopes(' . $issueid . ')">' . $issue . '</div> </td>
                                                <td>' . $category . '</td>
                                                <td>' . $issue_area . ' </td>
                                                <td>' . $impact . '</td>
                                                <td>' . $priority . ' </td>
                                                <td>' . $status . ' </td>
                                                <td>' . date("d-m-Y", strtotime($issuedate)) . ' </td>
                                            </tr>' . $issues_scope;

                                            echo $issues_body;
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
    <!-- end body  -->

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