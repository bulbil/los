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

				article = _.pairs(data[0]);
				_.each(article, function(i){
					if(i[0] == 'date_published') {
						d = i[1].split('-');
						i[1] = d[1] + '-' + d[0];
					}
					if(i[0] == 'type') { 
						i[1] = i[1].charAt(0).toUpperCase() + i[1].substr(1); 
						$('input#type').select2('val', [i[1]]);}
					losFormViews.appendInput(i[0], i[1]);
				})
		});
	},
	appendReview: function(id) { 

		$.getJSON('../includes/json.php?p=review&id=' + id, function(data) {

			review = _.pairs(data[0]);
			_.each(review, function(i) {
				losFormViews.appendInput(i[0], i[1]);
			})
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

	reconcileReview: function(id) {

		losFormViews.formValidation();
		losFormViews.themesList();
		losFormViews.tagsLists();
		losFormViews.mainList();
		losFormViews.typeList();

		losFormViews.appendArticle(id);

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


		$.getJSON('../includes/json.php?p=review&id=' + id, function(data){

			review1 = data[0];
			
			$.getJSON('../includes/json.php?p=review&id=' + id, function(data){
				
				review2 = data[0];

				$('#narration-pov-review-1 p').append(review1.narration_pov);
				$('#narration-pov-review-2 p').append(review2.narration_pov);
				
				$('#narration-tense-review-1 p').append(review1.narration_tense);
				$('#narration-tense-review-2 p').append(review2.narration_tense);

				$('#notes-review-1 p').append(review1.notes);
				$('#notes-review-2 p').append(review2.notes);

				$('#research-notes-review-1 p').append(review1.research_notes);
				$('#research-notes-review-2 p').append(review2.research_notes);

				$('#summary-review-1 p').append(review1.summary);
				$('#summary-review-2 p').append(review2.summary);


				function reviewBool(key) {

					domID = key.replace('_','-');
					boolResponse1 = (review1[key] == 1) ? "<span style='color: #5cb85c'><em>yes</em></span>" : "<span style='color: #428bca;'><em>nope</em></span>";  
					$('#' + domID + '-review-1 p').append(boolResponse1);
					boolResponse2 = (review2[key] == 1) ? "<span style='color: #5cb85c'><em>yes</em></span>" : "<span style='color: #428bca;'><em>nope</em></span>";  
					$('#' + domID + '-review-2 p').append(boolResponse2);
				}

				reviewBool('narration_embedded');
				reviewBool('narration_tenseshift');
			});
		});

		$.getJSON('../includes/json.php?p=tags&id=' + id, function(data){

			review1tags = data;
				
			$.getJSON('../includes/json.php?p=tags&id=' + id, function(data){

				review2tags = data;

				_.each(losFormViews.categories, function(category){

					domID = category.replace('_','-');

					review1tagsByCategory = makeArray(review1tags, category, 'category', 'tag');
					$('#' + domID + '-review-1 ul').append("<li>" + review1tagsByCategory.join("</li><li>"));

					review2tagsByCategory = makeArray(review2tags, category, 'category', 'tag');
					$('#' + domID + '-review-2 ul').append("<li>" + review2tagsByCategory.join("</li><li>"));										
					
					sharedTagsByCategory = _.intersection(review1tagsByCategory, review2tagsByCategory);
					$('input#' + domID).select2('val', [sharedTagsByCategory]);
				});

				review1tagsMain = makeArray(review1tags, '1', 'if_main', 'maintag');
				$('#main-review-1 ul').append("<li>" + review1tagsMain.join("</li><li>"));

				review2tagsMain = makeArray(review2tags, '1', 'if_main', 'maintag');
				$('#main-review-2 ul').append("<li>" + review1tagsMain.join("</li><li>"));

				sharedTagsMain = _.intersection(review1tagsMain, review2tagsMain);
				$('input#main').select2('val', [sharedTagsMain]);
			});
		});

		$.getJSON('../includes/json.php?p=themes&id=' + id, function(data){

			review1themes = data;
						
			$.getJSON('../includes/json.php?p=themes&id=' + id, function(data){
				review2themes = data;

				review1themesMain = makeArray(review1themes, '1', 'if_main', 'maintheme');
				$('#main-review-1 ul').append("<li>" + review1themesMain.join("</li><li>"));

				review2themesMain = makeArray(review2themes, '1', 'if_main', 'maintheme');				
				$('#main-review-2 ul').append("<li>" + review2themesMain.join("</li><li>"));

				sharedThemesMain = _.intersection(review1themesMain, review2themesMain);
				mainList = $('input#main').select2('val');
				mainList = mainList.concat(sharedThemesMain);
				$('input#main').select2('val', [mainList]);

				review1themes = _.pluck(review1themes, 'theme');
				$('#themes-list-review-1 ul').append("<li>" + review1themes.join("</li><li>"));

				review2themes = _.pluck(review2themes, 'theme');
				$('#themes-list-review-2 ul').append("<li>" + review2themes.join("</li><li>"));

				sharedThemes = _.intersection(review1themes, review2themes);
				$('input#themes-list').select2('val', [sharedThemes]);
			});
		});

		losFormViews.appendMain();
	},

	dataTable: function() { }
}