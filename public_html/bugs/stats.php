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

response_header('Bugs Stat');

$sql .= 'LEFT JOIN packages ON packages.name = bugdb.package_name ';
$where = '';
if (($_GET['category'] && $_GET['category'] != '') 
    || ($_GET['developer'] && $_GET['developer'] != '')) {
    $where = 'WHERE';
}
if ($_GET['category'] && $_GET['category'] != '') {
    !empty($_GET['developer']) ? $and = ' AND ' : '';
    $where .= ' categories.name = ' .  $dbh->quoteSmart($_GET['category']) . $and;
    $sql .= ' LEFT JOIN categories ON packages.category = categories.id';
}

if ($_GET['developer'] && $_GET['developer'] != '') {
    $where .= ' maintains.handle = ' .  $dbh->quoteSmart($_GET['developer']);
    $sql .= ' LEFT JOIN maintains ON packages.id = maintains.package';

}
$where == '' ? $extra = 'WHERE ' : $extra = 'AND ';
switch ($site) {
    case 'pear':
        $type = $extra.'packages.package_type = '.$dbh->quoteSmart('pear') . ' 
                        OR bugdb.package_name IN ('.$dbh->quoteSmart('Bug System').','.$dbh->quoteSmart('Web Site').',
                                                  '.$dbh->quoteSmart('Documentation').', '.$dbh->quoteSmart('PEPr').')';
        break;
        
    case 'pecl':
        $type = $extra.'packages.package_type = '.$dbh->quoteSmart('pecl') . ' 
                        OR bugdb.package_name IN ('.$dbh->quoteSmart('Bug System').','.$dbh->quoteSmart('Web Site').',
                                                  '.$dbh->quoteSmart('Documentation').')';
        break;
        
    default:
        $type = '';
        break;
}

$query = 'SELECT bugdb.status, bugdb.package_name, bugdb.email, bugdb.php_version, bugdb.php_os 
        FROM bugdb
        '.$sql.'
        ' . $where . $type .'
         GROUP BY bugdb.id ORDER BY bugdb.package_name ASC';

$result = $dbh->query($query);

while ($row = $result->fetchRow()) {
    $package_name['all'][$row['package_name']]++;
    $status_str = strtolower($row['status']);
    $package_name[$status_str][$row['package_name']]++;
    $package_name[$status_str]['all']++;
    $email[$row['email']]++;
    $php_version[$row['php_version']]++;
    $php_os[$row['php_os']]++;
    $status[$row['status']]++;
    $total++;
}

function bugstats($status, $name)
{
    global $package_name;

    if ($package_name[$status][$name] > 0) {
        return '<a href="search.php?cmd=display&amp;status=' . ucfirst($status) . ($name == 'all' ? '' : '&amp;package_name[]=' . urlencode($name)) . '&amp;by=Any&amp;limit=10'.$string.'">' . $package_name[$status][$name] . "</a>\n";
    }
}

echo "<table>\n";
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
    
    if (!isset($_GET['sort_by'])) { $_GET['sort_by'] = 'open'; }   
    if (!isset($_GET['rev'])) { $_GET['rev'] = 1; }
    
    if ($rev == 1) {
        arsort($package_name[$_GET['sort_by']]);
    } else {
        asort($package_name[$_GET['sort_by']]);
    }
    reset($package_name);
}

function sort_url ($name)
{
    global $sort_by,$rev,$phpver;

    if ($type == $_GET['sort_by']) {
        $reve = ($_GET['rev'] == 1) ? 0 : 1;        
    } else {
        $reve = 1;
    }
    return '<a href="./stats.php?sort_by='.urlencode($name).'&amp;rev='.$reve.'">'.ucfirst($name).'</a>';
}

/**
* Fetch list of all categories
*/
    $res = category::listAll();
    $_SERVER['QUERY_STRING'] ? $query_string = '?' . $_SERVER['QUERY_STRING'] : '';
