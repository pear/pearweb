<?php

/**
 * Produce statistical reports about bugs
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
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain common includes
 */
require_once './include/prepend.inc';

error_reporting(E_ALL ^ E_NOTICE);

$category  = $_GET['category'];
$developer = $_GET['developer'];
$rev       = $_GET['rev'];
$sort_by   = $_GET['sort_by'];

$dbh->setFetchMode(DB_FETCHMODE_ASSOC);

response_header('Bugs Stat');

switch ($site) {
    case 'pear':
        $type = ' WHERE p.package_type = '.$dbh->quoteSmart('pear');
        break;
    case 'pecl':
        $type = ' WHERE p.package_type = '.$dbh->quoteSmart('pecl');
        break;
    default:
        $type = ' WHERE p.package_type LIKE \'%\'';
        break;
}

$sql   = '';
$where = '';
if ($_GET['category'] && $_GET['category'] != '') {
    !empty($_GET['developer']) ? $and = ' AND ' : '';
    $where .= ' AND categories.name = ' .  $dbh->quoteSmart($_GET['category']) . $and;
    $sql .= ' LEFT JOIN categories ON p.category = categories.id';
}

if ($_GET['developer'] && $_GET['developer'] != '') {
    $where .= ' AND maintains.handle = ' .  $dbh->quoteSmart($_GET['developer']);
    $sql .= ' LEFT JOIN maintains ON p.id = maintains.package';

}

$where_clause = '';
if (empty($_GET['bug_type']) || $_GET['bug_type'] == 'All') {
    $bug_type = '';
} else {
    $bug_type = $_GET['bug_type'];
    $where_clause = " AND bug_type = '" . escapeSQL($bug_type) . "'";
}

$query = 'SELECT p.name FROM packages p '.$sql.$type.$where.' GROUP BY p.name';
$result = $dbh->getAll($query);

if ($_GET['developer'] == '' && $_GET['category'] == '') {
    $result[] = array('name' => 'Bug System');
    $result[] = array('name' => 'Documentation');
    $result[] = array('name' => 'Web Site');
    if ($site == 'pear') {
        $result[] = array('name' => 'PEPr');
    }
}

foreach ($result as $package) {
    $query = 'SELECT status, package_name
            FROM bugdb
            WHERE package_name = '.$dbh->quoteSmart($package['name']).$where_clause.'
            GROUP BY id';
    $result1 = $dbh->query($query);
    
    $package_name['all'][$package['name']]['total'] = 0;
    while ($row = $result1->fetchRow()) {
        $package_name['all'][$package['name']]['total']++;
        $status_str = strtolower($row['status']);
        $package_name[$status_str][$package['name']]++;
        $package_name[$status_str]['all']++;
        $status[$row['status']]++;
        $total++;
    }
}

if ($total > 0) {
    /* prepare for sorting by bug report count */
    foreach($package_name['all'] as $name => $value) {
        if (!isset($package_name['closed'][$name])) {      $package_name['closed'][$name]      = 0; }
        if (!isset($package_name['bogus'][$name])) {       $package_name['bogus'][$name]       = 0; }
        if (!isset($package_name['open'][$name])) {        $package_name['open'][$name]        = 0; }
        if (!isset($package_name['critical'][$name])) {    $package_name['critical'][$name]    = 0; }
        if (!isset($package_name['analyzed'][$name])) {    $package_name['analyzed'][$name]    = 0; }
        if (!isset($package_name['verified'][$name])) {    $package_name['verified'][$name]    = 0; }
        if (!isset($package_name['suspended'][$name])) {   $package_name['suspended'][$name]   = 0; }
        if (!isset($package_name['duplicate'][$name])) {   $package_name['duplicate'][$name]   = 0; }
        if (!isset($package_name['assigned'][$name])) {    $package_name['assigned'][$name]    = 0; }
        if (!isset($package_name['no feedback'][$name])) { $package_name['no feedback'][$name] = 0; }
        if (!isset($package_name['feedback'][$name])) {    $package_name['feedback'][$name]    = 0; }
    }
    
    if (!isset($sort_by)) { 
        $sort_by = 'open'; 
    }   
    if (!isset($_GET['rev'])) { 
        $rev = 1; 
    }
    
    if ($_GET['rev'] == 1) {
        arsort($package_name[$sort_by]);
    } else {
        asort($package_name[$sort_by]);
    }
    reset($package_name);
}

function bugstats($status, $name)
{
    global $package_name;

    if ($package_name[$status][$name] > 0) {
        return '<a href="search.php?cmd=display&amp;status=' . ucfirst($status) . ($name == 'all' ? '' : '&amp;package_name[]=' . urlencode($name)) . '&amp;by=Any&amp;limit=10'.$string.'">' . $package_name[$status][$name] . "</a>\n";
    }
}


function sort_url ($name)
{
    global $sort_by,$rev,$phpver,$category,$developer;

    if ($name == $sort_by) {
        $reve = ($rev == 1) ? 0 : 1;        
    } else {
        $reve = 1;
    }
    if ($sort_by != $name) {
        $attr = '';
    } else {
        $attr = 'class="bug_stats_choosen"';
    }
    return '<a href="./stats.php?sort_by='.urlencode($name).'&amp;rev='.$reve.'&amp;category='.$category.'&amp;developer='.$developer.'" '.$attr.'>'.ucfirst($name).'</a>';

}

