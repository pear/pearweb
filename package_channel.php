<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(dirname(__FILE__) . '/package-channel.xml',
    array(
        'baseinstalldir' => '/pear.php.net/',
        'packagefile' => 'package-channel.xml',
        'filelistgenerator' => 'svn',
        'roles' => array('*' => 'www'),
        'simpleoutput' => true,
        'include' => array(
            dirname(__FILE__) . '/public_html/channel.xml',
            'public_html/dtd/'
        ),
    ));
$a->setReleaseVersion('1.15.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
- add piece framework channel
- add links to channel info in the manual
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('4.3.0');
$a->setPearInstallerDep('1.8.0');
$a->addPackageDepWithChannel('required', 'HTTP_Request2', 'pear.php.net');
$a->generateContents();
$a->writePackageFile();