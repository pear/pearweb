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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("Quality Assurance Initiative");
?>

<h1>The PEAR Quality Assurance Initiative</h1>

<p>The PEAR Quality Assurance Initiative is still in its early stages,
but some basic information about how the
<acronym title="Quality Assurance">QA</acronym>
team works can be found in the
<a href="/pepr/pepr-proposal-show.php?id=60">appendant RFC</a>.
</p>

<p>The first step was to elect the seven &quot;Core&quot; members of
the <acronym title="Quality Assurance">QA</acronym> Team:</p>

<ul>

<?php

  echo '<li>' . user_link('gurugeek', true) . "</li>\n";
  echo '<li>' . user_link('thesaur', true) . "</li>\n";
  echo '<li>' . user_link('arnaud', true) . "</li>\n";
  echo '<li>' . user_link('toby', true) . "</li>\n";
  echo '<li>' . user_link('schst', true) . "</li>\n";
  echo '<li>' . user_link('davey', true) . "</li>\n";

?>

</ul>

<p>If you are interested in helping out, or if you have questions 
concerning the <acronym title="Quality Assurance">QA</acronym> 
initiative, you can contact the team using the mailing list
<?php echo make_mailto_link('pear-qa@lists.php.net'); ?>
 (<a href="/support/lists.php">subscription information</a>).</p>

<?php
response_footer();
?>
