<?php
require_once 'PEAR/PackageFileManager2.php';
$dir = dirname(__FILE__);
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(
    $dir . '/package.xml',
    array(
        'baseinstalldir' => '/pear.php.net/',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'www'),
        'exceptions' => array(
            'pearweb.php' => 'php',
        ),
        'simpleoutput' => true,
        'ignore' => array(
            '*.phar',
            'package-*.xml',
            'package.php',
            'package_*.php',
            'tests/',
            'weeklynews/',
            'scripts/'
        ),
    )
);


$a->setReleaseVersion('1.20.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
Request #15985 Upgrade to Savant3
Assorted fixed unit tests (old test expectations)
XHTML / HTML validity improvements
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('5.2.3');
$a->setPearInstallerDep('1.8.1');
$a->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.8.1');
$a->addPackageDepWithChannel('required', 'Archive_Tar', 'pear.php.net', '1.3.2');
$a->addPackageDepWithChannel('required', 'HTTP_Request2', 'pear.php.net');
$a->addPackageDepWithChannel('required', 'HTTP', 'pear.php.net', '1.4.0');
$a->addPackageDepWithChannel('required', 'Text_CAPTCHA_Numeral', 'pear.php.net', '1.1.0');
$a->addPackageDepWithChannel('required', 'Services_ProjectHoneyPot', 'pear.php.net');
// used only in cron jobs
$a->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.6.5');
$a->addPackageDepWithChannel('required', 'DB_DataObject', 'pear.php.net', '1.8.5');
$a->addPackageDepWithChannel('required', 'Savant3', 'phpsavant.com', '3.0.0');
$a->addPackageDepWithChannel('required', 'HTML_BBCodeParser', 'pear.php.net', '1.2.1');
$a->addPackageDepWithChannel('required', 'HTML_TagCloud', 'pear.php.net');
$a->addPackageDepWithChannel('required', 'HTML_Table', 'pear.php.net', '1.5');
$a->addPackageDepWithChannel('required', 'HTML_Menu', 'pear.php.net', '2.1.4');
$a->addPackageDepWithChannel('required', 'Pager', 'pear.php.net', '2.2.0');
$a->addPackageDepWithChannel('required', 'PEAR_PackageUpdate', 'pear.php.net');
//$a->addPackageDepWithChannel('required', 'PEAR_PackageFileManager', 'pear.php.net', '1.6.0');
$a->addPackageDepWithChannel('required', 'Net_URL2', 'pear.php.net', '0.1.0');
$a->addPackageDepWithChannel('required', 'Text_Diff', 'pear.php.net');
$a->addPackageDepWithChannel('required', 'HTTP_Upload', 'pear.php.net', '0.8.1');
$a->addPackageDepWithChannel('required', 'MDB2_Schema', 'pear.php.net', '0.6.0');
$a->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.8.4');
$a->addPackageDepWithChannel('required', 'Mail', 'pear.php.net', '1.1.13');
$a->addPackageDepWithChannel('required', 'Services_Trackback', 'pear.php.net', '0.4.0');
$a->addPackageDepWithChannel('required', 'HTML_QuickForm', 'pear.php.net', '3.2.3');
// This is used in the admin menu for category
$a->addPackageDepWithChannel('required', 'HTML_TreeMenu', 'pear.php.net', '1.2.0');
$a->addPackageDepWithChannel('required', 'MDB2_Driver_mysqli', 'pear.php.net');
$a->addExtensionDep('required', 'pcre');
$a->addExtensionDep('required', 'mysqli');
$a->addExtensionDep('required', 'fileinfo');
$a->addPackageDepWithChannel('required', 'Graph', 'components.ez.no');

include_once 'PEAR/Config.php';
include_once 'PEAR/PackageFile.php';
$config = &PEAR_Config::singleton();
$p      = &new PEAR_PackageFile($config);
// Specify subpackages
$e = $p->fromPackageFile($dir . '/package-channel.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($e, false);
$b = $p->fromPackageFile($dir . '/package-election.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($b, false);
$f = $p->fromPackageFile($dir . '/package-gopear.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($f, false);
$d = $p->fromPackageFile($dir . '/package-index.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($d, false);
$h = $p->fromPackageFile($dir . '/package-manual.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($h, false);
$c = $p->fromPackageFile($dir . '/package-pepr.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($c, false);
$g = $p->fromPackageFile($dir . '/package-qa.xml', PEAR_VALIDATE_NORMAL);
$a->specifySubpackage($g, false);

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
$a->addReplacement('pearweb.php', 'pear-config', '@www-dir@', 'www_dir');
$a->addReplacement('pearweb.php', 'pear-config', '@php-dir@', 'php_dir');
$a->addReplacement('pearweb.php', 'package-info', '@version@', 'version');
$a->addReplacement('cron/bug-update-php-version.php', 'pear-config', '@www-dir@', 'www_dir');
$a->generateContents();

if (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make') {
    $a->writePackageFile();
} else {
    $a->debugPackageFile();
}
