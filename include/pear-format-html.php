<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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

/* Send charset */
header('Content-Type: text/html; charset=iso-8859-1');

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'error_handler');

require_once 'layout.php';

$GLOBALS['main_menu'] = array(
    '/index.php'           => 'Home',
    '/news/'               => 'News'
);

$GLOBALS['docu_menu'] = array(
    '/manual/en/about-pear.php' => 'About PEAR',
    '/manual/index.php'    => 'Manual',
    '/manual/en/faq.php'   => 'FAQ',
    '/support.php'         => 'Support',
    '/group/'              => 'The PEAR Group'
);

$GLOBALS['downloads_menu'] = array(
    '/packages.php'        => 'List Packages',
    '/package-search.php'  => 'Search Packages',
    '/package-stats.php'   => 'Statistics'
);

$GLOBALS['developer_menu'] = array(
    '/accounts.php'        => 'List Accounts',
    '/release-upload.php'  => 'Upload Release',
    '/package-new.php'     => 'New Package'
);

$GLOBALS['proposal_menu'] = array(
    '/pepr/pepr-overview.php'       => 'Browse proposals',
    '/pepr/pepr-proposal-edit.php'  => 'New proposal'
);

$GLOBALS['admin_menu'] = array(
    '/admin/'                     => 'Overview'
);

$GLOBALS['_style'] = '';

