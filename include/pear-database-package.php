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
 * Class to handle packages
 *
 * @class   package
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class package
{
    /**
     * Add new package
     *
     * @param array
     * @return mixed ID of new package or PEAR error object
     */
    static function add($data)
    {
        global $dbh, $auth_user;
        // name, category
        // license, summary, description
        // lead
        extract($data);
        if (empty($license)) {
            $license = 'BSD License';
        }
        if (!empty($category) && (int)$category == 0) {
            $sql = 'SELECT id FROM categories WHERE name = ?';
            $category = $dbh->getOne($sql, array($category));
        }
        if (empty($category)) {
            return PEAR::raiseError("package::add: invalid `category' field");
        }

        if (empty($name)) {
            return PEAR::raiseError("package::add: invalid `name' field");
        }

        $query = '
            INSERT INTO packages
                (id, name, package_type, category, license, summary, description, homepage, cvs_link)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $id = $dbh->nextId('packages');
        $err = $dbh->query($query, array($id, $name, $type, $category, $license, $summary, $description, $homepage, $cvs_link));
        if (DB::isError($err)) {
            return $err;
        }
        $sql = 'UPDATE categories SET npackages = npackages + 1 WHERE id = ?';
        $err = $dbh->query($sql, array($category));
        if (DB::isError($err)) {
            return $err;
        }

        include_once 'pear-database-maintainer.php';
        $err = maintainer::add($id, $lead, 'lead');
        if (isset($lead) && DB::isError($err)) {
            return $err;
        }

        $event = $auth_user->handle . " (" . $auth_user->name . ") has added a new package " . $name;
        $mailtext = $event . "\n\nApprove: http://" . PEAR_CHANNELNAME . "/admin/package-approval.php?approve=" . $id;
        $mailtext .= "\nReject: http://" . PEAR_CHANNELNAME . "/admin/package-approval.php?reject=" . $id;
        // {{{ Logging mechanism
        require_once "Damblan/Log.php";
        require_once "Damblan/Log/Mail.php";

        // Syslog
        $logger = new Damblan_Log;
        $logger->log($event);

        // Logging via email
        $logger = new Damblan_Log_Mail;
        $logger->setRecipients("pear-group@php.net");
        $logger->setHeader("From", $auth_user->email);
        $logger->setHeader("Message-Id", "<approve-request-" . $id . "@" . PEAR_CHANNELNAME . ">");
        $logger->setHeader("Subject", "New package");
        $logger->log($mailtext);
        // }}}
        return $id;
    }

    /**
     * Get package information
     *
     * @static
     * @param  mixed   Name of the package or it's ID
     * @param  string  Single field to fetch
     * @param  boolean Look in all channels
     * @return mixed
     */
    static function info($pkg, $field = null, $show_all = false)
    {
        global $dbh;
        $what = is_numeric($pkg) ? 'id' : 'name';

        $package_type = '';
        if ($show_all === false) {
             $package_type = "p.package_type = '" . SITE .  "' AND";
        }

        $pkg_sql = "SELECT
            p.id AS packageid,
            p.name AS name,
            p.package_type AS type,
            c.id AS categoryid,
            c.name AS category,
            p.stablerelease AS stable,
            p.license AS license,
            p.summary AS summary,
            p.homepage AS homepage,
            p.description AS description,
            p.cvs_link AS cvs_link,
            p.doc_link as doc_link,
            p.bug_link as bug_link,
            p.unmaintained AS unmaintained,
            p.newpk_id AS newpk_id,
            p.newpackagename as new_package,
            p.newchannel as new_channel,
            p.blocktrackbacks
            FROM packages p, categories c
            WHERE " . $package_type . " p.approved = 1 AND c.id = p.category AND p.{$what} = ?";

        $rel_sql = '
            SELECT
                version, id, doneby, license, summary,
                description, releasedate, releasenotes, state
            FROM releases
            WHERE package = ?
            ORDER BY releasedate DESC';

        $notes_sql = 'SELECT id, nby, ntime, note FROM notes WHERE pid = ?';

        $deps_sql = '
            SELECT type, relation, version, name, `release` as `release`, optional
            FROM deps
            WHERE package = ? ORDER BY optional ASC';

        if ($field === null) {
            $info = $dbh->getRow($pkg_sql, array($pkg), DB_FETCHMODE_ASSOC);
            $info['releases'] = $dbh->getAssoc($rel_sql, false, array(@$info['packageid']), DB_FETCHMODE_ASSOC);
            $rels = count($info['releases']) ? array_keys($info['releases']) : array('');
            $info['stable'] = $rels[0];
            $info['notes']  = $dbh->getAssoc($notes_sql, false, array(@$info['packageid']), DB_FETCHMODE_ASSOC);
            $deps = $dbh->getAll($deps_sql, array(@$info['packageid']), DB_FETCHMODE_ASSOC);
            foreach ($deps as $dep) {
                $rel_version = null;
                foreach ($info['releases'] as $version => $rel) {
                    if ($rel['id'] == $dep['release']) {
                        $rel_version = $version;
                        break;
                    }
                }
                if ($rel_version !== null) {
                    unset($dep['release']);
                    $info['releases'][$rel_version]['deps'][] = $dep;
                }
            }

            return $info;
        }

        // get a single field
        if ($field == 'releases' || $field == 'notes') {
            if ($what == 'name') {
                $sql = 'SELECT p.id FROM packages p WHERE ' . $package_type . ' p.approved = 1 AND p.name = ?';
                $pid = $dbh->getOne($sql, array($pkg));
            } else {
                $pid = $pkg;
            }
            if ($field == 'releases') {
                $info = $dbh->getAssoc($rel_sql, false, array($pid), DB_FETCHMODE_ASSOC);
            } elseif ($field == 'notes') {
                $info = $dbh->getAssoc($notes_sql, false, array($pid), DB_FETCHMODE_ASSOC);
            }
        } elseif ($field == 'category') {
            $sql = "SELECT c.name FROM categories c, packages p ".
                 "WHERE c.id = p.category AND " . $package_type . " p.approved = 1 AND p.{$what} = ?";
            $info = $dbh->getOne($sql, array($pkg));
        } elseif ($field == 'description') {
            $sql = "SELECT description FROM packages p WHERE " . $package_type . " p.approved = 1 AND p.{$what} = ?";
            $info = $dbh->query($sql, array($pkg));
        } elseif ($field == 'authors') {
            $sql = "SELECT u.handle, u.name, u.email, u.showemail, m.active, m.role
                    FROM maintains m, users u, packages p
                    WHERE " . $package_type ." p.approved = 1 AND m.package = p.id
                    AND p.$what = ?
                    AND m.handle = u.handle";
            $info = $dbh->getAll($sql, array($pkg), DB_FETCHMODE_ASSOC);
        } else {
            if ($field == 'categoryid') {
                $dbfield = 'category';
            } elseif ($field == 'packageid') {
                $dbfield = 'id';
            } else {
                $dbfield = $field;
            }
            $sql = "SELECT $dbfield FROM packages p WHERE " . $package_type ." p.approved = 1 AND p.{$what} = ?";
            $info = $dbh->getOne($sql, array($pkg));
        }

        return $info;
    }

    /**
     * Lists the IDs and names of all approved PEAR packages
     *
     * Returns an associative array where the key of each element is
     * a package ID, while the value is the name of the corresponding
     * package.
     *
     * @static
     * @return array
     */
    static function listAllNames()
    {
        global $dbh;
        $sql = 'SELECT id, name FROM packages WHERE package_type = ? AND approved = 1 ORDER BY name';
        return $dbh->getAssoc($sql, false, array(SITE));
    }

    /**
     * List all packages
     *
     * @static
     * @param boolean Only list released packages?
     * @param boolean If listing released packages only, only list stable releases?
     * @param boolean List also PECL packages
     * @return array
     */
    static function listAll($released_only = true, $stable_only = true)
    {
        global $dbh;

        $sql = '
            SELECT
                p.name,
                p.id AS packageid,
                c.id AS categoryid,
                c.name AS category,
                p.license AS license,
                p.summary AS summary,
                p.description AS description,
                m.handle AS lead
            FROM packages p, categories c, maintains m
            WHERE
                p.package_type = ? AND
                p.approved = 1 AND
                c.id = p.category AND
                p.id = m.package AND
                m.role = ?
            ORDER BY p.name';
        $packageinfo = $dbh->getAssoc($sql, false, array(SITE, 'lead'), DB_FETCHMODE_ASSOC);

        $allreleases = $dbh->getAssoc(
            "SELECT p.name, r.id AS rid, r.version AS stable, r.state AS state ".
            "FROM packages p, releases r ".
            "WHERE p.package_type = ? AND " .
            ' p.id = r.package ' .
            "ORDER BY r.releasedate ASC ", false, array(SITE), DB_FETCHMODE_ASSOC);

        if ($released_only) {
            $stablereleases = $dbh->getAssoc(
                "SELECT p.name, r.id AS rid, r.version AS stable, r.state AS state ".
                "FROM packages p, releases r ".
                "WHERE p.package_type = ? AND " .
                "p.id = r.package ".
                "AND r.state = 'stable' " .
                "ORDER BY r.releasedate ASC ", false, array(SITE), DB_FETCHMODE_ASSOC);
        } else {
            $stablereleases = $allreleases;
        }

        $sql  = 'SELECT package, `release` , type, relation, `version`, name FROM deps';
        $deps = $dbh->getAll($sql, null, DB_FETCHMODE_ASSOC);
        foreach ($packageinfo as $pkg => $info) {
            $packageinfo[$pkg]['stable'] = false;
        }

        foreach ($stablereleases as $pkg => $stable) {
            $packageinfo[$pkg]['stable'] = $stable['stable'];
            $packageinfo[$pkg]['unstable'] = false;
            $packageinfo[$pkg]['state']  = $stable['state'];
        }

        if (!$stable_only) {
            foreach ($allreleases as $pkg => $stable) {
                if ($stable['state'] == 'stable') {
                    if (version_compare($packageinfo[$pkg]['stable'], $stable['stable'], '<')) {
                        // only change it if the version number is newer
                        $packageinfo[$pkg]['stable'] = $stable['stable'];
                    }
                } else {
                    if (!isset($packageinfo[$pkg]['unstable']) ||
                          version_compare($packageinfo[$pkg]['unstable'], $stable['stable'], '<')) {
                        // only change it if the version number is newer
                        $packageinfo[$pkg]['unstable'] = $stable['stable'];
                    }
                }
                $packageinfo[$pkg]['state']  = $stable['state'];
                if (isset($packageinfo[$pkg]['unstable']) && !$packageinfo[$pkg]['stable']) {
                    $packageinfo[$pkg]['stable'] = $packageinfo[$pkg]['unstable'];
                }
            }
        }
        $var = !$stable_only ? 'allreleases' : 'stablereleases';
        foreach (array_keys($packageinfo) as $pkg) {
            $_deps = array();
            foreach ($deps as $dep) {
                if ($dep['package'] == $packageinfo[$pkg]['packageid']
                    && isset($$var[$pkg])
                    && $dep['release'] == $$var[$pkg]['rid'])
                {
                    unset($dep['rid']);
                    unset($dep['release']);
                    if ($dep['type'] == 'pkg' && isset($packageinfo[$dep['name']])) {
                        $dep['package'] = $packageinfo[$dep['name']]['packageid'];
                    } else {
                        $dep['package'] = 0;
                    }
                    $_deps[] = $dep;
                };
            };
            $packageinfo[$pkg]['deps'] = $_deps;
        };

        if ($released_only) {
            if (!$stable_only) {
                foreach ($packageinfo as $pkg => $info) {
                    if (!isset($allreleases[$pkg]) && !isset($stablereleases[$pkg])) {
                        unset($packageinfo[$pkg]);
                    }
                }
            } else {
                foreach ($packageinfo as $pkg => $info) {
                    if (!isset($stablereleases[$pkg])) {
                        unset($packageinfo[$pkg]);
                    }
                }
            }
        }

        return $packageinfo;
    }

    /**
     * Get list of packages and their releases
     *
     * @access public
     * @return array
     * @static
     */
    static function listAllwithReleases()
    {
        global $dbh;

        $query = '
            SELECT
                p.id AS pid, p.name, r.id AS rid, r.version, r.state
            FROM packages p, releases r
            WHERE p.package_type = ? AND p.approved = 1 AND p.id = r.package
            ORDER BY p.name, r.version DESC';
        $sth = $dbh->getAll($query, array(SITE), DB_FETCHMODE_ASSOC);
        if (DB::isError($sth)) {
            return $sth;
        }

        $packages = array();
        foreach ($sth as $row) {
            $packages[$row['pid']]['name'] = $row['name'];
            $packages[$row['pid']]['releases'][] = array(
                'id'      => $row['rid'],
                'version' => $row['version'],
                'state'   => $row['state']
            );
        }

        return $packages;
    }

    /**
     * Updates fields of an existant package
     *
     * @param int $pkgid The package ID to update
     * @param array $data Assoc in the form 'field' => 'value'.
     * @return mixed True or PEAR_Error
     */
    static function updateInfo($pkgid, $data)
    {
        global $dbh, $auth_user;
        $package_id = package::info($pkgid, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError('Package not registered or not approved. Please register it first with "New Package" or wait until it gets approved.');
        }

        if ($auth_user->isAdmin() === false && $auth_user->isQA() === false) {
            include_once 'pear-database-user.php';
            $role = user::maintains($auth_user->handle, $package_id);
            if ($role != 'lead' && $role != 'developer') {
                return PEAR::raiseError('package::updateInfo: insufficient privileges');
            }
        }
        // XXX (cox) what about 'name'?
        $allowed = array('license', 'summary', 'description', 'category');
        $fields = $prep = array();
        foreach ($allowed as $a) {
            if (isset($data[$a])) {
                $fields[] = "$a = ?";
                $prep[]   = $data[$a];
            }
        }
        if (!count($fields)) {
            return;
        }
        $sql = 'UPDATE packages SET ' . implode(', ', $fields) . " WHERE id = $package_id";
        $row = package::info($pkgid, 'name');

        include_once 'pear-rest.php';
        $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
        $pear_rest->saveAllPackagesREST();
        $pear_rest->savePackageREST($row);
        $pear_rest->savePackagesCategoryREST(package::info($pkgid, 'category'));
        return $dbh->query($sql, $prep);
    }

    /**
     * Get packages that depend on the given package
     *
     * @param  string Name of the package
     * @return array  List of package that depend on $package
     */
    static function getDependants($package)
    {
        global $dbh;
        $query = '
            SELECT
                p.name AS p_name,
                MAX(r.version) AS max_dep,
                MAX(rm.version) as max_pkg
            FROM packages p, deps AS d
            LEFT JOIN releases AS r  ON r.id = d.release
            LEFT JOIN releases AS rm ON rm.package = d.package
            WHERE d.package = p.id AND d.type = ? AND d.name = ?
            GROUP BY d.package
            ORDER BY p.name';
        return $dbh->getAll($query, array('pkg', $package), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get list of recent releases for the given package
     *
     * @param  int Number of releases to return
     * @param  string Name of the package
     * @return array
     */
    static function getRecent($n, $package)
    {
        global $dbh;
        $recent = array();

        $query = '
            SELECT
                p.id AS id,
                p.name AS name,
                p.summary AS summary,
                r.version AS version,
                r.releasedate AS releasedate,
                r.releasenotes AS releasenotes,
                r.doneby AS doneby,
                r.state AS state
            FROM packages p, releases r
            WHERE
                p.package_type = ?
                AND p.approved = 1
                AND p.id = r.package
                AND p.name = ?
            ORDER BY r.releasedate DESC';

        $sth = $dbh->limitQuery($query, 0, $n, array(SITE, $package));
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    /**
     * Determines if the given package is valid
     *
     * @access public
     * @param  string Name of the package
     * @return  boolean
     */
    static function isValid($package)
    {
        global $dbh;
        $query = 'SELECT id FROM packages WHERE package_type = ? AND approved = 1 AND name = ?';
        $sth = $dbh->query($query, array(SITE, $package));
        return ($sth->numRows() > 0);
    }

    /**
     * Enable or disable trackbacks for a package
     *
     * @access public
     * @param  int ID of the package
     * @return void
     */
    static function allowTrackbacks($name, $allow)
    {
        global $dbh;

        $query = 'UPDATE packages
                    SET blocktrackbacks = ' . ((int) !$allow) . '
                WHERE name = ' . $dbh->quoteSmart($name);
        $res = $dbh->query($query);
    }

    /**
     * Generate link for package
     *
     * Returns HTML-code that creates a link to /package/<package>
     *
     * @access public
     * @return string
     */
    function makeLink($name)
    {
        return make_link('/package/' . $name . '/', $name);
    }

    /**
     * Get URL to license text
     *
     * @todo  Add more licenses here
     * @param string Name of the license
     * @param boolean Direct link or htmled one
     * @return string Link to license URL
     */
    static function get_license_link($license = '', $plain = false)
    {
        switch ($license) {
            case 'PHP License 3.01' :
            case 'PHP License' :
            case 'PHP 3.01' :
                $link = 'http://www.php.net/license/3_01.txt';
                break;

            case 'PHP 2.02' :
                $link = 'http://www.php.net/license/2_02.txt';
                break;

            case 'GPL' :
            case 'GNU General Public License' :
                $link = 'http://www.gnu.org/licenses/gpl.html';
                break;

            case 'LGPL' :
            case 'GNU Lesser General Public License' :
                $link = 'http://www.gnu.org/licenses/lgpl.html';
                break;

            case 'BSD' :
            case 'BSD License' :
            case 'New BSD License' :
            case 'New BSD' :
                $link = 'http://www.opensource.org/licenses/bsd-license.php';
                break;

            case 'MIT' :
            case 'MIT License' :
                $link = 'http://www.opensource.org/licenses/mit-license.php';
                break;

            default :
                $link = '';
                break;
        }

        if ($plain && $link != '') {
            return $link;
        }

        return ($link != '' ? '<a class="license" href="' . $link . '" rel="license">' . $license . "</a>\n" : $license);
    }
}