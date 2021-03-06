<?php
/*   Design and Code Project 2014
   Authors: Tsz Kit Law
   php file to generate data for bar and stack d3 charts in the svgCharts.php. 	sample query - 'svgData.php?query=monthlySpend&format=tsv&topicID=3'
   Revision History
   Initial Creation (modified version of data.php from Simon) - Kit
   Added the database queries and generate output to specified format for d3 charts - Kit
   replace calls to functions and parameters which don't exist and remove unused code - Simon
   Added topic filter - Kit
*/
require_once("config.inc.php");

// QUERY LIST HERE!
$queryVariables = array("debug", "wordle", "totalSpend", "monthlySpend");

$debug = "SELECT 'sekrit' AS secretSitePassword FROM dual;"; // little easter egg... since http://is.gd/9HluJs got reverted :(
$totalSpend = "select TopicID, sum(LifeSciences) AS LifeSciences, sum(EngineeringAndPhysical) AS EngineeringAndPhysical, sum(BuiltEnvironment) AS BuiltEnvironment, sum(ManagementAndLanguages) AS ManagementAndLanguages, sum(Petroleum) AS Petroleum, sum(Macs) AS Macs, sum(TechRes) AS TechRes, sum(Textiles) AS Textiles, sum(Other) AS Other from vw_hw_totalspendbyschool where TopicID = :topicID";
$monthlySpend = "SELECT vw_hw_grants.ID, TotalGrantValue, StartDate, EndDate, OrganisationDepartment, TopicID FROM vw_hw_grants, topicmap_grants_100 where topicmap_grants_100.ID = vw_hw_grants.ID and Proportion > 0.04 and TopicID = :topicID order by StartDate asc;";
$monthlySpend2 = "SELECT min(StartDate), max(EndDate) FROM vw_hw_grants, topicmap_grants_100 where topicmap_grants_100.ID = vw_hw_grants.ID and Proportion > 0.04 and TopicID = :topicID;";

// END OF QUERY LIST!

// default to sane value
$topicID = 0;
$queryToUse = "debug";
if (isset($_GET['topicID'])) {
	$topicID = $_GET['topicID'];
}

if(isset($_GET['query']))
{
	// sanitise the requested variable
	if(in_array($_GET['query'], $queryVariables ))
	{
		// the query is from the list of accepted values, so it's fine.
		$queryToUse = $_GET['query'];
	}
}

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
$query = $db->prepare($$queryToUse);
if ($queryToUse == "totalSpend" || $queryToUse == "wordle") {
	$query->execute(array(":topicID" => $topicID));
}
elseif ($queryToUse == "monthlySpend") {
	$query->execute(array(":topicID" => $topicID));
	
	$query2 = $db->prepare($monthlySpend2);
	$query2->execute(array(":topicID" => $topicID));
}

$query->execute();

// default to sane value
$dataFormat = "debug";
if(isset($_GET['format']))
{
	if(in_array($_GET['format'], array("tsv", "tsvfm", "csv", "csvfm", "debug")))
	{
		$dataFormat = $_GET['format'];
	}
}

if($dataFormat == "tsv")
{
	header('Content-Type: text/tab-separated-values');
	header('Content-Disposition: attachment; filename="data.tsv"');
}
if($dataFormat == "csv")
{
	header('Content-Type: text/comma-separated-values');
	header('Content-Disposition: attachment; filename="data.csv"');
}
else
{
	header('Content-Type: text/plain');
}

// get the columns in the dataset
$columnNames = array();
$columnCount = $query->columnCount();
for ($i = 0; $i < $columnCount; $i++)
{
	$columnMeta = $query->getColumnMeta($i);
	$columnNames[] = $columnMeta['name'];
}

// PROCESS DATA INTO OUTPUT FORMAT

if($dataFormat == "tsv" || $dataFormat == "tsvfm")
{
	$data = $query->fetchAll(PDO::FETCH_ASSOC);
	
	if ($queryToUse == "totalSpend") {
		echo "TopicId\tSch of Life Sciences\tSch of Engineering and Physical Science\tSch of the Built Environment\tSch of Management and Languages\tInstitute Of Petroleum Engineering\tS of Mathematical and Computer Sciences\tTechnology and Research Services\tSch of Textiles and Design\tOther\r\n";

		foreach ($data as $row)
		{
			echo implode("\t", $row) . "\r\n";
		}
	}
	elseif ($queryToUse == "monthlySpend") {
		$data2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		
		$row2 = $data2[0];
		
		$beginDate = $row2['min(StartDate)'];
		$lastDate = $row2['max(EndDate)'];
			
		$date1 = date_create($beginDate);
		$date2 = date_create($lastDate);
				
		$y1 = $date1->format("Y");
		$m1 = $date1->format("m");
				
		$y2 = $date2->format("Y");
		$m2 = $date2->format("m");
			
		$totalNumMonth = ($y2-$y1)*12+($m2-$m1)+1;
		$totalNumMonth += 12;
		
		$outputArray = array(array('date', 'Sch of Life Sciences', 'Sch of Engineering and Physical Science', 'Sch of the Built Environment', 'Sch of Management and Languages', 'Institute Of Petroleum Engineering', 'S of Mathematical and Computer Sciences', 'Technology and Research Services', 'Sch of Textiles and Design', 'Other'));
		
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
			
		foreach($data as $row)
		{
			$totalgrantvalue = $row['TotalGrantValue'];
			$startdate = $row['StartDate'];
			$enddate = $row['EndDate'];
				
			$date3 = date_create($startdate);
			$date4 = date_create($enddate);
				
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
				
			$roundedAvgMonthlyFunding = round($avgMonthlyFunding, 1);
			for ($i = 1; $i <= $numMonthAfterBeginDate; $i++) {
				$index++;
			}
			for ($i = 1; $i <= $numMonth; $i++) {
				$outputArray[$index][$school] += $roundedAvgMonthlyFunding;
				$totalAvgMonthlyFunding[$index] += $roundedAvgMonthlyFunding;
				$index++;
			}
			for ($i = 1; $i <= $numMonthBeforeLastDate; $i++) {
				$index++;
			}
		}
		for ($i = 0; $i <= $totalNumMonth; $i++) {
			echo $outputArray[$i][0];
			for ($j = 1; $j <= 9; $j++) {
				echo "\t".$outputArray[$i][$j];
			}
			echo "\r\n";
		}
	}
}
elseif($dataFormat == "csv" || $dataFormat == "csvfm")
{
	$data = $query->fetchAll(PDO::FETCH_ASSOC);
}

if($dataFormat == "debug")
{
	$data = $query->fetchAll(PDO::FETCH_ASSOC);

	print_r($data);
}
