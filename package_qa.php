<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(
    dirname(__FILE__) . '/package-qa.xml',
    array(
        'packagefile' => 'package-qa.xml',
        'baseinstalldir' => '/pear.php.net/',
        'filelistgenerator' => 'git',
        'roles' => array('*' => 'www'),
        'simpleoutput' => true,
        'include' => array(
            'public_html/qa/',
        ),
        'ignore' => array(
            'public_html/qa/index.php'
        ),
    ));
$a->setReleaseVersion('1.0.3');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
Add an unmaintained category to the output of packages-closed-no-release
Remove restrictions on orphan package list
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('5.2.3');
$a->setPearInstallerDep('1.7.1');
$a->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.7.1');
$a->addPackageDepWithChannel('required', 'pearweb', 'pear.php.net', '1.18.0');
$a->generateContents();
$a->writePackageFile();

if (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make') {
    $a->writePackageFile();
} else {
    $a->debugPackageFile();
}