function response_header($title = 'The PHP Extension and Application Repository', $style = false)
{
    global $_style, $_header_done, $SIDEBAR_DATA;
    if ($_header_done) {
        return;
    }
    $_header_done = true;
    $_style = $style;
    $rts = rtrim($SIDEBAR_DATA);
    if (substr($rts, -1) == '-') {
        $SIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        global $main_menu, $docu_menu, $downloads_menu, $auth_user, $proposal_menu;
        $SIDEBAR_DATA .= draw_navigation($main_menu);
        $SIDEBAR_DATA .= draw_navigation($docu_menu, 'Documentation:');
        $SIDEBAR_DATA .= draw_navigation($downloads_menu, 'Downloads:');
        $SIDEBAR_DATA .= draw_navigation($proposal_menu, 'Package Proposals:');
        init_auth_user();
        if (!empty($auth_user)) {
            if (!empty($auth_user->registered)) {
                global $developer_menu;
                if (auth_check('pear.dev')) {
                    $SIDEBAR_DATA .= draw_navigation($developer_menu, 'Developers:');
                }
            }
            if ($auth_user->isAdmin()) {
                global $admin_menu;
                $SIDEBAR_DATA .= draw_navigation($admin_menu, 'Administrators:');
            }
        }
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PEAR :: <?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" href="/style.css" />
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/feeds/latest.rss" />
</head>

<body <?php
    if (!empty($GLOBALS['ONLOAD'])) {
        print 'onload="' . $GLOBALS['ONLOAD']. '"';
    }
?>>
<div>
<a id="TOP" />
</div>
<table cellspacing="0" cellpadding="0" style="width: 100%; border: 0px;">
  <tr style="background-color: #339900;">
    <td rowspan="2" colspan="2" style="width: 120px; height: 1px; text-align: left;">
<?php print_link('/', make_image('pearsmall.gif', 'PEAR', false, false, false, false, 'margin: 5px;') ); ?><br />
    </td>
    <td colspan="3" style="height: 1px; text-align: right; vertical-align: top;">&nbsp;</td>
  </tr>

  <tr style="background-color: #339900;">
    <td colspan="3" style="height: 1px; text-align: right; vertical-align: bottom;">
      <?php

    if (empty($_COOKIE['PEAR_USER'])) {
        echo '<div class="menuBlack">';
        print_link('/account-request.php', 'Register', false);
        echo delim();
        if ($_SERVER['QUERY_STRING'] && $_SERVER['QUERY_STRING'] != 'logout=1') {
            print_link('/login.php?redirect=' . urlencode(
                       "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}"),
                       'Login', false);
        } else {
            print_link('/login.php?redirect=' . $_SERVER['PHP_SELF'],
                       'Login', false);
        }
    } else {
        print '<span class="menuWhite"><small>';
        print '<a href="/user/' . $_COOKIE['PEAR_USER'] . '">logged in as ';
        print strtoupper($_COOKIE['PEAR_USER']);
        print '</a>&nbsp;</small></span><br />';
        echo '<div class="menuBlack">';
        print_link('/?logout=1', 'Logout', false);
    }

    echo delim();
    print_link('/manual/', 'Docs', false);
    echo delim();
    print_link('/packages.php', 'Packages', false);
    echo delim();
    print_link('/support.php','Support',false);
    echo delim();
    print_link('/bugs/','Bugs',false);
      ?>&nbsp;<br />
      <?php spacer(2,2); ?>
      </div>
    </td>
  </tr>

  <tr style="background-color: #003300;"><td colspan="5" style="height: 1px;"><?php spacer(1,1);?><br /></td></tr>

  <tr style="background-color: #006600;">
    <td colspan="5" style="height: 1px; text-align: right; vertical-align: top;" class="menuWhite">
    <form method="post" action="/search.php">
    <div>
    <small>Search for</small>
    <input class="small" type="text" name="search_string" value="" size="20" />
    <small>in the</small>
    <select name="search_in" class="small">
        <option value="packages">Packages</option>
        <option value="site">This site (using Google)</option>
        <option value="pear-dev">Developer mailing list</option>
        <option value="pear-general">General mailing list</option>
        <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" style="vertical-align: middle;" />&nbsp;<br />
    </div>
    </form>
    </td></tr>

  <tr style="background-color: #003300;"><td colspan="5" style="height: 1px;"><?php spacer(1,1);?><br /></td></tr>

  <!-- Middle section -->

 <tr valign="top">
<?php if (isset($SIDEBAR_DATA)) { ?>
  <td colspan="2" class="sidebar_left" style="width: 149px; background-color: #F0F0F0;">
   <table cellpadding="4" cellspacing="0" style="width: 149px;">
    <tr style="vertical-align: top;">
     <td><?php echo $SIDEBAR_DATA?><br /></td>
    </tr>
   </table>
  </td>
<?php } ?>
  <td>
   <table cellpadding="10" cellspacing="0" style="width: 100%;">
    <tr>
     <td valign="top">
<?php
}

function &draw_navigation($data, $menu_title='')
{
    $html = "<br />\n";
    if (!empty($menu_title)) {
        $html .= "<strong>$menu_title</strong>\n";
        $html .= "<br />\n";
    }

    foreach ($data as $url => $tit) {
        $tt = str_replace(' ', '&nbsp;', $tit);
        if ($url == $_SERVER['PHP_SELF']) {
            $html .= make_image('box-1.gif') . "<strong>$tt</strong><br />\n";
        } else {
            $html .= make_image('box-0.gif') . "<a href=\"$url\">$tt</a><br />\n";
        }
    }
    return $html;
}

function response_footer($style = false)
{
    global $LAST_UPDATED, $MIRRORS, $MYSITE, $COUNTRIES,$SCRIPT_NAME, $RSIDEBAR_DATA;

    static $called;
    if ($called) {
        return;
    }
    $called = true;
    if (!$style) {
        $style = $GLOBALS['_style'];
    }


?>
     </td>
    </tr>
   </table>
  </td>

<?php if (isset($RSIDEBAR_DATA)) { ?>
  <td class="sidebar_right" style="background-color: #F0F0F0; width: 149px;">
    <table cellpadding="4" cellspacing="0" style="width: 149px;">
     <tr valign="top">
      <td><?php echo $RSIDEBAR_DATA; ?><br />
     </td>
    </tr>
   </table>
  </td>
<?php } ?>

 </tr>

 <!-- Lower bar -->

  <tr style="background-color: #003300"><td colspan="5" style="height: 1px;"><?php spacer(1,1);?><br /></td></tr>
  <tr style="background-color: #339900">
      <td colspan="5" style="height: 1px; text-align: right; vertical-align: bottom;">
      <div class="menuBlack" style="padding-right: 5px;">
<?php
print_link('/about/privacy.php', 'PRIVACY POLICY', false);
echo delim();
print_link('/credits.php', 'CREDITS', false);
?>
    </div>
      </td>
  </tr>
  <tr style="background-color: #003300"><td colspan="5" style="height: 1px;"><?php spacer(1,1); ?><br /></td></tr>

  <tr style="background-color: #CCCCCC; vertical-align: top;">
    <td colspan="5" style="height: 1px;">
	  <table cellspacing="0" cellpadding="5" style="width: 100%; border: 0px;">
	  	<tr>
		 <td>
		  <small>
	      <?php print_link('/copyright.php', 'Copyright &copy; 2001-2004 The PHP Group'); ?><br />
	      All rights reserved.<br />
	      </small>
		 </td>
		 <td style="text-align: right; vertical-align: top;">
		  <small>
	      Last updated: <?php echo $LAST_UPDATED; ?><br />
	      Bandwidth and hardware provided by: <?php ($_SERVER['SERVER_NAME'] == 'pear.php.net' ? print_link('http://www.pair.com/', 'pair Networks') : print '<i>This is an unofficial mirror!</i>'); ?>
	      </small>
		 </td>
		</tr>
      </table>
    </td>
  </tr>
</table>

</body>
</html>
<?php
}

function menu_link($text, $url) {
    echo "<p>\n";
    print_link($url, make_image('pear_item.gif', $text) );
    echo '&nbsp;';
    print_link($url, '<strong>' . $text . '</strong>' );
    echo "</p>\n";
}

function report_error($error)
{
    if (PEAR::isError($error)) {
        $error = $error->getMessage();
        $info = $error->getUserInfo();
        if ($info) {
            $error .= " : $info";
        }
    }
    print "<span style=\"color: #990000;\"><strong>$error</strong></span><br />\n";
}

function error_handler($errobj, $title = 'Error')
{
    if (PEAR::isError($errobj)) {
        $msg = $errobj->getMessage();
        $info = $errobj->getUserInfo();
    } else {
        $msg = $errobj;
        $info = '';
    }
    response_header($title);
    $report = "Error: $msg";
    if ((DEVBOX || !empty($_GET['__debug'])) && $info) {
        $report .= ": $info";
    }
    print "<span class=\"error\">$report</span><br />\n";
    response_footer();
    exit;
}


class BorderBox {
    function BorderBox($title, $width = '90%', $indent = '', $cols = 1,
                       $open = false) {
        $this->title = $title;
        $this->width = $width;
        $this->indent = $indent;
        $this->cols = $cols;
        $this->open = $open;
        $this->start();
    }

    function start() {
        $title = $this->title;
        if (is_array($title)) {
            $title = implode("</th><th>", $title);
        }
        $i = $this->indent;
        print "<!-- border box starts -->\n";
        print "$i<table cellpadding=\"0\" cellspacing=\"1\" style=\"width: $this->width; border: 0px;\">\n";
        print "$i <tr>\n";
        print "$i  <td style=\"background-color: #000000;\">\n";
        print "$i   <table cellpadding=\"2\" cellspacing=\"1\" style=\"width: 100%; border: 0px;\">\n";
        print "$i    <tr style=\"background-color: #CCCCCC;\">\n";
        print "$i     <th";
        if ($this->cols > 1) {
            print " colspan=\"$this->cols\"";
        }
        print ">$title</th>\n";
        print "$i    </tr>\n";
        if (!$this->open) {
            print "$i    <tr style=\"background-color: #FFFFFF;\">\n";
            print "$i     <td>\n";
        }
    }

    function end() {
        $i = $this->indent;
        if (!$this->open) {
            print "$i     </td>\n";
            print "$i    </tr>\n";
        }
        print "$i   </table>\n";
        print "$i  </td>\n";
        print "$i </tr>\n";
        print "$i</table>\n";
        print "<!-- border box ends -->\n";
    }

    function horizHeadRow($heading /* ... */) {
        $i = $this->indent;
        print "$i    <tr>\n";
        print "$i     <th style=\"vertical-align: top; background-color: #CCCCCC;\">$heading</th>\n";
        for ($j = 0; $j < $this->cols-1; $j++) {
            print "$i     <td style=\"vertical-align: top; background-color: #E8E8E8\">";
            $data = @func_get_arg($j + 1);
            if (empty($data)) {
                print "&nbsp;";
            } else {
                print $data;
            }
            print "</td>\n";
        }
        print "$i    </tr>\n";

    }

    function headRow() {
        $i = $this->indent;
        print "$i    <tr>\n";
        for ($j = 0; $j < $this->cols; $j++) {
            print "$i     <th style=\"vertical-align: top; background-color: #FFFFFF;\">";
            $data = @func_get_arg($j);
            if (empty($data)) {
                print '&nbsp;';
            } else {
                print $data;
            }
            print "</th>\n";
        }
        print "$i    </tr>\n";
    }

    function plainRow(/* ... */) {
        $i = $this->indent;
        print "$i    <tr>\n";
        for ($j = 0; $j < $this->cols; $j++) {
            print "$i     <td style=\"vertical-align: top; background-color: #FFFFFF;\">";
            $data = @func_get_arg($j);
            if (empty($data)) {
                print '&nbsp;';
            } else {
                print $data;
            }
            print "</td>\n";
        }
        print "$i    </tr>\n";
    }

    function fullRow($text) {
        $i = $this->indent;
        print "$i    <tr>\n";
        print "$i     <td style=\"background-color: #E8E8E8;\"";
        if ($this->cols > 1) {
            print " colspan=\"$this->cols\"";
        }
        print ">$text</td>\n";
        print "$i    </tr>\n";

    }
}

/**
* prints "urhere" menu bar
* Top Level :: XML :: XML_RPC
* @param bool $link_lastest If the last category should or not be a link
*/
function html_category_urhere($id, $link_lastest = false)
{
    $html = '<a href="/packages.php">Top Level</a>';
    if ($id !== null) {
        global $dbh;
        $res = $dbh->query("SELECT c.id, c.name
                            FROM categories c, categories cat
                            WHERE cat.id = $id
                            AND c.cat_left <= cat.cat_left
                            AND c.cat_right >= cat.cat_right");
        $nrows = $res->numRows();
        $i = 0;
        while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            if (!$link_lastest && $i >= $nrows -1) {
                break;
            }
            $html .= "  :: ".
                     "<a href=\"/packages.php?catpid={$row['id']}&amp;catname={$row['name']}\">".
                     "{$row['name']}</a>";
            $i++;
        }
        if (!$link_lastest) {
            $html .= '  :: <strong>' . $row['name'] . '</strong>';
        }
    }
    print $html;
}

/**
* Returns an absolute URL using Net_URL
*
* @param  string $url All/part of a url
* @return string      Full url
*/
function getURL($url)
{
	include_once 'Net/URL.php';
	$obj = new Net_URL($url);
	return $obj->getURL();
}

/**
* Redirects to the given full or partial URL.
* will turn the given url into an absolute url
* using the above getURL() function. This function
* does not return.
*
* @param string $url Full/partial url to redirect to
*/
function localRedirect($url)
{
	header('Location: ' . getURL($url));
	exit;
}

/**
 * Get URL to license text
 *
 * @todo  Add more licenses here
 * @param string Name of the license
 * @return string Link to license URL
 */
function get_license_link($license = "")
{
    switch ($license) {

        case 'PHP License' :
        case 'PHP 2.02' :
            $link = 'http://www.php.net/license/2_02.txt';
            break;

        case 'GPL' :
        case 'GNU General Public License' :
            $link = 'http://www.gnu.org/licenses/gpl.html';
            break;

        case 'LGPL' :
        case 'GNU Lesser General Public License' :
            $link = 'http://www.gnu.org/licenses/lgpl.html';
            break;

        default :
            $link = '';
            break;
    }

    return ($link != '' ? '<a href="' . $link . '">' . $license . "</a>\n" : $license);
}

function display_user_notes($user, $width = '50%')
{
    global $dbh;
    $bb = new BorderBox("Notes for user $user", $width);
    $notes = $dbh->getAssoc("SELECT id,nby,ntime,note FROM notes 
                WHERE uid = ? ORDER BY ntime", true, array($user));
    if (!empty($notes)) {
        print '<table cellpadding="2" cellspacing="0" style="border: 0px;">' . "\n";
        foreach ($notes as $nid => $data) {
        print " <tr>\n";
        print "  <td>\n";
        print "   <strong>{$data['nby']} {$data['ntime']}:</strong>";
        print "<br />\n";
        print "   ".htmlspecialchars($data['note'])."\n";
        print "  </td>\n";
        print " </tr>\n";
        print " <tr><td>&nbsp;</td></tr>\n";
        }
        print "</table>\n";
    } else {
        print 'No notes.';
    }
    $bb->end();
    return sizeof($notes);
}

// {{{ user_link()

/**
 * Create link to the account information page and to the user's wishlist
 *
 * @param string User's handle
 * @param bool   Should the wishlist link be skipped?
 * @return mixed False on error, otherwise string
 */
function user_link($handle, $compact = false)
{
    global $dbh;

    $query = "SELECT name, wishlist FROM users WHERE handle = '" . $handle . "'";
    $row = $dbh->getRow($query, DB_FETCHMODE_ASSOC);

    if (!is_array($row)) {
        return false;
    }

    return sprintf("<a href=\"/user/%s\">%s</a>%s\n",
                   $handle,
                   $row['name'],
                   ($row['wishlist'] != "" && $compact == false ? " [<a href=\"" . htmlentities($row['wishlist']) . "\">Wishlist</a>]" : '')
                   );
}

// }}}
?>
