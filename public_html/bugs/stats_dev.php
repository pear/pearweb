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
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

// Obtain common includes
require_once './include/prepend.inc';

error_reporting(E_ALL ^ E_NOTICE);

response_header('Developer Bugs Stats');

$dbh->setFetchMode(DB_FETCHMODE_ASSOC);
?>

<h1>Most Active Bug-fixing Developers</h1>
<p>The following is some informational statistics on bug fixing and reporting.
Developers are considered to have fixed a bug if the bug is marked
<strong>Closed</strong> and is assigned to the developer.</p>

<table id="dev-stats">
 <tr>
  <th>All Time</th>
  <th>Last 30 Days</th>
  <th>Bug Reporting</th>
 </tr>
 <tr>
  <td valign="top">
<?php
require_once 'bugs/pear-bugs.php';
$bugs = new PEAR_Bugs;
$develstats = $bugs->allDevelStats();
$lastmonth  = $bugs->lastMonthStats();
$reporters  = $bugs->reporterStats();
echo '<table class="dev-stats-sub">'; ?>
 <tr>
  <th class="bug_header">Closed Bugs</th>
  <th class="bug_header">Developer</th>
 </tr>
<?php
$handle = isset($auth_user->handle) ? $auth_user->handle : '';

foreach ($develstats as $stat) {
    $style = $stat['handle'] == $handle ? ' style="background-color: yellow;"' : '';
    echo " <tr>\n";
    echo '  <td class="bug_bg0"'.$style.'>' . $stat['c'] . "</td>\n";
    echo '  <td class="bug_bg0"'.$style.'><a href="/user/' . $stat['handle'] . '">' .
        $stat['handle'] . "</a></td>\n";
    echo " </tr>\n";
}
echo "</table>\n";

echo '</td><td valign="top">';
echo '<table class="dev-stats-sub">'; ?>
 <tr>
  <th class="bug_header">Closed Bugs</th>
  <th class="bug_header">Developer</th>
 </tr>
<?php
foreach ($lastmonth as $stat) {
    $style = $stat['handle'] == $handle ? ' style="background-color: yellow;"' : '';
    echo " <tr>\n";
    echo '  <td class="bug_bg0"'.$style.'>' . $stat['c'] . "</td>\n";
    echo '  <td class="bug_bg0"'.$style.'><a href="/user/' . $stat['handle'] . '">' .
        $stat['handle'] . "</a></td>\n";
    echo " </tr>\n";
}
echo "</table>\n";
echo '</td><td valign="top">';
echo '<table class="dev-stats-sub">'; ?>
 <tr>
  <th class="bug_header">Bugs + Comments + Patches</th>
  <th class="bug_header">Developer</th>
 </tr>
<?php
foreach ($reporters as $dev => $stat) {
    $style = $dev == $handle ? ' style="background-color: yellow;"' : '';
    echo " <tr>\n";
    echo '  <td class="bug_bg0"'.$style.'>' . $stat . "</td>\n";
    echo '  <td class="bug_bg0"'.$style.'><a href="/user/' . $dev . '">' .
        $dev . "</a></td>\n";
    echo " </tr>\n";
}
echo "</table>\n";
echo '</td>';
echo '</tr>';
echo '</table>';

response_footer();