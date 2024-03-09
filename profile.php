<?php
require('includes/head.php');
$results = '';
if ($permission) {
    try {

        $query_rsContrInfo = $db->prepare("SELECT *, dateregistered AS dtregistered FROM tbl_contractor WHERE contrid = :user_name");
        $query_rsContrInfo->execute(array(":user_name" => $user_name));
        $row_rsContrInfo = $query_rsContrInfo->fetch();

        $BusinessType = $row_rsContrInfo["businesstype"];
        $pinStatus = $row_rsContrInfo["pinstatus"];
        $ContrVat = $row_rsContrInfo["vatregistered"];
        $ContrCounty = $row_rsContrInfo["county"];

        $query_rsBzType = $db->prepare("SELECT * FROM tbl_contractorbusinesstype WHERE id='$BusinessType'");
        $query_rsBzType->execute();
        $row_rsBzType = $query_rsBzType->fetch();

        $query_rsContrPinStatus = $db->prepare("SELECT * FROM tbl_contractorpinstatus WHERE id='$pinStatus'");
        $query_rsContrPinStatus->execute();
        $row_rsContrPinStatus = $query_rsContrPinStatus->fetch();

        $query_rsContrVat = $db->prepare("SELECT * FROM tbl_contractorvat WHERE id='$ContrVat'");
        $query_rsContrVat->execute();
        $row_rsContrVat = $query_rsContrVat->fetch();

        $query_rsContrCounty = $db->prepare("SELECT * FROM counties WHERE id='$ContrCounty'");
        $query_rsContrCounty->execute();
        $row_rsContrCounty = $query_rsContrCounty->fetch();

        $query_rsContrDir = $db->prepare("SELECT * FROM tbl_contractordirectors WHERE contrid = :user_name Order BY id ASC");
        $query_rsContrDir->execute(array(":user_name" => $user_name));
        $totalRows_rsContrDir = $query_rsContrDir->rowCount();

        $query_rsContrDocs = $db->prepare("SELECT * FROM tbl_contractordocuments WHERE contrid = :user_name Order BY id ASC");
        $query_rsContrDocs->execute(array(":user_name" => $user_name));
        $row_rsContrDocs = $query_rsContrDocs->fetch();
        $totalRows_rsContrDocs = $query_rsContrDocs->rowCount();


        $query_rsContrProj = $db->prepare("SELECT p.*, g.projsector as sector, g.projsector, g.projdept,g.directorate FROM tbl_projects p inner join tbl_programs g ON g.progid=p.progid WHERE p.deleted='0' AND projcontractor = :user_name Order BY projid ASC");
        $query_rsContrProj->execute(array(":user_name" => $user_name));
        $totalRows_rsContrProj = $query_rsContrProj->rowCount();

        $query_rsPFiles = $db->prepare("SELECT *, date_created AS ufdate, @curRow := @curRow + 1 AS sn FROM tbl_contractordocuments WHERE contrid = :user_name Order BY id ASC");
        $query_rsPFiles->execute(array(":user_name" => $user_name));
        $row_rsPFiles = $query_rsPFiles->fetch();
        $totalRows_rsPFiles = $query_rsPFiles->rowCount();

        function alert_message($title, $msg, $icon, $url)
        {
            return "<script type=\"text/javascript\">
                swal({
                    title: '$title',
                    text: '$msg',
                    type: '$title',
                    timer: 5000,
                    icon:'$icon',
                    showConfirmButton: false
                });
                setTimeout(function(){
                    window.location.href = '$url';
                }, 3000);
            </script>";
        }

        if (isset($_POST['change_password']) && !empty($_POST['change_password'])) {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            if ($confirm_password == $new_password) {
                $auth = new Auth();
                $contractor_details = $auth->get_contractor_by_id($user_name);
                if (password_verify($old_password, $contractor_details->password)) {
                    $change_pass = $auth->change_password($user_name, $new_password);
                    var_dump(password_verify($old_password, $contractor_details->password));
                    if ($change_pass) {
                        $results = alert_message("Success", "Successfully changed password", "success", "logout.php");
                    } else {
                        $results = alert_message("Error", "Password could not be verified", "error", "profile.php");
                    }
                } else {
                    $results = alert_message("Error", "Error check your credentials passwords do not match", "error", "profile.php");
                }
            } else {
                $results = alert_message("Error", "Error check your credentials 3", "error", "profile.php");
            }
        }
    } catch (PDOException $ex) {
        $results = flashMessage("An error occurred: " . $ex->getMessage());
    }
?>

    <!-- start body  -->
    <div class="container-fluid">
        <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:10px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
            <h4 class="contentheader">
                <i class="fa fa-puzzle-piece" style="color:white"></i> Contractor Info
                <input type="button" VALUE="Go Back to Projects Dashboard" class="btn btn-warning pull-right" onclick="location.href='projects.php'" id="btnback">
                <?= $results ?>
            </h4>
        </div>
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="body">
                        <!-- start body -->
                        <div class="table-responsive">
                            <ul class="nav nav-tabs" style="font-size:14px">
                                <li class="active">
                                    <a data-toggle="tab" href="#home"><span class="fa fa-address-card-o"></span> Contractor Info</a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#menu1"><span class="fa fa-users"></span> Contractor List of Directors &nbsp;<span class="badge bg-blue"><?php echo $totalRows_rsContrDir; ?></span></a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#menu2"><span class="fa fa-certificate"></span> Contractor Documents&nbsp;<span class="badge bg-light-green"><?php echo $totalRows_rsContrDocs; ?></span></a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#menu3"><span class="fa fa-certificate"></span> Change Password &nbsp;<span class="badge bg-light-green">|</span></a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div id="home" class="tab-pane fade in active">
                                    <div style="color:#333; background-color:#EEE; width:100%; height:30px">
                                        <h4><i class="fa fa-list" style="font-size:25px;color:blue"></i> Project Contractor Information</h4>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover contractor_info" width="98%">
                                            <thead>
                                                <tr id="colrow">
                                                    <th style="width:30%">Title</th>
                                                    <th style="width:70%">Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Contractor Name</td>
                                                    <td><?php echo $row_rsContrInfo["contractor_name"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Pin Number</td>
                                                    <td><?php echo $row_rsContrInfo["pinno"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Business Registration Number</td>
                                                    <td><?php echo $row_rsContrInfo["busregno"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Business Type</td>
                                                    <td><?php echo $row_rsBzType["type"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Date Registered</td>
                                                    <td><?php echo $row_rsContrInfo["dtregistered"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Pin Status</td>
                                                    <td><?php echo $row_rsContrPinStatus["pin_status"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Is VAT Registered</td>
                                                    <td><?php echo $row_rsContrVat["vat"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Phone Number</td>
                                                    <td><?php echo $row_rsContrInfo["phone"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Email Address</td>
                                                    <td><?php echo $row_rsContrInfo["email"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Postal Address</td>
                                                    <td><?php echo $row_rsContrInfo["contact"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Physical Address</td>
                                                    <td><?php echo $row_rsContrInfo["address"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>City/Town</td>
                                                    <td><?php echo $row_rsContrInfo["city"]; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>County</td>
                                                    <td><?php echo $row_rsContrCounty["name"]; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="menu1" class="tab-pane fade">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover contractor_info" width="98%">
                                            <thead>
                                                <tr id="colrow">
                                                    <td width="5%">
                                                        <div align="center">#</div>
                                                    </td>
                                                    <td width="50%">Director Full Name</td>
                                                    <td width="20%">ID/Passport Number</td>
                                                    <td width="20%">Nationality</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sn = 0;
                                                while ($row_rsContrDir = $query_rsContrDir->fetch()) {
                                                    $sn = $sn + 1;
                                                    $Dirfullname = $row_rsContrDir['fullname'];
                                                    $DirID = $row_rsContrDir['pinpassport'];
                                                    $DirNat = $row_rsContrDir['nationality'];

                                                    $query_rsDirNat = $db->prepare("SELECT * FROM tbl_contractornationality WHERE id='$DirNat'");
                                                    $query_rsDirNat->execute();
                                                    $row_rsDirNat = $query_rsDirNat->fetch();
                                                ?>
                                                    <tr>
                                                        <td align="center"><?php echo $sn; ?></td>
                                                        <td style="padding-left:15px"><?php echo $Dirfullname; ?></td>
                                                        <td style="padding-left:15px"><?php echo $DirID; ?></td>
                                                        <td style="padding-left:15px"><?php echo $row_rsDirNat['nationality']; ?></td>

                                                    </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="menu2" class="tab-pane fade">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover contractor_info" width="98%" border="1">
                                            <thead>
                                                <tr id="colrow">
                                                    <td width="3%">
                                                        <div align="center"><strong># </strong></div>
                                                    </td>
                                                    <td width="52%"> File Name</td>
                                                    <td width="10%"> File Type</td>
                                                    <td width="15%"> File Date</td>
                                                    <td width="20%" data-orderable="false">
                                                        <div align="center"><i class="fa fa-cloud-download" style="font-size:24px;color:red" alt="download"></i></div>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($totalRows_rsPFiles > 0) {
                                                    if ($row_rsPFiles['file_format'] == "rtf" || $row_rsPFiles['file_format'] == "doc" || $row_rsPFiles['file_format'] == "docx") {
                                                        $docformat = "Word";
                                                    } elseif ($row_rsPFiles['file_format'] == "pdf" || $row_rsPFiles['file_format'] == "PDF") {
                                                        $docformat = "PDF";
                                                    } else {
                                                        $docformat = $row_rsPFiles['file_format'];
                                                    }
                                                    $sn = 0;
                                                    do {
                                                        $sn = $sn + 1;
                                                ?>
                                                        <tr>
                                                            <td width="3%" height="35">
                                                                <div align="center"><?php echo $sn; ?></div>
                                                            </td>
                                                            <td width="52%" height="35">
                                                                <div><?php echo $row_rsPFiles['attachment_purpose']; ?></div>
                                                            </td>
                                                            <td width="10%" height="35">
                                                                <div align="center"><?php echo $docformat; ?></div>
                                                            </td>
                                                            <td width="15%">
                                                                <div align="center"><?php echo date("d M Y", strtotime($row_rsPFiles['ufdate'])); ?></div>
                                                            </td>
                                                            <td width="20%">
                                                                <div align="center"><a href="<?php echo $row_rsPFiles['floc']; ?>" type="button" class="btn bg-light-green waves-effect" title="Download File" target="new">Download</a></div>
                                                            </td>
                                                        </tr>
                                                <?php } while ($row_rsPFiles = $query_rsPFiles->fetch());
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="menu3" class="tab-pane fade">
                                    <form class="form-horizontal" action="" method="post">
                                        <div class="form-group">
                                            <label for="OldPassword" class="col-sm-3 control-label">Old Password</label>
                                            <div class="col-sm-9">
                                                <div class="form-line">
                                                    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Old Password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="NewPassword" class="col-sm-3 control-label">New Password</label>
                                            <div class="col-sm-9">
                                                <div class="form-line">
                                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="NewPasswordConfirm" class="col-sm-3 control-label">New Password (Confirm)</label>
                                            <div class="col-sm-9">
                                                <div class="form-line">
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="New Password (Confirm)" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-9">
                                                <input type="hidden" name="change_password" value="change_password">
                                                <button type="submit" class="btn btn-danger">SUBMIT</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- end body -->
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
    <script>
        $(document).ready(function() {
            $('.contractor_info').DataTable();
        });
    </script>