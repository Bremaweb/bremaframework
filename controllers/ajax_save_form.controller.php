<?php
global $user;
$user->authenticate();

define("NO_HEADER",true);
define("NO_FOOTER",true);

$form_id = $_POST['guid'];
$retVal = bremaSaveForm($form_id);

if ( $retVal['success'] == 1 )
	array_unshift($retVal['message'],"Saved Successfully!");

$retVal['message'] = implode("<br />",$retVal['message']);

echo json_encode($retVal);
exit;
?>