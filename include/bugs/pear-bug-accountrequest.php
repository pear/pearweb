<?php
class PEAR_Bug_Accountrequest
{
    var $dbh;
    var $id;
    var $created_on;
    var $handle;
    var $salt;
    var $email;

    function __construct($handle = false)
    {
        $this->dbh = &$GLOBALS['dbh'];
        if ($handle) {
            $this->handle = $handle;
        } else {
            $this->handle = isset($GLOBALS['auth_user']) ? $GLOBALS['auth_user']->handle : false;
        }
        $this->cleanOldRequests();
    }

    function pending()
    {
        if (!$this->handle) {
            return false;
        }

        $sql = 'SELECT handle FROM bug_account_request WHERE handle = ?';
        $request = $this->dbh->getOne($sql, array($this->handle));

        if ($request) {
            return true;
        }
        return false;
    }

    function sendEmail()
    {
        if (!$this->handle) {
            throw new Exception('Internal fault: user was not set when sending email,
                                please report to ' . PEAR_DEV_EMAIL);
        }

        $sql  = 'SELECT salt FROM bug_account_request WHERE handle = ?';
        $salt = $this->dbh->getOne($sql, array($this->handle));
        if (!$salt) {
            throw new Exception('No such handle ' .
            $this->handle . ' found, cannot send confirmation email');
        }

        $sql   = 'SELECT email FROM bug_account_request WHERE salt = ?';
        $email = $this->dbh->getOne($sql, array($salt));
        if (!$email) {
            throw new Exception('No such salt found, cannot send confirmation email');
        }
        $mailData = array(
            'salt' => $salt,
        );
        require_once 'Damblan/Mailer.php';
        $mailer = Damblan_Mailer::create('pearweb_account_request_bug', $mailData);
        $additionalHeaders['To'] = $email;
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        if (!DEVBOX) {
            $e = $mailer->send($additionalHeaders);
        }
        PEAR::popErrorHandling();
        if (!DEVBOX && PEAR::isError($e)) {
            throw new Exception('Cannot send confirmation email: ' . $e->getMessage());
        }
        return true;
    }

    function _makeSalt($handle)
    {
        list($usec, $sec) = explode(' ', microtime());
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
            WHERE salt = ?', array($salt), DB_FETCHMODE_ASSOC);

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
    function addRequest($email)
    {
        $salt = $this->_makeSalt($email);
        $handle = '#' . substr($salt, 0, 19);
        $created_on = gmdate('Y-m-d H:i:s');

        $test = $this->dbh->getOne('SELECT email from users where email = ?', array($email));
        if ($test === $email) {
            return PEAR::raiseError('Email is already in use for an existing account');
        }
        $test = $this->dbh->getOne('SELECT email from bug_account_request where email = ?',
            array($email));
        if ($test === $email) {
            // re-use existing request
            $salt = $this->dbh->getOne('SELECT salt FROM bug_account_request WHERE email = ?',
                array($email));
            $this->find($salt);
            return $salt;
        }
        $query = 'INSERT INTO bug_account_request (created_on, handle, email, salt)
        VALUES (?, ?, ?, ?)';

        $res = $this->dbh->query($query, array($created_on, $handle, $email, $salt));
        if (DB::isError($res)) {
            return $res;
        }

        $sql = 'SELECT handle FROM bug_account_request WHERE salt = ?';
        $this->handle = $this->dbh->getOne($sql, array($salt));
        return $salt;
    }

    function deleteRequest()
    {
        $query = 'DELETE FROM bug_account_request WHERE salt = ?';
        return $this->dbh->query($query, array($this->salt));
    }

    function validateRequest($handle, $password, $password2, $name)
    {
        $errors = array();
        if (empty($handle) || !preg_match('/^[0-9a-z_]{2,20}\z/', $handle)) {
            $errors[] = 'Username is invalid.';
            $display_form = true;
        }

        if ($password == md5('') || empty($password)) {
            $errors[] = 'Password must not be empty';
        }
        if ($password !== $password2) {
            $errors[] = 'Passwords do not match';
        }

        include_once 'pear-database-user.php';
        if (user::exists($handle)) {
            $errors[] = 'User name "' . $handle .
                '" already exists, please choose another user name';
        }

        $name_parts = explode(' ', $name, 2);
        if (count($name_parts) == 2) {
            $firstname = $name_parts[0];
            $lastname = $name_parts[1];
        } else {
            $firstname = $name_parts[0];
            $lastname = null;
        }

        // First- and lastname must be longer than 1 character
        if (strlen($firstname) == 1) {
            $errors[] = 'Your firstname appears to be too short.';
        }
        if (strlen($lastname) == 1) {
            $errors[] = 'Your lastname appears to be too short.';
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
        $sql = 'SELECT handle FROM users WHERE handle = ?';
        if ($handle == $this->dbh->getOne($sql, array($handle))) {
            $id = $this->dbh->nextId("karma");

            $query = 'INSERT INTO karma VALUES (?, ?, ?, ?, NOW())';
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

        include_once 'pear-database-user.php';
        $useradd = user::add($data, true, true);
        if ($useradd !== true) {
            return $useradd;
        }

        $sql = 'SELECT handle from bug_account_request WHERE salt = ?';
        $temphandle = $this->dbh->getOne($sql, array($this->salt));
        // update all relevant records to the new handle
        $this->dbh->query('UPDATE bugdb_comments set reporter_name = ? WHERE handle = ?', array($name, $temphandle));
        $this->dbh->query('UPDATE bugdb set reporter_name = ? WHERE handle = ?', array($name, $temphandle));
        $this->dbh->query('UPDATE users set handle = ? WHERE handle = ?', array($handle, $temphandle));
        $this->dbh->query('UPDATE bugdb set registered = 1, handle = ? WHERE handle = ?', array($handle, $temphandle));
        $this->dbh->query('UPDATE bugdb_comments set handle = ? WHERE handle = ?', array($handle, $temphandle));
        $this->dbh->query('UPDATE bugdb_patchtracker set developer = ? WHERE developer = ?', array($handle, $temphandle));
        $this->handle = $handle;
        // activate the handle and grant karma
        // implicitly without human intervention
        // copied from the user class and Damblan_Karma

        include_once 'pear-database-user.php';
        $user = user::info($handle, null, 0);
        if (!isset($user['registered'])) {
            return false;
        }
        @$arr = unserialize($user['userinfo']);

        include_once 'pear-database-note.php';
        note::removeAll($handle);

        $data = array();
        $data['registered'] = 1;
        $data['password']   = $password;
        $data['name']       = $name;
        if (is_array($arr)) {
            $data['userinfo'] = $arr[1];
        }
        $data['create']   = gmdate('Y-m-d');
        $data['createBy'] = SITE . 'web';
        $data['handle']   = $handle;

        user::update($data, true);

        $query = 'INSERT INTO karma VALUES (?, ?, ?, ?, NOW())';

        $id = $this->dbh->nextId('karma');
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', SITE . 'web'));
        $id = $this->dbh->nextId('karma');
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.voter', SITE . 'web'));

        if (!DB::isError($sth)) {
            require_once 'bugs/pear-bugs-utils.php';
            $pbu = new PEAR_Bugs_Utils;
            note::add($this->handle, 'Account opened', SITE . 'web');
            $bugs = $this->dbh->getAll('SELECT * FROM bugdb WHERE handle = ?',
                array($this->handle), DB_FETCHMODE_ASSOC);
            foreach ($bugs as $bug) {
                $this->sendBugEmail($bug);
            }
            $patches = $this->dbh->getAll('SELECT bugdb.package_name, bugdb_patchtracker.*
                FROM bugdb_patchtracker, bugdb
                WHERE bugdb_patchtracker.developer = ?
                    AND bugdb.id = bugdb_patchtracker.bugdb_id', array($this->handle),
                    DB_FETCHMODE_ASSOC);
            foreach ($patches as $patch) {
                $pbu->sendPatchEmail($patch);
            }
            $bugs = $this->dbh->getAll('SELECT bugdb_comments.email,bugdb_comments.comment,
                    bugdb_comments.reporter_name, bugdb.id,
                    bugdb.bug_type,bugdb.package_name,bugdb.sdesc,
                    bugdb.ldesc,bugdb.php_version, bugdb.php_os,bugdb.status,
                    bugdb.assign,bugdb.package_version
                 FROM bugdb_comments, bugdb
                 WHERE bugdb.id = bugdb_comments.bug AND
                 bugdb_comments.handle = ?',
                array($this->handle), DB_FETCHMODE_ASSOC);
            foreach ($bugs as $bug) {
                $this->sendBugCommentEmail($bug);
            }
            $msg = "Your PEAR bug tracker account has been opened.\n"
                . "Bugs you have opened will now be displayed, and you can\n"
                . "add new comments to existing bugs";
            $xhdr = "From: " . PEAR_WEBMASTER_EMAIL;
            if (!DEVBOX) {
                mail($user['email'], "Your PEAR Bug Tracker Account Request", $msg, $xhdr, "-f " . PEAR_BOUNCE_EMAIL);
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
        $query = 'SELECT handle FROM bug_account_request WHERE created_on < ?';
        $all = $this->dbh->getAll($query, array($old));
        require_once 'bugs/patchtracker.php';
        $p = new Bugs_Patchtracker;
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
                $sql = 'SELECT * FROM bugdb_patchtracker WHERE developer = ?';
                $patches = $this->dbh->getAll($sql, array($data[0]), DB_FETCHMODE_ASSOC);
                foreach ($patches as $patch) {
                    $p->detach($patch['bugdb_id'], $patch['patch'], $patch['revision']);
                }
            }
        }
        $query = 'DELETE FROM bug_account_request WHERE created_on < ?';
        // purge out-of-date account requests
        return $this->dbh->query($query, array($old));
    }

    function sendBugCommentEmail($bug)
    {
        include_once 'pear-bugs-utils.php';
        $pbu = new PEAR_Bugs_Utils;
        $ncomment = trim($bug['comment']);
        $tla = array(
            'Open'        => 'Opn',
            'Bogus'       => 'Bgs',
            'Feedback'    => 'Fbk',
            'No Feedback' => 'NoF',
            'Wont fix'    => 'WFx',
            'Duplicate'   => 'Dup',
            'Critical'    => 'Ctl',
            'Assigned'    => 'Asn',
            'Analyzed'    => 'Ana',
            'Verified'    => 'Ver',
            'Suspended'   => 'Sus',
            'Closed'      => 'Csd',
            'Spam'        => 'Spm',
        );
        $types = array(
            'Bug'                     => 'Bug',
            'Feature/Change Request'  => 'Req',
            'Documentation Problem'   => 'Doc',
        );

        $headers = $text = array();

        /* Default addresses */
        list($mailto,$mailfrom, $Bcc) = $pbu->getPackageMail($bug['package_name'], $bug['id']);

        $headers[] = array(" ID", $bug['id']);

        $headers[] = array(" Comment by", $this->handle);
        $from = "\"$this->handle\" <$this->email>";

        $prefix = " ";
        if ($f = $pbu->spamProtect($this->email, 'text')) {
            $headers[] = array($prefix.'Reported By', $f);
        }

        $fields = array(
            'sdesc'            => 'Summary',
            'status'           => 'Status',
            'bug_type'         => 'Type',
            'package_name'     => 'Package',
            'php_os'           => 'Operating System',
            'package_version'  => 'Package Version',
            'php_version'      => 'PHP Version',
            'assign'           => 'Assigned To'
        );

        foreach ($fields as $name => $desc) {
            $prefix = " ";
            /* only fields that are set get added. */
            if ($f = $bug[$name]) {
                $headers[] = array($prefix . $desc, $f);
            }
        }

        # make header output aligned
        $actlength = $maxlength = 0;
        foreach ($headers as $v) {
            $actlength = strlen($v[0]) + 1;
            $maxlength = (($maxlength < $actlength) ? $actlength : $maxlength);
        }

        # align header content with headers (if a header contains
        # more than one line, wrap it intelligently)
        $header_text = "";
        $spaces = str_repeat(" ", $maxlength + 1);
        foreach ($headers as $v) {
            $hcontent = wordwrap($v[1], 72-$maxlength, "\n$spaces"); # wrap and indent
            $hcontent = rtrim($hcontent); # wordwrap may add spacer to last line
            $header_text .= str_pad($v[0] . ":", $maxlength) . " " . $hcontent . "\n";
        }

        if ($ncomment) {
            $text[] = " New Comment:\n\n".$ncomment;
        }

        $text[] = $pbu->getOldComments($bug['id'], empty($ncomment));

        /* format mail so it looks nice, use 72 to make piners happy */
        $wrapped_text = wordwrap(join("\n",$text), 72);

        /* developer text with headers, previous messages, and edit link */
        $dev_text = 'Edit report at ' .
                    "http://pear.php.net/bugs/bug.php?id=$bug[id]&edit=1\n\n" .
                    $header_text .
                    $wrapped_text .
                    "\n-- \nEdit this bug report at " .
                    "http://pear.php.net/bugs/bug.php?id=$bug[id]&edit=1\n";

        $user_text = $dev_text;

        $subj = $types[$bug['bug_type']];

        $new_status = $bug['status'];

        $subj .= " #{$bug['id']} [Com]: ";

        # the user gets sent mail with an envelope sender that ignores bounces
        if (DEVBOX === false) {
            @mail($bug['email'],
                  "[PEAR-BUG] " . $subj . $bug['sdesc'],
                  $user_text,
                  "From: PEAR Bug Database <$mailfrom>\n".
                  "Bcc: $Bcc\n" .
                  "X-PHP-Bug: $bug[id]\n".
                  "In-Reply-To: <bug-$bug[id]@pear.php.net>",
                  "-f ". PEAR_BOUNCE_EMAIL);
            # but we go ahead and let the default sender get used for the list

            @mail($mailto,
                  "[PEAR-BUG] " . $subj . $bug['sdesc'],
                  $dev_text,
                  "From: $from\n".
                  "X-PHP-Bug: $bug[id]\n".
                  "X-PHP-Type: "       . $bug['bug_type']    . "\n" .
                  "X-PHP-PackageVersion: "    . $bug['package_version'] . "\n" .
                  "X-PHP-Version: "    . $bug['php_version'] . "\n" .
                  "X-PHP-Category: "   . $bug['package_name']    . "\n" .
                  "X-PHP-OS: "         . $bug['php_os']      . "\n" .
                  "X-PHP-Status: "     . $new_status . "\n" .
                  "In-Reply-To: <bug-$bug[id]@pear.php.net>",
                  "-f " . PEAR_BOUNCE_EMAIL);
        }
    }

    function sendBugEmail($buginfo)
    {
        $report  = '';
        $report .= 'From:             ' . $this->handle . "\n";
        $report .= 'Operating system: ' . $buginfo['php_os'] . "\n";
        $report .= 'Package version:  ' . $buginfo['package_version'] . "\n";
        $report .= 'PHP version:      ' . $buginfo['php_version'] . "\n";
        $report .= 'Package:          ' . $buginfo['package_name'] . "\n";
        $report .= 'Bug Type:         ' . $buginfo['bug_type'] . "\n";
        $report .= 'Bug description:  ';

        $fdesc = $buginfo['ldesc'];
        $sdesc = $buginfo['sdesc'];

        $ascii_report  = "$report$sdesc\n\n" . wordwrap($fdesc);
        $ascii_report .= "\n-- \nEdit bug report at ";
        $ascii_report .= "http://" . PEAR_CHANNELNAME . "/bugs/bug.php?id=$buginfo[id]&edit=";

        include_once 'bugs/pear-bugs-utils.php';
        $pbu = new PEAR_Bugs_Utils;
        list($mailto, $mailfrom) = $pbu->getPackageMail($buginfo['package_name']);

        $email = $this->email;
        $protected_email  = '"' . $pbu->spamProtect($email, 'text') . '"';
        $protected_email .= '<' . $mailfrom . '>';

        if ((!isset($email) || !isset($mailfrom)) && (isset($buginfo['reporter_name']))) {
            $protected_email = '"' . $buginfo['reporter_name'] . '" <' . $pbu->spamProtect($buginfo['email']) . '>';
        }

        $extra_headers  = 'From: '           . $protected_email . "\n";
        $extra_headers .= 'X-PHP-BugTracker: PEARbug' . "\n";
        $extra_headers .= 'X-PHP-Bug: '      . $buginfo['id'] . "\n";
        $extra_headers .= 'X-PHP-Type: '     . $buginfo['bug_type'] . "\n";
        $extra_headers .= 'X-PHP-PackageVersion: '  . $buginfo['package_version'] . "\n";
        $extra_headers .= 'X-PHP-Version: '  . $buginfo['php_version'] . "\n";
        $extra_headers .= 'X-PHP-Category: ' . $buginfo['package_name'] . "\n";
        $extra_headers .= 'X-PHP-OS: '       . $buginfo['php_os'] . "\n";
        $extra_headers .= 'X-PHP-Status: Open' . "\n";
        $extra_headers .= 'Message-ID: <bug-' . $buginfo['id'] . '@' . PEAR_CHANNELNAME . '>';

        $types = array(
            'Bug'                     => 'Bug',
            'Feature/Change Request'  => 'Req',
            'Documentation Problem'   => 'Doc',
        );
        $type = @$types[$buginfo['bug_type']];

        if (!DEVBOX) {
            // mail to package developers
            @mail($mailto, "[PEAR-BUG] $buginfo[bug_type] #$buginfo[id] [NEW]: $sdesc",
                  $ascii_report . "1\n-- \n", $extra_headers,
                  '-f ' . PEAR_BOUNCE_EMAIL);
            // mail to reporter
            @mail($email, "[PEAR-BUG] $buginfo[bug_type] #$buginfo[id]: $sdesc",
                  $ascii_report . "2\n",
                  "From: " . PEAR_CHANNELNAME . " Bug Database <$mailfrom>\n" .
                  "X-PHP-Bug: $buginfo[id]\n" .
                  "Message-ID: <bug-$buginfo[id]@" . PEAR_CHANNELNAME . ">",
                  '-f ' . PEAR_BOUNCE_EMAIL);
        }
    }
}
