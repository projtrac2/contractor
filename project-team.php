<?php
$decode_projid = (isset($_GET['proj']) && !empty($_GET["proj"])) ? base64_decode($_GET['proj']) : "";
$projid_array = explode("projid54321", $decode_projid);
$projid = $projid_array[1];
$original_projid = $_GET['proj'];

require('includes/head.php');
if ($permission) {
    try {
        $query_rsProjects = $db->prepare("SELECT * FROM tbl_projects WHERE deleted='0' and projid=:projid");
        $query_rsProjects->execute(array(":projid" => $projid));
        $row_rsProjects = $query_rsProjects->fetch();
        $totalRows_rsProjects = $query_rsProjects->rowCount();

        $projname = $row_rsProjects['projname'];
        $projcode = $row_rsProjects['projcode'];
        $projfscyear = $row_rsProjects['projfscyear'];
        $projduration = $row_rsProjects['projduration'];
        $projcat = $row_rsProjects['projcategory'];
        $projstage = $row_rsProjects["projstage"];

        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();
        $start_date = $end_date = '';
        if ($totalRows_rsTender > 0) {
            $start_date = $row_rsTender['startdate'];
            $end_date = $row_rsTender['enddate'];
        }

        $percent2 = number_format(calculate_project_progress($projid, $projcat), 2);

        function get_roles($role)
        {
            global $db;
            $query_mbrrole = $db->prepare("SELECT * FROM tbl_project_team_roles WHERE id =:role");
            $query_mbrrole->execute(array(":role" => $role));
            $row_mbrrole = $query_mbrrole->fetch();
            $count_row_mbrrole = $query_mbrrole->rowCount();
            return $count_row_mbrrole > 0 ? $row_mbrrole['role'] : "";
        }

        function get_designation($moid)
        {
            global $db;
            $query_rsPMbrDesg = $db->prepare("SELECT * FROM tbl_pmdesignation WHERE moid =:moid ORDER BY moid ASC");
            $query_rsPMbrDesg->execute(array(":moid" => $moid));
            $row_rsPMbrDesg = $query_rsPMbrDesg->fetch();
            return $row_rsPMbrDesg ? $row_rsPMbrDesg['designation'] : "";
        }

        function get_sector($sector_id)
        {
            global $db;
            $query_rsMinistry = $db->prepare("SELECT sector FROM tbl_sectors WHERE stid = :sector_id");
            $query_rsMinistry->execute(array(":sector_id" => $sector_id));
            $row_rsMinistry = $query_rsMinistry->fetch();
            $rows_rsMinistry = $query_rsMinistry->rowCount();
            return $rows_rsMinistry > 0 ? $row_rsMinistry['sector'] : "";
        }

        function get_caretaker($projid, $user_name)
        {
            global $db;
            $standin_responsible = "";
            $query_rsOutput_standin = $db->prepare("SELECT * FROM tbl_project_team_leave  WHERE projid =:projid AND assignee=:user_name AND status = 1");
            $query_rsOutput_standin->execute(array(":projid" => $projid, ":user_name" => $user_name));
            $row_rsOutput_standin = $query_rsOutput_standin->fetch();
            $total_rsOutput_standin = $query_rsOutput_standin->rowCount();

            if ($total_rsOutput_standin > 0) {
                $owner_id = $row_rsOutput_standin['owner'];
                $query_rsOutput = $db->prepare("SELECT * FROM tbl_projmembers  WHERE projid =:projid AND team_type=4 AND responsible=:responsible");
                $query_rsOutput->execute(array(":projid" => $projid, ":responsible" => $owner_id));
                $total_rsOutput = $query_rsOutput->rowCount();
                $standin_responsible = $total_rsOutput > 0 ? true : false;
            }

            $query_rsPMbrs = $db->prepare("SELECT t.*, t.email AS email, tt.title AS ttitle, u.userid FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title WHERE userid = :standin_responsible ORDER BY ptid ASC");
            $query_rsPMbrs->execute(array(":standin_responsible" => $standin_responsible));
            $row_rsPMbrs = $query_rsPMbrs->fetch();
            $count_row_rsPMbrs = $query_rsPMbrs->rowCount();
            return $count_row_rsPMbrs > 0 ?  $row_rsPMbrs['ttitle'] . ". " . $row_rsPMbrs['fullname'] : "";
        }

?>
        <div class="container-fluid">
            <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                <h4 class="contentheader">
                    <i class="fa fa-users" style="color:white"></i> Team
                    <div class="btn-group" style="float:right; margin-right:10px">
                        <input type="button" VALUE="Go Back" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
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
                                <a href="project-timeline.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Timelines</a>
                                <a href="project-progress.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Progress</a>
                                <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px; margin-left:-9px">Team</a>
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
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr class="bg-grey">
                                            <th width="8%"><strong>Photo</strong></th>
                                            <th width="32%"><strong>Fullname</strong></th>
                                            <th width="15%"><strong>Designation</strong></th>
                                            <th width="10%"><strong>Role</strong></th>
                                            <th width="10%"><strong>Availability</strong></th>
                                            <th width="15%"><strong>Email</strong></th>
                                            <th width="10%"><strong>Phone</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query_rsMembers = $db->prepare("SELECT * FROM `tbl_projmembers` WHERE projid=:projid AND team_type=4");
                                        $query_rsMembers->execute(array(":projid" => $projid));
                                        $total_rsMembers = $query_rsMembers->rowCount();
                                        if ($total_rsMembers > 0) {
                                            $rowno = 0;
                                            while ($row_rsMembers = $query_rsMembers->fetch()) {
                                                $rowno++;
                                                $role_id = $row_rsMembers['role'];
                                                $ptid = $row_rsMembers['responsible'];
                                                $query_rsPMbrs = $db->prepare("SELECT t.*, t.email AS email, tt.title AS ttitle, u.userid FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title WHERE userid = :user_id ORDER BY ptid ASC");
                                                $query_rsPMbrs->execute(array(":user_id" => $ptid));
                                                $row_rsPMbrs = $query_rsPMbrs->fetch();
                                                $count_row_rsPMbrs = $query_rsPMbrs->rowCount();
                                                if ($count_row_rsPMbrs > 0) {
                                                    $mbrministry = $row_rsPMbrs['ministry'];
                                                    $mbrdept = $row_rsPMbrs['department'];
                                                    $mbrdesg = $row_rsPMbrs['designation'];
                                                    $userid = $row_rsPMbrs['userid'];
                                                    $availability = $row_rsPMbrs['availability'];
                                                    $file_path = $row_rsPMbrs['floc'];
                                                    $email = $row_rsPMbrs['email'];
                                                    $phone = $row_rsPMbrs['phone'];
                                                    $fullname = $row_rsPMbrs['ttitle'] . " " . $row_rsPMbrs['fullname'];
                                                    $user_role = get_roles($role_id);
                                                    $designation = get_designation($mbrdesg);
                                                    $ministries = get_sector($mbrministry);
                                                    $sections = get_sector($mbrdept);
                                                    $directorates = get_sector($mbrministry);

                                                    $ministry = $ministries != '' ? $ministries : "All " . $ministrylabelplural;
                                                    $section = $sections != '' ? $sections : "All " . $departmentlabelplural;
                                                    $directorate = $directorates != '' ? $directorates : "All " . $ministrylabelplural;

                                                    $p_availability = "<p>Yes</p>";
                                                    if ($availability == 0) {
                                                        $caretaker =    get_caretaker($projid, $ptid);
                                                        $p_availability = '<p  data-toggle="tooltip" data-placement="bottom" title="' . $caretaker . '">No</p>';
                                                    }
                                        ?>
                                                    <tr>
                                                        <td>
                                                            <img src="<?= $file_path; ?>" style="width:30px; height:30px; margin-bottom:0px" />
                                                        </td>
                                                        <td><?= $fullname; ?></td>
                                                        <td><?= $designation; ?></td>
                                                        <td><?= $user_role; ?></td>
                                                        <td><?= $p_availability ?></td>
                                                        <td><?= $email; ?></td>
                                                        <td><?= $phone; ?></td>
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