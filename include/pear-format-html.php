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

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'error_handler');

require_once 'pear-cache.php';

$encoding = 'iso-8859-1';
$extra_styles = array();

// Handling things related to the manual
if (substr($_SERVER['PHP_SELF'], 0, 7) == '/manual') {
    require_once 'pear-manual.php';
    $extra_styles[] = '/css/manual.css';

    // The Japanese manual translation needs UTF-8 encoding
    if (preg_match("=^/manual/ja=", $_SERVER['PHP_SELF'])) {
        $encoding = 'utf-8';

    // The Russian manual translation needs KOI8-R encoding
    } else if (preg_match("=^/manual/ru=", $_SERVER['PHP_SELF'])) {
        $encoding = 'KOI8-R';
    }
}

$GLOBALS['main_menu'] = array(
    '/index.php'           => 'Home',
    '/news/'               => 'News',
    '/qa/'                 => 'Quality Assurance',
    '/group/'              => 'The PEAR Group',
);

$GLOBALS['docu_menu'] = array(
    '/manual/en/about-pear.php' => 'About PEAR',
    '/manual/index.php'    => 'Manual',
    '/manual/en/faq.php'   => 'FAQ',
    '/support/'            => 'Support',
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
    '/pepr/pepr-overview.php'       => 'Browse Proposals',
    '/pepr/pepr-proposal-edit.php'  => 'New Proposal'
);

$GLOBALS['admin_menu'] = array(
    '/admin/'                     => 'Overview'
);

$GLOBALS['_style'] = '';


/**
 * Prints out the XHTML headers and top of the page.
 *
 * @param string $title  a string to go into the header's <title>
 * @param string $style
 * @return void
 */
