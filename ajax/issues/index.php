<?php
include '../controller.php';
try {
    if (isset($_GET['get_issue_more_info'])) {
        $issueid = $_GET['issueid'];

		$query_issue_details = $db->prepare("SELECT i.projid, i.issue_area, i.issue_priority, issue_description, m.description AS impact, category, recommendation, i.status, i.created_by AS monitor, i.date_created AS issuedate FROM tbl_projissues i INNER JOIN tbl_projrisk_categories c ON c.catid=i.risk_category inner join tbl_risk_impact m on m.id=i.issue_impact WHERE i.id=:issueid");
		$query_issue_details->execute(array(":issueid" =>$issueid));
		$row_issue_details = $query_issue_details->fetch();

		$category = $row_issue_details['category'];
		$issue_description = $row_issue_details['issue_description'];
		$issue_priority = $row_issue_details['issue_priority'];
		$issue_areaid = $row_issue_details['issue_area'];
		$issue_impact = $row_issue_details['impact'];
		$monitorid = $row_issue_details['monitor'];
		$recommendation = $row_issue_details['recommendation'];
		$issuedate = $row_issue_details['issuedate'];
		$projid = $row_issue_details['projid'];
		$issue_status_id = $row_issue_details['status'];

		if($issue_priority == 1){
			$priority = "High";
		}elseif($issue_priority == 2){
			$priority = "Medium";
		}elseif($issue_priority == 3){
			$priority = "Low";
		}

		$query_project = $db->prepare("SELECT projname, projcategory FROM tbl_projects WHERE projid=:projid");
		$query_project->execute(array(":projid" => $projid));
		$row_project = $query_project->fetch();
		$projname = $row_project["projname"];
		$projcategory = $row_project["projcategory"];

		$query_owner = $db->prepare("SELECT tt.title, fullname FROM tbl_projteam2 t inner join users u on u.pt_id=t.ptid inner join tbl_titles tt on tt.id=t.title WHERE userid='$monitorid'");
		$query_owner->execute();
		$row_owner = $query_owner->fetch();
		$monitor = $row_owner["title"] . '.' . $row_owner["fullname"];

		$query_issue_area =  $db->prepare("SELECT * FROM tbl_issue_areas WHERE id=:issue_areaid");
		$query_issue_area->execute(array(":issue_areaid" => $issue_areaid));
		$rows_issue_area = $query_issue_area->fetch();
		$issue_area = $rows_issue_area["issue_area"];

		$issues_scope = '';
		$textclass = "";
		if($issue_areaid == 2){
			$leftjoin = $projcategory == 2 ? "left join tbl_project_tender_details c on c.subtask_id=a.sub_task_id" : "left join tbl_project_direct_cost_plan c on c.subtask_id=a.sub_task_id";

			$query_site_id = $db->prepare("SELECT site_id FROM tbl_project_adjustments WHERE issueid=:issueid GROUP BY site_id");
			$query_site_id->execute(array(":issueid" => $issueid));

			$sites = 0;
			while ($rows_site_id = $query_site_id->fetch()) {
				$sites++;
				$site_id = $rows_site_id['site_id'];
				//$allsites = '';

				if($site_id==0){
					$allsites = '<tr class="adjustments '.$issueid.'" style="background-color:#a9a9a9">
						<th colspan="8">Site '.$sites.': Way Point Sub Tasks</th>
					</tr>';
				} else {
					$query_site = $db->prepare("SELECT site FROM tbl_project_sites WHERE site_id=:site_id");
					$query_site->execute(array(":site_id" => $site_id));
					$row_site = $query_site->fetch();
					$site = $row_site['site'];

					$allsites = '<tr class="adjustments '.$issueid.'" style="background-color:#a9a9a9">
						<th colspan="8">Site '.$sites.': '.$site.'</th>
					</tr>';
				}

				$query_adjustments = $db->prepare("SELECT t.task, a.units, a.timeline, c.unit_cost, u.unit FROM tbl_project_adjustments a left join tbl_projissues i on i.id = a.issueid left join tbl_task t on t.tkid=a.sub_task_id ".$leftjoin." left join tbl_measurement_units u on u.id=c.unit WHERE i.projid = :projid and issueid = :issueid and a.site_id=:site_id GROUP BY a.id");
				$query_adjustments->execute(array(":projid" => $projid, ":issueid" => $issueid, ":site_id" => $site_id));

				$issues_scope .= $allsites.'
				<tr class="adjustments '.$issueid.'" style="background-color:#cccccc">
					<th width="5%">#</th>
					<th width="50%">Sub-Task</th>
					<th width="15%">Requesting Units</th>
					<th width="15%">Additional Days</th>
					<th width="15%">Additional Cost</th>
				</tr>';
				$scopecount = 0;
				while ($row_adjustments = $query_adjustments->fetch()){
					$scopecount++;
					$subtask = $row_adjustments["task"];
					$units =  number_format($row_adjustments["units"])." ".$row_adjustments["unit"];
					$totalcost = $row_adjustments["unit_cost"] * $row_adjustments["units"];
					$timeline = $row_adjustments["timeline"]." days";
					$issues_scope .= '<tr class="adjustments '.$issueid.'" style="background-color:#e5e5e5">
						<td>'.$sites.'.'.$scopecount.'</td>
						<td>' . $subtask . '</td>
						<td>' . $units . ' </td>
						<td>' . $timeline . '</td>
						<td>' . number_format($totalcost, 2) . ' </td>
					</tr>';
				}
			}
		}elseif($issue_areaid == 3){
			$leftjoin = $projcategory == 2 ? "left join tbl_project_tender_details c on c.subtask_id=a.sub_task_id" : "left join tbl_project_direct_cost_plan c on c.subtask_id=a.sub_task_id";

			$query_site_id = $db->prepare("SELECT site_id FROM tbl_project_adjustments WHERE issueid=:issueid GROUP BY site_id");
			$query_site_id->execute(array(":issueid" => $issueid));

			$sites = 0;
			while ($rows_site_id = $query_site_id->fetch()) {
				$sites++;
				$site_id = $rows_site_id['site_id'];
				$allsites = '';

				if($site_id==0){
					$allsites = '<tr class="adjustments '.$issueid.'" style="background-color:#a9a9a9">
						<th colspan="8">Site '.$sites.': Way Point Sub Tasks</th>
					</tr>';
				} else {
					$query_site = $db->prepare("SELECT site FROM tbl_project_sites WHERE site_id=:site_id");
					$query_site->execute(array(":site_id" => $site_id));
					$row_site = $query_site->fetch();
					$site = $row_site['site'];

					$allsites = '<tr class="adjustments '.$issueid.'" style="background-color:#a9a9a9">
						<th colspan="8">Site '.$sites.': '.$site.'</th>
					</tr>';
				}

				$query_adjustments = $db->prepare("SELECT t.task, a.units, a.timeline, c.unit_cost, u.unit FROM tbl_project_adjustments a left join tbl_projissues i on i.id = a.issueid left join tbl_task t on t.tkid=a.sub_task_id ".$leftjoin." left join tbl_measurement_units u on u.id=c.unit WHERE i.projid = :projid and issueid = :issueid and a.site_id=:site_id GROUP BY a.id");
				$query_adjustments->execute(array(":projid" => $projid, ":issueid" => $issueid, ":site_id" => $site_id));

				$issues_scope .= $allsites.'
				<tr class="adjustments '.$issueid.'" style="background-color:#cccccc">
					<th width="5%">#</th>
					<th width="80%">Sub-Task</th>
					<th width="15%">Additional Days</th>
				</tr>';
				$scopecount = 0;
				while ($row_adjustments = $query_adjustments->fetch()){
					$scopecount++;
					$subtask = $row_adjustments["task"];
					$units =  number_format($row_adjustments["units"])." ".$row_adjustments["unit"];
					$totalcost = $row_adjustments["unit_cost"] * $row_adjustments["units"];
					$timeline = $row_adjustments["timeline"]." days";
					$issues_scope .= '<tr class="adjustments '.$issueid.'" style="background-color:#e5e5e5">
						<td>'.$sites.'.'.$scopecount.'</td>
						<td>' . $subtask . '</td>
						<td>' . $timeline . '</td>
					</tr>';
				}
			}
		}elseif($issue_areaid == 4){

			$leftjoin = $projcategory == 2 ? "left join tbl_project_tender_details c on c.subtask_id=a.sub_task_id" : "left join tbl_project_direct_cost_plan c on c.subtask_id=a.sub_task_id";

			$query_site_id = $db->prepare("SELECT site_id FROM tbl_project_adjustments WHERE issueid=:issueid GROUP BY site_id");
			$query_site_id->execute(array(":issueid" => $issueid));

			$sites = 0;
			while ($rows_site_id = $query_site_id->fetch()) {
				$sites++;
				$site_id = $rows_site_id['site_id'];
				//$allsites = '';

				if($site_id==0){
					$allsites = '<tr class="adjustments '.$issueid.'" style="background-color:#a9a9a9">
						<th colspan="8">Site '.$sites.': Way Point Sub Tasks</th>
					</tr>';
				} else {
					$query_site = $db->prepare("SELECT site FROM tbl_project_sites WHERE site_id=:site_id");
					$query_site->execute(array(":site_id" => $site_id));
					$row_site = $query_site->fetch();
					$site = $row_site['site'];

					$allsites = '<tr class="adjustments '.$issueid.'" style="background-color:#a9a9a9">
						<th colspan="8">Site '.$sites.': '.$site.'</th>
					</tr>';
				}

				$query_adjustments = $db->prepare("SELECT t.task, a.units, a.timeline, a.cost, u.unit FROM tbl_project_adjustments a left join tbl_projissues i on i.id = a.issueid left join tbl_task t on t.tkid=a.sub_task_id ".$leftjoin." left join tbl_measurement_units u on u.id=c.unit WHERE i.projid = :projid and issueid = :issueid and a.site_id=:site_id GROUP BY a.id");
				$query_adjustments->execute(array(":projid" => $projid, ":issueid" => $issueid, ":site_id" => $site_id));

				$issues_scope .= $allsites.'
				<tr class="adjustments '.$issueid.'" style="background-color:#cccccc">
					<th width="5%">#</th>
					<th width="65%">Sub-Task</th>
					<th width="15%">Measurement Unit</th>
					<th width="15%">Additional Cost</th>
				</tr>';
				$scopecount = 0;
				while ($row_adjustments = $query_adjustments->fetch()){
					$scopecount++;
					$subtask = $row_adjustments["task"];
					$unit =  $row_adjustments["unit"];
					$cost = $row_adjustments["cost"];

					$issues_scope .= '<tr class="adjustments '.$issueid.'" style="background-color:#e5e5e5">
						<td>'.$sites.'.'.$scopecount.'</td>
						<td>' . $subtask . '</td>
						<td>' . $unit . ' </td>
						<td>' . number_format($cost, 2) . ' </td>
					</tr>';
				}
			}
		}

		$issue_more_info = '
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Issue Description</label>
				<div id="severityname" class="require" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height: auto; width:98%">'.$issue_description.'</div>
			</div>
		</div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Project Name</label>
				<div id="severityname" class="require" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height: auto; width:98%">'.$projname.'</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Issue Category</label>
				<div id="severityname" class="require" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height:35px; width:98%">'.$category.'</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Issue Area</label>
				<div id="severityname" class="require" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height:35px; width:98%">'.$issue_area.'</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Issue Severity</label>
				<div id="severityname" class="require" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height:35px; width:98%">'.$issue_impact.'</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Issue Priority</label>
				<div id="severityname" class="require" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height:35px; width:98%">'.$priority.'</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Issue Owner</label>
				<div id="severityname" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height:35px; width:98%">'.$monitor.'</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12" style="margin-bottom:10px">
			<div class="form-inline">
				<label for="">Date Recorded</label>
				<div id="severityname" style="border:#CCC thin solid; border-radius:5px; padding-top: 7px; padding-left: 10px; height:35px; width:98%">'.$issuedate.'</div>
			</div>
		</div>';

		if($issue_areaid != 1){
			$issue_more_info .= '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-hover js-basic-example">
						<thead>
							<h4 class="text-primary">Issue Details:</h4>
						</thead>
						<tbody>
							' . $issues_scope . '
						</tbody>
					</table>
				</div>
			</div>';
		}

		$query_issue_files = $db->prepare("SELECT * FROM tbl_files WHERE projid = :projid and projstage = :issueid and (fcategory='Issue' OR fcategory='Issue Resolution')");
		$query_issue_files->execute(array(":projid" => $projid, ":issueid" => $issueid));
		$rowcount_files = $query_issue_files->rowCount();

		if($rowcount_files > 0){
			$issue_more_info .= '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<fieldset class="scheduler-border">
					<legend  class="scheduler-border" style="background-color:#c7e1e8; border-radius:3px;"><i class="fa fa-paperclip" aria-hidden="true"></i> Issue Attachments</legend>
					<div class="table-responsive">
						<table class="table table-bordered table-striped table-hover">
							<thead>
								<tr style="background-color:#cccccc">
									<th width="3%">#</th>
									<th width="35%">File Name</th>
									<th width="8%">File Type</th>
									<th width="36%">File Purpose</th>
									<th width="10%">Issue Stage</th>
									<th width="8%">Download</th>
								</tr>
							</thead>
							<tbody>';
								$files_count = 0;
								while($row_issue_files = $query_issue_files->fetch()){
									$files_count++;
									$filename = $row_issue_files["filename"];
									$filetype = $row_issue_files["ftype"];
									$filelocation = $row_issue_files["floc"];
									$filedescription = $row_issue_files["reason"];
									$issue_stage = $row_issue_files["fcategory"];
									$issue_more_info .= '
									<tr style="background-color:#e5e5e5">
										<td>' . $files_count.'</td>
										<td>' . $filename . '</td>
										<td>' . $filetype . ' </td>
										<td>' . $filedescription . '</td>
										<td>' . $issue_stage . '</td>
										<td><a href="' . $filelocation . '" download="' . $filename . '" type="button" class="btn btn-primary"><i class="fa fa-download" aria-hidden="true"></i></a></td>
									</tr>';
								}
							$issue_more_info .= '</tbody>
						</table>
					</div>
				</fieldset>
			</div>';
		}


        echo json_encode(["issue_more_info" => $issue_more_info]);

       // echo json_encode(["issue_more_info" => $issue_more_info, "issue_attachments" => $issue_attachments]);
	}
} catch (PDOException $ex) {
	customErrorHandler($ex->getCode(), $ex->getMessage(), $ex->getFile(), $ex->getLine());
}
