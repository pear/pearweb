<?php

/**
 * Contains functions and variables used throughout the bug system
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */


/*
 * DEFINE VARIABLES ============================
 */

/* update include/bugs/pear-bug-accountrequest.php if these change! */
// used in mail_bug_updates(), below, and class for search results
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
    'Spam'        => 'Spm',
);

$types = array(
    'Bug'                     => 'Bug',
    'Feature/Change Request'  => 'Req',
    'Documentation Problem'   => 'Doc',
);

// Used in show_state_options()
$state_types =  array (
    'Open'         => 2,
    'Closed'       => 2,
    'Duplicate'    => 1,
    'Critical'     => 1,
    'Assigned'     => 1,
    'Not Assigned' => 0,
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
    'Spam'         => 2,
    'All'          => 0
);

// Items that need bug tracking but aren't real packages
$pseudo_pkgs = array(
    'Web Site',
    'Documentation',
    'Bug System',
);
// Probably remove soon enough, has it's own package
if (SITE == 'pear') {
    $pseudo_pkgs[] = 'PEPr';
}


/*
 * DEFINE FUNCTIONS ============================
 */

/**
 * Escape string so it can be used as HTML
 *
 * If magic_quotes_gpc is on, the slashes are stripped out
 * then the escaping is done.
 *
 * @param string $in  the string to be sanitized
 *
 * @return string  the sanitized string
 *
 * @see txfield()
 */
function clean($in)
{
    return htmlspecialchars($in, ENT_QUOTES, 'ISO-8859-15', false);
}

/**
 * Escape strings so they can be used as literals in queries
 *
 * @param string|array $in  the data to be sanitized.  If it's an array, each
 *                           element is sanitized.
 *
 * @return string|array  the sanitized data
 *
 * @see clean(), txfield()
 */
function escapeSQL($in)
{
    global $dbh;
    if (is_array($in)) {
        $out = array();
        foreach ($in as $key => $value) {
            $out[$key] = $dbh->escapeSimple($value);
        }
        return $out;
    }

    return $dbh->escapeSimple($in);
}

/**
 * Returns the data from the field requested and sanitizes
 * it for use as HTML
 *
 * If the data from a form submission exists, that is used.
 * But if that's not there, the info is obtained from the database.
 *
 * @param string $n  the name of the field to be looked for
 *
 * @return mixed  the data requested
 *
 * @see clean(), txfield()
 */
function field($n, $bug) {
    return clean(isset($_POST['in']) ? $_POST['in'][$n] : $bug[$n]);
}

/**
 * Returns the data from the field requested and sanitizes
 * it for use as plain text
 *
 * If the data from a form submission exists, that is used.
 * But if that's not there, the info is obtained from the database.
 *
 * @param string $n  the name of the field to be looked for
 *
 * @return mixed  the data requested
 *
 * @see clean()
 */
function txfield($n, $bug = null, $in = null)
{
    $one = (isset($in)  && isset($in[$n]))  ? $in[$n]  : false;
    if ($one) {
        return $one;
    }

    $two = (isset($bug) && isset($bug[$n])) ? $bug[$n] : false;
    if ($two) {
        return $two;
    }
}

/**
 * Prints age <option>'s for use in a <select>
 *
 * @param string $current  the field's current value
 *
 * @return void
 */
function show_byage_options($current)
{
    $opts = array(
        '0'   => 'the beginning',
        '1'   => 'yesterday',
        '2'   => '2 days ago',
        '4'   => '4 days ago',
        '7'   => '7 days ago',
        '15'  => '15 days ago',
        '30'  => '30 days ago',
        '90'  => '90 days ago',
    );
    foreach ($opts as $k => $v) {
        $selected = $current == $k ? ' selected="selected"' : '';
        echo '<option value="'.$k.'"' . $selected . ">$v</option>\n";
    }
}

/**
 * Prints a list of <option>'s for use in a <select> element
 * asking how many bugs to display
 *
 * @param int $limit  the presently selected limit to be used as the default
 *
 * @return void
 */
function show_limit_options($limit = 30)
{
    for ($i = 10; $i < 100; $i += 10) {
        $selected = $limit == $i ? ' selected="selected"' : '';
        echo '<option value="' . $i . '"' . $selected . ">$i bugs</option>\n";
    }

    $all = $limit == 'All' ? ' selected="selected"' : '';
    echo '<option value="All"' . $all . ">All</option>\n";
}

/**
 * Prints bug type <option>'s for use in a <select>
 *
 * Options include "Bug", "Documentation Problem" and "Feature/Change Request."
 *
 * @param string $current  bug's current type
 * @param bool   $all      whether or not 'All' should be an option
 *
 * @retun void
 */
