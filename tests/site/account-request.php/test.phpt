--TEST--
account-request.php |  Matching layout
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = '/account-request.php';
$_SERVER['REQUEST_URI'] = null;
$_SERVER['QUERY_STRING'] = '';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/account-request.php';
$phpt->assertEquals(array (
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
%s
 <title>PEAR :: Request Account</title>
%s
<!-- START MAIN CONTENT -->

  <td class="content">

    <h1>Request Account</h1><h1>PLEASE READ THIS CAREFULLY!</h1>
<h3>
 You only need to request an account if you:
</h3>

<ul>
 <li>
  <a href="/account-request-newpackage.php">Want to propose a new (and <strong>complete</strong>) package for inclusion in PEAR.</a>
 </li>
 <li>
  <a href="/account-request-existingpackage.php">Will be helping develop an existing package.</a>  Seek approval first for this by mailing
  the <a  href="&#x6d;&#97;&#x69;&#108;&#x74;&#111;&#x3a;&#x70;&#101;&#x61;&#114;&#x2d;&#100;&#x65;&#118;&#x40;&#108;&#x69;&#115;&#x74;&#115;&#x2e;&#112;&#x68;&#112;&#x2e;&#110;&#x65;&#116;">PEAR developers mailing list</a> and developers of the package.
 </li>
 <li>
  <a href="/account-request-vote.php">Want to vote in a general PEAR election or
  report bugs/comment on bugs.</a>
 </li>
 <li>
  <a href="/bugs/">Want to report a bug, or comment on an existing bug.</a>
  (You can create an account automatically by choosing a username/password on the bug
  report or edit page)
 </li>
</ul>

<p>
 If the reason for your request does not fall under one of the
 reasons above, please contact the <a  href="&#x6d;&#97;&#x69;&#108;&#x74;&#111;&#x3a;&#x70;&#101;&#x61;&#114;&#x2d;&#100;&#x65;&#118;&#x40;&#108;&#x69;&#115;&#x74;&#115;&#x2e;&#112;&#x68;&#112;&#x2e;&#110;&#x65;&#116;">PEAR developers mailing list</a>;
</p>

<h3>
 You do <strong>not</strong> need an account to:
</h3>

<ul>
 <li>
  Use PEAR packages.
 </li>
 <li>
  Browse the PEAR website.
 </li>
 <li>
  Download PEAR packages.
 </li>
 <li>
  Express an idea for a PEAR package.  Only completed code can be proposed.
 </li>
</ul>
  </td>

<!-- END MAIN CONTENT -->
%s