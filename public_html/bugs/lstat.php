<?php

    require_once 'pear-database.php';
    $res = $dbh->getOne('SELECT count( id ) AS count FROM bugdb WHERE status = "Open" OR status = "Assigned"');
    if (DB::isError($res)) {
        die;
    }
    echo $res;

?>
