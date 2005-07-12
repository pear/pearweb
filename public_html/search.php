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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "Damblan/Search.php";
require_once "Pager/Pager.php";

$term = (isset($_GET['q']) ? trim(htmlspecialchars(strip_tags(urldecode($_GET['q'])))) : "");
$in = (isset($_GET['in']) ? $_GET['in'] : "packages");

$search =& Damblan_Search::factory($in, $dbh);
$search->search($term);

response_header("Search: " . $term);

echo "<h1>Search</h1>\n";
echo "<h2>" . $search->getTitle() . "</h2>\n";

$total = $search->getTotal();

$params = array(
                "mode"       => "Jumping",
                "perPage"    => ITEMS_PER_PAGE,
                "urlVar"     => "p",
                //    "delta"      => 5,
                "itemData"   => range(1, $total),
                "extraVars"  => array("q" => $term)
);
$pager =& Pager::factory($params);

echo "<form method=\"get\" name=\"search\" action=\"search.php\">\n";
echo "<input type=\"text\" name=\"q\" value=\"" . $term . "\" size=\"30\" /><input type=\"submit\" value=\"Search\" />\n";
echo "<script language=\"JavaScript\" type=\"text/javascript\">document.forms.search.q.focus();</script>\n";
echo "</form>\n";

if ($total > 0) {
    $start = (($pager->getCurrentPageID() - 1) * ITEMS_PER_PAGE) + 1;
    $end = ($start + 9 < $total ? $start + 9 : $total);

    echo "<p>Results <strong>" . $start . " - " . $end . "</strong> of <strong>" . $search->getTotal() . "</strong>:</p>\n";

    echo "<ol start=\"" . $start . "\">\n";
    foreach ($search->getResults($pager) as $result) {
        echo "<li>\n";
        echo $result['html'];
        echo "</li>\n";
    }
    echo "</ol>\n";

    echo $pager->links;
} else if (!empty($term)) {
    echo "<p><div class=\"explain\">Sorry, but we didn't find anything that matches &quot;" . $term . "&quot;.</div></p>\n";
}

response_footer();
