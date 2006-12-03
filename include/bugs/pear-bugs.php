<?php
/**
 * Bug statistics
 * @package pearweb
 */
class PEAR_Bugs
{
    var $_dbh;
    function PEAR_Bugs()
    {
        $this->_dbh = $GLOBALS['dbh'];
    }

    function packageBugStats($packageid)
    {
        $info = $this->_dbh->getAll('
            SELECT
                COUNT(bugdb.id) as count,
                AVG(TO_DAYS(NOW()) - TO_DAYS(ts1)) as average,
                MAX(TO_DAYS(NOW()) - TO_DAYS(ts1)) as oldest
            FROM bugdb, packages
            WHERE
                name=? AND
                bugdb.package_name = packages.name AND
                status IN ("Open","Feedback","Assigned","Analyzed","Verified","Critical") AND
                bug_type IN ("Bug","Documentation Problem")
            ', array($packageid), DB_FETCHMODE_ASSOC);
        return $info[0];
    }

    function bugRank()
    {
        $info = $this->_dbh->getAll('
            SELECT
                name,
                AVG(TO_DAYS(NOW()) - TO_DAYS(ts1)) as average
            FROM bugdb, packages
            WHERE
                bugdb.package_name = packages.name AND
                status IN ("Open","Feedback","Assigned","Analyzed","Verified","Critical") AND
                bug_type IN ("Bug","Documentation Problem") AND
                package_type="pear"
            GROUP BY package_name
            ORDER BY average ASC
        ', array(), DB_FETCHMODE_ASSOC);
        return $info;
    }
}
?>