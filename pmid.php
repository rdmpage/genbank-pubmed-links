<?php

// Get details for PubMed ids

require_once(dirname(__FILE__) . '/ncbi.php');

$pmids = array(
7476123,
8896377,
9159184,
9339350,
9615625,
9620266,
9635503,
9725849,
9729883,
9878231,
9880918,
10024439,
10368956,
10474899,
10555374,
10626039,
10636831,
10823673,
12755171,
21684998,
21857895,
28565202
);

foreach ($pmids as $pmid)
{
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
}

?>
