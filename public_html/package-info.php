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

require_once 'Damblan/Trackback.php';
require_once 'Damblan/Karma.php';
require_once 'Damblan/URL.php';

$site = new Damblan_URL;


// {{{ setup, queries

$params = array('package|pacid' => '', 'action' => '', 'version' => '');
$site->getElements($params);

$pacid = $params['package|pacid'];

// Package data
if (!empty($pacid)) {
    $pkg = package::info($pacid);

    $rel_count = count($pkg['releases']);
}

$version = 0;
$action = '';

if (!empty($params['action'])) {

    switch ($params['action']) {
    case 'download' :
    case 'docs' :
        $action =  $params['action'];
        if (!empty($params['version'])) {
            $version = $params['version'];
        }
        break;

    case 'bugs' :
        // Redirect to the bug database
        localRedirect("/bugs/search.php?direction=ASC&cmd=display&status=Open&package_name%5B%5D=" . urlencode($pkg['name']));
        break;

    case 'trackbacks' :
        if (isset($auth_user)) {
            $karma =& new Damblan_Karma($dbh);
            $trackbackIsAdmin = (isset($auth_user) && $karma->has($auth_user->handle, 'pear.dev'));
        } else {
            $trackbackIsAdmin = false;
        }

        $action = $params['action'];
        break;

    case 'redirected' :
        $redirected = true;
        $params['action']= '';

    default :
        $action = '';
        $version = $params['action'];
        break;
    }
}

