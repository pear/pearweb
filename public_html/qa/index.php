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

<p>The current membery of the
 <acronym title="Quality Assurance">QA</acronym> Team are:
</p>

<ul>
<?php
  echo ' <li>' . user_link('gurugeek', true) . "</li>\n";
  echo ' <li>' . user_link('thesaur', true) . "</li>\n";
  echo ' <li>' . user_link('arnaud', true) . "</li>\n";
  echo ' <li>' . user_link('toby', true) . "</li>\n";
  echo ' <li>' . user_link('schst', true) . "</li>\n";
  echo ' <li>' . user_link('davey', true) . "</li>\n";
  echo ' <li>' . user_link('kguest', true) . "</li>\n";
  echo ' <li>' . user_link('dufuz', true) . "</li>\n";
?>
</ul>

<p>If you are interested in helping out, or if you have questions
concerning the <acronym title="Quality Assurance">QA</acronym>
initiative, you can contact the team using the mailing list
<a href="<?php echo PEAR_QA_EMAIL; ?>"><?php echo PEAR_QA_EMAIL; ?></a>
 (<a href="/support/lists.php">subscription information</a>).</p>

<?php
if ($auth_user) {
    if (auth_check('pear.dev')) {
        $str = <<<EOD
<h2>Related Tools: </h2>
<ul>
 <li><a href="/qa/packages_orphan.php">List of orphan packages</a></li>
 <li><a href="http://pear.cweiske.de/">PEAR's QA test suite</a></li>
</ul>
EOD;
        echo $str;
    }
}

response_footer();