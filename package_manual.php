<?php
error_reporting(error_reporting() & ~E_STRICT & ~E_DEPRECATED);
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(
    dirname(__FILE__) . '/package-manual.xml',
    array(
        'packagefile' => 'package-manual.xml',
        'baseinstalldir' => '/pear.php.net/',
        'filelistgenerator' => 'file',
        'roles' => array('*' => 'www'),
        'exceptions' => array('pearweb_manual.php' => 'php'),
        'simpleoutput' => true,
        'include' => array(
            'cron/apidoc-fix-latest.php',
            'cron/apidoc-queue.php',
            'cron/find-documentation.php',
            'load-chm.sh',
            'public_html/admin/apidoc-log.php',
            'public_html/css/manual.css',
            'public_html/error/404-manual.php',
            'public_html/manual/',
            'public_html/notes/',
            'include/notes/',
            'include/pear-manual.php',
            'templates/notes/',
            'sql/pearweb_manual.xml',
        ),
    )
);
$a->setReleaseVersion('1.2.3');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
- Only list English manual, dropping .chm for downloads.
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('5.2.3');
$a->setPearInstallerDep('1.8.0');
$a->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.8.0');
$a->addPackageDepWithChannel('required', 'pearweb', 'pear.php.net', '1.18.0');
$a->addPackageDepWithChannel('required', 'VFS', 'pear.php.net');
$a->addPackageDepWithChannel('required', 'HTTP_Request2', 'pear.php.net');
$a->addPackageDepWithChannel('required', 'phpDocumentor', 'pear.phpdoc.org');
$a->addExtensionDep('required', 'pcre');
$a->addExtensionDep('required', 'mysqli');

$script = &$a->initPostinstallScript('pearweb_manual.php');
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

$a->addPostinstallTask($script, 'pearweb_election.php');
$a->addReplacement('pearweb_election.php', 'pear-config', '@www-dir@', 'www_dir');
$a->addReplacement('pearweb_election.php', 'pear-config', '@php-dir@', 'php_dir');
$a->addReplacement('pearweb_election.php', 'package-info', '@version@', 'version');
$a->clearContents();
$a->generateContents();

if (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make') {
    $a->writePackageFile();
} else {
    $a->debugPackageFile();
}
