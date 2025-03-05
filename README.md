# GenBank PubMed Links

Creating a database of links between GenBank accession numbers and PubMed ids.

## Source data

The file `https://ftp.ncbi.nlm.nih.gov/genbank/catalog/gb264.pmid_list.other.txt.gz` from https://ftp.ncbi.nlm.nih.gov/genbank/catalog/ is a list of GenBank accession numbers and one or more PMIDs (separated by commas).

Rather than store all of these, the goal is to extract a subset of links based on a target list of GenBank accession numbers (such as those from the BOLD DNA barcoding database).

### BOLD

Accession numbers from BOLD can be found in their data dumps. Some accessions have suffixes indicating that the records have been suppressed (e.g., `GU654642-SUPPRESSED`) or withdrawn (e.g., `HQ974947-WITHDRAWN`). Some accession may also contain spurious characters, e.g. `ï»¿JN303723-WITHDRAWN`.

## Database

We have a simple SQLite database to store the links between GenBank and PubMed. The `accession_pmid` table includes a column called `flag` where we can insert, say, `1` to flag a record that has problems, such as a PMID that does not resolve. Examples include [27797954](https://pubmed.ncbi.nlm.nih.gov/27797954/) which redirects to [28172670](https://pubmed.ncbi.nlm.nih.gov/28172670/).

### Views

The database has views to display accessions that lack a PMID, or are poorly formed, and PMIDs that don’t resolve.

## Scripts

`intersect.php` takes `gb264.pmid_list.other.txt` and loads it into memory, then loads a list of accessions (e.g., `insdc_acs.csv`) and outputs the (accession, pmid) pair in SQL to insert into the `accession_pmid` table. We also output a list of PMIDs to insert into the `pmid` table.

`pmid-details.php` takes a list of PMIDs and retrieves information from NCBI using the Entrez API. The PMID record is retrieved in JSON, then converted into CSL-JSON. Both are stored in the database, along with the publication title and the DOI (if present).

`import-accession.php` reads a list of accession numbers and adds them to the `accession` table. This enables us to compute a list of accession numbers that lack PMIDs.

## Output


