<?php
	require_once("config.inc.php");
	
	$main = function($print_func) {
	
	global $dbhost;
	global $dbname;
	global $dbuser;
	global $dbpass;

	// Connect to server and select databse.
	$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
	
	$queryToUse="SELECT vw_hw_grants.ID, TotalGrantValue, StartDate, EndDate, OrganisationDepartment, TopicID FROM vw_hw_grants, topicmap_grants_100 where topicmap_grants_100.ID = vw_hw_grants.ID and TopicID = :topicID order by StartDate asc;";
	
	$query = $db->prepare($queryToUse);
	$query->bindValue(":topicID", $_GET['topicID']);
	$query->execute();
	
	$result = $query->fetchAll();
	
	if (count($result) > 0) {
		$queryToUse2="SELECT min(StartDate), max(EndDate) FROM vw_hw_grants, topicmap_grants_100 where topicmap_grants_100.ID = vw_hw_grants.ID and TopicID = :topicID;";
		
		$query2 = $db->prepare($queryToUse2);
		$query2->bindValue(":topicID", $_GET['topicID']);
		$query2->execute();
		
		$result2 = $query2->fetchAll();
		
		if (count($result2) == 1)
		{
			$row2 = $result2[0];
			$beginDate = $row2['min(StartDate)'];
			$lastDate = $row2['max(EndDate)'];
			
			$date1 = DateTime::createFromFormat("Y-m-d H:i:s", $beginDate);
			$date2 = DateTime::createFromFormat("Y-m-d H:i:s", $lastDate);
				
			$y1 = $date1->format("Y");
			$m1 = $date1->format("m");
				
			$y2 = $date2->format("Y");
			$m2 = $date2->format("m");
			
			$totalNumMonth = ($y2-$y1)*12+($m2-$m1)+1;
			$totalNumMonth += 12;
			$outputArray = array(array('date', 'Sch of Life Sciences', 'Sch of Engineering and Physical Science', 'Sch of the Built Environment', 'Sch of Management and Languages', 'Institute Of Petroleum Engineering', 'S of Mathematical and Computer Sciences', 'Technology and Research Services', 'Sch of Textiles and Design', 'Other'));
			//$outputArray = array(array("date", "AvgMonthlyFunding"));

			$y = $y1;
			$m = $m1;
			
			for ($i = 1; $i <= $totalNumMonth; $i++) {
				if (strlen($m) == 1) {
					$m = "0".$m;
				}
				
				$outputArray[$i][0] = "$y$m";
				$totalAvgMonthlyFunding[$i] = 0;
				$m++;
				
				if ($m > 12) {
					$y++;
					$m = 1;
				}
			}
			
			for ($i = 1; $i <= 9; $i++) {
				for ($j = 1; $j <= $totalNumMonth; $j++) {
					$outputArray[$j][$i] = 0;
				}
			}
			
			foreach($result as $row)
			{
				$totalgrantvalue = $row['TotalGrantValue'];
				$startdate = $row['StartDate'];
				$enddate = $row['EndDate'];
				
				$date3 = DateTime::createFromFormat("Y-m-d H:i:s", $startdate);
				$date4 = DateTime::createFromFormat("Y-m-d H:i:s", $enddate);
				
				$y3 = $date3->format("Y");
				$m3 = $date3->format("m");
				
				$y4 = $date4->format("Y");
				$m4 = $date4->format("m");
				
				$numMonthAfterBeginDate = ($y3-$y1)*12+($m3-$m1);
				$numMonth = ($y4-$y3)*12+($m4-$m3)+1;
				$numMonthBeforeLastDate = ($y2-$y4)*12+($m2-$m4);
				
				$avgMonthlyFunding = $totalgrantvalue/$numMonth;
				
				$index = 1;
				$school = 0;
				
				if ($row['OrganisationDepartment'] == "Sch of Life Sciences") {
					$school = 1;
				}
				else if ($row['OrganisationDepartment'] == "Sch of Engineering and Physical Science") {
					$school = 2;
				}
				else if ($row['OrganisationDepartment'] == "Sch of the Built Environment") {
					$school = 3;
				}else if ($row['OrganisationDepartment'] == "Sch of Management and Languages") {
					$school = 4;
				}else if ($row['OrganisationDepartment'] == "Institute Of Petroleum Engineering") {
					$school = 5;
				}else if ($row['OrganisationDepartment'] == "S of Mathematical and Computer Sciences") {
					$school = 6;
				}else if ($row['OrganisationDepartment'] == "Technology and Research Services") {
					$school = 7;
				}else if ($row['OrganisationDepartment'] == "Sch of Textiles and Design") {
					$school = 8;
				}else if ($row['OrganisationDepartment'] == "Other") {
					$school = 9;
				}
				
				$roundedAvgMonthlyFunding = round($avgMonthlyFunding, 1, PHP_ROUND_HALF_UP);
				for ($i = 1; $i <= $numMonthAfterBeginDate; $i++) {
					//$outputArray[$index][$school] .= "\t0";
					$index++;
				}
				for ($i = 1; $i <= $numMonth; $i++) {
					//$outputArray[$index] .= "\t".round($avgMonthlyFunding, 1, PHP_ROUND_HALF_UP);
					$outputArray[$index][$school] += $roundedAvgMonthlyFunding;
					$totalAvgMonthlyFunding[$index] += $roundedAvgMonthlyFunding;
					$index++;
				}
				for ($i = 1; $i <= $numMonthBeforeLastDate; $i++) {
					//$outputArray[$index] .= "\t0";
					$index++;
				}
			}
			
			for ($i = 0; $i <= $totalNumMonth; $i++) {
				$print_func($outputArray[$i][0]);
				for ($j = 1; $j <= 9; $j++) {
					$print_func("\t".$outputArray[$i][$j]);
				}
				$print_func("\n");
			}

			$highestFundingOfOneMonth = max($totalAvgMonthlyFunding);
			$digit = strlen(round($highestFundingOfOneMonth, 0, PHP_ROUND_HALF_DOWN));
			$res = "1";
			$firstNum = substr($highestFundingOfOneMonth, 0, 1);
			if ($firstNum <= 8) {
				$res = ++$firstNum;
				$digit--;
			}
			for ($i = 1; $i <= $digit; $i++){
				$res = $res."0";
			}

			$_SESSION['MonthlyFunding'] = $res;
		}
	}
};
$f = fopen("monthlyfunding.tsv", "wb");
$main(function($output) use ($f) {
    fwrite($f, $output);
    //echo $output;
});
fclose($f);