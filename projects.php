<?php
require('includes/head.php');
if ($permission) {
    try {
        $query_rsProjects = $db->prepare("SELECT p.*, s.sector, g.projsector, g.projdept, g.directorate FROM tbl_projects p inner join tbl_programs g ON g.progid=p.progid inner join tbl_sectors s on g.projdept=s.stid WHERE p.deleted='0' AND p.projcontractor = :projcontractor AND projstage >= 17  ORDER BY p.projid DESC");
        $query_rsProjects->execute(array(":projcontractor" => $user_name));
        $totalRows_rsProjects = $query_rsProjects->rowCount();

        function get_issues($projid)
        {
            global $db;
            $query_issues = $db->prepare("SELECT * FROM  tbl_projissues WHERE projid = :projid");
            $query_issues->execute(array(":projid" => $projid));
            $count_issues = $query_issues->rowCount();
            return $count_issues > 0 ? true : false;
        }

        function get_scope_and_time_issues($projid)
        {
            global $db;
            $query_issues = $db->prepare("SELECT * FROM  tbl_projissues WHERE status=1 AND (issue_area=2 OR issue_area=3) AND projid = :projid");
            $query_issues->execute(array(":projid" => $projid));
            $count_issues = $query_issues->rowCount();
            return $count_issues > 0 ? true : false;
        }

        function get_activity($projid)
        {
            global $db;
            $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid LIMIT 1");
            $query_rsTask_Start_Dates->execute(array(':projid' => $projid));
            $rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
            return $rows_rsTask_Start_Dates ? true : false;
        }

        function get_breakdown($projid)
        {
            global $db;
            $query_rsTargetBreakdown = $db->prepare("SELECT * FROM  tbl_project_target_breakdown WHERE projid=:projid ");
            $query_rsTargetBreakdown->execute(array(':projid' => $projid));
            $totalRows_rsTargetBreakdown = $query_rsTargetBreakdown->rowCount();
            return $totalRows_rsTargetBreakdown > 0 ? true : false;
        }
?>
        <div class="container-fluid">
            <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                <h4 class="contentheader">
                    <i class="fa fa-dashboard" style="color:white"></i> Projects
                    <div class="btn-group" style="float:right">
                        <div class="btn-group" style="float:right">
                        </div>
                    </div>
                </h4>
            </div>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="manageItemTable">
                                    <thead>
                                        <tr style="background-color:#0b548f; color:#FFF">
                                            <th style="width:5%" align="center">#</th>
                                            <th style="width:10%">Code</th>
                                            <th style="width:31%">Project </th>
                                            <th style="width:12%">Start Date</th>
                                            <th style="width:12%">End date</th>
                                            <th style="width:10%">Progress</th>
                                            <th style="width:10%">Status</th>
                                            <th style="width:10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($totalRows_rsProjects > 0) {
                                            $counter = 0;
                                            while ($row_rsProjects = $query_rsProjects->fetch()) {
                                                $counter++;
                                                $projid = $row_rsProjects['projid'];
                                                $projid_hashed = base64_encode("projid54321{$projid}");
                                                $implementation = $row_rsProjects['projcategory'];
                                                $sub_stage = $row_rsProjects['proj_substage'];
                                                $projstage = $row_rsProjects['projstage'];
                                                $projname = $row_rsProjects['projname'];
                                                $projcode = $row_rsProjects['projcode'];
                                                $projstatus = $row_rsProjects['projstatus'];
                                                $project_start_date =  $project_end_date = "";
                                                $projcontractor =  $row_rsProjects['projcategory'];
                                                $proj_progress = calculate_project_progress($projid, $implementation);
                                                $progress = number_format(calculate_project_progress($projid, $implementation), 2);

                                                $query_rsTender_start_Date = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid LIMIT 1");
                                                $query_rsTender_start_Date->execute(array(':projid' => $projid));
                                                $rows_rsTender_start_Date = $query_rsTender_start_Date->fetch();
                                                $total_rsTender_start_Date = $query_rsTender_start_Date->rowCount();
                                                if ($total_rsTender_start_Date > 0) {
                                                    $project_start_date =  $rows_rsTender_start_Date['startdate'];
                                                    $project_end_date =  $rows_rsTender_start_Date['enddate'];
                                                }

                                                $breakdown = get_breakdown($projid);
                                                $activity = get_activity($projid);

                                                $filter = true;
                                                $today = date("Y-m-d");

                                                if ($filter) {
                                        ?>
                                                    <tr>
                                                        <td align="center"><?= $counter ?></td>
                                                        <td><?= $projcode ?></td>
                                                        <td>
                                                            <div class="links" style="background-color:#9E9E9E; color:white; padding:5px;">
                                                                <a href="project-dashboard.php?proj=<?php echo $projid_hashed; ?>" style="color:#FFF; font-weight:bold"><?= $projname ?></a>
                                                            </div>
                                                        </td>
                                                        <td><?= $project_start_date ?></td>
                                                        <td><?= $project_end_date ?></td>
                                                        <td><?= get_progress($progress, $projstatus) ?></td>
                                                        <td><?= get_status($projstatus) ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    Options <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <?php
                                                                    if ($projstage == 17) {
                                                                        if ($sub_stage < 4) {
                                                                    ?>
                                                                            <li>
                                                                                <a type="button" href="add-work-program.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                    <i class="fa fa-plus-square-o"></i> <?= $activity ? "Edit" : "Add" ?> Work Program
                                                                                </a>
                                                                            </li>
                                                                        <?php
                                                                        } else if ($sub_stage == 8) {
                                                                        ?>
                                                                            <li>
                                                                                <a type="button" href="add-target-breakdown.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                    <i class="fa fa-plus-square-o"></i> <?= $breakdown ? "Edit" : "Add" ?> Activity Target Breakdown
                                                                                </a>
                                                                            </li>
                                                                        <?php
                                                                        }
                                                                    } else {
                                                                        ?>
                                                                        <li>
                                                                            <a type="button" href="payment.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                <i class="fa fa-money text-warning"></i> Payment
                                                                            </a>
                                                                        </li>
                                                                        <?php
                                                                        if (get_issues($projid)) {
                                                                        ?>
                                                                            <li>
                                                                                <a type="button" href="project-issues.php?proj=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                    <i class="fa fa-exclamation-triangle text-danger"></i> Project Issues
                                                                                </a>
                                                                            </li>
                                                                        <?php
                                                                        }
                                                                        if (get_scope_and_time_issues($projid)) {
                                                                        ?>
                                                                            <li>
                                                                                <a type="button" href="adjust-work-program.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                    <i class="fa fa-exclamation-triangle text-danger"></i> Adjust Work Program
                                                                                </a>
                                                                            </li>
                                                                    <?php
                                                                        }
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
                    </div>
                </div>
            </div>
        </div>
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