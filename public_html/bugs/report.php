<?php /* vim: set noet ts=4 sw=4: : */
require_once 'bugs/prepend.inc';
require_once 'bugs/cvs-auth.inc';

error_reporting(E_ALL ^ E_NOTICE);

/* When user submits a report, do a search and display the results before allowing
 * them to continue */

if (isset($save) && isset($pw)) { # non-developers don't have $user set
	setcookie('MAGIC_COOKIE',base64_encode("$user:$pw"),time()+3600*24*12,'/','.php.net');
}
if (isset($MAGIC_COOKIE) && !isset($user) && !isset($pw)) {
	list($user,$pw) = explode(':', base64_decode($MAGIC_COOKIE));
}

/* See bugs.sql for the table layout. */

$errors = array();

if ($_POST['in']) {
	if (!($errors = incoming_details_are_valid($_POST['in'], 1))) {

		if (!$_POST['in']['did_luser_search']) {

			$_POST['in']['did_luser_search'] = 1;

			/* search for a match using keywords from the subject */

			$sdesc = rinse($_POST['in']['sdesc']);

			/* if they are filing a feature request, only look for similar features */
            $where_clause = 'WHERE ';
			
            $package_name = $_POST['in']['package_name'];
			if ($package_name == 'Feature/Change Request') {
				$where_clause .= "package_name = '$package_name'";
			} else {
				$where_clause .= "package_name != 'Feature/Change Request'";
			}
            
			list($sql_search, $ignored) = format_search_string($sdesc);

			$where_clause .= $sql_search;

			$query = "SELECT * from bugdb $where_clause LIMIT 5";

			$res = $dbh->query($query);
            if (DB::isError($res)) {
                    die ($query->getDebugInfo());
	        }

			if ($res->numRows() == 0) {
				$ok_to_submit_report = 1;
			} else {
				response_header('Report - Confirm');
                # the lol
                echo '<style type="text/css">'; include('./style.css'); echo '</style>';

?>
<p>Are you sure that you searched before you submitted your bug report?
We found the following bugs that seem to be similar to yours; please check them
before sumitting the report as they might contain the solution you are looking for.
</p>
<p>If you're sure that your report is a genuine bug that has not been reported before,
you can scroll down and click the submit button to really enter the details into our database.
</p>


<div class="warnings">
<table class="lusersearch">
<tr>
<td><strong>Description</strong></td>
<td><strong>Possible Solution</strong></td>
</tr>
<?php

				while ($row = $res->fetchRow()) {

					$resolution = mysql_get_one("SELECT comment from bugdb_comments where bug = " . $row['id'] . " 
                                                order by id desc limit 1");

					if ($resolution) {
						$resolution = htmlspecialchars($resolution);
					}

					$summary = $row['ldesc'];
					if (strlen($summary) > 256) {
						$summary = htmlspecialchars(substr(trim($summary), 0, 256)) . " ...";
					} else {
						$summary = htmlspecialchars($summary);
					}

					$bug_url = '/bugs/bug.php?id=' . $row['id'] . '&amp;edit=2';

					echo '<tr><td colspan=\"2\"><a href="'.$bug_url.'">Bug #' . $row['id'] . ': ' . htmlspecialchars($row['sdesc']) . '</a></td></tr>';
					echo '<tr><td>' . $summary . '</td><td>' . nl2br($resolution) . '</td>';

					echo "</tr>\n";

				}

?>
</table>
</div>
<?php

			}
		} else {
			/* we displayed the luser search and they said it really
			 * was not already submitted, so let's allow them to submit */
			$ok_to_submit_report = true;
		}

		if ($ok_to_submit_report) {

			/* Put all text areas together. */
			$fdesc = "Description:\n------------\n". $_POST['in']['ldesc'] ."\n\n";
			if (!empty($_POST['in']['repcode'])) {
				$fdesc .= "Reproduce code:\n---------------\n". $_POST['in']['repcode'] ."\n\n";
			}
			if (!empty($_POST['in']['expres']) || $_POST['in']['expres'] === '0') {
				$fdesc .= "Expected result:\n----------------\n". $_POST['in']['expres'] ."\n\n";
			}
			if (!empty($_POST['in']['actres']) || $_POST['in']['actres'] === '0') {
				$fdesc .= "Actual result:\n--------------\n". $_POST['in']['actres'] ."\n";
			}

            $bug_id = $dbh->nextId('bugdb');
            $bug_type     = $_POST['in']['bug_type'];
            $package_name = $_POST['in']['package_name'];
            $email        = $_POST['in']['email'];
            $sdesc        = $_POST['in']['sdesc'];
            $php_version  = $_POST['in']['php_version'];
            $php_os       = $_POST['in']['php_os'];
            $passwd       = $_POST['in']['passwd'];
			$query = 'INSERT INTO bugdb (id,package_name,bug_type,email,sdesc,ldesc,php_version,php_os,status,ts1,passwd) 
                    VALUES ('.
                    (int)$bug_id .','.
                    $dbh->quoteSmart($package_name).','.
                    $dbh->quoteSmart($bug_type).','.
                    $dbh->quoteSmart($email).','.
                    $dbh->quoteSmart($sdesc).','.
                    $dbh->quoteSmart($fdesc).','.
                    $dbh->quoteSmart($php_version).','.
                    $dbh->quoteSmart($php_os).','.
                    $dbh->quoteSmart('Open').',
                    NOW(),
                    '. $dbh->quoteSmart($passwd) .'
                    )';

            $res = $dbh->query($query);
            
            if (DB::isError($res)) {
                die ($res->getDebugInfo());
            }
			$cid = $bug_id;

			$report = '';
			$report .= 'From:             '.spam_protect(stripslashes($_POST['in']['email']))."\n";
			$report .= 'Operating system: '.stripslashes($_POST['in']['php_os'])."\n";
			$report .= 'PHP version:      '.stripslashes($_POST['in']['php_version'])."\n";
            $report .= 'Package:          '.$_POST['in']['package_name']."\n";
			$report .= 'Bug Type:     '.$_POST['in']['bug_type']."\n";
			$report .= 'Bug description:  ';

			$fdesc = stripslashes($fdesc);
			$sdesc = stripslashes($_POST['in']['sdesc']);

			$ascii_report = "$report$sdesc\n\n".wordwrap($fdesc);
			$ascii_report.= "\n-- \nEdit bug report at http://pear.php.net/bugs/bug.php?id=$cid&amp;edit=";

            list($mailto, $mailfrom) = get_package_mail($_POST['in']['package_name']);

			$email = stripslashes($_POST['in']['email']);
			$protected_email = '"'.spam_protect($email)."\" <$mailfrom>";

			// provide shortcut URLS for "quick bug fixes"
			$dev_extra = '';
            /*
			$maxkeysize = 0;
			foreach ($RESOLVE_REASONS as $v) {
				if (!$v['webonly']) {
					$actkeysize = strlen($v['desc']) + 1;
					$maxkeysize = (($maxkeysize < $actkeysize) ? $actkeysize : $maxkeysize);
				}
			}
			foreach ($RESOLVE_REASONS as $k => $v) {
				if (!$v['webonly'])
					$dev_extra .= str_pad($v['desc'] . ":", $maxkeysize) .
						" http://bugs.php.net/fix.php?id=$cid&amp;r=$k\n";
			}
            */

			// Set extra-headers
			$extra_headers = 'From: ' .  $protected_email . "\r\n";
			$extra_headers.= 'X-PHP-Bug: ' . $cid . "\r\n";
			$extra_headers.= 'X-PHP-Version: '  . rinse($_POST['in']['php_version'])  . "\r\n";
			$extra_headers.= 'X-PHP-Category: ' . rinse($_POST['in']['package_name']) . "\r\n";
			$extra_headers.= 'X-PHP-OS: '       . rinse($_POST['in']['php_os'])       . "\r\n";
			$extra_headers.= 'X-PHP-Status: Open ' . "\r\n";
			$extra_headers.= "Message-ID: <bug-$cid@pear.php.net>";

            if (DEVBOX == false) {
                // mail to package developers
                mail($mailto, "[PEAR-BUG] #$cid [NEW]: $sdesc", $ascii_report."1\n-- \n$dev_extra", $extra_headers, "-fpear-sys@php.net");
                // mail to reporter
                mail($email, "[PEAR-BUG] Bug #$cid: $sdesc", $ascii_report."2\n", "From: PHP Bug Database <$mailfrom>\nX-PHP-Bug: $cid\nMessage-ID: <bug-$cid@pear.php.net>", "-fpear-sys@php.net");
            }
            header("Location: bug.php?id=$cid&thanks=4");
            exit;
        }
    } else {
	    response_header('Report - Problems');
	}
}

if (!package_exists($_GET['package'])) {
	$errors[] = 'Package "' . $_GET['package'] . '" does not exist.';
	response_header('Report - Invalid bug type');
	display_errors($errors);
} else {

	if (!isset($_POST['in'])) {
	    response_header('Report - New');
	    show_bugs_menu($_GET['package']);
?>

<p>Before you report a bug, make sure to search for similar bugs using the
"Bug List" link. Also, read the instructions for <a target="top" href="http://bugs.php.net/how-to-report.php">how to report a bug
that someone will want to help fix</a>.</p>

<p>If you aren't sure that what you're about to report is a bug, you should ask for help using one of the means for support
<a href="/support.php">listed here</a>.</p>

<p><strong>Failure to follow these instructions may result in your bug
simply being marked as "bogus".</strong></p>

<p><strong>If you feel this bug concerns a security issue, eg a buffer overflow, weak encryption, etc, then email
<a href="mailto:pear-group@php.net?subject=%5BSECURITY%5D+possible+new+bug%21">pear-group@php.net</a> who will assess the situation. </strong></p>

<?php
	}

	if ($errors) display_errors($errors);
    
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?package=' . $_GET['package'];?>">
<input type="hidden" name="in[did_luser_search]" value="<?php echo $_POST['in']['did_luser_search'] ? 1 : 0; ?>" />
<table>
 <tr>
  <th style="text-align: right;">Your email address:</th>
  <td colspan="2">
   <input type="text" size="20" maxlength="40" name="in[email]" value="<?php echo clean($_POST['in']['email']);?>" />
  </td>
 </tr><tr>
  <th style="text-align: right;">PHP version:</th>
  <td>
   <select name="in[php_version]"><?php show_version_options($_POST['in']['php_version']);?></select>
  </td>
 </tr><tr>
  <th style="text-align: right;">Package affected:</th>
  <td colspan="2">
    <?php
    if (!empty($_GET['package'])) { ?>
        <input type="hidden" name="in[package_name]" value="<?php echo $_GET['package'];?>" />
        <?php echo $_GET['package'];
    } else {
    ?>
    <select name="in[package_name]"><?php show_types(null,0,$_GET['package']);?></select>
    <?php } ?>
  </td>
 </tr><tr>
  <th style="text-align: right;">Bug Type:</th>
  <td colspan="2">
    <select name="in[bug_type]">
        <option value="Bug">Bug</option>
        <option value="Feature/Change Request">Feature/Change Request</option>
    </select>
  </td>
 </tr><tr>
  <th style="text-align: right;">Operating system:</th>
  <td colspan="2">
   <input type="text" size="20" maxlength="32" name="in[php_os]" value="<?php echo clean($_POST['in']['php_os']);?>" />
  </td>
 </tr><tr>
  <th style="text-align: right;">Summary:</th>
  <td colspan="2">
   <input type="text" size="40" maxlength="79" name="in[sdesc]" value="<?php echo clean($_POST['in']['sdesc']);?>" />
  </td>
  </tr><tr>
  <th style="text-align: right;">Password:</th>
  <td>
   <input type="password" size="20" maxlength="20" name="in[passwd]" value="<?php echo clean($_POST['in']['passwd']);?>" />
  </td>
  <td><font size="-2">
    You may enter any password here, which will be stored for this bug report.
    This password allows you to come back and modify your submitted bug report
    at a later date. <!-- [<a href="/bug-pwd-finder.php">Lost a bug password?</a>] -->
  </font></td>
 </tr>
</table>
<table>
 <tr>
  <td style="vertical-align: top;" colspan="2">
   <font size="-1">
   Please supply any information that may be helpful in fixing the bug:
   <ul>
    <li>The version number of the PEAR package or files you are using.</li>
    <li>A short script that reproduces the problem.</li>
    <li>The list of modules you compiled PHP with (your configure line).</li>
    <li>Any other information unique or specific to your setup.</li>
    <li>Any changes made in your php.ini compared to php.ini-dist (<strong>not</strong> your whole php.ini!)</li>
    <li>A <a href="http://bugs.php.net/bugs-generating-backtrace.php">gdb backtrace</a>.</li>
   </ul>
   </font>
  </td>
 </tr>
 <tr>
  <td style="vertical-align: top;">
   <strong>Description:</strong><br />
   <font size="-1">
   </font>
  </td>
  <td>
   <textarea cols="60" rows="15" name="in[ldesc]"><?php echo clean($_POST['in']['ldesc']);?></textarea>
  </td>
 </tr>
 <tr>
  <td style="vertical-align: top;">
   <strong>Reproduce code:</strong><br />
   <font size="-1">
    Please <strong>do not</strong> post more than 20 lines of source code.<br />
    If the code is longer than 20 lines, provide a URL to the source<br />
    code that will reproduce the bug.
   </font>
  </td>
  <td style="vertical-align: top;">
   <textarea cols="60" rows="15" name="in[repcode]"><?php echo clean($_POST['in']['repcode']);?></textarea>
  </td>
 </tr>
 <tr>
  <td style="vertical-align: top;">
   <strong>Expected result:</strong><br />
   <font size="-1">
    What do you expect to happen or see when you run the code above ?<br />
   </font>
  </td>
  <td style="vertical-align: top;">
   <textarea cols="60" rows="15" name="in[expres]"><?php echo clean($_POST['in']['expres']);?></textarea>
  </td>
 </tr>
 <tr>
  <td style="vertical-align: top;">
   <strong>Actual result:</strong><br />
   <font size="-1">
    This could be a <a href="http://bugs.php.net/bugs-generating-backtrace.php">backtrace</a> for example.<br />
    Try to keep it as short as possible without leaving anything relevant out.
   </font>
  </td>
  <td style="vertical-align: top;">
   <textarea cols="60" rows="15" name="in[actres]"><?php echo clean($_POST['in']['actres']);?></textarea>
  </td>
 </tr>
 <tr>
  <td colspan="2">
   <div align="center"><input type="submit" value="Send bug report" /></div>
  </td>
 </tr>
</table>
</form>
<?php
}
response_footer();
?>