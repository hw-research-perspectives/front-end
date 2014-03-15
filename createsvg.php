<?php
	require_once("config.inc.php");
	
	$main = function($print_func) {
	
	global $dbhost;
	global $dbname;
	global $dbuser;
	global $dbpass;

	// Connect to server and select databse.
	$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
	
	$queryToUse="SELECT vw_hw_grants.ID, TotalGrantValue, StartDate, EndDate, TopicID FROM vw_hw_grants, topicmap_grants_100 where topicmap_grants_100.ID = vw_hw_grants.ID and TopicID = :topicID order by StartDate asc;";
	
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
			//$outputString = array('date');
			$outputString = array(array("date", "AvgMonthlyFunding"));

			//for ($i = 1; $i <= count($result); $i++) {
				//$outputString[0] .= "\t".$i;
			//}
			
			$y = $y1;
			$m = $m1;
			
			for ($i = 1; $i <= $totalNumMonth; $i++) {
				if (strlen($m) == 1) {
					$m = "0".$m;
				}
				
				$outputString[$i][0] = "$y$m";
				$outputString[$i][1] = 0;
				$totalAvgMonthlyFunding[$i] = 0;
				$m++;
				
				if ($m > 12) {
					$y++;
					$m = 1;
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
				
				for ($i = 1; $i <= $numMonthAfterBeginDate; $i++) {
					//$outputString[$index] .= "\t0";
					$index++;
				}
				for ($i = 1; $i <= $numMonth; $i++) {
					//$outputString[$index] .= "\t".round($avgMonthlyFunding, 1, PHP_ROUND_HALF_UP);
					$outputString[$index][1] += round($avgMonthlyFunding, 1, PHP_ROUND_HALF_UP);
					$totalAvgMonthlyFunding[$index] += round($avgMonthlyFunding, 1, PHP_ROUND_HALF_UP);
					$index++;
				}
				for ($i = 1; $i <= $numMonthBeforeLastDate; $i++) {
					//$outputString[$index] .= "\t0";
					$index++;
				}
			}
			
			for ($i = 0; $i <= $totalNumMonth; $i++) {
				$print_func($outputString[$i][0]."\t".$outputString[$i][1]."\n");
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

/*
		header("Content-Disposition: attachment; filename=\"monthlyfunding.tsv\"");
		header("Content-Type: text/tab-delimited-values");
	
		echo "date\tmontlyfunding\n";
		
		
		while ($row = mysql_fetch_assoc($result))
		{
			$granttitle = $row['GrantTitle'];
			$totalgrantvalue = $row['TotalGrantValue'];
			$startdate = $row['StartDate'];
			$enddate = $row['EndDate'];
			
			$date1 = DateTime::createFromFormat("Y-m-d H:i:s", $startdate);
			$date2 = DateTime::createFromFormat("Y-m-d H:i:s", $enddate);
			
			$y1 = $date1->format("Y");
			$m1 = $date1->format("m");
			
			$y2 = $date2->format("Y");
			$m2 = $date2->format("m");
			
			$numMonth = ($y2-$y1)*12+($m2-$m1)+1;
			$avgMonthlyFunding = $totalgrantvalue/$numMonth;
			
			$y = $y1;
			$m = $m1;
			
			for ($i = 1; $i <= $numMonth; $i++) {
				printf("%s%02d\t%s\n",
				$y,
				$m,
				$avgMonthlyFunding
				);
				
				$m++;
				
				if ($m > 12) {
					$y++;
					$m = 1;
				}
			}
            
   
		}
		*/
