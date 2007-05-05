<?php

class user
{
    static function remove($uid)
    {
        global $dbh;

        include_once 'pear-database-note.php';
        note::removeAll("uid", $uid);
        $GLOBALS['pear_rest']->deleteMaintainerREST($uid);
        $GLOBALS['pear_rest']->saveAllMaintainersREST();
        $dbh->query('DELETE FROM users WHERE handle = '. $dbh->quote($uid));
        return ($dbh->affectedRows() > 0);
    }

    // {{{ *proto bool   user::rejectRequest(string, string) API 1.0

    static function rejectRequest($uid, $reason)
    {
        global $dbh, $auth_user;
        list($email) = $dbh->getRow('SELECT email FROM users WHERE handle = ?',
                                    array($uid));

        include_once 'pear-database-note.php';
        note::add("uid", $uid, "Account rejected: $reason");
        $msg = "Your PEAR account request was rejected by " . $auth_user->handle . ":\n\n".
             "$reason\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";
        mail($email, "Your PEAR Account Request", $msg, $xhdr, "-f bounce-no-user@php.net");
        return true;
    }

    // }}}
    // {{{ *proto bool   user::activate(string) API 1.0

    static function activate($uid, $karmalevel = 'pear.dev')
    {
        require_once 'Damblan/Karma.php';

        global $dbh, $auth_user;

        $karma = new Damblan_Karma($dbh);

        $user = user::info($uid);
        if (!isset($user['registered'])) {
            return false;
        }
        @$arr = unserialize($user['userinfo']);

        include_once 'pear-database-note.php';
        note::removeAll('uid', $uid);

        $data = array();
        $data['registered'] = 1;
        $data['active'] = 1;
        /* $data['ppp_only'] = 0; */
        if (is_array($arr)) {
            $data['userinfo'] = $arr[1];
        }
        $data['created']   = gmdate('Y-m-d H:i');
        $data['createdby'] = $auth_user->handle;
        $data['handle']    = $user['handle'];

        user::update($data);

        $karma->grant($user['handle'], $karmalevel);
        if ($karma->has($user['handle'], 'pear.dev')) {
            $GLOBALS['pear_rest']->saveMaintainerREST($user['handle']);
            $GLOBALS['pear_rest']->saveAllMaintainersREST();
        }

        include_once 'pear-database-note.php';
        note::add("uid", $uid, "Account opened");
        $msg = "Your PEAR account request has been opened.\n".
             "To log in, go to http://" . PEAR_CHANNELNAME . "/ and click on \"login\" in\n".
             "the top-right menu.\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";
        mail($user['email'], "Your PEAR Account Request", $msg, $xhdr, "-f bounce-no-user@php.net");
        return true;
    }

    // }}}
    // {{{ +proto bool   user::isAdmin(string) API 1.0

    static function isAdmin($handle)
    {
        require_once "Damblan/Karma.php";

        global $dbh;
        $karma = new Damblan_Karma($dbh);

        return $karma->has($handle, "pear.admin");
    }

    // }}}
    // {{{ +proto bool   user::isQA(string) API post 1.0

    static function isQA($handle)
    {
        require_once 'Damblan/Karma.php';

        global $dbh;
        $karma = new Damblan_Karma($dbh);

        return $karma->has($handle, 'pear.qa');
    }

    // }}}
    // {{{  proto bool   user::listAdmins() API 1.0

    static function listAdmins()
    {
        require_once "Damblan/Karma.php";

        global $dbh;
        $karma = new Damblan_Karma($dbh);

        return $karma->getUser("pear.admin");
    }

    // }}}
    // {{{ +proto bool   user::exists(string) API 1.0

    static function exists($handle)
    {
        global $dbh;
        $sql = "SELECT handle FROM users WHERE handle=?";
        $res = $dbh->query($sql, array($handle));
        return ($res->numRows() > 0);
    }

    // }}}
    // {{{ +proto string user::maintains(string|int, [string]) API 1.0

