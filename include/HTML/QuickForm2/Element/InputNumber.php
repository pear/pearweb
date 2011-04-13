<?php
require_once 'HTML/QuickForm2/Element/Input.php';

if (!class_exists('HTML_QuickForm2_Element_InputNumber')) {
    class HTML_QuickForm2_Element_InputNumber extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'number');
    }

    HTML_QuickForm2_Factory::registerElement('number', 'HTML_QuickForm2_Element_InputNumber');
}
