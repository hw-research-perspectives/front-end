<?php
/* $outputTXT = '..\files\id_summary.txt';
require_once("config.inc.php");

$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
					
$queryToUse="SELECT * FROM summary";
$query = $db->prepare($queryToUse);
$success = $query->execute();

$data = $query->fetchAll(PDO::FETCH_ASSOC);
// print_r($data);

ini_set('max_execution_time', 0);
file_put_contents($outputTXT, "");
foreach ($data as $row)
	file_put_contents($outputTXT, $row['ID'] . " " . $row['ID'] . " " . $row['Summary'] . "\n", FILE_APPEND | LOCK_EX); */
ini_set('max_execution_time', 0);
echo 'loading...';
exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet import-file --input ..\files\id_summary.txt --remove-stopwords true --output ..\files\reference\instances_100.ser --keep-sequence 2>&1', $output_import);
foreach($output_import as $child) {
	echo $child . "<br />";
}
exec('..\mallet-2.0.7\bin\mallet train-topics --num-iterations 1000 --num-topics 100 --num-threads 2 --input ..\files\reference\instances_100.ser --output-model ..\files\reference\topic_model_100.mallet --inferencer-filename ..\files\reference\model_100_inferencer.mallet --output-state ..\files\topic-state.gz --output-topic-keys ..\files\topic_keys.txt --output-doc-topics ..\files\topics_composition.txt --topic-word-weights-file ..\files\topic_word_weights.txt --word-topic-counts-file ..\files\word_topic_counts.txt 2>&1', $output_train_topics);
foreach($output_train_topics as $child) {
	echo $child . "<br />";
}
// exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet infer-topics --input ..\files\instanceList.mallet --inferencer ..\files\reference\model_100_inferencer.mallet --output-doc-topics ..\files\new_topicmap.txt --num-iterations 100 --doc-topics-max 10 --doc-topics-threshold 0.1 2>&1', $output_infer);
	
echo "update database!<br />\n";
echo "update topics_100!<br />\n";
echo "update topicmap_grants_100!<br />\n"; //topic proportion topicID
echo "update topicwords_100!<br />\n"; //TopicID TopicWord TopicStem TopicCount
echo "finished.";
?>