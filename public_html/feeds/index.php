<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PEAR Group                               |
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
response_header('Syndication Feeds');
?>

<h1>Syndication Feeds</h1>

<h2>&raquo; RSS</h2>

<p>We have a number of <?php echo make_link('http://web.resource.org/rss/1.0/', 'RSS'); ?> 
feeds available for your viewing pleasure:</p>

<ul>
  <li><?php echo make_link('/feeds/latest.rss'); ?>: The latest 10 releases</li>
  <li><?php echo make_link('/feeds/popular.rss'); ?>: The most popular 10 releases</li>
  <li>Package feeds per category:
    <ul>
      <li><?php echo make_link('/feeds/cat_authentication.rss'); ?>: Authentication</li>
      <li><?php echo make_link('/feeds/cat_benchmarking.rss'); ?>: Benchmarking</li>
      <li>For all other categories, the same scheme applies</li>
    </ul>
  </li>
  <li>Package feeds per package:
    <ul>
      <li><?php echo make_link('/feeds/pkg_auth.rss'); ?>: Auth</li>
      <li><?php echo make_link('/feeds/pkg_mail_mime.rss'); ?>: Mail_Mime</li>
      <li>For all other packages, the same scheme applies</li>
    </ul>
  </li>
  <li>Package feeds per user:
    <ul>
      <li><?php echo make_link('/feeds/user_jon.rss'); ?>: Jon Parise (jon)</li>
      <li><?php echo make_link('/feeds/user_mj.rss'); ?>: Martin Jansen (mj)</li>
      <li>For all other users, the same scheme applies</li>
    </ul>
  </li>
  <li>PEPr proposal feeds:
    <ul>
      <li><?php echo make_link('/feeds/pepr.rss'); ?>: Most recent proposals</li>
      <li><?php echo make_link('/feeds/pepr_draft.rss'); ?>: Most recent proposals with a status of &quot;Draft&quot;</li>
      <li><?php echo make_link('/feeds/pepr_proposal.rss'); ?>: Most recent proposals with a status of &quot;Proposal&quot;</li>
      <li><?php echo make_link('/feeds/pepr_vote.rss'); ?>: Most recent proposals with a status of &quot;Vote&quot;</li>
      <li><?php echo make_link('/feeds/pepr_finished.rss'); ?>: Most recent proposals with a status of &quot;Finished&quot;</li>
    </ul>
  </li>
  <li>PEPr proposal search feeds:
    <ul>
      <li><?php echo make_link('/feeds/pepr_config.rss'); ?>: View all proposals containing &quot;config&quot;</li>
      <li><?php echo make_link('/feeds/pepr_XML.rss'); ?>: View all proposals containing &quot;XML&quot;</li>
      <li><?php echo make_link('/feeds/pepr_search_table+config.rss'); ?>: View proposals containing &quot;table&quot; and &quot;config&quot;</li>
      <li>For other searches, the same scheme applies.</li>
    </ul>
  </li>
</ul>

<p>If you have questions or suggestions about the RSS service, please
contact the <?php echo make_mailto_link(PEAR_WEBMASTER_EMAIL, 'webmasters'); ?>.</p>

<?php
response_footer();
?>
