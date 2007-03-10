<?php
require_once 'HTTP/Upload.php';
class Bug_Patchtracker
{
    var $_upload;
    var $_patchdir;
    var $_dbh;
    function Bug_Patchtracker()
    {
        if (!file_exists(PEAR_PATCHTRACKER_TMPDIR)) {
            if (!@mkdir(PEAR_PATCHTRACKER_TMPDIR)) {
                $this->_upload = false;
                $this->_dbh = &$GLOBALS['dbh'];
                return;
            }
        }
        $this->_upload = new HTTP_Upload('en');
        $this->_dbh = &$GLOBALS['dbh'];
    }

    /**
     * Return the directory in which patches should be stored
     *
     * @param int $bugid
     * @param string $name name of this patch line
     * @return string
     */
    function patchDir($bugid, $name)
    {
        return PEAR_PATCHTRACKER_TMPDIR . '/p' . $bugid . '/' . $name;
    }
    /**
     * Create the directory in which patches for this bug ID will be stored
     *
     * @param int $bugid
     */
    function setupPatchDir($bugid, $name)
    {
        if (file_exists($this->patchDir($bugid, $name))) {
            if (!is_dir($this->patchDir($bugid, $name))) {
                return PEAR::raiseError('Cannot create patch storage for Bug #' . $bugid .
                    ', storage directory exists and is not a directory');
            }
            return;
        }
        if (!file_exists(dirname($this->patchDir($bugid, $name)))) {
            // setup bug directory
            if (!@mkdir(dirname($this->patchDir($bugid, $name)))) {
                require_once 'PEAR.php';
                return PEAR::raiseError('Cannot create patch storage for Bug #' . $bugid);
            }
        } elseif (!is_dir(dirname($this->patchDir($bugid, $name)))) {
            return PEAR::raiseError('Cannot create patch storage for Bug #' . $bugid .
                ', storage directory exists and is not a directory');
        }
        // setup patch directory
        if (!@mkdir($this->patchDir($bugid, $name))) {
            require_once 'PEAR.php';
            return PEAR::raiseError('Cannot create patch storage for Bug #' . $bugid);
        }
    }

    /**
     * Retrieve a unique, ordered patch filename
     *
     * @param int $bugid
     * @param string $patch
     */
    function newPatchFileName($bugid, $patch, $handle)
    {
        $id = time();
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $e = $this->_dbh->query('INSERT INTO bugdb_patchtracker
            (bugdb_id, patch, revision, developer) VALUES(?, ?, ?, ?)',
            array($bugid, $patch, $id, $handle));
        if (PEAR::isError($e)) {
            // try with another timestamp
            $id++;
            $e = $this->_dbh->query('INSERT INTO bugdb_patchtracker
                (bugdb_id, patch, revision, developer) VALUES(?, ?, ?, ?)',
                array($bugid, $patch, $id, $handle));
        }
        PEAR::popErrorHandling();
        if (PEAR::isError($e)) {
            return PEAR::raiseError('Could not get unique patch file name for bug #' .
                $bugid . ', patch "'. $patch . '"');
        }
        return array($id, $this->getPatchFileName($id));
    }

    /**
     * Retrieve the name of the patch file on the system
     *
     * @param int $id revision ID
     * @return string
     */
    function getPatchFileName($id)
    {
        return 'p' . $id . '.patch.txt';
    }

    /**
     * Retrieve the full path to a patch file
     *
     * @param int $bugid
     * @param string $name
     * @param int $revision
     * @return string
     */
    function getPatchFullpath($bugid, $name, $revision)
    {
        return $this->patchDir($bugid, $name) .
            DIRECTORY_SEPARATOR . $this->getPatchFileName($revision);
    }

