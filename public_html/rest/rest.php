<?php
if (
    !isset($_GET['start']) OR !isset($_GET['end'])
    OR !is_numeric($_GET['start']) OR !is_numeric($_GET['end'])
) {
    response_header('ERROR');
    report_error('You must send a start and end time in timestamp format');
    response_footer();
    exit;
}

$start = (int)$_GET['start'];
$end   = (int)$_GET['end'];

$allowed_types = array('pecl', 'pear');
if (!isset($_GET['type']) OR !in_array(strtolower($_GET['type']), $allowed_types)) {
    $type = 'pear';
} else {
    $type = htmlspecialchars(strtolower($_GET['type']), ENT_QUOTES);
}

include_once 'pear-database-release.php';
$releases = release::getDateRange($start, $end, $type);
var_export(serialize($releases));