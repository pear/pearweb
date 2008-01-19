--TEST--
PEAR_REST->savePackagesCategoryREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require dirname(__FILE__) . '/setup_test.php.inc';
$pear_rest->savePackagesCategoryREST('Halb');
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/c/Halb/packagesinfo.xml', 'pc');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0666, fileperms($rdir . '/c/Halb/') & 0777, 'folder permissions');
    $phpt->assertEquals(0666, fileperms($rdir . '/c/Halb/packagesinfo.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<f xmlns="http://pear.php.net/dtd/rest.categorypackageinfo"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackageinfo
    http://pear.php.net/dtd/rest.categorypackageinfo.xsd">
<pi>
<p>
 <n>Blah1</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/rename">rename</ca>
 <l>BSD License</l>
 <s>Blah1</s>
 <d>Hi Blah1</d>
 <r xlink:href="/rest/r/blah1"/>
</p>
<a>
 <r><v>1.0.0</v><s>stable</s></r>
</a>
<deps>
 <v>1.0.0</v>
 <d>a:3:{i:0;a:4:{s:4:&quot;type&quot;;s:3:&quot;php&quot;;s:3:&quot;rel&quot;;s:2:&quot;ge&quot;;s:7:&quot;version&quot;;s:5:&quot;5.2.2&quot;;s:8:&quot;optional&quot;;s:2:&quot;no&quot;;}i:1;a:5:{s:4:&quot;type&quot;;s:3:&quot;pkg&quot;;s:3:&quot;rel&quot;;s:2:&quot;ge&quot;;s:7:&quot;version&quot;;s:5:&quot;1.0.0&quot;;s:8:&quot;optional&quot;;s:3:&quot;yes&quot;;s:4:&quot;name&quot;;s:5:&quot;Blah2&quot;;}i:2;a:5:{s:4:&quot;type&quot;;s:3:&quot;pkg&quot;;s:3:&quot;rel&quot;;s:2:&quot;ge&quot;;s:7:&quot;version&quot;;s:5:&quot;1.0.0&quot;;s:8:&quot;optional&quot;;s:2:&quot;no&quot;;s:4:&quot;name&quot;;s:5:&quot;Blah3&quot;;}}</d>
</deps>
</pi>
<pi>
<p>
 <n>Blah2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/rename">rename</ca>
 <l>BSD License</l>
 <s>Blah2</s>
 <d>Hi Blah2</d>
 <r xlink:href="/rest/r/blah2"/>
</p>
<a>
 <r><v>1.0.0</v><s>stable</s></r>
</a>
<deps>
 <v>1.0.0</v>
 <d>b:0;</d>
</deps>
</pi>
</f>', file_get_contents($rdir . '/c/Halb/packagesinfo.xml'),
    'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===