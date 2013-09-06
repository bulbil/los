
var losFormViews = {

	themesList: function() { 

		$.getJSON('../includes/json.php?p=themes_list', function(data) {

			var themes = [];
			$.each(data, function(key,value) {
				themes.push('<option>' + value.theme + '</option>');
			})

			$('select#themes-list').append(themes.join(''));
		})
	},

	appendArticle: function() { },
	appendReview: function() { },
	appendThemes: function() { },
	appendTags: function() { },
	
	editReview: function(id) { 

		$.getJSON('../includes/json.php?p=article&id=' + id, function(data) {

			var article = {};
			i = 0;
			$.each(data, function(key, value) {
				article[key] = value;
				console.log(article);
			})
		});

		$.getJSON('../includes/json.php?p=review&id=' + id, function(data) {

			var review = {};
			$.each(data, function(key, value) {
				review[key] = value;
				console.log(review);
			})
		});

		$.getJSON('../includes/json.php?p=themes&id=' + id, function(data) {

			var themes = {};
			$.each(data, function(key, value) {
				themes[key] = value;
			});
			console.log(themes);
		}); 

		$.getJSON('../includes/json.php?p=tags&id=' + id, function(data) {

			var tags = {};
			$.each(data, function(key, value) {
				tags[key] = value;
			}) 
			console.log(tags);
		});
	},

	lastReview: function() {

			$.getJSON('../includes/json.php?p=last', function(data) {

				var last = {};
				$.each(data, function(key, value) {
					last[key] = value;
				})
				console.log(last);
			})
	},

	dataTable: function() { }

}