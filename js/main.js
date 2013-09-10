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

// functions for modifying form values
var losFormViews = {

// adds the themes to the themes list
	themesList: function() { 

		$.getJSON('../includes/json.php?p=themes_list', function(data){
			themes = _.pluck(data, 'theme');
			$('input#themes-list').select2({
			width: '100%',
			tags: themes,
			createSearchChoice: function(term){return '';},
			closeOnSelect: false,
			openOnEnter: false
			});
		});
	},

	tagsLists: function(){

		$.getJSON('../includes/json.php?p=dump_tags', function(data){
			
			tags = data;

			_.each($('#tags input'), function(i) {

				category = i.getAttribute('id');
				tagsCategory = _.filter(tags, function(e) { return e.category == category; });
				tagsCategory = _.pluck(tagsCategory, 'tag');
				input = $('input#' + category);
				input.select2({ 
					width: '100%',
					tags: tagsCategory,
				});
			});
		});
	},

	mainLists: function() {

		$('input#main').select2({
			width: '100%',
			tags: []
		})
	},

	appendInput: function(key, value) {
		if($('form input#' + key)[0]) $('input#' + key.replace('_','-')).attr('value', value);
		if($('form textarea#' + key)[0]) { $('textarea#' + key.replace('_','-')).append(value);}
	 },
	appendReview: function() { },
	appendThemes: function() { },
	appendTags: function() { },

// appends data to the form in order to edit an existing review	
	editReview: function(id) { 

		$.getJSON('../includes/json.php?p=article&id=' + id, function(data) {

				article = _.pairs(data[0]);
				_.each(article, function(i){
					losFormViews.appendInput(i[0], i[1]);
				})
		});

		$.getJSON('../includes/json.php?p=review&id=' + id, function(data) {

			review = _.pairs(data[0]);
			_.each(review, function(i) {
				losFormViews.appendInput(i[0], i[1]);
			})
		});

		mainList = [];

		$.getJSON('../includes/json.php?p=themes&id=' + id, function(data) {

			articleThemes = _.pluck(data, 'theme');
			$('input#themes-list').select2('val', [articleThemes]);
			mainThemes = _.filter(data, function(e) { return e.if_main == 1; })
			mainThemes = _.pluck(mainThemes, 'theme');
			mainThemes = _.map(mainThemes, function(e) { return 'Theme: ' + e; });
			mainList = mainList.concat(mainThemes);
			$('input#main').select2('val', [mainList]);
		}); 

		$.getJSON('../includes/json.php?p=tags&id=' + id, function(data) {
			
			tags = data;
			_.each($('#tags input'), function(i) {
				category = i.getAttribute('id');
				tagsCategory = _.filter(tags, function(e) { return e.category == category; });
				tagsCategory = _.pluck(tagsCategory, 'tag');
				$('input#' + category).select2('val', [tagsCategory]);
			});

			mainTags = _.filter(tags, function(e) { return e.if_main == 1; });
			mainTags = _.map(mainTags, function(e) { 
				prefix = e.category.charAt(0).toUpperCase() + e.category.substr(1) + ': ';
				return  prefix + e.tag; });
			console.log(mainTags);
			mainList = mainList.concat(mainTags);
			$('input#main').select2('val', [mainList]);
		});

		$('#s2id_main ul.select2-choices').click(function() { 
				$('input#main').select2({
					tags: function(){ 

						mainTags = [];

						_.each($('#tags input'), function(e) { 

							category = e.getAttribute('id');
							input = $('input#' + category);
							categoryTags = _.filter(input.select2('val'), function(e) { if(!_.isObject(e)) return e; });
							categoryTags = _.map(categoryTags, function(e) { 
							
								prefix = category.charAt(0).toUpperCase() + category.substr(1) + ': '; 
								return prefix + e;
							});
							mainTags = mainTags.concat(categoryTags);
						});

						mainTags = mainTags.concat(_.map($('input#themes-list').select2('val'), function(e) { return 'Theme: ' + e;}));
						console.log(mainTags);
						return mainTags;	
					}
				});
			;})
	},

// appends data to the form from the last review by the current reviewer
	lastReview: function() {

			$.getJSON('../includes/json.php?p=last', function(data) {

				lastReview = data[0];
				losFormViews.appendInput('issue', lastReview.issue);
				losFormViews.appendInput('volume', lastReview.volume);
				losFormViews.appendInput('date_published', lastReview.date_published);
			})
	},

	dataTable: function() { }

}