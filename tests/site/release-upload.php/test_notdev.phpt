--TEST--
release-upload.php [not pear.dev]
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
$moresetup = dirname(__FILE__) . '/test_notdev.php.inc';
require dirname(__FILE__) . '/setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/release-upload.php';
$phpt->assertEquals(array (
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTF--
<?xml version="1.0" encoding="ISO-8859-15" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PEAR :: Insufficient Privileges</title>
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

    <div class="errors">ERROR:<ul><li>Insufficient Privileges</li>
</ul></div>

  </td>

<!-- END MAIN CONTENT -->

    
 </tr>
</table>

<!-- END MIDDLE -->
<!-- START FOOTER -->

%s
</html>