<?php
/**
 * Get common settings.
 */
require_once dirname(__FILE__) . '/../include/pear-config.php';
// Get the database class.
require_once 'DB.php';
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (PEAR::isError($dbh)) {
    die ("Failed to connect: $dsn\n");
}

$dbh->setFetchMode(DB_FETCHMODE_ASSOC);
$toProcess = $dbh->getAll('
            SELECT *
            FROM elections e LEFT JOIN election_results r on e.id = r.election_id
            WHERE r.election_id IS NULL AND e.voteend < NOW()
        ');
if (count($toProcess)) {
    foreach ($toProcess as $election) {
        $totalabstain = $dbh->getOne('
            SELECT COUNT(*) FROM election_votes_abstain WHERE election_id=?
        ', array($election['id']));
        if ($election['maximum_choices'] == 1) {
            $totalvotes = $dbh->getOne('
                SELECT COUNT(*) FROM election_votes_single WHERE election_id=?
            ', array($election['id'])) + $totalabstain;
            $results = $dbh->getAll('
                SELECT COUNT(*) as total, vote
                FROM election_votes_single
                WHERE
                    election_id=?
                GROUP BY vote
            ', array($election['id']), DB_FETCHMODE_ASSOC);
        } else {
            $totalvotes = $dbh->getOne('
                SELECT COUNT(*) FROM election_votes_multiple WHERE election_id=?
            ', array($election['id'])) + $totalabstain;
            $results = $dbh->getAll('
                SELECT COUNT(*) as total, vote
                FROM election_votes_multiple
                WHERE
                    election_id=?
                GROUP BY vote
            ', array($election['id']), DB_FETCHMODE_ASSOC);
        }
        foreach ($results as $vote) {
            $dbh->query('
                INSERT INTO election_results
                (election_id, choice, votepercent, votetotal)
                VALUES(?,?,?,?)
            ', array($election['id'], $vote['vote'], $vote['total'] / $totalvotes,
                $vote['total']));
        }
    }
}