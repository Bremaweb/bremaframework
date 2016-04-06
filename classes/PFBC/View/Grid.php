<?php
namespace PFBC\View;

class Grid extends \PFBC\View {

    protected $class = "Grid";
    public $map;

    public function render() {
        $this->_form->appendAttribute("class", $this->class);

        echo '<style type="text/css">
            #' . $this->_form->getAttribute("id") . ' input[type="radio"], #' . $this->_form->getAttribute("id") . ' input[type="button"], #' . $this->_form->getAttribute("id") . ' input[type="submit"], #' . $this->_form->getAttribute("id") . ' .input-prepend input, #' . $this->_form->getAttribute("id") . ' .input-append input { width: auto;}
            #' . $this->_form->getAttribute("id") . ' .row-fluid {margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #f4f4f4;}
            #' . $this->_form->getAttribute("id") . ' .row-section {margin-bottom: -10px; padding-bottom: 0px; border-bottom: none;}
        </style>
        <form', $this->_form->getAttributes(), '>';
        $this->_form->getErrorView()->render();


        $elements = $this->_form->getElements();
        $elementSize = sizeof($elements);
        $first = true;

        $layoutIndex = 0;
        $elementCountVariable = 0;

        for ($e = 0; $e < $elementSize; ++$e) {
            $element = $elements[$e];

            if ($element instanceof \PFBC\Element\Hidden ) {
                $element->render();
            } elseif ($element instanceof \PFBC\Element\Button) {
                if ($e == 0 || !$elements[($e - 1)] instanceof \PFBC\Element\Button) {
                    echo '<div class="form-actions">';
                }

                $element->render();

                if (($e + 1) == $elementSize || !$elements[($e + 1)] instanceof \PFBC\Element\Button) {
                    echo '</div>';
                }
            } else {
                $field_per_row = 1;
                if (isset($this->map[$layoutIndex])) {
                    $field_per_row = $this->map[$layoutIndex];
                }

                if ($first) {
                    echo '<div class="row-fluid' . $section_class . '">';
                } elseif ($elementCountVariable >= $field_per_row) {
                    echo '</div><div class="row-fluid' . $section_class . '">';
                    $layoutIndex++;
                    $elementCountVariable = 0;
                }

                if (isset($this->map[$layoutIndex])) {
                    $span_class = "col-md-" . (12 / $this->map[$layoutIndex]);
                } else {
                    $span_class = 'col-md-12';
                }

                $element->setAttribute("class", "input-block-level");

                echo '<div class="' . $span_class . '"' . ($element->getAttribute('display') ? ' style="display:none"' : '') . ($element->getAttribute('customid') ? ' id="' . $element->getAttribute('customid') . '"' : '') . '>', $this->renderLabel($element), '<div>', $element->render(), $this->renderDescriptions($element), '</div></div>';

                $elementCountVariable++;
                $first = false;
            }
        }


        echo '</div></form>';
    }

    protected function renderLabel(\PFBC\Element $element) {
        $label = $element->getLabel();
        if (!empty($label)) {
            echo '<label class="control-label" for="', $element->getAttribute("id"), '">';
            if ($element->isRequired())
                echo '<span class="required">* </span>';
            echo $label, '</label>';
        }
    }

}
?>