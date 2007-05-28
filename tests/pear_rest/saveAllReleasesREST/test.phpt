--TEST--
PEAR_REST->saveAllReleasesREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
require dirname(__FILE__) . '/test.phpt.setup.inc';
// ======TEST=========== //
$rest->saveAllReleasesREST('PEAR');
$phpunit->assertNoErrors('after');
$phpunit->assertFileExists($rdir . '/r/pear/latest.txt', 'latest.txt');
$phpunit->assertFileExists($rdir . '/r/pear/stable.txt', 'stable.txt');
$phpunit->assertFileExists($rdir . '/r/pear/beta.txt', 'beta.txt');
$phpunit->assertFileExists($rdir . '/r/pear/alpha.txt', 'alpha.txt');
$phpunit->assertFileExists($rdir . '/r/pear/allreleases.xml', 'allreleases.xml');
$phpunit->assertFileExists($rdir . '/r/pear/allreleases2.xml', 'allreleases2.xml');
if (!OS_WINDOWS) {
    $phpunit->assertEquals(0666, fileperms($rdir . '/r/pear/latest.txt') & 0777, 'permissions latest');
    $phpunit->assertEquals(0666, fileperms($rdir . '/r/pear/stable.txt') & 0777, 'permissions stable');
    $phpunit->assertEquals(0666, fileperms($rdir . '/r/pear/beta.txt') & 0777, 'permissions beta');
    $phpunit->assertEquals(0666, fileperms($rdir . '/r/pear/alpha.txt') & 0777, 'permissions alpha');
    $phpunit->assertEquals(0666, fileperms($rdir . '/r/pear/allreleases.xml') & 0777, 'permissions allreleases');
    $phpunit->assertEquals(0666, fileperms($rdir . '/r/pear/allreleases2.xml') & 0777, 'permissions allreleases2');
}
$phpunit->assertEquals('1.5.1', file_get_contents($rdir . '/r/pear/latest.txt'), 'latest contents');
$phpunit->assertEquals('1.5.1', file_get_contents($rdir . '/r/pear/stable.txt'), 'stable contents');
$phpunit->assertEquals('1.5.0RC3', file_get_contents($rdir . '/r/pear/beta.txt'), 'beta contents');
$phpunit->assertEquals('1.5.0a1', file_get_contents($rdir . '/r/pear/alpha.txt'), 'alpha contents');
$phpunit->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>stable</s></r>
 <r><v>1.5.0RC3</v><s>beta</s></r>
 <r><v>1.5.0RC2</v><s>beta</s></r>
 <r><v>1.5.0RC1</v><s>beta</s></r>
 <r><v>1.5.0a1</v><s>alpha</s></r>
 <r><v>1.4.11</v><s>stable</s></r>
 <r><v>1.4.10</v><s>stable</s></r>
 <r><v>1.4.10RC1</v><s>beta</s></r>
 <r><v>1.4.9</v><s>stable</s></r>
 <r><v>1.4.8</v><s>stable</s></r>
 <r><v>1.4.7</v><s>stable</s></r>
 <r><v>1.4.6</v><s>stable</s></r>
 <r><v>1.4.5</v><s>stable</s></r>
 <r><v>1.4.4</v><s>stable</s></r>
 <r><v>1.4.3</v><s>stable</s></r>
 <r><v>1.4.2</v><s>stable</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4.0RC2</v><s>beta</s></r>
 <r><v>1.4.0RC1</v><s>beta</s></r>
 <r><v>1.4.0b2</v><s>beta</s></r>
 <r><v>1.4.0b1</v><s>beta</s></r>
 <r><v>1.3.6</v><s>stable</s></r>
 <r><v>1.4.0a12</v><s>alpha</s></r>
 <r><v>1.4.0a11</v><s>alpha</s></r>
 <r><v>1.4.0a10</v><s>alpha</s></r>
 <r><v>1.4.0a9</v><s>alpha</s></r>
 <r><v>1.4.0a8</v><s>alpha</s></r>
 <r><v>1.4.0a7</v><s>alpha</s></r>
 <r><v>1.4.0a6</v><s>alpha</s></r>
 <r><v>1.4.0a5</v><s>alpha</s></r>
 <r><v>1.4.0a4</v><s>alpha</s></r>
 <r><v>1.4.0a3</v><s>alpha</s></r>
 <r><v>1.4.0a2</v><s>alpha</s></r>
 <r><v>1.4.0a1</v><s>alpha</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
 <r><v>1.3.4</v><s>stable</s></r>
 <r><v>1.3.3.1</v><s>stable</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.3b6</v><s>beta</s></r>
 <r><v>1.3b5</v><s>beta</s></r>
 <r><v>1.3b3</v><s>beta</s></r>
 <r><v>1.3b2</v><s>beta</s></r>
 <r><v>1.3b1</v><s>beta</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.2b5</v><s>beta</s></r>
 <r><v>1.2b4</v><s>beta</s></r>
 <r><v>1.2b3</v><s>beta</s></r>
 <r><v>1.2b2</v><s>beta</s></r>
 <r><v>1.2b1</v><s>beta</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0b3</v><s>stable</s></r>
 <r><v>1.0b2</v><s>stable</s></r>
 <r><v>1.0b1</v><s>stable</s></r>
 <r><v>0.90</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', file_get_contents($rdir . '/r/pear/allreleases.xml'), 'contents');
