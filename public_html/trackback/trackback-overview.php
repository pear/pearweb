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
   | Authors: Tobias Schlitt <toby@php.net>                               |
   |                                                                      |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once 'DB/Pager.php';

require_once 'Damblan/Trackback.php';
require_once 'Damblan/Karma.php';

function printPager($pager, $max, $unapprovedOnly)
{
    $req = '&max='.$max.(($unapprovedOnly) ? '&unapprovedOnly=1' : '');
    print '<p align="center">';
    if ($pager['current'] != 1) {
        print '<a href="/trackback/trackback-overview.php?offset='.$pager['pages'][$pager['firstpage']].$req.'">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;';
        print '<a href="/trackback/trackback-overview.php?offset='.$pager['prev'].$req.'">&lt;</a>&nbsp;&nbsp;&nbsp;';
    } else {
        print '&lt;&lt;&nbsp;&nbsp;&nbsp;';
        print '&lt;&nbsp;&nbsp;&nbsp;';
    }
    foreach ($pager['pages'] as $page => $offset) {
        if ($page != $pager['current']) {
            print '&nbsp;[<a href="/trackback/trackback-overview.php?offset='.$offset.$req.'">'.$page.'</a>]&nbsp;';
        } else {
            print '&nbsp;['.$page.']&nbsp;';
        }
    }
    if ($pager['current'] != $pager['numpages']) {
        print '&nbsp;&nbsp;&nbsp;<a href="/trackback/trackback-overview.php?offset='.$pager['pages'][$pager['lastpage']].$req.'">&gt;</a>';
        print '&nbsp;&nbsp;&nbsp;<a href="/trackback/trackback-overview.php?offset='.$pager['next'].$req.'">&gt;&gt;</a>';
    } else {
        print '&nbsp;&nbsp;&gt;&nbsp;';
        print '&nbsp;&nbsp;&gt;&gt;';
    }
}

$offset = (isset($_GET['offset'])) ? (int)$_GET['offset'] : 0;
$number = (isset($_GET['number'])) ? (int)$_GET['number'] : 10;
$lastMax = (isset($_GET['max'])) ? (int)$_GET['max'] : null;
$unapprovedOnly = (isset($_GET['unapprovedOnly'])) ? true : false;

// Determine administrative user
$karma =& new Damblan_Karma($dbh);
$trackbackIsAdmin = (isset($_COOKIE['PEAR_USER']) && $karma->has($_COOKIE['PEAR_USER'], 'pear.dev'));

// Prepare pager
$max = Damblan_Trackback::getCount($dbh, !$trackbackIsAdmin, $unapprovedOnly);
if ($max != $lastMax) {
    $offset = 0;
}
$pager = DB_Pager::getData($offset, $number, $max);

if ($max < 1) {
    PEAR::raiseError('Sorry, no trackbacks were found.');
}

// Fetch trackbacks
$trackbacks = Damblan_Trackback::recentTrackbacks($dbh, $offset, $number, !$trackbackIsAdmin, $unapprovedOnly);

response_header('Trackback overview');

printPager($pager, $max, $unapprovedOnly);

if ($trackbackIsAdmin) {
    if (!$unapprovedOnly) {
        print '<p><a href="/trackback/trackback-overview.php?unapprovedOnly=1">View only not appoved trackbacks.</a></p>';
    } else {
        print '<p><a href="/trackback/trackback-overview.php">View all trackbacks.</a></p>';
    }
}

// Printing pager.

print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
foreach ($trackbacks as $trackback) {
    print '<tr>';
    print '<th class="others">';
    print 'Package:';
    print '</th>';
    print '<td class="ulcell" style="width:100%">';
    print $trackback->get('id');
    print '</td>';
    print '</tr>';
    
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

printPager($pager, $max, $unapprovedOnly);

response_footer();
?>
