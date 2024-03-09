<?php
	$query_levelministry = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='ministry'");
	$query_levelministry->execute();
	$rows_levelministry = $query_levelministry->fetch();
	$ministrylabel = $rows_levelministry["label"];
	$ministrylabelplural = $rows_levelministry["label_plural"];
	
	$query_leveldepartment = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='department'");
	$query_leveldepartment->execute();
	$rows_leveldepartment = $query_leveldepartment->fetch();
	$departmentlabel = $rows_leveldepartment["label"];
	$departmentlabelplural = $rows_leveldepartment["label_plural"];
	
	$query_leveldirectorate = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='directorate'");
	$query_leveldirectorate->execute();
	$rows_leveldirectorate = $query_leveldirectorate->fetch();
	$directoratelabel = $rows_leveldirectorate["label"];
	$directoratelabelplural = $rows_leveldirectorate["label_plural"];
	
	$query_level1 = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='level1'");
	$query_level1->execute();
	$rows_level1 = $query_level1->fetch();
	$level1label = $rows_level1["label"];
	$level1labelplural = $rows_level1["label_plural"];
	
	$query_level2 = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='level2'");
	$query_level2->execute();
	$rows_level2 = $query_level2->fetch();
	$level2label = $rows_level2["label"];
	$level2labelplural = $rows_level2["label_plural"];
	
	$query_level3 = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='level3'");
	$query_level3->execute();
	$rows_level3 = $query_level3->fetch();
	$level3label = $rows_level3["label"];
	$level3labelplural = $rows_level3["label_plural"];
	
	$query_plan = $db->prepare("SELECT label, label_plural FROM tbl_terminologies WHERE name='Plan' and category='3'");
	$query_plan->execute();
	$rows_plan = $query_plan->fetch();
	$planlabel = $rows_plan["label"];
	$planlabelplural = $rows_plan["label_plural"];
	
	//$uppercase = strtoupper($ministrylabel);
?>