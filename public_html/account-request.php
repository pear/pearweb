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

        if (isset($_POST['purposecheck']) && count($_POST['purposecheck'])){
            display_error("We could not have said it more clearly. Read everything on "
                            . "this page and look at the form you are submitting carefully.");
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
            print "<br />Click <a href=\"/\">here</a> to go back to the front page.\n";
            $display_form = false;
        }
        break;
    }
} while (0);

if ($display_form) {

    response_header("Request Account");

    print "<h1>Request Account</h1>

<p>
You only need to request an account if you
<ul>
<li>Are planning to contribute a new package to PEAR and want to
propose this to the PEAR developer community.</li>

<li>Are going to help in the maintenance of an existing package. This
needs to be approved by the current maintainers of the package or by
the <a href=\"/group/\">PEAR Group</a>.</li>
</ul>

If the reason for your request does not fall under one of the
reasons above, please contact the <a href=\"mailto:pear-dev@lists.php.net\">
PEAR developers mailing list</a>.
</p>

<p>You do <b>not</b> need an account to:
<ul>
<li>Download, install and/or use PEAR packages.</li>
<li>Submit patches or code improvements for a particular package. 
Please use the <a href=\"/bugs/\">bug reporting system</a> for that
purpose.</li>
<li>Propose modifications to an existing package. Please use the
<a href=\"mailto:pear-dev@lists.php.net\">PEAR developers mailing 
list</a> for that or directly contact the maintainers of the package
in question.</li>
</ul>
These are not the only reasons for requests being rejected, but the most
common ones.
</p>

<p>If you want to contribute a package to PEAR, make sure that you have
followed all rules concerning PEAR packages.  Also, before a package
may be released, the code code must comply with the
<a href=\"http://pear.php.net/manual/en/standards.php\">PEAR Coding
Standards</a>.</p>

<p>Bogus, incomplete or incorrect requests will be summarily denied.</p>";

    print "<a name=\"requestform\" />\n";

    if (isset($errorMsg)) {
        print "<table>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><b>$errorMsg</b></td>\n";
        print " </tr>\n";
        print "</table>\n";
    }
    $invalid_purposes = array(
        'Learn about PEAR.',
        'Use PEAR.',
        'Download PEAR Packages.',
        'Submit patches/bugs.',
        'Suggest new features.',
        'Browse pear.php.net.'
        );
        $purposechecks = '';
        foreach ($invalid_purposes as $i => $purposeKey)
        {
            $purposechecks .= HTML_Form::returnCheckBox("purposecheck[$i]", @$_POST['purposecheck'][$i] ? 'on' : 'off');
            $purposechecks .= "$purposeKey <br />";
        }

    print "<form action=\"" . $_SERVER['PHP_SELF'] . "#requestform\" method=\"post\" name=\"request_form\">\n";
    $bb = new BorderBox("Request account", "90%", "", 2, true);
    $bb->horizHeadRow("Username:", HTML_Form::returnText("handle", @$_POST['handle'], 12));
    $bb->horizHeadRow("First Name:", HTML_Form::returnText("firstname", @$_POST['firstname']));
    $bb->horizHeadRow("Last Name:", HTML_Form::returnText("lastname", @$_POST['lastname']));
    $bb->horizHeadRow("Password:", HTML_Form::returnPassword("password", null, 10) . "   Again: " . HTML_Form::returnPassword("password2", null, 10));
    $bb->horizHeadRow("Email address:", HTML_Form::returnText("email", @$_POST['email']));
    $bb->horizHeadRow("Show email address?", HTML_Form::returnCheckbox("showemail", @$_POST['showemail']));
    $bb->horizHeadRow("Homepage", HTML_Form::returnText("homepage", @$_POST['homepage']));
    $bb->horizHeadRow("Purpose of your PEAR account:<br/>(Check all that apply)", $purposechecks);
    $bb->horizHeadRow("If your intended purpose is not in the list, please state it here:", HTML_Form::returnTextarea("purpose", stripslashes(@$_POST['purpose'])));
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