function show_type_options($current = 'Bug', $all = false)
{
    global $types;

    if ($all) {
        if (!$current) {
            $current = 'All';
        }
        echo '<option value="All"';
        if ($current == 'All') {
            echo ' selected="selected"';
        }
        echo ">All</option>\n";
    } elseif (!$current) {
        $current = 'Bug';
    }

    foreach ($types as $k => $v) {
        $s = $current == $k ? ' selected="selected"' : '';
        echo '<option value="' . $k . '"' . $s . '>' . $k . "</option>\n";
    }
}

/**
 * Prints bug state <option>'s for use in a <select> list
 *
 * @param string $state      the bug's present state
 * @param int    $user_mode  the 'edit' mode
 * @param string $default    the default value
 *
 * @return void
 */
function show_state_options($state, $user_mode = 0, $default = '')
{
    global $state_types;

    if (!$state && !$default) {
        $state = 'Open';
    } elseif (!$state) {
        $state = $default;
    }

    /* regular users can only pick states with type 2 for unclosed bugs */
    if ($state != 'All' && $state_types[$state] == 1 && $user_mode == 2) {
        /* If state was 'Feedback', set state to 'Open' automatically. */
        if ($state == 'Feedback' || $state == 'No Feedback') {
            echo "<option>Open</option>\n";
        } else {
            echo "<option>$state</option>\n";
        }
        if ($state != 'Bogus') {
            echo "<option>Closed</option>\n";
        }
    } else {
        foreach($state_types as $type => $mode) {
            if ($mode >= $user_mode) {
                echo '<option';
                if ($type == $state) {
                    echo ' selected="selected"';
                }
                echo ">$type</option>\n";
            }
        }
    }
}

/**
 * Prints bug resolution <option>'s for use in a <select> list
 *
 * @param string $current   the bug's present state
 * @param int    $expanded  whether or not a longer explanation should be
 *                          displayed
 *
 * @return void
 */
function show_reason_types($current = '', $expanded = 0)
{
    if ($expanded) {
        echo '<option value=""></option>' . "\n";
    }
    while (list($k,$v) = each($GLOBALS['RESOLVE_REASONS'])) {
        echo '<option value="' . $k . '"';
        if ($current==$k) {
            echo ' selected="selected"';
        }
        echo ">$v[desc]";
        if ($expanded) {
            echo " ($v[status])";
        }
        echo "</option>\n";
    }
}

