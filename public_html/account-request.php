<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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

require_once "HTML/Form.php";

function display_error($msg)
{
    global $errorMsg;

    if (is_object($msg)) {
        $msg = $msg->getMessage();
    }
    $errorMsg .= "<font color=\"#CC0000\" size=\"+1\">$msg</font><br />\n";
}

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "handle";

do {
    if (isset($_POST['submit'])) {
        response_header("Account Request Submitted");

        if (empty($_POST['comments_read'])) {
            display_error("Obviously you did not read all the comments concerning "
                          . "the need for an account. Please read them again.");
            $display_form = true;
            break;
        }

        $ok = user::add($_POST);

        if (!empty($_POST['jumpto'])) {
            $jumpto = $_POST['jumpto'];
        }
        if (isset($_POST['display_form'])) {
            $display_form = $_POST['display_form'];
        }

        if (!PEAR::isError($ok)) {
            if ($ok == true) {
                print "<h2>Account Request Submitted</h2>\n";
                print "Your account request has been submitted, it will ".
                    "be reviewed by a human shortly.  This may take from two ".
                    "minutes to several days, depending on how much time people ".
                    "have.  ".
                    "You will get an email when your account is open, or if ".
                    "your request was rejected for some reason.";
            } else {
                print "<h2>Possible Problem!</h2>\n";
                print "Your account request has been submitted, but there ".
                    "were problems mailing one or more administrators.  ".
                    "If you don't hear anything about your account in a few ".
                    "days, please drop a mail about it to the <i>pear-dev</i> ".
                    "mailing list.";
            }
            print "<br />Click the top-left PEAR logo to go back to the front page.\n";
        }
        break;
    }
} while (0);

if ($display_form) {

    response_header("Request Account");

    print "<h1>Request Account</h1>

<p>You do <b>not</b> need an account if you want to download, install and/or
use PEAR packages. You only need to request an account if you want to
contribute a new package to PEAR CVS, help in the maintainance of an existing
package, or list and release your package using the PEAR packager/installer
(without hosting the code in PEAR CVS).</p>

<p>If you are contributing a package to PEAR, make sure that you have gone through
the peer review process. Make also sure that if you are going to include code
in PEAR CVS, that this complies with the PEAR code standards before it is
released.</p>

<p>Bogus, incomplete or incorrect requests will be summarily denied.</p>";

    if (isset($errorMsg)) {
        print "<table>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><b>$errorMsg</b></td>\n";
        print " </tr>\n";
        print "</table>\n";
    }

    print "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=\"request_form\">\n";
    $bb = new BorderBox("Request account", "90%", "", 2, true);
    $bb->horizHeadRow("Username:", HTML_Form::returnText("handle", @$_POST['handle'], 12));
    $bb->horizHeadRow("First Name:", HTML_Form::returnText("firstname", @$_POST['firstname']));
    $bb->horizHeadRow("Last Name:", HTML_Form::returnText("lastname", @$_POST['lastname']));
    $bb->horizHeadRow("Password:", HTML_Form::returnPassword("password", null, 10) . "   Again: " . HTML_Form::returnPassword("password2", null, 10));
    $bb->horizHeadRow("Email address:", HTML_Form::returnText("email", @$_POST['email']));
    $bb->horizHeadRow("Show email address?", HTML_Form::returnCheckbox("showemail", @$_POST['showemail']));
    $bb->horizHeadRow("Homepage", HTML_Form::returnText("homepage", @$_POST['homepage']));
    $bb->horizHeadRow("Purpose of your PEAR account<br />(No account is needed for using PEAR or PEAR packages):", HTML_Form::returnTextarea("purpose", stripslashes(@$_POST['purpose'])));
    $bb->horizHeadRow("More relevant information<br />about you (optional):", HTML_Form::returnTextarea("moreinfo", stripslashes(@$_POST['moreinfo'])));
    $bb->horizHeadRow("You have read all the comments above:", HTML_Form::returnCheckbox("comments_read", @$_POST['comments_read']));
    $bb->horizHeadRow("<input type=\"submit\" name=\"submit\" />&nbsp;<input type=\"reset\" />");
    $bb->end();
    print "</form>";

    if ($jumpto) {
        print "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n";
        print "if (!document.forms[1].$jumpto.disabled) document.forms[1].$jumpto.focus();\n";
        print "\n// -->\n</script>\n";
    }
}

response_footer();

?>
