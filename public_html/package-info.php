<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2004 The PHP Group                                |
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

// }}}
// {{{ page header

if ($version) {
    response_header('Package :: '.$name.' :: '.$version);
} else {
    response_header('Package :: '.$name);
}

html_category_urhere($pkg['categoryid'], true);

print '<h2>Package Information: '.$name;
if ($version) {
    print " $version";
}

print "</h2>\n";

$nav_items = array('Main'          => array('url'   => '',
                                            'title' => ''),
                   'Download'      => array('url'   => 'download',
                                            'title' => 'Download releases of this package'),
                   'Documentation' => array('url'   => 'docs',
                                            'title' => 'Read the available documentation'),
                   'Bugs'          => array('url'   => 'bugs',
                                            'title' => 'View/Report Bugs')
                   );

if (isset($auth_user) && is_object($auth_user)) {
    $nav_items['Edit']             = array('url'   => '/package-edit.php?id='.$pacid,
                                           'title' => 'Edit this package"');
    $nav_items['Delete']           = array('url'   => '/package-delete.php?id='.$pacid,
                                           'title' => 'Delete this package');
    $nav_items['Edit Maintainers'] = array('url'   => '/admin/package-maintainers.php?pid='.$pacid,
                                           'title' => 'Edit the maintainers of this package');
}

print '<div id="nav">';

foreach ($nav_items as $title => $item) {
    if (!empty($item['url']) && $item['url']{0} == '/') {
        $url = $item['url'];
    } else {
        $url = '/package/' . $name . '/' . $item['url'];
    }
    print '<a href="' . $url . '"'
        . ' title="' . $item['title'] . '" '
        . ($action == $item['url'] ? ' class="active" ' : '')
        . '">'
        . $title
        . '</a>';
}

print '</div><br clear="all" />';

// }}}
// {{{ Package Information

if (empty($action)) {

    // {{{ General information

    print '<table border="0" cellspacing="0" cellpadding="2" width="90%">';
    print '<tr>';
    print '<th class="headrow" width="50%">&raquo; Summary:</th>';
    print '<th class="headrow" width="50%">&raquo; License:</th>';
    print '</tr>';
    print '<tr>';
    print '<td valign="top">' . $summary . '</td>';
    print '<td valign="top">' . get_license_link($license) . '</td>';
    print '</tr>';

    print '<tr><td>&nbsp;</td></tr>';

    print '<tr>';
    print '<th class="headrow" width="50%">&raquo; Description:</th>';
    print '<th class="headrow">&raquo; More Information:</th>';
    print '</tr>';
    print '<tr>';
    print '<td valign="top">' . nl2br($description) . '</td>';
    print '<td valign="top">';

    print '<ul>';

    if (!empty($homepage)) {
        print '<li>Homepage: ' . make_link($homepage) . '</li>';
    }
    if (!empty($cvs_link)) {
        print '<li><a href="' . $cvs_link . '" title="Browse the source tree (in CVS, Subversion or another RCS) of this package">Browse the source tree</a></li>';
    }
    print '<li><a href="/feeds/pkg_' . strtolower($name) . '.rss" title="RSS feed for the releases of the package">RSS release feed</a></li>';
    print '</td>';

    print '</tr>';

    print '<tr><td>&nbsp;</td></tr>';

    print '<tr>';
    print '<th class="headrow" width="50%">&raquo; Maintainers:</th>';
    print '<th class="headrow">&raquo; Packages that depend on ' . $name . ':</th>';
    print '</tr>';
    print '<tr>';
    print '<td valign="top">' . $accounts . '</td>';

    // {{{ Dependants

    echo '<td>';

    $dependants = package::getDependants($name);
    if ($rel_count > 0 && count($dependants) > 0) {

        echo '<ul>';

        foreach ($dependants as $dep) {
            $obj =& new PEAR_Package($dbh, $dep['p_name']);
            echo '<li>' . $obj->makeLink() . '</li>';
        }

        echo '</ul>';

    }

    echo '</td>';

    // }}}

    print '</tr>';

    print '</table>';

    // }}}

} elseif ($action == 'download') {

    // {{{ Download

    $i = 0;

    print '<table border="0" cellspacing="0" cellpadding="2" width="90%">';
    print '<tr>';
    print '<th class="headrow" width="20%">&raquo; Version:</th>';
    print '<th class="headrow">&raquo; Information:</th>';
    print '</tr>';

    foreach ($pkg['releases'] as $release_version => $info) {
        print '<tr>';

        if (($i++ == 0 && empty($version)) || ($release_version == $version)) {
            // Detailed view

            print '<td valign="top">' . $release_version . '</td>';
            print '<td>';
            print '<a href="/get/' . $name . '-' . $release_version . '.tgz"><b>Download</b></a><br /><br />';
            print '<b>Release date:</b> ' . date("d M Y, H:i A", strtotime($info['releasedate'])) . '<br />'; 
            print '<b>Release state:</b> ' . $info['state'] . '<br /><br />'; 
            print '<b>Changelog:</b><br /><br />' . nl2br($info['releasenotes']) . '<br /><br />';

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

            print '</td>';

        } else {
            // Simple view
            print '<td colspan="2"><a href="/package/' . $name . '/download/' . $release_version . '">' . $release_version . '</a></td>';
        }

        print '</tr>';
    }

    print '</table>';

    // }}}

} else if ($action == 'docs') {

    // {{{ Documentation

    print '<table border="0" cellspacing="0" cellpadding="2" width="90%">';
    print '<tr>';
    print '<th class="headrow" width="50%">&raquo; End-user documentation:</th>';
    print '<th class="headrow" width="50%">&raquo; API documentation:</th>';
    print '</tr>';
    print '<tr>';
    print '<td valign="top">';

    if (!empty($doc_link)) {
        print '<ul><li><a href="' . $doc_link . '">End-user Documentation</a></li></ul>';
    } else {
        print '<p>No end-user documentation is available for this package.</p>';
    }

    print '</td>';
    print '<td valign="top">';

    if ($rel_count > 0) {
        print '<p>Auto-generated API documentation for each ';
        print 'release is available.</p>';
        
        print '<ul>';

        foreach ($pkg['releases'] as $r_version => $release) {
            print '<li><a href="/package/' . $name . '/docs/' . $r_version . '/">' . $r_version . '</a></li>';
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
}

// }}}

response_footer();
?>
