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
            'package-channel.xml.php',
            'package-channel.xml',
            'channel.xml',
            'tests/',
        ),
    ));
$a->setReleaseVersion('1.15.2');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
 Add Security announcement
 http://pear.php.net/advisory-20070507.txt
 * Fix Bug #10856: proposal stays in voting phase [mj]
 * Fix Bug #10885: Milestones not saved when submitting a bug [cellog]
 * Fix Bug #10898: Broken link from /news [bjori]
 * Fix Bug #10902: Unable to view elections (not logged in) [cellog]
 * Fix Bug #10927: Typo in pearweb_mdb2schema.xml [wiesemann]
 * Fix Bug #10928: Notices generated in bug tracker [cellog]
 * Fix Bug #10929: Non-Dev Comments Cause Bug to Unassign Dev/Roadmap [cellog]
 * Fix Bug #10932: Admin package maintainers error [davidc]
 * Fix Bug #10933: Patch diff view shows warnings [cellog]
 * Fix Bug #10948: Unclosed tags in bug tracker [wiesemann]
 * Fix Bug #10950: Fix for #10908 removed "CVS" package version selection for new bugs [cellog]
 * Fix Bug #10957: dufuz broke voter account registration [cellog]
 * Fix Bug #10958: "Old patches" list does not UTC [mj]
 * Fix Bug #10959: context is not htmlspecialchar()ed in patch diff [cellog]
 * Fix Bug #10960: registration fails for voter [cellog]
 * Implement Feature #8808: RSS feed for bug system [cellog]
 * Implement Feature #10624: Generated package.xml mis-orders releases. [cellog]
 * Added developer implemented feature using Net_URL and now is correctly using Net_URL2 [davidc]
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
$a->addPackageDepWithChannel('required', 'Text_CAPTCHA_Numeral', 'pear.php.net', '1.1.0');
// used only in cron jobs
$a->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.6.5');
$a->addPackageDepWithChannel('required', 'DB_DataObject', 'pear.php.net', '1.8.5');
$a->addPackageDepWithChannel('required', 'Savant2', 'savant.pearified.com', '2.4.2');
$a->addPackageDepWithChannel('required', 'Cache', 'pear.php.net', '1.2');
$a->addPackageDepWithChannel('required', 'HTML_BBCodeParser', 'pear.php.net', '1.1');
$a->addPackageDepWithChannel('required', 'HTML_Form', 'pear.php.net', '1.3.0');
$a->addPackageDepWithChannel('required', 'HTML_TagCloud', 'pear.php.net');
$a->addPackageDepWithChannel('required', 'HTML_Table', 'pear.php.net', '1.5');
$a->addPackageDepWithChannel('required', 'Pager', 'pear.php.net', '2.2.0');
$a->addPackageDepWithChannel('required', 'PEAR_PackageUpdate', 'pear.php.net');
//$a->addPackageDepWithChannel('required', 'PEAR_PackageFileManager', 'pear.php.net', '1.6.0');
$a->addPackageDepWithChannel('required', 'Net_URL', 'pear.php.net', '1.0.14');
$a->addPackageDepWithChannel('required', 'Text_Diff', 'pear.php.net');
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
