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

$site = new Damblan_URL;


// {{{ setup, queries
$params = array('package|pacid' => '', 'action' => '', 'version' => '', 'allowtrackbacks' => '');
$site->getElements($params);

$pacid = $params['package|pacid'];

// Package data
if (!empty($pacid)) {
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
$new_package  = htmlspecialchars($pkg['new_package']);

// Maintainer information
$maintainers = maintainer::get($pacid);
$accounts  = '<ul>';

foreach ($maintainers as $handle => $row) {
    $accounts .= '<li>';
    $accounts .= user_link($handle);
    $accounts .= '(' . $row['role'] .
                  ($row['active'] == 0 ? ', inactive' : '')
		. ')';
    $accounts .= '</li>';
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

print '<h1>Package Information: ' . $name;
if ($version) {
    print ' ' .  $version;
}

print "</h1>\n";

print_package_navigation($pacid, $name, $action);

// }}}
// {{{ Package Information

if (empty($action)) {

    // {{{ General information

    /* UNMAINTAINED OR SUPERCEEDED PACKAGES WARNING */
    $dec_messages = array(
        'abandoned' => 'This package is not maintained anymore and has been superceded by <a href="/package/{{PACKAGE_NAME}}">{{PACKAGE_NAME}}</a>.',
        'superceded' => 'This package been superceded by <a href="/package/{{PACKAGE_NAME}}">{{PACKAGE_NAME}}</a> but is still maintained for bugs and security fixes',
        'unmaintained' => 'This package is not maintained, if you would like to take over please go to <a href="http://pear.php.net/manual/en/newmaint.takingover.php">this page</a>'
    );

    $dec_table = array(
        'abandoned'   => array('superceded' => 'Y', 'unmaintained' => 'Y'),
        'superceded'  => array('superceded' => 'Y', 'unmaintained' => 'N'),
        'unmaintained' => array('superceded' => 'N', 'unmaintained' => 'Y'),
    );

    $superceded = 'N';
    if ($new_package != '') {
        $superceded = 'Y';
    }

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
        $str .= str_replace('{{PACKAGE_NAME}}', $new_package, $dec_messages[$apply_rule]);
        $str .= '</div>';
        echo $str;
    }
    /* UNMAINTAINED OR SUPERCEDED PACKAGES WARNING */

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
    print '<li><a href="/package-stats.php?pid=' . $pkg['packageid'] . '" title="View download statistics for this package">View Download Statistics</a></li>';
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

    print '<a href="/package/' . htmlspecialchars($name) . '/download/All">Show All Changelogs</a>';
    print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
    print ' <tr>';
    print '  <th class="headrow" style="width: 20%">&raquo; Version</th>';
    print '  <th class="headrow">&raquo; Information</th>';
    print "</tr>\n";

    foreach ($pkg['releases'] as $release_version => $info) {
        print " <tr>\n";

        if ($show_all || ($i++ == 0 && empty($version)) || $release_version === $version) {
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

        print '<p><a href="/package/' . htmlspecialchars($name) . '/docs/latest/">Documentation for the latest release</a></p>';
        print hdelim();

        print '<strong>Complete list:</strong>';
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

    if ($pkg['blocktrackbacks']) {
        echo '<p>Trackbacks are disabled for this package. If you like to enable them, click below:</p>';
        echo '<p><a href="/package/' . $pkg['name'] . '/trackbacks/?allowtrackbacks=1">Allow trackbacks</a></p>';
        response_footer();
        exit();
    }

    include_once 'Damblan/Trackback.php';

    // Generate trackback list
    $trackbacks = Damblan_Trackback::listTrackbacks($dbh, $name, !$trackbackIsAdmin);

    print '<p>This page provides a list of trackbacks, which have been received to this package. A trackback is usually generated,
when a weblog entry is created, which is related to the package. If you want to learn more about trackbacks, please take a look at
what <a href="http://en.wikipedia.org/wiki/Trackback">Wikipedia writes about trackbacks</a>.</p>
<p>If you like to disable the trackbacks for this package, click here:
<p><a href="/package/' . $pkg['name'] . '/trackbacks/?allowtrackbacks=2">Disable trackbacks</a></p>';

    print '<p>The trackback URL for this package is: <a href="'.$trackback_uri.'">'.$trackback_uri.'</a>';

    if ($trackbackIsAdmin) {
        print '<div class="explain">You may manipulate the trackbacks of this package. In contrast to normal users, you see approved and pending trackbacks, while normal users only see the approved ones.</div>';
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
        print $trackback->get('blog_name');
        print '</td>';
        print '</tr>';

        if ($trackbackIsAdmin) {
            print '<tr>';
            print '<th class="others">';
            print 'Approved:';
            print '</th>';
            print '<td class="ulcell">';
            print ($trackback->get('approved')) ? '<b>yes</b>' : '<b>no</b>';
            print '</td>';
            print '</tr>';
        }
        print '<tr>';
        print '<th class="others">';
        print 'Title:';
        print '</th>';
        print '<td class="ulcell">';
        print '<a href="'.$trackback->get('url').'">'.$trackback->get('title').'</a>';
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<th class="others">';
        print 'Date:';
        print '</th>';
        print '<td class="ulcell">';
        print make_utc_date($trackback->get('timestamp'), 'Y-m-d');
        print '</td>';
        print '</tr>';

        print '<tr>';
        print '<th class="others">';
        print '</th>';
        print '<td class="ulcell">';
        print  $trackback->get('excerpt');
        print '</td>';
        print '</tr>';

        if ($trackbackIsAdmin) {
            print '<tr>';
            print '<th class="others">';
            print 'IP:';
            print '</th>';
            print '<td class="ulcell">';
            print $trackback->get('ip');
            print '</td>';
            print '</tr>';

            print '<tr>';
            print '<th class="others">';
            print '</th>';
            print '<td class="ulcell">';
            if (!$trackback->get('approved')) {
                print '[<a href="/trackback/trackback-admin.php?action=approve&id='.$trackback->get('id').'&timestamp='.$trackback->get('timestamp').'">Approve</a>] ';
            }
            print '[<a href="/trackback/trackback-admin.php?action=delete&id='.$trackback->get('id').'&timestamp='.$trackback->get('timestamp').'">Delete</a>]';
            print '</td>';
            print '</tr>';
        }

        print '<tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>';

    }
    print '</table>';
}

// }}}

response_footer();
