<?php

/**
 * The bug system home page
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

response_header("Bugs");
?>

<h1>PEAR Bug Tracking System</h1>
<p>The following options are avaible:</p>
<ul>
  <li><?php print_link('/bugs/search.php', 'Search for <b>existing bugs</b>'); ?></li>
  <li>Report a new bug for the:
      <?php print make_bug_link('Web Site', 'report', '<b>Web Site</b>');?>,
      <?php print make_bug_link('PEPr', 'report', '<b>PEPr</b>');?>,
      <?php print make_bug_link('Documentation', 'report', '<b>Documentation</b>');?> or
      <?php print make_bug_link('Bug System', 'report', '<b>Bug System</b>');?>
  </li>
  <li>If you want to report a bug for a <b>specific package</b>, please go to the
  package home using the <?php print_link('/packages.php', 'Browse packages');?> tool
  or the package <?php print_link('/package-search.php', 'Search System'); ?>.
  </li>
</ul>
<p>If you need support or you don't really know if it is a bug or not, please
use our <? print_link('/support.php', 'support channels');?>.</p>

<p>Before submitting a bug, please make sure nobody has already reported it.
Read our tips on how to <?php print_link('http://bugs.php.net/how-to-report.php', 'report a bug that someone will want to help fix', 'top');?>.
</p>
<?php
response_footer();
?>