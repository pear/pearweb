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

response_header('Bugs Stats');

$dbh->setFetchMode(DB_FETCHMODE_ASSOC);

$titles = array(
    'closed'      => 'Closed',
    'open'        => 'Open',
    'critical'    => 'Crit',
    'verified'    => 'Verified',
    'analyzed'    => 'Analyzed',
    'assigned'    => 'Assigned',
    'duplicate'   => 'Dupe',
    'feedback'    => 'Fdbk',
    'no feedback' => 'No&nbsp;Fdbk',
    'bogus'       => 'Bogus',
    'suspended'   => 'Susp',
);

$category  = $_GET['category'];
$developer = $_GET['developer'];
$rev       = $_GET['rev'];
$sort_by   = $_GET['sort_by'];
$total     = 0;
$row       = array();
$pkg       = array();
$pkg_total = array();
$all       = array();
$pseudo    = true;

$query  = 'SELECT b.package_name, LOWER(b.status) AS status, COUNT(*) AS quant'
        . ' FROM bugdb AS b';

$from = ' LEFT JOIN packages AS p ON p.name = b.package_name';
if (!empty($_GET['category'])) {
    $pseudo = false;
    $from .= ' JOIN categories AS c ON c.id = p.category';
    $from .= ' AND c.name = ' .  $dbh->quoteSmart($_GET['category']);
}
if (!empty($_GET['developer'])) {
    $pseudo = false;
    $from .= ' JOIN maintains AS m ON m.package = p.id';
    $from .= ' AND m.handle = ' .  $dbh->quoteSmart($_GET['developer']);
}

switch ($site) {
    case 'pecl':
        $where = ' WHERE p.package_type = ' . $dbh->quoteSmart($site);
        break;
    case 'pear':
        if ($pseudo) {
            $where .= " WHERE (p.package_type = 'pear'"
                    . " OR b.package_name IN ('"
                    . implode("', '", $pseudo_pkgs) . "'))";
        } else {
            $where = " WHERE p.package_type = 'pear'";
        }
        break;
    default:
        $where = ' WHERE 1=1';
}

if (empty($_GET['bug_type'])) {
    $bug_type = 'Bug';
    $_GET['bug_type'] = 'Bug';
} elseif ($_GET['bug_type'] == 'All') {
    $bug_type = '';
} else {
    $bug_type = $_GET['bug_type'];
    $where .= ' AND bug_type = ' . $dbh->quoteSmart($bug_type);
}

$query .= $from . $where;
$query .= ' GROUP BY b.package_name, b.status';
$query .= ' ORDER BY b.package_name, b.status';

$result =& $dbh->query($query);

while ($result->fetchInto($row)) {
    $pkg[$row['status']][$row['package_name']]  = $row['quant'];
    $pkg_total[$row['package_name']]           += $row['quant'];
    $all[$row['status']]                       += $row['quant'];
    $total                                     += $row['quant'];
}

if ($total > 0) {
    if (!isset($sort_by)) {
        $sort_by = 'open';
    }
    if (!isset($rev)) {
        $rev = 1;
    }

    if ($rev == 1) {
        arsort($pkg[$sort_by]);
    } else {
        asort($pkg[$sort_by]);
    }
    reset($pkg);
}


/*
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

/*
 * Fetch list of users/maintainers
 */
$users =& $dbh->query('SELECT u.handle AS handle, u.name AS name FROM users u, maintains m WHERE u.handle = m.handle
                        GROUP BY handle ORDER BY u.name');
    $_GET['developer'] == '' ? $selected = ' selected="selected"' : $selected = '';
    echo '<option value=""' . $selected . '>All</option>'."\n";
    while ($u = $users->fetchRow(DB_FETCHMODE_ASSOC)) {
        $_GET['developer'] == $u['handle'] ? $selected = ' selected="selected"' : $selected = '';
        echo '<option value="' . $u['handle'] . '"' . $selected . '>' . $u['name'] . '</option>'."\n";
    }
    echo '</select>
        <strong>Bug Type:</strong>
        <select id="bug_type" name="bug_type" onchange="this.form.submit(); return false;">';
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

echo " <tr>\n";
echo '  <td class="bug_head">All' . "</td>\n";
echo '  <td class="bug_bg0">' . $total . "</td>\n";

$i = 1;
foreach ($titles as $key => $val) {
    echo '  <td class="bug_bg' . $i++ % 2 . '">';
    echo bugstats($key, 'all') . "</td>\n";
}
echo ' </tr>' . "\n";

$stat_row = 1;
foreach ($pkg[$sort_by] as $name => $value) {
    if ($name != 'all') {
        /* Output a new header row every 40 lines */
        if (($stat_row++ % 40) == 0) {
            echo display_stat_header($total, false);
        }
        echo " <tr>\n";
        echo '  <td class="bug_head">' . package_link($name) . "</td>\n";
        echo '  <td class="bug_bg0">' . $pkg_total[$name];
        echo "</td>\n";

        $i = 1;
        foreach ($titles as $key => $val) {
            echo '  <td class="bug_bg' . $i++ % 2 . '">';
            echo bugstats($key, $name) . "</td>\n";
        }
        echo ' </tr>' . "\n";
    }
}

echo "</table>\n";

response_footer();



/*
 * DECLARE FUNCTIONS ===================================
 */

function bugstats($status, $name)
{
    global $pkg, $all;

    if (isset($pkg[$status][$name])) {
        return '<a href="search.php?cmd=display&amp;status=' .
               ucfirst($status) .
               ($name == 'all' ? '' : '&amp;package[]=' . urlencode($name)) .
               '&amp;by=Any&amp;limit=10'.$string.'">' .
               $pkg[$status][$name] . "</a>\n";
    } elseif ($name == 'all' && isset($all[$status])) {
        return '<a href="search.php?cmd=display&amp;status=' .
               ucfirst($status) .
               ($name == 'all' ? '' : '&amp;package[]=' . urlencode($name)) .
               '&amp;by=Any&amp;limit=10'.$string.'">' .
               $all[$status] . "</a>\n";
    } else {
        return '&nbsp';
    }
}

function sort_url($name)
{
    global $sort_by, $rev, $phpver, $category, $developer, $titles;

    if ($name == $sort_by) {
        $reve = ($rev == 1) ? 0 : 1;
    } else {
        $reve = 1;
    }
    if ($sort_by != $name) {
        $attr = 'class="bug_stats"';
    } else {
        $attr = 'class="bug_stats_choosen"';
    }
    return '<a href="./stats.php?sort_by=' . urlencode($name) .
           '&amp;rev=' . $reve . '&amp;category=' . $category .
           '&amp;developer=' .$developer . '" ' . $attr . '>' .
           $titles[$name] .'</a>';
}

function package_link($name)
{
    global $pseudo_pkgs;

    if (!in_array($name, $pseudo_pkgs)) {
        return '<a href="/package/' . $name . '" class="bug_stats">' .
               $name.'</a>';
    } else {
        return $name;
    }
}

function display_stat_header($total, $grandtotal = true)
{
    global $titles;

    $stat_head  = " <tr>\n";
    if ($grandtotal) {
        $stat_head .= '  <th class="bug_header">Name</th>' . "\n";
    } else {
        $stat_head .= '  <th class="bug_header">&nbsp;</th>' . "\n";
    }
    $stat_head .= '  <th class="bug_header">&nbsp;</th>' . "\n";

    foreach ($titles as $key => $val) {
        $stat_head .= '  <th class="bug_header">' . sort_url($key) . "</th>\n";
    }

    $stat_head .= '</tr>' . "\n";
    return $stat_head;
}

?>
