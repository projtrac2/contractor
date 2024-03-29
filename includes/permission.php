<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include_once 'projtrac-dashboard/resource/Database.php';
include_once 'projtrac-dashboard/resource/utilities.php';
include_once("includes/system-labels.php");

require 'vendor/autoload.php';
include "models/Auth.php";
include "models/Company.php";
require 'models/Connection.php';
require 'models/Email.php';

function get_current_url_tests()
{
	$path = $_SERVER['REQUEST_URI'];
	$paths = explode("/", $path);
	$url_path = isset($paths[2]) ? explode(".", $paths[2]) : explode(".", $paths[1]);
	return $url_path[0];
}

session_start();
// Set the inactivity time of 60 minutes (3600 seconds)
$inactivity_time = 15 * 60;

if (isset($_SESSION['last_timestamp']) && (time() - $_SESSION['last_timestamp']) > $inactivity_time) {
	session_unset();
	session_destroy();
	$current_page_url = get_current_url_tests();
	//Redirect user to login page
	header("Location: index.php?action=$current_page_url");
	exit();
} else {
	// Regenerate new session id and delete old one to prevent session fixation attack
	session_regenerate_id(true);
	// Update the last timestamp
	$_SESSION['last_timestamp'] = time();
}

(!isset($_SESSION['MM_Contractor'])) ? header("location: index.php") : "";

$user_name = $_SESSION['MM_Contractor'];
$contractor_name = $_SESSION['contractor_name'];
$avatar = $_SESSION['avatar'];
$today = date('Y-m-d');
$results = "";

$permission = true;

function calculate_project_progress($projid, $implimentation_type)
{
	global $db;
	$direct_cost = 0;
	if ($implimentation_type == 1) {
		$query_rsOther_cost_plan_budget =  $db->prepare("SELECT SUM(unit_cost * units_no) as sum_cost FROM tbl_project_direct_cost_plan WHERE projid =:projid AND cost_type=1 ");
		$query_rsOther_cost_plan_budget->execute(array(":projid" => $projid));
		$row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();
		$direct_cost = $row_rsOther_cost_plan_budget['sum_cost'] != null ? $row_rsOther_cost_plan_budget['sum_cost'] : 0;
	} else {
		$query_rsOther_cost_plan_budget =  $db->prepare("SELECT SUM(unit_cost * units_no) as sum_cost FROM tbl_project_tender_details WHERE projid =:projid ");
		$query_rsOther_cost_plan_budget->execute(array(":projid" => $projid));
		$row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();
		$direct_cost = $row_rsOther_cost_plan_budget['sum_cost'] != null ? $row_rsOther_cost_plan_budget['sum_cost'] : 0;
	}

	$query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE projid=:projid");
	$query_rsTask_Start_Dates->execute(array(':projid' => $projid));
	$totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();

	$progress = 0;
	$cost_used = 0;
	$project_complete = [];
	if ($totalRows_rsTask_Start_Dates > 0) {
		while ($Rows_rsTask_Start_Dates = $query_rsTask_Start_Dates->fetch()) {
			$subtask_id = $Rows_rsTask_Start_Dates['subtask_id'];
			$site_id = $Rows_rsTask_Start_Dates['site_id'];
			$complete = $Rows_rsTask_Start_Dates['complete'];

			$query_rsOther_cost_plan_budget =  $db->prepare("SELECT unit_cost , units_no FROM tbl_project_direct_cost_plan WHERE projid =:projid AND site_id=:site_id AND subtask_id=:subtask_id AND cost_type=1 ");
			$query_rsOther_cost_plan_budget->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
			$row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();

			if ($implimentation_type == 2) {
				$query_rsOther_cost_plan_budget =  $db->prepare("SELECT unit_cost , units_no FROM tbl_project_tender_details WHERE projid =:projid AND site_id=:site_id AND subtask_id=:subtask_id");
				$query_rsOther_cost_plan_budget->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
				$row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();
			}

			$units = $row_rsOther_cost_plan_budget ? $row_rsOther_cost_plan_budget['units_no'] : 0;
			$unit_cost = $row_rsOther_cost_plan_budget ? $row_rsOther_cost_plan_budget['unit_cost'] : 0;
			$cost = $units * $unit_cost;
			$cost_used += $units * $unit_cost;
			$percentage = 100;

			if ($complete == 0) {
				$project_complete[] = false;
				$query_rsPercentage =  $db->prepare("SELECT SUM(achieved)  as achieved FROM tbl_project_monitoring_checklist_score WHERE projid =:projid  AND site_id=:site_id AND subtask_id=:subtask_id");
				$query_rsPercentage->execute(array(":projid" => $projid, ":site_id" => $site_id, ":subtask_id" => $subtask_id));
				$row_rsPercentage = $query_rsPercentage->fetch();
				$sub_percentage = $row_rsPercentage['achieved'] != null ?  ($row_rsPercentage['achieved'] / $units) * 100 : 0;
				$percentage = $sub_percentage >= 100 ? 99 : $sub_percentage;
			}

			$progress += $cost > 0 && $direct_cost > 0 ? $cost / $direct_cost * $percentage : 0;
		}
		$progress =  !in_array(false, $project_complete) ? 100 : $progress;
	}

	return $progress;
}

