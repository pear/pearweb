--TEST--
release-upload.php [pear.dev, is lead]
--COOKIE--
PEAR_USER=cellog;PEAR_PW=hi
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'hithere';
$_SERVER['REQUEST_URI'] = '/release-upload.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = '';
$_COOKIE['PEAR_USER'] = 'cellog';
$_COOKIE['PEAR_PW'] = 'hi';
$moresetup = dirname(__FILE__) . '/test_devlead.php.inc';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/release-upload.php';
$phpt->assertEquals(array (
  0 => 'SELECT * FROM users WHERE handle = \'cellog\' AND registered = \'1\'',
  1 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  2 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.user\',\'pear.pepr\',\'pear.dev\',\'pear.admin\',\'pear.group\',\'pear.voter\',\'pear.bug\')',
  3 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  4 => 'SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = \'cellog\' AND
                 p.id = m.package AND
                 m.role = \'lead\'',
  5 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.dev\',\'pear.admin\',\'pear.group\')',
  6 => 'SELECT * FROM karma WHERE user = \'cellog\' AND level IN (\'pear.admin\',\'pear.group\')',
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
<?xml version="1.0" encoding="ISO-8859-15" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PEAR :: Upload New Release</title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" href="/css/style.css" />
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://localhost/feeds/latest.rss" />
</head>

<body>
<div>
<a id="TOP"></a>
</div>

<!-- START HEADER -->

%s
<!-- END HEADER -->
<!-- START MIDDLE -->

%s<!-- END LEFT SIDEBAR -->

        
<!-- START MAIN CONTENT -->

  <td class="content">

    <h1>Upload New Release</h1>
<p>
Upload a new package distribution file built using &quot;<code>pear
package</code>&quot; here.  The information from your package.xml file will
be displayed on the next screen for verification. The maximum file size
is 16 MB.
</p>

<p>
Uploading new releases is restricted to each package's lead developer(s).
</p><form action="release-upload.php" method="post" enctype="multipart/form-data" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Upload
 </caption>
 <tr>
  <th class="form-label_left"><label for="f" accesskey="i">D<span class="accesskey">i</span>stribution File</label></th>
  <td class="form-input">
   <input type="hidden" name="MAX_FILE_SIZE" value="16777216" />
   <input type="file" name="distfile" size="40" id="f"/>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="upload" value="Upload!" />
  </td>
 </tr>
</table>
<input type="hidden" name="_fields" value="distfile:upload" />
</form>


  </td>

<!-- END MAIN CONTENT -->

    
 </tr>
</table>

<!-- END MIDDLE -->
<!-- START FOOTER -->

%s
</html>