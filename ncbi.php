<?php

error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/env.php');

//----------------------------------------------------------------------------------------
function get($url, $content_type = '')
{	
	$data = null;

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE,
	  
	  CURLOPT_SSL_VERIFYHOST=> FALSE,
	  CURLOPT_SSL_VERIFYPEER=> FALSE,
	  
	);

	if ($content_type != '')
	{
		$opts[CURLOPT_HTTPHEADER] = array(
			"Accept: " . $content_type 
		);		
	}
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	return $data;
}

//----------------------------------------------------------------------------------------
// Given a PMID get the metadata in CSL format
function pmid_to_json($pmid)
{
	$json = '';
	
	$parameters = array(
		'db' 		=> 'pubmed',
		'retmode' 	=> 'json',
		'id' 		=> $pmid,
		'api_key'	=> getenv('NCBI_API_KEY')
	);
	
	$url = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?' . http_build_query($parameters);

	$json = get($url);

	return $json;
}

//----------------------------------------------------------------------------------------
// Given a PMID get the metadata in CSL format
function pmid_to_csl($pmid)
{
	$csl = null;
		
	$json = pmid_to_json($pmid);
	
	$obj = json_decode($json);

	if ($obj)
	{
		if (isset($obj->result->{$pmid}))
		{
			$csl = new stdclass;
			
			foreach ($obj->result->{$pmid} as $k => $v)
			{
				if (!empty($v))
				{
					switch ($k)
					{
						case 'authors':
							$csl->author = array();
							foreach ($v as $a)
							{
								$author = new stdclass;
								$author->literal = $a->name;
								$csl->author[] = $author;
							}
							break;
					
						case 'title':
						case 'volume':
						case 'issue':
							$csl->{$k} = $v;
							break;
	
						case 'source':
							$csl->{'container-title'} = $v;
							break;
							
						case 'pages':
							$csl->page = $v;
							break;
							
						case 'issn':
						case 'essn':
							if (!isset($csl->ISSN))
							{
								$csl->ISSN = array();
							}
							$csl->ISSN[] = $v;
							break;
							
						case 'articleids':
							foreach ($v as $articleid)
							{
								switch ($articleid->idtype)
								{
									case 'doi':
										$csl->DOI = $articleid->value;
										break;
	
									case 'pubmed':
										$csl->PMID = (Integer)$articleid->value;
										break;
										
									default:
										break;						
								}
							}
							break;
							
						case 'pubtype':
							foreach ($v as $type)
							{
								switch ($type)
								{
									case 'Journal Article':
										$csl->type = 'journal-article';
										break;
								
									default:
										break;
								}
							}
							break;
							
						case 'pubdate':
						case 'epubdate':
							if (!isset($csl->issued))
							{
								$date = $v;
								$csl->issued = new stdclass;			
								$csl->issued->{'date-parts'} = array();
		
								if (preg_match('/^([0-9]{4})$/', $date))
								{
									$csl->issued->{'date-parts'}[0] = array((Integer)$date);
								}
								
								if (preg_match('/^([0-9]{4})\s+([A-Z]\w+)$/', $date, $m))
								{
									$csl->issued->{'date-parts'}[0][] = (Integer)$m[1];
									$csl->issued->{'date-parts'}[0][] = (Integer)date("n",strtotime($m[2]));															
								}
		
								if (preg_match('/^([0-9]{4})\s+([A-Z]\w+)\s+(\d+)$/', $date, $m))
								{
									$csl->issued->{'date-parts'}[0][] = (Integer)$m[1];
									$csl->issued->{'date-parts'}[0][] = (Integer)date("n",strtotime($m[2]));
									$csl->issued->{'date-parts'}[0][] = (Integer)$m[3];															
								}
							}
							break;
					
						default:
							break;
			
					}				
				}
			}
			
			if (isset( $obj->result->{$pmid}->title))
			{		
				$csl->title = $obj->result->{$pmid}->title;
			}
		}
		
	}
	return $csl;
}

?>