function calculate_output_progress($output_id)
{
	global $db;
	$query_rsOther_cost_plan_budget =  $db->prepare("SELECT SUM(unit_cost * units_no) as sum_cost FROM tbl_project_tender_details WHERE output_id =:output_id ");
	$query_rsOther_cost_plan_budget->execute(array(":task_id" => $output_id));
	$row_rsOther_cost_plan_budget = $query_rsOther_cost_plan_budget->fetch();
	$direct_cost = $row_rsOther_cost_plan_budget['sum_cost'] != null ? $row_rsOther_cost_plan_budget['sum_cost'] : 0;


	$query_rsPercentage =  $db->prepare("SELECT * FROM tbl_project_direct_cost_plan d INNER JOIN tbl_project_monitoring_checklist_score s ON s.subtask_id = d.subtask_id WHERE d.outputid =:output_id ");
	$query_rsPercentage->execute(array(":output_id" => $output_id));
	$progress = 0;
	while ($row_rsPercentage = $query_rsPercentage->fetch()) {
		$subtask_id = $row_rsPercentage['subtask_id'];
		$cost =   $row_rsPercentage['unit_cost'] * $row_rsPercentage['units_no'];
		$percentage =   ($row_rsPercentage['achieved'] / $row_rsPercentage['units_no']) * 100;

		if ($percentage >= 100) {
			$query_rsTask_Start_Dates = $db->prepare("SELECT * FROM tbl_program_of_works WHERE subtask_id=:subtask_id  AND complete=1");
			$query_rsTask_Start_Dates->execute(array(':subtask_id' => $subtask_id));
			$totalRows_rsTask_Start_Dates = $query_rsTask_Start_Dates->rowCount();
			$percentage = $totalRows_rsTask_Start_Dates > 1 ? 100 : 99;
		}
		$progress += $cost / $direct_cost * $percentage;
	}


	$output_progress = '
    <div class="progress" style="height:20px; font-size:10px; color:black">
        <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
            ' . $progress . '%
        </div>
    </div>';
	if ($progress == 100) {
		$output_progress = '
        <div class="progress" style="height:20px; font-size:10px; color:black">
            <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $progress . '%; height:20px; font-size:10px; color:black">
            ' . $progress . '%
            </div>
        </div>';
	}

	return $output_progress;
}

function restriction()
{
	return "
	<script type='text/javascript'>
		swal({
		title: 'Success!',
		text: 'Sorry you are not permitted to access this page',
		type: 'Error',
		timer: 3000,
		icon:'error',
		showConfirmButton: false });
		setTimeout(function(){
			window.history.back();
		}, 3000);
	</script>";
}
