<?php /* vim: set noet ts=4 sw=4 ft=php : */

// $Id$

/* Email spam protection */
function spam_protect($txt)
{
    $translate = array('@' => ' at ', '.' => ' dot ');

    /* php.net addresses are not protected! */
    if (preg_match('/^(.+)@php\.net/i', $txt)) {
        return $txt;
    } else {
        return strtr($txt, $translate);
    }
}

/* scrub user input so it can be re-output */
function clean($in)
{
    return htmlspecialchars(get_magic_quotes_gpc()?stripslashes($in):$in);
}

/* just rinse out any slashes. :) */
function rinse($in)
{
    return get_magic_quotes_gpc()?stripslashes($in):$in;
}

/**
 * Escape string so it can be used as a literal in a query.
 *
 * If magic_quotes_gpc is on, the slashes are stripped out
 * then the escaping is done.
 */
function escapeSQL($in)
{
    global $dbh;
    if (get_magic_quotes_gpc()) {
        return $dbh->escapeSimple(stripslashes($in));
    } else {
        return $dbh->escapeSimple($in);
    }
}

/* handy function for when you're dealing with user input or a default. */
function oneof()
{
    foreach (func_get_args() as $arg) {
        if ($arg) return $arg;
    }
}

function show_byage_options($current)
{
    $opts = array(
        '0'   => 'the beginning',
        '1'   => 'yesterday',
        '7'   => '7 days ago',
        '15'  => '15 days ago',
        '30'  => '30 days ago',
        '90'  => '90 days ago',
    );
    while (list($k,$v) = each($opts)) {
        echo "<option value=\"$k\"", ($current==$k ? ' selected="selected"' : ''),
             ">$v</option>\n";
    }
}

function show_limit_options($limit=30)
{
    for($i=10;$i<100;) {
        echo "<option value=\"$i\"", ($limit==$i ? ' selected="selected"' : ''),">$i bugs</option>\n";
        $i=$i+10;
    }
    echo '<option value="All"', ($limit=='All' ? ' selected="selected"': ''),">All</option>\n";
}

$state_types =  array (
    'Open'         => 2,
    'Closed'       => 2,
    'Duplicate'    => 2,
    'Critical'     => 1,
    'Assigned'     => 1,
    'Analyzed'     => 1,
    'Verified'     => 1,
    'Suspended'    => 1,
    'Wont fix'     => 1,
    'No Feedback'  => 1,
    'Feedback'     => 1,
    'Old Feedback' => 0,
    'Stale'        => 0,
    'Fresh'        => 0,
    'Bogus'        => 2,
    'All'          => 0
);

function show_state_options($state, $user_mode=0, $default="")
{
    global $state_types;

    if (!$state && !$default) {
        $state = 'Open';
    }
    elseif (!$state) {
        $state = $default;
    }

    /* regular users can only pick states with type 2 for unclosed bugs */
    if($state != 'All' && $state_types[$state] == 1 && $user_mode == 2) {
        /* If state was 'Feedback', set state to 'Open' automatically. */
        if($state == 'Feedback' || $state == 'No Feedback') {
            echo "<option>Open</option>\n";
        } else {
            echo "<option>$state</option>\n";
        }
        if($state != 'Bogus') echo "<option>Closed</option>\n";
    } else {
        foreach($state_types as $type => $mode) {
            if ($mode >= $user_mode) {
                echo "<option";
                if($type == $state) echo ' selected="selected"';
                echo ">$type</option>\n";
            }
        }
    }
}

function show_version_options($current,$default="")
{
    $versions = array('4.3.6', '4.3.5', '4.3.4', '4.3.3', '4.3.2', '4.3.1', '4.3.0',
                    '4.2.2', '4.2.1', '4.2.0',
                    '4CVS-'.date('Y-m-d').' (stable)',
                    '5.0.0RC2 (Release Candidate 2)', '5.0.0RC1 (Release Candidate 1)', '5CVS-'.date('Y-m-d').' (dev)', 'Irrelevant');
    echo "<option value=\"\">--Please Select--</option>\n";
    while (list(,$v) = each($versions)) {
        echo "<option", ($current == $v ? " selected" : ""), ">$v</option>\n";
        if ($current == $v) $use++;
    }
    if (!$use && $current) echo "<option selected>$current</option>\n";
    echo "<option value=\"earlier\">Earlier? Upgrade first!</option>\n";
}

