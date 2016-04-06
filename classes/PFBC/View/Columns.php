<?php
namespace PFBC\View;

class Columns extends \PFBC\View {
	protected $class = "form-horizontal";
	public $columns;

	public function render() {
		$this->_form->appendAttribute("class", $this->class);

		echo '<form', $this->_form->getAttributes(), '><fieldset>';
		$this->_form->getErrorView()->render();

		$elements = $this->_form->getElements();
		$elementSize = sizeof($elements);
		$elementCount = 0;
		$_columns = $this->columns;
		$currentColumn = 0;
		$columnCounter = 0;
		// start the first column
		echo "<div class=\"container\">";
			echo "<div class=\"row\">";
				echo "<div class=\"" . $_columns[$currentColumn]['class'] . "\">";
		for($e = 0; $e < $elementSize; ++$e) {
			$element = $elements[$e];

			if($element instanceof \PFBC\Element\Hidden || $element instanceof \PFBC\Element\HTML)
				$element->render();
            elseif($element instanceof \PFBC\Element\Button) {
                if($e == 0 || !$elements[($e - 1)] instanceof \PFBC\Element\Button)
					echo '<div class="form-group">';
				else
					echo ' ';

				$element->render();

                if(($e + 1) == $elementSize || !$elements[($e + 1)] instanceof \PFBC\Element\Button)
                    echo '</div>';
            }
            else {
            	$columnCounter++;
            	if ( $columnCounter > $_columns[$currentColumn]['count'] ){
            		echo "</div>"; // close the current column
            		$currentColumn++;

            		if ( $_columns[$currentColumn]['spacer'] == 1 ){
            			echo "<div class=\"" . $_columns[$currentColumn]['class'] . "\">";
            				echo "&nbsp;";
            			echo "</div>";
            			$currentColumn++;
            		}

            		echo "<div class=\"" . $_columns[$currentColumn]['class'] . "\">";	// open the new column
            		$columnCounter = 0;

            	}
				echo '<div class="form-group" id="element_', $element->getAttribute('id'), '">', $this->renderLabel($element), '<div class="controls">', $element->render(), $this->renderDescriptions($element), '</div></div>';
				++$elementCount;
			}
		}
				echo "</div>";
			echo "</div>";
		echo "</div>";
		echo '</fieldset></form>';
    }

	protected function renderLabel(\PFBC\Element $element) {
        $label = $element->getLabel();
        if(!empty($label)) {
			echo '<label class="control-label" for="', $element->getAttribute("id"), '">';
			if($element->isRequired())
				echo '<span class="required">* </span>';
			echo $label, '</label>';
        }
    }
}
