<?php

    require_once 'pear-database.php';
    $res = $dbh->getOne('SELECT count( id ) AS count FROM bugdb WHERE STATUS = "Open"');
    if (DB::isError($res)) {
        die;
    }
    echo $res;

?>
