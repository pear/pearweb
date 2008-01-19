--TEST--
PEAR_REST->saveReleaseREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require_once 'PEAR/PackageFile.php';
$config = PEAR_Config::singleton();
$pkg = &new PEAR_PackageFile($config);
$packagexml = dirname(__FILE__) . '/packages/package.xml';
$pf = $pkg->fromPackageFile($packagexml, PEAR_VALIDATE_DOWNLOADING);
$mock->addDataQuery("SELECT releasedate FROM releases WHERE id = 123", array(
    array(
        'releasedate' => '2007-05-22 23:35:00',
    )), array('releasedate'));

// ======TEST=========== //
// we'll use a dummy file for the .tgz
$rest->saveReleaseREST($packagexml, file_get_contents($packagexml), $pf, 'cellog', 123);
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/r/pearweb/1.15.2.xml', '1.15.2.xml');
$phpt->assertFileExists($rdir . '/r/pearweb/package.1.15.2.xml', 'package.1.15.2.xml');
$phpt->assertFileExists($rdir . '/r/pearweb/deps.1.15.2.txt', 'deps.1.15.2.txt');
$phpt->assertFileExists($rdir . '/r/pearweb/v2.1.15.2.xml', 'v2.1.15.2.xml');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0777, fileperms($rdir . '/r/pearweb/') & 0777, 'folder permissions');
    $phpt->assertEquals(0666, fileperms($rdir . '/r/pearweb/1.15.2.xml') & 0777, 'permissions 1.15.2.xml');
    $phpt->assertEquals(0666, fileperms($rdir . '/r/pearweb/package.1.15.2.xml') & 0777, 'permissions 1.15.2.xml');
    $phpt->assertEquals(0666, fileperms($rdir . '/r/pearweb/deps.1.15.2.txt') & 0777, 'permissions 1.15.2.xml');
    $phpt->assertEquals(0666, fileperms($rdir . '/r/pearweb/v2.1.15.2.xml') & 0777, 'permissions v2.1.15.2.xml');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pearweb">pearweb</p>
 <c>pear.php.net</c>
 <v>1.15.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>The source code for the PEAR website</s>
 <d>The pearweb package contains:
 - cron jobs for the website in cron/
 - off-public web files in include
 - public web files
 - the PEAR bug tracker public_html/bugs
 - the PEPr proposal system public_html/pepr
 - sql defining the database and an MDB2_Schema files
 - a few regression tests
 - templates

 See the pearweb_phars package for install-pear-nozlib.phar/go-pear.phar
 See the pearweb_channelxml package for channel.xml</d>
 <da>2007-05-22 23:35:00</da>
 <n>Add Security announcement
http://pear.php.net/advisory-20070507.txt
* Fix Bug #10856: proposal stays in voting phase [mj]
* Fix Bug #10885: Milestones not saved when submitting a bug [cellog]
* Fix Bug #10898: Broken link from /news [bjori]
* Fix Bug #10902: Unable to view elections (not logged in) [cellog]
* Fix Bug #10927: Typo in pearweb_mdb2schema.xml [wiesemann]
* Fix Bug #10928: Notices generated in bug tracker [cellog]
* Fix Bug #10929: Non-Dev Comments Cause Bug to Unassign Dev/Roadmap [cellog]
* Fix Bug #10932: Admin package maintainers error [davidc]
* Fix Bug #10933: Patch diff view shows warnings [cellog]
* Fix Bug #10948: Unclosed tags in bug tracker [wiesemann]
* Fix Bug #10950: Fix for #10908 removed &quot;CVS&quot; package version selection for new bugs [cellog]
* Fix Bug #10957: dufuz broke voter account registration [cellog]
* Fix Bug #10958: &quot;Old patches&quot; list does not UTC [mj]
* Fix Bug #10959: context is not htmlspecialchar()ed in patch diff [cellog]
* Fix Bug #10960: registration fails for voter [cellog]
* Implement Feature #8808: RSS feed for bug system [cellog]
* Implement Feature #10624: Generated package.xml mis-orders releases. [cellog]
* Added developer implemented feature using Net_URL and now is correctly using Net_URL2 [davidc]</n>
 <f>' . filesize($packagexml) . '</f>
 <g>http://pear.php.net/get/pearweb-1.15.2</g>
 <x xlink:href="package.1.15.2.xml"/>
</r>', file_get_contents($rdir . '/r/pearweb/1.15.2.xml'), 'contents');
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release2
    http://pear.php.net/dtd/rest.release2.xsd">
 <p xlink:href="/rest/p/pearweb">pearweb</p>
 <c>pear.php.net</c>
 <v>1.15.2</v>
 <a>0.1.1</a>
 <mp>4.3.0</mp>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>The source code for the PEAR website</s>
 <d>The pearweb package contains:
 - cron jobs for the website in cron/
 - off-public web files in include
 - public web files
 - the PEAR bug tracker public_html/bugs
 - the PEPr proposal system public_html/pepr
 - sql defining the database and an MDB2_Schema files
 - a few regression tests
 - templates

 See the pearweb_phars package for install-pear-nozlib.phar/go-pear.phar
 See the pearweb_channelxml package for channel.xml</d>
 <da>2007-05-22 23:35:00</da>
 <n>Add Security announcement
http://pear.php.net/advisory-20070507.txt
* Fix Bug #10856: proposal stays in voting phase [mj]
* Fix Bug #10885: Milestones not saved when submitting a bug [cellog]
* Fix Bug #10898: Broken link from /news [bjori]
* Fix Bug #10902: Unable to view elections (not logged in) [cellog]
* Fix Bug #10927: Typo in pearweb_mdb2schema.xml [wiesemann]
* Fix Bug #10928: Notices generated in bug tracker [cellog]
* Fix Bug #10929: Non-Dev Comments Cause Bug to Unassign Dev/Roadmap [cellog]
* Fix Bug #10932: Admin package maintainers error [davidc]
* Fix Bug #10933: Patch diff view shows warnings [cellog]
* Fix Bug #10948: Unclosed tags in bug tracker [wiesemann]
* Fix Bug #10950: Fix for #10908 removed &quot;CVS&quot; package version selection for new bugs [cellog]
* Fix Bug #10957: dufuz broke voter account registration [cellog]
* Fix Bug #10958: &quot;Old patches&quot; list does not UTC [mj]
* Fix Bug #10959: context is not htmlspecialchar()ed in patch diff [cellog]
* Fix Bug #10960: registration fails for voter [cellog]
* Implement Feature #8808: RSS feed for bug system [cellog]
* Implement Feature #10624: Generated package.xml mis-orders releases. [cellog]
* Added developer implemented feature using Net_URL and now is correctly using Net_URL2 [davidc]</n>
 <f>' . filesize($packagexml) . '</f>
 <g>http://pear.php.net/get/pearweb-1.15.2</g>
 <x xlink:href="package.1.15.2.xml"/>
</r>', file_get_contents($rdir . '/r/pearweb/v2.1.15.2.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===