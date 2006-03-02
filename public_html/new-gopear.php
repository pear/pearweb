<?php //; echo; echo "YOU NEED TO RUN THIS SCRIPT WITH PHP NOW!"; echo; echo "Try this: lynx -source http://pear.php.net/go-pear | php -q"; echo; exit # -*- PHP -*-
# *****WARNING***** development refactored code, not for production
# +----------------------------------------------------------------------+
# | PHP Version 5                                                        |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997-2005 The PHP Group                                |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.02 of the PHP license,      |
# | that is bundled with this package in the file LICENSE, and is        |
# | available at through the world-wide-web at                           |
# | http://www.php.net/license/2_02.txt.                                 |
# | If you did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world-wide-web, please send a note to          |
# | license@php.net so we can mail you a copy immediately.               |
# +----------------------------------------------------------------------+
# | Authors: Greg Beaver <cellog@php.net>                                |
# |          Tomas V.V.Cox <cox@idecnet.com>                             |
# |          Stig Bakken <ssb@php.net>                                   |
# |          Christian Dickmann <dickmann@php.net>                       |
# |          Pierre-Alain Joye <pajoye@pearfr.org>                       |
# +----------------------------------------------------------------------+
# $Id$
#
# based on go-pear
#
# Automatically download all the files needed to run the "pear" command
# (the PEAR package installer).  Requires PHP 4.2.0 or newer.
#
# Installation: Linux
#
# This script can either be directly launched or passed via lynx like this.
#
#  $ lynx -source http://pear.php.net/go-pear | php
#
# The above assumes your php binary is named php and that it's
# executable through your PATH:
#
# Installation: Windows
#
# On Windows, go-pear uses stdin for user input, so you must download
# go-pear first and then run it:
#
# Note: In PHP 4.2.0-4.2.3, the PHP CLI binary is named php-cli.exe while since
# PHP 4.3.0, it's simply named php.exe in the cli/ folder of your PHP directory.
# The CGI is also named php.exe but it sits directly in your PHP directory.
#
#  > cli/php -r "readfile('http://pear.php.net/go-pear');" > go-pear
#  > cli/php go-pear
#
# In PHP 5.0.0, the PHP CLI binary is php.exe
#
#  > php -r "readfile('http://pear.php.net/go-pear');" > go-pear
#  > php go-pear
#
# Installation: Notes
#
# - If using the CGI version of PHP, append the -q option to suppress
#   headers in the output.
# - By default, go-pear will install a system-wide configuration file.  For
#   a local install use:
#   > php go-pear local
#
# - Once the go-pear script is initiated, you will see instructions on
#   how to continue installing PEAR.  The first thing you should see is:
#
#   Welcome to go-pear!
#
# Installation: Web browser
#
# You can now use go-pear via a webbrowser, thanks to Christian Dickmann. It is
# still beta codes, but feel free to test it:
# 1.: Download the go-pear script by using the "Save target as ..." function
# of your browser here.
#
# 2.: Place the go-pear file somewhere under the document root of your webserver.
# The easiest way is to create a new directory for pear and to put the file in there.
# Be sure your web server is setup to recognize PHP, and that you use an appropriate
# extension.  For example, you might name this file gopear.php
#
# 3.: Access go-pear through your webserver and follow the instructions. Please
# make sure that PHP has write access to the dir you want to install PEAR into.
# For example: http://localhost/pear/gopear.php
#
# 4.: After running go-pear you get a link to the Web Frontend of the PEAR installer.
# I suggest bookmarking this link.
#
# 5.: Protect the Web Frontend directory and the go-pear script with a password.
# Use .htaccess on Apache webservers for example.
#
#

