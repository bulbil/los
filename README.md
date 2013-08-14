####Land of Sunshine

A repository for the Land of Sunshine project, a digital humanities project sponsored by University of Michigan Department of English and the Graduate Library's Spatial and Numeric Data Services.

####What does it do?
Well, we're still figuring it out. So far locally-sourced, house-made metadata is lovingly applied to digitized volumes of *The Land of Sunshine: A Southwestern Magazine* (late 19th c. Californiana at it's best) at the article level, then:

- A web-based interface (PHP/MySQL/Bootstrap) allows logged in reviewers to add records to the db
- A reconciliation view facilitates two reviewers reconciling metadata on the same article with each other, adding a third, final record to the db for the article
- A sortable (possibly searchable) view displays all records
- Scripts automagically ingest already collected data living on a Google Spreadsheet
- A project management view tracks contributions by particular reviewers and articles assigned

*WE NEED TO TALK MORE ABOUT*:
- Tools for visualizing the data (frequency, time series, links, steam graph?)
- Tools for searching on the data
- Incorporating Internet Archive metadata
- test

####Ingredients
- custom php snippets
- bootstrap
- d3.js
- jQuery