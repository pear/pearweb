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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Accounts");

$page_size = 40;
$self = htmlspecialchars($_SERVER['PHP_SELF']);
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : null;
$letter = isset($_GET['letter']) ? strip_tags($_GET['letter']) : null;

echo "<h1>Accounts</h1>\n";

$sql = '
    SELECT SUBSTRING(handle,1,1) FROM users u
    LEFT JOIN karma k ON k.user = u.handle
    WHERE u.registered = 1 AND k.level = ? ORDER BY handle';

$all_firstletters = $dbh->getCol($sql, 0, array('pear.dev'));

// I wish there was a way to do this in mysql...
$first_letter_offsets = array();
for ($i = 0; $i < count($all_firstletters); $i++) {
    $l = $all_firstletters[$i];
    if (isset($first_letter_offsets[$l])) {
        continue;
    }
    $first_letter_offsets[$l] = $i;
}

if (preg_match('/^[a-z]\z/i', @$letter)) {
    $offset = $first_letter_offsets[$letter];
    $offset -= $offset % $page_size;
}

if (empty($show)) {
    $show = $page_size;
} else {
    settype($show, "integer");
}
settype($offset, "integer");

$sql = '
    SELECT COUNT(u.handle) FROM users u
    LEFT JOIN karma k ON k.user = u.handle
    WHERE u.registered = 1 AND k.level = ?';
$naccounts = $dbh->getOne($sql, array('pear.dev'));

$last_shown = $offset + $page_size - 1;

$firstletters = array_unique($all_firstletters);

$last = $offset - $page_size;
$lastlink = $self . "?offset=$last";
$next = $offset + $page_size;
$nextlink = $self. "?offset=$next";
echo '<table border="0" cellspacing="1" cellpadding="5" width="100%">' . "\n";
echo " <tr>\n";
echo '  <th class="form-label_top_center" style="font-size: 80%">';
if ($offset > 0) {
    echo "<a href=\"$lastlink\">&lt;&lt; Last $page_size</a>";
} else {
    echo "&nbsp;";
}
echo "</th>\n";
echo '  <th class="form-label_top_center" colspan="2">' . "\n";

echo '<table border="0" width="100%"><tr><td>';
foreach ($firstletters as $fl) {
    $o = $first_letter_offsets[$fl];
    if ($o >= $offset && $o <= $last_shown) {
        printf('<b>%s</b> ', strtoupper($fl));
    } else {
        printf('<a href="%s?letter=%s">%s</a> ', $self, $fl, strtoupper($fl));
    }
}
echo '</td><td rowspan="2">';
echo '<form method="get" action="#"><input type="button" onclick="';
$gourl = "http://" . PEAR_CHANNELNAME;
if ($_SERVER['SERVER_PORT'] != 80) {
    $gourl .= ":".$_SERVER['SERVER_PORT'];
}
$gourl .= "/user/";
echo "u=prompt('Go to account:','');if(u)location.href='$gourl'+u;";
echo '" value="Go to account.." /></form></td></tr><tr><td>';
printf("Displaying accounts %d - %d of %d<br />\n",
        $offset, min($offset+$show, $naccounts), $naccounts);

$sql = 'SELECT u.handle, name, u.email, u.homepage, u.showemail '.
       'FROM users u
       LEFT JOIN karma k ON k.user = u.handle
       WHERE u.registered = 1 AND k.level = "pear.dev" ORDER BY handle';
$sth = $dbh->limitQuery($sql, $offset, $show);

echo "</td></tr></table>\n";
echo "</th>\n";
echo '  <th class="form-label_top_center" style="font-size: 80%">';
if ($offset + $page_size < $naccounts) {
    $nn = min($page_size, $naccounts - $offset - $page_size);
    echo "<a href=\"$nextlink\">Next $nn &gt;&gt;</a>";
} else {
    echo "&nbsp;";
}
echo "</th>\n";
echo " </tr>\n";

echo " <tr>\n";
echo '  <th class="form-label_top_center">Handle</th>' . "\n";
echo '  <th class="form-label_top_center">Name</th>' . "\n";
echo '  <th class="form-label_top_center">Email</th>' . "\n";
echo '  <th class="form-label_top_center">Homepage</th>' . "\n";
echo " </tr>\n";

$rowno = 0;
while (is_array($row = $sth->fetchRow(DB_FETCHMODE_ASSOC))) {
    extract($row);
    if (++$rowno % 2) {
        echo " <tr bgcolor=\"#e8e8e8\">\n";
    } else {
        echo " <tr bgcolor=\"#e0e0e0\">\n";
    }
    echo "  <td>" . make_link("/user/" . $handle, $handle) . "</td>\n";
    echo '  <td style="white-space: nowrap">' . $name . "</td>\n";

    if ($showemail && !empty($auth_user) && !empty($auth_user->registered)) {
        echo '  <td>' . make_mailto_link($email) . "</td>\n";
    } else {
        echo '  <td>(';
        echo make_link('/account-mail.php?handle=' . $handle, 'not shown');
        echo ")</td>\n";
    }
    if (!empty($homepage)) {
        echo '<td><a href="' . $homepage . '" rel="nofollow">Homepage</a></td>' . "\n";
    } else {
        echo '<td>&nbsp;</td>';
    }
    echo " </tr>\n";
}

echo " <tr>\n";
echo '  <th class="form-label_top_center" style="font-size: 80%">' . "\n";
if ($offset > 0) {
    echo "<a href=\"$lastlink\">&lt;&lt; Last $page_size</a>";
} else {
    echo "&nbsp;";
}
echo "</th>\n";
echo '  <th class="form-label_top_center" colspan="2">';

echo '<table border="0"><tr><td>';
echo '</td><td rowspan="2">&nbsp;';
echo "</td></tr></table>\n";
echo "</th>\n";
echo '  <th class="form-label_top_center" style="font-size: 80%">';
if ($offset + $page_size < $naccounts) {
    $nn = min($page_size, $naccounts - $offset - $page_size);
    echo "<a href=\"$nextlink\">Next $nn &gt;&gt;</a>";
} else {
    echo "&nbsp;";
}
echo "</th>\n";
echo " </tr>\n";
echo "</table>\n";

response_footer();
