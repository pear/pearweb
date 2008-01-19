--TEST--
PEAR_REST->saveAllCategoriesREST()
--FILE--
<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
$mock->addDataQuery("SELECT * FROM categories ORDER BY name", array (
  0 =>
  array (
    'id' => '47',
    'parent' => '46',
    'name' => 'Audio',
    'summary' => NULL,
    'description' => 'Audio',
    'npackages' => '1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '152',
    'cat_right' => '153',
  ),
  1 =>
  array (
    'id' => '1',
    'parent' => NULL,
    'name' => 'Authentication',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '10',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '175',
    'cat_right' => '176',
  ),
  2 =>
  array (
    'id' => '2',
    'parent' => NULL,
    'name' => 'Benchmarking',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '2',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '3',
    'cat_right' => '4',
  ),
  3 =>
  array (
    'id' => '3',
    'parent' => NULL,
    'name' => 'Caching',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '4',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '5',
    'cat_right' => '6',
  ),
  4 =>
  array (
    'id' => '4',
    'parent' => NULL,
    'name' => 'Configuration',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '7',
    'cat_right' => '8',
  ),
  5 =>
  array (
    'id' => '5',
    'parent' => NULL,
    'name' => 'Console',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '14',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '9',
    'cat_right' => '10',
  ),
  6 =>
  array (
    'id' => '7',
    'parent' => NULL,
    'name' => 'Database',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '59',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '13',
    'cat_right' => '14',
  ),
  7 =>
  array (
    'id' => '8',
    'parent' => NULL,
    'name' => 'Date and Time',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '5',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '15',
    'cat_right' => '16',
  ),
  8 =>
  array (
    'id' => '6',
    'parent' => NULL,
    'name' => 'Encryption',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '13',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '11',
    'cat_right' => '12',
  ),
  9 =>
  array (
    'id' => '44',
    'parent' => NULL,
    'name' => 'Event',
    'summary' => NULL,
    'description' => 'Event message passing',
    'npackages' => '2',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '141',
    'cat_right' => '142',
  ),
  10 =>
  array (
    'id' => '33',
    'parent' => NULL,
    'name' => 'File Formats',
    'summary' => NULL,
    'description' => 'This category holds all sorts of packages reading/writing files of a certain format.',
    'npackages' => '29',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '63',
    'cat_right' => '64',
  ),
  11 =>
  array (
    'id' => '9',
    'parent' => NULL,
    'name' => 'File System',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '11',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '17',
    'cat_right' => '18',
  ),
  12 =>
  array (
    'id' => '34',
    'parent' => NULL,
    'name' => 'Gtk Components',
    'summary' => NULL,
    'description' => 'Graphical components for php-gtk',
    'npackages' => '5',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '65',
    'cat_right' => '66',
  ),
  13 =>
  array (
    'id' => '53',
    'parent' => NULL,
    'name' => 'Gtk2 Components',
    'summary' => NULL,
    'description' => 'Gtk2 Components',
    'npackages' => '7',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '177',
    'cat_right' => '178',
  ),
  14 =>
  array (
    'id' => '45',
    'parent' => NULL,
    'name' => 'GUI',
    'summary' => NULL,
    'description' => 'Graphic User Interface',
    'npackages' => '0',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '149',
    'cat_right' => '150',
  ),
  15 =>
  array (
    'id' => '10',
    'parent' => NULL,
    'name' => 'HTML',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '41',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '19',
    'cat_right' => '20',
  ),
  16 =>
  array (
    'id' => '11',
    'parent' => NULL,
    'name' => 'HTTP',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '15',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '21',
    'cat_right' => '22',
  ),
  17 =>
  array (
    'id' => '12',
    'parent' => NULL,
    'name' => 'Images',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '25',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '23',
    'cat_right' => '24',
  ),
  18 =>
  array (
    'id' => '28',
    'parent' => NULL,
    'name' => 'Internationalization',
    'summary' => NULL,
    'description' => 'I18N related packages',
    'npackages' => '7',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '51',
    'cat_right' => '52',
  ),
  19 =>
  array (
    'id' => '13',
    'parent' => NULL,
    'name' => 'Logging',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '25',
    'cat_right' => '26',
  ),
  20 =>
  array (
    'id' => '14',
    'parent' => NULL,
    'name' => 'Mail',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '11',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '27',
    'cat_right' => '28',
  ),
  21 =>
  array (
    'id' => '15',
    'parent' => NULL,
    'name' => 'Math',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '22',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '29',
    'cat_right' => '30',
  ),
  22 =>
  array (
    'id' => '46',
    'parent' => NULL,
    'name' => 'Multimedia',
    'summary' => NULL,
    'description' => 'Rich media manipulation',
    'npackages' => '0',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '151',
    'cat_right' => '154',
  ),
  23 =>
  array (
    'id' => '16',
    'parent' => NULL,
    'name' => 'Networking',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '68',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '31',
    'cat_right' => '32',
  ),
  24 =>
  array (
    'id' => '17',
    'parent' => NULL,
    'name' => 'Numbers',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '2',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '33',
    'cat_right' => '34',
  ),
  25 =>
  array (
    'id' => '18',
    'parent' => NULL,
    'name' => 'Payment',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '9',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '35',
    'cat_right' => '36',
  ),
  26 =>
  array (
    'id' => '19',
    'parent' => NULL,
    'name' => 'PEAR',
    'summary' => NULL,
    'description' => 'PEAR infrastructure',
    'npackages' => '17',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '37',
    'cat_right' => '38',
  ),
  27 =>
  array (
    'id' => '55',
    'parent' => NULL,
    'name' => 'PEAR Website',
    'summary' => NULL,
    'description' => 'web site infrastructure',
    'npackages' => '2',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '181',
    'cat_right' => '184',
  ),
  28 =>
  array (
    'id' => '25',
    'parent' => NULL,
    'name' => 'PHP',
    'summary' => NULL,
    'description' => 'Classes related to the PHP language itself',
    'npackages' => '42',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '47',
    'cat_right' => '48',
  ),
  29 =>
  array (
    'id' => '31',
    'parent' => NULL,
    'name' => 'Processing',
    'summary' => NULL,
    'description' => 'Foo',
    'npackages' => '2',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '61',
    'cat_right' => '62',
  ),
  30 =>
  array (
    'id' => '56',
    'parent' => '55',
    'name' => 'QA Tools',
    'summary' => NULL,
    'description' => 'Infrastructure QA related packages',
    'npackages' => '1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '182',
    'cat_right' => '183',
  ),
  31 =>
  array (
    'id' => '20',
    'parent' => NULL,
    'name' => 'Scheduling',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '0',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '39',
    'cat_right' => '40',
  ),
  32 =>
  array (
    'id' => '21',
    'parent' => NULL,
    'name' => 'Science',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '41',
    'cat_right' => '42',
  ),
  33 =>
  array (
    'id' => '54',
    'parent' => NULL,
    'name' => 'Security',
    'summary' => NULL,
    'description' => 'Security related packages',
    'npackages' => '0',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '179',
    'cat_right' => '180',
  ),
  34 =>
  array (
    'id' => '42',
    'parent' => NULL,
    'name' => 'Semantic Web',
    'summary' => NULL,
    'description' => 'The Semantic Web provides a common framework that allows data to be shared and reused across application, enterprise, and community boundaries',
    'npackages' => '3',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '111',
    'cat_right' => '112',
  ),
  35 =>
  array (
    'id' => '35',
    'parent' => NULL,
    'name' => 'Streams',
    'summary' => NULL,
    'description' => 'PHP streams implementations and utilities',
    'npackages' => '6',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '67',
    'cat_right' => '68',
  ),
  36 =>
  array (
    'id' => '27',
    'parent' => NULL,
    'name' => 'Structures',
    'summary' => NULL,
    'description' => 'Structures and advanced data types',
    'npackages' => '31',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '49',
    'cat_right' => '50',
  ),
  37 =>
  array (
    'id' => '37',
    'parent' => NULL,
    'name' => 'System',
    'summary' => NULL,
    'description' => 'System Utilities',
    'npackages' => '15',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '99',
    'cat_right' => '100',
  ),
  38 =>
  array (
    'id' => '43',
    'parent' => '29',
    'name' => 'Testing',
    'summary' => NULL,
    'description' => 'Packages for creating test suites',
    'npackages' => '-1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '58',
    'cat_right' => '59',
  ),
  39 =>
  array (
    'id' => '36',
    'parent' => NULL,
    'name' => 'Text',
    'summary' => NULL,
    'description' => 'Creating and manipulating text.',
    'npackages' => '29',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '69',
    'cat_right' => '70',
  ),
  40 =>
  array (
    'id' => '29',
    'parent' => NULL,
    'name' => 'Tools and Utilities',
    'summary' => NULL,
    'description' => 'Tools and Utilities for PHP or written in PHP',
    'npackages' => '23',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '53',
    'cat_right' => '60',
  ),
  41 =>
  array (
    'id' => '50',
    'parent' => NULL,
    'name' => 'Validate',
    'summary' => NULL,
    'description' => 'Data validation',
    'npackages' => '25',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '161',
    'cat_right' => '162',
  ),
  42 =>
  array (
    'id' => '40',
    'parent' => '29',
    'name' => 'Version Control',
    'summary' => NULL,
    'description' => 'Packages that allow access to version control systems such as CVS or Subversion',
    'npackages' => '1',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '56',
    'cat_right' => '57',
  ),
  43 =>
  array (
    'id' => '23',
    'parent' => NULL,
    'name' => 'Web Services',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '23',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '45',
    'cat_right' => '46',
  ),
  44 =>
  array (
    'id' => '22',
    'parent' => NULL,
    'name' => 'XML',
    'summary' => NULL,
    'description' => 'none',
    'npackages' => '38',
    'pkg_left' => NULL,
    'pkg_right' => NULL,
    'cat_left' => '43',
    'cat_right' => '44',
  ),
), array('id', 'parent', 'name', 'summary', 'description', 'npackages', 'pkg_left', 'pkg_right',
    'cat_left', 'cat_right'));

