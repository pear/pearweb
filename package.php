<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(dirname(__FILE__) . '/package.xml',
    array(
        'baseinstalldir' => '/',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'web'),
        'exceptions' => array('pearweb.php' => 'php'),
        'simpleoutput' => true,
        'ignore' => array(
            '*.phar',
            'package.xml',
            'package.php',
            'tests/',
        ),
    ));
$a->setReleaseVersion('1.9.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
* fix release upload: don\'t let custom non-pear built packages with no package.xml
  in root dir slip through the cracks
');
$a->resetUsesrole();
$a->addUsesRole('web', 'Role_Web', 'pearified.com');
$a->clearDeps();
$a->setPhpDep('4.3.0');
$a->setPearInstallerDep('1.4.11');
$a->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.5.0');
$a->addPackageDepWithChannel('required', 'Archive_Tar', 'pear.php.net', '1.3.2');
$a->addPackageDepWithChannel('required', 'HTTP_Request', 'pear.php.net', '1.2.2');
$a->addPackageDepWithChannel('required', 'HTTP', 'pear.php.net', '1.4.0');
// used only in cron jobs
$a->addPackageDepWithChannel('required', 'VFS', 'pear.php.net', '0.1.0');
$a->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.6.5');
$a->addPackageDepWithChannel('required', 'DB_DataObject', 'pear.php.net', '1.8.5');
$a->addPackageDepWithChannel('required', 'Savant2', 'savant.pearified.com', '2.4.2');
$a->addPackageDepWithChannel('required', 'Cache', 'pear.php.net', '1.2');
$a->addPackageDepWithChannel('required', 'HTML_BBCodeParser', 'pear.php.net', '1.1');
$a->addPackageDepWithChannel('required', 'HTML_Form', 'pear.php.net', '1.3.0');
$a->addPackageDepWithChannel('required', 'HTML_Table', 'pear.php.net', '1.5');
$a->addPackageDepWithChannel('required', 'Pager', 'pear.php.net', '2.2.0');
$a->addPackageDepWithChannel('required', 'PEAR_PackageUpdate', 'pear.php.net');
//$a->addPackageDepWithChannel('required', 'PEAR_PackageFileManager', 'pear.php.net', '1.6.0');
$a->addPackageDepWithChannel('required', 'Net_URL', 'pear.php.net', '1.0.14');
$a->addPackageDepWithChannel('required', 'HTTP_Upload', 'pear.php.net', '0.8.1');
$a->addPackageDepWithChannel('required', 'MDB2_Schema', 'pear.php.net', '0.6.0');
$a->addPackageDepWithChannel('required', 'DB_Pager', 'pear.php.net', '0.7');
$a->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.8.4');
$a->addPackageDepWithChannel('required', 'Mail', 'pear.php.net', '1.1.13');
$a->addPackageDepWithChannel('required', 'Services_Trackback', 'pear.php.net', '0.4.0');
// required for PEPr
$a->addPackageDepWithChannel('required', 'Text_Wiki', 'pear.php.net', '1.1.0');
$a->addPackageDepWithChannel('required', 'HTML_QuickForm', 'pear.php.net', '3.2.3');
$a->addPackageDepWithChannel('required', 'HTML_TreeMenu', 'pear.php.net', '1.2.0');
$a->addDependencyGroup('php4', 'Use this for PHP 4 (mysql ext)');
$a->addDependencyGroup('php5', 'Use this for PHP 5 (mysqli ext)');
$a->addGroupPackageDepWithChannel('package', 'php4', 'MDB2_Driver_mysql', 'pear.php.net');
$a->addGroupPackageDepWithChannel('package', 'php5', 'MDB2_Driver_mysqli', 'pear.php.net');
$a->addExtensionDep('required', 'pcre');
// required by VFS
$a->addExtensionDep('required', 'gettext');
$a->addExtensionDep('optional', 'mysql');
$a->addExtensionDep('optional', 'mysqli');
$a->addPackageDepWithChannel('required', 'Role_Web', 'pearified.com');
$script = &$a->initPostinstallScript('pearweb.php');
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
        $script->getParam('addnamev', 'Add NameVirtualHost directive? (yes/no)', 'string', 'yes'),
        $script->getParam('namehost', 'Virtual Host IP address', 'string', '*'),
        $script->getParam('pear', 'PEAR subdomain name', 'string', 'localhost'),
    )
    );

$a->addPostinstallTask($script, 'pearweb.php');
$a->addReplacement('pearweb.php', 'pear-config', '@web-dir@', 'web_dir');
$a->addReplacement('pearweb.php', 'pear-config', '@php-dir@', 'php_dir');
$a->addReplacement('pearweb.php', 'package-info', '@version@', 'version');
$a->generateContents();
$a->writePackageFile();
?>