function display_errors($errors)
{
    echo '<div class="errors">';
    if (count($errors) > 1) {
        echo 'You need to do the following before your submission will be accepted:<ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>\n";
        }
        echo '</ul>';
    }
    else {
        echo $errors[0];
    }
    echo '</div>';
}

function display_warnings($warnings)
{
    echo '<div class="warnings">';
    if (count($warnings) > 1) {
        echo "Please notice the following:<ul>\n";
        foreach ($warnings as $warning) {
            echo "<li>$warning</li>\n";
        }
        echo '</ul>';
    }
    else {
        echo $warnings[0];
    }
    echo '</div>';
}

function show_types($current,$show_any,$default="")
{
    include 'bugtypes.inc';

    if (!$current && !$default && !$show_any) {
        echo "<option value=\"none\">--Please Select--</option>\n";
    }
    elseif (!$current && $show_any == 1) {
        $current = 'Any';
    }
    elseif (!$current) {
        $current = $default;
    }

    while (list($key,$value) = each($items)) {
        if ($show_any == 1 || $key != 'Any') {
            echo "<option value=\"$key\"",
            ((is_array($current) && in_array($key, $current)) || ($key == $current) ? " selected" : ""),
            ">$value</option>\n";
            if ($key == $current) $use++;
        }
    }
    if (!$use && $current && !is_array ($current)) {
        echo "<option selected>$current</option>\n";
    }
}

function show_boolean_options($current) {
    $options = array('any', 'all', 'raw');
    while (list($val, $type) = each($options)) {
        echo '<input type="radio" name="boolean" value="', $val, '"';
        if ($val === $current) {
            echo ' CHECKED';
        }
        echo "> $type &nbsp;\n";
    }
}

