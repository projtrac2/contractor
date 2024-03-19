<?php
require('includes/head.php');
if ($permission) {
    try {
        $query_rsProjects = $db->prepare("SELECT p.*, s.sector, g.projsector, g.projdept, g.directorate FROM tbl_projects p inner join tbl_programs g ON g.progid=p.progid inner join tbl_sectors s on g.projdept=s.stid WHERE p.deleted='0' AND p.projcontractor = :projcontractor AND projstage=10 ORDER BY p.projid DESC");
        $query_rsProjects->execute(array(":projcontractor" => $user_name));
        $totalRows_rsProjects = $query_rsProjects->rowCount();
    } catch (PDOException $ex) {
        $results = flashMessage("An error occurred: " . $ex->getMessage());
    }
?>
    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:10px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-dashboard" style="color:white"></i> Dashboard
                <div class="btn-group" style="float:right">
                    <div class="btn-group" style="float:right">
                    </div>
                </div>
            </h4>
        </div>
        <div class="row clearfix">
            <div class="block-header">
                <?= $results; ?>
                <div class="header" style="padding-bottom:0px">
                    <div class="button-demo" style="margin-top:-15px">
                        <span class="label bg-black" style="font-size:18px"><img src="images/proj-icon.png" alt="Project Menu" title="Project Menu" style="vertical-align:middle; height:25px" /> Menu</span>
                        <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px">Dashboard</a>
                        <a href="projects.php" class="btn bg-light-blue waves-effect" style="margin-top:10px">Projects</a>
                        <a href="payment.php" class="btn bg-light-blue waves-effect" style="margin-top:10px">Payment</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="manageItemTable">
                                <thead>
                                    <tr style="background-color:#0b548f; color:#FFF">
                                        <th style="width:5%" align="center">#</th>
                                        <th style="width:10%">Code</th>
                                        <th style="width:41%">Project </th>
                                        <th style="width:12%">Start Date</th>
                                        <th style="width:12%">End date</th>
                                        <th style="width:10%">Progress</th>
                                        <th style="width:10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($totalRows_rsProjects > 0) {
                                        $counter = 0;
                                        while ($row_rsProjects = $query_rsProjects->fetch()) {
                                            $projid = $row_rsProjects['projid'];
                                            $projid_hashed = base64_encode("projid54321{$projid}");
                                            $implementation = $row_rsProjects['projcategory'];
                                            $sub_stage = $row_rsProjects['proj_substage'];
                                            $project_department = $row_rsProjects['projsector'];
                                            $project_section = $row_rsProjects['projdept'];
                                            $project_directorate = $row_rsProjects['directorate'];
                                            $projname = $row_rsProjects['projname'];
                                            $projcode = $row_rsProjects['projcode'];
                                            $progress = calculate_project_progress($projid, $implementation);
                                            $projstatus = $row_rsProjects['projstatus'];
                                            $projectid = base64_encode("projid54321{$projid}");

                                            $start_date = date('Y-m-d');
                                            $projduration =  $row_rsProjects['projduration'];
                                            $project_start_date =  $row_rsProjects['projstartdate'];
                                            $project_end_date = date('Y-m-d', strtotime($project_start_date . ' + ' . $projduration . ' days'));
                                            $projcontractor =  $row_rsProjects['projcategory'];

                                            $counter++;
                                            $query_rsTask_Start_Dates = $db->prepare("SELECT MIN(start_date) as start_date, MAX(end_date) as end_date FROM tbl_program_of_works WHERE projid=:projid LIMIT 1");
                                            $query_rsTask_Start_Dates->execute(array(':projid' => $projid));
                                            $rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                            $total_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();

                                            if (!is_null($rows_rsTask_Start_Dates['start_date'])) {
                                                $project_start_date =  $rows_rsTask_Start_Dates['start_date'];
                                                $project_end_date =  $rows_rsTask_Start_Dates['end_date'];
                                            } else {
                                                $query_rsTender_start_Date = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid LIMIT 1");
                                                $query_rsTender_start_Date->execute(array(':projid' => $projid));
                                                $rows_rsTender_start_Date = $query_rsTender_start_Date->fetch();
                                                $total_rsTender_start_Date = $query_rsTender_start_Date->rowCount();
                                                if ($total_rsTender_start_Date > 0) {
                                                    $project_start_date =  $rows_rsTender_start_Date['startdate'];
                                                    $project_end_date =  $rows_rsTender_start_Date['enddate'];
                                                }
                                            }

                                            $query_Projstatus =  $db->prepare("SELECT * FROM tbl_status WHERE statusid = :projstatus");
                                            $query_Projstatus->execute(array(":projstatus" => $projstatus));
                                            $row_Projstatus = $query_Projstatus->fetch();
                                            $total_Projstatus = $query_Projstatus->rowCount();
                                            $status = "";
                                            if ($total_Projstatus > 0) {
                                                $status_name = $row_Projstatus['statusname'];
                                                $status_class = $row_Projstatus['class_name'];
                                                $status = '<button type="button" class="' . $status_class . '" style="width:100%">' . $status_name . '</button>';
                                            }

                                            $project_progress = '
                                                    <div class="progress" style="height:20px; font-size:10px; color:black">
                                                        <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
                                                            ' . $progress . '%
                                                        </div>
                                                    </div>';
                                            if ($progress == 100) {
                                                $project_progress = '
                                                        <div class="progress" style="height:20px; font-size:10px; color:black">
                                                            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
                                                            ' . $progress . '%
                                                            </div>
                                                        </div>';
                                            }
                                    ?>
                                            <tr>
                                                <td align="center"><?= $counter ?></td>
                                                <td><?= $projcode ?></td>
                                                <td>
                                                    <div class="links" style="background-color:#9E9E9E; color:white; padding:5px;">
                                                        <a href="project-dashboard.php?proj=<?php echo $projectid; ?>" style="color:#FFF; font-weight:bold"><?= $projname ?></a>
                                                    </div>
                                                </td>
                                                <td><?= $project_start_date ?></td>
                                                <td><?= $project_end_date ?></td>
                                                <td><?= $project_progress ?></td>
                                                <td><?= $status ?></td>
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

        <!-- end body  -->
    <?php
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
    ?>

    <script src="assets/js/monitoring/issues.js"></script>