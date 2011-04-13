<?php
require_once 'HTML/QuickForm2/Element/Input.php';

if (!class_exists('HTML_QuickForm2_Element_InputEmail')) {
    class HTML_QuickForm2_Element_InputEmail extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'email');
    }

    HTML_QuickForm2_Factory::registerElement('email', 'HTML_QuickForm2_Element_InputEmail');
}
