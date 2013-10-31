<div class='container'>
<div class='row'>
<div class="col-md-6 col-md-offset-3">
<?php
// a beastly thing -- this is all the logic for whether to render a confirm form or not before
// database changes

$form = (isset($_POST['form'])) ? $_POST['form'] : $_SESSION['form_data']['form'];
// moves the $_POST stuff to $_SESSION so we can use it after the confirm form
$form_data = (isset($_SESSION['form_data'])) ? $_SESSION['form_data'] : $_POST;
$if_image = ($form_data['img_association'] == 'none') ? false : true;

// the main thing is comparing article ids and fields from $POST data and the db
// runs the comparison twice if an image and article form data are present
if(!isset($_SESSION['confirm'])) {

	if(!$if_image) compare_fields();
	elseif ($form_data['img_association'] == 'freestanding') compare_fields('',$if_image);
	else { compare_fields(); compare_fields($_SESSION['confirm'], $if_image);}
}

// sets the session confirm status based on successful submission of the confirm edit form
if(isset($_POST['confirm'])) $_SESSION['confirm'] = $_POST['confirm'];

$confirm = $_SESSION['confirm'];

switch ($confirm) {

	case 0: // 0 : adding a new article/image and new review/themes/tags
	case 1: // 1 : db data and $POST data are identical
	case 2: // 2 : freestanding image / no associated article
			echo_line($form. ' ' . $confirm. ' ' . $if_image); 
			edit_tables($form_data, $form, $confirm, $if_image);
			echo return_alert($form);
			unset_session_vars();
			break;

	case 3: // 3 : changes need to be confirmed
	case 4: // 4 : being associated with a new article or image
			if($form_data['img_association'] != 'freestanding') echo render_confirm_form($confirm);
			echo render_confirm_form($confirm, $if_image, 'submit');
			include "../html/footer.html";
			exit();

	case 5:	// 5 : error because id from the db and from $POST data don't match -- this could cause all kinds of trouble otherwise
			$html = "<div class = 'row'><h4 class='alert-danger'><em>ERROR !</em><br /><br />I'm sorry Dave, I'm afraid I can't do that.
			<br />Please check your bibliographic or image data.</h4><br />
			<input type='button' class='btn btn-danger' onclick='window.history.back()' value='return to editing' /></div>";
			echo $html;
			unset_session_vars();
			break;
	case 6: // 6 : update based on $POST data from confirm form
			edit_tables($form_data, $form, $confirm, $if_image);
			echo return_alert($form);
			unset_session_vars();
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
function compare_fields($confirm = '', $if_image = false) {
	
	echo_line('compare fields ' . $if_image);
	$table = (!$if_image) ? 'Articles' : 'Images';
	$id = (!$if_image) ? 'article_id' : 'img_id';	
	$columns = (!$if_image) ? $GLOBALS['articles'] : $GLOBALS['images'];
	
	// get rid of the reconciled field from the array
	unset($columns[9]);
	// the article id column just makes things unnecessarily complicated
	if($if_image) unset($columns[0]);

	// save the $POST data to insert it after the confirmation form
	$_SESSION['form_data'] = $_POST;

	try {

		$dbh = db_connect();

		// returns an article/image id base on the page_start / page_end / volume / issue or img_volume / img_issue / img_page / img_placement
		// what is used for comparison is easily changed in utilities by modifying to the
		// article_check or image_check arrays ...		
		$db_id = return_element_id($_POST, $dbh, $if_image);

		// if no article id in the db, add an article + add review / themes / tags
		if(!$db_id && $_POST['form'] == 'add') {

			if($_POST['img_association'] == 'freestanding') $_SESSION['confirm'] = 2;
			// even if adding a new attached image, you want to confirm any changes to the associated article
			if($_POST['img_association'] == 'attached') $_SESSION['confirm'] = 3;
			else $_SESSION['confirm'] = 0;
		}

		// if there should be an article id in the db, throw an error ... otherwise big trouble
		elseif(!$db_id) { $_SESSION['confirm'] = 5;}
		
		else {

			$post_id = (!$if_image) ? $_POST['id'] : $_POST['img_id'];
			// gets an array of db data for the article to compare with the $POST data (plus some normalizing)
			$db_data = return_row($columns, array($db_id), array($id), $table, $dbh);
			
			if(!$if_image){
				$db_data['type'] = ucwords($db_data['type']);
				$db_data['date_published'] = string_format($db_data['date_published'], 'date_check');
			}else{
				$db_data['img_type'] = ucwords($db_data['img_type']);
				$db_data['img_date'] = string_format($db_data['img_date'], 'date_check');
				if(isset($db_data['date_published'])) $db_data['date_published'] = string_format($db_data['date_published'], 'date_check');
			}
			// compares the db array to the post array
			$diff = array_diff_assoc($db_data, $_POST);
			$if_same = (empty($diff)) ? 1 : 0;

			// have to add the db id after comparison -- isn't in the original array
			if(!$if_image) $db_data['id'] = $db_id;
			else $db_data['img_id'] = $db_id;

			// save a copy of db_data for the confirm form
			if(!$if_image) $_SESSION['db_article'] = $db_data;
			else $_SESSION['db_image'] = $db_data;

			// makes sure the $POST form id and the id from the db match
			if($post_id == $db_id || $_POST['form'] == 'add') {

				// if there are no differences between the two arrays, skip the confirmation form
				if ($confirm == 3 || $confirm == 4) $_SESSION['confirm'] = 3;
				else $_SESSION['confirm'] = ($if_same) ? 1 : 3;
			// in the hopefully rare case that the id from the db and the $POST form don't match, throw
			// an error -- except if trying to add an existing review to
			// to a different, existing article 
			} else { 
				$_SESSION['confirm'] = ($_POST['form'] == 'edit') ? 4 : 5; 
			}
		}
	} catch(PDOException $e) { echo $e->getMessage(); }
}

// renders a form to confirm edits to the article level bibliographic data

// helper function for the rendering
function form_html($str1, $str2, $str3) {

		$html = "<div class='row'>";
		$html .= "<div class='form-group col-md-6'>";
		$html .= "<label for='$str3' id='$str3'>" . ucwords($str3) . ": " . $str1 . "</label>";
		$html .= "<input type='text' class='form-control' id='$str3' name='$str3' value=" . '"' . $str2 . '">';
		$html .= "</div></div>";
		
		return $html;
}

function render_confirm_form($int, $if_image = false, $p = '') {

	$element = (!$if_image) ? 'article' : 'image';	
	$columns = (!$if_image) ? $GLOBALS['articles'] : $GLOBALS['images'];
	
	if($if_image) unset($columns[0]);
	unset($columns[9]);
	
	$id = (!$if_image) ? 'db_article' : 'db_image';
	$row = $_SESSION[$id];

	$html = "<form action='submit-form.php' method='post'>";	
	$html .= "<div class='row'><h4>";
	if(!$p){ $html .= ($int == 4) ? "Please confirm that you intend to associate this review with a different $element."
						: "Please confirm your edits.";
			$html .= "<br /><br />Values that appear in bold will be overwritten.</h4></div>";
	}

	$form = '';
	foreach($columns as $column){
				
		$old_value = $row[$column];
		$new_value = $_POST[$column];

		if($int == 4) $form .= form_html($old_value,$new_value,$column);
		else $form .= ($old_value != $new_value) ? form_html($old_value,$new_value,$column) : '';
	}

	$html .= (strlen($form) > 0) ? $form : '';

	// only renders submit button if submit parameter is given
	if($p == 'submit') {
		$html .= "<input type='hidden' name='confirm' value='6'>";
		$html .= "<div class = 'row'><input type='submit' class='btn btn-warning' value='confirm'>";
		$html .= "<input class='btn btn-primary col-md-offset-1' onclick='window.history.back()' value='return to editing'></div></div></form>";			
	}
	return $html;
}