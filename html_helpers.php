<?php
/* several html helper functions */


function html_select($aryParams) {

	$selName = $aryParams['name'];
	$attributes = $aryParams['attributes'];
	$selected = $aryParams['selected'];
	$options = $aryParams['options'];

	$retVal = "<select name=\"$selName\" ";

	if ( is_array($attributes) ) {
		if ( !array_key_exists("id", $attributes) )
			$retVal .= "id=\"$selName\" ";
	} else {
		$retVal .= "id=\"$selName\" ";
	}
	if ( is_array($attributes) ) {
		foreach ( $attributes as $key => $v ) {
			$retVal .= $key . "=\"$v\" ";
		}
	}

	$retVal .= ">\r\n";

	if ( is_array($options) ) {
		foreach ( $options as $o ) {
			$attribs = "";
			if ( is_array($selected) ) {
				if ( in_array($o['value'], $selected) )
					$sel = " SELECTED";
				else
					$sel = "";
			} else {
				if ( strval($o['value']) === strval($selected) ) {
					$sel = " SELECTED";
				} else {
					$sel = "";
				}
			}

			if ( is_array($o['attrib']) ) {
				foreach ( $o['attrib'] as $kk => $vv )
					$attribs .= " $kk=\"$vv\"";
			}
			$retVal .= "<option value=\"" . $o['value'] . "\"" . $sel . " " . $attribs . ">" . $o['text'] . "</option>\r\n";
		}
	}

	$retVal .= "</select>\r\n";

	return $retVal;
}

function spinner(){
echo <<<SPINNER
    <div class="spinner">
  <div class="bounce1"></div>
  <div class="bounce2"></div>
  <div class="bounce3"></div>
</div>	
SPINNER;
}


?>