    /**
     * Attach a patch to this bug
     *
     * @param int $bugid
     * @param string $patch uploaded patch filename form variable
     * @param string $name patch name (allows several patches to be versioned)
     */
    function attach($bugid, $patch, $name, $handle)
    {
        if (!$this->_upload) {
            return PEAR::raiseError('Upload directory for patches could not be' .
                ' initialized');
        }
        if (!preg_match('/^[\w\-]+$/', $name) || strlen($name) > 40) {
            return PEAR::raiseError('Invalid patch name "' . $name . '"');
        }
        if (PEAR::isError($e = $this->setupPatchDir($bugid, $name))) {
            return $e;
        }
        $file = $this->_upload->getFiles($patch);
        if (PEAR::isError($file)) {
            return $file;
        }
        if ($file->isValid()) {
            $res = $this->newPatchFileName($bugid, $name, $handle);
            if (PEAR::isError($res)) {
                return $res;
            }
            list($id, $fname) = $res;
            $file->setName($fname);
            $file->setValidExtensions(array('txt', 'patch', 'diff'), 'accept');
            $tmpfile = $file->moveTo($this->patchDir($bugid, $name));
            if (PEAR::isError($tmpfile)) {
                $this->_dbh->query('DELETE FROM bugdb_patchtracker
                    WHERE bugdb_id = ? and patch = ? and revision = ?',
                    array($bugid, $name, $id));
                return $tmpfile;
            }
            if (!$file->getProp('size')) {
                $this->detach($bugid, $name, $id);
                return PEAR::raiseError('zero-length patches not allowed');
            }
            if ($file->getProp('size') > 10240) {
                $this->detach($bugid, $name, $id);
                return PEAR::raiseError('Patch files cannot be larger than 10k');
            }
            return $id;
        } elseif ($file->isMissing()) {
            return PEAR::raiseError('No patch has been uploaded.');
        } elseif ($file->isError()) {
            return $file;
        }
        return PEAR::raiseError('Unable to attach patch');
    }

    /**
     * Remove a patch revision from this bug
     *
     * @param int $bugid
     * @param string $name
     * @param int $revision
     */
    function detach($bugid, $name, $revision)
    {
        $this->_dbh->query('DELETE FROM bugdb_patchtracker
            WHERE bugdb_id = ? and patch = ? and revision = ?',
            array($bugid, $name, $revision));
        @unlink($this->patchDir($bugid, $name) . DIRECTORY_SEPARATOR .
            $this->getPatchFileName($revision));
    }

    /**
     * Retrieve the actual contents of the patch
     *
     * @param int $bugid
     * @param string $name
     * @param int $revision
     * @return string
     */
    function getPatch($bugid, $name, $revision)
    {
        if ($this->_dbh->getOne('SELECT bugdb_id FROM bugdb_patchtracker
              WHERE bugdb_id = ? AND patch = ? AND revision = ?',
              array($bugid, $name, $revision))) {
            $contents = @file_get_contents($this->getPatchFullpath($bugid, $name, $revision));
            if (!$contents) {
                return PEAR::raiseError('Cannot retrieve patch revision "' .
                    $revision . '" for patch "' . $name . '"');
            }
            return $contents;
        }
        return PEAR::raiseError('No such patch revision "' .
            $revision . '", or no such patch "' . $name . '"');
    }

    /**
     * Retrieve a listing of all patches and their revisions
     *
     * @param int $bugid
     * @return array
     */
    function listPatches($bugid)
    {
        return $this->_dbh->getAssoc(
            'SELECT patch, revision, developer FROM bugdb_patchtracker WHERE bugdb_id = ?
             ORDER BY revision DESC',
            false, array($bugid),
            DB_FETCHMODE_ORDERED, true
        );
    }

    /**
     * Retrieve a listing of all patches and their revisions
     *
     * @param int $bugid
     * @param string $patch
     * @return array
     */
    function listRevisions($bugid, $patch)
    {
        return $this->_dbh->getAll(
            'SELECT revision FROM bugdb_patchtracker WHERE bugdb_id = ? AND
             patch = ?
             ORDER BY revision DESC', array($bugid, $patch),
            DB_FETCHMODE_ORDERED
        );
    }

    /**
     * Retrieve the developer who uploaded this patch
     *
     * @param int $bugid
     * @param string $patch
     * @param int $revision
     * @return string|array array if no revision is selected
     */
    function getDeveloper($bugid, $patch, $revision = false)
    {
        if ($revision) {
            return $this->_dbh->getOne(
                'SELECT developer FROM bugdb_patchtracker
                 WHERE bugdb_id=? AND patch=? AND revision=?
                ', array($bugid, $patch, $revision));
        }
        return $this->_dbh->getAll(
            'SELECT developer,revision FROM bugdb_patchtracker
             WHERE bugdb_id=? AND patch=? ORDER BY revision DESC',
            array($bugid, $patch), DB_FETCHMODE_ASSOC
        );
    }

    /**
     * Retrieve information on a bug
     *
     * @param int $bugid
     * @return array
     */
    function getBugInfo($bugid)
    {
        $bugid = (int) $bugid;
        $info = $this->_dbh->getAll('SELECT * FROM bugdb WHERE id=?', array($bugid),
            DB_FETCHMODE_ASSOC);
        if (!is_array($info) || !count($info)) {
            return PEAR::raiseError('No such bug "' . $bugid . '"');
        }
        return $info[0];
    }
}
?>