--TEST--
/get/Archive_Tar-1.3.2
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'get';
$_SERVER['REQUEST_URI'] = '/get/Archive_Tar-1.3.2';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['PATH_INFO'] = '/Archive_Tar-1.3.2';
require dirname(__FILE__) . '/setup.php.inc';
require dirname(__FILE__) . '/test_opt.setup4.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/get';
$phpt->assertEquals(array (
  0 => 'SELECT id FROM packages p WHERE ((p.package_type = \'pear\' AND p.approved = 1) OR p.package_type = \'pecl\') AND  p.name = \'Archive_Tar\'',
  1 => 'SELECT id FROM releases WHERE package = 1 AND version = \'1.3.2\'',
  2 => 'SELECT fullpath, basename, id FROM files WHERE release = 1',
  3 => 'UPDATE aggregated_package_stats
            SET downloads=downloads+1
            WHERE
                package_id=1 AND
                release_id=1 AND
                yearmonth="' . date('Y-m-01') . '"',
  4 => 'UPDATE package_stats  SET dl_number = dl_number + 1, last_dl = \'2007-06-18 21:35:34\' WHERE pid = 1 AND rid = 1',
), $mock->queries, 'queries');
__halt_compiler();
?>
===DONE===
--EXPECTHEADERS--
Content-type: application/octet-stream
Content-disposition: attachment; filename="Archive_Tar-1.3.2.tgz"
Content-length: 17150
--EXPECTFILE--
packages/Archive_Tar-1.3.2.tgz
