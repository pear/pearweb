<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:  Stig S. Bakken <ssb@fast.no>                               |
   |           Tomas V.V. Cox <cox@php.net>                               |
   |           Martin Jansen <mj@php.net>                                 |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * Class to handle releases
 *
 * @class   release
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class release
{
    // {{{  proto array  release::getRecent([int]) API 1.0

    /**
     * Get recent releases
     *
     * @static
     * @param  integer Number of releases to return
     * @return array
     */
    static function getRecent($n = 5)
    {
        global $dbh;
        $sth = $dbh->limitQuery("SELECT packages.id AS id, ".
                                "packages.name AS name, ".
                                "packages.summary AS summary, ".
                                "releases.version AS version, ".
                                "releases.releasedate AS releasedate, ".
                                "releases.releasenotes AS releasenotes, ".
                                "releases.doneby AS doneby, ".
                                "releases.state AS state ".
                                "FROM packages, releases ".
                                "WHERE packages.id = releases.package ".
                                "AND packages.approved = 1 ".
                                "AND packages.package_type = 'pear' ".
                                "ORDER BY releases.releasedate DESC", 0, $n);
        $recent = array();
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{  proto array  release::getDateRange(int,int) API 1.0

    // }}}
    // {{{  proto array  release::getPopular(int) API 1.0
    static function getPopular($n = 5, $rss = false)
    {
        global $dbh;
        if ($rss) {
            $query = '
            SELECT
                packages.name, releases.version, downloads, releasedate,
                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as releasenotes
                FROM releases, packages, aggregated_package_stats a
                WHERE
                    packages.name <> "pearweb" AND
                    packages.name <> "pearweb_phars" AND
                    packages.id = releases.package AND
                    packages.package_type = \'pear\' AND
                    a.release_id = releases.id AND
                    a.package_id = packages.id AND
                    packages.newpk_id IS NULL AND
                    packages.unmaintained = 0 AND
                    a.yearmonth = "' . date('Y-m-01 00:00:00', time()) . '"
                ORDER BY releasenotes DESC';
        } else {
            $query = '
            SELECT
                packages.name, releases.version, downloads,
                    downloads/(CEIL((unix_timestamp(NOW()) - unix_timestamp(releases.releasedate))/86400)) as d
                FROM releases, packages, aggregated_package_stats a
                WHERE
                    packages.name <> "pearweb" AND
                    packages.name <> "pearweb_phars" AND
                    packages.id = releases.package AND
                    packages.package_type = \'pear\' AND
                    a.release_id = releases.id AND
                    a.package_id = packages.id AND
                    packages.newpk_id IS NULL AND
                    packages.unmaintained = 0 AND
                    a.yearmonth = "' . date('Y-m-01 00:00:00', time()) . '"
                ORDER BY d DESC';
        }
        $sth = $dbh->limitQuery($query, 0, $n);
        $recent = array();
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    /**
     * Get release in a specific time range
     *
     * @static
     * @param integer Timestamp of start date
     * @param integer Timestamp of end date
     * @return array
     */
    static function getDateRange($start,$end)
    {
        global $dbh;

        $recent = array();
        if (!is_numeric($start)) {
            return $recent;
        }
        if (!is_numeric($end)) {
            return $recent;
        }
        $start_f = date('Y-m-d 00:00:00',$start);
        $end_f = date('Y-m-d 00:00:00',$end);
        // limited to 50 to stop overkill on the server!
        $sth = $dbh->limitQuery("SELECT packages.id AS id, ".
                                "packages.name AS name, ".
                                "packages.summary AS summary, ".
                                "packages.description AS description, ".
                                "releases.version AS version, ".
                                "releases.releasedate AS releasedate, ".
                                "releases.releasenotes AS releasenotes, ".
                                "releases.doneby AS doneby, ".
                                "releases.state AS state ".
                                "FROM packages, releases ".
                                "WHERE packages.id = releases.package ".
                                "AND releases.releasedate > '{$start_f}' AND releases.releasedate < '{$end_f}'".
                                "ORDER BY releases.releasedate DESC",0,50);

        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ +proto string release::upload(string, string, string, string, binary, string) API 1.0

    /**
     * Upload new release
     *
     * @static
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     */
    static function upload($package, $version, $state, $relnotes, $tarball, $md5sum,
                    $pkg_info = false, $packagexml = false, $compatible = false)
    {
        global $auth_user;

        include_once 'pear-database-user.php';
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && !$auth_user->isAdmin() && !$auth_user->isQA()) {
            return PEAR::raiseError('release::upload: insufficient privileges');
        }
        $ref = release::validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);
        if (PEAR::isError($ref)) {
            return $ref;
        }

        return release::confirmUpload($package, $version, $state, $relnotes, $md5sum, $ref['package_id'], $ref['file'], $pkg_info, $packagexml, $compatible);
    }

    // }}}
    // {{{ +proto string release::validateUpload(string, string, string, string, binary, string) API 1.0

    /**
     * Determine if uploaded file is a valid release
     *
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     * @return mixed
     */
    static function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user;

        include_once 'pear-database-user.php';
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && !$auth_user->isAdmin() && !$auth_user->isQA()) {
            return PEAR::raiseError('release::validateUpload: insufficient privileges');
        }
        // (2) verify that package exists
        include_once 'pear-database-package.php';
        $package_id = package::info($package, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("package `$package' must be registered first");
        }

        // (3) verify that version does not exist
        $test = $dbh->getOne("SELECT version FROM releases ".
                             "WHERE package = ? AND version = ?",
                             array($package_id, $version));
        if (PEAR::isError($test)) {
            return $test;
        }
        if ($test) {
            return PEAR::raiseError("already exists: $package $version");
        }

        // (4) store tar ball to temp file
        $tempfile = sprintf("%s/%s%s-%s.tgz",
                            PEAR_TARBALL_DIR, ".new.", $package, $version);
        $file = sprintf("%s/%s-%s.tgz", PEAR_TARBALL_DIR, $package, $version);
        if (!@copy($tarball, $tempfile)) {
            return PEAR::raiseError("writing $tempfile failed: $php_errormsg");
        }

        if (!isset($package_id)) {
            return PEAR::raiseError("bad upload: package_id missing");
        }

        // later: do lots of integrity checks on the tarball
        if (!@rename($tempfile, $file)) {
            return PEAR::raiseError("renaming failed: $php_errormsg");
        }

        // (5) verify MD5 checksum
        $testsum = md5_file($file);
        if ($testsum != $md5sum) {
            $bytes = strlen($data);
            return PEAR::raiseError("bad md5 checksum (checksum=$testsum ($bytes bytes: $data), specified=$md5sum)");
        }

        // (6) unpack tarball
        $target = @fopen(PEAR_TARBALL_DIR . "/" . $package . "-" . $version . ".tar", "w+");
        if ($target) {
            fwrite($target, file_get_contents("compress.zlib://" . $file));
            fclose($target);
        }

        return array("package_id" => $package_id,
                     "file" => $file
                     );
    }

    // }}}
    // {{{ +proto bool   release::confirmUpload(string, string, string, string, string, int, binary) API 1.0

    /**
     * Confirm release upload
     *
     * @param string Package name
     * @param string Package version
     * @param string Package state
     * @param string Release notes
     * @param string md5
     * @param int    Package id from database
     * @param string package contents
     * @static
     * @return string  the file name of the upload or PEAR_Error object if problems
     */
    static function confirmUpload($package, $version, $state, $relnotes, $md5sum, $package_id, $file,
                           $pkg_info = false, $packagexml = false, $compatible = false)
    {
        require_once "PEAR/Common.php";

        global $dbh, $auth_user, $_PEAR_Common_dependency_types,
               $_PEAR_Common_dependency_relations;

        if (!$pkg_info) {
            require_once 'Archive/Tar.php';
            $tar = &new Archive_Tar($file);

            $oldpackagexml = $tar->extractInString('package.xml');
            if (null === $packagexml = $tar->extractInString('package2.xml')) {
                if ($oldpackagexml === null) {
                    return PEAR::raiseError('Archive uploaded does not appear to contain a package.xml!');
                }

                $packagexml = $oldpackagexml;
            }

            $compatible = $oldpackagexml != $packagexml ? true : false;
        }
        // Update releases table
        $query = "INSERT INTO releases (id,package,version,state,doneby,".
             "releasedate,releasenotes) VALUES(?,?,?,?,?,NOW(),?)";
        $sth = $dbh->prepare($query);
        $release_id = $dbh->nextId("releases");
        $dbh->execute($sth, array($release_id, $package_id, $version, $state,
                                  $auth_user->handle, $relnotes));
        // Update files table
        $query = "INSERT INTO files ".
             "(id,package,release,md5sum,basename,fullpath,packagexml) ".
             "VALUES(?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $file_id = $dbh->nextId("files");
        $ok = $dbh->execute($sth, array($file_id, $package_id, $release_id,
                                        $md5sum, basename($file), $file, $packagexml));
        /*
         * Code duplication with deps error
         * Should be droped soon or later using transaction
         * (and add mysql4 as a pe(ar|cl)web requirement)
         */
        if (PEAR::isError($ok)) {
            $dbh->query("DELETE FROM releases WHERE id = $release_id");
            @unlink($file);
            return $ok;
        }

        // Update dependency table
        $query = "INSERT INTO deps " .
            "(package, `release`, type, relation, version, name, optional) " .
            "VALUES (?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);

        if (!$pkg_info) {
            require_once 'PEAR/PackageFile.php';
            require_once 'PEAR/Config.php';
            $config = &PEAR_Config::singleton();
            $pf = &new PEAR_PackageFile($config);
            $pkg_info = $pf->fromXmlString($packagexml, PEAR_VALIDATE_DOWNLOADING,
                $compatible ? 'package2.xml' : 'package.xml');
        }

        $deps = $pkg_info->getDeps(true); // get the package2.xml actual content
        $storedeps = $pkg_info->getDeps(); // get the BC-compatible content
        $pearused = false;
        if (isset($deps['required']['package'])) {
            if (!isset($deps['required']['package'][0])) {
                $deps['required']['package'] = array($deps['required']['package']);
            }
            foreach ($deps['required']['package'] as $pkgdep) {
                if ($pkgdep['channel'] == 'pear.php.net' && strtolower($pkgdep['name']) == 'pear') {
                    $pearused = true;
                }
            }
        }
        if (is_array($storedeps)) {
            foreach ($storedeps as $dep) {
                $prob = array();

                if (empty($dep['type']) ||
                    !in_array($dep['type'], $_PEAR_Common_dependency_types))
                {
                    $prob[] = 'type';
                }

                if (empty($dep['name'])) {
                    /*
                     * NOTE from pajoye in ver 1.166:
                     * This works for now.
                     * This would require a 'cleaner' InfoFromXXX
                     * which may return a defined set of data using
                     * default values if required.
                     */
                    if (strtolower($dep['type']) == 'php') {
                        $dep['name'] = 'PHP';
                    } else {
                        $prob[] = 'name';
                    }
                } elseif (strtolower($dep['name']) == 'pear') {
                    if (!$pearused && $compatible) {
                        // there is no need for a PEAR dependency here
                        continue;
                    }
                    if (!$pearused && !$compatible) {
                        $dep['name'] = 'PEAR Installer';
                    }
                }

                if (empty($dep['rel']) ||
                    !in_array($dep['rel'], $_PEAR_Common_dependency_relations))
                {
                    $prob[] = 'rel';
                }

                if (empty($dep['optional'])) {
                    $optional = 0;
                } else {
                    if ($dep['optional'] != strtolower($dep['optional'])) {
                        $prob[] = 'optional';
                    }

                    $optional = $dep['optional'] == 'yes' ? 1 : 0;
                }

                if (count($prob)) {
                    $res = PEAR::raiseError('The following attribute(s) ' .
                            'were missing or need proper values: ' .
                            implode(', ', $prob));
                } else {
                    $res = $dbh->execute($sth,
                            array(
                                $package_id,
                                $release_id,
                                $dep['type'],
                                $dep['rel'],
                                @$dep['version'],
                                $dep['name'],
                                $optional));
                }

                if (PEAR::isError($res)) {
                    $dbh->query('DELETE FROM deps WHERE ' .
                                "release = $release_id");
                    $dbh->query('DELETE FROM releases WHERE ' .
                                "id = $release_id");
                    @unlink($file);
                    return $res;
                }
            }
        }

        include_once 'pear-database-package.php';
        if (!in_array(package::info($package, 'name'), array('pearweb', 'pearweb_phars'), true)) {
            // Add release archive file to API documentation queue
            $query = "INSERT INTO apidoc_queue (filename, queued) "
                 . "VALUES ('" . $file. "', NOW())";

            /*
             * Don't abort the release if something goes wrong.
             */
            $dbh->pushErrorHandling(PEAR_ERROR_RETURN);
            $sth = $dbh->query($query);
            $dbh->popErrorHandling();
        }

        // Update Cache
        $GLOBALS['pear_rest']->saveAllReleasesREST($package);
        $GLOBALS['pear_rest']->saveReleaseREST($file, $packagexml, $pkg_info, $auth_user->handle,
            $release_id);
        $GLOBALS['pear_rest']->savePackagesCategoryREST(package::info($package, 'category'));

        // gotta clear all the permutations
        include_once 'xmlrpc-cache.php';
        $cache = new XMLRPC_Cache;
        $cache->remove('package.listAll', array(false));
        $cache->remove('package.listAll', array(true));

        $cache->remove('package.listAll', array(false, true));
        $cache->remove('package.listAll', array(false, false));

        $cache->remove('package.listAll', array(true, true));
        $cache->remove('package.listAll', array(true, false));

        $cache->remove('package.listAll', array(false, true, true));
        $cache->remove('package.listAll', array(false, true, false));
        $cache->remove('package.listAll', array(false, false, true));
        $cache->remove('package.listAll', array(false, false, false));

        $cache->remove('package.listAll', array(true, true, true));
        $cache->remove('package.listAll', array(true, true, false));
        $cache->remove('package.listAll', array(true, false, true));
        $cache->remove('package.listAll', array(true, false, false));

        // make sure pecl is also removed
        $cache->remove('package.info', array($package, null));
        $cache->remove('package.info', array($package, array(null, null, true)));

        return $file;
    }

    // }}}
    // {{{ +proto bool   release::dismissUpload(string) API 1.0

    /**
     * Dismiss release upload
     *
     * @param string
     * @return boolean
     */
    static function dismissUpload($upload_ref)
    {
        return (bool)@unlink($upload_ref);
    }

    // }}}
    // {{{ NOEXPORT      release::HTTPdownload(string, [string], [string], [bool])

    /**
     * Download release via HTTP
     *
     * Not for xmlrpc export!
     *
     * @param string Name of the package
     * @param string Version string
     * @param string Filename
     * @param boolean Uncompress file before downloading?
     * @return mixed
     * @static
     */
    static function HTTPdownload($package, $version = null, $file = null, $uncompress = false)
    {
        global $dbh;

        include_once 'pear-database-package.php';
        $package_id = package::info($package, 'packageid', true);

        if (!$package_id) {
            return PEAR::raiseError("release download:: package '".htmlspecialchars($package).
                                    "' does not exist");
        } elseif (PEAR::isError($package_id)) {
            return $package_id;
        }

        if ($file !== null) {
            if (substr($file, -4) == '.tar') {
                $file = substr($file, 0, -4) . '.tgz';
                $uncompress = true;
            }
            $row = $dbh->getRow("SELECT fullpath, `release`, id FROM files ".
                                "WHERE UPPER(basename) = ?", array(strtoupper($file)),
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            } elseif ($row === null) {
                return PEAR::raiseError("File '$file' not found");
            }
            $path = $row['fullpath'];
            $log_release = $row['release'];
            $log_file = $row['id'];
            $basename = $file;
        } elseif ($version == null) {
            // Get the most recent version
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "ORDER BY releasedate DESC", DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        } elseif (release::isValidState($version)) {
            $version = strtolower($version);
            // Get the most recent version with a given state
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND state = '$version' ".
                                "ORDER BY releasedate DESC",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
            if (!isset($release_id)) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
            }
        } else {
            // Get a specific release
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND version = '$version'",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        }
        if (!isset($path) && isset($release_id)) {
            $sql = "SELECT fullpath, basename, id FROM files WHERE release = ".
                 $release_id;
            $row = $dbh->getRow($sql, DB_FETCHMODE_ORDERED);
            if (PEAR::isError($row)) {
                return $row;
            }
            list($path, $basename, $log_file) = $row;
            if (empty($path) || !@is_file($path)) {
                return PEAR::raiseError("release download:: no version information found");
            }
        }
        if (isset($path)) {
            if (!isset($log_release)) {
                $log_release = $release_id;
            }

            release::logDownload($package_id, $log_release, $log_file);

            header('Last-modified: ' .
                gmdate('D, d M Y H:i:s \G\M\T', filemtime($path)));
            header('Content-type: application/octet-stream');
            if ($uncompress) {
                $tarname = preg_replace('/\.tgz\z/', '.tar', $basename);
                header('Content-disposition: attachment; filename="'.$tarname.'"');
                readgzfile($path);
            } else {
                header('Content-disposition: attachment; filename="'.$basename.'"');
                header('Content-length: '.filesize($path));
                readfile($path);
            }

            return true;
        }
        header('HTTP/1.0 404 Not Found');
        print 'File not found';
    }

    // }}}
    // {{{  proto bool   release::isValidState(string) API 1.0

    /**
     * Determine if release state is valid
     *
     * @static
     * @param string State
     * @return boolean
     */
    static function isValidState($state)
    {
        static $states = array('devel', 'snapshot', 'alpha', 'beta', 'stable');
        return in_array($state, $states);
    }

    // }}}
    // {{{  proto array  release::betterStates(string) API 1.0

    /**
     * Convert a state into an array of less stable states
     *
     * @param string Release state
     * @param boolean include the state in the array returned
     * @return boolean
     */
    static function betterStates($state, $include = false)
    {
        static $states = array('snapshot', 'devel', 'alpha', 'beta', 'stable');
        $i = array_search($state, $states);
        if ($include) {
            $i--;
        }
        if ($i === false) {
            return false;
        }
        return array_slice($states, $i + 1);
    }

    // }}}
    // {{{ NOEXPORT      release::logDownload(integer, string, string)

    /**
     * Log release download
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @param string Filename
     */
    static function logDownload($package, $release_id, $file = null)
    {
        global $dbh;

        $dbh->query('UPDATE aggregated_package_stats
            SET downloads=downloads+1
            WHERE
                package_id=? AND
                release_id=? AND
                yearmonth="' . date('Y-m-01') . '"',
            array($package, $release_id));
        if ($dbh->affectedRows() == 0) {
            $dbh->query('INSERT INTO aggregated_package_stats
                (package_id, release_id, yearmonth, downloads)
                VALUES(?,?,?,1)',
                array($package, $release_id, date('Y-m-01')));
        }

//      This method can be used when we have MySQL 4.1,
//      30% efficiency gain at least over previous method
//      $dbh->query('INSERT INTO aggregated_package_stats
//          (package_id, release_id, yearmonth, downloads)
//          VALUES(?,?,?,1)
//          ON DUPLICATE KEY UPDATE downloads=downloads+1',
//          array($package, $release_id, date('Y-m-01')));

        // {{{ Update package_stats table

//      This method can be used when we have MySQL 4.1,
//      30% efficiency gain at least over previous method
//        $query = 'INSERT INTO package_stats
//                      (dl_number, package, release, pid, rid, cid, last_dl)
//                      VALUES (1, ?, ?, ?, ?, ?, ?)
//                      ON DUPLICATE KEY UPDATE
//                      dl_number=dl_number+1,
//                      last_dl = "' . date('Y-m-d H:i:s') . '"';
//
//        $dbh->query($query, array($pkg_info['name'],
//                                  $version,
//                                  $package,
//                                  $release_id,
//                                  $pkg_info['categoryid'],
//                                  date('Y-m-d H:i:s')
//                                  )
//                    );

        $query = 'UPDATE package_stats '
            . ' SET dl_number = dl_number + 1,'
            . " last_dl = '" . date('Y-m-d H:i:s') . "'"
            . ' WHERE pid = ? AND rid = ?';
        $dbh->query($query, array($package, $release_id));

        if ($dbh->affectedRows() == 0) {
            include_once 'pear-database-package.php';

            $query = 'SELECT version, name, category FROM releases, packages'
                   . ' WHERE package = ? AND releases.id = ? AND packages.id=releases.package';
            $pkginfo = $dbh->getAll($query, array($package, $release_id), DB_FETCHMODE_ASSOC);

            if (PEAR::isError($pkginfo) || !$pkginfo) {
                return PEAR::raiseError('release:: the package you requested'
                                        . ' has no release by that number');
            }

            $query = 'INSERT INTO package_stats'
                   . ' (dl_number, package, `release`, pid, rid, cid, last_dl)'
                   . ' VALUES (1, ?, ?, ?, ?, ?, ?)';

            $dbh->query($query, array($pkginfo[0]['name'],
                                      $pkginfo[0]['version'],
                                      $package,
                                      $release_id,
                                      $pkg_info[0]['category'],
                                      date('Y-m-d H:i:s')
                                      )
                        );
        }

        // }}}
    }

    // }}}

    // {{{ +proto string release::promote(array, string) API 1.0

    /**
     * Promote new release
     *
     * @param array Coming from PEAR_common::infoFromDescFile('package.xml')
     * @param string Filename of the new uploaded release
     * @return void
     */
    static function promote($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
            return;
        }

        include_once 'pear-database-package.php';
        $pacid   = package::info($pkginfo['package'], 'packageid');
        $authors = package::info($pkginfo['package'], 'authors');
        $txt_authors = '';
        foreach ($authors as $a) {
            if (!$a['active']) {
                continue;
            }
            $txt_authors .= $a['name'];
            if ($a['showemail']) {
                $txt_authors .= " <{$a['email']}>";
            }
            $txt_authors .= " ({$a['role']})\n";
        }
        $upload = basename($upload);
        $release = "{$pkginfo['package']}-{$pkginfo['version']} ({$pkginfo['release_state']})";
        $channel = PEAR_CHANNELNAME;
        $txtanounce =<<<END
The new PEAR package $release has been released at http://$channel/.

Release notes
-------------
{$pkginfo['release_notes']}

Package Info
------------
{$pkginfo['description']}

Related Links
-------------
Package home: http://$channel/package/$pkginfo[package]
   Changelog: http://$channel/package/$pkginfo[package]/download/$pkginfo[version]
    Download: http://download.$channel/package/$upload

Authors
-------
$txt_authors
END;

        $to   = '"PEAR general list" <' . PEAR_GENERAL_EMAIL . '>';
        $from = '"PEAR Announce" <' . PEAR_ANNOUNCE_EMAIL . '>';
        $subject = "[ANNOUNCEMENT] $release Released.";
        mail($to, $subject, $txtanounce, "From: $from", "-f bounce-no-user@php.net");
    }

    // }}}
    // {{{ +proto string release::promote_v2(array, string) API 1.0

    /**
     * Promote new release
     *
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string Filename of the new uploaded release
     * @return void
     */
    static function promote_v2($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
            return;
        }

        include_once 'pear-database-package.php';
        $pacid   = package::info($pkginfo->getPackage(), 'packageid');
        $authors = package::info($pkginfo->getPackage(), 'authors');
        $txt_authors = '';
        foreach ($authors as $a) {
            if (!$a['active']) {
                continue;
            }
            $txt_authors .= $a['name'];
            if ($a['showemail']) {
                $txt_authors .= " <{$a['email']}>";
            }
            $txt_authors .= " ({$a['role']})\n";
        }
        $upload = basename($upload);
        $release = $pkginfo->getPackage() . '-' . $pkginfo->getVersion() .
             ' (' . $pkginfo->getState() . ')';
        $txtanounce ='The new PEAR package ' . $release . ' has been released at http://' .
        PEAR_CHANNELNAME . '/.

