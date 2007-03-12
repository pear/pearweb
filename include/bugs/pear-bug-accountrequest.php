<?php
class PEAR_Bug_Accountrequest
{
    var $dbh;
    var $id;
    var $created_on;
    var $handle;
    var $salt;
    var $email;

    function PEAR_Bug_Accountrequest($handle = false)
    {
        $this->dbh = &$GLOBALS['dbh'];
        if ($handle) {
            $this->user = $handle;
        } else {
            $this->user = isset($GLOBALS['auth_user']) ? $GLOBALS['auth_user']->handle : false;
        }
        $this->cleanOldRequests();
    }

    function pending()
    {
        if (!$this->user) {
            return false;
        }
        $request = $this->dbh->getOne('
            SELECT handle
            FROM bug_account_request
            WHERE handle=?
        ', array($this->user));

        if ($request) {
            return true;
        }
        return false;
    }

    function sendEmail()
    {
        if (!$this->user) {
            return false;
        }
        $salt = $this->dbh->getOne('
            SELECT salt
            FROM bug_account_request
            WHERE handle=?
        ', array($this->user));
        if (!$salt) {
            return false;
        }
        $email = $this->dbh->getOne('
            SELECT email
            FROM bug_account_request
            WHERE handle=?
        ', array($this->user));
        if (!$email) {
            return false;
        }
        $mailData = array(
            'salt' => $salt,
        );
        require_once 'Damblan/Mailer.php';
        $mailer = Damblan_Mailer::create('pearweb_account_request_bug', $mailData);
        $additionalHeaders['To'] = $email;
        $mailer->send($additionalHeaders);
        return true;
    }

    function _makeSalt($handle)
    {
        list($usec, $sec) = explode(" ", microtime());
        return md5($handle . ((float)$usec + (float)$sec));
    }

    function find($salt)
    {
        if (!$salt) {
            return false;
        }
        $request = $this->dbh->getRow('
            SELECT id, created_on, salt, handle, email
            FROM bug_account_request
            WHERE salt=?
        ', array($salt), DB_FETCHMODE_ASSOC);

        if (count($request) > 0) {
            foreach ($request as $field => $value) {
                $this->$field = $value;
            }
            $this->user = $this->handle;
            return true;
        }
        return false;
    }

    /**
     * Adds a request in the DB
     *
     * @return string salt
     */
    function addRequest($email)
    {
        $salt = $this->_makeSalt($email);
        $handle = '#' . substr($salt, 0, 20);
        $created_on = gmdate('Y-m-d H:i:s');

        $query = '
        insert into bug_account_request (created_on, handle, email, salt)
        values (?, ?, ?, ?)';

        $res = $this->dbh->query($query, array($created_on, $handle, $email, $salt));

        if (DB::isError($res)) {
            return $res;
        }

        return $salt;
    }

    function deleteRequest()
    {
        $query = 'delete from bug_account_request where salt=?';

        return $this->dbh->query($query, array($this->salt));
    }

    function validateRequest($handle, $password, $password2, $name)
    {
        $errors = array();
        if (empty($handle) || !preg_match('/^[0-9a-z_]{2,20}$/', $handle)) {
            $errors[] = 'Username is invalid.';
            $display_form = true;
        }

        if ($password == md5('') || empty($password)) {
            $errors[] = 'Password must not be empty';
        }
        if ($password !== $password2) {
            $errors[] = 'Passwords do not match';
        }
        if (user::exists($handle)) {
            $errors[] = 'User name "' . $handle .
                '" already exists, please choose another user name';
        }
        @list($firstname, $lastname) = explode(' ', $name, 2);
        // First- and lastname must be longer than 1 character
        if (strlen($firstname) == 1) {
            $errors[] = 'Your firstname appears to be too short.';
        }
        if (strlen($lastname) == 1) {
            $errors[] = 'Your lastname appears to be too short.';
        }

        // Firstname and lastname must start with an uppercase letter
        if (!preg_match("/^[A-Z]/", $firstname)) {
            $errors[] = 'Your firstname must begin with an uppercase letter';
        }
        if (!preg_match("/^[A-Z]/", $lastname)) {
            $errors[] = 'Your lastname must begin with an uppercase letter';
        }

        // No names with only uppercase letters
        if ($firstname === strtoupper($firstname)) {
            $errors[] = 'Your firstname must not consist of only uppercase letters.';
        }
        if ($lastname === strtoupper($lastname)) {
            $errors[] = 'Your lastname must not consist of only uppercase letters.';
        }
        return $errors;
    }

    function confirmRequest($handle, $password, $name)
    {
        if ($handle == $this->dbh->getOne('SELECT handle FROM users WHERE 
              handle=?', array($handle))) {
            $id = $this->dbh->nextId("karma");

            $query = "INSERT INTO karma VALUES (?, ?, ?, ?, NOW())";
            $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', 'pearweb'));
            return true;
        }

        list($firstname, $lastname) = explode(' ', $name, 2);
        $data = array(
            'handle'     => $handle,
            'firstname'  => $firstname,
            'lastname'   => $lastname,
            'email'      => $this->email,
            'purpose'    => 'bug tracker',
            'password'   => $password,
            'password2'  => $password,
            'purpose'    => 'Open/Comment on bugs',
            'moreinfo'   => 'Automatic Account Request',
            'homepage'   => '',
        );

        $useradd = user::add($data, true);

        if (is_array($useradd)) {
            return $useradd;
        }

        $temphandle = $this->dbh->getOne('
            SELECT handle from bug_account_request WHERE salt=?', array($this->salt));
        // update all relevant records to the new handle
        $this->dbh->query('UPDATE users set handle=? WHERE handle=?', array($handle, $temphandle));
        $this->dbh->query('UPDATE bugdb set handle=? WHERE handle=?', array($handle, $temphandle));
        $this->dbh->query('UPDATE bugdb set reporter_name=? WHERE handle=?', array($name, $temphandle));
        $this->dbh->query('UPDATE bugdb_comments set reporter_name=? WHERE handle=?', array($name, $handle));
        $this->dbh->query('UPDATE bugdb_comments set handle=? WHERE handle=?', array($handle, $temphandle));
        $this->handle = $handle;
        // activate the handle and grant karma
        // implicitly without human intervention
        // copied from the user class and Damblan_Karma

        $user =& new PEAR_User($this->dbh, $this->handle);
        if (@$user->registered) {
            return false;
        }
        @$arr = unserialize($user->userinfo);
        note::removeAll("uid", $this->handle);
        $user->set('registered', 1);
        $user->set('password', $password);
        $user->set('name', $name);
        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }
        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', 'pearweb');
        $user->store();

        $id = $this->dbh->nextId("karma");

        $query = "INSERT INTO karma VALUES (?, ?, ?, ?, NOW())";
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', 'pearweb'));

        $id = $this->dbh->nextId("karma");
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.voter', 'pearweb'));

        if (!DB::isError($sth)) {
            note::add("uid", $this->handle, "Account opened", 'pearweb');
            $msg = "Your PEAR bug tracker account has been opened.\n"
                . "Bugs you have opened will now be displayed, and you can\n"
                . "add new comments to existing bugs";
            $xhdr = "From: pear-webmaster@lists.php.net";
            if (!DEVBOX) {
                mail($user->email, "Your PEAR Bug Tracker Account Request", $msg, $xhdr, "-f bounce-no-user@php.net");
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
        $old = gmdate('Y-m-d H:i:s', time() - 90000);
        $findquery = '
            select handle from bug_account_request
            where created_on < ?';
        $all = $this->dbh->getAll($findquery, array($old));
        // purge reserved usernames as well as their account requests
        if (is_array($all)) {
            foreach ($all as $data) {
                $this->dbh->query('
                    DELETE FROM users WHERE handle=?
                ', array($data[0]));
                $this->dbh->query('
                    DELETE FROM bugdb WHERE handle=?
                ', array($data[0]));
                $this->dbh->query('
                    DELETE FROM bugdb_comments WHERE handle=?
                ', array($data[0]));
            }
        }
        $query = '
            delete from bug_account_request
            where created_on < ?';
        // purge out-of-date account requests
        return $this->dbh->query($query, array($old));
    }
}
?>