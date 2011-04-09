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

require_once "HTML/QuickForm2.php";
require_once "HTTP/Request2.php";
require_once "Net/URL2.php";
require_once "Damblan/Log.php";
require_once "Damblan/Log/Mail.php";

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


/** @todo Shift ! */
if (!class_exists('HTML_QuickForm2_Element_InputEmai')) {
    class HTML_QuickForm2_Element_InputEmail extends HTML_QuickForm2_Element_Input
    {
        protected $attributes = array('type' => 'email');
    }

    HTML_QuickForm2_Factory::registerElement('email', 'HTML_QuickForm2_Element_InputEmail');
}

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
$form = new HTML_QuickForm2("submitForm");
$form->removeAttribute('name');

if (isset($auth_user)) {
    $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array("name" => $auth_user->name,
                             "email" => $auth_user->email)));
}

$name = $form->addElement("text", "name", array('required' => 'required', 'placeholder' => 'John Doe'))
                    ->setLabel("Your name")
                    ->addFilter("htmlspecialchars")
                    ->addRule('required', "Please enter your name");

$email = $form->addElement("email", "email", array('required' => 'required', 'you@example.com'))
                    ->setLabel("Email")
                    ->addFilter("htmlspecialchars");

$email->addRule('required', "Please enter your email address");
$email->addRule('callback', '', array('callback'  => 'filter_var',
                                      'arguments' => array(FILTER_VALIDATE_EMAIL)));

$project_name = $form->addElement("text", "project[name]", array('required' => 'required', 'placeholder' => 'PHPUnit'))
                    ->setLabel("Project Name")
                    ->addFilter("htmlspecialchars")
                    ->addRule('required', "Please enter your project name");

$link = $form->addElement("url", "project[link]", array('required' => 'required', 'placeholder' => 'http://pear.phpunit.de/'));
$link->setLabel("Link");
$link->addFilter("htmlspecialchars");
$link->addRule('required', "Please enter your project link");

$form->addElement("submit");

if ($form->validate()) {
    $url = new Net_URL2($link->getValue());

    try {
        $req = new HTTP_Request2;

        $req->setURL($url->getScheme() . "://" . $url->getHost() . ":" . $url->getPort() . "/channel.xml");
        $response = $req->send();
        if ($response->getStatus() != 200) {
            echo "<div class=\"errors\">The submitted URL does not ";
            echo "appear to point to a valid channel site.  You will ";
            echo "have to make sure that <tt>/channel.xml</tt> at least ";
            echo "exists and is valid.  If you think that this mechanism does not work ";
            echo "properly, please drop a mail to the ";
            echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.';
            echo "</div>";

            echo $form;
        } elseif (!$response->getBody()) {
            // channel.xml is empty - spam spam spam
            echo "<div class=\"errors\">The submitted URL does not ";
            echo "appear to point to a valid channel site.  You will ";
            echo "have to make sure that <tt>/channel.xml</tt> at least ";
            echo "exists and is valid.  If you think that this mechanism does not work ";
            echo "properly, please drop a mail to the ";
            echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.';
            echo "</div>";

            echo $form;
        } elseif (strlen($response->getBody()) > 100000) {
            // channel.xml is huge - possible DoS attack
            echo "<div class=\"errors\">The submitted URL does not ";
            echo "appear to point to a valid channel site.  You will ";
            echo "have to make sure that <tt>/channel.xml</tt> at least ";
            echo "exists and is not huge.  If you think that this mechanism does not work ";
            echo "properly, please drop a mail to the ";
            echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.';
            echo "</div>";

            echo $form;
        } else {
            do {
                // poor man's try/catch
                require_once 'PEAR/ChannelFile.php';
                $chan = new PEAR_ChannelFile;
                if (!$chan->fromXmlString($response->getBody())) {
                    // channel.xml is invalid xml - spam spam spam
                    echo "<div class=\"errors\">The submitted URL does not ";
                    echo "appear to point to a valid channel site.  You will ";
                    echo "have to make sure that <tt>/channel.xml</tt> at least ";
                    echo "exists and is valid.  If you think that this mechanism does not work ";
                    echo "properly, please drop a mail to the ";
                    echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.';
                    echo "</div>";

                    echo $form;
                    break;
                }
                if (!$chan->validate()) {
                    // channel.xml is invalid channelfile xml - spam spam spam
                    echo "<div class=\"errors\">The submitted URL does not ";
                    echo "appear to point to a valid channel site.  You will ";
                    echo "have to make sure that <tt>/channel.xml</tt> at least ";
                    echo "exists and is valid.  If you think that this mechanism does not work ";
                    echo "properly, please drop a mail to the ";
                    echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.';
                    echo "</div>";

                    echo $form;
                    break;
                }
                if ($url->getHost() != $chan->getServer()) {
                    // channel.xml refers to different site - spam spam spam
                    echo "<div class=\"errors\">The submitted URL does not ";
                    echo "appear to point to a valid channel site.  You will ";
                    echo "have to make sure that <tt>/channel.xml</tt> at least ";
                    echo "exists and is valid.  In addition, it must refer to ";
                    echo "your channel.  If you think that this mechanism does not work ";
                    echo "properly, please drop a mail to the ";
                    echo '<a href="mailto:' . PEAR_WEBMASTER_EMAIL . '">webmasters</a>.';
                    echo "</div>";

                    echo $form;
                    break;
                }
                $text = sprintf("[Channels] Please add %s (%s) to the channel index.",
                                $project_name->getValue(),
                                $link->getValue());
                $from = sprintf('"%s" <%s>',
                                $name->getValue(),
                                $email->getValue());

                $logger = new Damblan_Log;

                $observer = new Damblan_Log_Mail;
                $observer->setRecipients(PEAR_WEBMASTER_EMAIL);
                $observer->setHeader("From", $from);
                $observer->setHeader("Subject", "Channel link submission");
                $logger->attach($observer);

                $logger->log($text);

                echo "<div class=\"success\">Thanks for your submission.  It will ";
                echo "be reviewed as soon as possible.</div>\n";
            } while (false);
        }
    } catch (HTTP_Request2_Exception $exception) {
        echo '<div class="errors">';
        echo $exception->getMessage();
        echo "</div>";

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