function mail_bug_updates($bug, $in, $from, $ncomment, $edit=1)
{
    $text = array();
    $headers = array();

    /* Default addresses */
    list($mailto,$mailfrom) = get_package_mail(oneof($in['package_name'],$bug['package_name']));

    /* Get rid of slashes in bug status */
    $bug['status'] = stripslashes($bug['status']);

    $tla = array(
        'Open'        => 'Opn',
        'Bogus'       => 'Bgs',
        'Feedback'    => 'Fbk',
        'No Feedback' => 'NoF',
        'Wont fix'    => 'WFx',
        'Duplicate'   => 'Dup',
        'Critical'    => 'Ctl',
        'Assigned'    => 'Asn',
        'Analyzed'    => 'Ana',
        'Verified'    => 'Ver',
        'Suspended'   => 'Sus',
        'Closed'      => 'Csd',
    );

    $headers[] = array(" ID", $bug['id']);

    switch ($edit) {
    case 3:
        $from = spam_protect(rinse($in['commentemail']));
        $headers[] = array(" Comment by", $from);
        $from = "\"$from\" <$mailfrom>";
        break;
    case 2:
        $from = spam_protect(txfield('email'));
        $headers[] = array(" User updated by", $from);
        $from = "\"$from\" <$mailfrom>";
        break;
    default:
        $headers[] = array(" Updated by", $from);
    }

    if (changed('sdesc')) {
        $headers[] = array("-Summary", $bug['sdesc']);
    }

    $prefix = " ";
    if (changed('email')) {
        $headers[] = array("-Reported By", spam_protect($bug['email']));
        $prefix = "+";
    }
    if ($f = spam_protect(txfield('email'))) {
        $headers[] = array($prefix.'Reported By', $f);
    }

    $fields = array(
        'status'       => 'Status',
        'package_name' => 'Package',
        'php_os'       => 'Operating System',
        'php_version'  => 'PHP Version',
        'assign'       => 'Assigned To'
    );

    foreach ($fields as $name => $desc) {
        $prefix = " ";
        if (changed($name)) {
            $headers[] = array("-$desc", $bug[$name]);
            $prefix = "+";
        }
        /* only fields that are set get added. */
        if ($f = txfield($name)) {
            $headers[] = array($prefix.$desc, $f);
        }
    }

    # make header output aligned
    $maxlength = 0;
    $actlength = 0;
    foreach ($headers as $v) {
        $actlength = strlen($v[0]) + 1;
        $maxlength = (($maxlength < $actlength) ? $actlength : $maxlength);
    }

    # align header content with headers (if a header contains
    # more than one line, wrap it intelligently)
    $header_text = "";
    $spaces = str_repeat(" ", $maxlength + 1);
    foreach ($headers as $v) {
        $hcontent = wordwrap($v[1], 72-$maxlength, "\n$spaces"); # wrap and indent
        $hcontent = rtrim($hcontent); # wordwrap may add spacer to last line
        $header_text .= str_pad($v[0] . ":", $maxlength) . " " . $hcontent . "\n";
    }

    if ($ncomment) {
        $text[] = " New Comment:\n\n".stripslashes($ncomment);
    }

    $text[] = get_old_comments($bug['id'], empty($ncomment));

    /* format mail so it looks nice, use 72 to make piners happy */
    $wrapped_text = wordwrap(join("\n",$text), 72);

    /* user text with attention, headers and previous messages */
    $user_text  = "ATTENTION! Do NOT reply to this email!\n" .
                  "To reply, use the web interface found at\n" .
                  "http://pear.php.net/bugs/bug.php?id=$bug[id]&edit=2\n\n\n" .
                  $header_text .
                  $wrapped_text;

    /* developer text with headers, previous messages, and edit link */
    $dev_text = ' Edit report at:   ' .
                "http://pear.php.net/bugs/bug.php?id=$bug[id]&edit=1\n" .
                $header_text .
                $wrapped_text .
                "\n-- \nEdit this bug report at " .
                "http://pear.php.net/bugs/bug.php?id=$bug[id]&edit=1\n";

    /* send mail if status was changed or there is a comment */
    if ($in[status] != $bug[status] || $ncomment != "") {

        $old_status = $bug['status'];
        $new_status = $bug['status'];

        if ($in[status] != $bug[status] && $edit != 3) {    /* status changed */
            $new_status = $in['status'];
            $subj = "#{$bug['id']} [{$tla[$old_status]}->{$tla[$new_status]}]: ";
        } elseif ($edit == 3) {             /* comment */
            $subj = "#{$bug['id']} [Com]: ";
        } else {                            /* status did not change and not comment */
            $subj = "#{$bug['id']} [{$tla[$bug['status']]}]: ";
        }

        # the user gets sent mail with an envelope sender that ignores bounces
        if (DEVBOX == false) {
            @mail($bug[email],
                  "[PEAR-BUG] Bug " . rinse($subj) . txfield('sdesc'),
                  $user_text,
                  "From: PHP Bug Database <$mailfrom>\n".
                  "X-PHP-Bug: $bug[id]\n".
                  "In-Reply-To: <bug-$bug[id]@pear.php.net>",
                  "-fbounces-ignored@php.net");
            # but we go ahead and let the default sender get used for the list
            @mail($mailto,
                  "[PEAR-BUG] " . rinse($subj) . txfield('sdesc'),
                  $dev_text,
                  "From: $from\n".
                  "X-PHP-Bug: $bug[id]\n".
                  "X-PHP-Version: "    . stripslashes((($edit != 3) ? $in['php_version']  : $bug['php_version'])) . "\n" .
                  "X-PHP-Category: "   . stripslashes((($edit != 3) ? $in['package_name'] : $bug['package_name']))    . "\n" .
                  "X-PHP-OS: "         . stripslashes((($edit != 3) ? $in['php_os']       : $bug['php_os']))      . "\n" .
                  "X-PHP-Status: "     . stripslashes($new_status) . "\n" .
                  "X-PHP-Old-Status: " . stripslashes($old_status) . "\n" .
                  "In-Reply-To: <bug-$bug[id]@pear.php.net>",
                  "-fpear-sys@php.net");
        }
    }

    /* if a developer assigns someone else, let that other person know about it */
    if ($edit == 1 && $in['assign'] && $in['assign'] != $bug['assign']) {

        $info = user::info($in['assign'], "email");
        $email = $info['email'];

        if ($email == $from) {
            return;
        }

        if (DEVBOX == false) {
            @mail($email,
                  rinse($subj) . txfield('sdesc'),
                  wordwrap($in['assign'] . ' you have just been assigned to this bug by ' . $from . "\n\n") .
                  $dev_text,
                  "From: $from\n".
                  "X-PHP-Bug: $bug[id]\n".
                  "In-Reply-To: <bug-$bug[id]@pear.php.net>",
                  "-fpear-sys@php.net");
        }
    }

}