    static function maintains($user, $pkgid, $role = 'any')
    {
        global $dbh;
        include_once 'pear-database-package.php';
        $package_id = package::info($pkgid, 'id');
        if ($role == 'any') {
            return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? '.
                                'AND package = ?', array($user, $package_id));
        }
        if (is_array($role)) {
            return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                                'AND role IN ("?")', array($user, $package_id, implode('","', $role)));
        }
        return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                            'AND role = ?', array($user, $package_id, $role));
    }

    // }}}
    // {{{

    static function getPackages($user)
    {
        global $dbh;
        $query = 'SELECT p.id, p.name, m.role, m.active'
            . ' FROM packages p, maintains m'
            . ' WHERE m.handle = ? AND p.id = m.package AND p.package_type = "pear"'
            . ' ORDER BY p.name';

        return $dbh->getAll($query, array($user));
    }

    // }}}
    // {{{  proto string user::info(string, [string], [boolean]) API 1.0

    static function info($user, $field = null, $registered = true, $hidePassword = true)
    {
        global $dbh;

        $handle = strpos($user, '@') ? 'email' : 'handle';

        if ($field === null) {
            $registered = $registered === true ? '1' : '0';
            $row = $dbh->getRow('SELECT * FROM users WHERE registered = ? AND ' . $handle . ' = ?',
                                array($registered, $user), DB_FETCHMODE_ASSOC);
            if ($hidePassword) {
                unset($row['password']);
            }
            return $row;
        }

        if (($field == 'password' && $hidePassword) || preg_match('/[^a-z]/', $user)) {
            return null;
        }

        return $dbh->getRow('SELECT ! FROM users WHERE handle = ?',
                            array($field, $user), DB_FETCHMODE_ASSOC);

    }

    // }}}
    // {{{ listAll()

    static function listAll($registered_only = true)
    {
        global $dbh;
        $query = "SELECT * FROM users";
        if ($registered_only === true) {
            $query .= " WHERE registered = 1";
        }
        $query .= " ORDER BY handle";
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{ add()

    /**
     * Add a new user account
     *
     * During most of this method's operation, PEAR's error handling
     * is set to PEAR_ERROR_RETURN.
     *
     * But, during the DB_storage::set() phase error handling is set to
     * PEAR_ERROR_CALLBACK the report_warning() function.  So, if an
     * error happens a warning message is printed AND the incomplete
     * user information is removed.
     *
     * @param array   $data  Information about the user
     * @param boolean $md5ed true if the password has been hashed already
     * @param boolean $automatic true if this is an automatic account request
     *
     * @return mixed  true if there are no problems, false if sending the
     *                email failed, 'set error' if DB_storage::set() failed
     *                or an array of error messages for other problems
     *
     * @access public
     */
    static function add(&$data, $md5ed = false, $automatic = false)
    {
        global $dbh;

        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $errors = array();

        $required = array(
            'handle'     => 'Username',
            'firstname'  => 'First Name',
            'lastname'   => 'Last Name',
            'email'      => 'Email address',
            'purpose'    => 'Intended purpose',
        );

        $name = $data['firstname'] . " " . $data['lastname'];

        foreach ($required as $field => $desc) {
            if (empty($data[$field])) {
                $data['jumpto'] = $field;
                $errors[] = 'Please enter ' . $desc;
            }
        }

        if (!preg_match(PEAR_COMMON_USER_NAME_REGEX, $data['handle'])) {
            $errors[] = 'Username must start with a letter and contain'
                      . ' only letters and digits';
        }

        // Basic name validation

        // First- and lastname must be longer than 1 character
        if (strlen($data['firstname']) == 1) {
            $errors[] = 'Your firstname appears to be too short.';
        }
        if (strlen($data['lastname']) == 1) {
            $errors[] = 'Your lastname appears to be too short.';
        }

        // Firstname and lastname must start with an uppercase letter
        if (!preg_match("/^[A-Z]/", $data['firstname'])) {
            $errors[] = 'Your firstname must begin with an uppercase letter';
        }
        if (!preg_match("/^[A-Z]/", $data['lastname'])) {
            $errors[] = 'Your lastname must begin with an uppercase letter';
        }

        // No names with only uppercase letters
        if ($data['firstname'] === strtoupper($data['firstname'])) {
            $errors[] = 'Your firstname must not consist of only uppercase letters.';
        }
        if ($data['lastname'] === strtoupper($data['lastname'])) {
            $errors[] = 'Your lastname must not consist of only uppercase letters.';
        }

        if ($data['password'] != $data['password2']) {
            $data['password'] = $data['password2'] = "";
            $data['jumpto'] = "password";
            $errors[] = 'Passwords did not match';
        }

        if (!$data['password']) {
            $data['jumpto'] = "password";
            $errors[] = 'Empty passwords not allowed';
        }

        $handle = strtolower($data['handle']);
        $info = user::info($handle);

        if (isset($info['created'])) {
            $data['jumpto'] = "handle";
            $errors[] = 'Sorry, that username is already taken';
        }

        if ($errors) {
            $data['display_form'] = true;
            return $errors;
        }

        $data['display_form'] = false;
        $md5pw = $md5ed ? $data['password'] : md5($data['password']);
        $showemail = @(bool)$data['showemail'];
        // hack to temporarily embed the "purpose" in
        // the user's "userinfo" column
        $userinfo = serialize(array($data['purpose'], $data['moreinfo']));
        $set_vars = array(
            'handle'     => $handle,
            'name'       => $name,
            'email'      => $data['email'],
            'homepage'   => $data['homepage'],
            'showemail'  => $showemail,
            'password'   => $md5pw,
            'registered' => 0,
            'userinfo'   => $userinfo
        );

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');

        $sql = '
            INSERT INTO user
                (handle, name, email, homepage, showemail, password, registered, userinfo)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)';

        $err = $dbh->query($sql, $set_vars);
        if (DB::isError($err)) {
            return $err;
        }

        PEAR::popErrorHandling();

        $msg = "Requested from:   {$_SERVER['REMOTE_ADDR']}\n".
               "Username:         {$handle}\n".
               "Real Name:        {$name}\n".
               (isset($data['showemail']) ? "Email:            {$data['email']}\n" : "") .
               "Purpose:\n".
               "{$data['purpose']}\n\n".
               "To handle: http://{$_SERVER['SERVER_NAME']}/admin/?acreq={$handle}\n";

        if ($data['moreinfo']) {
            $msg .= "\nMore info:\n{$data['moreinfo']}\n";
        }

        $xhdr = "From: $name <{$data['email']}>\nMessage-Id: <account-request-{$handle}@" .
            PEAR_CHANNELNAME . ">\n";
        // $xhdr .= "\nBCC: pear-group@php.net";
        $subject = "PEAR Account Request: {$handle}";

        if (!DEVBOX && !$automatic) {
            if (PEAR_CHANNELNAME == 'pear.php.net') {
                $ok = @mail('pear-group@php.net', $subject, $msg, $xhdr,
                            '-f bounce-no-user@php.net');
            }
        } else {
            $ok = true;
        }

        PEAR::popErrorHandling();

        return $ok;
    }

    // }}}
    // {{{ update

    /**
     * Update user information
     *
     * @access public
     * @param  array User information
     * @return object|boolean DB error object on failure, true on success
     */
    static function update($data)
    {
        global $dbh;

        $fields = array(
            'name',
            'email',
            'homepage',
            'showemail',
            'userinfo',
            'pgpkeyid',
            'wishlist',
            'latitude',
            'longitude',
            'active',
            'password',
        );

        $info = user::info($data['handle']);
        // In case a active value isn't passed in
        $active = isset($info['active']) ? $info['active'] : true;

        $change_k = $change_v = array();
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields)) {
                continue;
            }
            $change_k[] = $key;
            $change_v[] = $value;
        }

        $sql = 'UPDATE users SET ' . "\n";
        foreach ($change_k as $k) {
            $sql .= $k . ' = ?,' . "\n";
        }
        $sql = substr($sql, 0, -2);
        $sql.= ' WHERE handle = ?';

        $change_v[] = $data['handle'];
        $err = $dbh->query($sql, $change_v);
        if (DB::isError($err)) {
            return $err;
        }

        if (isset($data['active']) && $data['active'] === 0 && $active) {
            // this user is completely inactive, so mark all maintains as not active.
            $dbh->query('UPDATE maintains SET active=0 WHERE handle=?', array($info['handle']));
        }
        return true;
    }

    // }}}
    // {{{ getRecentReleases(string, [int])

    /**
     * Get recent releases for the given user
     *
     * @access public
     * @param  string Handle of the user
     * @param  int    Number of releases (default is 10)
     * @return array
     */
    static function getRecentReleases($handle, $n = 10)
    {
        global $dbh;
        $recent = array();

        $query = "SELECT p.id AS id, " .
            "p.name AS name, " .
            "p.summary AS summary, " .
            "r.version AS version, " .
            "r.releasedate AS releasedate, " .
            "r.releasenotes AS releasenotes, " .
            "r.doneby AS doneby, " .
            "r.state AS state " .
            "FROM packages p, releases r, maintains m " .
            "WHERE p.package_type = 'pear' AND p.id = r.package " .
            "AND p.id = m.package AND m.handle = '" . $handle . "' " .
            "ORDER BY r.releasedate DESC";

        $sth = $dbh->limitQuery($query, 0, $n);
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ * proto array   user::getWhoIsWho() API 1.0

    /**
     * Get list of current developers and the packages they maintain.
     *
     * The output of this method is used on public websites for
     * a Who Is Who of the PEAR developers.  In order to avoid abuse,
     * access to this method via XML_RPC is granted based on a whitelist
     * of IP addresses.
     *
     * @access public
     * @return array
     */
    static function getWhoIsWho() {
        global $dbh;

        // IP whitelist
        if (!in_array($_SERVER['REMOTE_ADDR'], array('209.61.191.11'))) {
            return array();
        }

        $query_maintainers = "SELECT p.name, m.role, p.package_type "
            . "FROM maintains m, packages p "
            . "WHERE m.package = p.id AND m.handle = ?";
        $maintainers = $dbh->prepare($query_maintainers);

        $group = $dbh->prepare("SELECT COUNT(id) "
                               . "FROM karma "
                               . "WHERE level = 'pear.group' AND user = ?");

        /* The PECL developers don't have the "pear.dev" karma level.
         * Thus every registered user needs to be checked.
         */
        $query = "SELECT handle, name, homepage, userinfo "
            . "FROM users WHERE registered = 1";

        $query_group = "SELECT user FROM karma WHERE level = 'pear.group'";

        $group_ids = $dbh->getCol($query_group);
        $group_ids = array_flip($group_ids);

        $users = $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);

        foreach ($users as $id => $user) {
            // Figure out which packages are maintained by the user
            $sth = $dbh->execute($maintainers, array($user['handle']));

            // Skip if the user is maintaining nothing
            if ($sth->numRows() == 0) {
                unset($users[$id]);
                continue;
            }

            while ($row =& $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
                $users[$id]['maintains'][] = $row;

                if (!isset($users[$id]['type'])) {
                    $users[$id]['type'] = 0;
                }

                // Returned 'type' is 1 for PEAR, 2 for PECL or 3 for both
                switch ($users[$id]['type']) {
                case '3':
                    break;

                case '2':
                    if ($row['package_type'] == 'pear') {
                        $users[$id]['type'] = 3;
                    }
                    break;

                case '1':
                    if ($row['package_type'] == 'pecl') {
                        $users[$id]['type'] = 3;
                    }
                    break;

                default:
                    if ($row['package_type'] == 'pecl') {
                        $users[$id]['type'] = 2;
                    } else {
                        $users[$id]['type'] = 1;
                    }
                    break;
                }
            }

            if (isset($group_ids[$user['handle']])) {
                $users[$id]['group'] = 1;
            } else {
                $users[$id]['group'] = 0;
            }
        }

        return $users;
    }

    // }}}
}