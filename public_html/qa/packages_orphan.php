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
 * List orphan packages
 */
auth_require('pear.dev');

response_header('Quality Assurance Initiative - Orphan packages',
    false);

$query = "
    select p1.name as name1, p2.name as name2
    from packages p1
    left join packages p2 on p1.newpk_id=p2.id
    where p1.unmaintained=1 and p1.package_type = 'pear' and p1.approved = 1
    order by p1.name
";

$packages = $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);

if (count($packages) == 0) {
    echo '<p>There are no orphan packages</p>';
    response_footer();
    exit();
}

echo "<h3>List of orphan packages</h3>\n";

echo "<ul>\n";
foreach ($packages as $pck => $info) {

    $link = make_link('/package/' . $info['name1'],
        $info['name1'], '', 'title="' . $info['name1'] . '"');

    $link_superseding = '';

    if (!empty($info['name2'])) {
        $link_superseding = 'There is a superseding package: ';
        $link_superseding .= make_link('/package/' . $info['name2'],
        $info['name2'], '', 'title="' . $info['name2'] . '"');
    }

    echo '<li>' . $link . ' ' . $link_superseding . "</li>\n";
}
echo "</ul>\n";

response_footer();
?>
