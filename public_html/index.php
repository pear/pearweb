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

require_once 'pear-database-release.php';
require_once 'pepr/pepr.php';
require_once 'pear-database-user.php';

$recent = release::getRecent(5);
$popular = release::getPopular(5);


$proposals = proposal::getRecent($dbh, 5);


$developers = user::listRecentUsersByKarma('pear.dev', 3);

$rss_feed = DAMBLAN_RSS_CACHE_DIR . '/pear-news.xml';
if (file_exists($rss_feed)) {
    $blog = simplexml_load_file($rss_feed);
}

$self = strip_tags(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'iso-8859-1'));

$extraHeaders = '<link rel="alternate" href="http://blog.pear.php.net/feed/" type="application/rss+xml" title="PEAR News" />';

response_header("PEAR - PHP Extension and Application Repository", false, $extraHeaders);
?>
<style type="text/css">
#search {
    display: none;
}
#header img {

}
.pearblurb {
    background-color: rgb(0, 60, 0); 
    color: rgb(150, 250, 150);
}
.pearblurb a {
    color: rgb(150, 250, 190);
    text-decoration: none;
    border: 0;
}

.pearblurb form input[type=text] {
    font-size: 1.5em;
    width: 12em;
}


.pearblurb form input[type=submit] {
    font-size: 1.2em;
}

.pearblurb form {
    margin: auto;
    display: table;
}

.pearblurb {
    border-right: 1px solid rgb(100, 100, 100);
}
div#body {
    padding: 0;
}

div#otherbody {
    padding:0.3em 1em 1em;
    margin-top: 3.0em
}

</style>
 
<div style=" background-color: white;  -moz-border-radius: 5px; border-bottom: 1px solid rgb(20, 50, 20);">
    <div style="display: table-cell; text-align: center; vertical-align: middle">
        <div style="text-align: left; margin: 3.0em;">
            <span style="font-size: 2.0em">PEAR2</span>
            <p>Find out more about the <a href="http://pear2.php.net/">next PEAR</a></p>
            <ul>
                <li>Pyrus installer built from the ground up for PHP 5.3+.</li>
                <li>Growing core of packages with solid test coverage.</li>
                <li>Channel architechture means <em>packaging and deploying your code is a snap!</em></em></li>
            </ul>
        </div>

       
    </div>
    <div style="display: table-cell; width: 50%; padding: 1.0em;" class="pearblurb">

        <div style="padding: 1.0em; color: white">
            <span style="font-size: 1.5em; display: block;"><img src="http://pearweb/gifs/pearsmall.gif" alt="PEAR" /></span>
            PHP Extension and Application Repository
        </div>

            <p><acronym title="PHP Extension and Application Repository">PEAR</acronym> is a framework and distribution system for reusable PHP components.</p>

            <p>Sounds good? Perhaps you might want to know about <strong><a href="/manual/en/installation.php">installing PEAR on your system</a></strong> or <a href="/manual/en/guide.users.commandline.cli.php">installing pear packages</a>.</p>

            <form action="/search.php" method="get">
                <p><div style="padding-bottom: 0.2em">find a package:</div><input type="submit" name="" value="Go" style="float: right" /><input type="text"  name="q"/>
                <input type="hidden" name="in" value="packages" /><br />
</p>

                <p style="text-align: right">search for <strong>Mail</strong> or <strong>MDB2</strong> </p>
            </form>

            <p>You can find help using PEAR packages in the <a href="/manual/en/">online manual</a> and the <a href="/manual/en/faq.php">FAQ</a>.</p>

    <?php
    if (!$auth_user) {
    ?>
    <p>If you have been told by other PEAR developers to sign up for a PEAR website account, you can use <a href="/account-request.php"> this interface</a>.</p>
    <?php
    }
    ?>
    </div>
</div>
<div id="otherbody">
<?php $n = 0; ?>
<h2>&raquo; Hot off the Press</h2>
<div id="news">
<?php if (!empty($blog)) { ?>
    <?php foreach ($blog->xpath('//item') as $node) { ?>
        <?php if ($n++ >= 3) { continue; } ?>
        <div class="news-entry">
            <h4><?php print make_link((string)$node->link, (string)$node->title); ?></h4>
            <?php foreach ($node->children('content', true) as $description) { ?>
                <?php print $description; ?>
            <?php } ?>
            <p class="news-footer"><?php print $node->creator; ?> <?php print date("jS M Y h:ia", strtotime($node->pubDate)); ?>. Read <?php print make_link((string)$node->link, 'more'); ?> or see <?php print make_link((string)$node->comments, 'comments'); ?></p>
        </div>
    <?php } ?>
<?php } else { ?>
    <p>Looks like we don't have an RSS feed. Try adding a cron job to fetch <a href="http://blog.pear.php.net/feed/">http://blog.pear.php.net/feed/</a> and put it in <?php print $rss_feed; ?></p>
    <pre>wget --output-document=/var/tmp/pear/rss_cache/pear-news.xml http://blog.pear.php.net/feed/</pre>
<?php } ?>
</div>