Release notes
-------------
' . $pkginfo->getNotes() . '

Package Info
------------
' . $pkginfo->getDescription() . '

Related Links
-------------
Package home: http://' . PEAR_CHANNELNAME . '/package/' . $pkginfo->getPackage() . '
   Changelog: http://' . PEAR_CHANNELNAME . '/package/' . $pkginfo->getPackage() . '/download/' .
        $pkginfo->getVersion() . '
    Download: http://download.' . PEAR_CHANNELNAME . '/package/' . $upload . '

Authors
-------
' . $txt_authors;

        $to   = '"PEAR general list" <' . PEAR_GENERAL_EMAIL . '>';
        $from = '"PEAR Announce" <' . PEAR_ANNOUNCE_EMAIL . '>';
        $subject = "[ANNOUNCEMENT] $release Released.";
        mail($to, $subject, $txtanounce, "From: $from", "-f bounce-no-user@php.net");
    }

    // }}}
    // {{{ NOEXPORT      release::remove(int, int)

    /**
     * Remove release
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @return boolean
     */
    static function remove($package, $release)
    {
        global $dbh, $auth_user;
        include_once 'pear-database-user.php';
        if (!$auth_user->isAdmin() && !$auth_user->isQA() &&
            !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('release::remove: insufficient privileges');
        }

        $success = true;

        // get files that have to be removed
        $query = sprintf("SELECT fullpath FROM files WHERE package = '%s' AND release = '%s'",
                         $package,
                         $release);

        $sth = $dbh->query($query);

        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            if (!@unlink($row['fullpath'])) {
                $success = false;
            }
        }

        $query = sprintf("DELETE FROM files WHERE package = '%s' AND `release` = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);

        include_once 'pear-database-package.php';
        $pname = package::info($package, 'name');
        $version = $dbh->getOne('SELECT version from releases WHERE package = ? and id = ?',
            array($package, $release));
        $query = sprintf("DELETE FROM releases WHERE package = '%s' AND id = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);
        // remove statistics on this release
        $dbh->query('DELETE FROM package_stats WHERE pid = ? AND rid = ?', array($package, $release));
        $dbh->query('DELETE FROM aggregated_package_stats WHERE package_id = ? AND release_id = ?', array($package, $release));
        $GLOBALS['pear_rest']->saveAllReleasesREST($pname);
        $GLOBALS['pear_rest']->deleteReleaseREST($pname, $version);
        $GLOBALS['pear_rest']->savePackagesCategoryREST(package::info($pname, 'category'));

        if (PEAR::isError($sth)) {
            return false;
        }

        return true;
    }

    // }}}
    // {{{ getFAQ()

    /**
     * Get FAQ items for given package version
     *
     * @param string Name of the package
     * @param string Version string of the package
     * @return mixed PEAR_Error or Array
     */
    static function getFAQ($package, $version)
    {
        global $dbh;

        $query = "SELECT f.* FROM packages_faq f, packages p, releases r "
            . "WHERE p.name = ? AND p.id = r.package AND r.version = ? AND r.id = f.release";

        return $dbh->getAll($query, array($package, $version), DB_FETCHMODE_ASSOC);
    }
    // }}}
}

