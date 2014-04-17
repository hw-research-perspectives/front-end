<?php

/* Design and Code Project 2014
 *  Authors: Tsz Chun Law
 *  PHP script to generate a new topic model and update reference objects and database tables.
 *  Revision History
 *  Initial Creation - Tsz Chun
 *  Designed Mallet arguments - Tsz Chun
 *  Changes to number of iterations for Mallet. - Tsz Chun
 *  Added threshold and max to Mallet - Tsz Chun
 *  Created the structure for queries - Tsz Chun
 *  Completed update to topics_100 - Tsz Chun
 *  Completed update to topicmap_grants_100 - Tsz Chun
 */
 
// Retrieve summary table
$outputTXT = '..\files\id_summary.txt';
require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
					
$queryToUse="SELECT * FROM summary";
$query = $db->prepare($queryToUse);
$success = $query->execute();

$data = $query->fetchAll(PDO::FETCH_ASSOC);
print_r($data);

// Write summary table to a file for mallet input
ini_set('max_execution_time', 0);
file_put_contents($outputTXT, "");
foreach ($data as $row)
	file_put_contents($outputTXT, trim($row['ID'] . " " . $row['ID'] . " " . $row['Summary'], "\n") . "\n", FILE_APPEND | LOCK_EX);
	// No trim
	// file_put_contents($outputTXT, $row['ID'] . " " . $row['ID'] . " " . $row['Summary'] . "\n", FILE_APPEND | LOCK_EX);
	
// Generate new topic model
require_once("config.inc.php");
$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
echo 'loading...';

// Import file for Mallet
exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet import-file --input ..\files\id_summary.txt --remove-stopwords true --output ..\files\reference\instances_100.ser --keep-sequence 2>&1', $output_import);
foreach($output_import as $child) {
	echo $child . "<br />";
}

// Train topics
exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet train-topics --num-iterations 100 --num-topics 100 --num-threads 2 --input ..\files\reference\instances_100.ser --output-model ..\files\reference\topic_model_100.mallet --inferencer-filename ..\files\reference\model_100_inferencer.mallet --output-state ..\files\topic-state.gz --output-topic-keys ..\files\topic_keys.txt --output-doc-topics ..\files\topics_composition.txt --topic-word-weights-file ..\files\topic_word_weights.txt --word-topic-counts-file ..\files\word_topic_counts.txt --doc-topics-max 10 --doc-topics-threshold 0.01 2>&1', $output_train_topics);
foreach($output_train_topics as $child) {
	echo $child . "<br />";
}
// exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet infer-topics --input ..\files\instanceList.mallet --inferencer ..\files\reference\model_100_inferencer.mallet --output-doc-topics ..\files\new_topicmap.txt --num-iterations 100 --doc-topics-max 10 --doc-topics-threshold 0.1 2>&1', $output_infer);
	
// Update 3 tables in database
echo "update database!<br />\n";

// Replace topics_100 table
if (($handle = fopen("../files/topic_keys.txt", "r")) !== FALSE){
	$grantID = array();
	
	// Setting up the transaction query
	$row = 0;
	$queryToUse="BEGIN;TRUNCATE TABLE topics_100;";
	while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE){
		$num = count($data);
		print_r($data);
		echo "<p> $num fields in line $row: <br /></p>\n";
		
		$queryToUse=$queryToUse . "INSERT INTO topics_100 VALUES ($data[0],-1,\"$data[2]\");";
		// $queryToUse->bindValue(":data0", $data[0]);
		// $queryToUse->bindValue(":data1", $data[1]);
		$row++;
	}
	$queryToUse=$queryToUse . "COMMIT;";
	var_dump($queryToUse);
	$query = $db->prepare($queryToUse);
	$success = $query->execute();
}
echo "updated topics_100!<br />\n";

// Replace topicmap_grants_100 table
if (($handle = fopen('..\files\topics_composition.txt', "r")) !== FALSE)
{
	// Skip first row (header)
	if (($data = fgetcsv($handle, 1000, " ")) !== FALSE)
	{
		$row = 0;
		file_put_contents('..\SIM_and_SOM\topicmap_papers.csv', "ID,Proportion,TopicID" . "\n");
		$queryToUse="TRUNCATE TABLE topicmap_grants_100;";
		$query = $db->prepare($queryToUse);
		$query->execute();
		
		// For each line read successfully
		while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE)
		{
			// var_dump($data);
			$num = count($data);
			echo "<p> $num fields in line $row: <br /></p>\n";
			
			// Parse data
			for ($c=2; $c < $num-2; $c+=2)
			{
				echo $data[1] . " " . $data[$c+1] . " " . $data[$c] . " ";
				$c1 = $c + 1;
				
				$queryToUse="INSERT INTO topicmap_grants_100 VALUES (:datacc, :datac1, :datac)";
				file_put_contents('..\SIM_and_SOM\topicmap_papers.csv', "$data[1],$data[$c1],$data[$c]" . "\n", FILE_APPEND | LOCK_EX);
				$query = $db->prepare($queryToUse);
				$query->bindValue(":datacc", $data[1]);
				$query->bindValue(":datac1", $data[$c1]);
				$query->bindValue(":datac", $data[$c]);
				$success = $query->execute();
				echo "success: ";
				var_dump($success);
				echo "<br />\n";
			}
			$row++;
		}
	}
}
echo "updated topicmap_grants_100!<br />\n"; //topic proportion topicID

/* if (($handle = fopen("../files/word_topic_counts.txt", "r")) !== FALSE){
	file_put_contents($outputTXT, "");
	$grantID = array();
	
	$row = 0;
	$queryToUse="BEGIN;DELETE * FROM topicwords_100;";
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){
		// Need to do a parse
		$num = count($data);
		// print_r($data);
		echo "<p> $num fields in line $row: <br /></p>\n";
		
		$queryToUse=$queryToUse . "INSERT INTO topicmap_grants_100 VALUES (:data0,:dataXXX,:data1);";
		$queryToUse->bindValue(":data0", $data[0]);
		$queryToUse->bindValue(":data1", $data[1]);
		$row++;
	}
	$queryToUse=$queryToUse . "COMMIT;";
	$query = $db->prepare($queryToUse);
	$success = $query->execute();
}
echo "updated topicwords_100!<br />\n"; //TopicID TopicWord TopicStem TopicCount */
echo "finished.";
?>