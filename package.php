<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$a = PEAR_PackageFileManager2::importOptions(dirname(__FILE__) . '/package.xml',
    array(
        'baseinstalldir' => '/',
        'filelistgenerator' => 'cvs',
        'roles' => array('*' => 'www'),
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
            // next are files in pearweb_index package
            '*about/credits.php',
            '*about/index.php',
            '*about/privacy.php',
            'group/',
            'news/',
            'support/',
            '*public_html/copyright.php',
            '*public_html/credits.php',
            '*public_html/faq.php',
            '*public_html/index.php',
            '*public_html/mirrors.php',
            // next are files in pearweb_gopear package
            '*public_html/go-pear',
            '*public_html/new-gopear.php',
        ),
    ));
$a->setReleaseVersion('1.17.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
 * Fix Bug #9476: schema validation error on postinstall script [dufuz]
 * Fix Bug #11094: users table should include longitude, latitude on creation [dufuz]
 * Fix Bug #11295: Fixed in CVS does not mark a bug as closed [dufuz]
 * Fix Bug #11308: Bug tracker: no feedback after (un)subscribing [cellog]
 * Fix Bug #11382: Tell registering users they need to provide &quot;Firstname Lastname&quot; [dufuz]
 * Fix Bug #11412: Incorrect Package Statistics for Category [dufuz]
 * Fix Bug #11421: Developers should be able to read unconfirmed bugcomments [dufuz]
 * Fix Bug #11422: &quot;Status: Open | Feedback | All&quot; header not displayed when no bugs are
   found [wiesemann]
 * Fix Bug #11441: Package releases not available [cellog]
 * Fix Bug #11456: Admin page account overview [cellog]
 * Fix Bug #11497: RSS feed description field should contain breaks [dufuz]
 * Fix Bug #11502: Uploading patch doesn&apos;t work [cellog]
 * Fix Bug #11638: Link to Bug Tracker on User Note Entry Screen fails [dufuz]
 * Fix Bug #11759: Manual: Note form loses page URL [cellog]
 * Fix Bug #11760: Bug tracker: summary is shown twice [wiesemann]
 * Fix Bug #11180  User note\'s timestamp is updated on approval [cellog]
 * Fix Bug #11804: Forgotten Password Field for mailaddress too short [cellog]
 * Fix Bug #12004: Roadmap lacks error handling in case of duplicate roadmap versions [dufuz]
 * Fix Bug #12021: Inconsistent bug tracker behaviour [dufuz]
 * Fix Bug #12237: Patch upload doesn&apos;t work during ticket creation [dufuz]
 * Fix Bug #12296: 17k patch won&apos;t upload - described limit is 20k but actual is 10k. [dufuz]
 * Fix Bug #12467: adding 5.2.5 to the PHP version select box [wiesemann]
 * Fix Bug #12470: don&apos;t list non-developers [dufuz]
 * Fix Bug #12502: Bug tracker: &quot;Add Comment&quot; tab not always visible [dufuz]
 * Fix Bug #12526: Last updated on bottom of each page [dufuz]
 * Fix Bug #12718: Unnecessary space in package list on account info page [wiesemann]
 * Fix Bug #12728: Undefined variable error in notes system [wiesemann]
 * Implement Feature #3085: Link to package page from finished proposals [dufuz]
 * Implement Feature #10800: Copy closed tickets since last release into a new roadmap [dufuz]
 * Implement Feature #10900: link developer maps from pearweb [cweiske]
 * Implement Feature #10943: Remove verification screen for developers [dufuz]
 * Implement Feature #11086: tabs &amp; whitespace mixing in assorted files [dufuz]
 * Implement Feature #11109: Restyle the warning message css [dufuz]
 * Implement Feature #11118: List of PEAR packages should not show deprecated packages [dufuz]
 * Implement Feature #11270: Replace all image CAPTCHA with math one [dufuz]
 * Implement Feature #11362: Get rid of captcha for pear.dev when sending mail to user via webform [dufuz]
 * Implement Feature #11436: split go-pear in its own package [dufuz]
 * Implement Feature #11450: When showing bugs by user handle, hide those [quipo]
 * Implement Feature #11530: bug reports should have a panel/div explaining the colour coding [dufuz]
 * Implement Feature #11851: Assigned bugs that are not maintained by developer made visable on
   info page [dufuz]
 * Implement Feature #11985: Developer map rest link should be application/rdf+xml [davidc]
 * Implement Feature #12013: package name is shown in bug list even you select only one package [dufuz]
 * Implement Feature #12472: choose a sensible font size [dufuz]
 * Implement Feature #12591: Removing all code dealing with DES passwords [dufuz]
 * Implement Feature #12623: Removal of the xmlrpc gateway [dufuz]
 * Implement Feature #12631: Ditch the builtin HTML_Menu class for upstream one [dufuz]
 * Implement Feature #12632: Use the new file roles added in PEAR 1.7.0 [dufuz]
 * Implement Feature #12646: Package: field should link to the package home page [dufuz]
 * Implement Feature #12691: New presentation slide [ifeghali]
 * Implement Feature #12721: Show dependent packages in alphabetic order on package info page [wiesemann]
 * Implement Feature #12724: Cron job for fetching PHP versions into the bug tracker version
   dropdown list [dufuz]
 * Implement Feature #12786: Redesign of the menu system [dufuz]
 * Implement Feature #12787: YUI Reset CSS to unify the site more properly between A grade browsers [dufuz]
 * Implement Feature #12788: Make the layout fluid via Divs where it make sense [dufuz]
 * Implement Feature #12789: Move the developer bugs stats into it&apos;s own file [dufuz]
');
$a->resetUsesrole();
$a->clearDeps();
$a->setPhpDep('5.2.0');
$a->setPearInstallerDep('1.7.0RC1');
$a->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.7.0RC1');
$a->addPackageDepWithChannel('optional', 'pearweb_index', 'pear.php.net', '1.16.4');
$a->addPackageDepWithChannel('optional', 'pearweb_gopear', 'pear.php.net', '0.6.0');
$a->addPackageDepWithChannel('required', 'Archive_Tar', 'pear.php.net', '1.3.2');
$a->addPackageDepWithChannel('required', 'HTTP_Request', 'pear.php.net', '1.2.2');
$a->addPackageDepWithChannel('required', 'HTTP', 'pear.php.net', '1.4.0');
$a->addPackageDepWithChannel('required', 'Text_CAPTCHA_Numeral', 'pear.php.net', '1.1.0');
// used only in cron jobs
$a->addPackageDepWithChannel('required', 'DB', 'pear.php.net', '1.6.5');
$a->addPackageDepWithChannel('required', 'DB_DataObject', 'pear.php.net', '1.8.5');
$a->addPackageDepWithChannel('required', 'Savant2', 'savant.pearified.com', '2.4.2');
$a->addPackageDepWithChannel('required', 'Cache', 'pear.php.net', '1.2');
$a->addPackageDepWithChannel('required', 'HTML_BBCodeParser', 'pear.php.net', '1.2.1');
$a->addPackageDepWithChannel('required', 'HTML_Form', 'pear.php.net', '1.3.0');
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
$a->addPackageDepWithChannel('required', 'DB_Pager', 'pear.php.net', '0.7');
$a->addPackageDepWithChannel('required', 'Log', 'pear.php.net', '1.8.4');
$a->addPackageDepWithChannel('required', 'Mail', 'pear.php.net', '1.1.13');
$a->addPackageDepWithChannel('required', 'Services_Trackback', 'pear.php.net', '0.4.0');
// required for PEPr
$a->addPackageDepWithChannel('required', 'Text_Wiki', 'pear.php.net', '1.2.0');
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