if (empty($pacid) || !isset($pkg['name'])) {
    // Let's see if $pacid is a PECL package
    if (!isset($pkg['name'])) {
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

$name        = $pkg['name'];
$type        = $pkg['type'];
$summary     = stripslashes($pkg['summary']);
$license     = $pkg['license'];
$description = stripslashes($pkg['description']);
$category    = $pkg['category'];
$homepage    = $pkg['homepage'];
$pacid       = $pkg['packageid'];
$cvs_link    = $pkg['cvs_link'];
$doc_link    = $pkg['doc_link'];

// Maintainer information
$maintainers = maintainer::get($pacid);
$accounts  = '<ul>';

foreach ($maintainers as $handle => $row) {
    $accounts .= '<li>';
    $accounts .= user_link($handle);
    $accounts .= sprintf("(%s%s)<br />",
                         ($row['active'] == 0 ? "inactive " : ""),
                         $row['role']
                         );
    $accounts .= '</li>';
}

$accounts .= '</ul>';

// Information about the latest release below the summary
$versions = array_keys($pkg['releases']);

$uri = (isset($redirected) && $redirected === true) ? preg_replace('@/package(/[^/]+)/redirected@', '\1', $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];

$url = 'http://'.$_SERVER['SERVER_NAME'].$uri;

// Get trackback autodiscovery code
$tmpTrackback = Services_Trackback::create(array(
    'id'            => $name,
    'url'           => $url,
    'title'         => 'Package :: ' . htmlspecialchars($name),
    'trackback_url' => 'http://'.$_SERVER['SERVER_NAME'].'/trackback/trackback.php?id='.$name,
));

$trackbackRDF = $tmpTrackback->getAutodiscoveryCode();

// }}}
// {{{ page header

if ($version) {
    response_header('Package :: ' . htmlspecialchars($name) . ' :: ' . $version, null, $trackbackRDF);
} else {
    response_header('Package :: ' . htmlspecialchars($name), null, $trackbackRDF);
}

html_category_urhere($pkg['categoryid'], true);

print '<h1>Package Information: ' . htmlspecialchars($name);
if ($version) {
    print ' ' .  htmlspecialchars($version);
}

print "</h1>\n";

print_package_navigation($pacid, $name, $action);

// }}}
// {{{ Package Information

if (empty($action)) {

    // {{{ General information

    print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    print '<tr>';
    print '<th class="headrow" style="width: 50%">&raquo; Summary</th>';
    print '<th class="headrow" style="width: 50%">&raquo; License</th>';
    print '</tr>';
    print '<tr>';
    print '<td class="textcell">' . htmlspecialchars($summary) . '</td>';
    print '<td class="textcell">' . get_license_link($license) . '</td>';
    print '</tr>';

    print '<tr>';
    print '<th colspan="2" class="headrow">&raquo; Current Release</th>';
    print '</tr>';
    print '<tr>';
    print '<td colspan="2" class="textcell">';
    if (isset($versions[0])) {
        print '<a href="/get/' . htmlspecialchars($name) . '-' . $versions[0] . '.tgz">' . $versions[0] . '</a>';
        print ' (' . $pkg['releases'][$versions[0]]['state'] . ')';
        print ' was released on ' . make_utc_date(strtotime($pkg['releases'][$versions[0]]['releasedate']), 'Y-m-d');
        print ' (<a href="/package/' . htmlspecialchars($name) . '/download/">Changelog</a>)';

        if ($pkg['releases'][$versions[0]]['state'] != 'stable') {
            foreach ($pkg['releases'] as $rel_ver => $rel_arr) {
                if ($rel_arr['state'] == 'stable') {
                    print "<br />\n";
                    print '<a href="/get/' . htmlspecialchars($name) . '-';
                    print $rel_ver . '.tgz">' . $rel_ver . '</a>';
                    print ' (stable)';
                    print ' was released on ';
                    print make_utc_date(strtotime($rel_arr['releasedate']),
                                        'Y-m-d');
                    print ' (<a href="/package/' . htmlspecialchars($name);
                    print '/download/' . $rel_ver . '">Changelog</a>)';
                    break;
                }
            }
        }
    } else {
        print 'No releases have been made yet.';
    }
    print '</td>';
    print '</tr>';

    print '<tr>';
    print '<th colspan="2" class="headrow">&raquo; Description</th>';
    print '</tr>';
    print '<tr>';
    print '<td colspan="2" class="textcell">' . nl2br(htmlspecialchars($description)) . '</td>';
    print '</tr>';

    print '<tr>';
    print '<th class="headrow" style="width: 50%">&raquo; Maintainers</th>';
    print '<th class="headrow" style="width: 50%">&raquo; More Information</th>';
    print '</tr>';
    print '<tr>';
    print '<td class="ulcell">' . $accounts . '</td>';
    print '<td class="ulcell">';

    print '<ul>';

    if (!empty($homepage)) {
        print '<li>' . make_link(htmlspecialchars($homepage),
                                 'External Package Homepage') . '</li>';
    }
    if (!empty($cvs_link)) {
        print '<li><a href="' . htmlspecialchars($cvs_link) . '" title="Browse the source tree (in CVS, Subversion or another RCS) of this package">Browse the source tree</a></li>';
    }
    print '<li><a href="/feeds/pkg_' . strtolower(htmlspecialchars($name)) . '.rss" title="RSS feed for the releases of the package">RSS release feed</a></li>';
    print '<li><a href="/package-stats.php?pid=' . $pkg['packageid'] . '" title="View download statstics for this package">View Download Statistics</a></li>';
    print '</ul>';
    print '</td>';
    print '</tr>';

    // {{{ Dependants

    $dependants = package::getDependants($name);
    if ($rel_count > 0 && count($dependants) > 0) {
        print '<tr>';
        print '<th colspan="2" class="headrow">&raquo; Packages that depend on ' . htmlspecialchars($name) . '</th>';
        print '</tr>';
        print '<tr>';

        echo '<td colspan="2" class="ulcell">';
        echo '<ul>';

        foreach ($dependants as $dep) {
            $obj =& new PEAR_Package($dbh, $dep['p_name']);
            echo '<li>' . $obj->makeLink();
            if ($dep['max_dep'] != $dep['max_pkg']) {
                echo ' (versions &lt;= ' . $dep['max_dep'] . ')';
            }
            echo "</li>\n";
        }

        echo '</ul>';
        echo '</td>';

        print '</tr>';
    }

    // }}}

    print '</table>';

    // }}}

} elseif ($action == 'download') {

    // {{{ Download

    $i = 0;

    print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    print ' <tr>';
    print '  <th class="headrow" style="width: 20%">&raquo; Version</th>';
    print '  <th class="headrow">&raquo; Information</th>';
    print "</tr>\n";

    foreach ($pkg['releases'] as $release_version => $info) {
        print " <tr>\n";

        if (($i++ == 0 && empty($version)) || ($release_version == $version)) {
            // Detailed view

            print '<td class="textcell">' . $release_version . '</td>';
            print '<td>';
            print '<a href="/get/' . htmlspecialchars($name) . '-' . $release_version . '.tgz"><b>Download</b></a><br /><br />';
            print '<b>Release date:</b> ' . make_utc_date(strtotime($info['releasedate'])) . '<br />';
            print '<b>Release state:</b> ' . htmlspecialchars($info['state']) . '<br /><br />';
            print '<b>Changelog:</b><br /><br />' . nl2br(make_ticket_links(htmlspecialchars($info['releasenotes']))) . '<br /><br />';

            if (!empty($info['deps']) && count($info['deps']) > 0) {
                print '<b>Dependencies:</b>';

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
                        $dep_pkg =& new PEAR_Package($dbh, $dependency['name']);
                        if (!empty($dep_pkg->name) && $dep_pkg->package_type = 'pear' && $dep_pkg->approved = 1) {
                            $dependency['name'] = $dep_pkg->makeLink();
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

                    $dep_text .= '</li>';
                }

                print '<ul>' . $dep_text . '</ul>';

            }

            print "</td>\n";

        } else {
            // Simple view
            print '  <td><a href="/package/' . htmlspecialchars($name) . '/download/' . $release_version . '">' . $release_version . "</a></td>\n";
            print '  <td>' . make_utc_date(strtotime($info['releasedate']), 'Y-m-d') . ' &nbsp; &nbsp; ' . htmlspecialchars($info['state']) . "</td>\n";
        }

        print " </tr>\n";
    }

    print "</table>\n";

    // }}}

} else if ($action == 'docs') {

    // {{{ Documentation

    print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    print '<tr>';
    print '<th class="headrow" style="width: 50%">&raquo; End-user documentation</th>';
    print '<th class="headrow" style="width: 50%">&raquo; API documentation</th>';
    print '</tr>';
    print '<tr>';
    print '<td class="ulcell">';

    if (!empty($doc_link)) {
        print '<ul><li><a href="' . htmlspecialchars($doc_link) . '">End-user Documentation</a></li></ul>';
    } else {
        print '<p>No end-user documentation is available for this package.</p>';
    }

    print '</td>';
    print '<td class="textcell">';

    if ($rel_count > 0) {
        print '<p>Auto-generated API documentation for each ';
        print 'release is available.</p>';

        print '<ul>';

        foreach ($pkg['releases'] as $r_version => $release) {
            print '<li><a href="/package/' . htmlspecialchars($name) . '/docs/' . $r_version . '/">' . $r_version . '</a></li>';
        }

        print '</ul>';
        print '<p>This documentation has been generated from the ';
        print 'inline comments in the source code using ';
        print '<a href="/package/phpDocumentor/">phpDocumentor</a>.</p>';
    } else {
        print '<p>Auto-generated API documentation will be available ';
        print 'once that this package has rolled a release.</p>';
    }

    print '</td>';
    print '</tr>';
    print '</table>';

    // }}}
} elseif ($action == 'trackbacks') {

    // Generate trackback list
    $trackbacks = Damblan_Trackback::listTrackbacks($dbh, $name, !$trackbackIsAdmin);

    print '<p>This page provides a list of trackbacks, which have been received to this package. A trackback is usually generated,
when a weblog entry is created, which is related to the package. If you want to learn more about trackbacks, please take a look at
&quot; <a href="http://www.movabletype.org/trackback/beginners/">A Beginner\'s Guide to TrackBack</a>&quot; (by movabletype.org).</p>';

    print '<p>The trackback URL for this package is: <a href="'.$tmpTrackback->trackback_url.'">'.$tmpTrackback->trackback_url.'</a>';

    if ($trackbackIsAdmin) {
        print '<div class="explain">You may manipulate the trackbacks of this package. In contrast to normal users, you see approved and pending trackbacks </div>';
    }

    if (count($trackbacks) == 0) {
        print '<p>Sorry, there are no trackbacks for this package, yet.</p>';
    }

    print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    foreach ($trackbacks as $trackback) {
        print '<tr>';
        print '<th class="others">';
        print 'Weblog:';
        print '</th>';
        print '<td class="ulcell" style="width:100%">';
        print $trackback->blog_name;
        print '</td>';
        print '</tr>';

        if ($trackbackIsAdmin) {
            print '<tr>';
            print '<th class="others">';
            print 'Approved:';
            print '</th>';
            print '<td class="ulcell">';
            print ($trackback->approved) ? '<b>yes</b>' : '<b>no</b>';
            print '</td>';
            print '</tr>';
        }
        print '<tr>';
        print '<th class="others">';
        print 'Title:';
        print '</th>';
        print '<td class="ulcell">';
        print '<a href="'.$trackback->url.'">'.$trackback->title.'</a>';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<th class="others">';
        print 'Date:';
        print '</th>';
        print '<td class="ulcell">';
        print make_utc_date($trackback->timestamp, 'Y-m-d');
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<th class="others">';
        print '</th>';
        print '<td class="ulcell">';
        print  $trackback->excerpt;
        print '</td>';
        print '</tr>';

        if ($trackbackIsAdmin) {
            print '<tr>';
            print '<th class="others">';
            print '</th>';
            print '<td class="ulcell">';
            if (!$trackback->approved) {
                print '[<a href="/trackback/trackback-admin.php?action=approve&id='.$trackback->id.'&timestamp='.$trackback->timestamp.'">Approve</a>] ';
            }
            print '[<a href="/trackback/trackback-admin.php?action=delete&id='.$trackback->id.'&timestamp='.$trackback->timestamp.'">Delete</a>]';
            print '</td>';
            print '</tr>';
        }

        print '<tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>';

    }
    print '</table>';
}

// }}}

response_footer();
?>
