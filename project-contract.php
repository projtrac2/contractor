<?php
require('includes/head.php');
if ($permission && (isset($_GET['proj']) && !empty($_GET["proj"]))) {
    $decode_projid =  base64_decode($_GET['proj']);
    $projid_array = explode("projid54321", $decode_projid);
    $projid = $projid_array[1];
    $original_projid = $_GET['proj'];
    try {
        $percent2 = number_format(calculate_project_progress($projid, $projcat), 2);
        $query_rsMyP =  $db->prepare("SELECT * FROM tbl_projects WHERE deleted='0' AND projid = '$projid'");
        $query_rsMyP->execute();
        $row_rsMyP = $query_rsMyP->fetch();
        $rows_rsMyP = $query_rsMyP->rowCount();

        $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
        $query_rsTender->execute(array(":projid" => $projid));
        $row_rsTender = $query_rsTender->fetch();
        $totalRows_rsTender = $query_rsTender->rowCount();

        if ($rows_rsMyP > 0 && $totalRows_rsTender > 0) {
            $implementation_type = $row_rsMyP["projcategory"];
            $projname = $row_rsMyP['projname'];
            $projcode = $row_rsMyP['projcode'];
            $projcost = $row_rsMyP['projcost'];
            $projcat = $row_rsMyP['projcategory'];
            $projfscyear = $row_rsMyP['projfscyear'];
            $projduration = $row_rsMyP['projduration'];
            $implimentation_type = $row_rsMyP['projcategory'];
            $percent2 = number_format(calculate_project_progress($projid, $implimentation_type), 2);

            $locations = [];
            foreach ($projwards as $projward) {
                $query_projward = $db->prepare("SELECT parent, state FROM tbl_state WHERE id=:projward");
                $query_projward->execute(array(":projward" => $projward));
                $row_projward = $query_projward->fetch();
                $wards = $row_projward['state'];
                $parent = $row_projward['parent'];

                $query_projsb = $db->prepare("SELECT state FROM tbl_state WHERE id=:parent");
                $query_projsb->execute(array(":parent" => $parent));
                $row_projsb = $query_projsb->fetch();
                $subcounty = $wards == "Headquarters" ? "" : $row_projsb['state'] . " Sub-County";

                $location = '<span data-container="body" data-toggle="tooltip" data-html="true" data-placement="bottom" title="' . $subcounty . '" style="color:#2196F3">' . $wards . '</span>';

                $locations[] = $location;
            }
            $projlocations = implode("; ", $locations);

            $contractrefno = $tenderno  =  $tendertitle = $tendertype = $tendercat = $procurementmethod = "";
            $tenderevaluationdate = $tenderawarddate = $tendernotificationdate = $tendersignaturedate = "";
            $tenderstartdate = $tenderenddate = $financialscore = $technicalscore = $comments = $contractor_id = "";
            $pinnumber = $bizregno = $biztype = "";


            $contractrefno = $row_rsTender['contractrefno'];
            $tenderno = $row_rsTender['tenderno'];
            $tendertitle = $row_rsTender['tendertitle'];
            $tendertypeid = $row_rsTender['tendertype'];
            $tendercatid = $row_rsTender['tendercat'];
            $procurementmethodid = $row_rsTender['procurementmethod'];

            $tenderevaluationdate = $row_rsTender['evaluationdate'];
            $tenderawarddate = $row_rsTender['awarddate'];
            $tendernotificationdate = $row_rsTender['notificationdate'];
            $tendersignaturedate = $row_rsTender['signaturedate'];
            $tenderstartdate = $row_rsTender['startdate'];
            $tenderenddate = $row_rsTender['enddate'];
            $financialscore = $row_rsTender['financialscore'];
            $technicalscore = $row_rsTender['technicalscore'];
            $comments = $row_rsTender['comments'];
            $contractor_id = $row_rsTender['contractor'];

            $query_cont = $db->prepare("SELECT contractor_name, pinno, busregno, type FROM tbl_contractor c left join tbl_contractorbusinesstype b on c.businesstype=b.id WHERE contrid='$contractor_id'");
            $query_cont->execute();
            $row_cont = $query_cont->fetch();
            if ($row_cont) {
                $contractor = $row_cont['contractor_name'];
                $pinnumber = $row_cont['pinno'];
                $bizregno = $row_cont['busregno'];
                $biztype = $row_cont['type'];
            }


            $query_rsprocurementmethod = $db->prepare("SELECT * FROM tbl_procurementmethod WHERE id=:method");
            $query_rsprocurementmethod->execute(array(":method" => $procurementmethodid));
            $row_rsprocurementmethod = $query_rsprocurementmethod->fetch();
            $procurementmethod = $row_rsprocurementmethod['method'];


            $query_rsTender = $db->prepare("SELECT * FROM tbl_tenderdetails WHERE projid=:projid");
            $query_rsTender->execute(array(":projid" => $projid));
            $row_rsTender = $query_rsTender->fetch();
            $totalRows_rsTender = $query_rsTender->rowCount();
            $start_date = $end_date = '';
            if ($totalRows_rsTender > 0) {
                $start_date = $row_rsTender['startdate'];
                $end_date = $row_rsTender['enddate'];
            }
            $query_rscategory = $db->prepare("SELECT * FROM tbl_tender_category WHERE id=:tendercat");
            $query_rscategory->execute(array(":tendercat" => $tendercatid));
            $row_rscategory = $query_rscategory->fetch();
            $tendercat = $row_rscategory["category"];

            $query_rstender = $db->prepare("SELECT * FROM tbl_tender_type WHERE id=:typeid");
            $query_rstender->execute(array(":typeid" => $tendertypeid));
            $row_rstender = $query_rstender->fetch();
            $tendertype = $row_rstender["type"];

?>
            <div class="container-fluid">
                <div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
                    <h4 class="contentheader">
                        <i class="fa fa-users" style="color:white"></i> Contract
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
                                    <a href="project-team.php?proj=<?= $original_projid; ?>" class="btn bg-light-blue waves-effect" style="margin-top:10px; margin-left:-9px">Team</a>
                                    <a href="#" class="btn bg-grey waves-effect" style="margin-top:10px; margin-left:-9px">Contract</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="card">
                            <div class="row clearfix">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px;">
                                    <ul class="list-group">
                                        <li class="list-group-item list-group-item list-group-item-action active"> Project: <?= $projname ?> </li>
                                        <li class="list-group-item"><strong> Code: </strong> <?= $projcode ?> </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-header">
                                <ul class="nav nav-tabs" style="font-size:14px">
                                    <li class="active">
                                        <a data-toggle="tab" href="#menu1">
                                            <i class="fa fa-list-alt bg-green" aria-hidden="true"></i> Main Contract Details &nbsp;<span class="badge bg-green">|</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#menu2">
                                            <i class="fa fa-certificate bg-blue" aria-hidden="true"></i> Contract Statutory Guarantees &nbsp;<span class="badge bg-blue">|</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#contract_cost">
                                            <i class="fa fa-money bg-orange" aria-hidden="true"></i> Contract Cost &nbsp;<span class="badge bg-orange">|</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-toggle="tab" href="#menu3">
                                            <i class="fa fa-paperclip bg-brown" aria-hidden="true"></i> Other Contract Details &nbsp;<span class="badge bg-brown">|</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="body">
                                <div class="tab-content">
                                    <div id="menu1" class="tab-pane fade in active">
                                        <fieldset class="scheduler-border" style="background-color:#edfcf1; border-radius:3px">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px"><i class="fa fa-briefcase" style="color:#F44336" aria-hidden="true"></i> Contractor Details</legend>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-inline">
                                                    <label for="">Contractor Name</label>
                                                    <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%">
                                                        <?php echo $contractor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="contrinfo">
                                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                                    <label for="">Pin Number</label>
                                                    <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $pinnumber ?></div>
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                                    <label for="">Business Reg No.</label>
                                                    <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $bizregno ?></div>
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                                    <label for="">Business Type</label>
                                                    <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $biztype ?></div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="scheduler-border" style="border-radius:3px">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-list" style="color:#F44336" aria-hidden="true"></i> Tender and Contract Details
                                            </legend>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Contract Ref. Number *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $contractrefno ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Contract Signature Date *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tendersignaturedate ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Contract Start Date *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tenderstartdate ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Contract Expiry Date *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tenderenddate ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Number *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tenderno ?></div>
                                            </div>
                                            <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                                                <label for="Title">Tender Title *:</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; height: auto; width:98%"><?= $tendertitle; ?>
                                                </div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Type *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?php echo $tendertype; ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Category *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?php echo $tendercat; ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Procurement Method *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?php echo $procurementmethod ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Evaluation Date *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tenderevaluationdate ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Technical Score *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $technicalscore ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Financial Score *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $financialscore ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Award Date *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tenderawarddate ?></div>
                                            </div>
                                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                                <label for="">Tender Notification Date *</label>
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; width:98%"><?= $tendernotificationdate ?></div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div id="menu2" class="tab-pane fade">
                                        <fieldset class="scheduler-border" style="border-radius:3px">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-certificate" style="color:#F44336" aria-hidden="true"></i> Statutory Guarantees
                                            </legend>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered" id="guarantees_table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:5%">#</th>
                                                                <th style="width:35%">Guarantee</th>
                                                                <th style="width:13%">Start Date</th>
                                                                <th style="width:13%">End Date</th>
                                                                <th style="width:10%">Duration</th>
                                                                <th style="width:10%">Due In</th>
                                                                <th style="width:14%">Notification</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $query_contract_guarantees = $db->prepare("SELECT * FROM tbl_contract_guarantees WHERE projid=:projid");
                                                            $query_contract_guarantees->execute(array(":projid" => $projid));
                                                            $totalRows_contract_guarantees = $query_contract_guarantees->rowCount();

                                                            if ($totalRows_contract_guarantees > 0) {
                                                                $rowno = 0;
                                                                while ($row_contract_guarantees = $query_contract_guarantees->fetch()) {
                                                                    $rowno++;
                                                                    $start_date = $row_contract_guarantees['start_date'];
                                                                    $duration = $row_contract_guarantees['duration'];
                                                                    $notification = $row_contract_guarantees['notification'];
                                                                    $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration . ' days'));

                                                                    $today = date('Y-m-d');
                                                                    $origin = date_create($today);
                                                                    $target = date_create($end_date);
                                                                    $interval = date_diff($origin, $target);
                                                                    $remaining_time = $interval->format('%a');

                                                                    if ($remaining_time >= 30) {
                                                                        $badge = "bg-green";
                                                                    } elseif ($remaining_time < 30 && $remaining_time >= 10) {
                                                                        $badge = "bg-orange";
                                                                    } elseif ($remaining_time < 10) {
                                                                        $badge = "bg-danger";
                                                                    }
                                                            ?>
                                                                    <tr id="guarantee_row">
                                                                        <td style="width:5%"><?= $rowno ?></td>
                                                                        <td style="width:35%"><?= $row_contract_guarantees['guarantee'] ?></td>
                                                                        <td style="width:13%"><?= $start_date ?></td>
                                                                        <td style="width:13%"><?= $end_date ?></td>
                                                                        <td style="width:10%"><?= $duration ?> Days</td>
                                                                        <td style="width:10%"><span class="badge <?= $badge ?>"><?= $remaining_time ?> Days</span></td>
                                                                        <td style="width:14%"><?= $notification ?> Days</td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div id="menu3" class="tab-pane fade">
                                        <fieldset class="scheduler-border" style="border-radius:3px">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-commenting" style="color:#F44336" aria-hidden="true"></i> Comments
                                            </legend>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-control require" style="border:#CCC thin solid; border-radius:5px; height: auto; width:98%"><?= $comments ?></div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="scheduler-border" style="border-radius:3px">
                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                <i class="fa fa-paperclip" style="color:#F44336" aria-hidden="true"></i> Files and Documents
                                            </legend>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered" id="files_table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width:40%">Attachments *</th>
                                                                <th style="width:58%">Attachment Purpose *</th>
                                                                <th style="width:2%">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $query_contract_files = $db->prepare("SELECT * FROM tbl_files WHERE projid=:projid AND projstage=4");
                                                            $query_contract_files->execute(array(":projid" => $projid));
                                                            $totalRows_contract_files = $query_contract_files->rowCount();

                                                            if ($totalRows_contract_files > 0) {
                                                                while ($row_contract_files = $query_contract_files->fetch()) {
                                                                    $filepath = $row_contract_files['floc'];
                                                            ?>
                                                                    <tr>
                                                                        <td style="width:40%"><?= $row_contract_files['filename'] ?></td>
                                                                        <td style="width:58%"><?= $row_contract_files['reason'] ?></td>
                                                                        <td style="width:2%">
                                                                            <a href="<?= $filepath ?>" type="button" name="addplus" title="Download document" class="btn btn-success btn-sm" download>
                                                                                <i class="fa fa-cloud-download" aria-hidden="true"></i>
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div id="contract_cost" class="tab-pane fade">
                                        <div class="body">
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
                                                            <i class="fa fa-list-ol" aria-hidden="true"></i> Site <?= $counter ?> : <?= $site ?>
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
                                                                    $output_id = $row_Output['id'];
                                                                    $output = $row_Output['indicator_name'];
                                                        ?>
                                                                    <fieldset class="scheduler-border">
                                                                        <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                                            <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $output_counter ?> : <?= $output ?>
                                                                        </legend>
                                                                        <?php
                                                                        $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                                                                        $query_rsMilestone->execute(array(":output_id" => $output_id));
                                                                        $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                                                        if ($totalRows_rsMilestone > 0) {
                                                                            while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                                                $milestone = $row_rsMilestone['milestone'];
                                                                                $msid = $row_rsMilestone['msid'];
                                                                                $query_rsOther_cost_plan_budget =  $db->prepare("SELECT SUM(unit_cost * units_no) as sum_cost FROM tbl_project_tender_details WHERE projid =:projid AND site_id=:site_id AND tasks=:tasks");
                                                                                $query_rsOther_cost_plan_budget->execute(array(":projid" => $projid, ':site_id' => $site_id, ":tasks" => $msid));
                                                                                $row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();
                                                                                $sum_cost = $row_rsOther_cost_plan_budget['sum_cost'] != null ? $row_rsOther_cost_plan_budget['sum_cost'] : 0;
                                                                                $total_cost = 0;
                                                                        ?>
                                                                                <div class="row clearfix">
                                                                                    <input type="hidden" name="task_amount[]" id="task_amount<?= $msid ?>" class="task_costs" value="<?= $sum_cost ?>">
                                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                        <div class="card-header">
                                                                                            <div class="row clearfix">
                                                                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                                    <ul class="list-group">
                                                                                                        <li class="list-group-item list-group-item list-group-item-action active">Task: <?= $milestone ?>
                                                                                                        </li>
                                                                                                        <li class="list-group-item"><strong>Task Cost: </strong> <?= number_format($sum_cost, 2) ?> </li>
                                                                                                    </ul>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="table-responsive">
                                                                                            <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                                                <thead>
                                                                                                    <tr>
                                                                                                        <th style="width:5%">#</th>
                                                                                                        <th style="width:40%">Item </th>
                                                                                                        <th style="width:25%">Unit of Measure</th>
                                                                                                        <th style="width:10%">No. of Units</th>
                                                                                                        <th style="width:10%">Unit Cost (Ksh)</th>
                                                                                                        <th style="width:10%">Total Cost (Ksh)</th>
                                                                                                    </tr>
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                    <?php
                                                                                                    $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE tasks=:task_id AND site_id=:site_id ");
                                                                                                    $query_rsOther_cost_plan->execute(array(":task_id" => $msid, ':site_id' => $site_id));
                                                                                                    $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();
                                                                                                    if ($totalRows_rsOther_cost_plan > 0) {
                                                                                                        $table_counter = 0;
                                                                                                        while ($row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch()) {
                                                                                                            $table_counter++;
                                                                                                            $rmkid = $row_rsOther_cost_plan['id'];
                                                                                                            $description = $row_rsOther_cost_plan['description'];
                                                                                                            $unit = $row_rsOther_cost_plan['unit'];
                                                                                                            $unit_cost = $row_rsOther_cost_plan['unit_cost'];
                                                                                                            $units_no = $row_rsOther_cost_plan['units_no'];
                                                                                                            $total_cost = $unit_cost * $units_no;

                                                                                                            $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                                                                                                            $query_rsIndUnit->execute(array(":unit_id" => $unit));
                                                                                                            $row_rsIndUnit = $query_rsIndUnit->fetch();
                                                                                                            $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                                                                                                            $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
                                                                                                    ?>
                                                                                                            <tr id="row">
                                                                                                                <td style="width:5%"><?= $table_counter ?></td>
                                                                                                                <td style="width:40%"><?= $description ?></td>
                                                                                                                <td style="width:25%"><?= $unit_of_measure ?></td>
                                                                                                                <td style="width:10%"><?= number_format($units_no) ?></td>
                                                                                                                <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                                                <td style="width:10%"><?= number_format($total_cost, 2) ?></td>
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
                                                    ?>
                                                        <fieldset class="scheduler-border">
                                                            <legend class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px">
                                                                <i class="fa fa-list-ol" aria-hidden="true"></i> Output <?= $counter ?> : <?= $output ?>
                                                            </legend>
                                                            <?php
                                                            $query_rsMilestone = $db->prepare("SELECT * FROM tbl_milestone WHERE outputid=:output_id ORDER BY parent ASC");
                                                            $query_rsMilestone->execute(array(":output_id" => $output_id));
                                                            $totalRows_rsMilestone = $query_rsMilestone->rowCount();
                                                            if ($totalRows_rsMilestone > 0) {
                                                                while ($row_rsMilestone = $query_rsMilestone->fetch()) {
                                                                    $milestone = $row_rsMilestone['milestone'];
                                                                    $msid = $row_rsMilestone['msid'];
                                                                    $site_id = 0;
                                                                    $query_rsOther_cost_plan_budget =  $db->prepare("SELECT SUM(unit_cost * units_no) as sum_cost FROM tbl_project_tender_details WHERE projid =:projid AND tasks=:tasks ");
                                                                    $query_rsOther_cost_plan_budget->execute(array(":projid" => $projid, ":tasks" => $msid));
                                                                    $row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();
                                                                    $sum_cost = $row_rsOther_cost_plan_budget['sum_cost'] != null ? $row_rsOther_cost_plan_budget['sum_cost'] : 0;
                                                                    $total_cost = 0;
                                                            ?>
                                                                    <div class="row clearfix">
                                                                        <input type="hidden" name="task_amount[]" id="task_amount<?= $msid ?>" class="task_costs" value="<?= $sum_cost ?>">
                                                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                            <div class="card-header">
                                                                                <div class="row clearfix">
                                                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                                                        <ul class="list-group">
                                                                                            <li class="list-group-item list-group-item list-group-item-action active">Task: <?= $milestone ?>
                                                                                            </li>
                                                                                            <li class="list-group-item"><strong>Task Cost: </strong> <?= number_format($sum_cost, 2) ?> </li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="table-responsive">
                                                                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable" id="direct_table<?= $output_id ?>">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th style="width:5%">#</th>
                                                                                            <th style="width:40%">Item</th>
                                                                                            <th style="width:25%">Unit of Measure</th>
                                                                                            <th style="width:10%">No. of Units</th>
                                                                                            <th style="width:10%">Unit Cost (Ksh)</th>
                                                                                            <th style="width:10%">Total Cost (Ksh)</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        <?php
                                                                                        $query_rsOther_cost_plan =  $db->prepare("SELECT * FROM tbl_project_tender_details WHERE tasks=:task_id AND site_id=:site_id ");
                                                                                        $query_rsOther_cost_plan->execute(array(":task_id" => $msid, ':site_id' => $site_id));
                                                                                        $totalRows_rsOther_cost_plan = $query_rsOther_cost_plan->rowCount();
                                                                                        if ($totalRows_rsOther_cost_plan > 0) {
                                                                                            $table_counter = 0;
                                                                                            while ($row_rsOther_cost_plan = $query_rsOther_cost_plan->fetch()) {
                                                                                                $table_counter++;
                                                                                                $rmkid = $row_rsOther_cost_plan['id'];
                                                                                                $description = $row_rsOther_cost_plan['description'];
                                                                                                $unit = $row_rsOther_cost_plan['unit'];
                                                                                                $unit_cost = $row_rsOther_cost_plan['unit_cost'];
                                                                                                $units_no = $row_rsOther_cost_plan['units_no'];
                                                                                                $total_cost = $unit_cost * $units_no;

                                                                                                $query_rsIndUnit = $db->prepare("SELECT * FROM  tbl_measurement_units WHERE id = :unit_id");
                                                                                                $query_rsIndUnit->execute(array(":unit_id" => $unit));
                                                                                                $row_rsIndUnit = $query_rsIndUnit->fetch();
                                                                                                $totalRows_rsIndUnit = $query_rsIndUnit->rowCount();
                                                                                                $unit_of_measure = $totalRows_rsIndUnit > 0 ? $row_rsIndUnit['unit'] : '';
                                                                                        ?>
                                                                                                <tr id="row">
                                                                                                    <td style="width:5%"><?= $table_counter ?></td>
                                                                                                    <td style="width:40%"><?= $description ?></td>
                                                                                                    <td style="width:25%"><?= $unit_of_measure ?></td>
                                                                                                    <td style="width:10%"><?= number_format($units_no) ?></td>
                                                                                                    <td style="width:10%"><?= number_format($unit_cost, 2) ?></td>
                                                                                                    <td style="width:10%"><?= number_format($total_cost, 2) ?></td>
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
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
        } else {
            $results =  restriction();
            echo $results;
        }
    } catch (PDOException $ex) {
        customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
    }
} else {
    $results =  restriction();
    echo $results;
}

require('includes/footer.php');
?>