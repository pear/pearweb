<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004 The PEAR Group                                    |
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

<p>As a first step seven people were nominated to form the 
<acronym title="Quality Assurance">QA</acronym> Core Group:</p>

<ul>

<?php

  echo '<li>' . user_link('gurugeek') . "</li>\n";
  echo '<li>' . user_link('thesaur') . "</li>\n";
  echo '<li>' . user_link('arnaud') . "</li>\n";
  echo '<li>' . user_link('toby') . "</li>\n";
  echo '<li>' . user_link('schst') . "</li>\n";
  echo '<li>' . user_link('davey') . "</li>\n";
  echo '<li>' . user_link('lsmith') . "</li>\n";

?>

</ul>

<p>If you are interested in helping out, or if you have questions 
concerning the <acronym title="Quality Assurance">QA</acronym> 
initiative, you can contact the team using the mailing list
<?php echo make_mailto_link('pear-qa@lists.php.net'); ?>
 (<a href="/support.php">subscription information</a>).</p>

<?php
response_footer();
?>
