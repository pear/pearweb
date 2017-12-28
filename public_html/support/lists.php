<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 The PEAR Group                               |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header('Support - Mailing Lists');

/*
 * array of lists (
 *     list,
 *     name,
 *     short desc.,
 *     moderated,
 *     archive,
 *     digest,
 *     newsgroup
 * )
 */
$mailing_lists = array(
    'PEAR Mailing Lists',

    array (
        'pear-general',
        'PEAR general list',
        'A list for people with questions on how to use PEAR',
        false,
        true,
        true,
        'php.pear.general',
        'php.pear.general', // identifier for gmane.org
    ),

    array (
        'pear-dev',
        'PEAR developers list',
        'A list for the people who make PEAR packages',
        false,
        true,
        true,
        'php.pear.dev',
        'php.pear.devel',
    ),

    array (
        'pear-cvs',
        'PEAR SVN list',
        'All commits to PEAR\'s SVN repository get automatically posted to this list',
        false,
        true,
        true,
        'php.pear.cvs',
        'php.cvs.pear',
    ),

    array (
        'pear-doc',
        'PEAR documentation list',
        'A list for discussing topics related to the PEAR documentation.',
        false,
        true,
        true,
        'php.pear.doc',
        'php.pear.documentation',
    ),

    array (
        'pear-qa',
        'PEAR QA list',
        'A list for managing PEAR\'s Quality Assurance process',
        false,
        true,
        true,
        'php.pear.qa',
        'php.pear.qa',
    ),

    array (
        'pear-core',
        'PEAR Core development list',
        'A list for the people who make PEAR\'s core infrastructure',
        false,
        true,
        true,
        'php.pear.core',
        'php.pear.core',
    ),

    array (
        'pear-webmaster',
        'PEAR webmaster list',
        'A list for the people managing PEAR\'s website',
        false,
        true,
        true,
        'php.pear.webmaster',
        'php.pear.website',
    ),

    array (
        'pear-bugs',
        'PEAR bugs list',
        'A list for people that want to monitor every single bug and comments/changes on those bugs',
        false,
        false,
        true,
        'php.pear.bugs',
        'php.pear.bugs',
    ),
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

<table class="form-holder" cellpadding="5" cellspacing="1">

<?php

while (list(, $listinfo) = each($mailing_lists)) {
    if (!is_array($listinfo)) {
        echo ' <tr>' . "\n";
        echo '  <th class="form-label_top_center">' . $listinfo . '</th>' . "\n";
        echo '  <th class="form-label_top_center">Moderated</th>' . "\n";
        echo '  <th class="form-label_top_center">Archive</th>' . "\n";
        echo '  <th class="form-label_top_center">Newsgroup</th>' . "\n";
        echo '  <th class="form-label_top_center">Name</th>' . "\n";
        echo ' </tr>' . "\n";
    } else {
        echo ' <tr>' . "\n";
        echo '  <td class="form-input"><strong>' . $listinfo[1] . '</strong><br /><small>'. $listinfo[2] . "</small></td>\n";
        echo '  <td class="form-input_center">' . ($listinfo[3] ? 'yes' : 'no') . "</td>\n";
        echo '  <td class="form-input_center">' . ($listinfo[4] ? make_link("http://news.gmane.org/gmane.comp." . $listinfo[7], 'yes') : 'n/a') . "</td>\n";
        echo '  <td class="form-input_center">' . ($listinfo[6] ? make_link("news://news.php.net/".$listinfo[6], 'yes') . ' ' . make_link("http://news.php.net/group.php?group=".$listinfo[6], 'http') : 'n/a') . "</td>\n";
        echo '  <td class="form-input" style="vertical-align: middle"><tt>' . $listinfo[0] . "</tt></td>\n";
        echo ' </tr>' . "\n";
    }
}

?>

</table>

<p>
 To subscribe, send a mail to <tt>$name-subscribe@lists.php.net</tt>.
</p>

<?php
response_footer();
