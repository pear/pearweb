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

$SIDEBAR_DATA='
<h3>Documentation</h3>
<p>
The manual section for PEAR can be found
<a href="/manual/">here</a>.</p>
';

response_header("Support");
?>

<h2>Support</h2>

<b>Table of Contents</b>
<ul>
  <li><a href="support.php#lists">Mailing Lists</a></li>
  <li><a href="support.php#tutorials">Tutorials</a></li>
  <li><a href="support.php#resources">Resources</a></li>
  <li><a href="support.php#icons">PEAR Icons</a></li>
</ul>

<a name="lists" /><h3>Mailing Lists</h3>

<?php
if (isset($maillist)) {
    # should really grab some email validating routine and use it here.
    if (empty($email) || $email == 'user@example.com') {
        echo "You forgot to specify an email address to be added to the list.  ";
        echo "Go back and try again.";
    } else {
        $request = strtolower($action);
                if ($request != "subscribe" && $request != "unsubscribe") {
                    $request = "subscribe";
                }
        $sub = str_replace("@", "=", $email);
        switch ($maillist) {
            default:
            mail("$maillist-$request-$sub@lists.php.net", "Website Subscription",
                "This was a request generated from the form at http://pear.php.net/support.php.", "From: $email\r\n");
            break;
        }
?>
<p>
A request has been entered into the mailing list processing queue. You
should receive an email at <?php echo $email;?> shortly describing how to
complete your request.
</p>
<?php
    }
} else {

  // array of lists (list, name, short desc., moderated, archive, digest, newsgroup)
  $mailing_lists = array(

    'PEAR mailing lists',

    array (
      'pear-general', 'PEAR general list',
      'A list for users of PEAR',
      false, true, true, "php.pear.general"
    ),

    array (
      'pear-dev', 'PEAR developers list',
      'A list for developers of PEAR',
      false, true, true, "php.pear.dev"
    ),

    array (
      'pear-cvs', 'PEAR CVS list',
      'All the commits of the cvs PEAR code repository are posted to this list automatically',
      false, true, true, "php.pear.cvs"
    ),

    array (
      'pear-doc', 'PEAR documentation list',
      'A list for discussing topics related to the PEAR documentation.',
      false, true, true, "php.pear.doc"
    ),

    array (
      'pear-qa', 'PEAR QA list',
      'A list for managing the PEAR Quality Assurance',
      false, false, true, "php.pear.qa"
    ),

    array (
      'pear-core', 'PEAR Core development list',
      'A list, where the core infrastructure of PEAR is discussed',
      false, false, true, "php.pear.core"
    ),

    array (
      'pear-webmaster', 'PEAR webmaster list',
      'A list for managing the collaboration of the people working on the website.',
      false, false, true, "php.pear.webmaster"
    )
  );
?>
<p>
There are <?php echo count($mailing_lists)-1; ?> PEAR-related mailing 
lists available. Most of them have archives available, and they are
also available as newsgroups on our 
<a href="news://news.php.net">news server</a>. The archives are
searchable. The lists are described in more detail in the
<a href="/manual/en/support.php">manual</a>.
</p>

<form method="POST" action="http://pear.php.net/support.php">
<p>
<table cellpadding="5" cellspacing="1">
<?php

while ( list(, $listinfo) = each($mailing_lists)) {
    if (!is_array($listinfo)) {

        echo '<tr bgcolor="#cccccc">';
        echo '<th>' . $listinfo . '</th>';
        echo '<th>Moderated</th>';
        echo '<th>Archive</th>';
        echo '<th>Newsgroup</th>';
        echo '<th>Normal</th>';
        echo '<th>Digest</th>';
        echo '</tr>' . "\n";

    } else {

        echo '<tr align="center" bgcolor="#e0e0e0">';
        echo '<td align="left"><b>' . $listinfo[1] . '</b><br /><small>'. $listinfo[2] . '</small></td>';
        echo '<td>' . ($listinfo[3] ? 'yes' : 'no') . '</td>';
        echo '<td>' . ($listinfo[4] ? make_link("http://marc.theaimsgroup.com/?l=".$listinfo[0], 'yes') : 'n/a') . '</td>';
        echo '<td>' . ($listinfo[6] ? make_link("news://news.php.net/".$listinfo[6], 'yes') . ' ' . make_link("http://news.php.net/group.php?group=".$listinfo[6], 'http') : 'n/a') . '</td>';
        echo '<td><input name="maillist" type="radio" value="' . $listinfo[0] . '" /></td>';
        echo '<td>' . ($listinfo[5] ? '<input name="maillist" type="radio" value="'.$listinfo[0].'-digest" />' : 'n/a' ) . '</td>';
        echo '</tr>' . "\n";

    }
}

?>
</table>
</p>

<p align="center">
<b>Email:</b>
<input type="text" name="email" width="40" value="user@example.com" />
<input type="submit" name="action" value="Subscribe" />
<input type="submit" name="action" value="Unsubscribe" />
</p>

</form>

<p>
You will be sent a confirmation mail at the address you wish to
be subscribed or unsubscribed, and only added to the list after
following the directions in that mail.
</p>

<p>
There are a variety of commands you can use to modify your subscription.
Either send a message to pear-<tt>whatever</tt>@lists.php.net (as in,
pear-general@lists.php.net) or you can view the commands for
ezmlm <a href="http://www.ezmlm.org/ezman-0.32/ezman1.html">here</a>.
</p>

<p>
If you have questions concering this website, you can contact
<a href="mailto:pear-webmaster@lists.php.net">pear-webmaster@lists.php.net</a>.
</p>

<p>
Of course don't forget to visit the <i>#pear</i> IRC channel at the <a href="http://www.efnet.org">
Eris Free Net</a>.
</p>

<p>For german-speaking PEAR users <a href="http://www.pear-forum.de/">PEAR.forum</a>
provides web discussion boards, where questions concerning PEAR can
be discussed.</p>

<div class="listing">

<a name="tutorials" /><h3>PEAR Tutorials</h3>
<h4>PEAR Tutorials sites</h4>
<ul>
    <li>Tutorials about PEAR, available in many languages: 
    <a href="http://www.pearfr.org">www.pearfr.org</a>.
    </li>
    <li>Links to PEAR tutorials: <a href="http://www.phpkitchen.com/staticpages/index.php?page=2003041204203962">
       www.phpkitchen.com</a>
    </li>
</ul>

<h4>Tutorials about the PEAR DB package:</h4>

<ul>
    <li>Tutorial and conferences papers about a lot of PEAR packages
        <a href="http://www.pearfr.org/~amerz/">http://www.pearfr.org/~amerz/</a>
    </li>
	    
    <li><a href="http://www.pearfr.org/index.php/en/article/db_pager">Pear DB_Pager package Tutorial</a> by Tomas V.V.Cox,
    Arnaud Limbourg.
    This package handles all the stuff needed for displaying paginated results from an array or a database result.
    </li>

    <li><a href="http://www.phpbuilder.com/columns/allan20010115.php3">PEAR DB Tutorial</a> on phpbuilder.com</li>

    <li><a href="http://www.onlamp.com/pub/a/php/2001/11/29/peardb.html">PEAR::DB Primer</a> on O'Reilly Network</li>

    <li><a href="http://www.nusphere.com/products/library/script_peardb.pdf">Writing Scripts with PHP's PEAR DB Class</a> - by Paul DuBois (PDF) in nusphere.com</li>

    <li><a href="http://evolt.org/article/Abstract_PHP_s_database_code_with_PEAR_DB/17/21927/index.html">Abstract PHP's database code with PEAR::DB</a> on evolt.org</li>

    <li><a href="http://www.devshed.com/Server_Side/PHP/DBAbstraction">Database Abstraction With PHP</a> on devshed.com</li>

    <li><a href="http://nyphp.org/content/presentations/db160/">PEAR DB: What's New in 1.6.0</a> by Daniel Convissor</li>
</ul>

<h4>Tutorials about some PEAR packages in German:</h4>

<ul>
    <li>&quot;<a href="http://www.heise.de/ix/artikel/2003/12/124/">XML Transformer kann XSLT ersetzen</a>&quot;</li>

    <li>IT[X]:
    <a href="http://www.ulf-wendel.de/projekte/itx/index.php">http://www.ulf-wendel.de/projekte/itx/index.php</a>
    </li>

    <li><a href="http://www.ulf-wendel.de/projekte/menu/tutorial.php">Menu.php Tutorial</a> in German.
    This package generates a HTML menu from a multidimensional hash.</li>

    <li><a href="http://www.ulf-wendel.de/projekte/cache/">Tutorial about the PEAR Cache package</a></li>
</ul>

<h4>Other tutorials:</h4>

<ul>
    <li><a href="http://www.devshed.com/c/a/PHP/Serializing-XML-With-PHP/">Serializing XML With PHP</a>
    covers the task of serializing XML documents with PHP with the help of the 
    <a href="/package/XML_Serializer/">XML_Serializer</a> package.</li>

    <li><a href="http://www.sitepoint.com/article/xml-php-pear-xml_serializer">Instant XML with PHP and PEAR::XML_Serializer</a>
    is another article about XML_Serializer.</li>

    <li><a href="http://www.php-mag.net/itr/online_artikel/psecom,id,455,nodeid,114.html">Grokking Stats</a>:
    Article (PDF) by <a href="/user/jmcastagnetto">Jesus M. Castagnetto</a>
    about <a href="/package/Math_Stats/">Math_Stats</a> and 
    <a href="/package/Math_Histogram/">Math_Histogram</a>.</li>

    <li><a href="http://php.chregu.tv/sql2xml/">XML_sql2xml tutorial</a>. A PEAR package for converting SQL
    query results or arrays to XML.</li>

    <li><a href="http://www.mamasam.com/tutorials/en_html_table.html">Table.php</a> Tutorial. The table package
    allows you to generate HTML tables without the need to include HTML tags in your PHP code.</li>

   <li><a href="http://www.zend.com/zend/art/art-heyes.php">PEAR::Mail</a> on zend.com</li>

</ul>

<a name="resources" /><h3>PEAR resources</h3>

<ul>
    <li><a href="http://www.schlitt.info/download/PEAR_Powerworkshop_Spring_2004.tar.gz">2004-05-03 Amsterdam - 
    International PHP Conference Spring edition - PEAR Powerworkshop - PEAR not just a fleshy pome</a>
    by Lukas Smith and Tobias Schlitt.</li>

    <li><a href="http://www.planet-php.net/">Planet PHP</a> - The news and weblog aggregation portal. Get all
    PHP/PEAR information in one place.</li>

    <li><a href="http://www.schlitt.info/index.php/content.publications_phpconf2003ffm">2003-11-04 Frankfurt - 
    International PHP Conference - PEAR im Einsatz</a>
    by Tobias Schlitt.</li>

    <li><a href="http://conf2.php.net/show/powerpear">2002-11-03 Frankfurt - 
    International PHP Conference - Teach Yourself PEAR in 6 Hours</a>
    by Stig S. Bakken.</li>

    <li><a href="http://conf.php.net/pres/slides/pres/index.php?p=slides%2Fpear%2Flt2002&amp;id=pearlt2002">
    2002-06-08 Karlsruhe - LinuxTag - The State of (the) PEAR </a> by Stig S. Bakken.</li>


    <li><a href="http://conf.php.net/pres/index.php?p=slides%2Fpear&amp;id=pear">A presentation</a> by Stig Bakken,
    who is leading PEAR development, at ApacheCon 2001 on PEAR.</li>

    <li><a href="http://talks.php.net/show/sdphp_using_tools"><q>Making your (coding) life simpler</q></a>
    by Jesus M. Castagnetto (Presentation about PEAR and phpDocumentor 
    at a local PHP user group)</li>

    <li><a href="http://conf.php.net/pear2">An updated presentation</a> by Jon Parise,
    a PEAR contributor, from the O'Reilly Open Source Convention 2001.</li>

    <li><a href="http://pear.sourceforge.net/manual/">Russian PEAR Manual</a>.</li>

    <li><a href="http://www.onlamp.com/pub/a/php/2001/10/11/pearcache.html">Caching PHP Programs with PEAR</a> on O'Reilly Network</li>

    <li><a href="http://www.onlamp.com/pub/a/php/2001/07/19/pear.html">A Detailed Look at PEAR</a> on O'Reilly Network</li>

    <li><a href="http://www.onlamp.com/pub/a/php/2001/05/24/pear.html">An Introduction to PEAR</a> on O'Reilly Network</li>

    <li><a href="http://www.phpconcept.net/articles/article.en.php?id=1">Configure WinCVS for PEAR</a> by Vincent Blavet</li>

    <li><a href="http://www.macdevcenter.com/pub/a/mac/2003/01/21/pear_macosx.html">O'Reilly Network: PHP's PEAR on Mac OS X</a></li>
</ul>

</div>

<a name="icons" /><h3>Powered By PEAR</h3>

<p>
    What programming tool would be complete without a set of
    icons to put on your webpage, telling the world what makes
    your site tick?
</p>

<?php

$icons = Array(
    'pear-power.gif'	=> 'Powered by PEAR, GIF format',
    'pear-power.png'	=> 'Powered by PEAR, PNG format',
    'pear-icon.gif'		=> '32x32 PEAR icon, GIF format',
    'pear-icon.png'		=> '32x32 PEAR icon, PNG format',
    'pear-blogbutton1.png'		=> '80x15 PEAR blog button, PNG format',
    'pear-blogbutton2.png'		=> '80x15 PEAR blog button, PNG format',
);

echo '<table cellpadding="5" cellspacing="1">';

foreach ($icons as $file => $desc) {
    echo '<tr bgcolor="e0e0e0">';
    echo '<td>' . make_image($file,$desc) . '<br /></td>';
    echo '<td>' . $desc . '<br /><small>';
    $size = @getimagesize($HTTP_SERVER_VARS['DOCUMENT_ROOT'].'/gifs/'.$file);
    if ($size) {
        echo $size[0] . ' x ' . $size[1] . ' pixels<br />';
    }
    $size = @filesize($HTTP_SERVER_VARS['DOCUMENT_ROOT'].'/gifs/'.$file);
    if ($size) {
        echo $size . ' bytes<br />';
    }
    echo '</small>';
    echo '</td></tr>';
}

echo '</table>';

echo '<p><b>Note:</b> Please do not just include these icons directly but
        download them and save them locally in order to keep HTTP traffic
        low.</p>';
}
response_footer();
?>
