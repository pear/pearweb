<?php
set_include_path(dirname(__FILE__) . '/include' . PATH_SEPARATOR . get_include_path());

// Get common settings.
require_once 'pear-prepend.php';

// Get the database class.
require_once 'DB.php';
$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (DB::isError($dbh)) {
    die ("Failed to connect: $dsn\n");
}

require_once 'pear-database-maintainer.php';
require_once 'pear-database-note.php';

require_once 'Damblan/Karma.php';
$karma = new Damblan_Karma($dbh);

$karma_level = 'pecl.dev';

$sql = "SELECT p.name, p.id
        FROM packages p
        WHERE p.package_type = 'pecl'
        ORDER BY p.name";
$packages = $dbh->getAssoc($sql, false, null, DB_FETCHMODE_ASSOC);
foreach ($packages as $n => $id) {
    $m = maintainer::get((int)$id);

    if (!empty($m)) {
        echo "\nAltering karma for maintainers of $n package id $id\n";

        foreach ($m as $handle => $m_data) {
            if (!$karma->has($handle, $karma_level)) {
                echo "Giving $handle $karma_level karma\n";
                // Bypassing damblan karma because it needs a logged in user
                $id = $dbh->nextId('karma');
                if (DB::isError($id)) {
                    echo "Couldn't get a new id from the karma table\n";
                    exit;
                }

                $query = 'INSERT INTO karma (id, user, level, granted_by, granted_at)
                          VALUES (?, ?, ?, ?, NOW())';
                $sth = $dbh->query($query, array($id, $handle, $karma_level, 'peclweb'));
                if (DB::isError($sth)) {
                    echo "Giving karma to $handle failed!\n";
                    exit;
                }

                // Adding a note about it
                note::add($handle, 'karma ' . $karma_level . ' granted', 'peclweb');
            } else {
                echo "$handle already has $karma_level\n";
            }
        }
    } else {
        echo "Couldn't find any maintainers for $n id $id possibily an error!\n";
    }
}