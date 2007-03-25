<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(dirname(__FILE__) . '/package-channel.xml',
    array(
        'baseinstalldir' => '/',
        'packagefile' => 'package-channel.xml',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'web'),
        'exceptions' => array('pearweb.php' => 'php'),
        'simpleoutput' => true,
        'include' => array(
            dirname(__FILE__) . '/public_html/channel.xml',
        ),
    ));
$a->setReleaseVersion('1.0.0RC1');
$a->setReleaseStability('beta');
$a->setAPIStability('beta');
$a->setNotes('
first release
');
$a->resetUsesrole();
$a->addUsesRole('web', 'Role_Web', 'pearified.com');
$a->clearDeps();
$a->setPhpDep('4.3.0');
$a->setPearInstallerDep('1.4.11');
$a->addPackageDepWithChannel('required', 'Role_Web', 'pearified.com');
$a->generateContents();
$a->writePackageFile();
?>
