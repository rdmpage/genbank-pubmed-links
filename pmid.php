<?php

// Get details for PubMed ids

require_once(dirname(__FILE__) . '/ncbi.php');

$pmid = 21857895;

$json = pmid_to_json($pmid);

if ($json != '')
{
	$csl = pmid_to_csl($pmid);

	if ($csl)
	{
		// print_r($csl);
		
		$terms = array();
		
		if (isset($csl->title))
		{
			$terms[] = 'title="' . str_replace('"', '""', $csl->title) . '"';
		}
		
		if (isset($csl->DOI))
		{
			$terms[] = 'doi="' . $csl->DOI . '"';
		}
		
		$terms[] = 'json="' . str_replace('"', '""', $json) . '"';
		$terms[] = 'csl="' . str_replace('"', '""', json_encode($csl)) . '"';
				
		// print_r($terms);
		
		$sql = 'UPDATE pmid SET ' . join(",", $terms) . ' WHERE pmid=' . $pmid . ';';
		echo $sql;
	
	}
}

?>
