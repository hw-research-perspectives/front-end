<?php
exec('matlab -nosplash -nodesktop -r "run(\'..\SIM_and_SOM\sim_and_som.m\');exit;"', $output_SIM);
foreach($output_SIM as $child) {
	echo $child . "<br />";
}
include('parse_SOM.php');
?>