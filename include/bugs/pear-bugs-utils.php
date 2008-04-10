<?php
class PEAR_Bugs_Utils
{
    /**
     * Produces an array of email addresses the report should go to
     *
     * @param string $package_name  the package's name
     *
     * @return array  an array of email addresses
     */
    public function getPackageMail($package_name, $bug_id = false)
    {
        global $dbh;
        switch ($package_name) {
            case 'Bug System':
            case 'PEPr':
            case 'Web Site':
                $arr = $this->getPackageMail('pearweb');
                $arr[0] .= ',' . PEAR_WEBMASTER_EMAIL;
                return array($arr[0], PEAR_WEBMASTER_EMAIL);
            case 'Documentation':
                return array(PEAR_DOC_EMAIL, PEAR_DOC_EMAIL);
        }

        include_once 'pear-database-package.php';
        $maintainers = package::info($package_name, 'authors');

        $to = array();
        foreach ($maintainers as $data) {
            if (!$data['active']) {
                continue;
            }
            $to[] = $data['email'];
        }

        /* subscription */
        if ($bug_id) {
            $bug_id = (int)$bug_id;

            $assigned = $dbh->getOne('SELECT assign FROM bugdb WHERE id = ' . $bug_id);
            if ($assigned) {
                $assigned = $dbh->getOne('SELECT email FROM users WHERE handle = ?', array($assigned));
                if ($assigned && !in_array($assigned, $to)) {
                    // assigned is not a maintainer
                    $to[] = $assigned;
                }
            }

            // Add the bug mailing list if any
            if (PEARWEB_BUGS_ML_EMAIL != '') {
                $to[] = PEARWEB_BUGS_ML_EMAIL;
            }

            $bcc = $dbh->getCol('SELECT email FROM bugdb_subscribe WHERE bug_id = ' . $bug_id);
            $bcc = array_diff($bcc, $to);
            $bcc = array_unique($bcc);
            return array(implode(', ', $to), PEAR_QA_EMAIL, implode(', ', $bcc));
        }

        return array(implode(', ', $to), PEAR_QA_EMAIL);
    }

    /**
     * Obfuscates email addresses to hinder spammer's spiders
     *
     * Turns "@" into character entities that get interpreted as "at" and
     * turns "." into character entities that get interpreted as "dot".
     *
     * @param string $txt     the email address to be obfuscated
     * @param string $format  how the output will be displayed ('html', 'text')
     *
     * @return string  the altered email address
     */
    static public function spamProtect($txt, $format = 'html')
    {
        if ($format == 'html') {
            $translate = array(
                '@' => ' &#x61;&#116; ',
                '.' => ' &#x64;&#111;&#x74; ',
            );
        } else {
            $translate = array(
                '@' => ' at ',
                '.' => ' dot ',
            );
        }
        return strtr($txt, $translate);
    }

    /**
     * Produces a string containing the bug's prior comments
     *
     * @param int $bug_id  the bug's id number
     * @param int $all     should all existing comments be returned?
     *
     * @return string  the comments
     */
    function getOldComments($bug_id, $all = 0)
    {
        global $dbh;
        $divider = str_repeat('-', 72);
        $max_message_length = 10 * 1024;
        $max_comments = 5;
        $output = '';
        $count = 0;

        $res =& $dbh->query("SELECT ts, email, comment, handle FROM bugdb_comments WHERE bug = $bug_id ORDER BY ts DESC");

        # skip the most recent unless the caller wanted all comments
        if (!$all) {
            $row =& $res->fetchRow(DB_FETCHMODE_ORDERED);
            if (!$row) {
                return '';
            }
        }

        include_once 'bugs/pear-bugs-utils.php';
        $pbu = new PEAR_Bugs_Utils;
        while (($row =& $res->fetchRow(DB_FETCHMODE_ORDERED)) &&
                strlen($output) < $max_message_length && $count++ < $max_comments) {
            $email = $row[3] ? $row[3] : $pbu->spamProtect($row[1], 'text');
            $output .= "[$row[0]] $email\n\n$row[2]\n\n$divider\n\n";
        }

        if (strlen($output) < $max_message_length && $count < $max_comments) {
            $res =& $dbh->query("SELECT ts1,email,ldesc,handle FROM bugdb WHERE id=$bug_id");
            if (!$res) {
                return $output;
            }
            $row =& $res->fetchRow(DB_FETCHMODE_ORDERED);
            if (!$row) {
                return $output;
            }
            $email = $row[3] ? $row[3] : $pbu->spamProtect($row[1], 'text');
            return ("\n\nPrevious Comments:\n$divider\n\n" . $output . "[$row[0]] $email\n\n$row[2]\n\n$divider\n\n");
        }

        return ("\n\nPrevious Comments:\n$divider\n\n" . $output . "The remainder of the comments for this report are too long. To view\nthe rest of the comments, please view the bug report online at\n    http://" . PEAR_CHANNELNAME . "/bugs/bug.php?id=$bug_id\n");
    }

    static function sendPatchEmail($patch)
    {
        require_once 'Damblan/Mailer.php';
        $name = urlencode($patch['patch']);
        $id   = $patch['bug_id'];
        $host = 'http://' . PEAR_CHANNELNAME;
        $mailData = array(
            'id'         => $id,
            'url'        => $host .
                            "/bugs/patch-display.php?bug=$id&patch=$name&revision=$patch[revision]&display=1",
            'date'       => date('Y-m-d H:i:s'),
            'name'       => $patch['patch'],
            'package'    => $patch['package_name'],
            'summary'    => $GLOBALS['dbh']->getOne('SELECT sdesc from bugdb WHERE id = ?', array($id)),
            'packageUrl' => $host . '/bugs/bug.php?id=' . $id,
        );

        $additionalHeaders['To'] = self::getMaintainers($patch['package_name']);
        $mailer = Damblan_Mailer::create('Patch_Added', $mailData);
        $res = true;
        if (!DEVBOX) {
            $res = $mailer->send($additionalHeaders);
        }
        return $res;
    }

    // {{{ public function getMaintainers
    /**
     * Get maintainers
     *
     * Get maintainers to inform of a trackback (the
     * lead maintainers of a package).
     *
     * @since
     * @access public
     * @param  boolean $activeOnly  To get only active leads
     *                 is set to false by default so there's
     *                 no bc problems.
     *
     * @return array(string) The list of maintainer emails.
     */
    function getMaintainers ($id, $leadOnly = false, $activeOnly = true)
    {
        include_once 'pear-database-maintainer.php';
        $maintainers = maintainer::get($id, $leadOnly, $activeOnly);
        $res = array();

        include_once 'pear-database-user.php';
        foreach ($maintainers as $maintainer => $data) {
            $tmpUser = user::info($maintainer, 'email');
            if (!is_array($tmpUser) || !isset($tmpUser['email'])) {
                continue;
            }
            $res[] = $tmpUser['email'];
        }
        return $res;
    }
    // }}}
}