// ===== test ======
$rest->saveAllCategoriesREST();
$phpt->assertNoErrors('after');
$phpt->assertFileExists($rdir . '/c/categories.xml', 'info');
if (!OS_WINDOWS) {
    $phpt->assertEquals(0777, fileperms($rdir . '/c/') & 0777, 'folder permissions');
    $phpt->assertEquals(0666, fileperms($rdir . '/c/categories.xml') & 0777, 'permissions');
}
$phpt->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allcategories"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allcategories
    http://pear.php.net/dtd/rest.allcategories.xsd">
<ch>pear.php.net</ch>
 <c xlink:href="/rest/c/Audio/info.xml">Audio</c>
 <c xlink:href="/rest/c/Authentication/info.xml">Authentication</c>
 <c xlink:href="/rest/c/Benchmarking/info.xml">Benchmarking</c>
 <c xlink:href="/rest/c/Caching/info.xml">Caching</c>
 <c xlink:href="/rest/c/Configuration/info.xml">Configuration</c>
 <c xlink:href="/rest/c/Console/info.xml">Console</c>
 <c xlink:href="/rest/c/Database/info.xml">Database</c>
 <c xlink:href="/rest/c/Date%2Band%2BTime/info.xml">Date and Time</c>
 <c xlink:href="/rest/c/Encryption/info.xml">Encryption</c>
 <c xlink:href="/rest/c/Event/info.xml">Event</c>
 <c xlink:href="/rest/c/File%2BFormats/info.xml">File Formats</c>
 <c xlink:href="/rest/c/File%2BSystem/info.xml">File System</c>
 <c xlink:href="/rest/c/Gtk%2BComponents/info.xml">Gtk Components</c>
 <c xlink:href="/rest/c/Gtk2%2BComponents/info.xml">Gtk2 Components</c>
 <c xlink:href="/rest/c/GUI/info.xml">GUI</c>
 <c xlink:href="/rest/c/HTML/info.xml">HTML</c>
 <c xlink:href="/rest/c/HTTP/info.xml">HTTP</c>
 <c xlink:href="/rest/c/Images/info.xml">Images</c>
 <c xlink:href="/rest/c/Internationalization/info.xml">Internationalization</c>
 <c xlink:href="/rest/c/Logging/info.xml">Logging</c>
 <c xlink:href="/rest/c/Mail/info.xml">Mail</c>
 <c xlink:href="/rest/c/Math/info.xml">Math</c>
 <c xlink:href="/rest/c/Multimedia/info.xml">Multimedia</c>
 <c xlink:href="/rest/c/Networking/info.xml">Networking</c>
 <c xlink:href="/rest/c/Numbers/info.xml">Numbers</c>
 <c xlink:href="/rest/c/Payment/info.xml">Payment</c>
 <c xlink:href="/rest/c/PEAR/info.xml">PEAR</c>
 <c xlink:href="/rest/c/PEAR%2BWebsite/info.xml">PEAR Website</c>
 <c xlink:href="/rest/c/PHP/info.xml">PHP</c>
 <c xlink:href="/rest/c/Processing/info.xml">Processing</c>
 <c xlink:href="/rest/c/QA%2BTools/info.xml">QA Tools</c>
 <c xlink:href="/rest/c/Scheduling/info.xml">Scheduling</c>
 <c xlink:href="/rest/c/Science/info.xml">Science</c>
 <c xlink:href="/rest/c/Security/info.xml">Security</c>
 <c xlink:href="/rest/c/Semantic%2BWeb/info.xml">Semantic Web</c>
 <c xlink:href="/rest/c/Streams/info.xml">Streams</c>
 <c xlink:href="/rest/c/Structures/info.xml">Structures</c>
 <c xlink:href="/rest/c/System/info.xml">System</c>
 <c xlink:href="/rest/c/Testing/info.xml">Testing</c>
 <c xlink:href="/rest/c/Text/info.xml">Text</c>
 <c xlink:href="/rest/c/Tools%2Band%2BUtilities/info.xml">Tools and Utilities</c>
 <c xlink:href="/rest/c/Validate/info.xml">Validate</c>
 <c xlink:href="/rest/c/Version%2BControl/info.xml">Version Control</c>
 <c xlink:href="/rest/c/Web%2BServices/info.xml">Web Services</c>
 <c xlink:href="/rest/c/XML/info.xml">XML</c>
</a>', file_get_contents($rdir . '/c/categories.xml'), 'contents');
?>
===DONE===
--CLEAN--
<?php require dirname(dirname(__FILE__)) . '/teardown.php.inc'; ?>
--EXPECT--
===DONE===