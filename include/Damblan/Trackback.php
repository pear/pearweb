<?php

require_once 'Services/Trackback.php';

class Damblan_Trackback extends Services_Trackback {

    /**
     * The time the trackback has been discovered.
     *  
     * @var int
     * @since
     */
    private $_timestamp;

    /**
     * Boolean flag, if the trackback has been improved by a PEAR developer, yet.
     *  
     * @var bool
     * @since  
     */
    private $_approved;

    /**
     * __construct 
     * Overwriten constructor to get the timestamp while creating a trackback.
     * The timestamp is used as the primary key for the trackback table, in 
     * combination with the trackback ID (which is the package name).
     *  
     * @since  
     * @access public
     * @param array $data       The trackback data, an associative array of string values,
     *                          that may/has to contain the following keys, depending on the
     *                          purpose of the created trackback instance:
     *                          - 'id': The ID of the page the trackback was received for.
     *                          - 'trackback_url': The URL where trackbacks for this site should go.
     *                          - 'title': The title of the blog entry, which produced this trackback.
     *                          - 'url': The URL of the entry, which produced this trackback.
     *                          - 'excerpt': An abstract of the blog entry, which produced this trackback.
     *                          - 'blog_name': The name of the weblog, which produced this trackback.
     * @param int   $timestamp  A unix timestamp representing the time the trackback
     *                          was received.
     * @param bool  $approved   Wether the trackback is approved, yet. Default is false.
     * @return void
     */
    public function __construct($data, $timestamp, $approved = true)
    {
        parent::__construct($data);
        $this->_timestamp = (int)$timestamp;
        $this->_approved = $approved;
    }

    /**
     * __get 
     * Overwritten __get() to receive timestamp and improved state correctly.
     *  
     * @since  
     * @access public
     * @param   mixed $key The name of the property to receive.
     * @return  mixed $val The value of the property.
     */
    public function __get($key)
    {
        $testKey = '_'.$key;
        if (isset($this->$testKey)) {
            return $this->$testKey;
        } else {
            return $this->_data[$key];
       } 
    }

