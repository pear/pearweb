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
   | Authors: Richard Heyes                                               |
   +----------------------------------------------------------------------+
   $Id$
*/

/*
 * TODO
 * o Number of packages in brackets does not include packages in subcategories
 * o Make headers in package list clickable for ordering
 */

$script_name  = htmlspecialchars($_SERVER['SCRIPT_NAME']);

require_once 'HTML/Table.php';
require_once 'Pager/Pager.php';

// Returns an appropriate query string for a self referencing link
function getQueryString($catpid, $catname, $showempty = false, $moreinfo = false)
{
    $querystring = array();
    $entries_cnt = 0;
    if ($catpid) {
        $querystring[] = 'catpid=' . (int)$catpid;
        $entries_cnt++;
    }

    if ($catname) {
        $querystring[] = 'catname=' . urlencode($catname);
        $entries_cnt++;
    }

    if ($showempty) {
        $querystring[] = 'showempty=' . (int)$showempty;
        $entries_cnt++;
    }

    if ($moreinfo) {
        $querystring[] = 'moreinfo=' . (int)$moreinfo;
        $entries_cnt++;
    }


    if ($entries_cnt) {
        return '?' . implode('&amp;', $querystring);
    }

    return '';
}

/*
 * Check input variables
 * Expected url vars: catpid (category parent id), catname, showempty
 */
$moreinfo  = isset($_GET['moreinfo'])  ? (int)$_GET['moreinfo']   : false;
$catpid    = isset($_GET['catpid'])    ? (int)$_GET['catpid']     : null;
$showempty = isset($_GET['showempty']) ? (bool)$_GET['showempty'] : false;

if (empty($catpid)) {
    $category_where = 'IS NULL';
    $catname = 'Top Level';
} else {
    $category_where = '= ' . $catpid;
    if (isset($_GET['catname']) && eregi('^[0-9a-z_ ]{1,80}$', $_GET['catname'])) {
        $catname = $_GET['catname'];
    } else {
        $catname = '';
    }
}

// the user is already at the top level
if (empty($catpid)) {
    $showempty_link = 'Top Level';
} else {
    $showempty_link = '<a href="'. $script_name . getQueryString($catpid, $catname, !$showempty, $moreinfo) . '">' . ($showempty ? 'Hide empty' : 'Show empty').'</a>';
}

/*
 * Main part of script
 */

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

if ($catpid) {
    $catname = $dbh->getOne('SELECT name FROM categories WHERE id = ' . $catpid);
    $category_title = "Package Browser :: " . htmlspecialchars($catname);
} else {
    $category_title = 'Package Browser :: Top Level';
}

response_header($category_title);

// 1) Show categories of this level
$php = isset($_GET['php']) ? $_GET['php'] : 'all';
$sql = '
    SELECT
        c.*, COUNT(DISTINCT p.id) AS npackages
    FROM categories c
    LEFT JOIN packages p ON p.category = c.id';

if ($php != 'all') {
    $sql .= '
    LEFT JOIN releases r ON p.id = r.package
    LEFT JOIN deps d ON r.package = d.package';
}

$sql .='
    WHERE
        p.package_type = "' . SITE . '" AND
        p.approved = 1 AND
        c.parent ' . $category_where;

if ($php != 'all') {
    $php_version = $php == '5' ? ' >= 5 AND d.relation = "ge"' : ' = 4';
    $sql .= '
        AND
        d.release = (SELECT id FROM releases WHERE package = p.id ORDER BY releasedate DESC LIMIT 1) AND
        d.optional = 0 AND
        d.type = "php" AND
        SUBSTRING(d.version, 1, 1) ' . $php_version;
}

$sql .= '
    GROUP BY c.id
    ORDER BY c.name';
$sth = $dbh->query($sql);

$table   = new HTML_Table('border="0" cellpadding="6" cellspacing="2" width="100%"');
$nrow    = 0;
$catdata = array();

// Get names of sub-categories
$subcats = $dbh->getAssoc("SELECT p.id AS pid, c.id AS id, c.name AS name, c.summary AS summary".
                          "  FROM categories c, categories p ".
                          " WHERE p.parent $category_where ".
                          "   AND c.parent = p.id ORDER BY c.name",
                          false, null, DB_FETCHMODE_ASSOC, true);

