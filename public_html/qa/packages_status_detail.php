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
   | Authors: Arnaud Limbourg <arnaud@limbourg.com>                       |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * This page will show a list of packages with latest
 * release information along with bug count and any other info
 * that may be used to identify unmaintained packages
 */
auth_require('pear.qa');

require 'HTML/Table.php';
extra_styles('/css/packages_status.css');

// Sortable tables http://www.kryogenix.org/code/browser/sorttable/
$extra_header = '<script src="/javascript/sorttable.js"></script>';
$states = array('snapshot', 'devel', 'alpha', 'beta', 'stable');

response_header('Quality Assurance Initiative - Packages status',
    false, $extra_header);

$state = htmlspecialchars($_GET['state']);

if (!in_array($state, $states)) {
    report_error('This is not a valid state');
    response_footer();
    exit();
}

$pck = new package();

$packages = $pck->listAll(false, false, false, $state);

if (PEAR::isError($packages)) {
    report_error('Cannot list packages');
    response_footer();
    exit();
}

$total_packages_nbr = $studied_packages_nbr = 0;

$tables = array();
$time_scale = 15552000; // how much time elapsed since last release, in seconds 

$tables[$state]['old'] = new HTML_Table(
    array(
	'id'          => 'old',
	'cellspacing' => 0,
	'class'       => 'sortable'
    )
);
$tables[$state]['old']->setCaption(
    'Packages with state <em>'
    . $state . '</em> which have not been released in '
    . $time_scale / 86400 . ' days'
);
$tables[$state]['old']->setHeaderContents(0, 0, 'Package');
$tables[$state]['old']->setHeaderContents(0, 1, 'Version');
$tables[$state]['old']->setHeaderContents(0, 2, 'Date');
$tables[$state]['old']->setHeaderContents(0, 3, '# bugs');

$tables[$state]['new'] = new HTML_Table(
    array(
	'id'          => 'new',
	'cellspacing' => 0,
	'class'       => 'sortable'
    )
);
$tables[$state]['new']->setCaption(
    'Packages with state <em>'
    . $state . '</em> with a release in the past '
    . $time_scale / 86400 . ' days'
);
$tables[$state]['new']->setHeaderContents(0, 0, 'Package');
$tables[$state]['new']->setHeaderContents(0, 1, 'Version');
$tables[$state]['new']->setHeaderContents(0, 2, 'Date');
$tables[$state]['new']->setHeaderContents(0, 3, '# bugs');

foreach ($packages as $package => $pck_data) {
    $total_packages_nbr++; 

    $latest_release = $pck->getRecent(1, $package);

    if (PEAR::isError($latest_release) || count($latest_release) == 0) {
        continue;
    }

    // we just want to see the packages for a given state
    if ($latest_release[0]['state'] != $state) {
	continue;
    }

    $release_date = strtotime($latest_release[0]['releasedate']);

    $status = 'new';

    if (time() - $time_scale > $release_date) {
        $status = 'old';
    }

    $tables[$state][$status]->addRow(
        array(
            make_link('/package/' . $package, 
                $package, '', 'title="' . $package . '"'),
            $latest_release[0]['version'],
            date('Y-m-d', $release_date),
            make_link("/bugs/search.php?cmd=display&package_name%5B%5D=" . urlencode($package)),
            bugcount($package), '', 'title="Bugs for package ' . $package . '"' 
        )
    );

    $studied_packages_nbr++;
}

$html = '';
foreach ($tables as $state => $table) {
    if ($table['old']->getRowCount() > 1) {
        $html .= '<p class="old">' . $table['old']->toHtml() . '</p>';
    }
    if ($table['new']->getRowCount() > 1) {
        $html .= '<p id="new">' . $table['new']->toHtml() . '</div>' . "\n";
    }
}

$out = '
<div id="container">
    <p id="pageHeader">
        <h1>Summary</h1>
        <div id="subtitle">
            <h2>Number of packages in PEAR: {{TOTAL_PACKAGES_NUMBER}}</h2>
            <h2>Number of packages studied here : {{STUDIED_PACKAGES_NUMBER}}</h2>
        </div>
    </p>

    <div id="details">
        {{TABLES}}
    </div>
    
    <div id="footer">
    Page last updated on: {{UPDATE_DATE}}
    </div>
</div>
'; 

$search = array(
    '{{TOTAL_PACKAGES_NUMBER}}',
    '{{STUDIED_PACKAGES_NUMBER}}',
    '{{TABLES}}',
    '{{UPDATE_DATE}}',
);

$replace = array(
    $total_packages_nbr,
    $studied_packages_nbr,
    $html,
    date('d F Y \a\t H:i:s')
);

$out = str_replace($search, $replace, $out);

echo $out;

response_footer();

/**
 * Count number of bugs for a package
 *
 * @string package name
 * @return int number of bugs
 */
function bugcount($package)
{
    global $dbh;
 
    $query = '
        SELECT
            COUNT(*)
        FROM
            bugdb
        WHERE
            package_name=' . $dbh->quoteSmart($package) . '
            AND bug_type = "Bug"
            AND status IN ("Open", "Critical", "Assigned", "Analyzed")
            ';

    $count = $dbh->getOne($query);

    if (PEAR::isError($count)) {
        return '0';
    }

    if ($count > 0 && $count < 10) {
	    $count = "0$count";
    }

    return $count;
}
?>
