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

require_once "site.php";

$doc_languages = array("en" => "English", 
                       "fr" => "French",
                       "ja" => "Japanese",
                       "hu" => "Hungarian",
                       /*
                         "de" => "German", 
                         "it" => "Italian", 
                       */
                       "nl" => "Dutch", 
                       "ru" => "Russian");

$NEXT = $PREV = $UP = $HOME = array(false, false);
$TOC = array();

$SIDEBAR_DATA = '';

function setupNavigation($data) {
    global $NEXT, $PREV, $UP, $HOME, $TOC, $tstamp;
    $HOME = @$data["home"];
    $HOME[0] = "./";
    $NEXT = @$data["next"];
    $PREV = @$data["prev"];
    $UP   = @$data["up"];
    $TOC =  @$data["toc"];
    $tstamp = gmdate("D, d M Y",getlastmod());
}

function makeBorderTOC($this) {
    global $NEXT, $PREV, $UP, $HOME, $TOC, $DOCUMENT_ROOT;
    global $SIDEBAR_DATA, $LANG,$CHARSET;

    $SIDEBAR_DATA  = "\n\n<!-- START MANUAL'S SIDEBAR TOC -->\n\n";
    $SIDEBAR_DATA .= '<form method="get" action="/manual-lookup.php">' . "\n";
    $SIDEBAR_DATA .= '<table border="0" cellpadding="4" cellspacing="0">' . "\n";

    /** The manual lookup will be implemented at a later point.
    $SIDEBAR_DATA .= '<tr valign="top"><td><small>' .
        '<input type="hidden" name="lang" value="' . $LANG . '">' .
        'lookup: <input type="text" class="small" name="function" size="10"> ' .
        make_submit('small_submit_white.gif', 'lookup', 'bottom') .
        '<br /></small></td></tr>';

    $SIDEBAR_DATA .= '<tr bgcolor="#cccccc"><td></td></tr>';
    */

    $SIDEBAR_DATA .= '<tr valign="top"><td>' . "\n";

    $SIDEBAR_DATA .= ' <ul class="man-side_top">' . "\n"
                   . '  <li class="man-side_top">'
                   . make_link('./', $HOME[1]) . "\n"
                   . ' </ul>' . "\n\n";

    $SIDEBAR_DATA .= ' <hr class="greyline" width="100%" />' . "\n\n";

    if (($HOME[1] != $UP[1]) && $UP[1]) {
        $SIDEBAR_DATA .= ' <ul class="man-side_up">' . "\n"
                       . '  <li class="man-side_up">'
                       . make_link('./', $UP[1]) . "\n"
                       . ' </ul>' . "\n\n";
    }

    $SIDEBAR_DATA .= ' <ul class="man-side_pages">' . "\n";

    for ($i = 0; $i < count($TOC); $i++) {
        list($url, $title) = $TOC[$i];
        if (!$url || !$title) {
            continue;
        }
        $title_fmt = @htmlspecialchars($title, ENT_QUOTES, $CHARSET);
        if (strlen($title_fmt) > 25) {
            $title_fmt = str_replace('::', '::<br />', $title_fmt);
        }

        $SIDEBAR_DATA .= '  <li class="man-side_page">'
                . (($title == $this) ? $title_fmt : make_link($url, $title_fmt))
                . "</li>\n";
    }

    $SIDEBAR_DATA .= " </ul>\n\n";

    if (count($TOC) > 1) {
        $SIDEBAR_DATA .= ' <hr class="greyline" width="100%" />' . "\n\n";
    }

    $SIDEBAR_DATA .= ' <ul class="man-side_download">' . "\n"
                   . '  <li class="man-side_download">'
                   . make_link('/manual/', 'Download Documentation') . "\n"
                   . ' </ul>' . "\n\n";

    $SIDEBAR_DATA .= "</td></tr></table></form>\n\n";
    $SIDEBAR_DATA .= "<!-- END MANUAL'S SIDEBAR TOC -->\n\n-";
}

function navigationBar($title,$id,$loc) {
    global $NEXT, $PREV, $tstamp,$CHARSET;

    echo '<table class="man-nav" border="0" width="620" bgcolor="#e0e0e0" cellpadding="0" cellspacing="4">';
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
        echo ' (P<u>r</u>evious)';
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
        echo '(Ne<u>x</u>t) ';
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

    echo ' <tr class="man-nav_space" bgcolor="#cccccc" height="1">';
    echo "\n";
    echo '  <td class="man-nav_space" colspan="2" height="1">';
    echo "\n";
    spacer(1,1);
    echo "\n";
    echo '  </td>';
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
            echo '     </small></td>';
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
    }

    echo '   </table>';
    echo "\n";
    echo '  </td>';
    echo "\n";
    echo ' </tr>';
    echo "\n";
    echo "</table>\n";

}

function sendManualHeaders($charset,$lang) {
        global $LANG,$CHARSET;
        $LANG = $lang;
        $CHARSET = $charset;
    Header("Cache-Control: public, max-age=600");
    Header("Vary: Cookie");
    Header("Content-type: text/html;charset=$charset");
    Header("Content-language: $lang");
}

function manualHeader($title,$id="") {
    global $HTDIG, $LANGUAGES, $LANG, $SIDEBAR_DATA, $dbh;

    makeBorderTOC($title);

    /**
     * Show link to the package info file?
     */
    if (strstr(basename($_SERVER['PHP_SELF']), "packages.")
        && substr_count($_SERVER['PHP_SELF'], ".") > 2) {

        $package = substr(basename($_SERVER['PHP_SELF']), 0, (strlen(basename($_SERVER['PHP_SELF'])) - 4));
        $package = preg_replace("/(.*)\./", "", $package);

        $query = "SELECT id FROM packages WHERE LCASE(name) = LCASE('" . $package . "')";
        $sth = $dbh->query($query);
        $row = $sth->fetchRow();

        if (is_array($row)) {
            ob_start();

            echo "<div align=\"center\"><br /><br />\n";

            $bb = new Borderbox("Download");

            echo "<div align=\"left\">\n";
            print_link("/package-info.php?pacid=" . $row[0], make_image("box-0.gif") . " Package info");
            echo "</div>\n";
            $bb->end();

            echo "</div>\n";

            $SIDEBAR_DATA .= ob_get_contents();
            ob_end_clean();
        }
    }

    response_header('Manual: '.$title);
        # create links to plain html and other languages
    if (!$HTDIG) {
        navigationBar($title, $id, "top");
    }
}

function manualFooter($title,$id="") {
    global $HTDIG;
    if (!$HTDIG) {
        navigationBar($title, $id, "bottom");
    }

    response_footer();
}
?>