echo '<tr><td colspan="10"> 
        <form method="get" action="/bugs/stats.php' . $query_string . '">
        <div>
        <strong>Category:</strong> 
        <select name="category" id="category" onchange="this.form.submit();">';
            $_GET['category'] == '' ? $selected = ' selected="selected"' : $selected = '';
            echo '<option value=""' . $selected . '>All</option>'."\n";
                foreach ($res as $row) {
                    $_GET['category'] == $row['name'] ? $selected = ' selected="selected"' : $selected = '';
                    echo '<option value="' . $row['name'] . '"' . $selected .'>' . $row['name'] . '</option>'."\n";
                }
echo    '</select>
        <strong>Developer:</strong> 
        <select name="developer" id="developers" onchange="this.form.submit();">'."\n";

/**
* Fetch list of users/maintainers
*/
$users = $dbh->query('SELECT u.handle, u.name FROM users u, maintains m WHERE u.handle = m.handle 
                        GROUP BY handle ORDER BY u.name');
    $_GET['developer'] == '' ? $selected = ' selected="selected"' : $selected = '';
    echo '<option value=""' . $selected . '>Select user...</option>'."\n";
    while ($u = $users->fetchRow()) {
        $_GET['developer'] == $u['handle'] ? $selected = ' selected="selected"' : $selected = '';
        echo '<option value="' . $u['handle'] . '"' . $selected . '>' . $u['name'] . '</option>'."\n";
    }
echo '        </select></div>
        </form>
    </td></tr>' . "\n";


// Exit if there are no bugs for this version
if ($total == 0) {
    echo '<tr><td><p>No bugs found</p></td></tr></table>' . "\n";
    response_footer();
    exit;
}

$res = $dbh->query('SELECT count(id) AS total FROM bugdb');
$entries = $res->fetchRow();

echo '<tr id="bug_header"><td>
    <strong style="text-align: right;">Total bug entries in system:</strong></td>
    <td style="font-size: 130%;">' . $entries['total'] . '</td>
    <td><strong>' . sort_url('closed')      . '</strong></td>
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

echo '<tr><td class="bug_head"><strong>All:</strong></td>
    <td class="bug_bg1">' . $total . '</td>
    <td class="bug_bg2">'. bugstats('closed',      'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('open',        'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('critical',    'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('verified',    'all') .'&nbsp;</td>  
    <td class="bug_bg1">'. bugstats('analyzed',    'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('assigned',    'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('suspended',   'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('duplicate',   'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('feedback',    'all') .'&nbsp;</td>
    <td class="bug_bg2">'. bugstats('no feedback', 'all') .'&nbsp;</td>
    <td class="bug_bg1">'. bugstats('bogus',       'all') .'&nbsp;</td>
    </tr>' . "\n";

foreach ($package_name[$_GET['sort_by']] as $name => $value) {
    if (($package_name['open'][$name]     > 0 ||
        $package_name['critical'][$name]  > 0 ||
        $package_name['analyzed'][$name]  > 0 ||
        $package_name['verified'][$name]  > 0 ||
        $package_name['suspended'][$name] > 0 ||
        $package_name['duplicate'][$name] > 0 ||
        $package_name['assigned'][$name]  > 0 ||
        $package_name['feedback'][$name]  > 0 ) && $name != 'all')
    {
        echo '<tr><td class="bug_head">
            <strong>' . $name . ':</strong></td>
            <td class="bug_bg1">'. $package_name['all'][$name]    .'</td>
            <td class="bug_bg2">'. bugstats('closed',      $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('open',        $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('critical',    $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('verified',    $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('analyzed',    $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('assigned',    $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('suspended',   $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('duplicate',   $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('feedback',    $name) .'&nbsp;</td>
            <td class="bug_bg2">'. bugstats('no feedback', $name) .'&nbsp;</td>
            <td class="bug_bg1">'. bugstats('bogus',       $name) .'&nbsp;</td>
            </tr>' . "\n";
    }
}

echo "</table>\n";
response_footer();
?>