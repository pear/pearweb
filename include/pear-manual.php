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

require_once 'site.php';

$doc_languages = array('en' => 'English',
                       'fr' => 'French',
                       'de' => 'German',
                       'ja' => 'Japanese',
                       'nl' => 'Dutch',
                       'hu' => 'Hungarian',
                       // 'it' => 'Italian',
                       'pl' => 'Polish',
                       'ru' => 'Russian',
                       // 'es' => 'Spanish',
                       );

$NEXT = $PREV = $UP = $HOME = array(false, false);
$TOC = array();

$RSIDEBAR_DATA = '';

function setupNavigation($data)
{
    global $NEXT, $PREV, $UP, $HOME, $TOC, $tstamp;
    $HOME = @$data['home'];
    $HOME[0] = './';
    $NEXT = @$data['next'];
    $PREV = @$data['prev'];
    $UP   = @$data['up'];
    $TOC =  @$data['toc'];
    $tstamp = gmdate('D, d M Y',getlastmod());
}

function makeBorderTOC($this, $id = '')
{
    global $NEXT, $PREV, $UP, $HOME, $TOC, $DOCUMENT_ROOT;
    global $RSIDEBAR_DATA, $LANG,$CHARSET;

    $RSIDEBAR_DATA  = "\n\n<!-- START MANUAL'S SIDEBAR TOC -->\n\n";
    $RSIDEBAR_DATA .= '<form method="get" action="/manual-lookup.php">' . "\n";
    $RSIDEBAR_DATA .= '<table border="0" cellpadding="4" cellspacing="0">' . "\n";

    /** The manual lookup will be implemented at a later point.
    $RSIDEBAR_DATA .= '<tr valign="top"><td><small>' .
        '<input type="hidden" name="lang" value="' . $LANG . '">' .
        'lookup: <input type="text" class="small" name="function" size="10"> ' .
        make_submit('small_submit_white.gif', 'lookup', 'bottom') .
        '<br /></small></td></tr>';

    $RSIDEBAR_DATA .= '<tr bgcolor="#cccccc"><td></td></tr>';
    */

    $RSIDEBAR_DATA .= '<tr valign="top"><td>' . "\n";

    $RSIDEBAR_DATA .= ' <ul class="man-side_top">' . "\n"
                   . '  <li class="man-side_top">'
                   . make_link('./', $HOME[1]) . "</li>\n"
                   . ' </ul>' . "\n\n";

    $RSIDEBAR_DATA .= ' <hr class="greyline" width="100%" />' . "\n\n";

    if (($HOME[1] != $UP[1]) && $UP[1]) {
        $RSIDEBAR_DATA .= ' <ul class="man-side_up">' . "\n"
                       . '  <li class="man-side_up">'
                       . make_link($UP[0], $UP[1]) . "</li>\n"
                       . ' </ul>' . "\n\n";
    }

    $RSIDEBAR_DATA .= ' <ul class="man-side_pages">' . "\n";

    $package_name = getPackageNameForId($id);
    $indent = false;

    $toc_count = count($TOC);
    for ($i = 0; $i < $toc_count; $i++) {
        list($url, $title) = $TOC[$i];
        if (!$url || !$title) {
            continue;
        }

        $title_fmt = trim(@htmlspecialchars($title, ENT_QUOTES, $CHARSET));
        if (!is_null($package_name)) {
            $title_fmt = preg_replace('/^\s*' . $package_name . '[_\w]*::/', '', $title_fmt);
        }
        if (strlen($title_fmt) > 25) {
            $title_fmt = str_replace('::', '::<br />', $title_fmt);
        }

        // if we're in indentation mode for methods, we have to stop the
        // indentation when we find 'Class Summary'
        if ($indent && substr($title_fmt, 0, 13) == 'Class Summary') {
            $RSIDEBAR_DATA .= "</li>\n";
            $RSIDEBAR_DATA .= '</ul>';
            $indent = false;
        }

        // So that package/function names don't bleed over the sidebar
        $cut = $indent ? 22 : 25;
        $title_fmt = wordwrap($title_fmt, $cut, "\n", true);
        $class = ($indent) ? 'man-side_page_nested' : 'man-side_page';
        $RSIDEBAR_DATA .= '  <li class="' . $class . '">'
                . (($title == $this) ? "<strong>$title_fmt</strong>"
                                     : make_link($url, $title_fmt));

        // after 'Class Summary' (or 'constructor', if 'Class Summary' doesn't
        // exist, we want to indent the methods
        if (    substr($title_fmt, 0, 13) == 'Class Summary'
            || (substr($title_fmt, 0, 11) == 'constructor' && !$indent)
           ) {
            $indent = true;
            $RSIDEBAR_DATA .= '<ul class="man-side_pages">';
        }
    }

    if ($indent) {
        $RSIDEBAR_DATA .= "  </li>\n";
        $RSIDEBAR_DATA .= '</ul>';
    }

    $RSIDEBAR_DATA .= " </ul>\n\n";

    if (count($TOC) > 1) {
        $RSIDEBAR_DATA .= ' <hr class="greyline" width="100%" />' . "\n\n";
    }

    // if we have a package name, add links to the package and the API docs
    if (!is_null($package_name)) {
        $RSIDEBAR_DATA .= ' <ul class="man-side_download">' . "\n";
        $RSIDEBAR_DATA .= '  <li class="man-side_download">'
                       . make_link('/package/' . $package_name,
                                   'Package Info') . "</li>\n";
        $RSIDEBAR_DATA .= '  <li class="man-side_download">'
                       . make_link('/package/' . $package_name . '/docs/latest/',
                                   'API Documentation') . "</li>\n";
        $RSIDEBAR_DATA .= ' </ul>' . "\n";
        $RSIDEBAR_DATA .= ' <hr class="greyline" width="100%" />' . "\n\n";
    }

    $RSIDEBAR_DATA .= ' <ul class="man-side_download">' . "\n"
                   . '  <li class="man-side_download">'
                   . make_link('/manual/', 'Download Documentation') . "</li>\n";
    $RSIDEBAR_DATA .= ' </ul>' . "\n\n";

    $RSIDEBAR_DATA .= "</td></tr></table></form>\n\n";
    $RSIDEBAR_DATA .= "<!-- END MANUAL'S SIDEBAR TOC -->\n\n";
}

