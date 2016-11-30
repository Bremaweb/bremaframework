<?php
// debug levels
// 1 - important
// 2 - some trace
// 3 - verbose (constructors, method calls)
// 4 - all of the above + SQL queries

function debugLog($text,$level = 1){

	if ( DEBUG >= $level ){
		//if ( is_array($text) ){
		//	ksort($text);
			ob_start();
				print_r($text);
			$buff = ob_get_clean();

			$text = $buff;
		//}

		//if ( !$h )
			//echo "Unable to open file handle: " . $appRoot . "logs\debug_" . date("m-d-Y") . ".txt";

		$text = "[" . date("m/d/Y h:ia") . "] " . $text;

			if ( !file_exists(APP_DIR . "/logs") )
				mkdir(APP_DIR . "/logs");

			$h = fopen(APP_DIR . "/logs/debug_" . date("m-d-Y") . ".txt","a+");
			fwrite($h, ($text . "\r\n") );
			fclose($h);

		if ($_SERVER['HTTP_HOST'] == ""){
			echo $text . "\r\n";
		}
	}


}

function GUID()
{
	if (function_exists('com_create_guid') === true)
	{
		return trim(com_create_guid(), '{}');
	}

	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function getMailer(){

}

function cleanURL($str) {
	$clean = preg_replace("/[^a-zA-Z0-9 ]/", '', $str);
	$clean = strtolower($clean);
	$clean = str_replace(array("  "," "),array(" ","-"),$clean);

	return $clean;
}

function uniqueValue($table,$field,$value){
	global $db;
	$SQL = "SELECT $field FROM $table WHERE $field = '" . $db->escape($value) . "'";
	$r = $db->query($SQL);
	return ( $db->numrows($r) > 0 ? false : true );
}

function bremaSaveForm($form_id){
$retVal = array();
$message = array();
	if(PFBC\Form::isValid($form_id)) {
		$form = PFBC\Form::recover($form_id);
		$cl = $form->getAttribute("model");
		$model = new $cl();
		$table = $model->getTable();
		if ( $_REQUEST['id'] != "" ){
			$model->load($_REQUEST['id']);
		} else {
			$model->dt_created = time();
		}

		if ( $_REQUEST['password1'] != $_REQUEST['password2'] ){
			$retVal['success'] = 0;
			$message[] = "Passwords did not match! No changes were saved!";
			$retVal['message'] = $message;
			PFBC\Form::clearErrors($form_id);
			PFBC\Form::clearValues($form_id);
			return $retVal;
		} else {
			if ( $_REQUEST['password1'] != "" && $_REQUEST['password2'] != "" ){
				if ( $model->getPasswordField() != "" ){
					$pf = $model->getPasswordField();
					$model->$pf = md5($_REQUEST['password1']);
					$message[] = "Password updated successfully!";
				}
			}
		}

		$model->arraySet($_REQUEST["{$table}"]);

		if ( $model->save() ){
			$retVal['success'] = 1;
			$retVal['id'] = $model->id;
		} else {
			$retVal['success'] = 0;
			$message[] = "There was an error saving the information to the database";
		}
	} else {
		$retVal['success'] = 0;
		$message[] = "<strong>There was an error validating the form</strong>";
		//print_r($_SESSION["pfbc"][$form_id]["errors"]);
		$errors = $_SESSION["pfbc"][$form_id]["errors"];
		foreach ( $errors as $ferrors )
			$message[] = $ferrors[0];
	}
	$retVal['message'] = $message;
	PFBC\Form::clearErrors($form_id);
	PFBC\Form::clearValues($form_id);
	return $retVal;
}

function bremaSaveTableForm($form_id){

}

/* http://stackoverflow.com/questions/1416697/converting-timestamp-to-time-ago-in-php-e-g-1-day-ago-2-days-ago */
function time_elapsed_string($datetime, $full = false) {
	$now = new DateTime;

	if ( is_numeric($datetime) )
		$datetime = date("c",$datetime);

	$ago = new DateTime($datetime);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;

	$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
	);
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}

?>