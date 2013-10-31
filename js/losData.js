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

var losData = {

	articlesTable: table =  $('#articles-table').dataTable({

			"bProcessing": true,
			"bAutoWidth": false,
			"aLengthMenu": [[10, 25, 100, -1], [10, 25, 100, "All"]],
			"sAjaxSource": '/los/includes/json.php?p=data_table',
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
		}),

	imagesTable: table =  $('#images-table').dataTable({

			"bProcessing": true,
			"bAutoWidth": false,
			"aLengthMenu": [[10, 25, 100, -1], [10, 25, 100, "All"]],
			"sAjaxSource": '/los/includes/json.php?p=data_table&id=1',
			"aoColumns": [
				{"mData": "image.0", "sTitle": "id", "sWidth": "2%", "sType": "date"},
				{"mData": "image.2", "sTitle": "caption", "sWidth": "30%"},
				{"mData": "image.6", "sTitle": "author", "sWidth": "10%"},
				{"mData": "image.3", "sTitle": "volume", "sWidth": "10%", "sType": "date"},
				{"mData": "image.4", "sTitle": "issue", "sWidth": "10%", "sType": "date"},
				{"mData": "image.5", "sTitle": "page", "sWidth": "10%", "sType": "date"},
				{"mData": "image.7", "sTitle": "engraver", "sWidth": "10%", "sType": "date"},
				{"mData": "image.8", "sTitle": "date", "sWidth": "10%", "sType": "date"},
				{"mData": "image.9", "sTitle": "rotated", "sWidth": "10%", "sType": "date"},
				{"mData": "image.10", "sTitle": "placement", "sWidth": "10%", "sType": "date"},
				{"mData": "tags", "sTitle": "main tags", "sWidth": "5%"},
				{"mData": "themes", "sTitle": "main themes", "sWidth": "5%"},
			]
		}),

	initColChooser: function(table){

		var columns = [];
		var container = table.selector.split('-')[0];
		console.log(container);

		_.each($(container + ' th'), function(e) { columns.push($(e).html()); });

		$(container + ' .columns-chooser ul').append(
			"<li class='list-group-item'><button class='btn' id='reset'>select all/none</button></li>"
		);

		_.each(columns, function(e){

			$(container + ' .columns-chooser ul').append(
				"<li class='list-group-item'><input type='checkbox' name='" + e + "'>" 
				+ "<label class='pull-right'>" + e + "</label></input></li>"
			);
		});

		$(container + ' .columns-chooser li input').prop('checked',true);
	},

	activateColChooser: function(table) {

		var container = table.selector.split('-')[0];

		$(container + ' .columns-chooser').change(function(){ losData.setVisibility(table); });

		$(container + ' .dropdown-menu li').click(function(e) { e.stopPropagation(); });
		
		var resetBool = true;
		$(container + ' .columns-chooser button#reset').click(function(e) { 

			resetBool = (resetBool === true) ? false : true;
			_.each($(container + ' .columns-chooser :checkbox'), function(e, index) {
				$(e).prop('checked',resetBool);
				losData.setVisibility(table);
			});
		});
	},

	setVisibility: function(table) {

		var container = table.selector.split('-')[0];

		_.each($(container + ' .columns-chooser :checkbox'), function(e, index) {
			
			table.fnSetColumnVis(index, e.checked);
			table.fnSettings().aoColumns[index].bSearchable = e.checked;
		});
	}
}

$('.dataTables_filter input').addClass('form-control')
	.attr('placeholder', 'Search Columns');

losData.initColChooser(losData.articlesTable);
losData.activateColChooser(losData.articlesTable);

losData.initColChooser(losData.imagesTable);
losData.activateColChooser(losData.imagesTable);