function response_header($title = 'The PHP Extension and Application Repository', $style = false)
{
    global $_style, $_header_done, $SIDEBAR_DATA, $encoding, $extra_styles;

    if ($_header_done) {
        return;
    }

    $_header_done    = true;
    $_style          = $style;
    $rts             = rtrim($SIDEBAR_DATA);

    if (substr($rts, -1) == '-') {
        $SIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        global $main_menu, $docu_menu, $downloads_menu, $auth_user, $proposal_menu;
        $SIDEBAR_DATA .= draw_navigation($main_menu, 'Main:');
        $SIDEBAR_DATA .= draw_navigation($docu_menu, 'Documentation:');
        $SIDEBAR_DATA .= draw_navigation($downloads_menu, 'Downloads:');
        $SIDEBAR_DATA .= draw_navigation($proposal_menu, 'Package Proposals:');
        init_auth_user();
        if (!empty($auth_user)) {
            if (!empty($auth_user->registered)) {
                if (auth_check('pear.dev')) {
                    global $developer_menu;
                    $SIDEBAR_DATA .= draw_navigation($developer_menu, 'Developers:');
                }
            }
            if ($auth_user->isAdmin()) {
                global $admin_menu;
                $SIDEBAR_DATA .= draw_navigation($admin_menu, 'Administrators:');
            }
        } else {
            global $developer_menu;
            $tmp = array_slice($developer_menu, 0, 1);
            $SIDEBAR_DATA .= draw_navigation($tmp, 'Developers:');
        }
    }

echo '<?xml version="1.0" encoding="' . $encoding . '" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PEAR :: <?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" href="/css/style.css" />
<?php
    foreach ($extra_styles as $style_file) {
        echo ' <link rel="stylesheet" href="' . $style_file . "\" />\n";
    }
?>
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

<!-- START HEADER -->

<table class="head" cellspacing="0" cellpadding="0">
 <tr>
  <td class="head-logo">
   <?php print_link('/', make_image('pearsmall.gif', 'PEAR', false, false, false, false, 'margin: 5px;') ); ?><br />
  </td>
  <td class="head-menu">
   <?php

    if (empty($_COOKIE['PEAR_USER'])) {
        print_link('/account-request.php', 'Register', false,
                   'class="menuBlack"');
        echo delim();
        if ($_SERVER['QUERY_STRING'] && $_SERVER['QUERY_STRING'] != 'logout=1') {
            print_link('/login.php?redirect=' . urlencode(
                       "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}"),
                       'Login', false, 'class="menuBlack"');
        } else {
            print_link('/login.php?redirect=' . $_SERVER['PHP_SELF'],
                       'Login', false, 'class="menuBlack"');
        }
    } else {
        print '<small class="menuWhite">';
        print 'Logged in as ' . strtoupper($_COOKIE['PEAR_USER']) . ' (';
        print '<a class="menuWhite" href="/user/' . $_COOKIE['PEAR_USER'] . '">Info</a> | ';
        print '<a class="menuWhite" href="/account-edit.php?handle=' . $_COOKIE['PEAR_USER'] . '">Profile</a> | ';
        print '<a class="menuWhite" href="/bugs/search.php?handle=' . $_COOKIE['PEAR_USER'] . '&amp;cmd=display">Bugs</a>';
        print ")</small><br />\n";

        if (empty($_SERVER['QUERY_STRING'])) {
            print_link('?logout=1', 'Logout', false, 'class="menuBlack"');
        } else {
            print_link('?logout=1&amp;'
                            . htmlspecialchars($_SERVER['QUERY_STRING']),
                       'Logout', false, 'class="menuBlack"');
        }
    }

    echo delim();
    print_link('/manual/', 'Documentation', false, 'class="menuBlack"');
    echo delim();
    print_link('/packages.php', 'Packages', false, 'class="menuBlack"');
    echo delim();
    print_link('/support/','Support',false, 'class="menuBlack"');
    echo delim();
    print_link('/bugs/','Bugs',false, 'class="menuBlack"');
    ?>

  </td>
 </tr>

 <tr>
  <td class="head-search" colspan="2">
   <form method="post" action="/search.php">
    <p class="head-search"><span class="accesskey">S</span>earch for
    <input class="small" type="text" name="search_string" value="" size="20" accesskey="s" />
    in the
    <select name="search_in" class="small">
        <option value="packages">Packages</option>
        <option value="site">This site (using Google)</option>
        <option value="developers">Developers</option>
        <option value="pear-dev">Developer mailing list</option>
        <option value="pear-general">General mailing list</option>
        <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" style="vertical-align: middle;" />
    </p>
   </form>
  </td>
 </tr>
</table>

<!-- END HEADER -->
<!-- START MIDDLE -->

<table class="middle" cellspacing="0" cellpadding="0">
 <tr>

    <?php

    if (isset($SIDEBAR_DATA)) {
        ?>

<!-- START LEFT SIDEBAR -->
  <td class="sidebar_left">
   <?php echo $SIDEBAR_DATA ?>
  </td>
<!-- END LEFT SIDEBAR -->

        <?php
    }

    ?>

<!-- START MAIN CONTENT -->

  <td class="content">

    <?php
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

<!-- END MAIN CONTENT -->

    <?php

    if (isset($RSIDEBAR_DATA)) {
        ?>

<!-- START RIGHT SIDEBAR -->
  <td class="sidebar_right">
   <?php echo $RSIDEBAR_DATA; ?>
  </td>
<!-- END RIGHT SIDEBAR -->

        <?php
    }

    ?>

 </tr>
</table>

<!-- END MIDDLE -->
<!-- START FOOTER -->

<table class="foot" cellspacing="0" cellpadding="0">
 <tr>
  <td class="foot-bar" colspan="2">
<?php
print_link('/about/privacy.php', 'PRIVACY POLICY', false, 'class="menuBlack"');
echo delim();
print_link('/credits.php', 'CREDITS', false, 'class="menuBlack"');
?>
  </td>
 </tr>

 <tr>
  <td class="foot-copy">
   <small>
    <?php print_link('/copyright.php',
                     'Copyright &copy; 2001-2004 The PHP Group'); ?><br />
    All rights reserved.
   </small>
  </td>
  <td class="foot-source">
   <small>
    Last updated: <?php echo $LAST_UPDATED; ?><br />
    Bandwidth and hardware provided by:
    <?php
     if ($_SERVER['SERVER_NAME'] == 'pear.php.net') {
         print_link('http://www.pair.com/', 'pair Networks');
     } else {
         print '<i>This is an unofficial mirror!</i>';
     }
    ?>
 
   </small>
  </td>
 </tr>
</table>

<!-- END FOOTER -->

</body>
</html>

    <?php
}


