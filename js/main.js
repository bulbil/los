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

	categories: ['groups','persons','entities', 'places', 'activities','flora_fauna','commodities','events','works','technologies','environments'],

	// functions for validating entries
	formValidation: function(){

		inputIDs = ['page-start', 'page-end', 'issue', 'volume'];

		function validateNum(e) {
			$('input#' + e).change(function() {
				val = Number($(this).val());
				if(val > 0) $('#' + e + '-group').removeClass('has-error');
				if(!val) $('#' + e + '-group').addClass('has-error');
			});
		}

		_.each(inputIDs, function(e) { validateNum(e); })

		$('input#date-published').change(function() {

			reg = new RegExp(/\d{2}-\d{4}/);
			if($(this).val().match(reg)) $('#date-published-group').removeClass('has-error');
			if(!$(this).val().match(reg)) $('#date-published-group').addClass('has-error');
		})
	},

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

			_.each(losFormViews.categories, function(category) {

				tagsCategory = _.filter(tags, function(e) { return e.category == category; });
				tagsCategory = _.pluck(tagsCategory, 'tag');
				input = $('input#' + category.replace('_','-'));
				input.select2({ 
					width: '100%',
					tags: tagsCategory,
				});
			});
		});
	},

	mainList: function() {

		$('input#main').select2({
			width: '440px',
			tags: []
		})
	},

	typeList: function() {
		$('input#type').select2({
			width: '100%',
			tags: ['Advertisement', 'Editorial', 'Fiction', 'Nonfiction', 'Poetry'],
			createSearchChoice: function(term){return '';},
		});
	},

	appendInput: function(key, value) {
		key = key.replace('_','-');
		if($('form input#' + key)[0]) $('input#' + key).attr('value', value);
		if($('form textarea#' + key)[0]) $('textarea#' + key).append(value);
		if($('form select#' + key)[0]) $('textarea#' + key).append(value);
	 },

	appendArticle: function(id) {

				$.getJSON('../includes/json.php?p=article&id=' + id, function(data) {

				article = data[0];
				d = article.date_published.split('-');
				article.date_published = d[1] + '-' + d[0];

				article.type = article.type.charAt(0).toUpperCase() + article.type.substr(1); 
				$('input#type').select2('val', [article.type]);

				recMessage = (article.reconciled == 1) ? "<span style='color: #5cb85c'><em>yes</em></span>" : "<span style='color: #428bca;'><em>nope</em></span>";
				$('label#reconciled').append(recMessage);

				_.each(_.keys(article), function(key){
					losFormViews.appendInput(key, article[key]);
				});
		});
	},
	appendReview: function(id) { 

		$.getJSON('../includes/json.php?p=review&id=' + id, function(data) {

			review = data[0];
			_.each(_.keys(review), function(key) {
				console.log(review[key]);
				losFormViews.appendInput(key, review[key]);
			})
			$("input[name='timestamp']").val(review[timestamp]);
		});
	},

	appendThemes: function(id) { 
		$.getJSON('../includes/json.php?p=themes&id=' + id, function(data) {

			articleThemes = _.pluck(data, 'theme');
			$('input#themes-list').select2('val', [articleThemes]);
			mainThemes = _.filter(data, function(e) { return e.if_main == 1; })
			mainThemes = _.pluck(mainThemes, 'theme');
			mainThemes = _.map(mainThemes, function(e) { return 'Theme: ' + e; });
			mainList = $('input#main').select2('val');
			mainList = mainList.concat(mainThemes);
			$('input#main').select2('val', [mainList]);
		}); 

	},

	appendTags: function(id, str = '') { 

		$.getJSON('../includes/json.php?p=tags&id=' + id, function(data) {
			
			tags = data;
			_.each(losFormViews.categories, function(e) {
				category = e;
				tagsCategory = _.filter(tags, function(e) { return e.category == category; });
				tagsCategory = _.pluck(tagsCategory, 'tag');
				$('input#' + category).select2('val', [tagsCategory]);
			});

			mainTags = _.filter(tags, function(e) { return e.if_main == 1; });
			mainTags = _.map(mainTags, function(e) { 
				prefix = e.category.charAt(0).toUpperCase() + e.category.substr(1) + ': ';
				return  prefix + e.tag; });
			mainList = $('input#main').select2('val');
			mainList = mainList.concat(mainTags);
			$('input#main').select2('val', [mainList]);
		});
	},

	appendMain: function() {

		$('#s2id_main ul.select2-choices').click(function() {
				$('input#main').select2({
					tags: function(){ 

						mainTags = [];

						_.each(losFormViews.categories, function(category) { 

							input = $('input#' + category);
							categoryTags = _.chain(input.select2('val'))
								.filter( function(e) { if(!_.isObject(e)) return e; })
								.map( function(e) { return prefix = category.charAt(0).toUpperCase() + category.substr(1) + ': ' + e; })
								.value();
							mainTags = mainTags.concat(categoryTags);
						});

						mainTags = mainTags.concat(_.map($('input#themes-list').select2('val'), function(e) { return 'Theme: ' + e;}));
						return mainTags;	
					}
				});
			;})
	},