<h2>PEAR Community</h2>
<div style="float: left">
<script type="text/javascript" src="http://www.ohloh.net/p/3322/widgets/project_basic_stats.js"></script>
</div>
<h3>Need help?</h3>
<p>You can find help and <a href="/support/">support</a> on our <a href="http://pear.php.net/support/lists.php">mailing lists</a>, and <a href="irc://irc.efnet.org/pear">IRC channel</a></p>
<p>Our developers are also on <a href="http://www.linkedin.com/groups?gid=36298">LinkedIn</a>, <a href="http://www.ohloh.net/p/pear">Ohloh</a>, <a href="http://search.twitter.com/search?q=%23pear">Twitter</a>, <a href="http://identi.ca/group/pear">Identi.ca</a> or <a href="http://www.facebook.com/group.php?gid=7851891162">Facebook</a>, as well as the <a href="http://wiki.php.net/pear/">wiki</a>.</p>
<br style="clear: both; margin-bottom: 10.0em" />

<?php if (!empty($proposals)) {?>
<div style="display: table-cell; width: 25%;">
<?php
    $DATA = "<h3>Recently Proposed</h3>\n";
    $DATA .= '<table class="sidebar-releases">' . "\n";
    foreach ($proposals as $proposal) {
        $DATA .= "<tr><td>";
        $DATA .= make_link('/pepr/pepr-proposal-show.php?id=' . $proposal->id, wordwrap($proposal->pkg_category . '::' . $proposal->pkg_name,25,"\n",1)); 
        $DATA .= '<br />by ' . make_link('/user/' . htmlspecialchars($proposal->user_handle), $proposal->user_handle);

        $DATA .= '</td></tr>';
    }
    $feed_link = '<a href="/pepr/" title="PEPR Proposals">See all</a>';
    $DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $DATA .= "</table>\n";

    print $DATA;
?>
</div>
<?php } ?>

<?php if (!empty($developers)) { ?>
<div style="display: table-cell;  width: 25%;">
<h3>New Developers</h3>
<table class="sidebar-releases">
    <?php foreach ($developers as $developer) { ?>
        <tr><td><?php print make_link('/user/' . htmlspecialchars($developer['handle']), $developer['name']) . '<br />' . $developer['handle']; ?>

        </td></tr>
    <?php } ?>

    <tr><td align="right"><a href="/user/" title="Developers">See all</a></td></tr>
    </table>
</div>
<?php } ?>

<div style="display: table-cell;  width: 25%;">
<?php
if (!empty($recent) > 0) {
    $DATA = "<h3>Recent Releases</h3>\n";
    $DATA .= '<table class="sidebar-releases">' . "\n";
    $today = date("D, jS M y");
    foreach ($recent as $release) {
        $releasedate = format_date(strtotime($release['releasedate']), "D, jS M y");
        if ($releasedate == $today) {
            $releasedate = "today";
        }
        $DATA .= "<tr><td>";
        $DATA .= "<a href=\"/package/" . $release['name'] . "/\">";
        $DATA .= wordwrap($release['name'],25,"\n",1) . ' ' .
                          $release['version'] . '</a><br /> <small>(' .
                          $releasedate . ')</small></td></tr>';
    }
    $feed_link = '<a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" /></a>';
    $DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $DATA .= "</table>\n";
}

print $DATA;
?>
</div>
<?php if (!empty($popular)) { ?>
<div style="display: table-cell; width: 25%;">
    <h3>Popular Packages</h3>
    <table class="sidebar-releases">
    <?php foreach ($popular as $package) { ?>
        <tr>
            <td>
                <a href="/package/<?php print $package['name']; ?>"><?php print wordwrap($package['name'],25,"\n",1) . ' ' . $package['version']; ?></a><br />
                <small>(<?php print number_format($package['d'],2); ?>)</small>
            </td>
        </tr>

    ?>
    <?php } ?>
        <tr><td><small>downloads per day</small></td></tr>
        <tr><td align="right"><a href="/feeds/" title="Information about XML feeds for the PEAR website"><img src="/gifs/feed.png" width="16" height="16" alt="" /></a></td></tr>
    </table>
</div>
<?php } ?>
</div>
<?php
response_footer();
