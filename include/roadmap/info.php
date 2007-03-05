<?php
class Roadmap_Info
{
    function nextRelease($package)
    {
        $ret = $GLOBALS['dbh']->getAll('
            SELECT roadmap_version,releasedate
            FROM bugdb_roadmap WHERE
            package=? AND
            releasedate <> "1976-09-02 17:15:30"
            ORDER BY releasedate ASC
        ',array($package));
        if (!$ret) {
            return false;
        }
        $releases = array_keys(package::info($package, 'releases'));
        foreach ($ret as $roadmap) {
            if (in_array($roadmap[0], $releases)) continue;
            return $roadmap;
        }
    }

    function percentDone($package, $next = false)
    {
        if (!$next) {
            list($next,) = Roadmap_Info::nextRelease($package);
        }
        $count = $GLOBALS['dbh']->getAssoc('
            SELECT bugdb.status,COUNT(bugdb.id) FROM bugdb_roadmap_link,
                bugdb, bugdb_roadmap
            WHERE
                bugdb_roadmap.roadmap_version=? AND
                bugdb_roadmap.package=? AND
                bugdb_roadmap_link.roadmap_id=bugdb_roadmap.id AND
                bugdb.id=bugdb_roadmap_link.id
            GROUP BY bugdb.status
        ', false, array($next, $package));
        $total = 0;
        $closed = 0;
        foreach ($count as $status => $amount) {
            if ($status == 'Closed') {
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