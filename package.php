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


$a->setReleaseVersion('1.18.0');
$a->setReleaseStability('stable');
$a->setAPIStability('stable');
$a->setNotes('
- log errors in syslog
- improvements on honeypot bug reporting integration
- don\'t crash when crashing (e.g. when credentials are wrong)
- do not show bug stats if the person does not have a pear.dev karma [dufuz]
- use HTML 5 doctype [saltybeagle]
- list chm files on manual download page
- add apidoclog to admin menu
- fix mail template path
- use \$_SERVER[PEAR_BOX] instead of \$_ENV since nobody knows how to set that
- prevent search engines from sending password reminders
- move patch directory
- switch to eukhost
- script to fix "latest" api doc links
- use HTTP_Request2
- dozens of other things that are too many to be listed here
* Fix Bug #11180: User note\'s timestamp is updated on approval [cellog]
* Fix Bug #11998: Broken emails from user note to bug converter [davidc]
* Fix Bug #12375: Can\'t use UTF-8 chars in a bug report [dufuz]
* Fix Bug #12709: DB Error (syntax) transforming note into Doc bug [davidc]
* Fix Bug #12896: Bug system emails double the EOLs on quick response texts [dufuz]
* Fix Bug #12899: account-request-confirm.php will encourage incorrect characters in
   usernames [davidc]
* Fix Bug #12946: .diff patch upload does not work with opera/linux [dufuz]
* Fix Bug #12949: Notes to doc bug converter is broken [dufuz]
* Fix Bug #13064: Problems with automatically added linebreaks in manual navigation bar [dufuz]
* Fix Bug #13326: Removal of CHM upload script [mj]
* Fix Bug #13399: BugDB: PHP CVS versions are of by many months [dufuz]
* Fix Bug #13402: PEAR_Info doc end-user link broken [doconnor]
* Fix Bug #13453: Broken cron job - find-documentation.php [dufuz]
* Fix Bug #13534: Map view fails to render well if 0 entries [dufuz]
* Fix Bug #13585: bug search broken w short keywords (pear.php.net vs mysql
  configuration) [dufuz]
* Fix Bug #13874: Display issue subscription status [dufuz]
* Fix Bug #14011: Badly formatted descriptions [dufuz]
* Fix Bug #14039: Incorrect links in the PEAR Group election [dufuz]
* Fix Bug #14726: Bogus developer accounts [dufuz]
* Fix Bug #15459: @web_dir@ doesn\'t work with new pear [dufuz]
* Fix Bug #15460: VFS.php not found - missing dependency [dufuz]
* Fix Bug #15533: file upload leads to white page when permissions don\'t work [dufuz]
* Fix Bug #15666: List accounts page broken [dufuz]
* Fix Bug #15667: Undefined variable \$where [dufuz]
* Fix Bug #15669: Open feature requests count vs Open feature requests link [dufuz]
* Fix Bug #15718: PHP Notice:  Undefined variable: string [dufuz]
* Fix Bug #15724: The page to add a new PEAR channel doesn\'t load [dufuz]
* Fix Bug #15730: Roadmap -> Show Feature Detail displays oddly [dufuz]
* Fix Bug #15905: wrong links on packages changelog [dufuz]
* Fix Bug #15915: Search for developers - invalid pagination combo values [amir]
* Fix Bug #15921: "Manage your password" should appear only if there\'s really a form [dufuz]
* Fix Bug #15967: Issues with user notes [dufuz]
* Fix Bug #15968: user information: open bugs count [dufuz]
* Fix Bug #16036: Bug tracker emails: Patch links broken [dufuz]
* Implement Feature #10532: Add a pear-bugs mailing list [dufuz]
* Implement Feature #10903: Search the map for specific developer [dufuz]
* Implement Feature #11082: Package Summary (Bug) Request Report [dufuz]
* Implement Feature #11096: Patch: Consider adding microformat information to account pages [dufuz]
* Implement Feature #11289: Commenting on proposals [dufuz]
* Implement Feature #11350: Note management should not prompt the user delete notes when there are
  none [davidc]
* Implement Feature #11351: Make the Channels UI more public [dufuz]
* Implement Feature #11352: Don\'t hardcode in email addresses (specifically, channels) [dufuz]
* Implement Feature #11865: Note management displays too many results [davidc]
* Implement Feature #12436: bug tracker: keep history of changes [dufuz]
* Implement Feature #12453: disallow robots in versioned api docs [dufuz]
* Implement Feature #12563: QA, detection of closed bugs that haven\'t made it into a release in X
  months [dufuz]
* Implement Feature #12792: Throw out JPGraph for a new lib [dufuz]
* Implement Feature #12824: Better structure for the orphan QA page [dufuz]
* Implement Feature #12828: Empty search for package should return all packages [dufuz]
* Implement Feature #12829: Package search: option to allow more entries per page [dufuz]
* Implement Feature #12843: link bugs opened by account [dufuz]
* Implement Feature #12901: Improving pagination in packages.php [dufuz]
* Implement Feature #13034: Max patch size of 20k is too small [dufuz]
* Implement Feature #13041: Add note submitter to bugs created in the manual notes system [dufuz]
* Implement Feature #13105: rss feed: name of package in title [dufuz]
* Implement Feature #13327: When one package is found in 404, redirect right away [dufuz]
* Implement Feature #13428: Add title to anchors which link to other internal bugs [dufuz]
* Implement Feature #13470: make_link should make it easy to be xhtml friendly [dufuz]
* Implement Feature #13539: automatically search manual when 404 on root dir [cweiske]
* Implement Feature #13540: Improve the download interface for newbies [dufuz]
* Implement Feature #13661: Sort Roadmaps by Version [dufuz]
* Implement Feature #13833: Mail bug reporters when a release is out that fixes their bugs [dufuz]
* Implement Feature #14208: make IRC channel clickable in support page [dufuz]
* Implement Feature #14907: list people with qa karma on the qa page [dufuz]
* Implement Feature #15511: Swap away from DB_Pager to Pager [dufuz]
* Implement Feature #15668: Use HTTP_Request2 [dufuz]
* Implement Feature #15777: List of packages which requires PEAR older than 1.5.4. [amir]
* Implement Feature #15782: Find "stable" packages with version < 1.0 [amir]
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
$a->addPackageDepWithChannel('required', 'Savant2', 'savant.pearified.com', '2.4.2');
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
$a->generateContents();

if (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make') {
    $a->writePackageFile();
} else {
    $a->debugPackageFile();
}