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
		{"sTitle": "date", "sWidth": "10%", "sType": "date"},
		{"sTitle": "title", "sWidth": "30%"},
		{"sTitle": "author", "sWidth": "10%"},
		{"sTitle": "tag", "sWidth": "5%"},
		{"sTitle": "theme", "sWidth": "5%"}
	]

});

columns = ["date", "title", "author", "tag", "theme" ];


function setVis() {
	_.each($('#columns-chooser input'), function(e, index) {

		$('#data-table').dataTable().fnSetColumnVis(index, e.checked);
	});
}

_.each(columns, function(e){
	$('#columns-chooser').append(
		
		"<input type='checkbox' value='1' name='" + e + "'>", 
		"<label for='" + e + "'>" + e + '</label>'
	);
});

$('#columns-chooser input').attr('checked',true);

$('#columns-chooser').change(function(){ setVis(); });