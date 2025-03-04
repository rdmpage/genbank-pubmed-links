<?php

// Given two files, one larger than the other, find records in common

ini_set('memory_limit', '-1');

$big_filename = 'data/gb264.pmid_list.other.txt';
$big_file_handle = fopen($big_filename, "r");

$row_count = 0;

// get PMIDs

echo "-- Reading PMIDs\n";

$pmid = array();
while (!feof($big_file_handle)) 
{
	$row = trim(fgets($big_file_handle));
	$parts = explode("\t", $row);
	
	if (isset($parts[2]))
	{
		$pmid[$parts[0]] = $parts[2];
	}

	$row_count++;
}

echo "-- Done reading PMIDs\n";

$matched_pmids = array();

// get accession numbers and look up
$small_filename = 'data/insdc_acs.csv';

$row_count = 0;

$pmid_count = array();

$small_file_handle = fopen($small_filename, "r");
while (!feof($small_file_handle)) 
{
	$accession = trim(fgets($small_file_handle));
	
	if (isset($pmid[$accession]))
	{
		$pmid_list = explode(",", $pmid[$accession]);
		
		// echo $accession . "\n";
		// print_r($pmid_list);
		
		// SQL
		foreach ($pmid_list as $id)
		{
			$sql = 'REPLACE INTO accession_pmid(accession, pmid) VALUES ("' . $accession . '",' . $id . ');';
			echo $sql . "\n";
			
			if (!isset($pmid_count[$id]))
			{
				$pmid_count[$id] = 0;
			}
			$pmid_count[$id]++;
		}
	}
	
	$row_count++;
	
	// debugging
	if (count($pmid_count) > 20)
	{
		//break;
	}
}

//print_r($pmid_count);

foreach ($pmid_count as $id => $count)
{
	$sql = 'INSERT OR IGNORE INTO pmid(pmid) VALUES (' . $id . ');';
	echo $sql . "\n";
}


?>
