<?php

// Read list of accession numbers and import into database 
// (would be better to do this batch mode)

ini_set('memory_limit', '-1');

// get accession numbers and look up
$small_filename = 'data/insdc_acs.csv';

$row_count = 0;

$pmid_count = array();

$small_file_handle = fopen($small_filename, "r");
while (!feof($small_file_handle)) 
{
	$accession = trim(fgets($small_file_handle));
	
	$go = true;
	
	if ($row_count == 0)
	{
		$go = preg_match('/^[A-Z]{1,2}\d+$/', $accession);
	}
	
	if ($go)
	{
		$sql = 'INSERT OR IGNORE INTO accession(accession) VALUES ("' . $accession . '");';
		echo $sql . "\n";
	}
	
	$row_count++;
}

?>
