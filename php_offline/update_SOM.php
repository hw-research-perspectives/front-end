<?php

/* Design and Code Project 2014
 *  Authors: Tsz Chun Law
 *  PHP script to join Matlab execution for SIM and SOM with JSON output for the hexmap.
 *  Revision History
 *  Initial Creation - Tsz Chun
 */
 
exec('..\MATLAB\R2013a\bin\matlab.exe -nosplash -nodesktop -r "run(\'..\SIM_and_SOM\sim_and_som.m\');exit;"', $output_SIM);
foreach($output_SIM as $child) {
	echo $child . "<br />";
}
include('parse_SOM.php');
?>