// appends data to the form in order to edit an existing review	
	editReview: function(id) {

		losFormViews.formValidation();
		losFormViews.themesList();
		losFormViews.tagsLists();
		losFormViews.mainList();
		losFormViews.typeList();

		losFormViews.appendArticle(id);
		losFormViews.appendReview(id);
		losFormViews.appendThemes(id);
		losFormViews.appendTags(id);
		losFormViews.appendMain();

	},

// appends data to the form from the last review by the current reviewer
	lastReview: function() {

		losFormViews.formValidation();
		losFormViews.themesList();
		losFormViews.tagsLists();
		losFormViews.mainList();
		losFormViews.typeList();
		losFormViews.appendMain();

		$.getJSON('../includes/json.php?p=last', function(data) {

			lastReview = data[0];
			losFormViews.appendInput('issue', lastReview.issue);
			losFormViews.appendInput('volume', lastReview.volume);
			d = lastReview.date_published.split('-');
			d = d[1] + '-' + d[0];
			losFormViews.appendInput('date_published', d);
		})
	},

	reconcileReview: function(id1, id2) {

		losFormViews.formValidation();
		losFormViews.themesList();
		losFormViews.tagsLists();
		losFormViews.mainList();
		losFormViews.typeList();
		losFormViews.appendArticle(id1); 

		function makeArray(object, filter, column, p = '') {

			array = _.chain(object)
				.filter(function(e) { return e[column] == filter;})
				.map(function(e) { 
					
					switch(p){
						case 'tag': return e.tag;
						case 'maintag': return e.category.charAt(0).toUpperCase() + e.category.substr(1) + ': ' + e.tag;
						case 'theme': return e.theme;
						case 'maintheme': return 'Theme: ' + e.theme; 
					}
				})
				.value();
			return array;
		}

		$.getJSON('../includes/json.php?p=reviewer', function(data) {

			reviewer1 = data[0].initials;

			$.getJSON('../includes/json.php?p=reviewer&rid=' + id2, function(data){
					
				reviewer2 = data[0].initials;
				$('#narration-pov-review-1 h5').html(reviewer1);
				$('#narration-pov-review-2 h5').html(reviewer2);
				$('.one li.reviewer').html(reviewer1);
				$('.two li.reviewer').html(reviewer2);

			});
		});

		$.getJSON('../includes/json.php?p=review&id=' + id1, function(data){

			review1 = data[0];
			
			$.getJSON('../includes/json.php?p=review&id=' + id1 + '&rid=' + id2, function(data){
				
				review2 = data[0];

				function reviewText(key) {

					domID = key.replace('_', '-');
					$('#' + domID + '-review-1 p').append(review1[key]);
					$('#' + domID + '-review-2 p').append(review2[key]);
				}

				function reviewBool(key) {

					domID = key.replace('_','-');
					boolResponse1 = (review1[key] == 1) ? "<span style='color: #5cb85c'><em>yes</em></span>" : "<span style='color: #428bca;'><em>nope</em></span>";  
					$('#' + domID + '-review-1 p').append(boolResponse1);
					boolResponse2 = (review2[key] == 1) ? "<span style='color: #5cb85c'><em>yes</em></span>" : "<span style='color: #428bca;'><em>nope</em></span>";  
					$('#' + domID + '-review-2 p').append(boolResponse2);
				}

				reviewText('narration_pov');
				reviewText('narration_tense');
				reviewText('notes');
				reviewText('research_notes');
				reviewText('summary');
				reviewBool('narration_embedded');
				reviewBool('narration_tenseshift');
			});
		});

		$.getJSON('../includes/json.php?p=tags&id=' + id1, function(data){

			review1tags = data;
				
			$.getJSON('../includes/json.php?p=tags&id=' + id1 + '&rid=' + id2, function(data){

				review2tags = data;

				_.each(losFormViews.categories, function(category){

					domID = category.replace('_','-');

					review1tagsByCategory = makeArray(review1tags, category, 'category', 'tag');
					review2tagsByCategory = makeArray(review2tags, category, 'category', 'tag');
					sharedTagsByCategory = _.intersection(review1tagsByCategory, review2tagsByCategory);
					
					review1tagsByCategory = _.difference(review1tagsByCategory, sharedTagsByCategory);
					review2tagsByCategory = _.difference(review2tagsByCategory, sharedTagsByCategory);

					$('input#' + domID).select2('val', [sharedTagsByCategory]);
					$('#' + domID + '-review-1 ul').append("<li>" + review1tagsByCategory.join("</li><li>"));
					$('#' + domID + '-review-2 ul').append("<li>" + review2tagsByCategory.join("</li><li>"));										

				});

				review1tagsMain = makeArray(review1tags, '1', 'if_main', 'maintag');
				review2tagsMain = makeArray(review2tags, '1', 'if_main', 'maintag');
				sharedTagsMain = _.intersection(review1tagsMain, review2tagsMain);

				review1tagsMain = _.difference(review1tagsMain, sharedTagsMain);
				review2tagsMain = _.difference(review2tagsMain, sharedTagsMain);

				$('input#main').select2('val', [sharedTagsMain]);
				$('#main-review-1 ul').append("<li>" + review1tagsMain.join("</li><li>"));
				$('#main-review-2 ul').append("<li>" + review2tagsMain.join("</li><li>"));

			});
		});

		$.getJSON('../includes/json.php?p=themes&id=' + id1, function(data){

			review1themes = data;
									
			$.getJSON('../includes/json.php?p=themes&id=' + id1 + '&rid=' + id2, function(data){
				review2themes = data;

				review1themesMain = makeArray(review1themes, '1', 'if_main', 'maintheme');
				review2themesMain = makeArray(review2themes, '1', 'if_main', 'maintheme');				
				sharedThemesMain = _.intersection(review1themesMain, review2themesMain);

				review1themesMain = _.difference(review1themesMain, sharedThemesMain);
				review2themesMain = _.difference(review2themesMain, sharedThemesMain);

				mainList = $('input#main').select2('val');
				mainList = mainList.concat(sharedThemesMain);

				$('input#main').select2('val', [mainList]);
				$('#main-review-1 ul').append("<li>" + review1themesMain.join("</li><li>"));
				$('#main-review-2 ul').append("<li>" + review2themesMain.join("</li><li>"));

				review1themes = _.pluck(review1themes, 'theme');
				review2themes = _.pluck(review2themes, 'theme');
				sharedThemes = _.intersection(review1themes, review2themes);

				review1themes = _.difference(review1themes, sharedThemes);
				review2themes = _.difference(review2themes, sharedThemes);

				$('input#themes-list').select2('val', [sharedThemes]);
				$('#themes-list-review-1 ul').append("<li>" + review1themes.join("</li><li>"));
				$('#themes-list-review-2 ul').append("<li>" + review2themes.join("</li><li>"));

			});
		});

		losFormViews.appendMain();
	},

	dataTable: function() { }
}