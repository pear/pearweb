--TEST--
/get/Archive_Tar-1.3.2.tgz
--FILE--
<?php
// setup
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['PHP_SELF'] = 'get';
$_SERVER['REQUEST_URI'] = '/get/Archive_Tar-1.3.2.tgz';
$_SERVER['QUERY_STRING'] = '';
$_SERVER['PATH_INFO'] = '/Archive_Tar-1.3.2.tgz';
require dirname(__FILE__) . '/setup.php.inc';
require dirname(__FILE__) . '/test_opt.setup.php.inc';
include dirname(dirname(dirname(dirname(__FILE__)))) . '/public_html/get';
$phpt->assertEquals(array (
  0 => 'SELECT id FROM packages p WHERE ((p.package_type = \'pear\' AND p.approved = 1) OR p.package_type = \'pecl\') AND  p.name = \'Archive_Tar\'',
  1 => 'SELECT fullpath, `release`, id FROM files WHERE UPPER(basename) = \'ARCHIVE_TAR-1.3.2.TGZ\'',
  2 => 'UPDATE aggregated_package_stats
            SET downloads=downloads+1
            WHERE
                package_id=1 AND
                release_id=1 AND
                yearmonth="' . date('Y-m-01') . '"',
  3 => 'UPDATE package_stats  SET dl_number = dl_number + 1, last_dl = \'2007-06-18 21:35:34\' WHERE pid = 1 AND rid = 1',
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
