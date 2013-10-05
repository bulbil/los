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
var losForm = {

	// categories for the tags
	categories: ['activities','commodities','entities','environments','events',
				'florafauna','groups','persons','places','technologies','works'],

	inputIDs: ['page-start', 'page-end', 'issue', 'volume', 'date-published'],

	// functions for validating entries ... add more id's to the array to validate additional fields
	submitCheck: function(array) {

		fieldVals = [];

		_.each(array, function(e){ fieldVals.push($('input#' + e).val()); });
		toggle = ($('.has-error').length != 0 || !_.every(fieldVals)) ? false : true; 
		losForm.toggleDisable(['input#form-submit'],toggle);
	},

	formValidation: function(){

		function validateNum(array) {

			_.each(array, function(e) {

				$('input#' + e).change(function() {

					if(e == 'date-published') { 

						val = $(this).val();
						reg = new RegExp(/\d{2}-\d{4}/);
						if($(this).val().match(reg)) $('#date-published-group').removeClass('has-error');
						if(!$(this).val().match(reg)) $('#date-published-group').addClass('has-error');
					}else{

						val = Number($(this).val());
						if(val > 0) $('#' + e + '-group').removeClass('has-error');
						if(!val) $('#' + e + '-group').addClass('has-error');
					}
					losForm.submitCheck(array);
				});
			});
		}
	
	validateNum(losForm.inputIDs);
	},

// adds the themes to the themes list
	themesList: function(id, img = 0) { 

		$.getJSON('../includes/json.php?p=themes_list', function(data){
			themes = _.pluck(data, 'theme');
			$('input#themes').select2({
				width: '100%',
				tags: themes,
				createSearchChoice: function(term){return '';},
				closeOnSelect: false,
				openOnEnter: false
			});

			losForm.appendThemes(id, 0, img);
		});
	},

// adds the tags lists to the different tag category inputs
	tagsLists: function(id, img = 0){

		$.getJSON('../includes/json.php?p=dump_tags', function(data){
			
			tags = data;

			_.each(losForm.categories, function(category) {
				tagsCategory = _.filter(tags, function(e) { return e.category == category; });
				tagsCategory = _.pluck(tagsCategory, 'tag');
				// initializes each tag input as a select2 thing so the library can do its magic
				$('input#' + category).select2({ 
					width: '100%',
					tags: tagsCategory,
					closeOnSelect: false,
					openOnEnter: false
				});
			});

			losForm.appendTags(id, 0, img);
		});
	},

// initializes input#main as a select2 object so the select2 library can do its magic
	mainList: function() {

		$('input#main').select2({
			width: '440px',
			tags: [],
			closeOnSelect: false
		})
	},

// initializes input#type as a select2 object so the select2 library can do its magic
// if image is selected, makes the image fields available, disables the narration fields, sets up
// the select lists (image type, page placement) under the image tab
	typeList: function() {
		$('input#type').select2({
			width: '100%',
			tags: ['Advertisement', 'Editorial', 'Fiction', 'Nonfiction', 'Poetry'],
			createSearchChoice: function(term){return '';},
		});
	},

// initializes inputs for image fields
	imageLists: function() {

		$('input#img-type').select2({tags: ['drawing', 'engraving', 'photograph']});
		$('input#img-placement').select2({

			tags: ['1', '2', '3', '4', '5', '6'], 
			createSearchChoice: function(term){return '';},
		});
	},

// helper function for appending info to a particular form input type, whether input or textarea
	appendInput: function(key, value) {
		domID = key.replace('_','-');
		if($('form input#' + domID)[0]) $('input#' + domID).val(value);
		if($('form textarea#' + domID)[0]) $('textarea#' + domID).append(value);
		if($("input[name='" + key + "']")[0]) $("input[name='" + key + "']").attr('checked', value);
		if($('select#' + domID)[0]) $('select#' + domID).val(value);
	 },

// helper function for disabling form fields
	toggleDisable: function (array, p = 0) {

	 	_.each(array, function(e) {
	 		if(p == 1) $(e).removeAttr('disabled', 'disabled');
	 		else $(e).attr('disabled', 'disabled');
	 	});
	},

// when image is selected as type makes the image tab available, disables the narration tab, changes the form
// field names for summary fields so that $POST data has the right names for the database
	toggleImageFields: function() {

		getID = $("input[name='id']").val();

		if($('select#image').val() !== 'none') {

			$('textarea#summary').attr('name', 'img_description');
			$('textarea#notes').attr('name', 'img_notes');
			$('textarea#research-notes').attr('name', 'img_research_notes');

			$('ul#form-tabs li#img').removeClass('disabled');
			$('ul#form-tabs li#narr').addClass('disabled');
			$("input[name='id']").val(0);
			$("input[name='img_id']").val(getID);

			losForm.imageArticleFields();

		} else {

			$('textarea#summary').attr('name', 'summary');
			$('textarea#notes').attr('name', 'notes');
			$('textarea#research-notes').attr('name', 'research_notes');

			$('ul#form-tabs li#img').addClass('disabled');
			$('ul#form-tabs li#narr').removeClass('disabled');
			$("input[name='id']").val(getID);
			$("input[name='img_id']").val(0);

			losForm.imageArticleFields(1);
		}
	},

// helper function appends or clears input for the article-synced image fields
	 imageArticleFields: function(p = 0) {

		fields = ['volume', 'issue', 'date-published'];

		function fillImageFields(e){
			value = (p == 0) ? $('input#' + e).val() : '';
			e = (e === 'date-published') ? 'date' : e;
			$('input#img-' + e).val(value);
		}

		_.each(fields, function(e) {
			fillImageFields(e);
			$('input#' + e).change(function(){ fillImageFields(e); })
		});
	 },

// for an image, if article-related is selected, populates the image data fields with the appropriate article level values
// and repopulates them if the fields change
	imageArticleCheck: function() {

		disableArray = ['input#title', 'input#author', 'input#location', 'input#page-start', 
						'input#page-end', 'input#volume', 'input#issue', 'input#date-published'];

		function imageSelectCheck() {
			if($('select#image').val() !== 'none') {

				losForm.toggleImageFields();
				losForm.imageArticleFields(0);
				losForm.toggleDisable(disableArray, 1);
		 		
		 		if ($('select#image').val() === 'freestanding') {
		 			losForm.toggleDisable(disableArray, 0);
		 		}
	 		} else {
	 			losForm.toggleImageFields();
 				losForm.imageArticleFields(1);
			}
		}

		imageSelectCheck();	
	 	$('select#image').change(function() {
			imageSelectCheck();
	 	});
	 },

// helper function for returning a nicely formatted array for different purposes from data
	makeArray: function(object, filter, column, p) {

		p = (typeof p !== 'undefined') ? p : '';

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
	},

// on ajax success appends Articles table json to form fields
	appendArticle: function(id, img = 0) {

		function fillFields(e){  

			article = e[0];
			d = article.date_published.split('-');
			article.date_published = d[1] + '-' + d[0];

			article.type = article.type.charAt(0).toUpperCase() + article.type.substr(1); 
			$('input#type').select2('val', [article.type]);

			recMessage = (article.reconciled == 1) ? "<span style='color: #5cb85c'><em>yes</em></span>" : "<span style='color: #428bca;'><em>nope</em></span>";
			$('label#reconciled').append(recMessage);

			_.each(_.keys(article), function(key){
				losForm.appendInput(key, article[key]);
			});
			losForm.submitCheck(losForm.inputIDs);
		}

		if(img == 1) {

			$.getJSON('../includes/json.php?p=img_article&id=' + id + '&img=1', function(data) {
				id = data[0]['article_id'];

				if(id){
					$.getJSON('../includes/json.php?p=element&id=' + id, function(data) {
						fillFields(data);
						$('select#image').val('attached');
					});
				} else {
					$('select#image').val('freestanding');
					losForm.imageArticleCheck(1);
				}
				losForm.toggleImageFields();
				$('select#image').attr('disabled','disabled');
			});
		} else { 
			$.getJSON('../includes/json.php?p=element&id=' + id, function(data) {
			fillFields(data);
		});
		}
	},

// on ajax success appends Image table json to form fields
	appendImage: function(id){

		$.getJSON('../includes/json.php?p=element&id=' + id + '&img=1', function(data) {
			image = data[0];
			_.each(_.keys(image), function(key) {
			losForm.appendInput(key, image[key]);
			});
		});
	},

// on ajax success appends Review table json to form fields	
	appendReview: function(id, id2, img = 0) {

		imgParam = (img == 1) ? '&img=1' : '';

		id2 = (typeof id2 !== 'undefined') ? id2 : '';
		idParam = '&rid=' + id2;

		$.getJSON('../includes/json.php?p=review&id=' + id + idParam + imgParam, function(data) {
			review = data[0];
			_.each(_.keys(review), function(key) {
				losForm.appendInput(key, review[key]);
			})
			$("input[name='timestamp']").val(review['timestamp']);
		});
	},

// same as above but for reconciled reviews
	appendRecReviews: function(id1, id2, id3 = 0) {

		id3 = (typeof id3 !== 'undefined') ? id3 : '';
		idParam = (id3) ? '&rid=' + id3 : '';

	// on ajax success gets Reviews data for two reviews and appends them to DOM elements and the values for input fields
		$.getJSON('../includes/json.php?p=review&id=' + id1 + idParam, function(data){

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
	},

// on ajax success appends Articles_Themes table json to form fields
	appendThemes: function(id, id2 = 0, img = 0) {

		imgParam = (img == 1) ? '&img=1' : '';
		idParam = (id2 != 0) ? '&rid=' + id2 : '';

		$.getJSON('../includes/json.php?p=themes&id=' + id + idParam + imgParam, function(data) {
			articleThemes = _.pluck(data, 'theme');
			$('input#themes').select2('val', [articleThemes]);
			mainThemes = _.filter(data, function(e) { return e.if_main == 1; })
			mainThemes = _.pluck(mainThemes, 'theme');
			// for the main input, adds the prefix Theme
			mainThemes = _.map(mainThemes, function(e) { return 'Theme: ' + e; });
			mainList = $('input#main').select2('val');
			mainList = mainList.concat(mainThemes);
			$('input#main').select2('val', [mainList]);
		}); 
	},

// same as above but for reconciled reviews
	appendRecThemes: function(id1, id2, id3 = 0) {

		id3 = (id3 != 0) ? id3 : '';
		// on ajax success gets Articles_Themes data for two reviews and appends them to DOM elements and updates values for input fields 

		$.getJSON('../includes/json.php?p=themes&id=' + id1 + '&rid=' + id3, function(data){

			review1themes = data;
									
			$.getJSON('../includes/json.php?p=themes&id=' + id1 + '&rid=' + id2, function(data){
				
				review2themes = data;

				review1themesMain = losForm.makeArray(review1themes, '1', 'if_main', 'maintheme');
				review2themesMain = losForm.makeArray(review2themes, '1', 'if_main', 'maintheme');

				if(id3.length == 0){

					sharedThemesMain = _.intersection(review1themesMain, review2themesMain);
					review1themesMain = _.difference(review1themesMain, sharedThemesMain);
					review2themesMain = _.difference(review2themesMain, sharedThemesMain);
				
					mainList = $('input#main').select2('val');
					mainList = mainList.concat(sharedThemesMain);
					// adds shared main theme values to main input field
					$('input#main').select2('val', [mainList]);
				}
				
				// append reviewer main theme values to unordered lists				
				$('#main-review-1 ul').append("<li>" + review1themesMain.join("</li><li>"));
				$('#main-review-2 ul').append("<li>" + review2themesMain.join("</li><li>"));

				// losForm.recListsAddOnClick($('#main-review-1 ul li'), 'main');
				// losForm.recListsAddOnClick($('#main-review-2 ul li'), 'main');

				review1themes = _.pluck(review1themes, 'theme');
				review2themes = _.pluck(review2themes, 'theme');

				if(id3.length == 0){

					sharedThemes = _.intersection(review1themes, review2themes);
	
					review1themes = _.difference(review1themes, sharedThemes);
					review2themes = _.difference(review2themes, sharedThemes);
					// adds shared theme values to main input field
					$('input#themes').select2('val', [sharedThemes]);
				}

				// append reviewer theme values to unordered lists								
				$('#themes-review-1 ul').append("<li>" + review1themes.join("</li><li>"));
				$('#themes-review-2 ul').append("<li>" + review2themes.join("</li><li>"));

				losForm.recListsAddOnClick($('#themes-review-1 ul li'), 'themes');				
				losForm.recListsAddOnClick($('#themes-review-2 ul li'), 'themes');							
			});
		});
	},

// on ajax success appends Article_Tags table json to form fields
	appendTags: function(id, id2 = 0, img = 0) {

		imgParam = (img == 1) ? '&img=1' : '';
		idParam = (id2 != 0) ? '&rid=' + id2 : '';
		
		$.getJSON('../includes/json.php?p=tags&id=' + id + idParam + imgParam, function(data) {					
			tags = data;
			_.each(losForm.categories, function(category) {
				tagsCategory = losForm.makeArray(tags, category, 'category', 'tag');
				$('input#' + category).select2('val', [tagsCategory]);
			});

			mainTags = losForm.makeArray(tags, '1', 'if_main', 'maintag');

			mainList = $('input#main').select2('val');
			mainList = mainList.concat(mainTags);

			$('input#main').select2('val', [mainList]);
		});
	},

// same as above but for reconciled reviews
	appendRecTags: function(id1, id2, id3 = 0) {
	
		id3 = (id3 != 0) ? id3 : '';

		// on ajax success gets Articles_Tags data for two reviews and appends them to DOM elements and the values for input fields
		$.getJSON('../includes/json.php?p=tags&id=' + id1 + '&rid=' + id3, function(data){

			review1tags = data;
				
			$.getJSON('../includes/json.php?p=tags&id=' + id1 + '&rid=' + id2, function(data){

				review2tags = data;

				_.each(losForm.categories, function(category){

					domID = category.replace('_','-');

					review1tagsByCategory = losForm.makeArray(review1tags, category, 'category', 'tag');
					review2tagsByCategory = losForm.makeArray(review2tags, category, 'category', 'tag');

					if(id3.length == 0){
						sharedTagsByCategory = _.intersection(review1tagsByCategory, review2tagsByCategory);
						review1tagsByCategory = _.difference(review1tagsByCategory, sharedTagsByCategory);
						review2tagsByCategory = _.difference(review2tagsByCategory, sharedTagsByCategory);

						// adds shared tag values to appropriate tag category input fields
						$('input#' + domID).select2('val', [sharedTagsByCategory]);
					}

				// append reviewer tags to unordered lists
					$('#' + domID + '-review-1 ul').append("<li>" + review1tagsByCategory.join("</li><li>"));
					$('#' + domID + '-review-2 ul').append("<li>" + review2tagsByCategory.join("</li><li>"));										

					losForm.recListsAddOnClick($('#' + domID + '-review-1 ul li'), domID);
					losForm.recListsAddOnClick($('#' + domID + '-review-2 ul li'), domID);

				});

				review1tagsMain = losForm.makeArray(review1tags, '1', 'if_main', 'maintag');
				review2tagsMain = losForm.makeArray(review2tags, '1', 'if_main', 'maintag');

				if(id3.length == 0){
					sharedTagsMain = _.intersection(review1tagsMain, review2tagsMain);
					review1tagsMain = _.difference(review1tagsMain, sharedTagsMain);
					review2tagsMain = _.difference(review2tagsMain, sharedTagsMain);
					// adds shared main tag values to main input field
					$('input#main').select2('val', [sharedTagsMain]);
				}

				// append reviewer main tag values to unordered lists
				$('#main-review-1 ul').append("<li>" + review1tagsMain.join("</li><li>"));
				$('#main-review-2 ul').append("<li>" + review2tagsMain.join("</li><li>"));
			});
		});
	},

// when the input#main select2 is clicked, appends values from what is currently entered into the tag category & themes fields
	appendMain: function() {

		$('#s2id_main ul.select2-choices').click(function() {
				$('input#main').select2({
					tags: function(){ 

						mainTags = [];

						_.each(losForm.categories, function(category) {

							domID = category.replace('_', '-');
							categoryTags = $('input#' + domID).select2('val');
							categoryTags = _.chain(categoryTags)
								.map(function(e) { return category.charAt(0).toUpperCase() + category.substr(1) + ': ' + e; })
								.value();
							mainTags = mainTags.concat(categoryTags);
						});

						mainTags = mainTags.concat(_.map($('input#themes').select2('val'), function(e) { return 'Theme: ' + e;}));
						return mainTags;	
					}
				});
			;})
	},

	appendInitials: function(id1, id2) {
	
		idParam = (id2) ? '&rid=' + id1 : '';
		id = (id2) ? id2 : id1;
		
		
		// gets reviewer initials and adds them to DOM elements
		$.getJSON('../includes/json.php?p=reviewer' + idParam, function(data) {
			reviewer1 = data[0].initials;

			$.getJSON('../includes/json.php?p=reviewer&rid=' + id, function(data){
					
				reviewer2 = data[0].initials;
				$('#narration-pov-review-1 h5').html(reviewer1);
				$('#narration-pov-review-2 h5').html(reviewer2);
				$('.one li.reviewer').html(reviewer1);
				$('.two li.reviewer').html(reviewer2);

			});
		});
	},

	recListsAddOnClick: function(obj,domID) {

			$(obj).css('cursor','pointer')			
				.click(function(e) {				
					tagList = $('input#' + domID).select2('val');
					tagList.push(e.target.innerHTML);
					$(this).css('background', '#ddd');
					$('input#' + domID).select2('val', tagList);				
				});
	},

	prepare: function(id, img) {

		losForm.formValidation();
		losForm.typeList();
		losForm.imageLists();

		losForm.tagsLists(id,img);
		losForm.themesList(id,img);
		losForm.mainList();
		losForm.appendMain();
		$('input#type').change(function() {	losForm.toggleImageFields(this); })
		losForm.imageArticleCheck();
	},

// edit view : appends all data from an existing review (Articles, Review, Articles_Tags, Articles_Themes)
	editReview: function(id, img = 0) {

		losForm.prepare(id,img);

		losForm.appendArticle(id,img);
		losForm.appendReview(id,img);
		if(img == 1) losForm.appendImage(id);
	},

// add review view : appends data to the form from the last review by the current reviewer
	lastReview: function() {

		losForm.prepare();

		$.getJSON('../includes/json.php?p=last', function(data) {

			lastReview = data[0];
			losForm.appendInput('issue', lastReview.issue);
			losForm.appendInput('volume', lastReview.volume);
			d = lastReview.date_published.split('-');
			d = d[1] + '-' + d[0];
			losForm.appendInput('date_published', d);
			losForm.submitCheck(losForm.inputIDs);
		})

		losForm.appendImage;
	},
	// reconcile view : a kind of heinous number of lines to append data to the form from two existing reviews (Articles, Review, Articles_Tags, Articles_Themes)
	reconcileReview: function(id1, id2) {

		losForm.prepare();

		losForm.appendArticle(id1);
 		losForm.appendInitials(id2);
 
 		losForm.appendRecReviews(id1,id2);
 		losForm.appendRecTags(id1,id2);
 		losForm.appendRecThemes(id1,id2);
	},

	editReconciled: function(id1,id2,id3) {

		losForm.prepare();
		
		losForm.appendArticle(id1);
		losForm.appendReview(id1,9);
		losForm.appendThemes(id1,9);
		losForm.appendTags(id1,9);

 		losForm.appendInitials(id2,id3);
 		losForm.appendRecReviews(id1,id2,id3);
 		losForm.appendRecTags(id1,id2,id3);
 		losForm.appendRecThemes(id1,id2,id3);
	}
}

// var losTable = {

// 	draw: function(){

// 		$.getJSON('../includes/json.php?p=test_table', function(data){

// 			$('table#data-table').add('tr');
// 		})
// 	}

// }