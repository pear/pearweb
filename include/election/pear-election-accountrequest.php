<?php
class PEAR_Election_Accountrequest
{
    var $dbh;
    var $id;
    var $created_on;
    var $handle;
    var $salt;
    var $email;

    function __construct()
    {
        $this->dbh = &$GLOBALS['dbh'];
        $this->user = isset($GLOBALS['auth_user']) ? $GLOBALS['auth_user']->handle : false;
        $this->cleanOldRequests();
    }

    function _makeSalt($handle)
    {
        return(md5(openssl_random_pseudo_bytes(512)));
    }

    function find($salt)
    {
        $sql = '
            SELECT id, created_on, salt, handle
            FROM election_account_request
            WHERE salt = ?';
        $request = $this->dbh->getRow($sql, array($salt), DB_FETCHMODE_ASSOC);

        if (count($request) > 0) {
            foreach ($request as $field => $value) {
                $this->$field = $value;
            }
            return true;
        }
        return false;
    }

    /**
     * Adds a request in the DB
     *
     * @return string salt
     */
    function addRequest($handle, $email, $firstName, $lastName, $pw1, $pw2)
    {
        $data = array(
            'handle'     => $handle,
            'firstname'  => $firstName,
            'lastname'   => $lastName,
            'email'      => $email,
            'password'   => $pw1,
            'password2'  => $pw2,
            'purpose'    => 'vote in general election',
            'fromt_site' => SITE,
            'moreinfo'   => '',
            'homepage'   => '',
        );

        include_once 'pear-database-user.php';
        $useradd = user::add($data, false, true);
        if (is_array($useradd) || DB::isError($useradd)) {
            return $useradd;
        }

        $salt = $this->_makeSalt($handle);
        $created_on = gmdate('Y-m-d');

        $query = '
        INSERT INTO election_account_request (created_on, handle, email, salt)
        VALUES (?, ?, ?, ?)';

        $res = $this->dbh->query($query, array($created_on, $handle, $email, $salt));
        if (DB::isError($res)) {
            user::remove($handle);
            return $res;
        }

        //$this->find($salt);
        //$this->sendRequest();

        return $salt;
    }

    function deleteRequest()
    {
        $query = 'DELETE FROM election_account_request WHERE salt = ?';
        return $this->dbh->query($query, array($this->salt));
    }

    function confirmRequest($salt)
    {
        if (!$this->find($salt)) {
            return PEAR::raiseError('cannot find request');
        }

        // activate the handle and grant karma
        // implicitly without human intervention
        // copied from the user class and Damblan_Karma

        include_once 'pear-database-user.php';
        $user = user::info($this->handle, null, 0);
        if (!isset($user['registered'])) {
            return PEAR::raiseError('Error - user request was deleted, please try again');
        }

        include_once 'pear-database-note.php';
        note::removeAll($this->handle);

        $data = array();
        $data['handle']     = $user['handle'];
        $data['registered'] = 1;
        $data['created']    = gmdate('Y-m-d');
        $data['createdby']  = SITE . 'web';

        $e = user::update($data, true);
        if (PEAR::isError($e) || !$e) {
            return $e;
        }

        $query = 'INSERT INTO karma VALUES (?, ?, ?, ?, NOW())';

        $id = $this->dbh->nextId('karma');
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.voter', SITE . 'web'));
        $id = $this->dbh->nextId('karma');
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', SITE . 'web'));

        if (!DB::isError($sth)) {
            note::add($this->handle, 'Account opened', SITE . 'web');
            $msg = "Your PEAR voter account has been opened.\n"
                . "You can now participate in the elections  by going to\n"
                . "    http://" . PEAR_CHANNELNAME . "/election/";
            $xhdr = "From: " . PEAR_WEBMASTER_EMAIL;
            if (!DEVBOX){
                mail($user['email'], "Your PEAR Account Request", $msg, $xhdr, "-f " . PEAR_BOUNCE_EMAIL);
            }
            $this->deleteRequest();
            return true;
        }

        return false;
    }

    function listRequests()
    {
    }

    function cleanOldRequests()
    {
        $old = gmdate('Y-m-d', strtotime('-1 Day'));
        $findquery = 'SELECT handle FROM election_account_request WHERE created_on < ?';
        $all = $this->dbh->getAll($findquery, array($old));
        // purge reserved usernames as well as their account requests
        if (is_array($all)) {
            foreach ($all as $data) {
                $this->dbh->query('
                    DELETE FROM users WHERE handle = ?
                ', array($data[0]));
                $this->dbh->query('
                    DELETE FROM bugdb WHERE handle = ?
                ', array($data[0]));
                $this->dbh->query('
                    DELETE FROM bugdb_comments WHERE handle = ?
                ', array($data[0]));
            }
        }
        $query = 'DELETE FROM election_account_request WHERE created_on < ?';
        // purge out-of-date account requests
        return $this->dbh->query($query, array($old));
    }
}