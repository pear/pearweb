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
    if ($new !== null) {
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

//include_once 'MDB2.php';
//if (empty($dbc)) {
//    $options = array(
//        'persistent' => false,
//        'portability' => MDB2_PORTABILITY_ALL,
//    );
//    $dbc = MDB2::singleton(PEAR_DATABASE_DSN, $options);
//}

$self = htmlspecialchars($_SERVER['PHP_SELF']);

// Handling things related to the manual
$in_manual = false;

if (substr($self, 0, 7) == '/manual') {
    if (substr($self, 7, 10) != '/index.php') {
        $in_manual = true;
    }

    require_once 'pear-manual.php';

    extra_styles('/css/manual.css');
}

$_style = '';

/**
 * Prints out the XHTML headers and top of the page.
 *
 * @param string $title  a string to go into the header's <title>
 * @param string $style
 * @return void
 */
function response_header($title = 'The PHP Extension and Application Repository', $style = false, $extraHeaders = '', $head = '')
{
    global $_style, $_header_done, $self, $auth_user, $RSIDEBAR_DATA, $in_manual;

    $extra_styles = extra_styles();

    if ($_header_done) {
        return;
    }

    $_header_done = true;
    $_style       = $style;
    $rts          = rtrim($RSIDEBAR_DATA);

    if (substr($rts, -1) == '-') {
        $RSIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        $menu = draw_navigation();
    }

    if ($in_manual === false) {
        // The manual-related code takes care of sending the right headers.
        header('Content-Type: text/html; charset=ISO-8859-15');
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head<?php echo $head ?>>
<?php echo $extraHeaders; ?>
 <title><?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" type="text/css" href="/css/reset-fonts.css" />
 <link rel="stylesheet" type="text/css" href="/css/style.css" />
<?php
    foreach ($extra_styles as $style_file) {
        echo ' <link rel="stylesheet" type="text/css" href="' . $style_file . "\" />\n";
    }
?>
 <!--[if IE 7]><link rel="stylesheet" type="text/css" href="/css/IE7styles.css" /><![endif]-->
 <!--[if IE 6]><link rel="stylesheet" type="text/css" href="/css/IE6styles.css" /><![endif]-->
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://<?php echo PEAR_CHANNELNAME; ?>/feeds/latest.rss" />
 <!-- compliance patch for microsoft browsers -->
<!--[if lt IE 8]>
 <script src="/javascript/IE8.js" type="text/javascript"></script>
<![endif]-->
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
        if (@$_SERVER['QUERY_STRING'] && @$_SERVER['QUERY_STRING'] != 'logout=1') {
            $qs = @$_SERVER['QUERY_STRING'];
            echo make_link('/login.php?redirect=' . urlencode(
                       "{$self}?{$qs}"),
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


        $query_string = empty($_SERVER['QUERY_STRING']) ? '' : '&amp;' . htmlspecialchars($_SERVER['QUERY_STRING']);
        echo make_link('?logout=1' . $query_string, 'Sign Out');
        echo "</li>\n";
    }
?>
  </ul>
 </div>

 <div id="search">
  <form method="get" action="/search.php">
   <p style="margin: 0px;">
    <span class="accesskey">S</span>earch for
    <input type="text" name="q" value="" size="20" accesskey="s" />
    in the
    <select name="in">
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
   <?php echo make_link('/', make_image('pearsmall.gif', 'PEAR')); ?><br />
  </div>

<div id="menubar">
<?php echo $menu['main']; ?>
</div>

<?php echo $menu['sub']; ?>
<!-- END HEADER -->
<!-- START MIDDLE -->
<?php
    $style = '';

    if (isset($RSIDEBAR_DATA)) {
        $style = ' style="margin-right: 230px;"'
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
    global $_style;
    static $called;
    if ($called) {
        return;
    }

    $called = true;
    if (!$style) {
        $style = $_style;
    }
?>

  </div>

<!-- END MAIN CONTENT -->
<!-- END MIDDLE -->

<!-- START FOOTER -->
<div id="footer">
  <div id="foot-bar"><?php
echo make_link('/about/privacy.php', 'PRIVACY POLICY');
echo '&nbsp;|&nbsp;';
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
    echo $extraContent;
}
?>
</html>
<?php
}

function draw_navigation()
{
    global $auth_user;

    // SELF doesn't cut it here, using REQUEST URI instead
    $self = strip_tags(htmlspecialchars(@$_SERVER['REQUEST_URI'], ENT_QUOTES, 'iso-8859-1'));
    if ($self === '/') {
        $self = '/index.php';
    }

    include_once 'pear-auth.php';
    init_auth_user();

    $main_order = $main = $data = $sub = $rel = array();

    $main_order[1]      = '/index.php';
    $main['/index.php'] = 'Main';
    $sub['/index.php']  = array();
    $sub['/index.php']['/index.php']   = 'Home';
    $sub['/index.php']['/news/']       = 'News';
    $sub['/index.php']['/qa/']         = 'Quality Assurance';
    $sub['/index.php']['/group/']      = 'The PEAR Group';
    $sub['/index.php']['/mirrors.php'] = 'Mirrors';

    $main_order[2]     = '/support/';
    $main['/support/'] = 'Support';
    $sub['/support/']  = array();
    $sub['/support/']['/support/']              = 'Overview';
    $sub['/support/']['/support/lists.php']     = 'Mailing Lists';
    $sub['/support/']['/support/books.php']     = 'Books';
    $sub['/support/']['/support/tutorials.php'] = 'Tutorials';
    $sub['/support/']['/support/slides.php']    = 'Presentation Slides';
    $sub['/support/']['/support/icons.php']     = 'Icons';
    $sub['/support/']['/support/forums.php']    = 'Forums';

    $main_order[3]    = '/manual/';
    $main['/manual/'] = 'Documentation';
    $sub['/manual/']  = array();
    $sub['/manual/']['/manual/en/about-pear.php'] = 'About PEAR';
    $sub['/manual/']['/manual/']                  = 'Manual';
    $sub['/manual/']['/manual/en/faq.php']        = 'FAQ';

    $main_order[4]         = '/packages.php';
    $main['/packages.php'] = 'Packages';
    $sub['/packages.php']  = array();
    $sub['/packages.php']['/packages.php']      = 'List Packages';
    $sub['/packages.php']['/search.php']        = 'Search Packages';
    $sub['/packages.php']['/package-stats.php'] = 'Statistics';
    $sub['/packages.php']['/channels/']         = 'Channels';

    $main_order[6]         = '/accounts.php';
    $main['/accounts.php'] = 'Developers';
    $sub['/accounts.php']  = array();
    $sub['/accounts.php']['/map/']         = 'Find a Developer';
    $sub['/accounts.php']['/accounts.php'] = 'List Accounts';
    if (!empty($auth_user) && !empty($auth_user->registered) && auth_check('pear.dev')) {
        $sub['/accounts.php']['/release-upload.php'] = 'Upload Release';
        $sub['/accounts.php']['/package-new.php']    = 'New Package';
        $sub['/accounts.php']['/notes/admin/']       = 'Manage User Notes';
        $sub['/accounts.php']['/election/']          = 'View Elections';
    }

    $main_order[5]  = '/pepr/';
    $main['/pepr/'] = 'Package Proposals';
    $sub['/pepr/']  = array();
    $sub['/pepr/']['/pepr/']                       = 'Browse Proposals';
    $sub['/pepr/']['/pepr/pepr-proposal-edit.php'] = 'New Proposal';

    $main_order[7]  = '/bugs/';
    $main['/bugs/'] = 'Bugs';
    $sub['/bugs/']  = array();
    $sub['/bugs/']['/bugs/search.php']    = 'Search for bugs';
    $sub['/bugs/']['/bugs/stats.php']     = 'Package Bug Statistics';
    $sub['/bugs/']['/bugs/stats_dev.php'] = 'Developers Bug Statistics';

    if (!empty($auth_user) && $auth_user->isAdmin()) {
        $main_order[8]   = '/admin/';
        $main['/admin/'] = 'Administrators';
        $sub['/admin/']  = array();
        $sub['/admin/']['/admin/']                     = 'Overview';
        $sub['/admin/']['/admin/package-approval.php'] = 'Package approvals';
        $sub['/admin/']['/admin/category-manager.php'] = 'Manage categories';
        $sub['/admin/']['/tags/admin.php']             = 'Manage tags';
        $sub['/admin/']['/admin/karma.php']            = 'Karma';
    }

    // Orders the main items in the proper order according to $main_order
    ksort($main_order);
    foreach ($main_order as $mo) {
        if (isset($main[$mo])) {
            $data[$mo] = $main[$mo];
        }
    }

    // Relationship linker
    foreach (array_keys($sub) as $path) {
        $keys = array_keys($sub[$path]);
        $temp = array_fill_keys($keys, $path);
        $rel += $temp;
    }

    // Can't find a match, lets cut pieces of the url
    // lets first try sub dir + a php file
    if (!isset($rel[$self]) || $rel[$self] === null) {
        $pos  = strpos($self, '.php');
        $self = $pos !== false ? substr($self, 0, $pos + 4) : $self;
    }

    // Can't find a match, lets cut pieces of the url
    if ((!isset($rel[$self]) || $rel[$self] === null) && strlen($self) > 0) {
        $pos  = strpos($self, '/', 1);
        $self = $pos !== false ? substr($self, 0, $pos + 1) : $self;
    }

    /* Check if it's a top level item.
     * There are cases were we don't want to put fake second level
     * menu item, like Bugs -> Index, the top level link serves as Index
     */
    if (isset($data[$self])) {
        $rel += array($self => $self);
    }

    // avoid a notice if the array key isn't set
    if (!array_key_exists($self, $rel)) {
        $rel[$self] = null;
    }

    // Not really menu items but required so the correct
    // sub menu item gets selected
    $fake = array(
        '/developers/'        => '/accounts.php',
        '/user/'              => '/accounts.php',
        '/package/'           => '/packages.php',
        '/package-edit.php'   => '/packages.php',
        '/package-delete.php' => '/packages.php',
    );

    if (isset($fake[$self])) {
        $self = $fake[$self];
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
    foreach ($data as $url => $tit) {
        $class = '';
        if ($url == $self) {
            $class = ' class="current"';
        }

        $html .= ' <li' . $class . '>';
        $html .= '<a href="' . $url . '">' . $tit . '</a>';
        $html .= "</li>\n";
    }
    $html .= "</ul>\n\n";

    return $html;
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
        echo '<li>' . $msg . "</li>\n";
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
    echo '<div class="success">' .$in . "</div>\n";
}

/**
 * prints "urhere" menu bar
 * Top Level :: XML :: XML_RPC
 * @param bool $link_lastest If the last category should or not be a link
 */
function html_category_urhere($id, $link_lastest = false, $php = 'all')
{
    $url = '/packages.php';
    if ($php != 'all' && ($php == '4' || $php == '5')) {
        $url .= '&amp;php=' . $php;
    }
    $html = '<a href="' . $url . '">Top Level</a>';
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

            $url = '/packages.php?catpid=' . $row['id'] . '&amp;catname=' . $row['name'];
            if ($php != 'all' && ($php == '4' || $php == '5')) {
                $url .= '&amp;php=' . $php;
            }
            $html .= '  :: <a class="category" href="' . $url . '">' . $row['name'] . '</a>';
            $i++;
        }
        if (!$link_lastest) {
            $html .= '  :: <strong>' . $row['name'] . '</strong>';
        }
    }
    echo $html;
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
    if  ($keepProtocol === false) {
        $url = preg_replace("/^https/", "http", $url);
    }
    header('Location: ' . $url);
    exit;
}

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

    $query = 'SELECT name, wishlist FROM users WHERE handle = ?';
    $row = $dbh->getRow($query, DB_FETCHMODE_ASSOC, array($handle));
    if (!is_array($row)) {
        return false;
    }

    if ($row['wishlist'] != '' && $compact === false) {
        $link = make_link('http://' . PEAR_CHANNELNAME . '/wishlist.php/' . $handle, 'Wishlist');
        $wish = '[' . $link . ']';
    } else {
        $wish = '';
    }

    return sprintf('<a href="/user/%s">%s</a>&nbsp;%s', $handle, $row['name'], $wish);
}

/**
 * Returns a hyperlink to something
 */
function make_link($url, $linktext = '', $target = '', $extras = '', $title = '')
{
    $url = htmlspecialchars($url, ENT_QUOTES, 'ISO8859-15', false);
    return sprintf('<a href="%s"%s%s%s>%s</a>',
        $url,
        ($target ? ' target="'.$target.'"' : ''),
        ($extras ? ' '.$extras : ''),
        ($title ? ' title="'.$title.'"' : ''),
        ($linktext != '' ? $linktext : $url)
    );
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
 * Returns an IMG tag for a given file (relative to the images dir)
 */
function make_image($file, $alt = '', $align = '', $extras = '', $dir = '',
                    $border = 0, $styles = '')
{
    if (!$dir) {
        $dir = '/gifs';
    }

    $size = @getimagesize($_SERVER['DOCUMENT_ROOT'].$dir.'/'.$file);
    $s = $size !== false ? ' ' . $size[3] : '';

    $image = sprintf('<img src="%s/%s" style="border: %d;%s%s"%s alt="%s" %s />',
        $dir,
        $file,
        $border,
        ($styles ? ' '.$styles            : ''),
        ($align  ? ' float: '.$align.';'  : ''),
        $s,
        ($alt    ? $alt : ''),
        ($extras ? ' '.$extras            : '')
    );

    return $image;
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
        $css = $page == $item['url'] ? ' class="current"' : '';
        echo make_link($item['url'], $title, '', $css, $item['title']);
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

    $items = array(
        'Main'          => array('url'   => '',
                                 'title' => 'Main view'),
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
        $items['Edit']             = array('url'   => '/package-edit.php?id='.$pacid,
                                               'title' => 'Edit this package');
        $items['Edit Maintainers'] = array('url'   => '/admin/package-maintainers.php?pid='.$pacid,
                                               'title' => 'Edit the maintainers of this package');
    }

    if (isset($auth_user) && is_object($auth_user)
        && ($auth_user->isAdmin() || $auth_user->isQA())
    ) {
        $items['Delete']           = array('url'   => '/package-delete.php?id='.$pacid,
                                               'title' => 'Delete this package');
    }

    //echo print_tabbed_navigation($nav_items);

    echo '<div id="nav">' . "\n";
    foreach ($items as $title => $item) {
        if (!empty($item['url']) && $item['url']{0} == '/') {
            $url = $item['url'];
        } else {
            $url = '/package/' . htmlspecialchars($name) . '/' . $item['url'];
        }
        $css = $action == $item['url'] ? ' class="current" ' : '';
        echo make_link($url, $title, '', $css, $item['title']);
    }
    echo '</div>' . "\n";
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
    global $dbh;
    $text = preg_replace('/#patch bug:([0-9]+);patch:([0-9a-z_\-\.]+);revision:([0-9]+);/i',
                         '<a href="patch-display.php?bug_id=\\1&amp;patch=\\2&amp;revision=\\3">patch \\2</a>',
                         $text);
    $text = preg_replace('/(?<=php)\s*(bug(?:fix)?|feat(?:ure)?|doc(?:umentation)?|req(?:uest)?)\s+#([0-9]+)/i',
                         ' <a href="http://bugs.php.net/\\2">\\1 \\2</a>',
                         $text);
    $pear_regex = '/(?<![>a-z])(bug(?:fix)?|feat(?:ure)?|doc(?:umentation)?|req(?:uest)?)\s+#([0-9]+)/i';
    //$text = preg_replace($pear_regex, '<a href="/bugs/\\2">\\0</a>', $text);
    preg_match_all($pear_regex, $text, $matches);
    if (!empty($matches[2])) {
        $ids = implode(', ', $matches[2]);
        $sql = 'SELECT package_name, sdesc FROM bugdb WHERE id IN(' . $ids . ')';
        $res = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);
        foreach ($res as $k => $b) {
            $t     = $matches[0][$k];
            $title = $b['package_name'] . ': ' . $b['sdesc'];
            $link  = make_link($matches[2][$k], $t, null, null, $title);
            $text  = str_replace($t, $link, $text);
        }
    }

    return $text;
}

/**
 * Turns a unix timestamp into a uniformly formatted date
 * If the date is during the current year, the year is omitted.
 *
 * @param int $date  the unix timestamp to be formatted
 * @param string $format date function formatted string
 * @return string  the formatted date
 */
function format_date($ts = null, $format = 'Y-m-d H:i e')
{
    if (!$ts) {
        $ts = time();
    }
    return gmdate($format, $ts - date('Z', $ts));
}