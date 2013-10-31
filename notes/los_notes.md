##LoS Notes

10.20 NK

###A Brief Introduction to the Architecture

Hope this is at all logical. Please feel free to email me (bil@umich.edu) should you have any questions at all.

The app is broken up according to (very loose!) idea of Model / View / Controller.

'includes' has all the php that talks with the database while 'html' contains reusable html fragments -- 'views' contains simple files with minimal logic that glue the two together.

The main libraries used are select2 and dataTables, both open source and well-documented and with an active development community -- as well as Bootstrap 3.

####css
Stylesheets from bootstrap and those from the js libraries I used.

style.css includes custom tweaks that I've added.

###fonts
Standard with Bootstrap, includes sources for the glyphicons.

###html
The file names of the html files should be self explanatory. The form fragments are used according to the different form configurations. The prefix 'rec' indicates form views accessed when reconciling and editing reconciled reviews. reviewer-table.html is called for the main reviewer page's table.

###img
Static image files.

###includes
####misc
csv_assoc_array - Brute force way of getting text spreadsheet data into db. Obsolete first version.

db - DB connect info and connect function

json - returns various json objects based on get request, hopefully easily modified to return what kind of data is needed.

utilities - collection of functions, mostly for 1) interfacing with the db, 2) formatting data for various uses, 3) rendering tables. Epic number of lines.

render-submit-form - An intermediate step between the form and editing the database. Has the logic for rendering different confirmation forms and executing changes to the db.

####db interface
insert-csv - Populates the database with data from the text datapoints google spreadsheet. Can use other csv files with minimal modification.

insert-img-csv - Populates the database with data from the image datapoints google spreadsheet.

insert-form - Form's interface with the db. Inserts/edits form data.

themes - Populates the database with themes data from the themes google spreadsheet authority list

###js
All the js files/libraries. Powered by Bootstrap, JQuery, select2 and the dataTables.

losData - For rendering the dataTables instances on the data-table view.

losForm - All the js that goes into the form, including populating fields, syncing, validating, disabling fields, etc. Kind of a beast.

###views
PHP files actually served to users. Includes the login / session logic.

csv - Enters google spreadsheet data into the database. With a get parameter decides whether to enter text datapoints or image.

visualization - A place for expanding out and showcases visualizations. D3's already included in the js to so go to it ...

###los_create_db.sql
This file consists of a model of the database. Should be fairly accurate; I've made no modifications to the db structure without updating this file.