$phpunit->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases2
    http://pear.php.net/dtd/rest.allreleases2.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <r><v>1.5.1</v><s>stable</s><m>4.3.0</m></r>
 <r><v>1.5.0</v><s>stable</s><m>4.3.0</m></r>
 <r><v>1.5.0RC3</v><s>beta</s><m>4.3.0</m></r>
 <r><v>1.5.0RC2</v><s>beta</s><m>4.3.0</m></r>
 <r><v>1.5.0RC1</v><s>beta</s><m>4.3.0</m></r>
 <r><v>1.5.0a1</v><s>alpha</s><m>4.3.0</m></r>
 <r><v>1.4.11</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.10</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.10RC1</v><s>beta</s><m>4.2</m></r>
 <r><v>1.4.9</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.8</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.7</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.6</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.5</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.4</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.3</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.2</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.1</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.0</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.0RC2</v><s>beta</s><m>4.2</m></r>
 <r><v>1.4.0RC1</v><s>beta</s><m>4.2</m></r>
 <r><v>1.4.0b2</v><s>beta</s><m>4.2</m></r>
 <r><v>1.4.0b1</v><s>beta</s><m>4.2</m></r>
 <r><v>1.3.6</v><s>stable</s><m>4.2</m></r>
 <r><v>1.4.0a12</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a11</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a10</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a9</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a8</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a7</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a6</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a5</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a4</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a3</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a2</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.4.0a1</v><s>alpha</s><m>4.2</m></r>
 <r><v>1.3.5</v><s>stable</s><m>4.2</m></r>
 <r><v>1.3.4</v><s>stable</s><m>4.2</m></r>
 <r><v>1.3.3.1</v><s>stable</s><m>4.2</m></r>
 <r><v>1.3.3</v><s>stable</s><m>4.2</m></r>
 <r><v>1.3.1</v><s>stable</s><m>4.2</m></r>
 <r><v>1.3</v><s>stable</s><m>4.1</m></r>
 <r><v>1.3b6</v><s>beta</s><m>4.1</m></r>
 <r><v>1.3b5</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.3b3</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.3b2</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.3b1</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.2.1</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.2</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.2b5</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.2b4</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.2b3</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.2b2</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.2b1</v><s>beta</s><m>4.0.0</m></r>
 <r><v>1.1</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.0.1</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.0</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.0b3</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.0b2</v><s>stable</s><m>4.0.0</m></r>
 <r><v>1.0b1</v><s>stable</s><m>4.0.0</m></r>
 <r><v>0.90</v><s>beta</s><m>4.0.0</m></r>
 <r><v>0.11</v><s>beta</s><m>4.0.0</m></r>
 <r><v>0.10</v><s>beta</s><m>4.0.0</m></r>
 <r><v>0.9</v><s>stable</s><m>4.0.0</m></r>
</a>', file_get_contents($rdir . '/r/pear/allreleases2.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===