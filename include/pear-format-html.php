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

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'error_handler');
set_exception_handler('error_handler');

function extra_styles($new = null)
{
    static $extra_styles = array();
    if (!is_null($new)) {
        $extra_styles[] = $new;
    }
    return $extra_styles;
}

include_once 'DB.php';

if (empty($dbh)) {
    $options = array(
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    );
    $dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
}

$self = htmlspecialchars($_SERVER['PHP_SELF']);

// Handling things related to the manual
$in_manual = false;

if (substr($self, 0, 7) == '/manual') {
    if (substr($self, 7, 10) != "/index.php") {
        $in_manual = true;
    }

    require_once 'pear-manual.php';

    extra_styles('/css/manual.css');
}

$GLOBALS['_style'] = '';
$_style = '';

/**
 * Prints out the XHTML headers and top of the page.
 *
 * @param string $title  a string to go into the header's <title>
 * @param string $style
 * @return void
 */
function response_header($title = 'The PHP Extension and Application Repository', $style = false, $extraHeaders = '')
{
    global $_style, $_header_done, $SIDEBAR_DATA, $self, $auth_user, $RSIDEBAR_DATA;

    $extra_styles = extra_styles();

    if ($_header_done) {
        return;
    }

    $_header_done = true;
    $_style       = $style;
    $rts          = rtrim($SIDEBAR_DATA);

    if (substr($rts, -1) == '-') {
        $SIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        $menu = draw_navigation();
    }

    if (isset($GLOBALS['in_manual']) && $GLOBALS['in_manual'] == false) {
        /* The manual-related code takes care of sending the right
         * headers.
         */
        header('Content-Type: text/html; charset=ISO-8859-15');
        echo '<?xml version="1.0" encoding="ISO-8859-15" ?>';
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php
echo $extraHeaders;
?>
 <title>PEAR :: <?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" type="text/css" href="/css/reset-fonts.css" />
 <link rel="stylesheet" type="text/css" href="/css/style.css" />
 <!--[if IE 7]><link rel="stylesheet" type="text/css" href="/css/IE7styles.css" /><![endif]-->
 <!--[if IE 6]><link rel="stylesheet" type="text/css" href="/css/IE6styles.css" /><![endif]-->
<?php
    foreach ($extra_styles as $style_file) {
        echo ' <link rel="stylesheet" href="' . $style_file . "\" />\n";
    }
?>
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/feeds/latest.rss" />
</head>

<body>
<div>
<a id="TOP"></a>
</div>

<div id="doc3">
<!-- START HEADER -->
 <div id="user">
  <ul>
<?php
    if (!$auth_user) {
        echo '   <li>' . make_link('/account-request.php', 'Register') . '</li>' . "\n";

        echo '   <li class="last">';
        if ($_SERVER['QUERY_STRING'] && $_SERVER['QUERY_STRING'] != 'logout=1') {
            echo make_link('/login.php?redirect=' . urlencode(
                       "{$self}?{$_SERVER['QUERY_STRING']}"),
                       'Login');
        } else {
            echo make_link('/login.php?redirect=' . $self, 'Login');
        }
        echo '</li>' . "\n";
    } else {
        echo '   <li>logged in as <a href="/user/' . $auth_user->handle . '">' . $auth_user->handle . '</a></li>' . "\n";
        echo '   <li><a href="/account-edit.php?handle=' . $auth_user->handle . '">Profile</a></li>' . "\n";
        echo '   <li><a href="/bugs/search.php?handle=' . $auth_user->handle . '&amp;cmd=display&amp;status=OpenFeedback&amp;showmenu=1">Bugs</a></li>' . "\n";
        echo '   <li><a href="/bugs/search.php?cmd=display' .
            '&amp;status=All&amp;bug_type=All&amp;author_email=' . $auth_user->handle .
            '&amp;direction=DESC&amp;order_by=ts1&amp;showmenu=1">My Bugs</a></li> '  . "\n" . '   <li class="last signout">';

        if (empty($_SERVER['QUERY_STRING'])) {
            echo make_link('?logout=1', 'Sign Out');
        } else {
            echo make_link('?logout=1&amp;'
                            . htmlspecialchars($_SERVER['QUERY_STRING']),
                       'Sign Out');
        }
        echo "</li>\n";
    }
?>
  </ul>
 </div>

<div id="search">
  <form method="get" action="/search.php">
   <p style="margin: 0px;">
    <span class="accesskey">S</span>earch for
    <input class="small" type="text" name="q" value="" size="20" accesskey="s" />
    in the
    <select name="in" class="small">
        <option value="packages">Packages</option>
        <option value="site">This site (using Yahoo!)</option>
        <option value="users">Developers</option>
        <option value="pear-dev">Developer mailing list</option>
        <option value="pear-general">General mailing list</option>
        <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" style="vertical-align: middle;" />
   </p>
  </form>
 </div>

  <div id="header">
   <?php echo make_link('/', make_image('pearsmall.gif', 'PEAR', false, false, false, false, 'margin: 5px;') ); ?><br />
  </div>

<div id="menubar">
<?php echo $menu['main']; ?>
</div>

<?php echo $menu['sub']; ?>
<!-- END HEADER -->
<!-- START MIDDLE -->
<?php
    $style = '';
    if (isset($SIDEBAR_DATA)) {
        $style = ' style="margin-left: 210px;"';
?>

<!-- START LEFT SIDEBAR -->
  <div class="sidebar_left">
   <span id="sidebar">
<?php echo $SIDEBAR_DATA ?>
   </span>
  </div>
<!-- END LEFT SIDEBAR -->

<?php
    }

    if (isset($RSIDEBAR_DATA)) {
        $style = ' style="margin-right: 210px;"'
?>

<!-- START RIGHT SIDEBAR -->
  <div class="sidebar_right">
   <?php echo $RSIDEBAR_DATA; ?>
  </div>
<!-- END RIGHT SIDEBAR -->

<?php
    }
?>

<!-- START MAIN CONTENT -->

  <div id="body"<?php echo $style;?>>

<?php
}


function response_footer($style = false, $extraContent = false)
{
    static $called;
    if ($called) {
        return;
    }

    $called = true;
    if (!$style) {
        $style = $GLOBALS['_style'];
    }
?>

  </div>

<!-- END MAIN CONTENT -->
<!-- END MIDDLE -->

<!-- START FOOTER -->
<div id="footer">
  <div id="foot-bar"><?php
echo make_link('/about/privacy.php', 'PRIVACY POLICY');
echo delim();
echo make_link('/about/credits.php', 'CREDITS');
?></div>
  <div id="foot-copy">
    <?php echo make_link('/copyright.php',
                     'Copyright &copy; 2001-' . date('Y') . ' The PHP Group'); ?>
    All rights reserved.
  </div>
  <div id="foot-source">
    Bandwidth and hardware provided by:
    <?php
     if ($_SERVER['SERVER_NAME'] == 'pear.php.net') {
        echo make_link('http://www.pair.com/', 'pair Networks');
     } elseif ($_SERVER['SERVER_NAME'] == PEAR_CHANNELNAME) {
        echo PEAR_CHANNELNAME;
     } else {
         echo '<i>This is an unofficial mirror!</i>';
     }
     echo "\n";
    ?>
  </div>
</div>
<!-- Onload focus to pear -->
<?php if (isset($GLOBALS['ONLOAD'])): ?>
<script language="javascript">
function makeFocus() {
    <?php echo htmlspecialchars($GLOBALS['ONLOAD']); ?>
}

function addEvent(obj, eventType, functionCall){
    if (obj.addEventListener){
        obj.addEventListener(eventType, functionCall, false);
        return true;
    } else if (obj.attachEvent){
        var r = obj.attachEvent("on"+eventType, functionCall);
        return r;
    } else {
        return false;
    }
}
addEvent(window, 'load', makeFocus);
</script>
<?php endif; ?>
<!-- END FOOTER -->
 </div>
</body>
<?php
if ($extraContent) {
    print $extraContent;
}
?>
</html>
<?php
}

function draw_navigation()
{
    global $auth_user;

    // SELF doesn't cut it here, using REQUEST URI instead
    $self = strip_tags(htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'iso-8859-1'));
    if ($self === '/') {
        $self = '/index.php';
    }

    include_once 'pear-auth.php';
    init_auth_user();

    $data = array(
        '/index.php'        => 'Main',
        '/support/'         => 'Support',
        '/manual/'          => 'Documentation',
        '/packages.php'     => 'Packages',
        '/pepr/'            => 'Package Proposals',
        '/accounts.php'     => 'Developers',
        '/bugs/'            => 'Bugs',
    );

    if (!empty($auth_user) && $auth_user->isAdmin()) {
        $data['/admin/'] = 'Administrators';
    }

    $sub = $rel = array();
    $sub['/index.php'] = array(
        '/index.php'           => 'Home',
        '/news/'               => 'News',
        '/qa/'                 => 'Quality Assurance',
        '/group/'              => 'The PEAR Group',
        '/mirrors.php'         => 'Mirrors',
    );

    $sub['/support/'] = array(
        '/support/'              => 'Overview',
        '/support/lists.php'     =>  'Mailing Lists',
        '/support/books.php'     => 'Books',
        '/support/tutorials.php' => 'Tutorials',
        '/support/slides.php'    => 'Presentation Slides',
        '/support/icons.php'     => 'Icons',
        '/support/forums.php'    => 'Forums',
    );

    $sub['/manual/'] = array(
        '/manual/en/about-pear.php' => 'About PEAR',
        '/manual/'                  => 'Manual',
        '/manual/en/faq.php'        => 'FAQ',
    );

    $sub['/packages.php'] = array(
        '/packages.php'        => 'List Packages',
        '/search.php'          => 'Search Packages',
        '/package-stats.php'   => 'Statistics'
    );

    if (!empty($auth_user) && !empty($auth_user->registered) && auth_check('pear.dev')) {
        $sub['/accounts.php'] = array(
            '/map/'                => 'Find a Developer',
            '/accounts.php'        => 'List Accounts',
            '/release-upload.php'  => 'Upload Release',
            '/package-new.php'     => 'New Package',
            '/notes/admin'         => 'Manage User Notes',
            '/election/'           => 'View Elections',
        );
    } else {
        $sub['/accounts.php'] = array(
            '/map/'                => 'Find a Developer',
            '/accounts.php'        => 'List Accounts'
        );
    }

    $sub['/pepr/'] = array(
        '/pepr/'                        => 'Browse Proposals',
        '/pepr/pepr-proposal-edit.php'  => 'New Proposal'
    );

    $sub['/bugs/'] = array(
        '/bugs/search.php'    => 'Search for bugs',
        '/bugs/stats.php'     => 'Package Bug Statistics',
        '/bugs/stats_dev.php' => 'Developers Bug Statistics',
    );

    $sub['/admin/'] = array(
        '/admin/' => 'Overview',
        '/admin/package-approval.php'    => 'Package approvals',
        '/admin/category-manager.php'    => 'Manage categories',
        '/tags/admin.php'                => 'Manage tags',
        '/admin/karma.php'               => 'Karma',
        '/admin/chm-upload.php'          => 'CHM upload',
    );

    // Relationship linker
    foreach (array_keys($sub) as $path) {
        $keys = array_keys($sub[$path]);
        $temp = array_fill_keys($keys, $path);
        $rel += $temp;
    }

    // Can't find a match, lets cut pieces of the url
    if (!isset($rel[$self]) OR $rel[$self] === null) {
        $pos  = strpos($self, '/', 1);
        $self = substr($self, 0, $pos + 1);
    }

    /* Check if it's a top level item.
     * There are cases were we don't want to put fake second level
     * menu item, like Bugs -> Index, the top level link serves as Index
     */
    if (isset($data[$self])) {
        $rel += array($self => $self);
    }

    // Still no luck, lets fallback on index.php
    if ($rel[$self] === null) {
        $self = '/index.php';
    }

    $menu = array();
    $menu['main'] = make_menu($data, 'menu', $rel[$self]);
    $menu['sub']  = make_menu($sub[$rel[$self]], 'submenu', $self);

    return $menu;
}

