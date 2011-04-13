<?php
require_once 'HTML/QuickForm2/Renderer/Default.php';

/**
 * A custom Quickform2 renderer, which skips the factory/proxy behaviour.
 *
 * Additionally, it has a different template for grouped form controls (typically checkboxes)
 *
 * @bug http://pear.php.net/bugs/bug.php?id=18435&thanks=4 
 */
class HTML_QuickForm2_Renderer_PEAR extends HTML_QuickForm2_Renderer_Default { 
    public function __construct() {
        $checkbox_template = '<label for="{id}" class="element <qf:error> error</qf:error>"><qf:required><span class="required">* </span></qf:required>{element} {label}</label><br />
<qf:error><span class="error">{error}</span></qf:error>';

        $this->setElementTemplateForGroupClass('html_quickform2_container', 'html_quickform2_element_inputcheckable', $checkbox_template);
    } 
}
