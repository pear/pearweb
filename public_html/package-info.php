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
   | Authors: Martin Jansen <mj@php.net>                                  |
   |          Tomas V.V.Cox <cox@idecnet.com>                             |
   +----------------------------------------------------------------------+
   $Id$
*/


require_once 'Damblan/Karma.php';
require_once 'Damblan/URL.php';
require_once 'bugs/pear-bugs.php';
require 'roadmap/info.php';

$site = new Damblan_URL;


// {{{ setup, queries
$params = array('package|pacid' => '', 'action' => '', 'version' => '', 'allowtrackbacks' => '');
$site->getElements($params);

$pacid = $params['package|pacid'];

// Package data
if (!empty($pacid)) {
    include_once 'pear-database-package.php';
    $pkg = package::info($pacid);
//    $stats = $dbh->getAssoc('SELECT
//releases.package,
//dl_number/DATEDIFF(NOW(),MIN(releases.releasedate)) as d
//FROM releases, packages, package_stats
//WHERE
//    packages.id = releases.package AND
//    packages.package_type = \'pear\' AND
//    package_stats.release = releases.version AND
//    package_stats.package = packages.name
//GROUP BY releases.package
//ORDER BY d DESC');
//
//    $amount = $stats[$pkg['packageid']];
//    $newstats = array_flip(array_values($stats));
//    $rank = ($newstats[$amount] + 1) . ' of ' . count($stats);

    $rel_count = count($pkg['releases']);
}

$version = 0;
$action = '';
$show_all = false;

if (!empty($params['action'])) {

    switch ($params['action']) {
    case 'download' :
    case 'docs' :
        $action =  $params['action'];
        if (!empty($params['version']) && $params['version'] != 'All') {
            $version = htmlspecialchars(strip_tags($params['version']));
        } elseif ($params['version'] == 'All') {
            $show_all = true;
        }
        break;

    case 'bugs' :
        // Redirect to the bug database
        localRedirect("/bugs/search.php?cmd=display&package_name%5B%5D=" . urlencode($pkg['name']));
        break;

    case 'trackbacks' :
        if (isset($auth_user)) {
            $karma =& new Damblan_Karma($dbh);
            $trackbackIsAdmin = (isset($auth_user) && $karma->has($auth_user->handle, 'pear.dev'));
            if ($trackbackIsAdmin) {
                include_once 'pear-database-package.php';
                if ($pkg['blocktrackbacks'] && $params['allowtrackbacks'] == 1) {
                    package::allowTrackbacks($pkg['name'], true);
                    localRedirect('/package/' . $pkg['name'] . '/trackbacks');
                } elseif ($params['allowtrackbacks'] == 2) {
                    package::allowTrackbacks($pkg['name'], false);
                    localRedirect('/package/' . $pkg['name'] . '/trackbacks');
                }
            }
        } else {
            if ($pkg['blocktrackbacks']) {
                localRedirect('/package/' . $pkg['name']);
            } else {
                $trackbackIsAdmin = false;
            }
        }

        $action = $params['action'];
        break;

    case 'redirected' :
        $redirected = true;
        $params['action']= '';

    default :
        $action = '';
        $version = htmlspecialchars(strip_tags($params['action']));
        break;
    }
}

if (empty($pacid) || !isset($pkg['name'])) {
    // Let's see if $pacid is a PECL package
    if (!isset($pkg['name'])) {
        include_once 'pear-database-package.php';
        $pkg_name = package::info($pacid, 'name', true);
        if (!empty($pkg_name)) {
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: http://pecl.php.net/package/' . $pkg_name);
            header('Connection: close');
            exit();
        }
    }

    $_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
    include 'error/404.php';
    exit();
}
// Information about the latest release below the summary
$versions = array_keys($pkg['releases']);
if (!in_array($version, $versions)) {
    $version = 0;
}


$name         = $pkg['name'];
$type         = $pkg['type'];
$summary      = stripslashes($pkg['summary']);
$license      = $pkg['license'];
$description  = stripslashes($pkg['description']);
$category     = $pkg['category'];
$homepage     = $pkg['homepage'];
$pacid        = $pkg['packageid'];
$cvs_link     = $pkg['cvs_link'];
$doc_link     = $pkg['doc_link'];
$unmaintained = ($pkg['unmaintained']) ? 'Y' : 'N';
$supersede = (bool) $pkg['new_channel'];

// Maintainer information
include_once 'pear-database-maintainer.php';
$maintainers = maintainer::get($pacid);
$accounts  = '<ul>';
//$bugs = new PEAR_Bugs;

