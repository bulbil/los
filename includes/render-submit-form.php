<div class='container'>
<div class='row'>
<div class="col-md-6 col-md-offset-3">
<?php
// the main thing is comparing article ids and fields from $POST data and the db
if(!isset($_SESSION['confirm'])) compare_fields();

// sets the session confirm status based on successful submission of the confirm edit form
if(isset($_POST['confirm'])) $_SESSION['confirm'] = $_POST['confirm'];

// sets some variables depending on the state
$confirm = (isset($_SESSION['confirm'])) ? $_SESSION['confirm'] : '';
$form = (isset($_POST['form'])) ? $_POST['form'] : $_SESSION['form_data']['form'];
$form_data = (isset($_SESSION['form_data'])) ? $_SESSION['form_data'] : $_POST;

switch ($confirm) {

	case 0: // 0 : adding a new article and new review/themes/tags
	case 1: // 1 : db data and $POST data are identical
			edit_tables($form_data, $form, $confirm);
			echo return_alert($form);
			unset($_SESSION['confirm']);
			break;

	case 2: // 2 : changes need to be confirmed
	case 3: // 3 : being associated with a new article
			echo render_confirm_form($confirm);
			include "../html/footer.html";
			exit();

	case 4:	// 4 : error because id from the db and from $POST data don't match -- this could cause all kinds of trouble otherwise
			$html = "<div class = 'row'><h4 class='alert-danger'><em>ERROR !</em><br /><br />I'm sorry Dave, I'm afraid I can't do that.
			<br />Please check your bibliographic data.</h4><br />
			<input type='button' class='btn btn-danger' onclick='window.history.back()' value='return to editing' /></div>";
			echo $html;
			unset($_SESSION['confirm']);
			break;
	case 5: // 5 : update based on $POST data from confirm form
			foreach($_POST as $key=>$value) $form_data[$key] = $_POST[$key];
			edit_tables($form_data, $form, $confirm);
			echo return_alert($form);
			break;
	}

// returns an alert after a successful insert or update into the db
function return_alert($str) {

	switch ($str) {
		case 'add': return "<div class='alert alert-success'>Nice One! You've successfully added a new review</div>"; break;
		case 'reconcile': return "<div class='alert alert-info'>Nice One! You've successfully added a reconciled review</div>"; break;
		case 'edit': return "<div class='alert alert-warning'>Nice One! You've successfully updated an existing review</div>"; break;
		case 'recedit': return "<div class='alert alert-warning'>Nice One! You've successfully updated an existing reconciled review</div>"; break;
	}
}

// checks to see if either the article id is provided or, if not, whether 
// the page start + end, volume, issue correspond to an existing article in the db
function compare_fields() {
	// get rid of the reconciled field from the array	
	$articles = $GLOBALS['articles'];
	$images = $GLOBALS['images'];

	unset($articles[9]);

	// save the $POST data to insert it after the confirmation form
	$_SESSION['form_data'] = $_POST;
	try {

		$dbh = db_connect();
		
		$post_id = $_POST['id'];
		// returns an article id base on the page_start / page_end / volume / issue		
		$db_id = return_article_id($_POST, $dbh);
		// if no article id in the db, add an article + add review / themes / tags
		if(!$db_id && $_POST['form'] == 'add') { $_SESSION['confirm'] = 0; }
		// if there should be an article id in the db, throw an error
		elseif(!$db_id) { $_SESSION['confirm'] = 4;}
		
		else {
			// gets an array of db data for the article to compare with the $POST data (plus some normalizing)
			$db_data = return_row($articles, array($db_id), array('article_id'), 'Articles', $dbh);
			$db_data['type'] = ucwords($db_data['type']);
			$db_data['date_published'] = string_format($db_data['date_published'], 'date_check');

			// compares the db array to the post array
			$articles_diff = array_diff_assoc($db_data, $_POST);
			$if_same = (empty($articles_diff)) ? 1 : 0; 
			
			// have to add the db id after comparison -- isn't in the original array
			$db_data['id'] = $db_id;
			// save a copy of db_data for the confirm form
			$_SESSION['db_data'] = $db_data;

			// makes sure the $POST form id and the id from the db match
			if($post_id == $db_id || $_POST['form'] == 'add') {

				// if there are no differences between the two arrays, skip the confirmation form
				$_SESSION['confirm'] = ($if_same) ? 1 : 2;

			// in the hopefully rare case that the id from the db and the $POST form don't match, throw
			// an error -- except if trying to add an existing review to
			// to a different, existing article 
			} else { 
				$_SESSION['confirm'] = ($_POST['form'] == 'edit') ? 3 : 4; 
			}
		}
	} catch(PDOException $e) { echo $e->getMessage(); }
}

// renders a form to confirm edits to the article level bibliographic data
function render_confirm_form($int) {
	
	$articles = $GLOBALS['articles'];
	unset($articles[9]);

	function render_compare_form($str1, $str2, $str3) {
	
			$html = "<div class='row'>";
			$html .= "<div class='form-group col-md-6'>";
			$html .= "<label for='$str3' id='$str3'>" . ucwords($str3) . ": " . $str1 . "</label>";
			$html .= "<input type='text' class='form-control' id='$str3' name='$str3' value=" . '"' . $str2 . '">';
			$html .= "</div></div>";
			
			return $html;
	}

	$html = "<form action='submit-form.php' method='post'>";	
	$html .= "<div class='row'><h4>";
	$html .= ($int == 3) ? "Please confirm that you intend to associate this review with a different article."
						: "Please confirm that you intend to edit an article's bibliographic information.";
	$html .= "<br /><br />Values that appear in bold will be overwritten.</h4></div>";
	
	foreach($articles as $column){
	
		$old_value = $_SESSION['db_data'][$column];
		$new_value = $_POST[$column];

		if($int == 3) $html .= render_compare_form($old_value,$new_value,$column);
		else $html .= ($old_value != $new_value) ? render_compare_form($old_value,$new_value,$column) : '';
	}

	$html .= "<input type='hidden' name='confirm' value='5'>";
	if($int == 3) $html .=  "<input type='hidden' name='reassociate_id' value='" . $_SESSION['db_data']['id'] . "'>";
	$html .= "<div class = 'row'><input type='submit' class='btn btn-warning' value='confirm'>";
	$html .= "<input class='btn btn-primary col-md-offset-1' onclick='window.history.back()' value='return to editing'></div></div></form>";
	
	return $html;
}