# GenBank PubMed Links

Creating a database of links between GenBank accession numbers and PubMed ids.

## Source data

The file `gb264.pmid_list.other.txt.gz` from the [GenBabnk FTP server](https://ftp.ncbi.nlm.nih.gov/genbank/catalog/) is a list of GenBank accession numbers and one or more PMIDs (separated by commas).

Rather than store all of these, the goal is to extract a subset of links based on a target list of GenBank accession numbers (such as those from the BOLD DNA barcoding database).

### BOLD

Accession numbers from BOLD can be found in their data dumps. Some accessions have suffixes indicating that the records have been suppressed (e.g., `GU654642-SUPPRESSED`) or withdrawn (e.g., `HQ974947-WITHDRAWN`). Some accessions may also contain spurious characters, e.g. `ï»¿JN303723-WITHDRAWN`.

### ORCID

Given a DOI we can use the [ORCID](https://orcid.org) API to attempt to find the ORCID(s) for any of that paper’s authors. We can then populate a table of author details from ORCID by retrieving data for the ORCID as JSON-LD. One potenial use is to try and match people who have identified specimens in BOLD with those who have authored barcode papers.

## Database

We use a simple SQLite database to store the links between GenBank and PubMed. The `accession_pmid` table includes a column called `flag` where we can insert, say, `1` to flag a record that has problems, such as a PMID that does not resolve. Examples include [27797954](https://pubmed.ncbi.nlm.nih.gov/27797954/) which redirects to [28172670](https://pubmed.ncbi.nlm.nih.gov/28172670/).

### Schema

```
CREATE TABLE accession (
    accession TEXT PRIMARY KEY
);

CREATE TABLE "accession_pmid" (
    accession TEXT
  , pmid INTEGER
  , flag INTEGER
  , PRIMARY KEY(accession, pmid)
);

CREATE INDEX ap_flag ON accession_pmid(flag ASC);

CREATE TABLE "pmid" (
    pmid INTEGER PRIMARY KEY
  , doi TEXT
  , title TEXT
  , json TEXT
  , csl TEXT
);
```

### Views

The database has views to display accessions that lack a PMID, or have poorly formed accession numbers, and PMIDs that don’t resolve. We also have views to dump a list of the accession to PMID mapping, and metadata for each PMID.

```
CREATE VIEW accession_without_pmid AS
SELECT accession FROM accession LEFT JOIN accession_pmid USING(accession) WHERE pmid IS NULL;

CREATE VIEW bad_accession AS
SELECT accession FROM accession WHERE accession LIKE “%-%”;

CREATE VIEW bad_pmid AS
SELECT pmid FROM pmid WHERE title IS NULL;

CREATE VIEW export_accession_pmid_doi AS
SELECT accession, pmid, doi 
FROM accession_pmid INNER join pmid USING(pmid) ORDER BY accession;

CREATE VIEW export_pmid AS
SELECT pmid, doi, title, csl
FROM pmid ORDER BY pmid;

```

## Scripts

`intersect.php` takes `gb264.pmid_list.other.txt` and loads it into memory, then loads a list of accessions (e.g., `insdc_acs.csv`) and outputs the (accession, pmid) pair in SQL to insert into the `accession_pmid` table. We also output a list of PMIDs to insert into the `pmid` table.

`pmid-details.php` takes a list of PMIDs and retrieves information from NCBI using the Entrez API. The PMID record is retrieved in JSON, then converted into CSL-JSON. Both are stored in the database, along with the publication title and the DOI (if present).

`import-accession.php` reads a list of accession numbers and adds them to the `accession` table. This enables us to compute a list of accession numbers that lack PMIDs.

## Output

Output files use tab separated values (TSV). The file `export_accession_pmid_doi.tsv` lists all accession numbers that  have been mapped to a PMID (and also include the DOI if known). The file `export_pmid.tsv` lists basic metadata for each PMID, such as DOI, title, and bibliographic information in CSL-JSON format (which can be formatted using tools such as [Citation.js](https://citation.js.org)).

