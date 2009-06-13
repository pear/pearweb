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

include_once 'pear-database-release.php';
$recent = release::getRecent(5);
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<strong>Recent&nbsp;Releases:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    $today = date("D, jS M y");
    foreach ($recent as $release) {
        $releasedate = format_date(strtotime($release['releasedate']), "D, jS M y");
        if ($releasedate == $today) {
            $releasedate = "today";
        }
        $RSIDEBAR_DATA .= "<tr><td>";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $release['name'] . "/\">";
        $RSIDEBAR_DATA .= wordwrap($release['name'],25,"\n",1) . ' ' .
                          $release['version'] . '</a><br /> <small>(' .
                          $releasedate . ')</small></td></tr>';
    }
    $feed_link = '<a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" /></a>';
    $RSIDEBAR_DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

$popular = release::getPopular(5);
if (@sizeof($popular) > 0) {
    $RSIDEBAR_DATA .= "<strong>Popular&nbsp;Packages*:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    foreach ($popular as $package) {
        $RSIDEBAR_DATA .= "<tr><td>";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $package['name'] . "/\">";
        $RSIDEBAR_DATA .= wordwrap($package['name'],25,"\n",1) . ' ' . $package['version'] . '</a><br /> <small>(' .
                          number_format($package['d'],2) . ')</small></td></tr>';
    }
    $feed_link = '<a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" /></a>';
    $RSIDEBAR_DATA .= "<tr><td><small>* downloads per day</small></td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

$rss_feed = DAMBLAN_RSS_CACHE_DIR . '/pear-news.xml';
if (file_exists($rss_feed)) {
    $blog = simplexml_load_file($rss_feed);
}

$self = strip_tags(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'iso-8859-1'));
response_header();
?>

<h1>PEAR - PHP Extension and Application Repository</h1>

<h2>&raquo; What is it?</h2>

<p><acronym title="PHP Extension and Application Repository">PEAR</acronym> is a framework and distribution system for reusable PHP components.</p>

<p>Sounds good? Perhaps you might want to know about <strong><a href="/manual/en/installation.php">installing PEAR on your system</a></strong> or <a href="/manual/en/guide.users.commandline.cli.php">installing pear packages</a>.</p>

<p>You can find help using PEAR packages in the <a href="/manual/en/">online manual</a> and the <a href="/manual/en/faq.php">FAQ</a>.</p>

<?php
if (!$auth_user) {
?>
<p>If you have been told by other PEAR developers to sign up for a PEAR website account, you can use <a href="/account-request.php"> this interface</a>.</p>
<?php
}
?>
<?php $n = 0; ?>
<h2>&raquo; Hot off the Press</h2>
<div id="news">
<?php if (!empty($blog)) { ?>
    <?php foreach ($blog->xpath('//item') as $node) { ?>
        <?php if ($n++ >= 3) { continue; } ?>
        <div class="news-entry">
            <h4><?php print make_link((string)$node->link, (string)$node->title); ?></h4>
            <?php print $node->description; ?>
            <p class="news-footer"><?php print $node->creator; ?> <?php print date("jS M Y h:ia", strtotime($node->pubDate)); ?>. Read <?php print make_link((string)$node->link, 'more'); ?> or see <?php print make_link((string)$node->comments, 'comments'); ?></p>
        </div>
    <?php } ?>
<?php } else { ?>
    <p>Looks like we don't have an RSS feed. Try adding a cron job to fetch <a href="http://blog.pear.bluga.net/feed/">http://blog.pear.bluga.net/feed/</a> and put it in <?php print $rss_feed; ?></p>
    <pre>wget --output-document=/var/tmp/pear/rss_cache/pear-news.xml http://blog.pear.bluga.net/feed/</pre>
<?php } ?>
</div>
<?php
response_footer();