function &draw_navigation($data, $menu_title='')
{
    $html = "\n";
    if (!empty($menu_title)) {
        $html .= "<strong>$menu_title</strong>\n";
    }

    $html .= '<ul class="side_pages">' . "\n";
    foreach ($data as $url => $tit) {
        $html .= ' <li class="side_page">';
        if ($url == $_SERVER['PHP_SELF']) {
            $html .= '<strong>' . $tit . '</strong>';
        } else {
            $html .= '<a href="' . $url . '">' . $tit . '</a>';
        }
        $html .= "</li>\n";
    }
    $html .= "</ul>\n\n";

    return $html;
}

function menu_link($text, $url) {
    echo "<p>\n";
    print_link($url, make_image('pear_item.gif', $text) );
    echo '&nbsp;';
    print_link($url, '<strong>' . $text . '</strong>' );
    echo "</p>\n";
}

/**
 * Display errors or warnings as a <ul> inside a <div>
 *
 * Here's what happens depending on $in:
 *   + string: value is printed
 *   + array:  looped through and each value is printed.
 *             If array is empty, nothing is displayed.
 *             If a value contains a PEAR_Error object,
 *   + PEAR_Error: prints the value of getMessage() and getUserInfo()
 *                 if DEVBOX is true, otherwise prints data from getMessage().
 *
 * @param string|array|PEAR_Error $in  see long description
 * @param string $class  name of the HTML class for the <div> tag.
 *                        ("errors", "warnings")
 * @param string $head   string to be put above the message
 *
 * @return bool  true if errors were submitted, false if not
 */
function report_error($in, $class = 'errors', $head = 'ERROR:')
{
    if (PEAR::isError($in)) {
        if (DEVBOX == true) {
            $in = array($in->getMessage() . '... ' . $in->getUserInfo());
        } else {
            $in = array($in->getMessage());
        }
    } elseif (!is_array($in)) {
        $in = array($in);
    } elseif (!count($in)) {
        return false;
    }

    echo '<div class="' . $class . '">' . $head . '<ul>';
    foreach ($in as $msg) {
        if (PEAR::isError($msg)) {
            if (DEVBOX == true) {
                $msg = $msg->getMessage() . '... ' . $msg->getUserInfo();
            } else {
                $msg = $msg->getMessage();
            }
        }
        echo '<li>' . htmlspecialchars($msg) . "</li>\n";
    }
    echo "</ul></div>\n";
    return true;
}

/**
 * Forwards warnings to report_error()
 *
 * For use with PEAR_ERROR_CALLBACK to get messages to be formatted
 * as warnings rather than errors.
 *
 * @param string|array|PEAR_Error $in  see report_error() for more info
 *
 * @return bool  true if errors were submitted, false if not
 *
 * @see report_error()
 */
function report_warning($in)
{
    return report_error($in, 'warnings', 'WARNING:');
}

/**
 * Generates a complete PEAR web page with an error message in it then
 * calls exit
 *
 * For use with PEAR_ERROR_CALLBACK error handling mode to print fatal
 * errors and die.
 *
 * @param string|array|PEAR_Error $in  see report_error() for more info
 * @param string $title  string to be put above the message
 *
 * @return void
 *
 * @see report_error()
 */
function error_handler($errobj, $title = 'Error')
{
    response_header($title);
    report_error($errobj);
    response_footer();
    exit;
}

/**
 * Displays success messages inside a <div>
 *
 * @param string $in  the message to be displayed
 *
 * @return void
 */
function report_success($in)
{
    echo '<div class="success">';
    echo htmlspecialchars($in);
    echo "</div>\n";
}


class BorderBox {
    function BorderBox($title, $width = '90%', $indent = '', $cols = 1,
                       $open = false)
    {
        $this->title  = $title;
        $this->width  = $width;
        $this->indent = $indent;
        $this->cols   = $cols;
        $this->open   = $open;
        $this->start();
    }

