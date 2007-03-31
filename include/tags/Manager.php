<?php
class Tags_Manager
{
    private $dbh;
    function __construct()
    {
        $this->dbh = $GLOBALS['dbh'];
    }

    function getPackages($tag)
    {
        if (!($id = $this->tagExists($tag))) {
            throw new Exception('Unknown tag "' . $tag . '"');
        }
        return $this->dbh->getAll('SELECT
                p.name,
                c.id as catid,
                c.name as category,
                p.summary
            FROM tag_package_link l, packages p, categories c
            WHERE
                tagid=? AND
                p.id=l.package_id AND
                c.id=p.category
            ORDER BY p.name', array($id), DB_FETCHMODE_ASSOC);
    }

    function getTags($package = false, $filter = false)
    {
        if ($package) {
            $pid = package::info($package, 'id');
            if (!$pid) {
                throw new Exception('Unknown package "' . $package . '"');
            }
            $psql = 'package_id=?';
        } else {
            if ($filter) {
                if (auth_check('pear.admin')) {
                    return $this->dbh->getAssoc('SELECT tagid, tagname FROM
                        tagnames
                        ORDER BY tagname', false, array(), DB_FETCHMODE_ASSOC);
                } else {
                    return $this->dbh->getAssoc('SELECT tagid, tagname FROM
                        tagnames
                        WHERE tagnames.adminkey <> 1
                        ORDER BY tagname', false, array(), DB_FETCHMODE_ASSOC);
                }
            } else {
                return $this->dbh->getAll('SELECT tagid, tagname, tagdesc, adminkey FROM
                    tagnames
                    ORDER BY tagname', array(), DB_FETCHMODE_ASSOC);
            }
        }
        if ($filter) {
            if (auth_check('pear.admin')) {
                return $this->dbh->getAssoc('SELECT tagnames.tagid, tagname, tagdesc, adminkey FROM
                    tag_package_link, tagnames
                    WHERE
                        ' . $psql . ' AND tagnames.tagid = tag_package_link.tagid
                    GROUP BY tagnames.tagid
                    ORDER BY tagname', false, array($pid), DB_FETCHMODE_ASSOC);
            } else {
                return $this->dbh->getAssoc('SELECT tagnames.tagid, tagname, tagdesc, adminkey FROM
                    tag_package_link, tagnames
                    WHERE
                        ' . $psql . ' AND tagnames.tagid = tag_package_link.tagid
                        AND tagnames.adminkey <> 1
                    GROUP BY tagnames.tagid
                    ORDER BY tagname', false, array($pid), DB_FETCHMODE_ASSOC);
            }
        }
        return $this->dbh->getAll('SELECT tagnames.tagid, tagname, tagdesc, adminkey FROM
            tag_package_link, tagnames
            WHERE
                ' . $psql . ' AND tagnames.tagid = tag_package_link.tagid
            GROUP BY tagnames.tagid
            ORDER BY tagname', array($pid), DB_FETCHMODE_ASSOC);
    }

    function getTagCloud($package)
    {
        require_once 'tags/Cloud.php';
        $tags = new Pearweb_TagCloud();
        $ptags = $this->getTags($package);
        foreach ($ptags as $info) {
            $tags->addElement($info['tagname'], '/tags/packages.php?tag=' .
                $info['tagname'],
                $this->dbh->getOne('SELECT COUNT(*) FROM tag_package_link
                    WHERE tagid=' . $info['tagid']),
                time(), $info['tagdesc']);
        }
        return array('css' => $tags->buildCSS(), 'html' => $tags->buildHTML());
    }

    function getGlobalTagCloud()
    {
        require_once 'tags/Cloud.php';
        $tags = new Pearweb_TagCloud();
        $ptags = $this->getTags();
        foreach ($ptags as $info) {
            $tags->addElement($info['tagname'], '/tags/packages.php?tag=' .
                $info['tagname'],
                $this->dbh->getOne('SELECT COUNT(*) FROM tag_package_link
                    WHERE tagid=' . $info['tagid']),
                time(), $info['tagdesc']);
        }
        return array('css' => $tags->buildCSS(), 'html' => $tags->buildHTML());
    }

    function tagExists($tag)
    {
        return $this->dbh->getOne('SELECT tagid from tagnames WHERE tagname=?', array($tag));
    }

    function deleteTag($tid)
    {
        if (!is_int($tid) && !($tid = $this->tagExists($tid))) {
            return;
        }
        $admin = $this->dbh->getOne('SELECT adminkey FROM tagnames WHERE tagid=?',
            array($tid));
        if ($admin) {
            if (!auth_check('pear.group') && !auth_check('pear.admin')) {
                throw new Exception('User is not authorized to delete administrative tags');
            }
        } else {
            if (!auth_check('pear.dev')) {
                throw new Exception('User is not authorized to delete regular tags');
            }
        }
        $this->dbh->query('DELETE FROM tag_package_link WHERE tagid=?', array($tid));
        $this->dbh->query('DELETE FROM tagnames WHERE tagid=?', array($tid));
    }

    function validateNewTag($tag, $desc, $admin)
    {
        $errors = array();
        if (!preg_match('/^[a-zA-Z_\.0-9]+$/', $tag)) {
            $errors[] = 'Invalid tag name, must be letters, numbers, underscore (_) or period (.)';
        }
        if (empty($desc)) {
            $errors[] = 'Description cannot be empty';
        }
        if (strlen($desc) > 200) {
            $errors[] = 'Description is too long (must be 200 chars or less)';
        }
        if ($admin && !auth_check('pear.admin')) {
            $errors[] = 'Only PEAR administrators can create administrative tags';
        }
        if (!$admin && !auth_check('pear.dev')) {
            $errors[] = 'Only PEAR developers can create tags';
        }
        return $errors;
    }

    function createAdminTag($tag, $description)
    {
        if (!auth_check('pear.group') && !auth_check('pear.admin')) {
            throw new Exception('User is not authorized to add administrative tags');
        }
        if ($this->tagExists($tag)) {
            throw new Exception('Tag "' . $tag . '" already exists');
        }
        $this->dbh->query('INSERT INTO tagnames (tagname, tagdesc, adminkey) VALUES
            (?,?,1)', array($tag, $description));
    }

    function createRegularTag($tag, $description)
    {
        if (!auth_check('pear.dev')) {
            throw new Exception('User is not authorized to add tags');
        }
        if ($this->tagExists($tag)) {
            throw new Exception('Tag "' . $tag . '" already exists');
        }
        $this->dbh->query('INSERT INTO tagnames (tagname, tagdesc, adminkey) VALUES
            (?,?,0)', array($tag, $description));
    }

    function createPackageTag($tag, $package, $create = false)
    {
        if (!is_int($package)) {
            $package = package::info($package, 'id');
        }
        if (!$package) {
            throw new Exception('Tag "' . $tag . '" cannot be applied to an unknown package');
        }
        if (is_numeric($tag)) {
            $tagid = $tag;
        } else {
            $tagid = $this->tagExists($tag);
        }
        if (!$tagid && $create) {
            $this->createRegularTag($tag, $tag);
        } elseif (!$tagid) {
            throw new Exception('Tag "' . $tag . '" must be created before using');
        }
        if ($this->dbh->getOne('SELECT adminkey FROM tagnames WHERE tagid=?',
            array($tagid))) {
            if (!auth_check('pear.group') && !auth_check('pear.admin')) {
                throw new Exception('Only PEAR administrators can set or remove this tag');
            }
        }
        if (!$this->dbh->getOne('SELECT package_id FROM tag_package_link WHERE
                        package_id=? AND tagid=?', array($package, $tagid))) {
            $this->dbh->query('INSERT INTO tag_package_link (package_id, tagid) VALUES(?,?)',
                array($package, $tagid));
        }
    }

    function clearTags($package)
    {
        $admin = auth_check('pear.admin') || auth_check('pear.group');
        foreach ($this->getTags($package) as $tag) {
            if ($tag['adminkey'] && !$admin) continue;
            $this->removePackageTag($tag['tagid'], $package);
        }
    }

    function removePackageTag($tag, $package)
    {
        if (!is_int($package)) {
            $package = package::info($package, 'id');
        }
        if (!is_numeric($tag)) {
            $tagid = $this->tagExists($tag);
        } else {
            $tagid = $tag;
        }
        if (!$package || !$tagid) {
            return;
        }
        if ($this->dbh->getOne('SELECT adminkey FROM tagnames WHERE tagid=?',
            array($tagid))) {
            if (!auth_check('pear.group') && !auth_check('pear.admin')) {
                throw new Exception('Only PEAR administrators can set or remove this tag');
            }
        }
        $this->dbh->query('DELETE FROM tag_package_link WHERE package_id=? AND tagid=?',
            array($package, $tagid));
    }
}