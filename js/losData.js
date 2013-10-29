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
	"aLengthMenu": [[10, 25, 100, -1], [10, 25, 100, "All"]],
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

var losData = {
	
	initColumns: function(){

		var columns = [];
		_.each($('#data-table th'), function(e) { columns.push($(e).html()); });

		$('#columns-chooser ul').append(
			"<li class='list-group-item'><button class='btn' id='reset'>select all/none</button></li>"
		);

		_.each(columns, function(e){

			$('#columns-chooser ul').append(
				"<li class='list-group-item'><input type='checkbox' name='" + e + "'>" 
				+ "<label class='pull-right'>" + e + "</label></input></li>"
			);
		});

		$('#columns-chooser li input').attr('checked',true);

		$('#columns-chooser').change(function(){ losData.setVisibility(); });

		$('.dropdown-menu li').click(function(e) { e.stopPropagation(); });
		
		var resetBool = true;
		$('#columns-chooser button#reset').click(function(e) { 

			console.log('fire ' + resetBool);
			resetBool = (resetBool === true) ? false : true;
			_.each($('#columns-chooser :checkbox'), function(e, index) {
				$(e).prop('checked',resetBool);
				$('#data-table').dataTable().fnSetColumnVis(index, resetBool);
			});
			
		});
	},

	setVisibility: function() {

		_.each($('#columns-chooser :checkbox'), function(e, index) {
			$('#data-table').dataTable().fnSetColumnVis(index, e.checked);
		});
	}
}

$('.dataTables_filter input').attr('placeholder', 'Search Columns');

losData.initColumns();