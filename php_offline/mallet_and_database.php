<?php
$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream');
$inputCSV = '..\files\input.csv';
$outputTXT = '..\files\id_summary.txt';

require_once("config.inc.php");

if( $_FILES['file']['name'] != "" )
{
	if((in_array($_FILES['file']['type'],$mimes)) !== FALSE){
		copy( $_FILES["file"]["tmp_name"], $inputCSV ) or 
			die( "Could not copy file!");
		$row = 0;
		if (($handle = fopen($inputCSV, "r")) !== FALSE){
			file_put_contents($outputTXT, "");
			$grantID = array();
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){
				$num = count($data);
				// print_r($data);
				echo "<p> $num fields in line $row: <br /></p>\n";
				$row++;
				if ($num == 15)
				{
					// for ($c=0; $c < $num; $c++) {
						// echo $data[$c];
						// echo ", ";
					// }
					echo $data[0] . ", " . $data[1];
					
					// Constructing an input list for Mallet
					file_put_contents($outputTXT, $data[0] . " " . $data[0] . " " . $data[14] . "\n", FILE_APPEND | LOCK_EX);
					
					// Connect to server and select databse.
					$db = new PDO("mysql:host=$dbhost;dbname=$dbname;", $dbuser, $dbpass);
					
					$queryToUse="INSERT INTO information VALUES (:data0,:data1,:data2,:data3,:data4,:data5,:data6,:data7,:data8,:data9,:data10,:data11,:data12,:data13)";
					
					$query = $db->prepare($queryToUse);
					$query->bindValue(":data0", $data[0]);
					$query->bindValue(":data1", $data[1]);
					$query->bindValue(":data2", $data[2]);
					$query->bindValue(":data3", $data[3]);
					$query->bindValue(":data4", $data[4]);
					$query->bindValue(":data5", $data[5]);
					$query->bindValue(":data6", $data[6]);
					$query->bindValue(":data7", $data[7]);
					$query->bindValue(":data8", $data[8]);
					$query->bindValue(":data9", $data[9]);
					$query->bindValue(":data10", $data[10]);
					$query->bindValue(":data11", $data[11]);
					$query->bindValue(":data12", $data[12]);
					$query->bindValue(":data13", $data[13]);
					$success = $query->execute();
					echo "<br />\ninformation insert success: ";
					var_dump($success);
					
					$queryToUse="INSERT INTO summary VALUES (:data0,:data14)";
					$query = $db->prepare($queryToUse);
					$query->bindValue(":data0", $data[0]);
					$query->bindValue(":data14", $data[14]);
					$success = $query->execute();
					echo "<br />\nsummary insert success: ";
					var_dump($success);
					$grantID[] = $data[0];
				}
				else{
					die('Incorrect number of fields, must have 15 fields.');
				}
				echo "<br />\n";
			}
			fclose($handle);
			runMallet();
			if (($handle = fopen('../files/new_topicmap.txt', "r")) !== FALSE)
			{
				// Skip first row (header)
				if (($data = fgetcsv($handle, 1000, " ")) !== FALSE)
				{
					$row = 0;
					while (($data = fgetcsv($handle, 1000, " ")) !== FALSE)
					{
						$num = count($data);
						echo "<p> $num fields in line $row: <br /></p>\n";
						for ($c=2; $c < $num-2; $c+=2)
						{
							echo $grantID[$row] . " " . $data[$c+1] . " " . $data[$c] . " ";
							$c1 = $c + 1;
							
							$queryToUse="INSERT INTO topicmap_grants_100 VALUES (:grantID, :datac1, :datac)";
							$query = $db->prepare($queryToUse);
							$query->bindValue(":grantID", $grantID[$row]);
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
		}
		else {
			die("Uploaded file is corrupted or not a csv file.");
		}
	} else {
	  die("Sorry, types other than csv are not allowed.");
	}
}
else {
    die("No file specified!");
}

function runMallet() {
	ini_set('max_execution_time', 0);
	exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet import-file --input ..\files\id_summary.txt --output ..\files\instanceList.mallet --use-pipe-from ..\files\reference\instances_100.ser 2>&1', $output_import);

	// foreach($output_import as $child) {
		// echo $child . "<br />";
	// }
	
	exec('cmd.exe /c ..\mallet-2.0.7\bin\mallet infer-topics --input ..\files\instanceList.mallet --inferencer ..\files\reference\model_100_inferencer.mallet --output-doc-topics ..\files\new_topicmap.txt --num-iterations 100 --doc-topics-max 10 --doc-topics-threshold 0.1 2>&1', $output_infer);
	
	// foreach($output_infer as $child) {
		// echo $child . "<br />";
	// }
}

?>
<html>
<head>
<title>Uploading Complete</title>
</head>
<body>
<h2>Uploaded File Info:</h2>
<ul>
<li>Sent file: <?php echo $_FILES['file']['name'];  ?>
<li>File size: <?php echo $_FILES['file']['size'];  ?> bytes
<li>File type: <?php echo $_FILES['file']['type'];  ?>
</ul>
<br />
<br />
<br />
<br />
<br />
<br />
</body>
</html>