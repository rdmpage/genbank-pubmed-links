# GenBank PubMed Links

Creating a database of links between GenBank accession numbers and PubMed ids.

## Source data

The file `https://ftp.ncbi.nlm.nih.gov/genbank/catalog/gb264.pmid_list.other.txt.gz` from https://ftp.ncbi.nlm.nih.gov/genbank/catalog/ is a list of GenBank accession numbers and one or more PMIDs (separated by commas).

Rather than upload all of these, the goal is to extract a subset of links based on a target list of GenBank accession numbers (such as those from the BOLD DNA barcoding database).

## Scripts

`intersect.php` takes `gb264.pmid_list.other.txt` and loads it into memory, then loads a list of accessions and outputs the (accession, pmid) pair in SQL to insert into the `accession_pmid` table. We also output a list of PMIDs to insert into the `pmid` table.

## Output