function navigationBar($title, $id, $loc)
{
    global $NEXT, $PREV, $tstamp,$CHARSET;

    echo '<table class="man-nav" cellpadding="0" cellspacing="4">';
    echo "\n";

    echo ' <tr class="man-nav_prev-next" valign="top">';
    echo "\n";
    echo '  <td class="man-nav_prev" align="left">';
    echo "\n   ";
    if ($PREV[1]) {
        $link = @htmlspecialchars($PREV[1], ENT_QUOTES, $CHARSET);
        if (strlen($link) > 30) {
            $link = str_replace('::', '::<br />', $link);
        }
        make_image('caret-l.gif', 'previous');
        print_link($PREV[0], $link, false,
                   ($loc == 'top' ? 'accesskey="r"' : false)
        );
        echo ' (P<span class="accesskey">r</span>evious)';
    }
    echo "\n";
    echo '  </td>';
    echo "\n";

    echo '  <td class="man-nav_next" align="right">';
    echo "\n";
    if ($NEXT[1]) {
        $link = @htmlspecialchars($NEXT[1], ENT_QUOTES, $CHARSET);
        if (strlen($link) > 30) {
            $link = str_replace('::', '::<br />', $link);
        }
        echo '(Ne<span class="accesskey">x</span>t) ';
        print_link($NEXT[0], $link, false,
                   ($loc == 'top' ? 'accesskey="x"' : false)
        );
        make_image('caret-r.gif', 'next');
    }
    echo "\n";
    echo '  </td>';
    echo "\n";
    echo ' </tr>';
    echo "\n";

    echo ' <tr class="man-nav_space">';
    echo "\n";
    echo '  <td class="man-nav_space" colspan="2" height="1">';
    echo '<hr class="greyline" width="100%" />';
    echo '</td>';
    echo "\n";
    echo ' </tr>';
    echo "\n";

    echo ' <tr class="man-nav_langholder">';
    echo "\n";
    echo '  <td class="man-nav_langholder" colspan="2">';
    echo "\n";
    echo '   <table class="man-nav_langholder" width="100%" border="0">';
    echo "\n";
    echo '    <tr class="man-nav_view-updated" valign="top">';
    echo "\n";

    if ($loc != 'bottom') {
        global $LANGUAGES;
        $links = array();
        foreach($LANGUAGES as $code => $name) {
            if (file_exists("../$code/$id")) {
                $links[] = make_link("../$code/$id", $name);
            }
        }
        $file = substr($id,0,-4);
        if (file_exists("html/$file.html")) {
            $links[] = make_link("html/$file.html", 'Plain HTML');
        }

        echo '     <td class="man-nav_view" align="left">';
        echo "\n";
        if (count($links)) {
            echo 'View this page in';
        } else {
            echo '&nbsp;';
        }
        echo "\n";
        echo '     </td>';
        echo "\n";
        echo '     <td class="man-nav_updated" align="right">';
        echo "\n";
        echo 'Last updated: '.$tstamp;
        echo "\n";
        echo '     </td>';
        echo "\n";
        echo '    </tr>';
        echo "\n";

        if (count($links)) {
            echo '    <tr class="man-nav_languages">';
            echo "\n";
            echo '     <td class="man-nav_languages" colspan="2" align="left">';
            echo "\n";
            echo join(delim(false, ' | '), $links);
            echo "\n";
            echo '     </td>';
            echo "\n";
            echo '    </tr>';
            echo "\n";
        }

    } else {
        echo '     <td class="man-nav_download" align="left">';
        echo "\n";
        echo make_link('/download-docs.php', 'Download Documentation');
        echo "\n";
        echo '     </td>';
        echo "\n";
        echo '     <td class="man-nav_updated" align="right">';
        echo "\n";
        echo 'Last updated: '.$tstamp;
        echo "\n";
        echo '     </td>';
        echo "\n";
        echo '    </tr>';
        echo "\n";
        echo '    <tr><td colspan="2" class="man-nav_bug" align="left">';
        echo "\n";
        echo '    Do you think that something on this page is wrong?';
        $package_name = getPackageNameForId($id);
        echo '    Please <a href="' . getBugReportLink($package_name) . '">file a bug report</a> ';
        echo '    or <a href="/notes/add-note-form.php?redirect=' . htmlentities($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8') . '&uri=' . htmlspecialchars($id) . '">add a note</a>. ';
        echo "\n";
        echo '   </td></tr>';
        echo "\n";
        echo "<tr><td colspan=\"2\"><strong>User Notes:</strong></td></tr>\n";
        echo "<tr><td colspan=\"2\">\n";
        echo getComments($id);
        echo "</td></tr>\n";
        echo "\n";
    }

    echo '   </table>';
    echo "\n";
    echo '  </td>';
    echo "\n";
    echo ' </tr>';
    echo "\n";
    echo "</table>\n";

}

function getPackageNameForId($id)
{
    global $dbh;
    static $package_name = null;  // static variable to avoid multiple queries
    if (is_null($package_name)) {
        $res = preg_match('/^package\.[\w-]+\.([\w-]+).*\.php$/', $id, $matches);
        if ($res === 1) {
            $package = str_replace('-', '_', $matches[1]);
            $query = 'SELECT name FROM packages WHERE LCASE(name) = LCASE(?)';
            $package_name = $dbh->getOne($query, $package);
        }
    }
    return $package_name;
}

function getBugReportLink($package_name)
{
    $bug_report_link = '/bugs/report.php?package=Documentation';
    if (!is_null($package_name)) {
        $bug_report_link = '/bugs/report.php?package=' . $package_name;
    }
    return $bug_report_link;
}

function getComments($uri)
{
    $output = '';

    require_once 'notes/ManualNotes.class.php';
    $manualNotes = new Manual_Notes;
    $comments = $manualNotes->getPageComments($uri, auth_check('pear.dev'));

    if (empty($comments)) {
        $output .= 'There are no user contributed notes for this page.';
    }

    foreach ($comments as $comment) {
        $manualNotes->display($comment);
    }

    return $output;
}

function sendManualHeaders($charset, $lang)
{
        global $LANG,$CHARSET;
        $LANG = $lang;
        $CHARSET = $charset;
        Header('Cache-Control: public, max-age=600');
        Header('Vary: Cookie');
        Header('Content-type: text/html;charset=' . $charset);
        Header('Content-language: ' . $lang);
}

function manualHeader($title, $id = '')
{
    global $HTDIG, $LANGUAGES, $LANG, $CHARSET, $RSIDEBAR_DATA, $dbh;

    makeBorderTOC($title, $id);

    echo '<?xml version="1.0" encoding="' . $CHARSET . '" ?>';
    response_header('Manual :: ' . $title);
    # create links to plain html and other languages
    if (!$HTDIG) {
        navigationBar($title, $id, 'top');
    }
}

function manualFooter($title, $id = '')
{
    global $HTDIG;
    if (!$HTDIG) {
        navigationBar($title, $id, 'bottom');
    }

    response_footer();
}