    /**
     * Save a trackback into the database.
     * This method saves a trackback into the database. BEWARE: It
     * does not update exististing database entries! The trackback is
     * just inserted.
     *  
     * @since  
     * @access public
     * @param object DB $dbh Database connection object (PEAR::DB).
     * @return void
     */
    public function save ( $dbh )
    {
        $necessaryData = array('id', 'title', 'url', 'excerpt', 'blog_name');
        $this->_checkData($necessaryData);
        $data = $this->_getDecodedData($necessaryData);
        $this->_timestamp = time();
        $approved = ($this->_approved) ? 'true' : 'false';
        $sql = "INSERT INTO trackbacks (id, title, url, excerpt, blog_name, approved, timestamp) VALUES (
                    ".$dbh->quoteSmart($data['id']).",
                    ".$dbh->quoteSmart($data['title']).",
                    ".$dbh->quoteSmart($data['url']).",
                    ".$dbh->quoteSmart($data['excerpt']).",
                    ".$dbh->quoteSmart($data['blog_name']).",
                    ".$dbh->quoteSmart($approved).",
                    ".$dbh->quoteSmart($this->_timestamp)."
                )";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            throw new Exception('Unable to save trackback: '.$res->getMessage());
        }
        return true;
    }

    /**
     * Load a trackback from the database.
     * Load a trackback from the database. At least the ID and timestamp
     * of the trackback have to be set.
     *  
     * @since  
     * @access public
     * @param object DB $dbh The database connection.
     * @param int $timestamp The timestamp of the trackback to load.
     * @return void
     */
    public function load ( $dbh )
    {
        $necessaryData = array('id');
        if (!isset($this->_timestamp) || !is_int($this->_timestamp)) {
            throw new Exception('Necessary attribute timestamp missing.');
        }
        $this->_checkData($necessaryData);
        $data = $this->_getDecodedData($necessaryData);
        $sql = "SELECT id, title, excerpt, blog_name, url, timestamp, approved FROM trackbacks WHERE
                    id = ".$dbh->quoteSmart($data['id'])."
                    AND timestamp = ".$dbh->quoteSmart($this->_timestamp);
        $res = $dbh->getRow($sql, null, DB_FETCHMODE_ASSOC);
        if (DB::isError($res) || !is_array($res) || !count($res)) {
            throw new Exception('Unable to load trackback: '.$res->getMessage());
        }
        foreach ($res as $key => $val) {
            if (($key != 'timestamp') && ($key != 'approved')) {
                $this->_data[$key] = $val;
            } else {
                switch ($key) {
                case 'approved':
                    $this->_approved = ($val == 'true');
                    break;
                default:
                    $key = '_'.$key;
                    $this->$key = $val;
                }
            }
        }
        return true;
    }

    /**
     * Approves a trackback.
     * Sets the approved flag for the trackback to true and saves that to
     * the database.
     *  
     * @since  
     * @access public
     * @param object DB $dbh The database connection.
     * @param int $timestamp The timestamp of the trackback to load.
     * @return void
     */
    public function approve($dbh)
    {
        $necessaryData = array('id');
        $data = $this->_getDecodedData($necessaryData);
        $sql = "UPDATE trackbacks SET approved = ".$dbh->quoteSmart('true')." WHERE
                    id = ".$dbh->quoteSmart($data['id'])."
                    AND timestamp = ".$dbh->quoteSmart($this->_timestamp);
        $res = $dbh->query($sql) ;
        if (DB::isError($res)) {
            throw new Exception('Could not approve trackback.');
        }
        $this->_approved = true;
        return true;
    }

    /**
     * delete 
     * Delete a trackback
     *  
     * @since  
     * @access public
     * @param  
     * @return void
     */
    public function delete($dbh)
    {
        $necessaryData = array('id');
        $data = $this->_getDecodedData($necessaryData);
        $this->load($dbh, $this->_timestamp);
        $sql = "DELETE FROM trackbacks WHERE
                    id = ".$dbh->quoteSmart($data['id'])."
                    AND timestamp = ".$dbh->quoteSmart($this->_timestamp);
        $res = $dbh->query($sql) ;
        if (DB::isError($res)) {
            throw new Exception('Could not delete trackback.');
        }
        return true;
    }

    /**
     * listTrackbacks 
     * Get a list of trackbacks for an ID. The list can be influenced through
     * several parameters of this method.
     *  
     * @since  
     * @access public
     * @static
     * @param object(DB)    $dbh            The database connection object (PEAR::DB).
     * @param int           $id             The ID to fetch trackbacks for.
     * @param bool          $approvedOnly   Wether to fetch only approved trackbacks (default is true).
     * @param string        $orderBy        Order criteria for the list (default is 'timestamp DESC').
     * @param int           $limit          The limit of trackbacks to list (default is 10).
     * @return array                        Array of PEAR_Trackback objects.
     * @throws Exception If no results are received.
     */
    public static function listTrackbacks($dbh, $id, $approvedOnly = true, $orderBy = 'timestamp DESC', $limit = 10)
    {
        $sql = 'SELECT id, title, excerpt, blog_name, url, timestamp, approved FROM trackbacks WHERE
                id LIKE '.$dbh->quoteSmart($id);
        if ($approvedOnly) {
            $sql .= ' AND approved = '.$dbh->quoteSmart('true');
        }
        $sql .= ' ORDER BY '.$orderBy;
        $sql .= ' LIMIT '.$limit;
        $res = $dbh->getAll($sql, null, DB_FETCHMODE_ASSOC);
        if (DB::isError($res)) {
            throw new Exception('Could not receive trackback list: '.$res->getMessage());
        }
        $ret = array();
        foreach ($res as $row) {
            $ts = $row['timestamp'];
            unset($row['timestamp']);
            $app = ($row['approved'] == 'true');
            unset($row['approved']);
            $ret[] = new Damblan_Trackback($row, $ts, $app);
        }
        return $ret;
    }

    /**
     * Print list of trackbacks
     * Prints a list of generated by Damblan_Trackback::listTrackbacks(). If the
     * $admin flag is set to true, administrative links are printed additionally.
     *  
     * @since  
     * @static
     * @access public
     * @param array $trackbackList An array of Damblan_Trackback objects.
     * @param bool  $admin Wether to output administrative links or not.
     * @return void
     */
    public static function printList ($trackbackList, $admin = false)
    {
        print '<table border="0" cellspacing="0" cellpadding="2" style="width: 100%">';
        foreach ($trackbackList as $trackback) {
            print '<tr>';
            print '<th class="others">';
            print 'Weblog:';
            print '</th>';
            print '<td class="ulcell" style="width:100%">';
            print $trackback->blog_name;
            print '</td>';
            print '</tr>';
            
            if ($admin) {
                print '<tr>';
                print '<th class="others">';
                print 'Approved:';
                print '</th>';
                print '<td class="ulcell">';
                print ($trackback->approved) ? '<b>yes</b>' : '<b>no</b>';
                print '</td>';
                print '</tr>';
            }
            print '<tr>';
            print '<th class="others">';
            print 'Title:';
            print '</th>';
            print '<td class="ulcell">';
            print '<a href="'.$trackback->url.'">'.$trackback->title.'</a>';
            print '</td>';
            print '</tr>';
            
            print '<tr>';
            print '<th class="others">';
            print 'Date:';
            print '</th>';
            print '<td class="ulcell">';
            print  date('D M d H:i:s Y', $trackback->timestamp - date('Z', $trackback->timestamp)) . ' UTC';
            print '</td>';
            print '</tr>';
            
            print '<tr>';
            print '<th class="others">';
            print '</th>';
            print '<td class="ulcell">';
            print  $trackback->excerpt;
            print '</td>';
            print '</tr>';

            if ($admin) {
                print '<tr>';
                print '<th class="others">';
                print '</th>';
                print '<td class="ulcell">';
                if (!$trackback->approved) {
                    print '[<a href="/trackback/trackback-admin.php?action=approve&id='.$trackback->id.'&timestamp='.$trackback->timestamp.'">Approve</a>] ';
                }
                print '[<a href="/trackback/trackback-admin.php?action=delete&id='.$trackback->id.'&timestamp='.$trackback->timestamp.'">Delete</a>]';
                print '</td>';
                print '</tr>';
            }

            print '<tr><td colspan="2" style="height: 20px;">&nbsp;</td></tr>';
            
        }
        print '</table>';
    }
}
?>