// Get names of sub-packages
$sql = '
    SELECT
        p.category, p.id AS id, p.name AS name, p.summary AS summary
    FROM categories c
    LEFT JOIN packages p ON p.category = c.id';

if ($php != 'all') {
    $sql .= '
    LEFT JOIN releases r ON p.id = r.package
    LEFT JOIN deps d ON r.package = d.package';
}

$sql .= '
    WHERE
        c.parent ' . $category_where . '
        AND p.approved = 1
        AND p.package_type = "' . SITE . '"
        AND (p.newpk_id IS NULL OR p.newpk_id = 0)
        AND p.category = c.id';

if ($php != 'all') {
    $php_version = $php == '5' ? ' >= 5 AND d.relation = "ge"' : ' = 4';
    $sql .= '
        AND
        d.release = (SELECT id FROM releases WHERE package = p.id ORDER BY releasedate DESC LIMIT 1) AND
        d.optional = 0 AND
        d.type = "php" AND
        SUBSTRING(d.version, 1, 1) ' . $php_version . '
        GROUP BY p.id';
}

$sql .= '
    ORDER BY p.name';

$subpkgs = $dbh->getAssoc($sql, false, null, DB_FETCHMODE_ASSOC, true);

$max_sub_links = 4;
$totalpackages = 0;
while ($sth->fetchInto($row)) {
    extract($row);
    $ncategories = ($cat_right - $cat_left - 1) / 2;

    if (!$showempty && $npackages < 1) {
        continue;  // Show categories with packages
    }

    $current_level_cat[$id] = $npackages;

    $sub_items = 0;

    $sub_links = array();
    if (isset($subcats[$id])) {
        foreach ($subcats[$id] as $subcat) {
            $sub_links[] = '<b><a href="'. $script_name .'?catpid='.$subcat['id'].'&amp;catname='.
                            urlencode($subcat['name']).'&amp;php=' . $php . '" title="'.htmlspecialchars($subcat['summary']).'">'.$subcat['name'].'</a></b>';
            if (count($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    if (isset($subpkgs[$id])) {
        foreach ($subpkgs[$id] as $subpkg) {
            $sub_links[] = '<a href="/package/' . $subpkg['name'] .'" title="'.
                            htmlspecialchars($subpkg['summary']).'">'.$subpkg['name'].'</a>';
            if (count($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    $sub_links = implode(', ', $sub_links);
    if (count($sub_links) >= $max_sub_links) {
        $sub_links .= ' ' . make_image("caret-r.gif", "[more]");
    }

    settype($npackages, 'string');
    settype($ncategories, 'string');

    $data  = '<font size="+1"><b><a href="'. $script_name .'?catpid='.$id.'&amp;catname='.urlencode($name).'&amp;php=' . $php . '">'.$name.'</a></b></font> ('.$npackages.')<br />';//$name; //array($name, $npackages, $ncategories, $summary);
    $data .= $sub_links.'<br />';
    $catdata[] = $data;

    $totalpackages += $npackages;

    if ($nrow++ % 2 == 1) {
        $table->addRow(array($catdata[0], $catdata[1]));
        $table->setCellAttributes($table->getRowCount()-1, 0, 'width="50%"');
        $table->setCellAttributes($table->getRowCount()-1, 1, 'width="50%"');
        $catdata = array();
    }
} // End while

// Any left over (odd number of categories).
if (count($catdata) > 0){
    $table->addRow(array($catdata[0]));
    $table->setCellAttributes($table->getRowCount()-1, 0, 'width="50%"');
    $table->setCellAttributes($table->getRowCount()-1, 1, 'width="50%"');
}

/*
 * Begin code for showing packages if we
 * aren't at the top level.
 */
$numPages = $currentPage = 1;
if (!empty($catpid)) {
    $nrow = 0;
    // Subcategories list
    $minPackages = ($showempty) ? 0 : 1;

    $subcats = $dbh->getAll("SELECT id, name, summary FROM categories WHERE " .
                            "parent = $catpid", DB_FETCHMODE_ASSOC);


    if (count($subcats) > 0) {
        foreach ($subcats as $subcat) {
            if ($current_level_cat[$subcat['id']] < 1) {
                continue;
            }
            $subCategories[] = sprintf('<b><a href="%s?catpid=%d&catname=%s" title="%s">%s</a></b>',
                                       $script_name,
                                       $subcat['id'],
                                       urlencode($subcat['name']),
                                       htmlspecialchars($subcat['summary']),
                                       $subcat['name']);
        }
        $subCategories = implode(', ', $subCategories);
    }

    // Package list
    $php = isset($_GET['php']) ? $_GET['php'] : 'all';
    $sql = '
        SELECT
            p.id, p.name, p.summary, p.license, p.unmaintained, p.newpk_id,
            (SELECT COUNT(package) FROM releases WHERE package = p.id) AS numreleases,
            (SELECT state FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS status
        FROM packages p';

    if ($php != 'all') {
        $sql .= '
        LEFT JOIN releases r ON p.id = r.package
        LEFT JOIN deps d ON r.package = d.package';
    }

    $sql .= '
        WHERE
            p.package_type = ? AND p.approved = 1 AND p.category = ?';

    if ($php != 'all') {
        $php_version = $php == '5' ? ' >= 5 AND d.relation = "ge"' : ' = 4';
        $sql .= '
            AND
            d.release = (SELECT id FROM releases WHERE package = p.id ORDER BY releasedate DESC LIMIT 1) AND
            d.optional = 0 AND
            d.type = "php" AND
            SUBSTRING(d.version, 1, 1) ' . $php_version . '
            GROUP BY p.id';
    }

    $sql .='
        ORDER BY p.name ASC';
    $packages = $dbh->getAll($sql, array(SITE, $catpid));

    // Paging
    $total = count($packages);

    $pager_options = array(
        'mode'       => 'Sliding',
        'perPage'    => '15',
        'delta'      => 5,
        'totalItems' => $total,
        'urlVar'     => 'page',
        'lastPagePre'     => '[ <strong>',
        'lastPagePost'    => '</strong> ]',
        'firstPagePre'    => '[ <strong>',
        'firstPagePost'   => '</strong> ]',
        'spacesBeforeSeparator' => 2,
        'spacesAfterSeparator ' => 1,
        //'linkClass'  => '',
        'curPageLinkClassName'  => 'current',
    );
    $pager = Pager::factory($pager_options);
    list($first, $last) = $pager->getOffsetByPageId();
    $pager_links = $pager->links;

    $currentPage = $pager->getCurrentPageID();
    $numPages    = $pager->numPages();
    $packages = array_slice($packages, $first - 1, 15);

    foreach ($packages as $key => $pkg) {
        $extendedInfo = array();
        $extendedInfo['status'] = $pkg['status'];

        // Make status coloured
        switch ($extendedInfo['status']) {
            case 'stable':
                $extendedInfo['status'] = '<span style="color: #006600">Stable</span>';
                break;

            case 'beta':
                $extendedInfo['status'] = '<span style="color: #ffc705">Beta</span>';
                break;

            case 'alpha':
                $extendedInfo['status'] = '<span style="color: #ff0000">Alpha</span>';
                break;
        }

        if ($pkg['unmaintained'] == 0) {
            $m = '<span style="color: #006600">Yes</span>';
        } else {
            $m = '<span style="color: #ff0000">No</span>';
        }
        $extendedInfo['maintained'] = $m;

        if (!empty($pkg['newpk_id'])) {
            $d = $dbh->getOne('SELECT name FROM packages WHERE id = ' . $pkg['newpk_id'] . ' ORDER BY id DESC LIMIT 1');
            $msg = 'This package has been deprecated in favor of <a href="/package/'.  $d .'">' . $d . '</a>';
            $extendedInfo['deprecated'] = $msg;
        }

        $packages[$key]['eInfo'] = $extendedInfo;
    }
}

// Template
include PEARWEB_TEMPLATEDIR . 'packages.html';