function dump($var) {
    if (defined('WEBINSTALLER') && WEBINSTALLER == 'cgi') {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
    print_r($var);
}
error_reporting(E_ALL);
$sapi_name = php_sapi_name();
@ob_end_clean();
define('WEBINSTALLER',
    ($sapi_name != 'cli' &&
    !(substr($sapi_name,0,3)=='cgi' &&
    !isset($_SERVER['GATEWAY_INTERFACE']))));

define('GO_PEAR_VER', '0.6.0');

if (WEBINSTALLER) {
    $installer = new Gopear_Web;
} else {
    $installer = new Gopear_CLI;
}

verifyPHPVersion();

function installerbail($msg = '')
{
    $GLOBALS['installer']->bail($msg);
}

register_shutdown_function('installerbail');

$installer->setupConfigDesc();

$installer->detect_install_dirs();

$installer->doSetupConfigVars();

$progress = 0;

$installer->displayPreamble();

$installer->setupTempStuff();

$installer->doGetConfigVars();
$installer->postProcessConfigVars();

####
# Download
####

ini_set("include_path", $installer->ptmp);

$installer->setupZlibAndPackages();

$installer->displayHTMLProgress($progress = 5);

$installer->mergeGoPearBundle();
if (!$installer->install_pfc) {
    $basicprogressgoal = 70;
} else {
    $basicprogressgoal = 70 - round((70 - $progress) *
            (count($installer->installer_packages) / count($installer->to_install)));
}
$installer->downloadPackages($installer->installer_packages, $tarball, $progress, $basicprogressgoal);

if ($progress < $basicprogressgoal) {
    $installer->displayHTMLProgress($progress = $basicprogressgoal);
}

if ($installer->install_pfc) {
    $installer->downloadPackages($installer->pfc_packages, $tarball, $progress, 70);
}

$installer->displayHTMLProgress($progress = 70);

$installer->bootStrap('PEAR', $tarball, 'pear-core/PEAR.php', 'PEAR.php');
include_once 'PEAR.php';
print "ok\n";

$installer->displayHTMLProgress($progress = 71);

mkdir('Archive', 0700);
$installer->bootStrap('Archive_Tar', $tarball, 'pear/Archive_Tar/Archive/Tar.php', 'Archive/Tar.php');
print "ok\n";

$installer->displayHTMLProgress($progress = 72);

mkdir('Console', 0700);
$installer->bootStrap('Console_Getopt', $tarball, 'pear-core/Console/Getopt.php',
    'Console/Getopt.php');
print "ok\n";

$installer->displayHTMLProgress($progress = 73);

PEAR::setErrorHandling(PEAR_ERROR_DIE, "\n%s\n");
print 'Extracting installer..................';
$dot = strrpos($tarball['PEAR'], '.');
$pkg = substr($tarball['PEAR'], 0, $dot);
$ext = substr($tarball['PEAR'], $dot+1);

include_once 'Archive/Tar.php';
$tar = &new Archive_Tar($tarball['PEAR'], $installer->have_gzip);
if (!$tar->extractModify($installer->ptmp, $pkg)) {
    $installer->bail("failed!\n");
}
print "ok\n";

$tarball['PEAR'] = 'package.xml'; // :-)

include_once "PEAR.php";
include_once "PEAR/Config.php";
include_once "PEAR/Command.php";
include_once "PEAR/Registry.php";

if (WEBINSTALLER || isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'local') {
    $config = &PEAR_Config::singleton($prefix."/pear.conf", '');
} else {
    $config = &PEAR_Config::singleton();
};
$config->set('preferred_state', 'stable');
foreach ($installer->config_vars as $var) {
    $config->set($var, $installer->$var);
}

$config->store();

$registry = &new PEAR_Registry($php_dir);
PEAR_Command::setFrontendType('CLI');
$install = &PEAR_Command::factory('install', $config);
$install_options = array(
    'nodeps' => true,
    'force' => true,
    );
foreach ($tarball as $pkg => $src) {
    $options = $install_options;
    if ($registry->packageExists($pkg)) {
        $options['upgrade'] = true;
    }

    $install->run('install', $options, array($src));

    $installer->displayHTMLProgress($progress += round(26 / count($tarball)));
}

$installer->displayHTMLProgress($progress = 99);

// Base installation finished

ini_restore("include_path");

$installer->finishInstall();
// Set of functions/classes following

function verifyPHPVersion()
{
    // Check if PHP version is sufficient
    if (function_exists("version_compare") && version_compare(phpversion(), "4.2.0",'<')) {
        die("Sorry!  Your PHP version is too old.  PEAR requires PHP version 4.2.0
or newer for stable operation.

It may be that you have a newer version of PHP installed in your web
server, but an older version installed as the 'php' command.  In this
case, you need to rebuild PHP from source.

If your source is 4.2.x, you need to run 'configure' with the
--enable-cli option, rebuild and copy sapi/cli/php somewhere.

If your source is 4.3.x or newer, just make sure you don't run
'configure' with --disable-cli, rebuild and copy sapi/cli/php.

Please upgrade PHP to a newer version, and try again.  See you then.

");
    }
}

class Gopear_Base
{
    var $bin_dir;
    var $config_vars;
    var $config_desc =
        array(
            'prefix' => 'Installation prefix',
            'bin_dir' => 'Binaries directory',
            'php_dir' => 'PHP code directory ($php_dir)',
            'doc_dir' => 'Documentation base directory',
            'data_dir' => 'Data base directory',
            'test_dir' => 'Tests base directory',
        );
    var $data_dir;
    var $descfmt;
    var $desclin;
    var $doc_dir;
    var $first;
    var $have_gzip;
    var $http_proxy;
    var $install_pfc;
    var $installer_packages =
        array(
            'PEAR-stable',
            'Archive_Tar-stable',
            'Console_Getopt-stable',
            'XML_RPC-stable'
        );
    var $last;
    var $local_dir = array();
    var $origpwd;
    var $pfc_packages = array(
            'DB',
            'Net_Socket',
            'Net_SMTP',
            'Mail',
            'XML_Parser',
            'PHPUnit'
        );
    var $ptmp;
    var $prefix;
    var $php_sapi_name;
    var $php_dir;
    var $php_bin;
    var $test_dir;
    var $to_install;
    var $urltemplate;

    function Gopear_Base()
    {
        ini_set('track_errors', true);
        ini_set('magic_quotes_runtime', false);
        error_reporting(E_ALL);
        define('WINDOWS', (substr(PHP_OS, 0, 3) == 'WIN'));
        ob_implicit_flush(true);
        set_time_limit(0);
        if ($this->my_env('HTTP_PROXY')) {
            $this->http_proxy = my_env('HTTP_PROXY');
        } elseif ($this->my_env('http_proxy')) {
            $this->http_proxy = $this->my_env('http_proxy');
        } else {
            $this->http_proxy = '';
        }
    }

    /**
     * Try to detect the kind of SAPI used by the
     * the given php.exe.
     * @author Pierrre-Alain Joye
     */
    function win32DetectPHPSAPI()
    {
        if ($this->php_bin!='') {
            //exec('"' . $this->php_bin . '" -v', $res);
            if (is_array($res)) {
                if (isset($res[0]) && strpos($res[0],"(cli)")) {
                    return 'cli';
                }
                if (isset($res[0]) && strpos($res[0],"cgi")) {
                    return 'cgi';
                }
                if (isset($res[0]) && strpos($res[0],"cgi-fcgi")) {
                    return 'cgi';
                } else {
                    return 'unknown';
                }
            }
        }
        return 'unknown';
    }

    function loadZlib()
    {
        if (!extension_loaded('zlib')) {
            if (WINDOWS) {
                @dl('php_zlib.dll');
            } elseif (PHP_OS == 'HP-UX') {
                @dl('zlib.sl');
            } elseif (PHP_OS == 'AIX') {
                @dl('zlib.a');
            } else {
                @dl('zlib.so');
            }
        }
    }

    function setupZlibAndPackages()
    {
        $this->loadZlib();
        if (!extension_loaded('zlib')) {
            $this->urltemplate = 'http://pear.php.net/get/%s?uncompress=yes';
            $this->have_gzip = null;
        } else {
            $this->urltemplate = 'http://pear.php.net/get/%s';
            $this->have_gzip = true;
        }
        print "Loading zlib: " . ($this->have_gzip ? 'ok' : 'failed') . "\n";
        if (!$this->have_gzip) {
            print "Downloading uncompressed packages\n";
        };

        if ($this->install_pfc) {
            $this->to_install = array_merge($this->installer_packages, $this->pfc_packages);
        } else {
            $this->to_install = $this->installer_packages;
        }
    }

    function setupTempStuff()
    {
        $this->tmp_dir($this->ptmp, $this->prefix);
        $foo = $this->ptmp;
        $this->ptmp = tempnam($foo, 'gope');
        if (WINDOWS) {
            $this->ptmp = str_replace($foo, '', $this->ptmp);
            $foo = str_replace("\\\\", '/', $foo);
            $s = substr($this->ptmp, 0, 1);
            if ($s=="\\" || $s=='/') {
                $this->ptmp = $foo . '/' . substr($this->ptmp, 1);
            } else {
                $this->ptmp = $foo . '/' . $this->ptmp;
            }
        }

        $this->rm_rf($this->ptmp);
        $this->mkdir_p($this->ptmp, 0700);
        $ok = @chdir($this->ptmp);
    }

    function setupConfigDesc()
    {
    }

    function setupConfigVars()
    {
        $this->origpwd = getcwd();

        $this->config_vars = array_keys($this->config_desc);

        // make indices run from 1...
        array_unshift($this->config_vars, "");
        unset($this->config_vars[0]);
        reset($this->config_vars);
        $this->desclen = max(array_map('strlen', $this->config_desc));
        $this->descfmt = "%-{$this->desclen}s";
        $this->first = key($this->config_vars);
        end($this->config_vars);
        $this->last = key($this->config_vars);
    }

    function postProcessConfigVars()
    {
        foreach ($this->config_vars as $n => $var) {
            for ($m = 1; $m <= count($this->config_vars); $m++) {
                $var2 = $this->config_vars[$m];
                $this->$var = str_replace('$'.$var2, $this->$var2, $this->$var);
            }
        }

        foreach ($this->config_vars as $var) {
            $dir = $this->$var;

            if (!preg_match('/_dir$/', $var)) {
                continue;
            }

            if (!@is_dir($dir)) {
                if (!$this->mkdir_p($dir)) {
                    $root = WINDOWS ? 'administrator' : 'root';
                    $this->bail("Unable to create {$this->config_desc[$var]} $dir.
Run this script as $root or pick another location.\n");
                }
            }
        }
    }

    function which($program, $dont_search_in = false)
    {
        if (WINDOWS) {
            if ($_path = $this->my_env('Path')) {
                $dirs = explode(';', $_path);
            } else {
                $dirs = explode(';', $this->my_env('PATH'));
            }
            foreach ($dirs as $i => $dir) {
                $dirs[$i] = strtolower(realpath($dir));
            }
            if ($dont_search_in) {
                $dont_search_in = strtolower(realpath($dont_search_in));
            }
            if ($dont_search_in &&
                ($key = array_search($dont_search_in, $dirs)) !== false)
            {
                unset($dirs[$key]);
            }

            foreach ($dirs as $dir) {
                $dir = str_replace('\\\\', '\\', $dir);
                if (!strlen($dir)) {
                    continue;
                }
                if ($dir{strlen($dir) - 1} != '\\') {
                    $dir .= '\\';
                }
                $tmp = $dir . $program;
                $info = pathinfo($tmp);
                if (in_array(strtolower($info['extension']),
                      array('exe', 'com', 'bat', 'cmd'))) {
                    if (file_exists($tmp)) {
                        return strtolower($tmp);
                    }
                } elseif (file_exists($ret = $tmp . '.exe') ||
                    file_exists($ret = $tmp . '.com') ||
                    file_exists($ret = $tmp . '.bat') ||
                    file_exists($ret = $tmp . '.cmd')) {
                    return strtolower($ret);
                }
            }
        } else {
            $dirs = explode(':', $this->my_env('PATH'));
            if ($dont_search_in &&
                ($key = array_search($dont_search_in, $dirs)) !== false)
            {
                unset($dirs[$key]);
            }
            foreach ($dirs as $dir) {
                if (is_executable("$dir/$program")) {
                    return "$dir/$program";
                }
            }
        }
        return false;
    }

    /**
     * Fixes for winXP/wrong tmp set by Urs Gehrig (urs@circle.ch)
     */
    function tmp_dir()
    {
        $_temp = false;
        if (WINDOWS){
            if ($this->my_env('TEMP')) {
                $_temp = $this->my_env('TEMP');
            } elseif ($this->my_env('TMP')) {
                $_temp = $this->my_env('TMP');
            } elseif ($this->my_env('windir')) {
                $_temp = $this->my_env('windir') . '\temp';
            } elseif ($this->my_env('SystemRoot')) {
                $_temp = $this->my_env('SystemRoot') . '\temp';
            }

            // handle ugly ENV var like \Temp instead of c:\Temp
            $dirs = explode("\\", realpath($_temp));
            if (strpos($_temp, ":") != 1) {
                unset($_temp);
                $_dirs = array();
                foreach ($dirs as $val) {
                    if ((boolean)$val) {
                        $_dirs[] = str_replace("/", "",  $val);
                    }
                }
                unset($dirs);
                $dirs = $_dirs;
                array_unshift($dirs, "c:");
                $_temp = $dirs[0];
                for ($i = 1; $i < count($dirs); $i++) {
                    $_temp .= "//" . $dirs[$i];
                }
            }
            $this->ptmp = $_temp;
        } else {
            $_temp = $this->my_env('TMPDIR');
            if (!$_temp) {
                if (is_writable('/tmp')) {
                    $_temp = '/tmp';
                }
            }
        }

        // If for some reason the user has no rights to access to
        // the standard tempdir, we assume that he has the right
        // to access his prefix and choose $prefix/tmp as tempdir
        if (!$_temp) {
            print "System's Tempdir failed, trying to use \$prefix/tmp ...";
            $res = $this->mkdir_p($this->prefix . '/tmp');
            if (!$res) {
                $this->bail('mkdir ' . $this->prefix . '/tmp ... failed');
            }

            $this->ptmp = $this->prefix . '/tmp';
            $_temp = tempnam($this->prefix . '/tmp', 'gope');

            $this->rm_rf($_temp);
            $this->mkdir_p($_temp, 0700);
            $ok = @chdir($this->ptmp);

            if (!$ok) { // This should not happen, really ;)
                $this->bail('chdir ' . $this->ptmp . ' ... failed');
            }

            print "ok\n";

            // Adjust TEMPDIR envvars
            if (!isset($_ENV)) {
                $_ENV = array();
            };
            $_ENV['TMPDIR'] = $_ENV['TEMP'] = $this->prefix . '/tmp';
        }
        $this->ptmp = $_temp;
    }

    function strip_magic_quotes($value)
    {
        if (ini_get('magic_quotes_gpc')) {
            return stripslashes($value);
        }
        return $value;
    }

    function rm_rf($path)
    {
        if (@is_dir($path) && is_writable($path)) {
            $dp = opendir($path);
            while ($ent = readdir($dp)) {
                if ($ent == '.' || $ent == '..') {
                    continue;
                }
                $file = $path . DIRECTORY_SEPARATOR . $ent;
                if (@is_dir($file)) {
                    $this->rm_rf($file);
                } elseif (is_writable($file)) {
                    unlink($file);
                } else {
                    echo $file . "is not writable and cannot be removed.
Please fix the permission or select a new path.\n";
                }
            }
            closedir($dp);
            return rmdir($path);
        } else {
            return @unlink($path);
        }
    }

    function mkdir_p($dir, $mode = 0777)
    {
        if (@is_dir($dir)) {
            return true;
        }
        $parent = dirname($dir);
        $ok = true;
        if (!@is_dir($parent) && $parent != $dir) {
            $ok = $this->mkdir_p(dirname($dir), $mode);
        }
        if ($ok) {
            $ok = @mkdir($dir, $mode);
            if (!$ok) {
                print "mkdir failed: $dir\n";
            }
        }
        return $ok;
    }

    function my_env($var)
    {
        if (is_array($_ENV) && isset($_ENV[$var])) {
            return $_ENV[$var];
        }
        return getenv($var);
    }

    function bail($msg = '')
    {
        error_reporting(E_ALL);
        ini_set('display_error',1);
        if ($this->ptmp && is_dir($this->ptmp)) {
            chdir($this->origpwd);
            $this->rm_rf($this->ptmp);
        }
    }

    function bootStrap($package, $tarball, $cvspath, $file)
    {
        print 'Bootstrapping: ' . $package . str_repeat('.', 23 - strlen($package));
        if (in_array(basename($file), $this->local_dir)) {
            copy(dirname(__FILE__).'/go-pear-bundle/PEAR.php', $file);
            echo "(local) ";
        } else {
            $r = 'RELEASE_' . ereg_replace('[^A-Za-z0-9]', '_',
                substr(substr($tarball[$package], strlen($package) + 1), 0, -4));
            $url = "http://cvs.php.net/co.php/$cvspath?p=1&r=$r";
            $this->download_url($url, $file, $this->http_proxy);
            echo "(remote) ";
        }
    }

    function detect_install_dirs($_prefix = null) {
        if (WINDOWS) {
            if ($_prefix === null) {
                $this->prefix = getcwd();
            } else {
                $this->prefix = $_prefix;
            }

            if (!@is_dir($this->prefix)) {
                if (@is_dir('c:\php5')) {
                    $this->prefix = 'c:\php5';
                } elseif (@is_dir('c:\php4')) {
                    $this->prefix = 'c:\php4';
                } elseif (@is_dir('c:\php')) {
                    $this->prefix = 'c:\php';
                }
            }

            $this->bin_dir   = '$prefix';
            $this->php_dir   = '$prefix\pear';
            $this->doc_dir   = '$php_dir\docs';
            $this->data_dir  = '$php_dir\data';
            $this->test_dir  = '$php_dir\tests';
            /*
             * Detects php.exe
             */
            if ($t = $this->my_env('PHP_PEAR_PHP_BIN')) {
                $this->php_bin   = $t;
            } elseif ($t = $this->my_env('PHP_BIN')) {
                $this->php_bin   = $t;
            } elseif ($t = $this->which('php')) {
                $this->php_bin = $t;
            } elseif (is_file($this->prefix.'\cli\php.exe')) {
                $this->php_bin = $this->prefix.'\cli\php.exe';
            } elseif (is_file($this->prefix.'\php.exe')) {
                $this->php_bin = $this->prefix.'\php.exe';
            }
            if ($this->php_bin && !is_file($this->php_bin)) {
                $this->php_bin = '';
            } else {
                if (!ereg(":", $this->php_bin)) {
                    $this->php_bin = getcwd() . DIRECTORY_SEPARATOR . $this->php_bin;
                }
            }
            if (!is_file($this->php_bin)) {
                if (is_file('c:/php/cli/php.exe')) {
                    $this->php_bin = 'c:/php/cli/php.exe';
                } elseif (is_file('c:/php5/php.exe')) {
                    $this->php_bin = 'c:/php5/php.exe';
                } elseif (is_file('c:/php4/cli/php.exe')) {
                    $this->php_bin = 'c:/php4/cli/php.exe';
                }
            }
        } else {
            if ($_prefix === null) {
                $this->prefix    = dirname(PHP_BINDIR);
            } else {
                $this->prefix = $_prefix;
            }
            $this->bin_dir   = '$prefix/bin';
            $this->php_dir   = '$prefix/share/pear';
            $this->doc_dir   = '$php_dir/docs';
            $this->data_dir  = '$php_dir/data';
            $this->test_dir  = '$php_dir/tests';
            // check if the user has installed PHP with PHP or GNU layout
            if (@is_dir("$this->prefix/lib/php/.registry")) {
                $this->php_dir = '$this->prefix/lib/php';
            } elseif (@is_dir("$this->prefix/share/pear/lib/.registry")) {
                $this->php_dir = '$prefix/share/pear/lib';
                $this->doc_dir   = '$prefix/share/pear/docs';
                $this->data_dir  = '$prefix/share/pear/data';
                $this->test_dir  = '$prefix/share/pear/tests';
            } elseif (@is_dir("$this->prefix/share/php/.registry")) {
                $this->php_dir = '$prefix/share/php';
            }
        }
    }

    function download_url($url, $destfile = null, $proxy = null)
    {
        $use_suggested_filename = ($destfile === null);
        if ($use_suggested_filename) {
            $destfile = basename($url);
        }
        $tmp = parse_url($url);
        if (empty($tmp['port'])) {
            $tmp['port'] = 80;
        }
        if (empty($proxy)) {
            $fp = fsockopen($tmp['host'], $tmp['port'], $errno, $errstr);
            //print "\nconnecting to $tmp[host]:$tmp[port]\n";
        } else {
            $tmp_proxy = parse_url($proxy);
            $phost     = $tmp_proxy['host'];
            $pport     = $tmp_proxy['port'];
            $fp = fsockopen($phost, $pport, $errno, $errstr);
            //print "\nconnecting to $phost:$pport\n";
        }
        if (!$fp) {
            $this->bail("download of $url failed: $errstr ($errno)\n");
        }
        if (empty($proxy)) {
            $path = $tmp['path'];
        } else {
            $path = "http://$tmp[host]:$tmp[port]$tmp[path]";
        }
        if (isset($tmp['query'])) {
            $path .= "?$tmp[query]";
        }
        if (isset($tmp['fragment'])) {
            $path .= "#$tmp[fragment]";
        }
        $request = "GET $path HTTP/1.0\r\nHost: $tmp[host]:$tmp[port]\r\n".
            "User-Agent: go-pear\r\n";

        if (!empty($proxy) && $tmp_proxy['user'] != '') {
            $request .= 'Proxy-Authorization: Basic ' .
                        base64_encode($tmp_proxy['user'] . ':' . $tmp_proxy['pass']) . "\r\n";
            //print "\nauthenticating against proxy with : user = ${tmp_proxy['user']} \n";
            //print "and pass = ${tmp_proxy['pass']}\n";
        } // if
        $request .= "\r\n";
        fwrite($fp, $request);
        $cdh = "content-disposition:";
        $cdhl = strlen($cdh);
        $content_length = 0;
        while ($line = fgets($fp, 2048)) {
            if (trim($line) == '') {
                break;
            }
            if (preg_match('/^Content-Length: (.*)$/i', $line, $matches)) {
                $content_length = trim($matches[1]);
            };
            if ($use_suggested_filename && !strncasecmp($line, $cdh, $cdhl)) {
                if (eregi('filename="([^"]+)"', $line, $matches)) {
                    $destfile = basename($matches[1]);
                }
            }
        }
        if ($content_length) {
            $this->displayHTMLSetDownload($destfile);
        };
        $wp = fopen($destfile, "wb");
        if (!$wp) {
            $this->bail("could not open $destfile for writing\n");
        }
        $bytes_read = 0;
        $progress = 0;
        while ($data = fread($fp, 2048)) {
            fwrite($wp, $data);
            $bytes_read += strlen($data);
            if ($content_length != 0 && floor($bytes_read * 10 / $content_length) != $progress) {
                $progress = floor($bytes_read * 10 / $content_length);
                $this->displayHTMLDownloadProgress($progress * 10);
            };
        }
        fclose($fp);
        fclose($wp);
        return $destfile;
    }

    function downloadPackages($installer_packages, &$tarball, &$progress, $progressgoal)
    {
        $start = $progress;
        $increment = ($progressgoal - $start - 0.5) / count($installer_packages);
        foreach ($installer_packages as $pkg) {
            foreach($this->local_dir as $file) {
                if (substr($file, 0, strlen(str_replace('-stable', '', $pkg))) ==
                      str_replace('-stable', '', $pkg)) {
                    $pkg = str_replace('-stable', '', $pkg);
                    echo str_pad("Using local package: $pkg", max(38, 21+strlen($pkg)+4), '.');
                    copy(dirname(__FILE__).'/go-pear-bundle/' . $file, $file);
                    $tarball[$pkg] = $file;
                    echo "ok\n";
                    $progress = (int) round($progress + $increment);
                    $this->displayHTMLProgress($progress);
                    continue 2;
                };
            };

            $msg = str_pad("Downloading package: $pkg", max(38, 21+strlen($pkg)+4), '.');
            print $msg;
            $url = sprintf($this->urltemplate, $pkg);
            $pkg = str_replace('-stable', '', $pkg);
            $tarball[$pkg] = $this->download_url($url, null, $this->http_proxy);
            print "ok\n";
            $progress = (int) round($progress + $increment);
            $this->displayHTMLProgress($progress);
        }
    }

    /**
     * Get the php.ini file used with the current
     * process or with the given php.exe
     *
     * Horrible hack, but well ;)
     *
     * Not used yet, will add the support later
     * @author Pierre-Alain Joye <paj@pearfr.org>
     */
    function getPhpiniPath()
    {
        $pathIni = get_cfg_var('cfg_file_path');
        if ($pathIni && is_file($pathIni)) {
            return $pathIni;
        }

        // Oh well, we can keep this too :)
        // I dunno if get_cfg_var() is safe on every OS
        if (WINDOWS) {
            // on Windows, we can be pretty sure that there is a php.ini
            // file somewhere
            do {
                $php_ini = PHP_CONFIG_FILE_PATH . DIRECTORY_SEPARATOR . 'php.ini';
                if (@file_exists($php_ini)) {
                    break;
                }
                $php_ini = 'c:\winnt\php.ini';
                if (@file_exists($php_ini)) {
                    break;
                }
                $php_ini = 'c:\windows\php.ini';
            } while (false);
        } else {
            $php_ini = PHP_CONFIG_FILE_PATH . DIRECTORY_SEPARATOR . 'php.ini';
        }

        if (@is_file($php_ini)) {
            return $php_ini;
        }

        // We re running in hackz&troubles :)
        ob_implicit_flush(false);
        ob_start();
        phpinfo(INFO_GENERAL);
        $strInfo = ob_get_contents();
        ob_end_clean();
        ob_implicit_flush(true);

        if (php_sapi_name() != 'cli') {
            $strInfo = strip_tags($strInfo,'<td>');
            $arrayInfo = explode("</td>", $strInfo );
            $cli = false;
        } else {
            $arrayInfo = explode("\n", $strInfo);
            $cli = true;
        }

        foreach ($arrayInfo as $val) {
            if (strpos($val,"php.ini")) {
                if ($cli) {
                    list(,$pathIni) = explode('=>', $val);
                } else {
                    $pathIni = strip_tags(trim($val));
                }
                $pathIni = trim($pathIni);
                if (is_file($pathIni)) {
                    return $pathIni;
                }
            }
        }

        return false;
    }

    function mergeGoPearBundle()
    {
        $dh = false;
        if (file_exists(dirname(__FILE__).'/go-pear-bundle') ||
              is_dir(dirname(__FILE__).'/go-pear-bundle')) {
            $dh = @opendir(dirname(__FILE__).'/go-pear-bundle');
        }
        $local_dir = array();
        if ($dh) {
            while($file = @readdir($dh)) {
                if ($file == '.' || $file == '..' ||
                      !is_file(dirname(__FILE__).'/go-pear-bundle/'.$file)) {
                    continue;
                };
                $this->local_dir[] = $file;
            };
        }
    }

    function displayHTMLSetDownload()
    {
    }

    function displayHTMLProgress()
    {
    }

    function displayHTMLDownloadProgress()
    {
    }
}

class Gopear_CLI extends Gopear_Base
{
    var $tty;
    function Gopear_CLI()
    {
        parent::Gopear_Base();
        ini_set('html_errors', 0);
        define('WIN32GUI', WINDOWS && php_sapi_name() == 'cli' && $this->which('cscript'));
        $this->tty = WINDOWS ? @fopen('\con', 'r') : @fopen('/dev/tty', 'r');

        if (!$this->tty) {
            $this->tty = fopen('php://stdin', 'r');
        }
    }

    function bail($msg = '')
    {
        parent::bail();
        die($msg);
    }

    function postProcessConfigVars()
    {
        parent::postProcessConfigVars();
        $msg = "The following PEAR packages are bundled with PHP: " .
            implode(', ', $this->pfc_packages);
        print "\n" . wordwrap($msg, 75) . ".\n";
        print "Would you like to install these as well? [Y/n] : ";
        $this->install_pfc = !stristr(fgets($this->tty, 1024), "n");
        print "\n";
    }

    function displayPreamble()
    {
        if (WINDOWS) {
            /*
             * Checks PHP SAPI version under windows/CLI
             */
            if ($this->php_bin == '') {
                print "
We do not find any php.exe, please select the php.exe folder (CLI is
recommanded, usually in c:\php\cli\php.exe)
";
                $php_bin_set = false;
            } elseif (strlen($this->php_bin)) {
                $this->php_bin_sapi = $this->win32DetectPHPSAPI();
                $php_bin_set = true;
                switch ($this->php_bin_sapi) {
                    case 'cli':
                    break;
                    case 'cgi':
                    case 'cgi-fcgi':
                        print "
*NOTICE*
We found php.exe under $this->php_bin, it uses a $this->php_bin_sapi SAPI. PEAR commandline
tool works well with it, if you have a CLI php.exe available, we
recommand to use it.
";
                    break;
                    default:
                        print "
*WARNING*
We found php.exe under $this->php_bin, it uses an unknown SAPI. PEAR commandline
tool has not been tested with it, if you have a CLI (or CGI) php.exe available,
we strongly recommand to use it.

";
                    break;
                }
            }
        }
    }

    function setupConfigDesc()
    {
        if (WINDOWS) {
            $this->config_desc['php_bin'] = 'directory containing php.exe';
        }
    }

    function doGetConfigVars()
    {
        while (true) {
            static $php_bin_set = false;
            $disp_php_dir = $this->php_dir;
            foreach ($this->config_vars as $m => $var2) {
                $disp_php_dir = str_replace('$'.$var2, $this->$var2, $disp_php_dir);
            }
            $extra = '';
            if ($disp_php_dir != $this->php_dir) {
                $extra = " = $disp_php_dir";
            }
            print "
Below is a suggested file layout for your new PEAR installation.  To
change individual locations, type the number in front of the
directory.  Type 'all' to change all of them or simply press Enter to
accept these locations.

*** \$prefix = $this->prefix
*** \$php_dir = $this->php_dir$extra
";

            foreach ($this->config_vars as $n => $var) {
                printf("%2d. $this->descfmt : %s\n", $n, $this->config_desc[$var], $this->$var);
            }

            print "\n$this->first-$this->last, 'all' or Enter to continue: ";
            $tmp = trim(fgets($this->tty, 1024));
            if ( empty($tmp) ) {
                if( WINDOWS && !$php_bin_set ){
                    echo "**ERROR**
Please, enter the php.exe path.

";
                } else {
                    break;
                }
            }
            if (isset($this->config_vars[(int)$tmp])) {
                $var = $this->config_vars[(int)$tmp];
                $desc = $this->config_desc[$var];
                $current = $this->$var;
                if (WIN32GUI){
                    $tmp = $this->win32BrowseForFolder("Choose a Folder for $desc [$current] :");
                } else {
                    print "$desc [$current] : ";
                    $tmp = trim(fgets($this->tty, 1024));
                }
                $old = $this->$var;
                $this->$var = $$var = $tmp;
                if (WINDOWS && $var=='php_bin') {
                    if (file_exists($tmp . DIRECTORY_SEPARATOR . 'php.exe')) {
                        $tmp = $tmp . DIRECTORY_SEPARATOR . 'php.exe';
                        $this->php_bin_sapi = $this->win32DetectPHPSAPI();
                        if ($this->php_bin_sapi=='cgi'){
                            print "
******************************************************************************
NOTICE! We found php.exe under $this->php_bin, it uses a $this->php_bin_sapi SAPI.
PEAR commandline tool works well with it.
If you have a CLI php.exe available, we recommand to use it.

";
                        } elseif ($this->php_bin_sapi=='unknown') {
                            print "
******************************************************************************
WARNING! We found php.exe under $this->php_bin, it uses an $this->php_bin_sapi SAPI.
PEAR commandline tool has NOT been tested with it.
If you have a CLI (or CGI) php.exe available, we strongly recommand to use it.

";
                        }
                        echo "php.exe (sapi: $this->php_bin_sapi) found.\n\n";
                        $php_bin_set = true;
                    } else {
                        echo "**ERROR**: no php.exe found in this folder.\n";
                        $tmp='';
                    }
                }

                if (!empty($tmp) ) {
                    $this->$var = $tmp;
                }
            } elseif ($tmp == 'all') {
                foreach ($this->config_vars as $n => $var) {
                    $desc = $this->config_desc[$var];
                    $current = $this->$var;
                    print "$desc [$current] : ";
                    $tmp = trim(fgets($this->tty, 1024));
                    if (!empty($tmp)) {
                        $this->$var = $tmp;
                    }
                }
            }
        }
    }

    function doSetupConfigVars()
    {
        $local = isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'local';
        if ($local) {
            $local = "
Running in local install mode
";
        } elseif (WINDOWS) {
            $local = "
Use 'php " . $_SERVER['argv'][0] . " local' to install a local copy of PEAR.
";
        }
        print "Welcome to go-pear!

Go-pear will install the 'pear' command and all the files needed by
it.  This command is your tool for PEAR installation and maintenance.
$local
Go-pear also lets you download and install the PEAR packages: " .
        implode(', ', $this->pfc_packages) . ".


If you wish to abort, press Control-C now, or press Enter to continue: ";

        fgets($this->tty, 1024);

        print "\n";

            print "HTTP proxy (http://user:password@proxy.myhost.com:port), or Enter for none:";

        if (!empty($this->http_proxy)) {
            print " [$this->http_proxy]:";
        }
        print " ";
        $tmp = trim(fgets($this->tty, 1024));
        if (!empty($tmp)) {
            $this->http_proxy = $tmp;
        }
        $this->setupConfigVars();
    }

    function finishInstall()
    {
        $sep = WINDOWS ? ';' : ':';
        $include_path = explode($sep, ini_get('include_path'));
        if (WINDOWS) {
            $found = false;
            $t = strtolower($this->php_dir);
            foreach ($include_path as $path) {
                if ($t == strtolower($path)) {
                    $found = true;
                    break;
                }
            }
        } else {
            $found = in_array($this->php_dir, $include_path);
        }
        if (!$found) {
            print "
******************************************************************************
WARNING!  The include_path defined in the currently used php.ini does not
contain the PEAR PHP directory you just specified:
<$this->php_dir>
If the specified directory is also not in the include_path used by
your scripts, you will have problems getting any PEAR packages working.
";

            if ($php_ini = $this->getPhpiniPath()) {
                print "\n\nWould you like to alter php.ini <$php_ini>? [Y/n] : ";
                $alter_phpini = !stristr(fgets($this->tty, 1024), "n");
                if ($alter_phpini) {
                    $this->alterPhpIni($php_ini);
                } else {
                    if (WINDOWS) {
                        print "
Please look over your php.ini file to make sure
$php_dir is in your include_path.";
                    } else {
                        print "
I will add a workaround for this in the 'pear' command to make sure
the installer works, but please look over your php.ini or Apache
configuration to make sure $php_dir is in your include_path.
";
                    }
                }
            }

        print "
Current include path           : ".ini_get('include_path')."
Configured directory           : $this->php_dir
Currently used php.ini (guess) : $this->php_ini
";

            print "Press Enter to continue: ";
            fgets($this->tty, 1024);
        }

        $pear_cmd = $this->bin_dir . DIRECTORY_SEPARATOR . 'pear';
        $pear_cmd = WINDOWS ? strtolower($pear_cmd).'.bat' : $pear_cmd;

        // check that the installed pear and the one in tha path are the same (if any)
        $pear_old = $this->which(WINDOWS ? 'pear.bat' : 'pear', $this->bin_dir);
        if ($pear_old && ($pear_old != $pear_cmd)) {
            // check if it is a link or symlink
            $islink = WINDOWS ? false : is_link($pear_old) ;
            if ($islink && readlink($pear_old) != $pear_cmd) {
                print "\n** WARNING! The link $pear_old does not point to the " .
                      "installed $pear_cmd\n";
            } elseif (is_writable($pear_old) && !is_dir($pear_old)) {
                rename($pear_old, "{$pear_old}_old");
                print "\n** WARNING! Backed up old pear to {$pear_old}_old\n";
            } else {
                print "\n** WARNING! Old version found at $pear_old, please remove it or ".
                      "be sure to use the new $pear_cmd command\n";
            }
        }

        print "\nThe 'pear' command is now at your service at $pear_cmd\n";

        // Alert the user if the pear cmd is not in PATH
        $old_dir = $pear_old ? dirname($pear_old) : false;
        if (!$this->which('pear', $old_dir)) {
            print "
** The 'pear' command is not currently in your PATH, so you need to
** use '$pear_cmd' until you have added
** '$this->bin_dir' to your PATH environment variable.

";

        print "Run it without parameters to see the available actions, try 'pear list'
to see what packages are installed, or 'pear help' for help.

For more information about PEAR, see:

  http://pear.php.net/faq.php
  http://cvs.php.net/co.php/pearweb/doc/pear_package_manager.txt?p=1
  http://pear.php.net/manual/

Thanks for using go-pear!

";
        }

        if (WINDOWS) {
            $this->win32CreateRegEnv();
        }
    }
/*
 * Not optimized, but seems to work, if some nice
 * peardev will test it? :)
 *
 * @Author Pierre-Alain Joye <paj@pearfr.org>
 */
    function alterPhpIni($pathIni='')
    {
        $iniSep = WINDOWS?';':':';

        if ($pathIni=='') {
            $pathIni =  $this->getPhpiniPath();
        }

        $arrayIni = file($pathIni);
        $i=0;
        $found=0;

        // Looks for each active include_path directives
        foreach ( $arrayIni as $iniLine ) {
            $iniLine = trim($iniLine);
            $iniLine = str_replace(array("\n","\r"),array(),$iniLine);
            if( preg_match("/^include_path/",$iniLine) ){
                $foundAt[] = $i;
                $found++;
            }
            $i++;
        }

        if ( $found ) {
            $includeLine = $arrayIni[$foundAt[0]];
            list(,$currentPath)=explode('=',$includeLine);

            $currentPath = trim($currentPath);
            if(substr($currentPath,0,1)=='"'){
                $currentPath = substr($currentPath,1,strlen($currentPath)-2);
            }

            $arrayPath = explode($iniSep, $currentPath);
            if( $arrayPath[0]=='.' ){
                $newPath[0] = '.';
                $newPath[1] = $this->php_dir;
                array_shift($arrayPath);
            } else {
                $newPath[0] = $this->php_dir;
            }

            foreach( $arrayPath as $path ){
                $newPath[]= $path;
            }
        } else {
            $newPath[0] = '.';
            $newPath[1] = $this->php_dir;

        }
        $nl = WINDOWS?"\r\n":"\n";
        $includepath = 'include_path="'.implode($iniSep,$newPath).'"';
        $newInclude =   "$nl$nl;***** Added by go-pear$nl".
                        $includepath.
                        $nl.";*****".
                        $nl.$nl;

        $arrayIni[$foundAt[0]] =  $newInclude;

        for( $i=1; $i<$found; $i++){
            $arrayIni[$foundAt[$i]]=';'.trim($arrayIni[$foundAt[$i]]);
        }

        $newIni = implode("",$arrayIni);
        if ( !($fh = @fopen($pathIni, "wb+")) ){
            $prefixIni = $this->prefix . DIRECTORY_SEPARATOR . "php.ini-gopear";
            $fh = fopen($prefixIni, "wb+");
            if ( !$fh ) {
                echo
"
******************************************************************************
WARNING!  I cannot write to $pathIni nor in $this->prefix/php.ini-gopear. Please
modify manually your php.ini by adding:

$includepath

";
                return false;
            } else {
                fwrite($fh, $newIni, strlen($newIni));
                fclose($fh);
                echo
"
******************************************************************************
WARNING!  I cannot write to $pathIni, but I succesfully created a php.ini
under <$this->prefix/php.ini-gopear>. Please replace the file <$pathIni> with
<$prefixIni> or modify your php.ini by adding:

$includepath

";

            }
        } else {
            fwrite($fh, $newIni, strlen($newIni));
            fclose($fh);
            echo "
php.ini <$pathIni> include_path updated.
";
        }
        return true;
    }
    /**
     * Create a vbs script to browse the getfolder dialog, called
     * by cscript, if it's available.
     * $label is the label text in the header of the dialog box
     *
     * TODO:
     * - Do not show Control panel
     * - Replace WSH with calls to w32 as soon as callbacks work
     * @author Pierrre-Alain Joye
     */
    function win32BrowseForFolder($label)
    {
        static $wshSaved=false;
        static $cscript='';
    $wsh_browserfolder = 'Option Explicit
Dim ArgObj, var1, var2, sa, sFld
Set ArgObj = WScript.Arguments
Const BIF_EDITBOX = &H10
Const BIF_NEWDIALOGSTYLE = &H40
Const BIF_RETURNONLYFSDIRS   = &H0001
Const BIF_DONTGOBELOWDOMAIN  = &H0002
Const BIF_STATUSTEXT         = &H0004
Const BIF_RETURNFSANCESTORS  = &H0008
Const BIF_VALIDATE           = &H0020
Const BIF_BROWSEFORCOMPUTER  = &H1000
Const BIF_BROWSEFORPRINTER   = &H2000
Const BIF_BROWSEINCLUDEFILES = &H4000
Const OFN_LONGNAMES = &H200000
Const OFN_NOLONGNAMES = &H40000
Const ssfDRIVES = &H11
Const ssfNETWORK = &H12
Set sa = CreateObject("Shell.Application")
var1=ArgObj(0)
Set sFld = sa.BrowseForFolder(0, var1, BIF_EDITBOX + BIF_VALIDATE + BIF_BROWSEINCLUDEFILES + BIF_RETURNFSANCESTORS+BIF_NEWDIALOGSTYLE , ssfDRIVES )
if not sFld is nothing Then
    if not left(sFld.items.item.path,1)=":" Then
        WScript.Echo sFld.items.item.path
    Else
        WScript.Echo "invalid"
    End If
Else
    WScript.Echo "cancel"
End If
';
        if( !$wshSaved){
            $cscript = $this->ptmp . DIRECTORY_SEPARATOR . "bf.vbs";
            $fh = fopen($cscript, "wb+");
            fwrite($fh, $wsh_browserfolder, strlen($wsh_browserfolder));
            fclose($fh);
            $wshSaved  = true;
        }
        exec('cscript ' . $cscript . ' "' . $label . '" //noLogo', $arPath);
        if ($arPath[0]=='' || $arPath[0]=='cancel') {
            return '';
        } elseif ($arPath[0]=='invalid') {
            echo "Invalid Path.\n";
            return '';
        }
        return $arPath[0];
    }

    /**
     * Generates a registry addOn for Win32 platform
     * This addon set PEAR environment variables
     * @author Pierrre-Alain Joye
     */
    function win32CreateRegEnv()
    {
        $nl = "\r\n";
        $reg ='REGEDIT4'.$nl.
                '[HKEY_CURRENT_USER\Environment]'. $nl .
                '"PHP_PEAR_SYSCONF_DIR"="' . addslashes($this->prefix) . '"' . $nl .
                '"PHP_PEAR_INSTALL_DIR"="' . addslashes($this->php_dir) . '"' . $nl .
                '"PHP_PEAR_DOC_DIR"="' . addslashes($this->doc_dir) . '"' . $nl .
                '"PHP_PEAR_BIN_DIR"="' . addslashes($this->bin_dir) . '"' . $nl .
                '"PHP_PEAR_DATA_DIR"="' . addslashes($this->data_dir) . '"' . $nl .
                '"PHP_PEAR_PHP_BIN"="' . addslashes($this->php_bin) . '"' . $nl .
                '"PHP_PEAR_TEST_DIR"="' . addslashes($this->test_dir) . '"' . $nl;

        $fh = fopen($this->prefix . DIRECTORY_SEPARATOR . 'PEAR_ENV.reg', 'wb');
        if($fh){
            fwrite($fh, $reg, strlen($reg));
            fclose($fh);
            echo "

* WINDOWS ENVIRONMENT VARIABLES *
For convenience, a REG file is available under $this->prefix\\PEAR_ENV.reg .
This file creates ENV variables for the current user.

Double-click this file to add it to the current user registry.

";
        }
    }
}

class Gopear_Web extends Gopear_Base
{
    var $installer_packages = array(
        'PEAR-stable',
        'Archive_Tar-stable',
        'Console_Getopt-stable',
        'XML_RPC-stable',
        'Pager',
        'HTML_Template_IT',
        'Net_UserAgent_Detect',
        'PEAR_Frontend_Web-0.4',
        );

    var $webfrontend_file;
    var $wwwerrors;

    function Gopear_Web()
    {
        parent::Gopear_Base();
        $this->setupWebInstaller();
        ini_set('html_errors', 1);
        define('WIN32GUI', false);
    }

    function loadZlib()
    {
        // In Web context we could be in multithread env which makes dl() end up with a fatal error.
    }

    /**
     * Try to detect the kind of SAPI used by the
     * the given php.exe.
     * @author Pierrre-Alain Joye
     */
    function win32DetectPHPSAPI()
    {
        return $this->php_sapi_name;
    }

    function bail($msg = '')
    {
        parent::bail();
        $msg = @ob_get_contents() ."\n\n". $msg;
        @ob_end_clean();
        if (!empty($msg)) {
            //$this->displayHTML('error', $msg);
        }
        exit;
    }

    function doGetConfigVars()
    {
    }

    function setupConfigDesc()
    {
        $this->config_desc['cache_dir'] = 'PEAR Installer cache directory';
        $this->config_desc['cache_ttl'] = 'Cache TimeToLive';
        $this->config_desc['webfrontend_file'] = 'Filename of WebFrontend';
        $this->config_desc['php_bin'] = "php.exe path, optional (CLI command tools)";
    }

    function displayHTML($page = 'Welcome', $data = array())
    {
        $this->displayHTMLHeader();

?>
<a name="TOP" /></a>
<table border="0" cellspacing="0" cellpadding="0" height="48" width="100%">
  <tr bgcolor="#339900">
    <td align="left" width="120">
      <img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=pearlogo" width="104" height="50" vspace="2" hspace="5" alt="PEAR">
    </td>
    <td align="left" valign="middle" width="20">
      &nbsp;
    </td>
    <td align="left" valign="middle">
      <span class="Headline">Go-PEAR</span>
    </td>
  </tr>

  <tr bgcolor="#003300"><td colspan="3"></td></tr>

  <tr bgcolor="#006600">
    <td align="right" valign="top" colspan="3">
        <span style="color: #ffffff">Version <?php echo GO_PEAR_VER; ?></span>&nbsp;<br />
    </td>
  </tr>

  <tr bgcolor="#003300"><td colspan="3"></td></tr>
</table>
<table cellpadding="0" cellspacing="0" width="100%">
 <tr valign="top">
  <td bgcolor="#f0f0f0" width="100">
   <table width="200" border="0" cellpadding="4" cellspacing="0">
    <tr valign="top">
     <td style="font-size: 90%" align="left" width="200">
       <br><br>
       <img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=smallpear" border="0">
       <a href="<?php echo basename(__FILE__); ?>?step=Welcome&restart=1" <?php if ($page == 'Welcome') echo ' class="green"'; ?>>
         Welcome to Go-PEAR
       </a><br/>

       <img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=smallpear" border="0">
       <a href="<?php echo basename(__FILE__); ?>?step=config" <?php if ($page == 'config') echo ' class="green"'; ?>>
         Configuration
       </a><br/>

       <img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=smallpear" border="0">
<?php if ($page == 'install') echo '<span class="green">'; ?>
         Complete installation<br/>
<?php if ($page == 'install') echo '</span>'; ?>

     </td>
    </tr>
   </table>
  </td>
  <td bgcolor="#cccccc" width="1" background="/gifs/checkerboard.gif"></td>
  <td>
   <table width="100%" cellpadding="10" cellspacing="0">
    <tr>
     <td valign="top">
<table border="0">
<tr>
  <td width="20">
  </td>
  <td>
<?php
        if ($page == 'error') {
?>
            <span class="title">Error</span><br/>
            <br/>
<?php
            $value = $data;
            if (preg_match('/ok$/', $value)) {
                $value = preg_replace('/(ok)$/', '<span class="green">\1</span>', $value);
            }
            if (preg_match('/failed$/', $value)) {
                $value = preg_replace('/(failed)$/', '<span style="color: #ff0000">\1</span>', $value);
            }
            if (preg_match('/^install ok:/', $value)) {
                $value = preg_replace('/^(install ok:)/', '<span class="green">\1</span>', $value);
            }
            if (preg_match('/^Warning:/', $value)) {
                $value = '<span style="color: #ff0000">'.$value.'</span>';
            }

            echo nl2br($value);
        } elseif ($page == 'Welcome') {
?>
            <span class="title">Welcome to go-pear <?php echo GO_PEAR_VER; ?>!</span><br/>
            <br/>
            Go-pear will install the Web Frontend of the PEAR Installer and all the needed <br/>
            files. This frontend is your tool for PEAR installation and maintenance.<br/>
            <br/>
            Go-pear also lets you download and install these PEAR packages: <?php
            echo implode(', ', $this->pfc_packages); ?>.<br/>
            <br/>
            <a href="<?php echo basename(__FILE__); ?>?step=config" class="green">Next &gt;&gt;</a>
<?php
        } elseif ($page == 'config') {
            if (!empty($this->http_proxy)) {
                list($proxy_host, $proxy_port) = explode(':', $this->http_proxy);
            } else {
                $proxy_host = $proxy_port = '';
            }
?>
            <form action="<?php echo basename(__FILE__);?>?step=install" method="post">
            <span class="title">Configuration</span><br/>
            <br/>
<?php
            if (count($this->wwwerrors)) {
                foreach ($this->wwwerrors as $_error) {
                    echo '<span style="color: #ff0000">' . $_error . '</span><br/>';
                }
                echo "<br/>";
            }
?>
            HTTP proxy (host:port):
            <input type="text" name="proxy[host]" value="<?php echo $proxy_host;?>">
            <input type="text" name="proxy[port]" value="<?php echo $proxy_port;?>" size="6">
            <br/><br/><hr/><br/>
            Below is a suggested file layout for your new PEAR installation. <br/>
            <br/>
            <table border="0">
              <TR>
                <TD valign="top"><img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=note" border="0"></TD>
                <TD>
                  <span class="green">
                    <b>Note:</b> Make sure that PHP has the permission to access the specified<br/>
                    directories.<br/><br/>
                  </span>
                </TD>
              </TR>
            <tr>
             <td valign="top"><img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=note" border="0"></td>
                <td>
                  <span class="green">
                    <b>Hint:</b> $prefix and $php_dir work just like PHP variables<br />
                    $prefix is the value of &quot;1. Installation prefix&quot;<br />
                    (the current value of $prefix is <em><?php echo $this->prefix; ?></em>.<br />
                    and so $prefix/PEAR is <em><?php echo $this->prefix; ?>/PEAR</em>).
                  </span>
                </td>
            </tr>
            </table>
            <TABLE border="0">
<?php
            // Display error messages
            if (isset($this->www_errors) && sizeof($this->www_errors) ) {
                echo "<tr><td>";
                echo '<span class="red">ERROR(S):</span>';
                echo "</td></tr>";
                foreach ($this->www_errors as $n => $var) {
                    echo "<tr><td>";
                    echo '<span class="red">' . $this->config_desc[$n] . ': </span>';
                    echo "</td><td>";
                    echo '<span class="red">' . $this->www_errors[$n] . '</span>';
                    echo "<br>\n";
                    echo "</td></tr>\n";
                }
            }

            foreach ($this->config_vars as $n => $var) {
                printf('<tr><td>%d. %s</td><td><input type="text" name="config[%s]" value="%s"></td></tr>',
                $n,
                $this->config_desc[$var],
                $var,
                $this->$var);
            }
?>
            </TABLE>
            <br/><hr/><br/>
            The following PEAR packages are common ones, and can be installed<br/>
            by go-pear too: <br/>
<?php echo implode(', ', $this->pfc_packages);?>.<br/>
            <input type="checkbox" name="install_pfc" <?php if($this->install_pfc) echo 'checked';?>> Install those too<br/>
            <br/><br/>
            <table border="0">
              <TR>
                <TD valign="top"><img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=note" border="0"></TD>
                <TD>
                  <span class="green">
                      <b>Note:</b> Installation might take some time, because go-pear has to download<br/>
                      all needed files from pear.php.net. Just be patient and wait for the next<br/>
                      page to load.<br/>
                  </span>
                </TD>
              </TR>
            </table>
            <br>
            <input type="checkbox" name="BCmode" id="BCmode" checked> Compatibility-Mode for old non-DOM Browsers<br/>
            <script type="text/javascript">
            <!--
                if (document.getElementById('BCmode')) {
                    document.getElementById('BCmode').checked = 0;
                };
            // -->
            </script>

<?php
            if (WINDOWS && phpversion() == '4.1.1') {
?>
                    <table border="0">
                      <TR>
                        <TD valign="top"><img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=note" border="0"></TD>
                        <TD>
                          <span style="color: #ff0000">
                              <b>Warning:</b> Your PHP version (4.1.1) might be imcompatible with go-pear due to a bug<br/>
                              in your PHP binary. If the installation crashes you might want to update your PHP version.</br>
                          </span>
                        </TD>
                      </TR>
                    </table>
<?php
            }
?>
            <br/>
            <input type="submit" value="Install" onClick="javascript: submitButton.value='Downloading and installing ... please wait ...'" name="submitButton">
            </form>
<?php
        } elseif ($page == 'install') {
?>
            <span class="title">Installation Complete - Summary</span><br/>
<?php
            $this->displayHTMLInstallationSummary($data);
        } elseif ($page == 'preinstall') {
?>
            <span class="title">Installation in progress ...</span><br/>
            <br/>
            <script language="javascript">
            <!--

                var progress;
                var downlodprogress;
                progress = 0;
                downloadprogress = 0;

                function setprogress(value)
                {
                    progress = value;

                    prog = document.getElementById('installation_progress');
                    prog.innerHTML = progress + " %";
                    progress2 = progress / 10;
                    progress2 = Math.floor(progress2);
                    for (i=0; i < 10; i++)
                        document.getElementById('progress_cell_'+i).style.backgroundColor = "#cccccc";
                    switch(progress2)
                    {
                        case 10:
                            document.getElementById('progress_cell_9').style.backgroundColor = "#006600";
                        case  9:
                            document.getElementById('progress_cell_8').style.backgroundColor = "#006600";
                        case  8:
                            document.getElementById('progress_cell_7').style.backgroundColor = "#006600";
                        case  7:
                            document.getElementById('progress_cell_6').style.backgroundColor = "#006600";
                        case  6:
                            document.getElementById('progress_cell_5').style.backgroundColor = "#006600";
                        case  5:
                            document.getElementById('progress_cell_4').style.backgroundColor = "#006600";
                        case  4:
                            document.getElementById('progress_cell_3').style.backgroundColor = "#006600";
                        case  3:
                            document.getElementById('progress_cell_2').style.backgroundColor = "#006600";
                        case  2:
                            document.getElementById('progress_cell_1').style.backgroundColor = "#006600";
                        case  1:
                            document.getElementById('progress_cell_0').style.backgroundColor = "#006600";
                    };
                }

                function addprogress(value)
                {
                    progress += value;
                    setprogress(progress);
                }

                function setdownloadfile(value)
                {
                    setdownloadprogress(0);

                    prog = document.getElementById('download_file');
                    prog.innerHTML = 'Downloading '+value+' ...';
                };

                function setdownloadprogress(value)
                {
                    downloadprogress = value;

                    prog = document.getElementById('download_progress');
                    prog.innerHTML = downloadprogress + " %";
                    progress2 = downloadprogress / 10;
                    progress2 = Math.floor(progress2);
                    for (i=0; i < 10; i++)
                        document.getElementById('download_progress_cell_'+i).style.backgroundColor = "#cccccc";
                    switch(progress2)
                    {
                        case 10:
                            document.getElementById('download_progress_cell_9').style.backgroundColor = "#006600";
                        case  9:
                            document.getElementById('download_progress_cell_8').style.backgroundColor = "#006600";
                        case  8:
                            document.getElementById('download_progress_cell_7').style.backgroundColor = "#006600";
                        case  7:
                            document.getElementById('download_progress_cell_6').style.backgroundColor = "#006600";
                        case  6:
                            document.getElementById('download_progress_cell_5').style.backgroundColor = "#006600";
                        case  5:
                            document.getElementById('download_progress_cell_4').style.backgroundColor = "#006600";
                        case  4:
                            document.getElementById('download_progress_cell_3').style.backgroundColor = "#006600";
                        case  3:
                            document.getElementById('download_progress_cell_2').style.backgroundColor = "#006600";
                        case  2:
                            document.getElementById('download_progress_cell_1').style.backgroundColor = "#006600";
                        case  1:
                            document.getElementById('download_progress_cell_0').style.backgroundColor = "#006600";
                    };
                };

            // -->
            </script>
            <table style="border-width: 1px; border-color: #000000" cellspacing="0" cellpadding="0">
            <tr>
              <td>
                <table border="0">
                  <tr>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_0">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_1">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_2">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_3">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_4">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_5">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_6">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_7">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_8">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="progress_cell_9">&nbsp;</td>
                    <td bgcolor="#ffffff" width="10" height="20">&nbsp;</td>
                    <td bgcolor="#ffffff" height="20" id="installation_progress" class="green">0 %</td>
                  </tr>
                </table>
                <br>
                <table border="0">
                  <tr>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_0">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_1">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_2">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_3">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_4">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_5">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_6">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_7">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_8">&nbsp;</td>
                    <td bgcolor="#cccccc" width="10" height="20" id="download_progress_cell_9">&nbsp;</td>
                    <td bgcolor="#ffffff" width="10" height="20">&nbsp;</td>
                    <td bgcolor="#ffffff" height="20" id="download_progress" class="green">0 %</td>
                    <td bgcolor="#ffffff" width="10" height="20">&nbsp;</td>
                    <td bgcolor="#ffffff" height="20" id="download_file" class="green"></td>
                  </tr>
                </table>
                <br>
                <iframe src="<?php echo basename(__FILE__); ?>?step=install-progress&amp;<?php echo SID;?>" width="700" height="700" frameborder="0" marginheight="0" marginwidth="0"></iframe>
              </td>
            </tr>
            </table>
<?php
        }
?>
  </td>
</tr>
</table>


</td>
    </tr>
   </table>
  </td>

 </tr>
</table>
<?php
        $this->displayHTMLFooter();
    }

    function displayHTMLHeader()
    {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
 <title>PEAR :: Installer :: Go-PEAR</title>
 <style type="text/css">
 <!--
    a {
        color:#000000;
        text-decoration: none;
    }
    a:visited {
        color:#000000;
        text-decoration: none;
    }
    a:active {
        color:#000000;
        text-decoration: none;
    }
    a:hover {
        color:#000000;
        text-decoration: underline;
    }

    a.green {
        color:#006600;
        text-decoration: none;
    }
    a.green:visited {
        color:#006600;
        text-decoration: none;
    }
    a.green:active {
        color:#006600;
        text-decoration: none;
    }
    a.green:hover {
        color:#006600;
        text-decoration: underline;
    }

    body, td, th {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 90%;
    }

    p {
        font-family: verdana,arial,helvetica,sans-serif;
    }

    th.pack {
        color: #FFFFFF;
        background: #009933;
        text-align: right;
    }

    td.package_info_title {
        color: #006600;
        font-weight: bold;
    }

    th.others {
        color: #006600;
        text-align: left;
    }

    em {
        font-weight: bold;
        font-style: italic;
    }

    .green {
        color: #006600;
    }
    .red {
        color: #006600;
    }

    span.headline {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 125%;
        font-weight: bold;
        color: #ffffff;
    }

    span.title {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 110%;
        font-weight: bold;
        color: #006600;
    }

    .newsDate {
        font-size: 85%;
        font-style: italic;
        color: #66cc66;
    }

    .compact {
        font-family: arial, helvetica, sans-serif;
        font-size: 90%;
    }

    .menuWhite {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 75%;
        color: #ffffff;
    }
    .menuBlack {
        font-family: verdana,arial,helvetica,sans-serif;
        text-decoration: none;
        font-weight: bold;
        font-size: 75%;
        color: #000000;
    }

    .sidebar {
        font-size: 85%;
    }

    code, pre, tt {
        font-family: Courier, "Courier New", monospace;
        font-size: 90%;
    }

    pre.php {
        border-color:       black;
        border-style:       dashed;
        border-width:       1px;
        background-color:   #eeeeee;
        padding:            5px;
    }

    h1 {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 140%;
        font-weight: bold;
        color: #006600;
    }

    h2 {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 125%;
        font-weight: bold;
        color: #006600;
    }

    h3 {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 110%;
        font-weight: bold;
        color: #006600;
    }

    small {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 75%;
    }

    a.small {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 75%;
        text-decoration: none;
    }

    .tableTitle {
        font-family: verdana,arial,helvetica,sans-serif;
        font-weight: bold;
    }

    .tableExtras {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 85%;
        color: #FFFFFF;
    }

    input {
        font-family: verdana,arial,helvetica,sans-serif;
    }

    textarea {
        font-family: verdana,arial,helvetica,sans-serif;
    }

    input.small, select.small {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 75%;
    }

    textarea.small {
        font-family: verdana,arial,helvetica,sans-serif;
        font-size: 75%;
    }

    form {
        margin-bottom : 0;
    }
 -->
 </style>
 <meta name="description" content="This is the Web Interface of the PEAR Installer" />
</head>

<body   topmargin="0" leftmargin="0"
        marginheight="0" marginwidth="0"
        bgcolor="#ffffff"
        text="#000000"
        link="#006600"
        alink="#cccc00"
        vlink="#003300"
>
<?php
    }

    function displayHTMLFooter()
    {
        ?>
    </body>
    </html>
        <?php
    }

    function displayHTMLDownloadProgress($progress)
    {
        if (!(isset($_SESSION['go-pear']['DHTML']) && $_SESSION['go-pear']['DHTML'])) {
            return;
        };
        $msg = ob_get_contents();
        ob_end_clean();

        echo '<script type="text/javascript"> parent.setdownloadprogress(' .
            ((int) $progress) . ');  </script>';

        ob_start();
        echo $msg;
    }

    function displayHTMLInstallationSummary($data = '')
    {
        $next     = NULL;
        $prefix   = dirname($this->webfrontend_file);
        $doc_root = $this->strip_magic_quotes($_SERVER['DOCUMENT_ROOT']);
        $file_dir = dirname(__FILE__);
        if ( WINDOWS ) {
            $prefix   = str_replace('/', '\\', strtolower($prefix));
            $doc_root = str_replace('/', '\\', strtolower($doc_root));
            $file_dir = str_replace('/', '\\', strtolower($file_dir));
        }

        if ($doc_root && substr($prefix, 0, strlen($doc_root)) == $doc_root) {
            $next = substr($prefix, strlen($doc_root)).'/index.php';
        } else if ($file_dir && substr($prefix, 0, strlen($file_dir)) == $file_dir) {
            $next = substr($prefix, strlen($file_dir)).'/index.php';
        }

        if ($data) {
            echo "<br/>".$data;
        }
?>
            <br/>
            <table border="0">
              <TR>
                <TD valign="top"><img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=note" border="0"></TD>
                <TD>
                  <span class="green">
                  <b>Note:</b> To use PEAR without any problems you need to add your<br/>
                  PEAR Installation path (<?php echo $GLOBALS['php_dir']; ?>)<br>
                  to your <a href="http://www.php.net/manual/en/configuration.directives.php#ini.include_path">include_path</a>.<br/>
                      <br/>
                  Using a .htaccess file or directly edit httpd.conf would be working solutions<br/>
                  for Apache running servers, too.<br/>
                  </span>
                </TD>
              </TR>
            </table>
            <br/>
            For more information about PEAR, see:<br/>
            <a href="http://pear.php.net/faq.php" target="_new" class="green">PEAR FAQ</a><br/>
            <a href="http://pear.php.net/manual/" target="_new" class="green">PEAR Manual</a><br/>
            <br/>
            Thanks for using go-pear!<br/>
            <br/>
<?php
        if ($next === NULL) {
?>
                    <table border="0">
                      <TR>
                        <TD valign="top"><img src="<?php echo basename(__FILE__); ?>?action=img&amp;img=note" border="0"></TD>
                        <TD>
                          <span style="color: #ff0000">
                            <b>Warning:</b> Go-PEAR was not able to determine the URL to the newly<br/>
                            installed Web Frontend of the PEAR Installer. Please access it manually.<br/>
                            Since you specified the prefix, you should know how to do so.<br/>
                          </span>
                        </TD>
                      </TR>
                    </table>
<?php
        } else {
            if ($_GET['step'] == 'install-progress') {
?>
                        <a href="<?php echo $next;?>" class="green" target="_parent">Start Web Frontend of the PEAR Installer &gt;&gt;</a>
<?php
            } else {
?>
                        <a href="<?php echo $next;?>" class="green">Start Web Frontend of the PEAR Installer &gt;&gt;</a>
<?php
            }
        }
    }

    function displayHTMLProgress($progress)
    {
        if (!(isset($_SESSION['go-pear']['DHTML']) && $_SESSION['go-pear']['DHTML'])) {
            return;
        };
        $msg = ob_get_contents();
        ob_end_clean();

        $msg = explode("\n", $msg);
        foreach($msg as $key => $value) {
            if (preg_match('/ok$/', $value)) {
                $value = preg_replace('/(ok)$/', '<span class="green">\1</span>', $value);
            };
            if (preg_match('/failed$/', $value)) {
                $value = preg_replace('/(failed)$/', '<span style="color: #ff0000">\1</span>', $value);
            };
            if (preg_match('/^install ok:/', $value)) {
                $value = preg_replace('/^(install ok:)/', '<span class="green">\1</span>', $value);
            };
            if (preg_match('/^Warning:/', $value)) {
                $value = '<span style="color: #ff0000">'.$value.'</span>';
            };
            $msg[$key] = $value;
        };
        $msg = implode('<br>', $msg);

        $msg .= '<script type="text/javascript"> parent.setprogress(' .
            ((int) $progress) . ');  </script>';

        echo $msg;
        ob_start();
    }

    function displayHTMLSetDownload($file)
    {
        if (!(isset($_SESSION['go-pear']['DHTML']) && $_SESSION['go-pear']['DHTML'])) {
            return;
        };
        $msg = ob_get_contents();
        ob_end_clean();

        echo '<script type="text/javascript"> parent.setdownloadfile("' . $file . '");  </script>';

        ob_start();
        echo $msg;
    }

    function displayPreamble()
    {
        if ( isset($this->www_errors) && sizeof($this->www_errors) ) {
            $this->displayHTML('config');
            exit;
        } else {
            if (isset($_SESSION['go-pear']['DHTML']) && $_SESSION['go-pear']['DHTML'] == true && $_GET['step'] == 'install') {
                $_GET['step'] = 'preinstall';
            }
            if ($_GET['step'] != 'install' && $_GET['step'] != 'install-progress') {
                $this->displayHTML($_GET['step']);
                exit;
            }
            if ($_GET['step'] == 'install-progress') {
                $this->displayHTMLHeader();
                echo "Starting installation ...<br/>";
            }
            ob_start();
        }
    }

    function doSetupConfigVars()
    {
        @session_start();

        /*
            See bug #23069
        */
        if (WINDOWS) {
            $this->php_sapi_name = win32DetectPHPSAPI();
            if ($this->php_sapi_name=='cgi'){
                $msg = "
Sorry! The PEAR installer actually does not work on Windows platform using CGI and Apache.
Please install the module SAPI (see http://www.php.net/manual/en/install.apache.php for the
instructions) or use the CLI (cli\php.exe) in the console.
        ";
                $this->displayHTML('error', $msg);
                exit;
            }
        }

        if (!isset($_SESSION['go-pear']) || isset($_GET['restart'])) {
            $_SESSION['go-pear'] = array(
                'http_proxy' => $this->http_proxy,
                'config' => array(
                    'prefix' => dirname(__FILE__),
                    'bin_dir' => $this->bin_dir,
                    'php_bin' => $this->php_bin,
                    'php_dir' => '$prefix/PEAR',
                    'doc_dir' => $this->doc_dir,
                    'data_dir' => $this->data_dir,
                    'test_dir' => $this->test_dir,
                    'cache_dir' => '$php_dir/cache',
                    'cache_ttl' => 300,
                    'webfrontend_file' => '$prefix/index.php',
                    ),
                'install_pfc' => true,
                'DHTML' => true,
                );
        }
        if (!isset($_GET['step'])) {
            $_GET['step'] = 'Welcome';
            /* clean up old sessions datas */
            session_destroy();
        }
        if ($_GET['step'] == 'install') {

            $_SESSION['go-pear']['http_proxy'] =
                $this->strip_magic_quotes($_POST['proxy']['host']) . ':' .
                $this->strip_magic_quotes($_POST['proxy']['port']);

            if ($_SESSION['go-pear']['http_proxy'] == ':') {
                $_SESSION['go-pear']['http_proxy'] = '';
            }

            $this->wwwerrors = array();

            foreach($_POST['config'] as $key => $value) {
                $_POST['config'][$key] = $this->strip_magic_quotes($value);
                if($key!='cache_ttl'){
                    if ( empty($_POST['config'][$key]) ) {
                        if (WEBINSTALLER && $key != 'php_bin' ) {
                            $this->wwwerrors[$key] = 'Please fill this path, you can use $prefix, $php_dir or a full path.';
                        }
                    }
                }
            }

            if( sizeof($this->wwwerrors) > 0){
                $_GET['step'] = 'config';
            }

            $_SESSION['go-pear']['config'] = $_POST['config'];
            $_SESSION['go-pear']['install_pfc'] = (isset($_POST['install_pfc']) && $_POST['install_pfc'] == 'on');
            $_SESSION['go-pear']['DHTML'] = isset($_POST['BCmode']) ? !($_POST['BCmode'] == "on") : true;
        }

        $http_proxy = $_SESSION['go-pear']['http_proxy'];
        foreach($_SESSION['go-pear']['config'] as $var => $value) {
            $this->$var = $value;
        }
        $this->install_pfc = $_SESSION['go-pear']['install_pfc'];
        $this->setupConfigVars();
    }

    function finishInstall($webfrontend_file, $doc_dir, &$progress)
    {
        print "Writing WebFrontend file ... ";
        @unlink($this->webfrontend_file); //Delete old one
        copy ($this->doc_dir . DIRECTORY_SEPARATOR.
                'PEAR_Frontend_Web' . DIRECTORY_SEPARATOR.
                'docs' . DIRECTORY_SEPARATOR .
                'example.php',
                $this->webfrontend_file
            );
        if ($_GET['step'] == 'install-progress') {
            $this->displayHTMLProgress($progress = 100);
            ob_end_clean();
            $this->displayHTMLInstallationSummary();
            $this->displayHTMLFooter();
        } else {
            $out = ob_get_contents();

            $out = explode("\n", $out);
            foreach($out as $line => $value) {
                if (preg_match('/ok$/', $value)) {
                    $value = preg_replace('/(ok)$/', '<span class="green">\1</span>', $value);
                };
                if (preg_match('/^install ok:/', $value)) {
                    $value = preg_replace('/^(install ok:)/', '<span class="green">\1</span>', $value);
                };
                if (preg_match('/^Warning:/', $value)) {
                    $value = '<span style="color: #ff0000">'.$value.'</span>';
                };
                $out[$line] = $value;
            };
            $out = nl2br(implode("\n",$out));
            ob_end_clean();

            $this->displayHTML('install', $out);
        }
    }

    function setupWebInstaller()
    {
        /*
         * See bug #23069
         */

        if (WINDOWS) {
            $this->php_sapi_name = $this->win32DetectPHPSAPI();
            if ($this->php_sapi_name=='cgi') {
            $msg = nl2br("
Sorry! The PEAR installer actually does not work on Windows platform using CGI and Apache.
Please install the module SAPI (see http://www.php.net/manual/en/install.apache.php for the
instructions) or use the CLI (cli\php.exe) in the console.
    ");
                $this->displayHTML('error', $msg);
            }
        }

        if (isset($_GET['action']) && $_GET['action'] == 'img' && isset($_GET['img'])) {
            switch ($_GET['img'])
            {
                case 'note':
                case 'pearlogo':
                case 'smallpear':
                    $this->showImage($_GET['img']);
                    exit;
                default:
                    exit;
            };
        }
    }

    function showImage($img)
    {
        $images = array(
            'smallpear' => array(
                'type' => 'gif',
                'data' => 'R0lGODlhEQATAMQAAAAAACqUACiTAC2WAC+YAzKZBTSaBsHgszOZADCYADmcB4TCZp3Ohtfrzd/v1+by4PD47DaaAz+fDUijF2WyOlCoHvT58VqtJPn893y+S/v9+f7//f3+/Pz9+////////ywAAAAAEQATAAAFkqAnjiR5NGXqcdpCoapnMVRdWbEHUROVVROYalHJTCaVAKWTcjAUGckgQY04SJAFMhJJIL5e4a5I6X6/gwlkRIwOzucAY9SYZBRvOCKheIwYFxR5enxCLhVeemAHbBQVg4SMIoCCinsKVyIOdlKKAhQcJFpGiWgFQiIYPxeJCQEEcykcDIgDAwYUkjEWB70NGykhADs=',
                ),
            'pearlogo' => array(
                'type' => 'gif',
                'data' => 'R0lGODlhaAAyAMT/AMDAwP3+/TWaAvD47Pj89vz++zebBDmcBj6fDEekFluvKmu3PvX68ujz4XvBS8LgrNXqxeHw1ZnPaa/dgvv9+cLqj8LmltD2msnuls3xmszwmf7+/f///wAAAAAAAAAAACH5BAEAAAAALAAAAABoADIAQAX/ICCOZGmeaKqubOtWWjwJphLLgH1XUu//C1Jisfj9YLEKQnSY3GaixWQqQTkYHM4AMulNLJFC9pEwIW/odKU8cqTfsWoTTtcomU4ZjbR4ZP+AgYKCG0EiZ1AuiossEhwEXRMEg5SVWQ6MmZqKWD0QlqCUEHubpaYlExwRPRZioZZVp7KzKQoSDxANDLsNXA5simd2FcQYb4YAc2jEU80TmAAIztPCMcjKdg4OEsZJmwIWWQPQI4ikIwtoVQnddgrv8PFlCWgYCwkI+fp5dkvJ/IlUKMCy6tYrDhNIIKLFEAWCTxse+ABD4SClWA0zovAjcUJFi6EwahxZwoGqHhFA/4IqoICkyxQSKkbo0gDkuBXV4FRAJkRCnTgi2P28IcEfk5xpWppykFJVuScmEvDTEETAVJ6bEpypcADPkz3pvKVAICHChkC7siQ08zVqu4Q6hgIFEFZuEn/KMgRUkaBmAQs+cEHgIiHVH5EAFpIgW4+NT6LnaqhDwe/Ov7YOmWZp4MkiAWBIl0kAVsJWuzcYpdiNgddc0E8cKBAu/FElBwagMb88ZZKDRAkWJtkWhHh3wwUbKHQJN3wQAaXGR2LpArv5oFHRR34C7Mf6oLXZNfqBgNI7oOLhj1f8PaGpygHQ0xtP8MDVKwYTSKcgxr9/hS6/pCCAAg5M4B9/sWh1YP9/XSgQWRML/idBfKUc4IBET9lFjggKhDYZAELZJYEBI2BDB3ouNBEABwE8gAwiCcSYgAKqPdEVAG7scM8BPPZ4AIlM+OgjAgpMhRE24OVoBwsIFEGFA7ZkQQBWienWxmRa7XDjKZXhBdAeSmKQwgLuUVLICa6VEKIGcK2mQWoVZHCBXJblJUFkY06yAXlGsPIHBEYdYiWHb+WQBgaIJqqoHFNpgMGB7dT5ZQuG/WbBAIAUEEFNfwxAWpokTIXJAWdgoJ9kRFG2g5eDRpXSBpEIF0oEQFaZhDbaSFANRgqcJoEDRARLREtxOQpsPO906ZUeJgjQB6dZUPBAdwcF8KLXXRVQaKFcsRRLJ6vMiiCNKxRE8ECZKgUA3Va4arOAAqdGRWO7uMZH5AL05gvsjQbg6y4NCjQ1kw8TVGcbdoKGKx8j3bGH7nARBArqwi0gkFJBrZiXBQRbHoIgnhSjcEBKfD7c3HMhz+JIQSY3t8GGKW+SUhfUajxGzKd0IoHBNkNQK86ZYEqdzYA8AHQpqXRUm80oHs1CAgMoBxzRqvzs9CIKECC1JBp7enUpfXHApwVYNAfo16c4IrYPLVdSAJVob7IAtCBFQGHcs/RRdiUDPHA33oADEAIAOw==',
                ),
            'note' => array(
                'type' => 'png',
                'data' => 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAAAAADFHGIkAAAAAmJLR0QAAKqNIzIAAAEESURBVHjaZZIhksMwDEV9voWFSwsLA0MLDf8VdARBUUNBQ1FBHcErZ5M0baXJjOPnb0vfLuMMn3H+lWMgBKL89A1Eq9Q9IrwB+gIOsnMPBR8giMclguQfBGS8x5xIoPQxnxqb4LL/eQ4l2AVNONP2ZshLCqJ3qqzWtT5pNgNnLU4OcNbuiqaLmFmHGhJ0TCMC99+f2wphlhaOYjuQVc0IIzLH2BRWfQoWsNSjct8AVop4rF3belTuVAb3MRj6kLrcTwtIy+g03V1vC57t1XrMzqfP5pln5yLTkk7+5UhstvOni1X3ixLEdf2c36+W0Q7kOb48hnSRLI/XdNPfX4kpMkgP5R+elfdkDPprQgAAAEN0RVh0U29mdHdhcmUAQCgjKUltYWdlTWFnaWNrIDQuMi44IDk5LzA4LzAxIGNyaXN0eUBteXN0aWMuZXMuZHVwb250LmNvbZG6IbgAAAAqdEVYdFNpZ25hdHVyZQAzYmQ3NDdjNWU0NTgwNzAwNmIwOTBkZDNlN2EyNmM0NBTTk/oAAAAOdEVYdFBhZ2UAMjR4MjQrMCswclsJMQAAAABJRU5ErkJggg==',
                ),
            );

        header('Content-Type: image/' . $images[$img]['type']);
        echo base64_decode($images[$img]['data']);
    }
}
?>