function make_menu($data, $id, $self)
{
    $html = "\n";
    $html .= '<ul id="' . $id . '">' . "\n";
    $first = true;
    foreach ($data as $url => $tit) {
        $class = array();
        if ($first) {
            $first = false;
            $class[] = 'first';
        }
        if ($url == $self) {
            $class[] = 'current';
        }

        if (!empty($class)) {
            $class = ' class="' . implode(' ', $class) . '"';
        } else {
            $class = '';
        }

        $html .= ' <li' . $class . '>';
        $html .= '<a href="' . $url . '">' . $tit . '</a>';
        $html .= "</li>\n";
    }
    $html .= "</ul>\n\n";

    return $html;
}

function menu_link($text, $url) {
    echo "<span>\n";
    echo make_link($url, make_image('pear_item.gif', $text) );
    echo '&nbsp;';
    echo make_link($url, '<strong>' . $text . '</strong>' );
    echo "</span>\n";
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
 * @param string|array|PEAR_Error|Exception $in  see long description
 * @param string $class  name of the HTML class for the <div> tag.
 *                        ("errors", "warnings")
 * @param string $head   string to be put above the message
 *
 * @return bool  true if errors were submitted, false if not
 */
function report_error($in, $class = 'errors', $head = 'ERROR:')
{
    if (PEAR::isError($in) || $in instanceof Exception) {
        if (DEVBOX == true) {
            if ($in instanceof Exception) {
                $in = array($in->__toString());
            } else {
                $in = array($in->getMessage() . '... ' . $in->getUserInfo());
            }
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
        if (PEAR::isError($msg) || $msg instanceof Exception) {
            if (DEVBOX == true) {
                if ($msg instanceof Exception) {
                    $msg = array($msg->__toString());
                } else {
                    $msg = array($msg->getMessage() . '... ' . $msg->getUserInfo());
                }
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
        echo "<!-- border box starts -->\n";
        echo "$i<table cellpadding=\"0\" cellspacing=\"1\" style=\"width: $this->width; border: 0px;\">\n";
        echo "$i <tr>\n";
        echo "$i  <td style=\"background-color: #000000;\">\n";
        echo "$i   <table cellpadding=\"2\" cellspacing=\"1\" style=\"width: 100%; border: 0px;\">\n";
        echo "$i    <tr style=\"background-color: #CCCCCC;\">\n";
        echo "$i     <th";
        if ($this->cols > 1) {
            echo " colspan=\"$this->cols\"";
        }
        echo ">$title</th>\n";
        echo "$i    </tr>\n";
        if (!$this->open) {
            echo "$i    <tr style=\"background-color: #FFFFFF;\">\n";
            echo "$i     <td>\n";
        }
    }

    function end()
    {
        $i = $this->indent;
        if (!$this->open) {
            echo "$i     </td>\n";
            echo "$i    </tr>\n";
        }
        echo "$i   </table>\n";
        echo "$i  </td>\n";
        echo "$i </tr>\n";
        echo "$i</table>\n";
        echo "<!-- border box ends -->\n";
    }

    function horizHeadRow($heading /* ... */)
    {
        $i = $this->indent;
        echo "$i    <tr>\n";
        echo "$i     <th style=\"vertical-align: top; background-color: #CCCCCC;\">$heading</th>\n";
        for ($j = 0; $j < $this->cols-1; $j++) {
            echo "$i     <td style=\"vertical-align: top; background-color: #E8E8E8\">";
            $data = @func_get_arg($j + 1);
            if (!isset($data)) {
                echo "&nbsp;";
            } else {
                print $data;
            }
            echo "</td>\n";
        }
        echo "$i    </tr>\n";

    }

    function headRow()
    {
        $i = $this->indent;
        echo "$i    <tr>\n";
        for ($j = 0; $j < $this->cols; $j++) {
            echo "$i     <th style=\"vertical-align: top; background-color: #FFFFFF;\">";
            $data = @func_get_arg($j);
            if (empty($data)) {
                echo '&nbsp;';
            } else {
                print $data;
            }
            echo "</th>\n";
        }
        echo "$i    </tr>\n";
    }

    function plainRow(/* ... */)
    {
        $i = $this->indent;
        echo "$i    <tr>\n";
        for ($j = 0; $j < $this->cols; $j++) {
            echo "$i     <td style=\"vertical-align: top; background-color: #FFFFFF;\">";
            $data = @func_get_arg($j);
            if (empty($data)) {
                echo '&nbsp;';
            } else {
                print $data;
            }
            echo "</td>\n";
        }
        echo "$i    </tr>\n";
    }

    function fullRow($text)
    {
        $i = $this->indent;
        echo "$i    <tr>\n";
        echo "$i     <td style=\"background-color: #E8E8E8;\"";
        if ($this->cols > 1) {
            echo " colspan=\"$this->cols\"";
        }
        echo ">$text</td>\n";
        echo "$i    </tr>\n";

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
    include_once 'Net/URL2.php';
    $obj = new Net_URL2($url);
    return $obj->getURL();
}

/**
 * Redirects to the given full or partial URL.
 * will turn the given url into an absolute url
 * using the above getURL() function. This function
 * does not return.
 *
 * @param string $url Full/partial url to redirect to
 * @param  bool  $keepProtocol Whether to keep the current protocol or to force HTTP
 */
function localRedirect($url, $keepProtocol = true)
{
    $url = getURL($url, $keepProtocol);
    if  ($keepProtocol == false) {
        $url = preg_replace("/^https/", "http", $url);
    }
    header('Location: ' . $url);
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

        case 'PHP License 3.01' :
        case 'PHP License' :
        case 'PHP 3.01' :
            $link = 'http://www.php.net/license/3_01.txt';
            break;

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

        case 'BSD' :
        case 'BSD License' :
        case 'New BSD License' :
        case 'New BSD' :
            $link = 'http://www.opensource.org/licenses/bsd-license.php';
            break;

        case 'MIT' :
        case 'MIT License' :
            $link = 'http://www.opensource.org/licenses/mit-license.php';
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
        echo '<table cellpadding="2" cellspacing="0" style="border: 0px;">' . "\n";
        foreach ($notes as $nid => $data) {
        echo " <tr>\n";
        echo "  <td>\n";
        echo "   <strong>{$data['nby']} {$data['ntime']}:</strong>";
        echo "<br />\n";
        echo "   ".htmlspecialchars($data['note'])."\n";
        echo "  </td>\n";
        echo " </tr>\n";
        echo " <tr><td>&nbsp;</td></tr>\n";
        }
        echo "</table>\n";
    } else {
        echo 'No notes.';
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

    return sprintf("<a href=\"/user/%s\">%s</a>&nbsp;%s\n",
                   $handle,
                   $row['name'],
                   ($row['wishlist'] != "" && $compact == false ? '['.make_link('http://' . htmlspecialchars($_SERVER['HTTP_HOST']) . '/wishlist.php/' . $handle, 'Wishlist').']' : '')
                   );
}

// }}}

/**
 * Returns a hyperlink to something
 */
function make_link($url, $linktext = '', $target = '', $extras = '', $title = '')
{
    return sprintf('<a href="%s"%s%s%s>%s</a>',
        $url,
        ($target ? ' target="'.$target.'"' : ''),
        ($extras ? ' '.$extras : ''),
        ($title ? ' title="'.$title.'"' : ''),
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
    global $self;

    $page = basename($self);

    echo '<div id="nav">' . "\n";
    echo "  <ul>\n";
    foreach ($items as $title => $item) {
        echo "    <li>";
        echo '<a href="' . $item['url']
             . '" title="' . $item['title'] . '"';
        if ($page == $item['url']) {
            echo ' class="active"';
        }
        echo '>' . $title . "</a>";
        echo "</li>\n";
    }
    echo "  </ul>\n";
    echo "</div>\n";
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
                                                'title' => 'View/Report Bugs'),

                       'Trackbacks'    => array('url'   => 'trackbacks',
                                                'title' => 'Show Related Sites'),
/*
        'Wiki'          => array('url'   => 'wiki',
                                'title' => 'View wiki area')*/
                       );

    if (isset($auth_user) && is_object($auth_user) &&
        (user::maintains($auth_user->handle, $pacid, 'lead') ||
         user::isAdmin($auth_user->handle) ||
         user::isQA($auth_user->handle))
       ) {
        $nav_items['Edit']             = array('url'   => '/package-edit.php?id='.$pacid,
                                               'title' => 'Edit this package');
        $nav_items['Edit Maintainers'] = array('url'   => '/admin/package-maintainers.php?pid='.$pacid,
                                               'title' => 'Edit the maintainers of this package');
    }
    if (isset($auth_user) && is_object($auth_user) &&
        ($auth_user->isAdmin() || $auth_user->isQA())
       ) {
        $nav_items['Delete']           = array('url'   => '/package-delete.php?id='.$pacid,
                                               'title' => 'Delete this package');
    }

    echo '<div id="nav">';

    foreach ($nav_items as $title => $item) {
        if (!empty($item['url']) && $item['url']{0} == '/') {
            $url = $item['url'];
        } else {
            $url = '/package/' . htmlspecialchars($name) . '/' . $item['url'];
        }
        echo '<a href="' . $url . '"'
            . ' title="' . $item['title'] . '" '
            . ($action == $item['url'] ? ' class="active" ' : '')
            . '>'
            . $title
            . '</a> ';
    }

    echo '</div>';
}

/**
 * Turns bug/feature request numbers into hyperlinks
 *
 * If the bug number is prefixed by the word "PHP," the link will
 * go to bugs.php.net.  Otherwise, the bug is considered a PEAR bug.
 *
 * @param string $text  the text to check for bug numbers
 *
 * @return string  the string with bug numbers hyperlinked
 */
function make_ticket_links($text)
{
    $text = preg_replace('/(?<=php)\s*(bug(?:fix)?|feat(?:ure)?|doc(?:umentation)?|req(?:uest)?)\s+#([0-9]+)/i',
                         ' <a href="http://bugs.php.net/\\2">\\1 \\2</a>',
                         $text);
    $text = preg_replace('/(?<![>a-z])(bug(?:fix)?|feat(?:ure)?|doc(?:umentation)?|req(?:uest)?)\s+#([0-9]+)/i',
                         '<a href="/bugs/\\2">\\0</a>', $text);
    return $text;
}