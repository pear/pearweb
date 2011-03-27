<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2006 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Renderer.php';
require_once 'HTML/QuickForm2/Element/Input.php';

/** @todo Shift ! */
if (!class_exists('HTML_QuickForm2_Element_InputUrl')) {
    class HTML_QuickForm2_Element_InputUrl extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'url');
    }

    HTML_QuickForm2_Factory::registerElement('url', 'HTML_QuickForm2_Element_InputUrl');
}

if (!defined('PEAR_COMMON_PACKAGE_NAME_PREG')) {
    define('PEAR_COMMON_PACKAGE_NAME_PREG', '^([A-Z][a-zA-Z0-9_]+|[a-z][a-z0-9_]+)$');
}

auth_require('pear.dev');

$display_form = true;
$errors       = array();
$jumpto       = 'name';

$valid_args = array('submit', 'name','category','license','summary','desc','homepage','cvs_link');
foreach ($valid_args as $arg) {
    if (isset($_POST[$arg])) {
        $_POST[$arg] = htmlspecialchars($_POST[$arg]);
    }
}

$submit = isset($_POST['submit']) ? true : false;

do {
    if ($submit) {
        $required = array('name'    => 'enter the package name',
                          'summary' => 'enter the one-liner description',
                          'desc'    => 'enter the full description',
                          'license' => 'choose a license type',
                          'category'=> 'choose a category');
        foreach ($required as $field => $_desc) {
            if (empty($_POST[$field])) {
                $errors[] = "Please $_desc!";
                $jumpto = $field;
                break 2;
            }
        }

        if (!preg_match('/' . PEAR_COMMON_PACKAGE_NAME_PREG . '/', $_POST['name'])) {
            $errors[] = 'Invalid package name.  PEAR package names must start'
                        . ' with a capital letter and contain only letters,'
                        . ' digits and underscores.';
            break;
        }

        $dbh->expectError(DB_ERROR_CONSTRAINT);
        include_once 'pear-database-package.php';
        $pkg = package::add(array(
                                  'name'        => $_POST['name'],
                                  'type'        => SITE,
                                  'category'    => $_POST['category'],
                                  'license'     => $_POST['license'],
                                  'summary'     => $_POST['summary'],
                                  'description' => $_POST['desc'],
                                  'homepage'    => $_POST['homepage'],
                                  'cvs_link'    => $_POST['cvs_link'],
                                  'lead'        => $auth_user->handle
                                  ));
        $dbh->popExpect();
        if (DB::isError($pkg) && $pkg->getCode() == DB_ERROR_CONSTRAINT) {
            error_handler("The `" . $_POST['name'] . "' package already exists!",
                          "Package already exists");
            exit;
        }
        $display_form = false;
        response_header("Package Registered");
        echo "The package `" . $_POST['name'] . "' has been registered in PEAR.<br />\n";
        echo "You have been assigned as lead developer.<br />\n";
        echo "The " . make_link("/group/", "PEAR Group") . " has been notified and the package will be approved soon.<br />\n";
    }
} while (false);

if ($display_form) {
    response_header('New Package');
    echo "<h1>New Package</h1>\n";
    report_error($errors);
?>

<p>
 Use this form to register a new package.
</p>

<p>
 <strong>Before proceeding</strong>, make sure you pick the right name for
 your package.  This is usually done through &quot;community consensus,&quot;
 which means posting a suggestion to the pear-dev mailing list and have
 people agree with you.
</p>

<p>
Generally, a format of <em>Category_SpecificComponent</em> is a good way to go.
</p>
<?php
    // get parent categories
    $sql = 'SELECT id, name FROM categories WHERE parent IS NULL ORDER BY name';
    $parents = $dbh->getAssoc($sql);
    // get child categories
    $sql = 'SELECT id, parent, name FROM categories WHERE parent IS NOT NULL ORDER BY parent, name';
    $kids = $dbh->getAssoc($sql);
    $children = array();
    foreach ($kids as $id => $c) {
        $children[$c[0]][$id] = $c[1];
    }

    $categories = array();
    $categories[''] = '-- Select Category --';

    function recur_categories($children, $parents, $me, &$categories, $indent = '--')
    {
        foreach ($children[$me] as $nid => $category) {
            $categories[$nid] = $indent . ' ' . $category;
            if (isset($children[$nid])) {
                recur_categories($children, $parents, $nid, $categories, $indent . '--');
            }
        }
    }

    foreach ($parents as $id => $category) {
        $categories[$id] = $category;
        if (isset($children[$id])) {
            recur_categories($children, $parents, $id, $categories, '--');
        }
    }

$form = new HTML_QuickForm2('package-new', 'post');
$form->removeAttribute('name');

$renderer = HTML_QuickForm2_Renderer::factory('default');

    // Set defaults for the form elements
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
        'name'     => isset($_POST['name'])     ? $_POST['name']     : '',
        'license'  => isset($_POST['license'])  ? $_POST['license']  : '',
        'category' => isset($_POST['category']) ? $_POST['category'] : '',
        'summary'  => isset($_POST['summary'])  ? $_POST['summary']  : '',
        'desc'     => isset($_POST['desc'])     ? $_POST['desc']     : '',
        'homepage' => isset($_POST['homepage']) ? $_POST['homepage'] : '',
        'cvs_link' => isset($_POST['cvs_link']) ? $_POST['cvs_link'] : '',
    )));

    $form->addElement('text', 'name', array('size' => 20, 'required' => 'required', 'placeholder' => 'XML_Parser', 'pattern' => PEAR_COMMON_PACKAGE_NAME_PREG))->setLabel("Package Name");
    $form->addElement('text', 'license', array('size' => 20, 'required' => 'required', 'placeholder' => 'BSD'))->setLabel("License");
    $form->addElement('select', 'category', array('required' => 'required'))->setLabel("Category")->loadOptions($categories);
    $form->addElement('textarea', 'summary', array('cols' => 60, 'required' => 'required'))->setLabel("Summary");
    $form->addElement('textarea', 'desc', array('cols' => 60, 'rows' => 5, 'required' => 'required'))->setLabel("Full description");
    $form->addElement('url', 'homepage', array('size' => 40, 'placeholder' => 'http://example.com'))->setLabel("Additional project homepage");
    $form->addElement('url', 'cvs_link', array('size' => 40, 'placeholder' => 'http://example.com/svn/trunk/'))->setLabel("Version control web URL");
    $form->addElement('submit', 'submit', 'Submit Request');

    print '<h2>Register Package</h2>';
    print $form->render($renderer);

    if ($jumpto) {
        echo "\n<script type=\"text/javascript\">\n<!--\n";
        echo "document.forms[1].$jumpto.focus();\n";
        echo "// -->\n</script>\n";
    }
}

response_footer();
