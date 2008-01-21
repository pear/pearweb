<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Richard Heyes <richard@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/*
 * Use JPGraph library to display graphical
 * stats of downloads.
 *
 * TODO:
 *  o Dropdown on stats page to determine between
 *    monthly/weekly stats
 *  o Multiple releases per graph, ie side by side
 *    bar chart.
 */

/*
 * Determine the stats based on the supplied
 * package id (pid) and release id (rid).
 * If release id is empty a group bar chart is
 * drawn with each release having a different
 * color.
 */

$releases = !empty($_GET['releases']) ? explode(',', $_GET['releases']) : array(0);
if (!isset($releases) || !is_array($releases)) {
    exit;
}

require_once 'ezc/Base/base.php';
function __autoload($className)
{
    ezcBase::autoload($className);
}

class pearwebPalette extends ezcGraphPaletteTango
{
    protected $majorGridColor  = '#000000BB';
    protected $chartBackground = '#FFFFFF';
    protected $dataSetSymbol = array(
        ezcGraph::BULLET,
    );
}


// Cache time in secs
$cache_time = 300;

// Get package name
$package_id   = (int)$_GET['pid'];
$package_name = $dbh->getOne('SELECT name FROM packages WHERE id = ' . $package_id);
$package_rel  = count($releases) === 1 ? $dbh->getOne('SELECT version FROM releases WHERE id = ' . (int)$releases[0]) : '';

if (!isset($_GET['type']) OR empty($_GET['type'])) {
    $class = 'ezcGraphLineChart';
} else {
    $type = strtolower(htmlspecialchars($_GET['type'], ENT_QUOTES));
    if ($type === 'bar') {
        $class = 'ezcGraphBarChart';
    } elseif ($type === 'line') {
        $class = 'ezcGraphLineChart';
    }
}

$graph = new $class();
// Set up the title for the graph
$graph->title = 'Download statistics for ' . $package_name . ' ' . $package_rel;
// $graph->palette = new ezcGraphPaletteBlack();
$graph->palette = new pearwebPalette();
if (isset($_GET['output']) && $_GET['output'] == 'image') {
    // replace with cairo when it's out
    $graph->driver = new ezcGraphGdDriver();
    $graph->options->font = dirname(dirname(__FILE__)) . '/include/fonts/coolveti.ttf';
}

if ($class = 'ezcGraphLineChar') {
    $graph->options->fillLines = 230;
}

$graph->legend->position = ezcGraph::RIGHT;
$graph->legend->portraitSize = .1;
$graph->legend->font->maxFontSize = 12;

if (count($releases) === 1) {
    $graph->legend = false;
}


// This is the x axis labels. May change when selectable dates is added.
$year   = date('Y') - 1;
$month  = date('n') + 1;
$x_axis = array();
for ($i = 0; $i < 12; $i++) {
    $time = mktime(0, 0, 0, $month + $i, 1, $year);
    $x_axis[date('Ym', $time)] = date('M Y', $time);
}

$r   = array();
foreach ($releases as $release) {
    $r_name = $dbh->getOne('SELECT version FROM releases WHERE id = ' . (int)$release);
    $r[$r_name] = array();
    foreach ($x_axis as $key => $value) {
        $r[$r_name][$value] = 0;
    }

    $sql = sprintf("SELECT YEAR(yearmonth) AS dyear, MONTH(yearmonth) AS dmonth, SUM(downloads) AS downloads
                        FROM aggregated_package_stats a, releases r
                        WHERE a.package_id = %d
                            AND r.id = a.release_id
                            AND r.package = a.package_id
                            AND yearmonth > (now() - INTERVAL 1 YEAR)
                            %s
                        GROUP BY dyear, dmonth
                        ORDER BY dyear DESC, dmonth DESC",
                   $package_id,
                   $release_clause = $release > 0 ? 'AND a.release_id = ' . (int) $release : '');

    if ($result = $dbh->query($sql)) {
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $key = sprintf('%04d%02d', $row['dyear'], $row['dmonth']);
            if (!isset($x_axis[$key])) {
                continue;
            }
            $key = $x_axis[$key];
            if (isset($r[$r_name][$key])) {
                $r[$r_name][$key] = (int)$row['downloads'];
            }
        }
    }
}

foreach ($r as $k => $v) {
    $graph->data[$k] = new ezcGraphArrayDataSet($v);
}
$graph->xAxis->labelCount = 12;

// Go through setting up the graph
if (!DEVBOX) {
    // Send some caching headers to prevent unnecessary requests
    header('Last-Modified: ' . date('c'));
    header('ETag: ' . md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']));
    header('Expires: ' . date('r', time() + $cache_time));
    header('Cache-Control: public, max-age=' . $cache_time);
    header('Pragma: cache');

    // Main graph object
    $graph->renderToOutput(743, 250, md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']));
//    $graph = new Graph(543, 200, md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']), $cache_time);
} else {
    // Main graph object
    $graph->renderToOutput(743, 250);
}