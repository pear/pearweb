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
   $Id: add.php 310096 2011-04-09 14:29:22Z clockwerx $
*/
require_once "HTML/QuickForm2.php";
require_once "HTTP/Request2.php";
require_once "Net/URL2.php";
require_once 'pear-database-channel.php';
require_once 'HTML/QuickForm2/Renderer.php';
require_once 'HTML/QuickForm2/Element/Input.php';
require_once 'PEAR/ChannelFile.php';


/** @todo Shift ! */
if (!class_exists('HTML_QuickForm2_Element_InputUrl')) {
    class HTML_QuickForm2_Element_InputUrl extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'url');
    }

    HTML_QuickForm2_Factory::registerElement('url', 'HTML_QuickForm2_Element_InputUrl');
}


/** @todo Shift ! */
if (!class_exists('HTML_QuickForm2_Element_InputEmai')) {
    class HTML_QuickForm2_Element_InputEmail extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'email');
    }

    HTML_QuickForm2_Factory::registerElement('email', 'HTML_QuickForm2_Element_InputEmail');
}

auth_require('pear.admin');

if (empty($_REQUEST['channel']) || !channel::exists($_REQUEST['channel'])) {
    die("Invalid channel specified");
}

$channel = $dbh->query("SELECT * FROM channels WHERE name = ?", array($_REQUEST['channel']))->fetchRow(DB_FETCHMODE_ASSOC);

$chan = new PEAR_ChannelFile;

$tabs = array("List" => array("url" => "/channels/index.php",
                              "title" => "List Sites."),
              "Add Site" => array("url" => "/channels/add.php",
                                  "title" => "Add your site.")
              );


response_header("Channels :: Edit");

?>

<h1>Channels</h1>

<?php print_tabbed_navigation($tabs); ?>

<h2>Edit Channel</h2>

<?php
$form = new HTML_QuickForm2("submitForm");
$form->removeAttribute('name');

$form->addDataSource(new HTML_QuickForm2_DataSource_Array(array("contact_name" => $channel['contact_name'],
                         "contact_email" => $channel['contact_email'],
                         "project_label" => $channel["project_label"],
                         "project_link" => $channel["project_link"],
                         "is_active" => 1,
                        )));

$contact_name = $form->addElement("text", "contact_name", array('required' => 'required', 'placeholder' => 'John Doe'));
$contact_name->setLabel("Your name");
$contact_name->addFilter("htmlspecialchars");
$contact_name->addRule('required', "Please enter your name");

$contact_email = $form->addElement("email", "contact_email", array('required' => 'required', 'you@example.com'));
$contact_email->setLabel("Email");
$contact_email->addFilter("htmlspecialchars");

$contact_email->addRule('required', "Please enter your email address");
$contact_email->addRule('callback', '', array('callback'  => 'filter_var',
                                      'arguments' => array(FILTER_VALIDATE_EMAIL)));

$project_label = $form->addElement("text", "project_label", array('required' => 'required', 'placeholder' => 'PHPUnit'));
$project_label->setLabel("Project Name");
$project_label->addFilter("htmlspecialchars");
$project_label->addRule('required', "Please enter your project name");

$project_link = $form->addElement("url", "project_link", array('required' => 'required', 'placeholder' => 'http://pear.phpunit.de/'));
$project_link->setLabel("Project Homepage");
$project_link->addFilter("htmlspecialchars");
$project_link->addRule('required', "Please enter your project link");

$is_active = $form->addElement("checkbox", 'is_active', array('checked' => $channel["is_active"]));
$is_active->setLabel("Active?");

$form->addElement("submit");

if ($form->validate()) {
    $url = new Net_URL2('http://' . $project_name->getValue());

    try {
        $req = new HTTP_Request2;

        $req->setURL($url->getScheme() . "://" . $url->getHost() . ":" . $url->getPort() . "/channel.xml");
        channel::validate($req, $chan);

        if ($url->getHost() != $chan->getServer()) {
            throw new Exception("Channel server for wrong host");
        }

        channel::edit($channel['name'], $project_label->getValue(), $project_link->getValue(), $contact_name->getValue(), $contact_email->getValue());

        if ($is_active->getValue()) {
            channel::activate($channel['name']);
        } else {
            channel::deactivate($channel['name']);
        }


        echo "<div class=\"success\">Changes saved</div>\n";
    } catch (Exception $exception) {
        echo '<div class="errors">';

        switch ($exception->getMessage()) {
            case "Invalid channel site":
            case "Empty channel.xml":
                echo "The submitted URL does not ";
                echo "appear to point to a valid channel site.  You will ";
                echo "have to make sure that <tt>/channel.xml</tt> at least ";
                echo "exists and is valid.";
            break;

            default:
                echo $exception->getMessage();
            break;
        }

        echo '</div>';
        echo "<p>If you think that this mechanism does not work ";
        echo "properly, please drop a mail to the ";
        echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.</p>';

        echo $form;
    }
} else {
    echo $form;

    echo "<p>The &quot;Project Link&quot; should not point to the main ";
    echo "homepage of the project, but rather to a page with installation ";
    echo "instructures.</p>";
}
?>

<p><a href="/channels/">Back to the index</a></p>

<?php
response_footer();
