##LoS Notes

###8.23

- Took days to get a script for parsing the G-Spreadsheet outputted CSV up and running and still not totally functional.
- Specific problems:
-- Single-quotes in tags do not escape well though single-quotes in the title do for some reason.
-- Still plenty of special characters to deal with.
-- Need a new way to access the article_id -- $pdo->last\_id hack doesn't sync for some reason -- increments before necessary.

Things I did:
- Moved to a system of Booleans for 'is_secondary' (for secondary themes) and for 'is\_main' for whether a tag or themes associated with an article is a main element
- Function to take data and return Booleans
- Function to return strings from special fields: type, datetime, timestamp, themes, tags
- Wrote a script to insert themes and secondary themes


The idea is that with all this written, actually submitting new data will be much easier. Plus reviewers are still submitting new articles via the Google forms -- so getting this dialed will be necessary regardless. Just feels tedious and slow.
