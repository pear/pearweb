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

require_once 'HTML/Table.php';
response_header('Documentation');
?>

<div class="manual-content" id="manual-content">

 <h1>Documentation</h1>

 <p class="para">
  The PEAR documentation is a centralized place where developers can
  add the documentation for their package.
 </p>

 <p class="para">
  Currently the documentation is available in the following languages:
 </p>

 <ul class="itemizedlist">
<?php
echo "  <li class=\"listitem\">";
echo '<strong>' . make_link('/manual/en/', 'English') . '</strong>';
echo "</li>\n";
?>
 </ul>


 <h2>Download Documentation</h2>

 <p class="para">
  If you prefer to have an offline version of the documentation, you can
  download it in a variety of formats.
 </p>

 <blockquote class="note">
  <p class="para"><strong>Note to Windows users</strong>: If you are using
   Microsoft Internet Explorer under Windows XP SP2 or
   later and you are going to download the documentation in CHM
   format, you should "unblock" the file after downloading it, by
   right-clicking on it and selecting the properties menu item. Then click
   on the 'Unblock' button. Failing to do this may lead to errors
   in the visualization of the file, due to a Microsoft bug.
  </p>
 </blockquote>

<?php
$formats = array(
    // "pear_manual_{LANG}.chm"         => array('HTML Help file',      'chm'),
    "pear_manual_{LANG}.tar.bz2"     => array('Many HTML files',     'tar.bz2'),
    "pear_manual_{LANG}.tar.gz"      => array('Many HTML files',     'tar.gz'),
    "pear_manual_{LANG}.zip"         => array('Many HTML files',     'zip'),
    "pear_manual_{LANG}.html.gz"     => array('One big HTML file',   'html.gz'),
    "pear_manual_{LANG}.html.zip"    => array('One big HTML file',   'html.zip'),
    "pear_manual_{LANG}.html.bz2"    => array('One big HTML file',   'html.bz2'),
);

$table = new HTML_Table('class="informaltable"');

$table->addRow(array('Type', 'Format'), '', 'th');
$doc_languages = array('en' => 'English');
foreach ($doc_languages as $domain => $name) {
    $language = $name;
    $table->addRow(array($language), 'colspan="2"', 'th');

    foreach ($formats as $filename => $information) {
        $filename = str_replace("{LANG}", $domain, $filename);

        $information[0] = make_link(
            '/distributions/manual/' . $filename,
            $information[0]
        );
        $table->addRow($information);
    }
}
echo $table->toHTML();
?>

</div>

<?php

response_footer();
