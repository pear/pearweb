<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.0 of the PHP license,       |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/3_0.txt.                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("Channels :: Add");

require_once "HTML/QuickForm.php";
require_once "Damblan/Log.php";
require_once "Damblan/Log/Mail.php";


$tabs = array("List" => array("url" => "/channels/index.php",
                              "title" => "List Sites."),
              "Add Site" => array("url" => "/channels/add.php",
                                  "title" => "Add your site.")
              );
?>

<h1>Channels</h1>

<?php print_tabbed_navigation($tabs); ?>

<h2>Add Site</h2>

<p>If you are running an open-source project that also provides 
PEAR-compatible packages, you can submit it for inclusion in the
<a href="/channels/">index</a>.  Please be aware that the PEAR webmaster
staff may reject your submission if they do not consider it appropriate.</p>

<?php
$form = new HTML_QuickForm("submitForm");

$form->addElement("text", "name", "Your name:", 
                  array("size" => 30));
$form->addElement("text", "email", "Your email address:",
                  array("size" => 30));
$form->addElement("text", "project[name]", "Project Name:",
                  array("size" => 30));
$form->addElement("text", "project[link]", "Project Link:",
                  array("size" => 30));
$form->addElement("submit", null, "Send");

$form->addRule("name", "Please enter your name", "required", null, "client");
$form->applyFilter("name", "htmlspecialchars");
$form->addRule("email", "Please enter your email address", "required", null, "client");
$form->addRule("email", "Please enter a valid email address", "email", null, "client");
$form->applyFilter("email", "htmlspecialchars");
$form->addRule("project[name]", "Please enter the project name", "required", null, "client");
$form->applyFilter("project[name]", "htmlspecialchars");
$form->addRule("project[link]", "Please enter the project link", "required", null, "client");
$form->addRule("project[link]", "The project link has to begin with http://", "regex", "#^http://#", "client");
$form->applyFilter("project[link]", "htmlspecialchars");

if ($form->validate()) {
    $text = sprintf("[Channels] Please add %s (%s) to the channel index.",
                    $form->exportValue("project[name]"),
                    $form->exportValue("project[link]"));
    $from = sprintf('"%s" <%s>',
                    $form->exportValue("name"),
                    $form->exportValue("email"));

    $logger = new Damblan_Log;
    $observer = new Damblan_Log_Mail;
    $observer->setRecipients("pear-webmaster@lists.php.net");
    $observer->setHeader("From", $from);
    $observer->setHeader("Subject", "Channel link submission");
    $logger->attach($observer);
    $logger->log($text);

    echo "<div class=\"success\">Thanks for your submission.  It will ";
    echo "be reviewed as soon as possible.</div>\n";
} else {
    $form->display();
}
?>

<p><a href="/channels/">Back to the index</a></p>

<?php
response_footer();