foreach ($maintainers as $handle => $row) {
    //$buginfo = $bugs->getRank($handle);
    $accounts .= '<li>';
    $accounts .= user_link($handle);
    $accounts .= '(' . $row['role'] .
                  ($row['active'] == 0 ? ', inactive' : '');
    $accounts .= ')</li>';
}

$accounts .= '</ul>';

$channel_name = PEAR_CHANNELNAME;

if ($pkg['blocktrackbacks']) {
    $trackback_header = '';
} else {
    $trackback_uri = "http://$channel_name/trackback/trackback.php?id=$name";
    $trackback_header = <<<EOD
<!--
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">
    <rdf:Description
        rdf:about="http://$channel_name/package/$name"
        dc:identifier="http://$channel_name/package/$name"
        dc:title="Package :: $name"
        trackback:ping="$trackback_uri" />
</rdf:RDF>
-->
EOD;
}
// }}}
// {{{ page header

$name = htmlspecialchars(strip_tags($name));

if ($version) {
    response_header('Package :: ' . $name . ' :: ' . $version, null, $trackback_header);
} else {
    response_header('Package :: ' . $name, null, $trackback_header);
}

html_category_urhere($pkg['categoryid'], true);

echo '<h1>Package Information: ' . $name; // . ' (download rank: ' . $rank . ')';
if ($version) {
    echo ' ' .  $version;
}

echo "</h1>\n";

print_package_navigation($pacid, $name, $action);

// }}}
// {{{ Package Information