function show_package_version_options($package, $current)
{
    global $pseudo_pkgs, $dbh;
    if (in_array($package, $pseudo_pkgs, true)) {
        echo '<input type="text" size="20" maxlength="100" name="in[package_version]" id="in[package_version]"
    value="' . clean($current) . '" />';
        return;
    }
    $pid = $dbh->getOne('SELECT id from packages where name = ?', array($package));
    if (!$pid) {
        echo '<input type="text" size="20" maxlength="100" name="in[package_version]" id="in[package_version]"
    value="' . clean($current) . '" />';
        return;
    }
    $candidates = $dbh->getAll('
        SELECT releases.version, releases.state
            FROM releases, packages
            WHERE packages.name = ? and releases.package = packages.id ORDER BY releases.releasedate DESC', array($package));
    $newest = count($candidates) ? $candidates[0] : false;
    if ($newest) {
        $stable = null;
        if ($candidates[0][1] != 'stable') {
            foreach ($candidates as $ver) {
                if ($ver[0] == $newest[0] || $ver[1] != 'stable') {
                    continue;
                }
                $stable = $ver;
                break;
            }
        }
        if (isset($stable)) {
            $versions = array($newest, $stable);
        } else {
            $versions = array($newest);
        }
    } else {
        $versions = array(1 => 1);
    }
    echo '<select name="in[package_version]" id="in[package_version]">' . "\n";
    echo '<option value="">--Please Select--</option>' . "\n";
    $use = 0;

    while (list(,$v) = each($versions)) {
        echo '<option';
        if ($current == $v[0]) {
            echo ' selected="selected"';
        }

        $p = htmlspecialchars($v[0]);
        echo ' value = "' . $p . '">' . $p . ' (' . htmlspecialchars($v[1]) . ")</option>\n";
    }
    echo '<option value="CVS"';
    echo $current == 'CVS' ? ' selected' : '';
    echo ">CVS</option>\n";
    echo '<option value="Unkown"';
    echo $current == 'Unknown' ? ' selected' : '';
    echo ">I have no idea</option>\n";
    echo "<option value=\"earlier\"", ($current == "earlier" ? " selected" : ""),  ">Earlier? Upgrade first!</option>\n";
    echo "</select>\n";
}

/**
 * Prints PHP version number <option>'s for use in a <select> list
 *
 * @param string $current  the bug's current version number
 * @param string $default  a version number that should be the default
 *
 * @return void
 */
function show_version_options($current, $default = '')
{
    $use = 0;

    echo '<option value="">--Please Select--</option>' . "\n";
    include dirname(__FILE__) . '/php_versions.php';
    while (list(,$v) = each($versions)) {
        echo '<option';
        if ($current == $v) {
            echo ' selected="selected"';
            $use++;
        }
        echo '>' . htmlspecialchars($v) . "</option>\n";
    }
    if (!$use && $current) {
        echo '<option selected="selected">' . htmlspecialchars($current) . "</option>\n";
    }

    echo '<option>Irrelevant</option>' . "\n";
    echo '<option value="earlier">Earlier? Upgrade first!</option>' . "\n";
}

/**
 * Prints package name <option>'s for use in a <select> list
 *
 * @param string $current   the bug's present state
 * @param int    $show_any  whether or not 'Any' should be an option.  'Any'
 *                          will only be printed if no $current value exists.
 * @param string $default   the default value
 *
 * @return void
 */
function show_types($current, $show_any, $default = '')
{
    global $dbh;
    static $bug_items;

    if (!isset($bug_items)) {
        $bug_items = array();
        $bug_items['Web Site']      = 'Web Site';
        $bug_items['Bug System']    = '&nbsp;&nbsp;&nbsp;Bug System';
        $bug_items['PEPr']          = '&nbsp;&nbsp;&nbsp;PEPr';
        $bug_items['Documentation'] = 'Documentation';

        $sql = "
            SELECT name FROM packages
            WHERE approved = 1 AND package_type = ?
            ORDER BY name";
        $list = $dbh->getCol($sql, 'name', array(SITE));
        foreach ($list as $name) {
            $bug_items[$name] = $name;
        }
    }

    $use = 0;

    if (!$current && !$default && !$show_any) {
        echo "<option value=\"none\">--Please Select--</option>\n";
    } elseif (!$current && $show_any == 1) {
        $current = 'Any';
    } elseif (!$current) {
        $current = $default;
    }

    foreach ($bug_items as $key => $value) {
        if ($show_any == 1 || $key != 'Any') {
            echo "<option value=\"$key\"";
            if ((is_array($current) && in_array($key, $current)) ||
                ($key == $current))
            {
                echo ' selected="selected"';
            }
            echo ">$value</option>\n";
            if ($key == $current) {
                $use++;
            }
        }
    }
}

/**
 * Prints a series of radio inputs to determine how the search
 * term should be looked for
 *
 * @param string $current   the users present selection
 *
 * @return void
 */
function show_boolean_options($current)
{
    $options = array('any', 'all', 'raw');
    while (list($val, $type) = each($options)) {
        echo '<input type="radio" name="boolean" value="', $val, '"';
        if ($val === $current) {
            echo ' checked="checked"';
        }
        echo " />$type&nbsp;\n";
    }
}

function bug_diff($bug, $in, $previous, $current)
{
    $changed = array();

    if ($in['email'] && (trim($in['email']) != trim($bug['email']))) {
        include_once 'bugs/pear-bugs-utils.php';
        $changed['reported_by']['from'] = $bug['email'];
        $changed['reported_by']['to']   = PEAR_Bugs_Utils::spamProtect(txfield('email', $bug, $in), 'text');
    }

    $fields = array(
        'sdesc'            => 'Summary',
        'status'           => 'Status',
        'bug_type'         => 'Type',
        'package_name'     => 'Package',
        'php_os'           => 'Operating System',
        'package_version'  => 'Package Version',
        'php_version'      => 'PHP Version',
        'assign'           => 'Assigned To'
    );

    foreach (array_keys($fields) as $name) {
        if ($in[$name] && (trim($in[$name]) != trim($bug[$name]))) {
            $changed[$name]['from'] = $bug[$name];
            $changed[$name]['to']   = txfield($name, $bug, $in);
        }
    }

    if ($current != $previous) {
        if (count($previous)) {
            array_walk($previous, create_function('&$a', '$a=$a[0];'));
        }
        $changed['roadmap']['from'] = implode($previous, ', ');
        if (count($current)) {
            array_walk($current, create_function('&$a', '$a=$a[0];'));
        }
        $changed['roadmap']['to'] = implode($current, ', ');
    }

    return $changed;
}

function bug_diff_render_html($diff)
{
    $fields = array(
        'sdesc'            => 'Summary',
        'status'           => 'Status',
        'bug_type'         => 'Type',
        'package_name'     => 'Package',
        'php_os'           => 'Operating System',
        'package_version'  => 'Package Version',
        'php_version'      => 'PHP Version',
        'assign'           => 'Assigned To',
        'roadmap'          => 'Roadmap Versions',
    );

    // make diff output aligned
    $actlength = $maxlength = 0;
    foreach (array_keys($diff) as $v) {
        $actlength = strlen($fields[$v]) + 2;
        $maxlength = ($maxlength < $actlength) ? $actlength : $maxlength;
    }

    $changes = '<div id="changeset">' . "\n";
    $spaces = str_repeat(' ', $maxlength + 1);
    foreach ($diff as $name => $content) {
        // align header content with headers (if a header contains
        // more than one line, wrap it intelligently)
        $field = str_pad($fields[$name] . ':', $maxlength);
        $from  = wordwrap('-'.$field.$content['from'], 72 - $maxlength, "\n$spaces"); // wrap and indent
        $from  = rtrim($from); // wordwrap may add spacer to last line
        $to    = wordwrap('+'.$field.$content['to'], 72 - $maxlength, "\n$spaces"); // wrap and indent
        $to    = rtrim($to); // wordwrap may add spacer to last line
        $changes .= '<span class="removed">' . clean($from) . '</span>' . "\n";
        $changes .= '<span class="added">' .   clean($to) . '</span>' . "\n";
    }
    $changes .= '</div>' . "\n";

    return $changes;
}

/**
 * Send an email notice about bug aditions and edits
 *
 * @param
 *
 * @return void
 */
function mail_bug_updates($bug, $in, $from, $ncomment, $edit = 1, $id, $previous = array(),
    $current = array())
{
    global $tla, $types;

    $changed = bug_diff($bug, $in, $previous, $current);
    $headers = $text = array();

    /* Default addresses */
    include_once 'bugs/pear-bugs-utils.php';
    $pbu = new PEAR_Bugs_Utils;
    $pm  = isset($in['package_name']) ? $in['package_name'] : $bug['package_name'];
    list($mailto, $mailfrom, $Bcc) = $pbu->getPackageMail($pm, $id);

    $headers[] = array(" ID", $bug['id']);

    switch ($edit) {
        case 3:
            $headers[] = array(' Comment by', $from);
            $from = "\"$from\" <$mailfrom>";
            break;
        case 2:
            $from = $pbu->spamProtect(txfield('email', $bug, $in), 'text');
            $headers[] = array(' User updated by', $from);
            $from = "\"$from\" <$mailfrom>";
            break;
        default:
            $headers[] = array(' Updated by', $from);
    }

    $fields = array(
        'email'            => 'Reported By',
        'sdesc'            => 'Summary',
        'status'           => 'Status',
        'bug_type'         => 'Type',
        'package_name'     => 'Package',
        'php_os'           => 'Operating System',
        'package_version'  => 'Package Version',
        'php_version'      => 'PHP Version',
        'assign'           => 'Assigned To',
        'roadmap'          => 'Roadmap Versions',
    );

    foreach ($fields as $name => $desc) {
        $prefix = ' ';
        if (isset($changed[$name])) {
            $headers[] = array("-$desc", $changed[$name]['from']);
            $prefix = '+';
        }

        // only fields that are set get added.
        if ($f = txfield($name, $bug, $in) OR $name == 'roadmap') {
            if ($name == 'email') {
                $f = $pbu->spamProtect($f, 'text');
            }
            $foo = isset($changed[$name]['to']) ? $changed[$name]['to'] : $f;
            $headers[] = array($prefix.$desc, $foo);
        }
    }

    // make header output aligned
    $actlength = $maxlength = 0;
    foreach ($headers as $v) {
        $actlength = strlen($v[0]) + 2;
        $maxlength = ($maxlength < $actlength) ? $actlength : $maxlength;
    }

    // align header content with headers (if a header contains
    // more than one line, wrap it intelligently)
    $header_text = '';
    $spaces = str_repeat(' ', $maxlength + 1);
    foreach ($headers as $v) {
        $hcontent     = wordwrap($v[1], 72 - $maxlength, "\n$spaces"); # wrap and indent
        $hcontent     = rtrim($hcontent); # wordwrap may add spacer to last line
        $header_text .= str_pad($v[0] . ':', $maxlength) . $hcontent . "\n";
    }

    if ($ncomment) {
        $ncomment = preg_replace('#<div id="changeset">(.*)</div>#sUe',
                                 "ltrim(strip_tags('\\1'))", $ncomment);
        $text[] = " New Comment:\n\n" . $ncomment;
    }

    $old_comments = $pbu->getOldComments($bug['id'], empty($ncomment));
    $old_comments = preg_replace('#<div id="changeset">(.*)</div>#sUe',
                                 "ltrim(strip_tags('\\1'))", $old_comments);
    $text[] = $old_comments;

    /* format mail so it looks nice, use 72 to make piners happy */
    $wrapped_text = wordwrap(join("\n",$text), 72);

    /* user text with attention, headers and previous messages */
    $user_text  = "ATTENTION! Do NOT reply to this email!\n" .
                  "To reply, use the web interface found at\n" .
                  "http://" . PEAR_CHANNELNAME . "/bugs/bug.php?id=$bug[id]&edit=2\n\n\n" .
                  $header_text .
                  $wrapped_text;

    /* developer text with headers, previous messages, and edit link */
    $dev_text = 'Edit report at ' .
                "http://" . PEAR_CHANNELNAME . "/bugs/bug.php?id=$bug[id]&edit=1\n\n" .
                $header_text .
                $wrapped_text .
                "\n-- \nEdit this bug report at " .
                "http://" . PEAR_CHANNELNAME . "/bugs/bug.php?id=$bug[id]&edit=1\n";

    if (preg_match('/.*@php\.net\z/', $bug['email'])) {
        $user_text = $dev_text;
    }

    /* send mail if status was changed or there is a comment */
    if ($in['status'] != $bug['status'] || $ncomment != "" || ($current != $previous)) {
        if (isset($in['bug_type']) && $in['bug_type'] != $bug['bug_type']) {
            $subj = $types[$bug['bug_type']] . '->' . $types[$in['bug_type']];
        } else {
            $subj = $types[$bug['bug_type']];
        }

        $old_status = $bug['status'];
        $new_status = $bug['status'];

        if ($in['status'] != $bug['status'] && $edit != 3) {    /* status changed */
            $new_status = $in['status'];
            $subj .= " #{$bug['id']} [{$tla[$old_status]}->{$tla[$new_status]}]: ";
        } elseif ($edit == 3) {             /* comment */
            $subj .= " #{$bug['id']} [Com]: ";
        } else {                            /* status did not change and not comment */
            $subj .= " #{$bug['id']} [{$tla[$bug['status']]}]: ";
        }

        # the user gets sent mail with an envelope sender that ignores bounces
        if (DEVBOX === false) {
            @mail($bug['email'],
                  "[" . SITE_BIG . "-BUG] " . $subj . txfield('sdesc', $bug, $in),
                  $user_text,
                  "From: " . SITE_BIG  . "Bug Database <$mailfrom>\n".
                  "Bcc: $Bcc\n" .
                  "X-PHP-Bug: $bug[id]\n".
                  "In-Reply-To: <bug-$bug[id]@" . PEAR_CHANNELNAME . ">",
                  "-f " . PEAR_BOUNCE_EMAIL);
            # but we go ahead and let the default sender get used for the list

            @mail($mailto,
                  "[" . SITE_BIG . "-BUG] " . $subj . txfield('sdesc', $bug, $in),
                  $dev_text,
                  "From: $from\n".
                  "X-PHP-Bug: $bug[id]\n".
                  "X-PHP-Type: "       . (($edit != 3) ? $in['bug_type']     : $bug['bug_type'])    . "\n" .
                  "X-PHP-PackageVersion: "    . (($edit != 3) ? $in['package_version']  : $bug['package_version']) . "\n" .
                  "X-PHP-Version: "    . (($edit != 3) ? $in['php_version']  : $bug['php_version']) . "\n" .
                  "X-PHP-Category: "   . (($edit != 3) ? $in['package_name'] : $bug['package_name'])    . "\n" .
                  "X-PHP-OS: "         . (($edit != 3) ? $in['php_os']       : $bug['php_os'])      . "\n" .
                  "X-PHP-Status: "     . $new_status . "\n" .
                  "X-PHP-Old-Status: " . $old_status . "\n" .
                  "In-Reply-To: <bug-$bug[id]@" . PEAR_CHANNELNAME . ">",
                  "-f " . PEAR_BOUNCE_EMAIL);
        }
    }

    /* if a developer assigns someone else, let that other person know about it */
    if ($edit == 1 && $in['assign'] && $in['assign'] != $bug['assign']) {
        include_once 'pear-database-user.php';
        $info = user::info($in['assign'], "email");
        $email = $info['email'];

        // If the developer assigns him self then skip
        if ($email == $from) {
            return;
        }

        if (DEVBOX === false) {
            @mail($email,
                  $subj . txfield('sdesc', $bug, $in),
                  wordwrap($in['assign'] . ' you have just been assigned to this bug by ' . $from . "\n\n") .
                  $dev_text,
                  "From: $from\n".
                  "X-PHP-Bug: $bug[id]\n".
                  "In-Reply-To: <bug-$bug[id]@" . PEAR_CHANNELNAME . ">",
                  "-f " . PEAR_BOUNCE_EMAIL);
        }
    }
}

/**
 * Converts any URI's found in the string to hyperlinks
 *
 * @param string $text  the text to be examined
 *
 * @return string  the converted string
 */
function addlinks($text)
{
    $text = preg_replace("/((mailto|http|ftp|nntp|news):.+?)(&gt;|\\s|\\)|\\.\\s|$)/i","<a href=\"\\1\">\\1</a>\\3",$text);
    # what the heck is this for?
    $text = preg_replace("/[.,]?-=-\"/", '"', $text);
    return $text;
}

/**
 * Determine if the given package name is legitimate
 *
 * @param string $type  the name of the package
 *
 * @return bool
 */
function package_exists($type)
{
    global $dbh, $pseudo_pkgs;

    if (empty($type)) {
        return false;
    }
    if (in_array($type, $pseudo_pkgs)) {
        return true;
    }

    $sql = 'SELECT count(id) FROM packages WHERE packages.name = ?';
    $found = $dbh->getOne($sql, array($type));
    return $found == 1 ? true : false;
}

/**
 * Validate an incoming bug report
 *
 * @param
 *
 * @return void
 */
function incoming_details_are_valid($in, $initial = 0, $logged = false)
{
    global $bug, $dbh, $types;

    $errors = array();
    if (empty ($in['package_name']) || $in['package_name'] == 'none') {
        $errors[] = 'Please select an appropriate bug type.';
    } else if (!package_exists($in['package_name'])) {
        $errors[] = 'Please select an appropriate bug type.';
    }

    if ($initial || (!empty($in['email']) && $bug['email'] != $in['email'])) {
        if (!preg_match("/^[.\\w+-]+@[.\\w-]+\\.\\w{2,}\z/i",$in['email'])) {
            $errors[] = 'Please provide a valid email address.';
        }
    }

    if (isset($in['php_version']) && $in['php_version'] == 'earlier') {
        $errors[] = 'Please select a valid PHP version. If your PHP version is too old, please upgrade first and see if the problem has not already been fixed.';
    }

    if (isset($in['package_version']) && $in['package_version'] == 'earlier') {
        $errors[] = 'Please select a valid Package version. If your Package version is too old, please upgrade first and see if the problem has not already been fixed.';
    }

    if (!array_key_exists($in['bug_type'], $types)) {
        $errors[] = 'Please select a valid bug type.';
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

    return $errors;
}

/**
 * Prepare a query string with the search terms
 *
 * @param string $search  the term to be searched for
 *
 * @return array
 */
function format_search_string($search)
{
    // Function will be updated to make results more relevant.
    // Quick hack for indicating ignored words.
    $min_word_len = 3;

    $words = preg_split("/\s+/", $search);
    $ignored = $used = array();
    foreach ($words AS $match) {
        if (strlen($match) < $min_word_len) {
            array_push($ignored, $match);
        } else {
            array_push($used, $match);
        }
    }

    if (BOOLEAN_SEARCH) {
        // require all used words (all)
        if (BOOLEAN_SEARCH === 1) {
            $newsearch = '';
            foreach ($used as $word) {
                $newsearch .= "+$word ";
            }
            return array(" AND MATCH (bugdb.email,sdesc,ldesc) AGAINST ('" . escapeSQL($newsearch) . "' IN BOOLEAN MODE)", $ignored);

        // allow custom boolean search (raw)
        } elseif (BOOLEAN_SEARCH === 2) {
            return array(" AND MATCH (bugdb.email,sdesc,ldesc) AGAINST ('" . escapeSQL($search) . "' IN BOOLEAN MODE)", $ignored);
        }
    }
    // require any of the words (any)
    return array(" AND MATCH (bugdb.email,sdesc,ldesc) AGAINST ('" . escapeSQL($search) . "')", $ignored);
}

/**
 * Print the bug navigation menu
 *
 * The package home page and report new bug links are only displayed
 * if the bug/search being looked at only covers a single package.
 *
 * @param string|array $pkgname  the names of the packages the user is
 *                               presently viewing bugs for
 *
 * @return void
 */
function show_bugs_menu($pkgname, $status = null, $fulllink = false)
{
    global $dbh, $pseudo_pkgs;

    if (empty($pkgname) && !$fulllink) {
        return;
    }

    $pkgname = (array)$pkgname;
    $home    = true;

    foreach ($pkgname as $key => $value) {
        if (!preg_match('/^[A-Za-z0-9_ ]+\z/', $value)) {
            return;
        }
        if (in_array($value, $pseudo_pkgs)) {
            $home = false;
            $pkgname[$key] = htmlspecialchars(strtr($value, ' ', '+'));
        }
    }
    $pkgs = 'package_name[]=' . implode('&amp;package_name[]=', $pkgname);

    echo "\n\n<!-- BEGIN BUG NAV -->\n\n";
    echo '<table width="100%" border="0"><tr><td>' . "\n ";

    if (count($pkgname) == 1 && !$fulllink) {
        if ($home) {
            echo make_link('/package/' . $pkgname[0], 'Package home');
            echo '&nbsp;|&nbsp;';
        }
        echo make_link('/bugs/report.php?package=' . $pkgname[0],
                       'Report new bug');
        echo '&nbsp;|&nbsp;';
    }

    if (count($pkgname) == 1 && !$fulllink) {
        echo make_link('/bugs/search.php?package_name[]=' . $pkgname[0], 'New search');
        echo '&nbsp;|&nbsp;';
        echo make_link('/bugs/roadmap.php?package=' . $pkgname[0], 'Development Roadmap');
    } else {
        echo make_link('/bugs/search.php', 'New search');
    }

    echo "\n</td>\n";

    $style['Open']     = ($status == 'Open')    ?' class="status_active"':'';
    $style['Feedback'] = ($status == 'Feedback')?' class="status_active"':'';
    $style['All']      = ($status == 'All')     ?' class="status_active"':'';
    $style['CRSLR']    = ($status == 'CRSLR')   ?' class="status_active"':'';
    echo '<td style="text-align: right;">' . "\n Status: ";
    if (!$fulllink) {
        $fulllink = '/bugs/search.php?cmd=display&amp;' . $pkgs;
    }
    echo make_link($fulllink .
                   '&amp;status=Open', 'Open', '', $style['Open']);
    echo '&nbsp;|&nbsp;';
    echo make_link($fulllink .
                   '&amp;status=Feedback', 'Feedback', '', $style['Feedback']);
    echo '&nbsp;|&nbsp;';
    echo make_link($fulllink .
                   '&amp;status=All', 'All', '', $style['All']);
    if (!class_exists('package')) {
        require_once 'pear-database-package.php';
    }
    if (count($pkgname) == 1) {
        $releases = package::info($pkgname[0], 'releases');
        if (count($releases)) {
            echo '&nbsp;|&nbsp;';
            echo make_link($fulllink .
                           '&amp;status=CRSLR', 'Closed Since Version ' .
                           key($releases), '', $style['CRSLR'], 'Closed bugs since last release');
        }
    }
    echo "\n</td></tr></table>\n";
    echo '<hr />';
    echo "\n\n<!-- END BUG NAV -->\n\n";
}

/**
 * Send the confirmation mail to confirm a subscription removal
 *
 * @param integer   bug ID
 * @param string    email to remove
 * @param array     bug data
 *
 * @return void
 */
 function unsubscribe_hash($bug_id, $email, $bug)
 {
    global $dbh;

    $now = time();
    $hash = crypt($email . $bug_id, $now);
    $bug_id = (int)$bug_id;

    $query = 'UPDATE bugdb_subscribe
                SET unsubscribe_date = ' . $now . ',
                unsubscribe_hash = ' . "'" . escapeSQL($hash) . "'" .
                ' WHERE bug_id = ' . $bug_id . ' AND email=' ."'" . escapeSQL($email) . "'";

    $res = $dbh->query($query);

    $affected = $dbh->affectedRows($res);

    if ($affected > 0) {
        $hash = urlencode($hash);
        /* user text with attention, headers and previous messages */
        $user_text  = "ATTENTION! Do NOT reply to this email!\n\n" .
                      "A request has been made to remove your subscription to\n" .
                      SITE_BIG . " bug #$bug_id ($bug[sdesc])\n\n" .
                      "To confirm the removal please use this link:\n" .
                      "http://" . PEAR_CHANNELNAME . "/bugs/bug.php?id=$bug[id]&unsubscribe=1&t=$hash\n\n\n";
        if (!DEVBOX) {
            @mail($email,
              "[" . SITE_BIG . "-BUG-unsubscribe] " . $bug['sdesc'],
              $user_text,
              "From: " . SITE_BIG . " Bug Database <$mailfrom>\n".
              "X-PHP-Bug: $bug[id]\n".
              "In-Reply-To: <bug-$bug[id]@" . PEAR_CHANNELNAME . ">",
              '-f ' . PEAR_BOUNCE_EMAIL);
        }
    }
}

/**
 * Remove a subscribtion
 *
 * @param integer   bug ID
 * @param string    hash
 *
 * @return void
 */
 function unsubscribe($bug_id, $hash)
 {
    global $dbh;

    $hash = escapeSQL($hash);
    $bug_id = (int) $bug_id;

    $query = 'SELECT
                bug_id, email, unsubscribe_date, unsubscribe_hash
            FROM
                bugdb_subscribe
            WHERE
                bug_id = ' . $bug_id . '
            AND
                unsubscribe_hash = ' . "'" . $hash . "'
            LIMIT 1";

    $sub = $dbh->getRow($query, DB_FETCHMODE_ASSOC);

    if (!$sub) {
        return false;
    }

    $now = time();
    $requested_on = $sub['unsubscribe_date'];
    /* 24hours delay to answer the mail */
    if (($now - $requested_on) > (24*60*60)) {
        return false;
    }

    $query = 'DELETE FROM bugdb_subscribe WHERE bug_id = ' . $bug_id . '
                AND unsubscribe_hash = ' . "'" . $hash . "'
                AND email = '"  . $sub['email'] . "'";
    $dbh->query($query);
    return true;
 }


class Bug_DataObject
{
    function init()
    {
        require_once 'DB/DataObject.php';
        require_once 'Savant2.php';
        $options = &PEAR::getStaticProperty('DB_DataObject','options');
        $type = extension_loaded('mysqli') ? 'mysqli' : 'mysql';
        $options = array(
            'database'         => PEAR_DATABASE_DSN,
            'schema_location'  => dirname(dirname(dirname(dirname(__FILE__)))) . '/include/bugs',
            'class_location'   => dirname(dirname(dirname(dirname(__FILE__)))) . '/include/DataObject',
            'require_prefix'   => 'DataObject/',
            'class_prefix'     => 'Bugs_DBDataObject_',
        );
    }

    function bugDB($table)
    {
        $a = DB_DataObject::factory($table);
        if (!is_a($a, 'DB_DataObject')) {
            PEAR::raiseError('unknown table "' . $table . '"');
        }
        $a->database('pear');
        return $a;
    }

    function pearDB($table)
    {
        $a = DB_DataObject::factory($table);
        if (!is_a($a, 'DB_DataObject')) {
            PEAR::raiseError('unknown table "' . $table . '"');
        }
        $a->database('pear');
        return $a;
    }

    function getChannel()
    {
        return PEAR_CHANNELNAME;
    }

    /**
     * Some channels have subdirectories, so return the host portion only
     *
     * @return string
     */
    function getHost()
    {
        $a = parse_url('http://' . PEAR_CHANNELNAME);
        return $a['host'];
    }

    /**
     * Return the host in a format that can be used in a regular expression
     *
     * @return string
     */
    function getPregHost()
    {
        return str_replace(array('.'),
            array('\\.'),
            Bug_DataObject::getHost());
    }

    function isDeveloper($email)
    {
        $db = Bug_DataObject::pearDB('users');
        $db->email = $email;
        return $db->find();
    }

    function getPath($uri, $bugs = false)
    {
        if ($bugs) {
            $uri = '/bugs/' . $uri;
        }
        return str_replace(Bug_DataObject::getHost(), '', PEAR_CHANNELNAME) . '/' . $uri;
    }

    /**
     * Creates a link to the bug system
     */
    function link($package, $type = 'list', $linktext = '')
    {
        switch ($type) {
            case 'bugurl':
                return '/bugs/' . $package;
            case 'bugsearchurl':
                return '/bugs/search.php';
            case 'url':
                return '/' . $package;
            case 'normal':
                return '<a href="/' .
                        $package . "\">$linktext</a>";
            case 'listurl':
                return '/bugs/search.php?' .
                       'cmd=display&amp;status=Open&amp;package[]=' .
                        urlencode($package);
            case 'roadmapurl':
                return '/bugs/roadmap.php?' .
                       'package=' .
                        urlencode($package);
            case 'list':
                if (!$linktext) {
                    $linktext = 'Package Bugs';
                }
                return '<a href="/bugs/search.php?' .
                       'cmd=display&amp;status=Open&amp;package[]=' .
                        urlencode($package) . "\">$linktext</a>";
            case 'report':
                if (!$linktext) {
                    $linktext = 'Report a new bug';
                }
                return '<a href="/bugs/report.php?' .
                       'package=' .
                        urlencode($package) . "\">$linktext</a>";
            case 'reporturl':
                return '/bugs/report.php?' .
                       'package=' .
                        urlencode($package);
            case 'packages':
                if (!$linktext) {
                    $linktext = 'Browse Packages';
                }
                return '<a href="/packages.php">' .
                    $linktext . '</a>';
            case 'search':
                if (!$linktext) {
                    $linktext = 'Search Packages';
                }
                return '<a href="/search.php">' .
                    $linktext . '</a>';
            case 'searchurl':
                return '/search.php';
            case 'home':
                return '/' . urlencode($package);
        }
    }

    function template()
    {
        return 'bugs'; // this will be more customizable very shortly
    }

    function getSavant()
    {
        return new Savant2(array(
            'template_path' => PEARWEB_TEMPLATEDIR . Bug_DataObject::template(),
        ));
    }
}
