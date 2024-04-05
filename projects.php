<?php
require('includes/head.php');
if ($permission) {
    try {
        $query_rsProjects = $db->prepare("SELECT p.*, s.sector, g.projsector, g.projdept, g.directorate FROM tbl_projects p inner join tbl_programs g ON g.progid=p.progid inner join tbl_sectors s on g.projdept=s.stid WHERE p.deleted='0' AND p.projcontractor = :projcontractor AND projstage >= 8  ORDER BY p.projid DESC");
        $query_rsProjects->execute(array(":projcontractor" => $user_name));
        $totalRows_rsProjects = $query_rsProjects->rowCount();

        function get_progress($progress, $projstatus)
        {
            $css_class = "progress-bar progress-bar-info progress-bar-striped active";
            $progress_bar = $progress;
            if ($progress == 100 && $projstatus == 5) {
                $css_class = "progress-bar progress-bar-success progress-bar-striped active";
                $progress_bar = 100;
            } else if ($progress > 100) {
                if ($projstatus == 5) {
                    $css_class = "progress-bar progress-bar-success progress-bar-striped active";
                    $progress_bar = 100;
                } else {
                    $css_class = "progress-bar progress-bar-info progress-bar-striped active";
                    $progress_bar = 100;
                }
            } else if ($progress <  100 && $projstatus == 5) {
                $css_class = "progress-bar progress-bar-success progress-bar-striped active";
                $progress_bar = 100;
            }

            return  '
            <div class="progress" style="height:20px; font-size:10px; color:black">
                <div class="' . $css_class . '" role="progressbar" aria-valuenow="' . $progress_bar . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress_bar . '%; height:20px; font-size:10px; color:black">
                    ' . $progress . '%
                </div>
            </div>';
        }

        function get_status($projstatus)
        {
            global $db;
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
            return $status;
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
                                                $project_start_date =  $row_rsProjects['projstartdate'];
                                                $project_end_date =  $row_rsProjects['projenddate'];
                                                $projcontractor =  $row_rsProjects['projcategory'];
                                                $proj_progress = calculate_project_progress($projid, $implementation);
                                                $progress = number_format(calculate_project_progress($projid, $implementation), 2);

                                                $query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid LIMIT 1");
                                                $query_rsTask_Start_Dates->execute(array(':projid' => $projid));
                                                $rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch();
                                                $activity = $rows_rsTask_Start_Dates ? "Edit" : "Add";

                                                $query_rsTender_start_Date = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid LIMIT 1");
                                                $query_rsTender_start_Date->execute(array(':projid' => $projid));
                                                $rows_rsTender_start_Date = $query_rsTender_start_Date->fetch();
                                                $total_rsTender_start_Date = $query_rsTender_start_Date->rowCount();
                                                if ($total_rsTender_start_Date > 0) {
                                                    $project_start_date =  $rows_rsTender_start_Date['startdate'];
                                                    $project_end_date =  $rows_rsTender_start_Date['enddate'];
                                                }

                                                $filter = false;


                                                $today = date("Y-m-d");

                                                if ($projstage == 8) {
                                                    if ($sub_stage == 1 || $project_start_date <= $today) {
                                                        $filter = true;
                                                    } else if ($sub_stage == 2) {
                                                        $filter = true;
                                                    }
                                                } else {
                                                    $filter = true;
                                                    if ($sub_stage == 0) {
                                                    } else if ($sub_stage == 1) {
                                                    } else if ($sub_stage == 2) {
                                                    }
                                                }


                                                $query_rsPayement_reuests =  $db->prepare("SELECT * FROM  tbl_contractor_payment_requests WHERE status <> 3 AND contractor_id=:contractor_id AND projid=:projid");
                                                $query_rsPayement_reuests->execute(array(":contractor_id" => $user_name, ":projid" => $projid));
                                                $total_rsPayement_reuests = $query_rsPayement_reuests->rowCount();
                                                $issue = true;
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
                                                                    if ($projstage == 8) {
                                                                        if ($sub_stage > 0) {
                                                                    ?>
                                                                            <li>
                                                                                <a type="button" href="add-work-program.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                    <i class="fa fa-plus-square-o"></i> <?= $activity ?> Work Program
                                                                                </a>
                                                                            </li>
                                                                        <?php
                                                                        }
                                                                    } else {
                                                                        ?>
                                                                        <li>
                                                                            <a type="button" href="payment.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                <i class="fa fa-money text-warning"></i> Payment Requests
                                                                            </a>
                                                                        </li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <li>
                                                                        <a type="button" href="project-issues.php?proj=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                            <i class="fa fa-exclamation-triangle text-danger"></i> Project Issues
                                                                        </a>
                                                                    </li>
                                                                    <?php
                                                                    if ($issue) {
                                                                    ?>
                                                                        <li>
                                                                            <a type="button" href="adjust-work-program.php?projid=<?= $projid_hashed ?>" id="addFormModalBtn">
                                                                                <i class="fa fa-exclamation-triangle text-danger"></i> Adjust Work Program
                                                                            </a>
                                                                        </li>
                                                                    <?php
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