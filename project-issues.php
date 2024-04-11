<?php
require('includes/head.php');
if ($permission && (isset($_GET['proj']) && !empty($_GET["proj"]))) {
	$decode_projid =  base64_decode($_GET['proj']);
	$projid_array = explode("projid54321", $decode_projid);
	$projid = $projid_array[1];
	try {
		$query_rsMyP = $db->prepare("SELECT * FROM tbl_projects p inner join tbl_programs g on g.progid=p.progid WHERE p.deleted='0' AND projid='$projid'");
		$query_rsMyP->execute();
		$row_rsMyP = $query_rsMyP->fetch();
		$currentdate = date("Y-m-d");

		if ($row_rsMyP) {
			$projname = $row_rsMyP['projname'];
			$projcategory = $row_rsMyP["projcategory"];
			$query_issues = $db->prepare("SELECT c.catid, c.category, i.id, i.issue_description, i.issue_area, i.issue_impact, i.issue_priority, i.date_created, i.status FROM tbl_projrisk_categories c left join tbl_projissues i on c.catid = i.risk_category WHERE projid = :projid");
			$query_issues->execute(array(":projid" => $projid));
			$count_issues = $query_issues->rowCount();

			function get_inspection_status($status_id)
			{
				global $db;
				$sql = $db->prepare("SELECT * FROM tbl_issue_status WHERE id = :status_id");
				$sql->execute(array(":status_id" => $status_id));
				$row = $sql->fetch();
				$rows_count = $sql->rowCount();
				return ($rows_count > 0) ? $row['status'] : "";
			}

?>
			<div class="container-fluid">
				<div class="block-header bg-blue-grey" width="100%" height="55" style="margin-top:70px; padding-top:5px; padding-bottom:5px; padding-left:15px; color:#FFF">
					<h4 class="contentheader">
						<i class="fa fa-warning" style="color:white"></i> Project Issues
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
										</ul>
									</div>
								</div>
							</div>
							<div class="body">
								<div class="table-responsive">
									<table class="table table-bordered table-striped table-hover js-basic-example">
										<thead>
											<tr>
												<th style="width:4%">#</th>
												<th style="width:56%">Issue Description</th>
												<th style="width:10%">Issue Area</th>
												<th style="width:10%">Category</th>
												<th style="width:10%">Status</th>
												<th style="width:10%">Issue Date</th>
											</tr>
										</thead>
										<tbody>
											<?php
											if ($count_issues > 0) {
												$nm = 0;

												while ($row_issues = $query_issues->fetch()) {
													$nm = $nm + 1;
													$issueid = $row_issues["id"];
													$issueareaid = $row_issues["issue_area"];
													$category = $row_issues["category"];
													$issue = $row_issues["issue_description"];
													$impactid = $row_issues["issue_impact"];
													$priorityid = $row_issues["issue_priority"];
													$issuestatusis = $row_issues["status"];
													$issuedate = $row_issues["date_created"];

													$query_risk_impact =  $db->prepare("SELECT * FROM tbl_risk_impact WHERE id=:impactid");
													$query_risk_impact->execute(array(":impactid" => $impactid));
													$row_risk_impact = $query_risk_impact->fetch();
													$impact = $row_risk_impact["description"];

													$query_issue_area =  $db->prepare("SELECT * FROM tbl_issue_areas WHERE id=:issueareaid");
													$query_issue_area->execute(array(":issueareaid" => $issueareaid));
													$row_issue_area = $query_issue_area->fetch();
													$issue_area = $row_issue_area["issue_area"];

													$query_timeline =  $db->prepare("SELECT * FROM tbl_project_workflow_stage_timelines WHERE category = 'issue' and stage=1 and active=1");
													$query_timeline->execute();
													$row_timeline = $query_timeline->fetch();
													$timelineid = $row_timeline["id"];
													$time = $row_timeline["time"];
													$units = $row_timeline["units"];
													$stgstatus = $row_timeline["status"];

													$duedate = strtotime($issuedate . "+ " . $time . " " . $units);
													$actionnduedate = date("d M Y", $duedate);

													$current_date = date("Y-m-d");
													$actduedate = date("Y-m-d", $duedate);


													if ($actduedate >= $current_date) {
														$actionstatus = $stgstatus;
														$styled = 'style="color:blue"';
													} elseif ($actduedate < $current_date) {
														$actionstatus = "Behind Schedule";
														$styled = 'style="color:red"';
													}

													if ($issuestatusis == 0) {
														$issuestatus = "Pending Action";
													} elseif ($issuestatusis == 1) {
														$issuestatus = "Resolved";
													} elseif ($issuestatusis == 2) {
														$issuestatus = "Project Put On Hold";
													} elseif ($issuestatusis == 3) {
														$issuestatus = "Project Restored";
													} elseif ($issuestatusis == 4) {
														$issuestatus = "Request Approved";
													} elseif ($issuestatusis == 5) {
														$issuestatus = "Project Restored & Request Approved";
													} elseif ($issuestatusis == 6) {
														$issuestatus = "Project Cancelled";
													} elseif ($issuestatusis == 7) {
														$issuestatus = "Issue Closed";
														$styled = 'style="color:green"';
													}
											?>
													<tr style="background-color:#fff">
														<td width="4%" align="center"><?php echo $nm; ?></td>
														<td width="56%">
															<a data-toggle="modal" data-target="#issueDetailsModal" id="issueDetailsModalBtn" onclick="issue_details(<?= $issueid ?>)">
																<?php echo $issue; ?>
															</a>
														</td>
														<td width="10%"><?php echo $issue_area; ?></td>
														<td width="10%"><?php echo $category; ?></td>
														<td width="10%" <?= $styled ?>><?php echo $issuestatus; ?></td>
														<td width="10%"><?php echo date("d M Y", strtotime($issuedate)); ?></td>
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

			<!-- Modal Issue Escalation -->
			<div class="modal fade" id="issueDetailsModal" role="dialog">
				<div class="modal-dialog  modal-lg">
					<div class="modal-content">
						<div class="modal-header bg-info">
							<button type="button" class="close" data-dismiss="modal">&times;</button>
							<h3 class="modal-title" align="center">
								<font color="#000">Issue Details</font>
							</h3>
						</div>
						<div class="modal-body">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div id="issue_details">
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" align="center">
								<button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- #END# Modal Issue Escalation -->

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
<script src="assets/js/issues/issues.js"></script>