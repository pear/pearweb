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
require_once 'package/releasehelper.php';

$site = new Damblan_URL;


// {{{ setup, queries
$params = array(
    'package|pacid' => '',
    'action' => '',
    'version' => '',
    'allowtrackbacks' => ''
);
$site->getElements($params);

$pacid = $params['package|pacid'];

// Package data
if (!empty($pacid)) {
    include_once 'pear-database-package.php';
    $pkg = package::info($pacid);
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

    case 'doap':
        //throw out doap data
        include 'package-doap.php';
        exit();
        break;

    case 'redirected':
        //needs to be directly before default
        $redirected = true;
        $params['action']= '';

    default:
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
$bug_link     = $pkg['bug_link'];
$unmaintained = ($pkg['unmaintained']) ? 'Y' : 'N';
$supersede    = (bool) $pkg['new_channel'];

// Maintainer information
include_once 'pear-database-maintainer.php';
$maintainers = maintainer::get($pacid);
$accounts  = '<ul>' . "\n";
//$bugs = new PEAR_Bugs;

foreach ($maintainers as $handle => $row) {
    //$buginfo = $bugs->getRank($handle);
    $accounts .= '<li>';
    $accounts .= user_link($handle);
    $accounts .= ' (' . $row['role'] .
                  ($row['active'] == 0 ? ', inactive' : '');
    $accounts .= ')</li>' . "\n";
}

$accounts .= '</ul>' . "\n";

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
$extraHeaders = $trackback_header
    . ' <link rel="meta" title="DOAP" type="application/rdf+xml"'
    . ' href="/package/' . $name . '/doap"/>';

if ($version) {
    response_header($name . ' :: ' . $version, null, $extraHeaders);
} else {
    response_header($name, null, $extraHeaders);
}

html_category_urhere($pkg['categoryid'], true);

$v = $version ? ' ' .  $version : '';
echo '<h1>Package Information: ' . $name . $v . "</h1>\n";

print_package_navigation($pacid, $name, $action);

// }}}
// {{{ Package Information

if (empty($action)) {

    // {{{ General information

    // {{{ Supeseded checks
    $dec_messages = array(
        'abandoned'    => 'This package is not maintained anymore and has been superseded.',
        'superseded'   => 'This package has been superseded, but is still maintained for bugs and security fixes.',
        'unmaintained' => 'This package is not maintained, if you would like to take over please go to <a href="http://pear.php.net/manual/en/newmaint.takingover.php">this page</a>.'
    );

    $dec_table = array(
        'abandoned'    => array('superseded' => 'Y', 'unmaintained' => 'Y'),
        'superseded'   => array('superseded' => 'Y', 'unmaintained' => 'N'),
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
        if ($pkg['new_channel'] == PEAR_CHANNELNAME) {
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

    echo '<table border="0" cellspacing="0" cellpadding="2" class="Project">' . "\n";
    echo '<tr>' . "\n";
    echo '<th>&raquo; Summary</th>' . "\n";
    echo '<th>&raquo; License</th>' . "\n";
    echo '</tr>' . "\n";
    echo '<tr>' . "\n";
    echo '<td class="shortdesc">' . htmlspecialchars($summary) . '</td>' . "\n";
    echo '<td class="license">' . package::get_license_link($license) . '</td>' . "\n";
    echo '</tr>' . "\n";

    echo '<tr>' . "\n";
    echo '<th>&raquo; Current Release</th>' . "\n";
    if (empty($pkg['bug_link'])) {
        echo '<th>&raquo; Bug Summary</th>' . "\n";
    } else {
        echo "<th>&nbsp;</th>\n";
    }
    echo '</tr>' . "\n";
    echo '<tr>' . "\n";
    echo '<td>' . "\n";
    if (isset($versions[0])) {
        echo ' <a class="download-page" href="/package/' . htmlspecialchars($name) . '/download/">' . $versions[0] . '</a>';
        echo ' (' . $pkg['releases'][$versions[0]]['state'] . ')';
        echo ' was released on ' . format_date(strtotime($pkg['releases'][$versions[0]]['releasedate']), 'Y-m-d');
        echo ' (<a class="download-page" href="/package/' . htmlspecialchars($name) . '/download/">Changelog</a>)';

        if ($pkg['releases'][$versions[0]]['state'] != 'stable') {
            foreach ($pkg['releases'] as $rel_ver => $rel_arr) {
                if ($rel_arr['state'] == 'stable') {
                    echo "<br />\n";
                    echo '<a href="http://download.' . PEAR_CHANNELNAME . '/package/' . htmlspecialchars($name) . '-';
                    echo $rel_ver . '.tgz">' . $rel_ver . '</a>';
                    echo ' (stable)';
                    echo ' was released on ';
                    echo format_date(strtotime($rel_arr['releasedate']), 'Y-m-d');
                    echo ' (<a href="/package/' . htmlspecialchars($name);
                    echo '/download/' . $rel_ver . '">Changelog</a>)';
                    break;
                }
            }
        }
    } else {
        echo 'No releases have been made yet.';
    }
    if (empty($pkg['bug_link']) && Roadmap_Info::roadmapExists($name)) {
        echo '<br /><a href="/bugs/roadmap.php?package=' . urlencode($name) .
            '">Development Roadmap</a>';
        $nextrelease = Roadmap_Info::nextRelease($name);
        if ($nextrelease) {
            $x = ceil((((strtotime($nextrelease[1]) - time()) / 60) / 60) / 24);
            echo ' (next release: <strong><a href="/bugs/roadmap.php?package=' .
                urlencode($name) .'&amp;roadmapdetail=' . $nextrelease[0]
                . '#a' . $nextrelease[0] . '">' . $nextrelease[0] .
                '</a></strong> in ';
            echo $x . ' day';
            if ($x != 1) echo 's';
            if ($x < 0) echo '!!';
            echo ', ' . Roadmap_Info::percentDone($name) . '% complete)';
        }
    }
    echo '</td>' . "\n";
    echo '<td>' . "\n";
    if (empty($pkg['bug_link'])) {
        $bugs = new PEAR_Bugs;
        $buginfo = $bugs->packageBugStats($pkg['name']);
        $frinfo  = $bugs->packageFeaturestats($pkg['name']);
        if (!$buginfo['count']) {
            echo 'No open bugs';
        }

        if ($buginfo['count'] || $frinfo['count']) {
            echo '<ul>';
        }

        if ($buginfo['count']) {
            $bstats = $bugs->bugRank();
            foreach ($bstats as $i => $pi) {
                if ($pi['name'] == $pkg['name']) {
                    echo '<li>Package Maintenance Rank: <strong>' . ++$i . '</strong> of ' .
                        count ($bstats) .
                        ' packages with open bugs</li>';
                    break;
                }
            }
            $link = make_link('/bugs/search.php?cmd=display&amp;package_name[]=' . $pkg['name'] . '&amp;status=OpenFeedback&amp;bug_type=Bugs', 'open bugs');
            echo '<li>Number of ' . $link . ': <strong>' .
                $buginfo['count'] . ' (' . $buginfo['total'] . ' total bugs)</strong></li>' . "\n";
            echo '<li>Average age of open bugs: <strong>' . round($buginfo['average']) . ' days</strong></li>' . "\n";
            echo '<li>Oldest open bug: <strong>' . $buginfo['oldest'] . ' days</strong></li>' . "\n";
        }

        if ($frinfo['count']) {
            $link = make_link('/bugs/search.php?cmd=display&amp;package_name[]=' . $pkg['name'] . '&amp;status=OpenFeedback&amp;bug_type=Feature%2FChange+Request', 'feature requests');
            echo '<li>Number of open ' . $link . ': <strong>' .
                $frinfo['count'] . ' (' . $frinfo['total'] . ' total feature requests)</strong></li>' . "\n";

        }

        if ($buginfo['count'] || $frinfo['count']) {
            echo '</ul>';
        }

        echo '<br />' . make_link('/bugs/report.php?package=' . $name, 'Report a new bug to ' . $name);
    }
    echo '</td>' . "\n";
    echo '</tr>' . "\n";
    echo '<tr>' . "\n";
    if (isset($auth_user)) {
        echo '<th>&raquo; Description</th>' . "\n";
        echo '<th>&raquo; Package.xml suggestions (for developers)</th>' . "\n";
    } else {
        echo '<th colspan="2">&raquo; Description</th>' . "\n";
    }
    echo '</tr>' . "\n";
    echo '<tr>' . "\n";
    if (isset($auth_user)) {
        $helper = new package_releasehelper($pkg['name']);
        echo '<td class="description">' . nl2br(htmlspecialchars($description)) . '</td>' . "\n";
        echo '<td>' . "\n";
        echo '<ul>' . "\n";
        if (!$helper->hasReleases()) {
            echo '   <li>First release should be version <strong><a href="/bugs/roadmap.php?package=' .
            urlencode($name) . '&amp;showornew=0.1.0">0.1.0</a></strong>, stability <strong>alpha</strong>';
            echo '   </li>' . "\n";
        } else {
            $bugfix = $helper->getNextBugfixVersion();
            $newfeatures =  $helper->getNewFeatureVersion();
            if ($helper->nextCanBeStable()) {
                echo '   <li>';
                echo '    Next Bugfix release should be: <strong><a href="' .
                    '/bugs/roadmap.php?package=' . urlencode($name) . '&amp;showornew=' .
                    $bugfix[0]. '#a' . $bugfix[0] . '">' . $bugfix[0] . '</a></strong>, stability ' .
                      '<strong>' . $bugfix[1] . '</strong>';
                echo '   </li>' . "\n";
                echo '   <li>';
                if ($helper->lastWasReleaseCandidate()) {
                    echo '    Next Stable release should be: <strong>';
                } else {
                    echo '    Next New Feature release should be: <strong>';
                }
                echo '<a href="' .
                    '/bugs/roadmap.php?package=' . urlencode($name) . '&amp;showornew=' .
                    $newfeatures[0]. '#a' . $newfeatures[0] . '">' . $newfeatures[0] .
                      '</a></strong>, stability <strong>' . $newfeatures[1] . '</strong>';
                echo '   </li>' . "\n";
            } else {
                echo '   <li>';
                echo '    Next Bugfix release should be: <strong><a href="' .
                    '/bugs/roadmap.php?package=' . urlencode($name) . '&amp;showornew=' .
                    $bugfix[0]. '#a' . $bugfix[0] . '">' . $bugfix[0] . '</a></strong>, stability ' .
                      '<strong>' . $bugfix[1] . '</strong>';
                echo '   </li>' . "\n";
                $beta =  $helper->getNextBetaRelease();
                if ($beta) {
                    echo '   <li>';
                    echo '    Next Stable API release should be: <strong><a href="' .
                        '/bugs/roadmap.php?package=' . urlencode($name) . '&amp;showornew=' .
                        $beta[0] . '#a' . $beta[0] . '">' . $beta[0] .
                        '</a></strong>, stability <strong>' . $beta[1] . '</strong>';
                    echo '   </li>' . "\n";
                }
                if ($helper->canAddFeatures()) {
                    echo '   <li>';
                    echo '    Next New Feature release should be: <strong><a href="' .
                        '/bugs/roadmap.php?package=' . urlencode($name) . '&amp;showornew=' .
                        $newfeatures[0] . '#a' . $newfeatures[0] . '">' . $newfeatures[0] .
                        '</a></strong>, stability <strong>' . $newfeatures[1] . '</strong>';
                    echo '   </li>' . "\n";
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
            echo ' </li>' . "\n";
        }
        echo '</ul>' . "\n";
        echo '</td>' . "\n";
    } else {
        echo '<td colspan="2">' . nl2br(htmlspecialchars($description)) . '</td>' . "\n";
    }
    echo '</tr>' . "\n";

    echo '<tr>' . "\n";
    echo '<th>&raquo; Maintainers</th>' . "\n";
    echo '<th>&raquo; More Information</th>' . "\n";
    echo '</tr>' . "\n";
    echo '<tr>' . "\n";
    echo '<td>' . $accounts . '</td>' . "\n";
    echo '<td>' . "\n";

    echo '<ul>' . "\n";

    if (!empty($homepage)) {
        echo '<li>' . make_link(htmlspecialchars($homepage), 'External Package Homepage', '' , 'class="homepage"') . '</li>' . "\n";
    }
    if (!empty($cvs_link)) {
        echo '<li><a href="' . htmlspecialchars($cvs_link) . '" title="Browse the source tree (in CVS, Subversion or another RCS) of this package">Browse the source tree</a></li>' . "\n";
    }
    echo '<li><a href="/feeds/pkg_' . strtolower(htmlspecialchars($name)) . '.rss" title="RSS feed for the releases of the package">RSS release feed</a></li>' . "\n";
    echo '<li><a href="/package-stats.php?pid=' . $pkg['packageid'] . '&amp;cid=' .
        $pkg['categoryid'] . '" title="View download statistics for this package">View Download Statistics</a></li>' . "\n";
    echo '</ul>' . "\n";
    echo '</td>' . "\n";
    echo '</tr>' . "\n";

    echo '</table>' . "\n";

    // {{{ Dependants

    echo '<hr />';
    echo '<div style=" font-size: 0.9em; padding: 1.0em;">';
    include_once 'pear-database-package.php';
    $dependants = package::getDependants($name);
    if ($rel_count > 0 && count($dependants) > 0) {
        echo '<div style="width: 30em; float: left; margin: 0.5em">';
        echo '<h4>Packages that depend on ' . htmlspecialchars($name) . '</h4>' . "\n";
        echo '<ul>' . "\n";

        foreach ($dependants as $dep) {
            echo '<li>' . package::makeLink($dep['p_name']);
            if ($dep['max_dep'] != $dep['max_pkg']) {
                echo ' (versions &lt;= ' . $dep['max_dep'] . ')';
            }
            echo "</li>\n";
        }

        echo '</ul>' . "\n";
        echo '</div>';
    }


    
    $dependencies = package::getDependencies($name);
    if (count($dependencies) > 0) {
        echo '<div style="width: 30em; float: left; margin: 0.5em">';
        echo '<h4>Dependencies for ' . htmlspecialchars($name) . '</h4>' . "\n";
        echo '<ul>' . "\n";

        foreach ($dependencies as $dep) {
            echo '<li>';

            switch ($dep['type']) {
                case 'pkg':
                    echo package::makeLink($dep['name']);
                    break;
                case 'php':
                    echo $dep['name'];
                    break;
                case 'ext':
                    echo $dep['name'] . " extension";
                    break;
                default:
                    echo $dep['name'] .  $dep['type'];
                    break;
            }

            echo " ";
            echo $dep['version'] . " ";
            echo $dep['optional']? " (Optional)" : null;

            echo "</li>\n";
        }

        echo '</ul>' . "\n";
        echo '</div>';
    }
    echo '<br style="clear: both" /></div>';

    // }}}



    // }}}

} elseif ($action == 'download') {
    $helper = new package_releasehelper($name);
    // {{{ Download

    $i = 0;

    echo '<p><a href="/package/' . htmlspecialchars($name) . '/download/All">Show All Changelogs</a></p>
    <table id="download-releases">
     <tr>
      <th>&raquo; Version</th>
      <th>&raquo; Information</th>';
    echo "</tr>\n";

    foreach ($pkg['releases'] as $release_version => $info) {
        $first = ($i++ == 0 && empty($version));
        $featured = $show_all || $first || $release_version === $version;
        $td_class = $featured? 'featured-release' : 'normal-release';

        echo ' <tr class="' . $td_class . '">' . "\n";
        if ($featured) {

            // Detailed view
            ?>
            <td class="textcell">
             <strong><?php echo $release_version; ?></strong>
            </td>
            <td>
                <div class="package-download-action">
                    <h4>Easy Install</h4>
                    <p class="action-hint">Not sure? Get <a href="/manual/en/installation.php">more info</a>.</p>
                    <p class="action"><kbd>pear install <?php echo htmlspecialchars($name); ?>-<?php echo $release_version; ?></kbd></p>

                    <?php if (!$helper->hasOldPackagexml()) { ?>
                        <h4>Pyrus Install</h4>
                        <p>Try <a href="http://pear2.php.net/">PEAR2</a>'s installer, Pyrus.</p>
                        <p class="action"><kbd>php pyrus.phar install pear/<?php echo htmlspecialchars($name); ?>-<?php echo $release_version; ?></kbd></p>
                    <?php } ?>
                    

                </div>

                <div class="package-download-action download">
                    <h4>Download</h4>
                    <p class="action-hint">For manual installation only</p>
                    <p class="action">
                        <?php print make_link('http://download.pear.php.net/package/' . htmlspecialchars($name) . '-' . $release_version . '.tgz',
                                              $release_version); ?>
                    </p>
                </div>
                <br style="clear: both;" />
            <?php
            echo '<strong>Release date:</strong> ' . format_date(strtotime($info['releasedate'])) . '<br />';
            echo '<strong>Release state:</strong> ';
            echo '<span class="' . htmlspecialchars($info['state']) . '">' . htmlspecialchars($info['state']) . '</span><br /><br />';
            echo '<strong>Changelog:</strong><br /><br />' . nl2br(make_ticket_links(htmlspecialchars($info['releasenotes']), '/bugs/')) . '<br /><br />';

            if (!empty($info['deps']) && count($info['deps']) > 0) {
                echo '<strong>Dependencies:</strong>';

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

                if (!empty($dep_text)) {
                    echo '<ul>' . $dep_text . '</ul>';
                }

            }

            echo "</td>\n";

        } else {
            // Simple view
            echo '<td><p>';
            echo make_link('/package/' . htmlspecialchars($name) . '/download/' . $release_version, $release_version);
            echo "</p></td>\n";

            echo '<td>';
            echo '<strong>' . format_date(strtotime($info['releasedate']), 'Y-m-d') . '</strong><br />';
            echo '<span class="' . htmlspecialchars($info['state']) . '">' . htmlspecialchars($info['state']) . '</span>';
            echo "</td>\n";
        }

        echo " </tr>\n";
    }

    echo "</table>\n";

    // }}}

} else if ($action == 'docs') {

    // {{{ Documentation

    // Redirect users to the end user docs if auto gen docs are not present and
    // end user docs are, happens only in pecl
    if ($rel_count === 0 && !empty($doc_link)) {
        localRedirect($doc_link);
    }

    echo '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    echo '<tr>';
    echo '<th width="50%">&raquo; End-user documentation</th>';
    echo '<th>&raquo; API documentation</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="vertical-align: top">';

    if (!empty($doc_link)) {
        echo '<ul><li><a href="' . htmlspecialchars($doc_link) . '">End-user Documentation</a></li></ul>';
    }

    // auto-discover toc even if doc_link is empty
    $tocfile = 'manual/en/packagetocs/' . strtolower($name) . '.htm';
    if (file_exists($tocfile)) {
        echo file_get_contents($tocfile);
    } else if (empty($doc_link)) {
        echo '<p>No end-user documentation is available for this package.</p>';
    }

    echo '</td>';
    echo '<td style="vertical-align: top">';

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
        echo '<td style="width:100%">';
        print $trackback->get('blog_name');
        echo '</td>';
        echo '</tr>';

        if ($trackbackIsAdmin) {
            echo '<tr>';
            echo '<th class="others">';
            echo 'Approved:';
            echo '</th>';
            echo '<td>';
            print ($trackback->get('approved')) ? '<b>yes</b>' : '<b>no</b>';
            echo '</td>';
            echo '</tr>';
        }
        echo '<tr>';
        echo '<th class="others">';
        echo 'Title:';
        echo '</th>';
        echo '<td>';
        echo '<a href="'.$trackback->get('url').'">'.$trackback->get('title').'</a>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th class="others">';
        echo 'Date:';
        echo '</th>';
        echo '<td>';
        print format_date($trackback->get('timestamp'), 'Y-m-d');
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th class="others">';
        echo '</th>';
        echo '<td>';
        print  $trackback->get('excerpt');
        echo '</td>';
        echo '</tr>';

        if ($trackbackIsAdmin) {
            echo '<tr>';
            echo '<th class="others">';
            echo 'IP:';
            echo '</th>';
            echo '<td>';
            print $trackback->get('ip');
            echo '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<th class="others">';
            echo '</th>';
            echo '<td>';
            if (!$trackback->get('approved')) {
                echo '[<a href="/trackback/trackback-admin.php?action=approve&amp;id='.$trackback->get('id').'&amp;timestamp='.$trackback->get('timestamp').'">Approve</a>] ';
            }
            echo '[<a href="/trackback/trackback-admin.php?action=delete&ampid='.$trackback->get('id').'&amp;timestamp='.$trackback->get('timestamp').'">Delete</a>]';
            echo '</td>';
            echo '</tr>';
        }

        echo '<tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>';

    }
    echo '</table>';
}

// }}}

response_footer();