if (empty($action)) {

    // {{{ General information

    // {{{ Supeseded checks
    $dec_messages = array(
        'abandoned' => 'This package is not maintained anymore and has been superseded.',
        'superseded' => 'This package has been superseded, but is still maintained for bugs and security fixes.',
        'unmaintained' => 'This package is not maintained, if you would like to take over please go to <a href="http://pear.php.net/manual/en/newmaint.takingover.php">this page</a>.'
    );

    $dec_table = array(
        'abandoned'   => array('superseded' => 'Y', 'unmaintained' => 'Y'),
        'superseded'  => array('superseded' => 'Y', 'unmaintained' => 'N'),
        'unmaintained' => array('superseded' => 'N', 'unmaintained' => 'Y'),
    );

    $superseded = $supersede ? 'Y' : 'N';

    $apply_rule = null;
    foreach ($dec_table as $rule => $conditions) {
        $match = true;
        foreach ($conditions as $condition => $value) {
            if ($$condition != $value) {
                $match = false;
                break;
            }
        }
        if ($match) {
            $apply_rule = $rule;
        }
    }

    if (!is_null($apply_rule) && isset($dec_messages[$apply_rule])) {
        $str  = '<div class="warnings">';
        $str .= $dec_messages[$apply_rule];
        if ($pkg['new_channel'] == 'pear.php.net') {
            $str .= '  Use <a href="/package/' . $pkg['new_package'] .
                '">' . htmlspecialchars($pkg['new_package']) . '</a> instead.';
        } elseif ($pkg['new_channel']) {
            $str .= '  Package has moved to channel <a href="http://' . $pkg['new_channel'] .
            '">' . htmlspecialchars($pkg['new_channel']) . '</a>, package ' .
            $pkg['new_package'] . '.';
        }
        $str .= '</div>';
        echo $str;
    }
    // }}}

    echo '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    echo '<tr>';
    echo '<th class="headrow" style="width: 50%">&raquo; Summary</th>';
    echo '<th class="headrow" style="width: 50%">&raquo; License</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="textcell">' . htmlspecialchars($summary) . '</td>';
    echo '<td class="textcell">' . package::get_license_link($license) . '</td>';
    echo '</tr>';

    echo '<tr>';
    echo '<th width="50%" class="headrow">&raquo; Current Release</th>';
    echo '<th width="50%" class="headrow">&raquo; Bug Summary</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td width="50%" class="textcell">';
    if (isset($versions[0])) {
        echo '<a href="http://download.pear.php.net/package/' . htmlspecialchars($name) . '-' . $versions[0] . '.tgz">' . $versions[0] . '</a>';
        echo ' (' . $pkg['releases'][$versions[0]]['state'] . ')';
        echo ' was released on ' . make_utc_date(strtotime($pkg['releases'][$versions[0]]['releasedate']), 'Y-m-d');
        echo ' (<a href="/package/' . htmlspecialchars($name) . '/download/">Changelog</a>)';

        if ($pkg['releases'][$versions[0]]['state'] != 'stable') {
            foreach ($pkg['releases'] as $rel_ver => $rel_arr) {
                if ($rel_arr['state'] == 'stable') {
                    echo "<br />\n";
                    echo '<a href="http://download.pear.php.net/package/' . htmlspecialchars($name) . '-';
                    print $rel_ver . '.tgz">' . $rel_ver . '</a>';
                    echo ' (stable)';
                    echo ' was released on ';
                    print make_utc_date(strtotime($rel_arr['releasedate']),
                                        'Y-m-d');
                    echo ' (<a href="/package/' . htmlspecialchars($name);
                    echo '/download/' . $rel_ver . '">Changelog</a>)';
                    break;
                }
            }
        }
    } else {
        echo 'No releases have been made yet.';
    }
    if (Roadmap_Info::roadmapExists($name)) {
        echo '<br /><a href="/bugs/roadmap.php?package=' . urlencode($name) .
            '">Development Roadmap</a>';
        $nextrelease = Roadmap_Info::nextRelease($name);
        if ($nextrelease) {
            $x = ceil((((strtotime($nextrelease[1]) - time()) / 60) / 60) / 24);
            echo ' (next release: <strong><a href="/bugs/roadmap.php?package=' .
                urlencode($name) .'&roadmapdetail=' . $nextrelease[0]
                . '#a' . $nextrelease[0] . '">' . $nextrelease[0] .
                '</a></strong> in ';
            echo $x . ' day';
            if ($x != 1) echo 's';
            if ($x < 0) echo '!!';
            echo ', ' . Roadmap_Info::percentDone($name) . '% complete)';
        }
    }
    echo '</td>';
    echo '<td width="50%" class="textcell">';
    $bugs = new PEAR_Bugs;
    $buginfo = $bugs->packageBugStats($pkg['name']);
    if (!$buginfo['count']) {
        echo 'No open bugs';
    } else {
        echo '<ul>';
        $bstats = $bugs->bugRank();
        foreach ($bstats as $i => $pi) {
            if ($pi['name'] == $pkg['name']) {
                echo '<li>Package Maintenance Rank: <strong>' . ++$i . '</strong> of ' .
                    count ($bstats) .
                    ' packages with open bugs</li>';
                break;
            }
        }
        echo '<li>Number of <a href="/bugs/search.php?cmd=display&package_name[]=' .
            $pkg['name'] . '&status=OpenFeedback&bug_type=Bugs">open bugs</a>: <strong>' .
            $buginfo['count'] . ' (' . $buginfo['total'] . ' total bugs)</strong></li>';
        echo '<li>Average age of open bugs: <strong>' . round($buginfo['average']) . ' days</strong></li>';
        echo '<li>Oldest open bug: <strong>' . $buginfo['oldest'] . ' days</strong></li>';
        echo '</ul>';
    }
    echo '</td>';
    echo '</tr>';
    echo '<tr>';
    if (isset($auth_user)) {
        echo '<th class="headrow">&raquo; Description</th>';
        echo '<th class="headrow">&raquo; Package.xml suggestions (for developers)</th>';
    } else {
        echo '<th colspan="2" class="headrow">&raquo; Description</th>';
    }
    echo '</tr>';
    echo '<tr>';
    if (isset($auth_user)) {
        require 'package/releasehelper.php';
        $helper = new package_releasehelper($pkg['name']);
        echo '<td class="textcell">' . nl2br(htmlspecialchars($description)) . '</td>';
        echo '<td class="textcell">';
        echo '<ul>';
        if (!$helper->hasReleases()) {
            echo '   <li>First release should be version <strong><a href="/bugs/roadmap.php?package=' .
            urlencode($name) . '&showornew=0.1.0">0.1.0</a></strong>, stability <strong>alpha</strong>';
            echo '   </li>';
        } else {
            $bugfix = $helper->getNextBugfixVersion();
            $newfeatures =  $helper->getNewFeatureVersion();
            if ($helper->nextCanBeStable()) {
                echo '   <li>';
                echo '    Next Bugfix release should be: <strong><a href="' .
                    '/bugs/roadmap.php?package=' . urlencode($name) . '&showornew=' .
                    $bugfix[0]. '#a' . $bugfix[0] . '">' . $bugfix[0] . '</a></strong>, stability ' .
                      '<strong>' . $bugfix[1] . '</strong>';
                echo '   </li>';
                echo '   <li>';
                if ($helper->lastWasReleaseCandidate()) {
                    echo '    Next Stable release should be: <strong>';
                } else {
                    echo '    Next New Feature release should be: <strong>';
                }
                echo '<a href="' .
                    '/bugs/roadmap.php?package=' . urlencode($name) . '&showornew=' .
                    $newfeatures[0]. '#a' . $newfeatures[0] . '">' . $newfeatures[0] .
                      '</a></strong>, stability <strong>' . $newfeatures[1] . '</strong>';
                echo '   </li>';
            } else {
                echo '   <li>';
                echo '    Next Bugfix release should be: <strong><a href="' .
                    '/bugs/roadmap.php?package=' . urlencode($name) . '&showornew=' .
                    $bugfix[0]. '#a' . $bugfix[0] . '">' . $bugfix[0] . '</a></strong>, stability ' .
                      '<strong>' . $bugfix[1] . '</strong>';
                echo '   </li>';
                $beta =  $helper->getNextBetaRelease();
                if ($beta) {
                    echo '   <li>';
                    echo '    Next Stable API release should be: <strong><a href="' .
                        '/bugs/roadmap.php?package=' . urlencode($name) . '&showornew=' .
                        $beta[0] . '#a' . $beta[0] . '">' . $beta[0] .
                        '</a></strong>, stability <strong>' . $beta[1] . '</strong>';
                    echo '   </li>';
                }
                if ($helper->canAddFeatures()) {
                    echo '   <li>';
                    echo '    Next New Feature release should be: <strong><a href="' .
                        '/bugs/roadmap.php?package=' . urlencode($name) . '&showornew=' .
                        $newfeatures[0] . '#a' . $newfeatures[0] . '">' . $newfeatures[0] .
                        '</a></strong>, stability <strong>' . $newfeatures[1] . '</strong>';
                    echo '   </li>';
                }
            }
        }
        if ($helper->hasOldPackagexml()) {
            echo ' <li>';
            echo '  <strong><blink>WARNING</blink>: the last release of this package used ';
            echo '  package.xml version 1.0</strong>, which is deprecated.';
            echo '  To use package.xml version 2.0, run &quot;pear convert&quot;';
            echo '  to create package2.xml. ';
            echo '  Both package.xml and package2.xml may be used together, or you may ';
            echo '  choose to replace package.xml with package2.xml for the next release.';
            echo ' </li>';
        }
        echo '</ul>';
        echo '</td>';
    } else {
        echo '<td colspan="2" class="textcell">' . nl2br(htmlspecialchars($description)) . '</td>';
    }
    echo '</tr>';

    echo '<tr>';
    echo '<th class="headrow" style="width: 50%">&raquo; Maintainers</th>';
    echo '<th class="headrow" style="width: 50%">&raquo; More Information</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="ulcell">' . $accounts . '</td>';
    echo '<td class="ulcell">';

    echo '<ul>';

    if (!empty($homepage)) {
        echo '<li>' . make_link(htmlspecialchars($homepage),
                                 'External Package Homepage') . '</li>';
    }
    if (!empty($cvs_link)) {
        echo '<li><a href="' . htmlspecialchars($cvs_link) . '" title="Browse the source tree (in CVS, Subversion or another RCS) of this package">Browse the source tree</a></li>';
    }
    echo '<li><a href="/feeds/pkg_' . strtolower(htmlspecialchars($name)) . '.rss" title="RSS feed for the releases of the package">RSS release feed</a></li>';
    echo '<li><a href="/package-stats.php?pid=' . $pkg['packageid'] . '&cid=' .
        $pkg['categoryid'] . '" title="View download statistics for this package">View Download Statistics</a></li>';
    echo '</ul>';
    echo '</td>';
    echo '</tr>';

    // {{{ Dependants

    include_once 'pear-database-package.php';
    $dependants = package::getDependants($name);
    if ($rel_count > 0 && count($dependants) > 0) {
        echo '<tr>';
        echo '<th colspan="2" class="headrow">&raquo; Packages that depend on ' . htmlspecialchars($name) . '</th>';
        echo '</tr>';
        echo '<tr>';

        echo '<td colspan="2" class="ulcell">';
        echo '<ul>';

        foreach ($dependants as $dep) {
            echo '<li>' . package::makeLink($dep['p_name']);
            if ($dep['max_dep'] != $dep['max_pkg']) {
                echo ' (versions &lt;= ' . $dep['max_dep'] . ')';
            }
            echo "</li>\n";
        }

        echo '</ul>';
        echo '</td>';

        echo '</tr>';
    }

    // }}}

    echo '</table>';

    // }}}

} elseif ($action == 'download') {

    // {{{ Download

    $i = 0;

    echo '<a href="/package/' . htmlspecialchars($name) . '/download/All">Show All Changelogs</a>';
    echo '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    echo ' <tr>';
    echo '  <th class="headrow" style="width: 20%">&raquo; Version</th>';
    echo '  <th class="headrow">&raquo; Information</th>';
    echo "</tr>\n";

    foreach ($pkg['releases'] as $release_version => $info) {
        echo " <tr>\n";

        if ($show_all || ($i++ == 0 && empty($version)) || $release_version === $version) {
            // Detailed view

            echo '<td class="textcell">' . $release_version . '</td>';
            echo '<td>';
            echo '<a href="http://download.pear.php.net/package/' . htmlspecialchars($name) . '-' . $release_version . '.tgz"><b>Download</b></a><br /><br />';
            echo '<b>Release date:</b> ' . make_utc_date(strtotime($info['releasedate'])) . '<br />';
            echo '<b>Release state:</b> ' . htmlspecialchars($info['state']) . '<br /><br />';
            echo '<b>Changelog:</b><br /><br />' . nl2br(make_ticket_links(htmlspecialchars($info['releasenotes']))) . '<br /><br />';

            if (!empty($info['deps']) && count($info['deps']) > 0) {
                echo '<b>Dependencies:</b>';

                $rel_trans = array('lt' => 'older than %s',
                                   'le' => 'version %s or older',
                                   'eq' => 'version %s',
                                   'ne' => 'any version but %s',
                                   'gt' => 'newer than %s',
                                   'ge' => '%s or newer',
                                   );
                $dep_type_desc = array('pkg'    => 'PEAR Package',
                                       'ext'    => 'PHP Extension',
                                       'php'    => 'PHP Version',
                                       'prog'   => 'Program',
                                       'ldlib'  => 'Development Library',
                                       'rtlib'  => 'Runtime Library',
                                       'os'     => 'Operating System',
                                       'websrv' => 'Web Server',
                                       'sapi'   => 'SAPI Backend',
                                       );

                $dep_text = '';
                foreach ($info['deps'] as $dependency) {

                    // Print link if it's a PEAR package and it's in the db
                    if ($dependency['type'] == 'pkg') {
                        $dep_pkg = package::info($dependency['name']);
                        if (!empty($dep_pkg['name']) && $dep_pkg['package_type'] = 'pear') {
                            $dependency['name'] = package::makeLink($dependency['name']);
                        }
                    }

                    if (isset($rel_trans[$dependency['relation']])) {
                        $rel = sprintf($rel_trans[$dependency['relation']], $dependency['version']);
                        $dep_text .= sprintf("<li>%s: %s %s",
                                             $dep_type_desc[$dependency['type']], $dependency['name'], $rel);
                    } else {
                        $dep_text .= sprintf("<li>%s: %s", $dep_type_desc[$dependency['type']], $dependency['name']);
                    }
                    if ($dependency['optional'] == 1) {
                        $dep_text .= ' (optional)';
                    }
                    if ($dependency['relation'] == 'not') {
                        $dep_text .= ' (conflicts with some versions)';
                    }

                    $dep_text .= '</li>';
                }

                echo '<ul>' . $dep_text . '</ul>';

            }

            echo "</td>\n";

        } else {
            // Simple view
            echo '  <td><a href="/package/' . htmlspecialchars($name) . '/download/' . $release_version . '">' . $release_version . "</a></td>\n";
            echo '  <td>' . make_utc_date(strtotime($info['releasedate']), 'Y-m-d') . ' &nbsp; &nbsp; ' . htmlspecialchars($info['state']) . "</td>\n";
        }

        echo " </tr>\n";
    }

    echo "</table>\n";

    // }}}

} else if ($action == 'docs') {

    // {{{ Documentation

    echo '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    echo '<tr>';
    echo '<th class="headrow" style="width: 50%">&raquo; End-user documentation</th>';
    echo '<th class="headrow" style="width: 50%">&raquo; API documentation</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td class="ulcell">';

    if (!empty($doc_link)) {
        echo '<ul><li><a href="' . htmlspecialchars($doc_link) . '">End-user Documentation</a></li></ul>';
    } else {
        echo '<p>No end-user documentation is available for this package.</p>';
    }

    echo '</td>';
    echo '<td class="textcell">';

    if ($rel_count > 0) {
        echo '<p>Auto-generated API documentation for each ';
        echo 'release is available.</p>';

        echo '<p><a href="/package/' . htmlspecialchars($name) . '/docs/latest/">Documentation for the latest release</a></p>';
        echo '<hr />';

        echo '<strong>Complete list:</strong>';
        echo '<ul>';

        foreach ($pkg['releases'] as $r_version => $release) {
            echo '<li><a href="/package/' . htmlspecialchars($name) . '/docs/' . $r_version . '/">' . $r_version . '</a></li>';
        }

        echo '</ul>';
        echo '<p>This documentation has been generated from the ';
        echo 'inline comments in the source code using ';
        echo '<a href="/package/phpDocumentor/">phpDocumentor</a>.</p>';
    } else {
        echo '<p>Auto-generated API documentation will be available ';
        echo 'once that this package has rolled a release.</p>';
    }

    echo '</td>';
    echo '</tr>';
    echo '</table>';

    // }}}
} elseif ($action == 'trackbacks') {

    if ($pkg['blocktrackbacks']) {
        echo '<p>Trackbacks are disabled for this package. If you like to enable them, click below:</p>';
        echo '<p><a href="/package/' . $pkg['name'] . '/trackbacks/?allowtrackbacks=1">Allow trackbacks</a></p>';
        response_footer();
        exit();
    }

    include_once 'Damblan/Trackback.php';

    // Generate trackback list
    $trackbacks = Damblan_Trackback::listTrackbacks($dbh, $name, !$trackbackIsAdmin);

    echo '<p>This page provides a list of trackbacks, which have been received to this package. A trackback is usually generated,
when a weblog entry is created, which is related to the package. If you want to learn more about trackbacks, please take a look at
what <a href="http://en.wikipedia.org/wiki/Trackback">Wikipedia writes about trackbacks</a>.</p>
<p>If you like to disable the trackbacks for this package, click here:
<p><a href="/package/' . $pkg['name'] . '/trackbacks/?allowtrackbacks=2">Disable trackbacks</a></p>';

    echo '<p>The trackback URL for this package is: <a href="'.$trackback_uri.'">'.$trackback_uri.'</a>';

    if ($trackbackIsAdmin) {
        echo '<div class="explain">You may manipulate the trackbacks of this package. In contrast to normal users, you see approved and pending trackbacks, while normal users only see the approved ones.</div>';
    }

    if (count($trackbacks) == 0) {
        echo '<p>Sorry, there are no trackbacks for this package, yet.</p>';
    }

    echo '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    foreach ($trackbacks as $trackback) {
        echo '<tr>';
        echo '<th class="others">';
        echo 'Weblog:';
        echo '</th>';
        echo '<td class="ulcell" style="width:100%">';
        print $trackback->get('blog_name');
        echo '</td>';
        echo '</tr>';

        if ($trackbackIsAdmin) {
            echo '<tr>';
            echo '<th class="others">';
            echo 'Approved:';
            echo '</th>';
            echo '<td class="ulcell">';
            print ($trackback->get('approved')) ? '<b>yes</b>' : '<b>no</b>';
            echo '</td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<th class="others">';
        echo 'Title:';
        echo '</th>';
        echo '<td class="ulcell">';
        echo '<a href="'.$trackback->get('url').'">'.$trackback->get('title').'</a>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th class="others">';
        echo 'Date:';
        echo '</th>';
        echo '<td class="ulcell">';
        print make_utc_date($trackback->get('timestamp'), 'Y-m-d');
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th class="others">';
        echo '</th>';
        echo '<td class="ulcell">';
        print  $trackback->get('excerpt');
        echo '</td>';
        echo '</tr>';

        if ($trackbackIsAdmin) {
            echo '<tr>';
            echo '<th class="others">';
            echo 'IP:';
            echo '</th>';
            echo '<td class="ulcell">';
            print $trackback->get('ip');
            echo '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<th class="others">';
            echo '</th>';
            echo '<td class="ulcell">';
            if (!$trackback->get('approved')) {
                echo '[<a href="/trackback/trackback-admin.php?action=approve&id='.$trackback->get('id').'&timestamp='.$trackback->get('timestamp').'">Approve</a>] ';
            }
            echo '[<a href="/trackback/trackback-admin.php?action=delete&id='.$trackback->get('id').'&timestamp='.$trackback->get('timestamp').'">Delete</a>]';
            echo '</td>';
            echo '</tr>';
        }

        echo '<tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>';

    }
    echo '</table>';
}

// }}}

response_footer();