function changed($n) {
    return $_POST['in'][$n]
        && stripslashes(trim($_POST['in'][$n])) != trim($GLOBALS['bug'][$n]);
}

function field($n) {
    return oneof(clean($_POST['in'][$n]),
                 htmlspecialchars($GLOBALS['bug'][$n]));
}

function format_date($date) {
  if (date("Y") != date("Y", $date)) {
    return date("j M Y g:ia T", $date);
  }
  return date("j M g:ia T", $date);
}

function txfield($n)
{
    return oneof(rinse($_POST['in'][$n]),
                 $GLOBALS['bug'][$n]);
}

function get_old_comments ($bug_id, $all = 0)
{
    global $dbh;
    $divider = str_repeat("-", 72);
    $max_message_length = 10 * 1024;
    $max_comments = 5;
    $output = ""; $count = 0;

    $res =& $dbh->query("SELECT ts, email, comment FROM bugdb_comments WHERE bug=$bug_id ORDER BY ts DESC");

    # skip the most recent unless the caller wanted all comments
    if (!$all) {
        $row =& $res->fetchRow(DB_FETCHMODE_ORDERED);
        if (!$row) return "";
    }

    while (($row =& $res->fetchRow(DB_FETCHMODE_ORDERED)) && strlen($output) < $max_message_length && $count++ < $max_comments) {
        $output .= "[$row[0]] ". spam_protect($row[1]) ."\n\n$row[2]\n\n$divider\n\n";
    }

    if (strlen($output) < $max_message_length && $count < $max_comments) {
        $res =& $dbh->query("SELECT ts1,email,ldesc FROM bugdb WHERE id=$bug_id");
        if (!$res) return $output;
        $row =& $res->fetchRow(DB_FETCHMODE_ORDERED);
        if (!$row) return $output;
        return ("\n\nPrevious Comments:\n$divider\n\n" . $output . "[$row[0]] ". spam_protect($row[1]) ."\n\n$row[2]\n\n$divider\n\n");
    }
    else {
        return ("\n\nPrevious Comments:\n$divider\n\n" . $output . "The remainder of the comments for this report are too long. To view\nthe rest of the comments, please view the bug report online at\n    http://pear.php.net/bugs/bug.php?id=$bug_id\n");
    }

    return "";
}

function addlinks($text)
{
    $text = htmlspecialchars($text);
    $text = preg_replace("/((mailto|http|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/i","<a href=\"\\1\">\\1</a>\\3",$text);
    # what the heck is this for?
    $text = preg_replace("/[.,]?-=-\"/", '"', $text);
    return $text;
}
/* validate a package */
function package_exists ($type)
{
    global $dbh;

    if (empty($type)) {
        return false;
    }
    if (in_array($type, array('Web Site', 'Documentation', 'Bug System', 'PEPr'))) {
        return true;
    }
    $found = $dbh->getOne('SELECT count(*) FROM packages WHERE packages.name=' . "'" . $type ."'");
    return $found == 1 ? true : false;
}

/* validate an incoming bug report */
function incoming_details_are_valid ($in, $initial=0)
{
    global $bug, $dbh;

    $errors = array();
    if (empty ($in['package_name']) || $in['package_name'] == 'none') {
        $errors[] = 'Please select an appropriate bug type.';
    } else if (!package_exists($in['package_name'])) {
        $errors[] = 'Please select an appropriate bug type.';
    }

    if ($initial || (!empty($in[email]) && $bug[email] != $in[email])) {
        if (!preg_match("/[.\\w+-]+@[.\\w-]+\\.\\w{2,}/i",$in['email'])) {
            $errors[] = 'Please provide a valid email address.';
        }
    }


    if (isset($in['php_version']) && $in['php_version'] == 'earlier') {
        $errors[] = 'Please select a valid PHP version. If your PHP version is too old, please upgrade first and see if the problem has not already been fixed.';
    }

    if (empty($in['php_version'])) {
        $errors[] = 'Please select a valid PHP version.';
    }

    if (empty($in['sdesc'])) {
        $errors[] = 'You must supply a short description of the bug you are reporting.';
    }

    if ($initial && empty($in['ldesc'])) {
        $errors[] = 'You must supply a long description of the bug you are reporting.';
    }

    if ($initial && empty($in['passwd'])) {
        $errors[] = 'You must supply a password for this bug report.';
    }

    return $errors;
}

