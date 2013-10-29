/////////////////////////////////////////////////////////
//
//
//									<コ:彡
//
//						LAND OF SUNSHINE 
//						university of michigan digital humanities project
// 						nabil kashyap (nabilk.com)
//
/////////////////////////////////////////////////////////


$('#data-table').dataTable({

	"bProcessing": true,
	"bAutoWidth": false,
	"sAjaxSource": '/los/includes/json.php?p=test_table',
	"aoColumns": [
		{"mData": "article.0", "sTitle": "id", "sWidth": "2%", "sType": "date"},
		{"mData": "article.1", "sTitle": "title", "sWidth": "30%"},
		{"mData": "article.2", "sTitle": "author", "sWidth": "10%"},
		{"mData": "article.3", "sTitle": "location", "sWidth": "10%", "sType": "date"},
		{"mData": "article.4", "sTitle": "volume", "sWidth": "10%", "sType": "date"},
		{"mData": "article.5", "sTitle": "issue", "sWidth": "10%", "sType": "date"},
		{"mData": "article.6", "sTitle": "start page", "sWidth": "10%", "sType": "date"},
		{"mData": "article.7", "sTitle": "end page", "sWidth": "10%", "sType": "date"},
		{"mData": "article.8", "sTitle": "date", "sWidth": "10%", "sType": "date"},
		{"mData": "article.9", "sTitle": "type", "sWidth": "10%", "sType": "date"},
		{"mData": "tags", "sTitle": "main tags", "sWidth": "5%"},
		{"mData": "themes", "sTitle": "main themes", "sWidth": "5%"},
	]
});


var columns = [];

_.each($('#data-table th'), function(e) { columns.push($(e).html()); });

console.log(columns);


$('#data-table th.sorting').css('text-decoration', 'underline');

function setVis() {
	_.each($('ul#columns-chooser li input'), function(e, index) {
		$('#data-table').dataTable().fnSetColumnVis(index, e.checked);
	});
}

_.each(columns, function(e){

	$('#columns-chooser ul').append(
		"<li class='list-group-item'><input type='checkbox' value='1' name='" + e + "'>" 
		+ "<label class='pull-right'>" + e + "</label></input></li>"
	);
});

$('.dropdown-menu li').click(function(e) {
    e.stopPropagation();
});

$('.dataTables_filter input').addClass('form-control input-sm');
$('.dataTables_filter input').attr('placeholder', 'Search Columns');

$('#columns-chooser input').attr('checked',true);

$('#columns-chooser').change(function(){ setVis(); });