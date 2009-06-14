<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(
    dirname(__FILE__) . '/package-index.xml',
    array(
        'packagefile' => 'package-index.xml',
        'baseinstalldir' => '/pear.php.net/',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'www'),
        'simpleoutput' => true,
        'include' => array(
            'public_html/about/credits.php',
            'public_html/about/index.php',
            'public_html/about/privacy.php',
            'public_html/group/',
            'public_html/news/',
            'public_html/qa/index.php',
            'public_html/support/',
            'public_html/copyright.php',
            'public_html/index.php',
            'public_html/mirrors.php',
            'public_html/gophp5.php',
        ),
    )
);
$a->setReleaseVersion('1.21.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
Refactored RSS feeds for bugs
Changed RSS feeds for homepage
Individual bugs now render Baetle (RDF)
Improved homepage to focus on the human side(s) of PEAR
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('5.2.3');
$a->setPearInstallerDep('1.8.0');

$a->generateContents();

if (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make') {
    $a->writePackageFile();
} else {
    $a->debugPackageFile();
}