    function start()
    {
        $title = $this->title;
        if (is_array($title)) {
            $title = implode('</th><th>', $title);
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

    function end()
    {
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

    function horizHeadRow($heading /* ... */)
    {
        $i = $this->indent;
        print "$i    <tr>\n";
        print "$i     <th style=\"vertical-align: top; background-color: #CCCCCC;\">$heading</th>\n";
        for ($j = 0; $j < $this->cols-1; $j++) {
            print "$i     <td style=\"vertical-align: top; background-color: #E8E8E8\">";
            $data = @func_get_arg($j + 1);
            if (!isset($data)) {
                print "&nbsp;";
            } else {
                print $data;
            }
            print "</td>\n";
        }
        print "$i    </tr>\n";

    }

    function headRow()
    {
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

    function plainRow(/* ... */)
    {
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

    function fullRow($text)
    {
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

/**
 * Returns a hyperlink to something
 */
function make_link($url, $linktext = '', $target = '', $extras = '')
{
    return sprintf('<a href="%s"%s%s>%s</a>',
        $url,
        ($target ? ' target="'.$target.'"' : ''),
        ($extras ? ' '.$extras : ''),
        ($linktext != '' ? $linktext : $url)
    );
}

/**
 * Echos a hyperlink to something
 */
function print_link($url, $linktext = '', $target = '', $extras = '')
{
    echo make_link($url, $linktext, $target, $extras);
}

/**
 * Creates a link to the bug system
 */
function make_bug_link($package, $type = 'list', $linktext = '')
{
    switch ($type) {
        case 'list':
            if (!$linktext) {
                $linktext = 'Package Bugs';
            }
            return make_link('/bugs/search.php?cmd=display&amp;status=Open&amp;package_name[]=' . urlencode($package), $linktext);
        case 'report':
            if (!$linktext) {
                $linktext = 'Report a new bug';
            }
            return make_link('/bugs/report.php?package=' . urlencode($package), $linktext);
    }
}

/**
 * Turns the provided email address into a "mailto:" hyperlink.
 *
 * The link and link text are obfuscated by alternating Ord and Hex
 * entities.
 *
 * @param string $email     the email address to make the link for
 * @param string $linktext  a string for the visible part of the link.
 *                           If not provided, the email address is used.
 * @param string $extras    a string of extra attributes for the <a> element
 *
 * @return string  the HTML hyperlink of an email address
 */
function make_mailto_link($email, $linktext = '', $extras = '')
{
    $tmp = '';
    for ($i = 0, $l = strlen($email); $i<$l; $i++) {
        if ($i % 2) {
            $tmp .= '&#' . ord($email[$i]) . ';';
        } else {
            $tmp .= '&#x' . dechex(ord($email[$i])) . ';';
        }
    }

    return '<a ' . $extras . ' href="&#x6d;&#97;&#x69;&#108;&#x74;&#111;&#x3a;'
           . $tmp . '">' . ($linktext != '' ? $linktext : $tmp) . '</a>';
}

/**
 * Prints an IMG tag for a sized spacer GIF
 */
function spacer($width = 1, $height = 1, $align = '', $extras = '')
{
    printf('<img src="/gifs/spacer.gif" width="%d" height="%d" style="border: 0px;" alt="" %s%s />',
        $width,
        $height,
        ($align ? 'align="'.$align.'" ' : ''),
        ($extras ? $extras : '')
    );
}

/**
 * Tags the output of make_image() and resize it manually
 */
function resize_image($img, $width = 1, $height = 1)
{
    $str = preg_replace('/width=\"([0-9]+?)\"/i', '', $img );
    $str = preg_replace('/height=\"([0-9]+?)\"/i', '', $str );
    $str = substr($str,0,-1) . sprintf(' height="%s" width="%s" />', $height, $width );
    return $str;
}

/**
 * Returns an IMG tag for a given file (relative to the images dir)
 */
function make_image($file, $alt = '', $align = '', $extras = '', $dir = '',
                    $border = 0, $styles = '')
{
    if (!$dir) {
        $dir = '/gifs';
    }
    if ($size = @getimagesize($_SERVER['DOCUMENT_ROOT'].$dir.'/'.$file)) {
        $image = sprintf('<img src="%s/%s" style="border: %d;%s%s" %s alt="%s" %s />',
            $dir,
            $file,
            $border,
            ($styles ? ' '.$styles            : ''),
            ($align  ? ' float: '.$align.';'  : ''),
            $size[3],
            ($alt    ? $alt : ''),
            ($extras ? ' '.$extras            : '')
        );
    } else {
        $image = sprintf('<img src="%s/%s" style="border: %d;%s%s" alt="%s" %s />',
            $dir,
            $file,
            $border,
            ($styles ? ' '.$styles            : ''),
            ($align  ? ' float: '.$align.';'  : ''),
            ($alt    ? $alt : ''),
            ($extras ? ' '.$extras            : '')
        );
    }
    return $image;
}

/**
 * Prints an IMG tag for a given file
 */
function print_image($file, $alt = '', $align = '', $extras = '', $dir = '',
                     $border = 0)
{
    print make_image($file, $alt, $align, $extras, $dir);
}

/**
 * Print a pipe delimiter
 */
function delim($color = false, $delimiter = '&nbsp;|&nbsp;')
{
    if (!$color) {
        return $delimiter;
    }
    return sprintf('<span style="color: %s;">%s</span>', $color, $delimiter);
}

/**
 * Prints a horizontal delimiter
 */
function hdelim()
{
    return '<hr />';
}

/**
 * Prints a tabbed navigation bar based on the parameter $items
 */
function print_tabbed_navigation($items)
{
   $page = basename($_SERVER['PHP_SELF']);

    echo '<div id="nav">' . "\n";
    foreach ($items as $title => $item) {
        echo '<a href="' . $item['url']
             . '" title="' . $item['title'] . '"';
        if ($page == $item['url']) {
            echo ' class="active"';
        }
        echo '>' . $title . "</a>\n";
    }
    echo '</div>';    
}

/**
 * Prints a tabbed navigation bar for the various package pages.
 *
 * @param int    $pacid   the id number of the package being viewed
 * @param string $name    the name of the package being viewed
 * @param string $action  the indicator of the current page view
 *
 * @return void
 */
function print_package_navigation($pacid, $name, $action)
{
    global $auth_user;

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
                                               'title' => 'Edit this package');
        $nav_items['Edit Maintainers'] = array('url'   => '/admin/package-maintainers.php?pid='.$pacid,
                                               'title' => 'Edit the maintainers of this package');
        $nav_items['Delete']           = array('url'   => '/package-delete.php?id='.$pacid,
                                               'title' => 'Delete this package');
    }

    print '<div id="nav">';

    foreach ($nav_items as $title => $item) {
        if (!empty($item['url']) && $item['url']{0} == '/') {
            $url = $item['url'];
        } else {
            $url = '/package/' . htmlspecialchars($name) . '/' . $item['url'];
        }
        print '<a href="' . $url . '"'
            . ' title="' . $item['title'] . '" '
            . ($action == $item['url'] ? ' class="active" ' : '')
            . '>'
            . $title
            . '</a>';
    }

    print '</div>';
}

/**
 * Sets <var>$_SESSION['captcha']</var> then produces a CAPTCHA image
 * and a form input element
 *
 * Only generate a new <var>$_SESSION['captcha']</var> if it doesn't exist
 * yet.  This avoids the problem of the CAPTCHA value being changed but the
 * old image remaining in the browser's cache.  This is necessary because
 * caching can not be reliably disabled.
 *
 * Use upper case letters to reduce confusion with some of these fonts.
 * Input is passed through strtoupper() before comparison.
 *
 * Don't use "I" or "O" to avoid confusion with numbers.  Don't use digits
 * because some of the fonts don't handle them.
 *
 * @return string  the CAPTCHA image and form intut
 */
function generate_captcha() {
    if (!isset($_SESSION['captcha'])) {
        $_SESSION['captcha'] = '';
        $useable = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        for ($i = 0; $i < 4; $i++) {
            $_SESSION['captcha'] .= substr($useable, mt_rand(0, 23), 1);
        }
    }
    $out  = 'Type &quot;' . $_SESSION['captcha'] . '&quot; into this box... ';
    $out .= '<input type="text" size="4" maxlength="4" name="captcha" />';
    return $out;
}

/**
 * Check if the CAPTCHA value submitted by the user in
 * <var>$_POST['captcha']</var> matches <var>$_SESSION['captcha']</var>
 *
 * If the two values don't match, this function will unset()
 * <var>$_SESSION['captcha']</var>.  Unsetting it will cause
 * generate_captcha() to come up with a new CAPTCHA value and image.
 * This prevents brute force attacks.
 *
 * Similarly, if the submission is correct <var>$_SESSION['captcha']</var>
 * is unset() in order to keep robots from making multiple requests with
 * a correctly guessed CAPTCHA value.
 *
 * @return bool  true if input matches captcha, false if not
 */
function validate_captcha() {
    if (!isset($_POST['captcha']) ||
        !isset($_SESSION['captcha']) ||
        $_SESSION['captcha'] != strtoupper($_POST['captcha']))
    {
        unset($_SESSION['captcha']);
        return false;
    } else {
        unset($_SESSION['captcha']);
        return true;
    }
}

?>
