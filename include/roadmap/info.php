<?php
class Roadmap_Info
{
    function roadmapExists($package)
    {
        $sql = 'SELECT id FROM bugdb_roadmap WHERE package = ?';
        $ret = $GLOBALS['dbh']->getOne($sql, array($package));
        return (boolean)$ret;
    }

    function nextRelease($package)
    {
        $sql = '
            SELECT roadmap_version,releasedate
            FROM bugdb_roadmap WHERE
            package = ? AND
            releasedate <> "1976-09-02 17:15:30"
            ORDER BY releasedate ASC';

        $ret = $GLOBALS['dbh']->getAll($sql, array($package));
        if (!$ret) {
            return false;
        }

        include_once 'pear-database-package.php';
        $releases = array_keys(package::info($package, 'releases'));
        foreach ($ret as $roadmap) {
            if (in_array($roadmap[0], $releases)) {
                continue;
            }

            return $roadmap;
        }
    }

    function percentDone($package, $next = false)
    {
        if (!$next) {
            list($next,) = Roadmap_Info::nextRelease($package);
        }

        $sql = '
            SELECT bugdb.status, COUNT(bugdb.id)
            FROM bugdb_roadmap_link b_r_l, bugdb_roadmap b_r, bugdb
            WHERE
                b_r.roadmap_version = ? AND
                b_r.package = ? AND
                b_r_l.roadmap_id = b_r.id AND
                bugdb.id = b_r_l.id
            GROUP BY bugdb.status';

        $count = $GLOBALS['dbh']->getAssoc($sql, false, array($next, $package));
        $total = $closed = 0;
        $s = array('Closed', 'Duplicate', 'Bogus', "Won't Fix");
        foreach ($count as $status => $amount) {
            if (in_array($status, $s)) {
                $closed = $amount;
            }
            $total += $amount;
        }
        if (!$total) {
            return 0;
        }
        return round(100 * $closed / $total);
    }
}