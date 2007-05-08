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
require_once "HTTP/Request.php";
require_once "Net/URL2.php";
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

if (isset($auth_user)) {
    $form->setDefaults(array("name" => $auth_user->name,
                             "email" => $auth_user->email));
}

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
$form->addRule("project[link]", "Please supply a valid project link", "regex", "#^http(s?)://(.+)#", "client");
$form->applyFilter("project[link]", "htmlspecialchars");

if ($form->validate()) {
    $req =& new HTTP_Request;

    $url =& new Net_URL2($form->exportValue("project[link]"));
    $req->setURL($url->protocol . "://" . $url->host . ":" . $url->port . "/channel.xml");
    $req->sendRequest();
    if ($req->getResponseCode() != 200) {
        echo "<div class=\"errors\">The submitted URL does not ";
        echo "appear to point to a valid channel site.  You will ";
        echo "have to make sure that <tt>/channel.xml</tt> at least ";
        echo "exists and is valid.  If you think that this mechanism does not work ";
        echo "properly, please drop a mail to the ";
        echo "<a href=\"mailto:pear-webmaster@lists.php.net\">webmasters</a>.";
        echo "</div>";

        $form->display();
    } elseif (!$req->getResponseBody()) {
        // channel.xml is empty - spam spam spam
        echo "<div class=\"errors\">The submitted URL does not ";
        echo "appear to point to a valid channel site.  You will ";
        echo "have to make sure that <tt>/channel.xml</tt> at least ";
        echo "exists and is valid.  If you think that this mechanism does not work ";
        echo "properly, please drop a mail to the ";
        echo "<a href=\"mailto:pear-webmaster@lists.php.net\">webmasters</a>.";
        echo "</div>";

        $form->display();
    } elseif (strlen($req->getResponseBody()) > 100000) {
        // channel.xml is huge - possible DoS attack
        echo "<div class=\"errors\">The submitted URL does not ";
        echo "appear to point to a valid channel site.  You will ";
        echo "have to make sure that <tt>/channel.xml</tt> at least ";
        echo "exists and is not huge.  If you think that this mechanism does not work ";
        echo "properly, please drop a mail to the ";
        echo "<a href=\"mailto:pear-webmaster@lists.php.net\">webmasters</a>.";
        echo "</div>";

        $form->display();
    } else {
        do {
            // poor man's try/catch
            require_once 'PEAR/ChannelFile.php';
            $chan = new PEAR_ChannelFile;
            if (!$chan->fromXmlString($req->getResponseBody())) {
                // channel.xml is invalid xml - spam spam spam
                echo "<div class=\"errors\">The submitted URL does not ";
                echo "appear to point to a valid channel site.  You will ";
                echo "have to make sure that <tt>/channel.xml</tt> at least ";
                echo "exists and is valid.  If you think that this mechanism does not work ";
                echo "properly, please drop a mail to the ";
                echo "<a href=\"mailto:pear-webmaster@lists.php.net\">webmasters</a>.";
                echo "</div>";
        
                $form->display();
                break;
            }
            if (!$chan->validate()) {
                // channel.xml is invalid channelfile xml - spam spam spam
                echo "<div class=\"errors\">The submitted URL does not ";
                echo "appear to point to a valid channel site.  You will ";
                echo "have to make sure that <tt>/channel.xml</tt> at least ";
                echo "exists and is valid.  If you think that this mechanism does not work ";
                echo "properly, please drop a mail to the ";
                echo "<a href=\"mailto:pear-webmaster@lists.php.net\">webmasters</a>.";
                echo "</div>";
        
                $form->display();
                break;
            }
            if ($url->host != $chan->getServer()) {
                // channel.xml refers to different site - spam spam spam
                echo "<div class=\"errors\">The submitted URL does not ";
                echo "appear to point to a valid channel site.  You will ";
                echo "have to make sure that <tt>/channel.xml</tt> at least ";
                echo "exists and is valid.  In addition, it must refer to ";
                echo "your channel.  If you think that this mechanism does not work ";
                echo "properly, please drop a mail to the ";
                echo "<a href=\"mailto:pear-webmaster@lists.php.net\">webmasters</a>.";
                echo "</div>";
        
                $form->display();
                break;
            }
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
        } while (false);
    }
} else {
    $form->display();

    echo "<p>The &quot;Project Link&quot; should not point to the main ";
    echo "homepage of the project, but rather to a page with installation ";
    echo "instructures.</p>";
}
?>

<p><a href="/channels/">Back to the index</a></p>

<?php
response_footer();
