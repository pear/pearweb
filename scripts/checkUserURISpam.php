<?php
require_once 'Net/DNSBL/SURBL.php';
$surbl = new Net_DNSBL_SURBL;

require_once 'DB.php';
$dbh = DB::connect('mysqli://pear:pear@localhost/pear');
$sql = 'SELECT homepage, handle FROM users WHERE registered = 1';
$users = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);

foreach ($users as $u) {
    if (!empty($u['homepage']) && $surbl->isListed($u['homepage'])) {
        echo $u['handle'] . "\n";
    }
}