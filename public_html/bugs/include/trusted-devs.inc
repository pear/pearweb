<?php
require_once "Damblan/Karma.php";

$karma = new Damblan_Karma($dbh);
$dev_list = $karma->getUsers("pear.bug.admin");
foreach ($dev_list as $dev) {
    $trusted_developers[] = $dev['user'];
}
