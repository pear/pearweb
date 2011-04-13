<?php
require_once 'HTML/QuickForm2/Element/Input.php';

if (!class_exists('HTML_QuickForm2_Element_InputUrl')) {
    class HTML_QuickForm2_Element_InputUrl extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'url');
    }

    HTML_QuickForm2_Factory::registerElement('url', 'HTML_QuickForm2_Element_InputUrl');
}
