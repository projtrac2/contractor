<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : header("Location: projects");
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];

$original_projid = $_GET['proj'];
require('includes/head.php');

if ($permission) {
    try {
        $query_projdetails = $db->prepare("SELECT * FROM tbl_projects WHERE projid=:projid");
        $query_projdetails->execute(array(":projid" => $projid));
        $row_projdetails = $query_projdetails->fetch();
        $projname = $row_projdetails['projname'];
        $projcategory = $row_projdetails['projcategory'];
        $percent2 = calculate_project_progress($projid, $projcategory);

        $query_issues = $db->prepare("SELECT i.id, i.origin, p.projid, p.projname AS projname,p.projcategory, category, observation, recommendation, status, priority, i.created_by AS monitor, i.date_created AS issuedate, issue_area FROM tbl_projissues i INNER JOIN tbl_projects p ON p.projid=i.projid INNER JOIN tbl_projrisk_categories c ON c.rskid=i.risk_category WHERE p.projid=:projid");
        $query_issues->execute(array(":projid" => $projid));
        $count_issues = $query_issues->rowCount();
    } catch (PDOException $ex) {
        $result = flashMessage("An error occurred: " . $ex->getMessage());
        print($result);
    }
?>
    <!-- JQuery Nestable Css -->
    <link href="projtrac-dashboard/plugins/nestable/jquery-nestable.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/strategicplan/view-strategic-plan-framework.css">
    <link rel="stylesheet" href="css/highcharts.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-3d.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
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
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="header" style="padding-bottom:0px">
                        <div class="button-demo" style="margin-top:-15px">
                            <a href="project-dashboard.php?proj=<?php echo $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px">Dashboard</a>
                            <a href="project-timeline.php?proj=<?php echo $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Timeline</a>
                            <a href="project-team.php?proj=<?php echo $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Team</a>
                            <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px; margin-left:-9px">Issues</a>
                            <a href="project-media.php?proj=<?php echo $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Media</a>
                        </div>
                    </div>
                    <h4>
                        <div class="col-lg-10 col-md-10 col-sm-12 col-xs-12" style="font-size:15px; background-color:#CDDC39; border:#CDDC39 thin solid; border-radius:5px; margin-bottom:2px; height:25px; padding-top:2px; vertical-align:center">
                            Project Name: <font color="white"><?php echo $projname; ?></font>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12" style="font-size:15px; background-color:#CDDC39; border-radius:5px; height:25px; margin-bottom:2px">
                            <div class="progress" style="height:23px; margin-bottom:1px; margin-top:1px; color:black">
                                <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="<?= $percent2 ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= $percent2 ?>%; margin:auto; padding-left: 10px; padding-top: 3px; text-align:left; color:black">
                                    <?= $percent2 ?>%
                                </div>
                            </div>
                        </div>
                    </h4>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                <thead>
                                    <tr class="bg-orange">
                                        <th style="width:4%">#</th>
                                        <th style="width:32%">Issue</th>
                                        <th style="width:8%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($count_issues > 0) {
                                        $nm = 0;

                                        while ($row_issues = $query_issues->fetch()) {
                                            $nm = $nm + 1;
                                            $id = $row_issues['id'];
                                            $category = $row_issues['category'];
                                            $observation = $row_issues['observation'];
                                            $issueareaid = $row_issues['issue_area'];
                                            $observation = $row_issues['observation'];
                                            $monitorid = $row_issues['monitor'];
                                            $recommendation = $row_issues['recommendation'];
                                            $issuedate = $row_issues['issuedate'];
                                            $issuestatusid = $row_issues['status'];
                                            $priorityid = $row_issues['priority'];

                                            if ($issuestatusid == 1) {
                                                $issuestatus = "Open";
                                            } elseif ($issuestatusid == 2) {
                                                $issuestatus = "Analysis";
                                            } elseif ($issuestatusid == 3) {
                                                $issuestatus = "Analyzed";
                                            } elseif ($issuestatusid == 4) {
                                                $issuestatus = "Escalated";
                                            } elseif ($issuestatusid == 5) {
                                                $issuestatus = "Continue";
                                            } elseif ($issuestatusid == 6) {
                                                $issuestatus = "On Hold";
                                            } elseif ($issuestatusid == 7) {
                                                $issuestatus = "Closed";
                                            }
                                    ?>
                                            <tr style="background-color:#fff">
                                                <td align="center"><?php echo $nm; ?></td>
                                                <td><?php echo $observation; ?></td>
                                                <td <?= $styled ?>><?php echo $issuestatus; ?></td>
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
?>