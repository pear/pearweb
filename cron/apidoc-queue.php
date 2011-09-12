#!/usr/local/bin/php
<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2004-2005 The PEAR Group                               |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Author: Martin Jansen <mj@php.net>                                   |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */
// Old PHPDocumentor setup:
// For manual testing on sg1
// $_ENV['PEAR_DATABASE_DSN'] = 'mysqli://user:password@localhost/database';

// 1. Ensure you have the appropriate phpdocumentor html template smarty bit installed.
// ERROR: template directory "/usr/local/lib/php/pear/data/PhpDocumentor/phpDocumentor/Converters/HTML/Smarty/templates/PEAR/" does not exist
// svn co svn co https://svn.php.net/repository/pear/packages/PhpDocumentor/trunk/ ../PhpDocumentor
//  cp -R ../PhpDocumentor/phpDocumentor/Converters/HTML/Smarty/templates/PEAR/* /usr/local/lib/php/pear/data/PhpDocumentor/phpDocumentor/Converters/HTML/Smarty/templates/PEAR/

// 2. Ensure you have a package in the correct APIDOC_QUEUE location - IE
// /home/pear/packages/Validate_HU-0.1.2.tgz
// 3. Ensure the apidoc_queue table has an entry to that .tar.gz file. IE:
// INSERT INTO  apidoc_queue(filename) VALUES( '/home/pear/packages/Validate_HU-0.1.2.tgz');

// Shiny new docblox setup
// pear channel-discover pear.docblox-project.org
// pear channel-discover pear.michelf.com
// pear install michelf/markdownextra
// pear install docblox
// apt-get install graphviz
// And... uncomment the below
$documentation_engine = 'phpdocumentor';
// $documentation_engine = 'docblox';

require_once dirname(dirname(__FILE__)) . '/include/pear-config.php';
require_once 'DB.php';
require_once 'PEAR/Common.php';
require_once 'Archive/Tar.php';

$debug = false;

class apidocqueue extends PEAR_Common
{
    // {{{ infoFromTgzFile()
    /**
     * Returns information about a package file.  Expects the name of
     * a gzipped tar file as input.
     *
     * @param string  $file  name of .tgz file
     *
     * @return array  array with package information
     *
     * @access public
     * @deprecated use PEAR_PackageFile->fromTgzFile() instead
     *
     */
    function infoFromTgzFile($file)
    {
        $config = &PEAR_Config::singleton();
        $packagefile = new PEAR_PackageFile($config);
        $pf = &$packagefile->fromTgzFile($file, PEAR_VALIDATE_NORMAL);
        if (PEAR::isError($pf)) {
            $errs = $pf->getUserinfo();
            if (is_array($errs)) {
                foreach ($errs as $error) {
                    $e = $this->raiseError($error['message'], $error['code'], null, null, $error);
                }
            }
            return $pf;
        }
        return $this->_postProcessValidPackagexml($pf);
    }

   /**
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @return array
     */
    function _postProcessValidPackagexml(&$pf)
    {
        if (is_a($pf, 'PEAR_PackageFile_v2')) {
            // sort of make this into a package.xml 1.0-style array
            // changelog is not converted to old format.
            $arr = $pf->toArray(true);
            $arr = array_merge($arr, $arr['old']);
            unset($arr['old']);
            unset($arr['xsdversion']);
            unset($arr['contents']);
            unset($arr['compatible']);
            unset($arr['channel']);
            unset($arr['uri']);
            unset($arr['dependencies']);
            unset($arr['phprelease']);
            unset($arr['extsrcrelease']);
            unset($arr['zendextsrcrelease']);
            unset($arr['extbinrelease']);
            unset($arr['zendextbinrelease']);
            unset($arr['bundle']);
            unset($arr['lead']);
            unset($arr['developer']);
            unset($arr['helper']);
            unset($arr['contributor']);
            $arr['filelist'] = $pf->getFilelist();
            $this->pkginfo = $arr;
            return $arr;
        } else {
            $this->pkginfo = $pf->toArray();
            return $this->pkginfo;
        }
    }
}

$pkg_handler = new apidocqueue();

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh = DB::connect(PEAR_DATABASE_DSN, $options);
if (PEAR::isError($dbh)) {
    die($dbh->getMessage());
}
$query = "SELECT filename FROM apidoc_queue WHERE finished = '0000-00-00 00:00:00'";
$rows = $dbh->getCol($query);

foreach ($rows as $filename) {
    $info = $pkg_handler->infoFromTgzFile($filename);
    $tar = new Archive_Tar($filename);

    if (PEAR::isError($info)) {
        trigger_error($info->getMessage());
        continue;
    }

    $name = (isset($info['package']) ? $info['package'] : $info['name']);

    echo "Generating documentation for " . $name . " " . $info['version'] . "\n";

    /* Extract files into temporary directory */
    $tmpdir = PEAR_TMPDIR . "/apidoc/" . $name;

    if (!$pkg_handler->mkDirHier($tmpdir)) {
        die("Unable to create temporary directory " . $tmpdir . "\n");
    }

    $tar->extract($tmpdir);


    switch ($documentation_engine) {
        case 'docblox':
            $command = sprintf(
                "/usr/local/bin/docblox -d %s --title '%s' -t %s --template %s --ignore */data/*,*/tests/*; rm -rf %s",
                $tmpdir,
                $name,
                $name . " " . $info['version'],
                PEAR_APIDOC_DIR . "/" . $name . "-" . $info['version'],
                "default",
                $tmpdir
            );
 
            break;
        case 'phpdocumentor':
        default:
            $command = sprintf(
                "/usr/local/bin/phpdoc -d %s -dn '%s' -ti '%s' -p on -s on -t %s -o %s --ignore */data/*,*/tests/*; rm -rf %s",
                $tmpdir,
                $name,
                $name . " " . $info['version'],
                PEAR_APIDOC_DIR . "/" . $name . "-" . $info['version'],
                "HTML:Smarty:PEAR",
                $tmpdir
            );

            break;
    }

    $output = "";
    $process = popen($command, "r");

    if ($process) {
        while ($line = fgets($process)) {
            $output .= $line;
        }
        pclose($process);

        //update "latest" symlink
        $latestdir  = PEAR_APIDOC_DIR . $name . '-latest';
        $versiondir = PEAR_APIDOC_DIR . $name . '-' . $info['version'];
        is_link($latestdir) && unlink($latestdir);
        symlink($versiondir, $latestdir);

        // In debug mode; we render the output to stdout
        if ($debug) {
            print $output;
        }

        // In debug mode; we may not wish to write back changes
        if (!$debug) {
            $query = "UPDATE apidoc_queue SET finished = NOW(), log = ? WHERE filename = ?";
            $dbh->query($query, array($output, $filename));
        } 
    }
}