function package_link ($name)
{
    $filter = array('Bug System',
                    'Web Site',
                    'Documentation',
                    'PEPr');
    if (!in_array($name, $filter)) {
        return '<a href="/package/'.$name.'" style="color: black;">'.$name.'</a>';
    } else {
        return $name;
    }
}

function display_stat_header($total, $grandtotal = true) {
    global $dbh;
    if ($grandtotal) {
        $stat_head = '<tr class="bug_header"><td><strong>Name</strong></td>
            <td>&nbsp;</td>';
    } else {
        $stat_head = '<tr class="bug_header"><td>&nbsp;</td><td>&nbsp;</td>';
    }
    $stat_head .= '<td><strong>' . sort_url('closed')      . '</strong></td>
    <td><strong>' . sort_url('open')        . '</strong></td>
    <td><strong>' . sort_url('critical')    . '</strong></td>
    <td><strong>' . sort_url('verified')    . '</strong></td>
    <td><strong>' . sort_url('analyzed')    . '</strong></td>
    <td><strong>' . sort_url('assigned')    . '</strong></td>
    <td><strong>' . sort_url('suspended')   . '</strong></td>
    <td><strong>' . sort_url('duplicate')   . '</strong></td>
    <td><strong>' . sort_url('feedback')    . '</strong></td>
    <td><strong>' . sort_url('no feedback') . '</strong></td>
    <td><strong>' . sort_url('bogus')       . '</strong></td>
    </tr>' . "\n";
    return $stat_head;
}

/**
* Fetch list of all categories
*/
echo '<table style="font-size: 90%;">'."\n";
    $res = category::listAll();
    $_SERVER['QUERY_STRING'] ? $query_string = '?' . $_SERVER['QUERY_STRING'] : '';
echo '<tr><td colspan="13"> 
        <form method="get" action="/bugs/stats.php' . $query_string . '">
        <div>
        <strong>Category:</strong> 
        <select name="category" id="category" onchange="this.form.submit(); return false;">';
            $_GET['category'] == '' ? $selected = ' selected="selected"' : $selected = '';
            echo '<option value=""' . $selected . '>All</option>'."\n";
                foreach ($res as $row) {
                    $_GET['category'] == $row['name'] ? $selected = ' selected="selected"' : $selected = '';
                    echo '<option value="' . $row['name'] . '"' . $selected .'>' . $row['name'] . '</option>'."\n";
                }
echo    '</select>
        <strong>Developer:</strong> 
        <select name="developer" id="developers" onchange="this.form.submit(); return false;">'."\n";

/**
* Fetch list of users/maintainers
*/
$users = $dbh->query('SELECT u.handle AS handle, u.name AS name FROM users u, maintains m WHERE u.handle = m.handle 
                        GROUP BY handle ORDER BY u.name');
    $_GET['developer'] == '' ? $selected = ' selected="selected"' : $selected = '';
    echo '<option value=""' . $selected . '>All</option>'."\n";
    while ($u = $users->fetchRow(DB_FETCHMODE_ASSOC)) {
        $_GET['developer'] == $u['handle'] ? $selected = ' selected="selected"' : $selected = '';
        echo '<option value="' . $u['handle'] . '"' . $selected . '>' . $u['name'] . '</option>'."\n";
    }
    echo '</select>   <strong>Bug Type:</strong><select id="bug_type" name="bug_type" onchange="this.form.submit(); return false;">';
            show_type_options($_GET['bug_type'], true);
    echo '</select>
        <input type="submit" name="submitStats" value="Search" />
        </div>
        </form></td></tr></table>' . "\n";
    echo '<table style="width: 100%;">'."\n";

// Exit if there are no bugs for this version
if ($total == 0) {
    echo '<tr><td><p>No bugs found</p></td></tr></table>' . "\n";
    response_footer();
    exit;
}
    
echo display_stat_header($total, true);

echo '<tr><td class="bug_head"><strong>All</strong></td>
    <td class="bug_bg1">' . $total . '</td>
    <td class="bug_bg2">'. bugstats('closed',      'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('open',        'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('critical',    'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('verified',    'all') .'&nbsp;</td>  
    <td class="bug_bg2">'. bugstats('analyzed',    'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('assigned',    'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('suspended',   'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('duplicate',   'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('feedback',    'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('no feedback', 'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('bogus',       'all') .'&nbsp;</td>
    </tr>' . "\n";

$stat_row = 1;
foreach ($package_name[$sort_by] as $name => $value) {
    if ($name != 'all') {
        /* Output a new header row every 40 lines */
        if (($stat_row++ % 40) == 0) { 
            echo display_stat_header($total, false);
        }
        echo '<tr><td class="bug_head">
            <strong>' . package_link($name) . '</strong></td>
            <td class="bug_bg1">'. $package_name['all'][$name]['total'] .'</td>
            <td class="bug_bg2">'. bugstats('closed',      $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('open',        $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('critical',    $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('verified',    $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('analyzed',    $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('assigned',    $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('suspended',   $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('duplicate',   $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('feedback',    $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('no feedback', $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('bogus',       $name) .'&nbsp;</td>
            </tr>' . "\n";
    }
}

echo "</table>\n";

$dbh->setFetchMode(DB_FETCHMODE_ORDERED);

response_footer();
?>
