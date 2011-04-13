<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(
    dirname(__FILE__) . '/package-pepr.xml',
    array(
        'packagefile' => 'package-pepr.xml',
        'baseinstalldir' => '/pear.php.net/',
        'filelistgenerator' => 'svn',
        'roles' => array('*' => 'www'),
        'exceptions' => array('pearweb_pepr.php' => 'php'),
        'simpleoutput' => true,
        'include' => array(
            'cron/pepr.php',
            'public_html/pepr/',
            'include/pepr/',
            'sql/pearweb_pepr.xml',
        ),
    ));
$a->setReleaseVersion('1.0.4');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
- Migration to QF2 for everything but pepr-proposal-editing [clockwerx]
- Remove BBCode (in favour of only supporting Text_Wiki from now on) [till]
- Small refactoring while debugging [till]
- Fix Bug #16578 Unable to use https URL in "Links" section [saltybeagle]
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('5.2.3');
$a->setPearInstallerDep('1.7.1');
$a->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.7.1');
$a->addPackageDepWithChannel('required', 'HTML_QuickForm', 'pear.php.net', '3.2.12');
$a->addPackageDepWithChannel('required', 'HTML_QuickForm2', 'pear.php.net', '0.5.3');
$a->addPackageDepWithChannel('required', 'pearweb', 'pear.php.net', '1.18.0');
$a->addPackageDepWithChannel('required', 'Text_Wiki', 'pear.php.net', '1.2.0');
$a->addExtensionDep('required', 'pcre');
$a->addExtensionDep('required', 'mysqli');

$script = &$a->initPostinstallScript('pearweb_pepr.php');
$script->addParamGroup(
    'askdb',
    array(
        $script->getParam('yesno', 'Update pearweb database?', 'yesno', 'y'),
    )
    );
$script->addParamGroup(
    'init',
    array(
        $script->getParam('driver', 'Database driver', 'string', 'mysqli'),
        $script->getParam('user', 'Database User name', 'string', 'pear'),
        $script->getParam('password', 'Database password', 'password', 'pear'),
        $script->getParam('host', 'Database host', 'string', 'localhost'),
        $script->getParam('database', 'Database name', 'string', 'pear'),
    )
    );
$script->addParamGroup(
    'askhttpd',
    array(
        $script->getParam('yesno', 'Update httpd.conf to add pearweb? (y/n)', 'yesno', 'y'),
    )
    );
$script->addParamGroup(
    'httpdconf',
    array(
        $script->getParam('path', 'Full path to httpd.conf', 'string'),
    )
    );

$a->addPostinstallTask($script, 'pearweb_pepr.php');
$a->addReplacement('pearweb_pepr.php', 'pear-config', '@www-dir@', 'www_dir');
$a->addReplacement('pearweb_pepr.php', 'pear-config', '@php-dir@', 'php_dir');
$a->addReplacement('pearweb_pepr.php', 'package-info', '@version@', 'version');
$a->generateContents();
$a->writePackageFile();

if (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make') {
    $a->writePackageFile();
} else {
    $a->debugPackageFile();
}
