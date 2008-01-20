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

require_once 'HTML/QuickForm.php';

if (!defined('PEAR_COMMON_PACKAGE_NAME_PREG')) {
    define('PEAR_COMMON_PACKAGE_NAME_PREG', '/^([A-Z][a-zA-Z0-9_]+|[a-z][a-z0-9_]+)\z/');
}

auth_require('pear.dev');

$display_form = true;
$width = 60;
$errors = array();
$jumpto = 'name';

$valid_args = array('submit', 'name','category','license','summary','desc','homepage','cvs_link');
foreach ($valid_args as $arg) {
    if(isset($_POST[$arg])) {
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

        if (!preg_match(PEAR_COMMON_PACKAGE_NAME_PREG, $_POST['name'])) {
            $errors[] = 'Invalid package name.  PEAR package names must start'
                        . ' with a capital letter and contain only letters,'
                        . ' digits and underscores.';
            break;
        }

        $dbh->expectError(DB_ERROR_CONSTRAINT);
        include_once 'pear-database-package.php';
        $pkg = package::add(array(
                                  'name'        => $_POST['name'],
                                  'type'        => 'pear',
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
        print "The package `" . $_POST['name'] . "' has been registered in PEAR.<br />\n";
        print "You have been assigned as lead developer.<br />\n";
        print "The " . make_link("/group/", "PEAR Group") . " has been notified and the package will be approved soon.<br />\n";
    }
} while (false);

if ($display_form) {
    $title = "New Package";
    response_header($title);

    print "<h1>$title</h1>\n";

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
 Note that if you don't follow this simple rule and break
 established naming conventions, your package will be taken hostage.
 So please play nice, that way we can keep the bureaucracy at a minimum.
</p>

    <?php

    // get parent categories
    $parentcategories = $dbh->getAssoc("SELECT id,name FROM categories
        WHERE parent IS NULL ORDER BY name");
    // get child categories
    $children = $dbh->getAll("SELECT parent,id,name FROM categories
        WHERE parent IS NOT NULL ORDER BY parent, name");
    $childcategories = array();
    foreach ($children as $cinfo) {
        $id = array_shift($cinfo);
        $childcategories[$id][] = $cinfo;
    }

    $categories = array();
    $categories[''] = '-- Select Category --';
    function recur_categories($childcategories, $parentcategories, $me,
                              &$categories, $indent = '--')
    {
        foreach ($childcategories[$me] as $category) {
            $nid = $category[0];
            $category = $category[1];
            $category = $indent . $category;
            $categories[$nid] = $category;
            if (isset($childcategories[$nid])) {
                recur_categories($childcategories, $parentcategories, $nid, $categories,
                    $indent . '--');
            }
        }
    }
    foreach ($parentcategories as $id => $category) {
        $categories[$id] = $category;
        if (isset($childcategories[$id])) {
            recur_categories($childcategories, $parentcategories, $id, $categories,
                '--');
        }
    }

$form = new HTML_QuickForm('package-new', 'post', 'package-new.php', 'test');

$renderer =& $form->defaultRenderer();
$renderer->setElementTemplate('
 <tr>
  <th class="form-label_left">
   <!-- BEGIN required --><span style="color: #ff0000">*</span><!-- END required -->
   {label}
  </th>
  <td class="form-input">
   <!-- BEGIN error --><span style="color: #ff0000">{error}</span><br /><!-- END error -->
   {element}
  </td>
 </tr>
');

$renderer->setFormTemplate('
<form{attributes}>
 <div>
  {hidden}
  <table border="0" class="form-holder" cellspacing="1">
   {content}
  </table>
 </div>
</form>');

    // Set defaults for the form elements
    $form->setDefaults(array(
        'name'     => isset($_POST['name'])     ? $_POST['name']     : '',
        'license'  => isset($_POST['license'])  ? $_POST['license']  : '',
        'category' => isset($_POST['category']) ? $_POST['category'] : '',
        'summary'  => isset($_POST['summary'])  ? $_POST['summary']  : '',
        'desc'     => isset($_POST['desc'])     ? $_POST['desc']     : '',
        'homepage' => isset($_POST['homepage']) ? $_POST['homepage'] : '',
        'cvs_link' => isset($_POST['cvs_link']) ? $_POST['cvs_link'] : '',
    ));

    $form->addElement('html', '<caption class="form-caption">Register Package</caption>');
    $form->addElement('text', 'name', 'Package Name', array('size' => 20));
    $form->addElement('text', 'license', 'License', array('size' => 20));
    $form->addElement('select', 'category', 'Category', $categories);
    $form->addElement('textarea', 'summary', 'Summary', array('cols' => $width));
    $form->addElement('textarea', 'desc', 'Full description', array('cols' => $width, 'rows' => 3));
    $form->addElement('text', 'homepage', 'Additional project homepage', array('size' => 40));
    $form->addElement('text', 'cvs_link', 'CVS Web URL', array('size' => 40));
    $form->addElement('static', null, null, '<small>For example: http://cvs.php.net/cvs.php/pear/XML_Parser</small>');
    $form->addElement('submit', 'submit', 'Submit Request');
    $form->display();

    if ($jumpto) {
        print "\n<script language=\"JavaScript\">\n<!--\n";
        print "document.forms[1].$jumpto.focus();\n";
        print "// -->\n</script>\n";
    }

    print "</form>\n";
}

response_footer();