function get_package_mail($package_name)
{
    // XXX Change to pear-bugs when it gets opened
    //$to[] = "pear-dev@lists.php.net";

    switch ($package_name) {
        case 'Web Site':
            return array('pear-webmaster@lists.php.net', 'pear-webmaster@lists.php.net');
        case 'Bug System':
            return array('pear-dev@lists.php.net', 'pear-dev@lists.php.net');
        case 'Documentation':
            return array('pear-doc@lists.php.net', 'pear-doc@lists.php.net');
    }
    $maintainers = package::info($package_name, 'authors');
    $to = array();
    foreach ($maintainers as $data) {
        $to[] = $data['email'];
    }
    return array(implode(', ', $to), 'pear-dev@lists.php.net');
}

function get_row_color($row)
{
    if ($row['package_name']=='Feature/Change Request') {
        return '#aaaaaa';
    }
    switch($row['status']) {
        case 'Open':
            return '#ffbbaa';
            break;
        case 'Critical':
            return '#ff0000';
            break;
        case 'Closed':
            return '#aaffbb';
            break;
        case 'No Feedback':
            return '#bbffcc';
            break;
        case 'Suspended':
            return '#ffccbb';
            break;
        case 'Assigned':
            return '#bbaaff';
            break;
        case 'Feedback':
            return '#bbeeff';
            break;
        case 'Analyzed':
            return '#99bbaa';
            break;
        case 'Duplicate':
            return '#bbbbbb';
            break;
        default:
            return '#aaaaaa';
            break;
    }
}

function format_search_string($search)
{
    // Function will be updated to make results more relevant.
    // Quick hack for indicating ignored words.

    global $mysql4;

    $min_word_len=3;

    $words = preg_split("/\s+/", stripslashes($search));
    $ignored = $used = array();
    foreach($words AS $match) {
        if(strlen($match) < $min_word_len) {
            array_push($ignored, $match);
        } else {
                array_push($used, $match);
        }
    }

    if ($mysql4 && BOOLEAN_SEARCH) {
        // require all used words (all)
        if (BOOLEAN_SEARCH === 1) {
            $newsearch = '';
            foreach ($used as $word) {
                $newsearch .= "+$word ";
            }
            return array(" AND MATCH (bugdb.email,sdesc,ldesc) AGAINST ('" . addslashes($newsearch) . "' IN BOOLEAN MODE)", $ignored);

        // allow custom boolean search (raw)
        } elseif (BOOLEAN_SEARCH === 2) {
            return array(" AND MATCH (bugdb.email,sdesc,ldesc) AGAINST ('" . addslashes($search) . "' IN BOOLEAN MODE)", $ignored);
        }
    }
    // require any of the words (any)
    return array(" AND MATCH (bugdb.email,sdesc,ldesc) AGAINST ('" . addslashes($search) . "')", $ignored);
}

// PEAR Related only
function show_bugs_menu($pkgname)
{
    global $dbh;

    if (empty($pkgname) ||  in_array($pkgname, array('Web Site', 'Documentation', 'Bug System'))) {
        return;
    }
    $found = $dbh->getOne('SELECT count(*) FROM packages WHERE packages.name='."'$pkgname'");
    if ($found!=1)
    {
        return;
    }

    echo '<table width="100%" align="center" border="0"><tr><td>';
    echo make_link('/package/' . $pkgname, 'Package home');
    echo delim();
    echo make_link('/bugs/report.php?package='.$pkgname, 'Report a new bug');
    echo delim();
    echo make_link('/bugs/search.php?cmd=display&package_name[]='.$pkgname.'&amp;status=Open', 'Bug list');
    if (preg_match('/search.php/', $_SERVER['PHP_SELF'])) {
        // This could be changes by a SELECT box and JS
        echo '</td><td style="text-align: right;">Status: ';
        echo make_link('/bugs/search.php?cmd=display&package_name[]='.$pkgname.'&amp;status=Open', 'Open');
        echo delim();
        echo make_link('/bugs/search.php?cmd=display&package_name[]='.$pkgname.'&amp;status=All', 'All');
    }
    echo '</td></tr></table>';
    echo '<hr />';
}

?>
