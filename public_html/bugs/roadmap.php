<?php

/**
 * Obtain common includes
 */
require dirname(__FILE__) . '/include/functions.inc';
Bug_DataObject::init();
if (isset($_GET['edit'])) {
    $bugdb = Bug_DataObject::bugDB('bugdb_roadmap');
    $bugdb->id = $_GET['edit'];
    if (!$bugdb->find(true)) {
        response_header('Error :: no such roadmap');
        display_bug_error('Unknown roadmap "' . clean($_GET['edit']));
        response_footer();
        exit;
    }
    $_GET['package'] = $bugdb->package;
}
if (isset($_GET['edit']) || isset($_GET['new']) || isset($_GET['delete'])) {
    auth_require();
    if (isset($_GET['delete'])) {
        $roadmap = Bug_DataObject::bugDB('bugdb_roadmap');
        $roadmap->id = $_GET['delete'];
        if ($roadmap->find(true)) {
            $_GET['package'] = $roadmap->package;
        } else {
            $_GET['package'] = '@#^$&*#^@*$&@';
        }
    }
    $bugtest = Bug_DataObject::pearDB('maintains');
    $bugtest->package = package::info($_GET['package'], 'id');
    $bugtest->handle = $auth_user->handle;
    if (!$bugtest->find(true) || !$bugtest->role == 'lead') {
        response_header('Error :: insufficient privileges');
        display_bug_error('You must be a lead maintainer to edit a package\'s roadmap');
        response_footer();
        exit;
    }
}
if (isset($_GET['new']) && isset($_POST['go'])) {
    $bugdb = Bug_DataObject::bugDB('bugdb_roadmap');
    $bugdb->roadmap_version = $_POST['roadmap_version'];
    $bugdb->releasedate = date('Y-m-d H:i:s', strtotime($_POST['releasedate']));
    $bugdb->package = $_GET['package'];
    $bugdb->insert();
    unset($_GET['new']);
}
if (isset($_GET['edit']) && isset($_POST['go'])) {
    $bugdb = Bug_DataObject::bugDB('bugdb_roadmap');
    $bugdb->id = $_GET['edit'];
    if ($bugdb->find(false)) {
        $bugdb->roadmap_version = $_POST['roadmap_version'];
        $bugdb->releasedate = date('Y-m-d H:i:s', strtotime($_POST['releasedate']));
        $bugdb->package = $_GET['package'];
        $bugdb->description = $_POST['description'];
        $bugdb->update();
        unset($_GET['edit']);
    }
}
if (isset($_GET['delete'])) {
    $links = Bug_DataObject::bugDB('bugdb_roadmap_link');
    $bugdb = Bug_DataObject::bugDB('bugdb_roadmap');
    $links->roadmap_id = $bugdb->id = $_GET['delete'];
    $links->delete();
    $bugdb->delete();
}
$test = Bug_DataObject::pearDB('packages');
$test->name = $_GET['package'];
if (!isset($_GET['package']) || !$test->find()) {
    response_header('Error :: no such package');
    display_bug_error('Unknown package "' . clean($_GET['package']));
    response_footer();
    exit;
}
$order_options = array(
    ''             => 'relevance',
    'id'           => 'ID',
    'ts1'          => 'date',
    'package'      => 'package',
    'bug_type'     => 'bug_type',
    'status'       => 'status',
    'package_version'  => 'package_version',
    'php_version'  => 'php_version',
    'php_os'       => 'os',
    'sdesc'        => 'summary',
    'assign'       => 'assignment',
);
$bugdb = Bug_DataObject::bugDb('bugdb');
$savant = Bug_DataObject::getSavant();
/*
* need to move this to DB eventually...
*/
$mysql4 = function_exists('mysqli_connect') ||
    version_compare(mysql_get_server_info(), '4.0.0', 'ge');

if ($mysql4) {
    $bugdb->selectAdd('SQL_CALC_FOUND_ROWS');
} else {
}

$bugdb->selectAdd('TO_DAYS(NOW())-TO_DAYS(bugb.ts2) AS unchanged');
$bugdb->package_name = $_GET['package'];

if (empty($_GET['direction']) || $_GET['direction'] != 'DESC') {
    $direction = 'ASC';
} else {
    $direction = 'DESC';
}

if (empty($_GET['order_by']) ||
    !array_key_exists($_GET['order_by'], $order_options))
{
    $order_by = 'id';
} else {
    $order_by = $_GET['order_by'];
}

if (empty($_GET['reorder_by']) ||
    !array_key_exists($_GET['reorder_by'], $order_options))
{
    $reorder_by = '';
} else {
    $reorder_by = $_GET['reorder_by'];
    if ($order_by == $reorder_by) {
        $direction = $direction == 'ASC' ? 'DESC' : 'ASC';
    } else {
        $direction = 'ASC';
        $order_by = $reorder_by;
    }
}

$bugdb->orderBy($order_by . ' ' . $direction);

if (empty($_GET['begin']) || !(int)$_GET['begin']) {
    $begin = 0;
} else {
    $begin = (int)$_GET['begin'];
}

if (empty($_GET['limit']) || !(int)$_GET['limit']) {
    if (!empty($_GET['limit']) && $_GET['limit'] == 'All') {
        $limit = 'All';
    } else {
        $limit = 30;
        $bugdb->limit($begin, $limit);
    }
} else {
    $limit  = (int)$_GET['limit'];
    $bugdb->limit($begin, $limit);
}

$allroadmaps = Bug_DataObject::bugDB('bugdb_roadmap');
$allroadmaps->package = $_GET['package'];
$allroadmaps->find(false);
$roadmaps = Bug_DataObject::bugDB('bugdb_roadmap_link');
$roadmaps->selectAs();
$savant->bugs = $savant->features = $savant->roadmap = array();
$peardb = Bug_DataObject::pearDB('releases');
$peardb->package = $_GET['package'];
while ($allroadmaps->fetch()) {
    $test = clone($peardb);
    $test->version = $allroadmaps->roadmap_version;
    if ($test->find()) {
        // already released, so this is defunct
        continue;
    }
    $features = clone($bugdb);
    $bugs = clone($bugdb);

    $roadmaps->roadmap_id = $allroadmaps->id;
    $features->selectAs();
    $features->joinAdd($roadmaps);
    $features->bug_type = 'Feature/Change Request';
    $rows = $features->find(false);

    if ($mysql4) {
        $total_rows = $dbh->getOne('SELECT FOUND_ROWS()');
    } else {
        /* lame mysql 3 compatible attempt to allow browsing the search */
        $total_rows = $rows < 10 ? $rows : $begin + $rows + 10;
    }

    if ($rows) {
        $package_string = '';

        $link = 'roadmap.php' .
                '?' .
                $package_string  .
                '&amp;order_by='    . $order_by .
                '&amp;direction='   . $direction .
                '&amp;limit='       . $limit;

        $savant->begin = $begin;
        $savant->rows = $rows;
        $savant->total_rows = $total_rows;
        $savant->link = $link;
        $savant->limit = $limit;
        $results = array();
        while ($features->fetch()) {
            $results[] = $features->toArray();
        }
        $savant->results = $results;
        $savant->tla = $tla;
        $savant->types = $types;
        $features = $savant->fetch('searchresults.php');
    } else {
        $features = 'None';
    }

    $bugs->bug_type = 'Bug';
    $bugs->selectAs();
    $bugs->joinAdd($roadmaps);
    $rows = $bugs->find(false);

    if ($mysql4) {
        $total_rows = $dbh->getOne('SELECT FOUND_ROWS()');
    } else {
        /* lame mysql 3 compatible attempt to allow browsing the search */
        $total_rows = $rows < 10 ? $rows : $begin + $rows + 10;
    }

    if ($rows) {
        $package_string = '';

        $link = 'roadmap.php' .
                '?' .
                $package_string  .
                '&amp;order_by='    . $order_by .
                '&amp;direction='   . $direction .
                '&amp;limit='       . $limit;

        $savant->begin = $begin;
        $savant->rows = $rows;
        $savant->total_rows = $total_rows;
        $savant->link = $link;
        $savant->limit = $limit;
        $results = array();
        while ($bugs->fetch()) {
            $results[] = $bugs->toArray();
        }
        $savant->results = $results;
        $savant->tla = $tla;
        $savant->types = $types;
        $bugs = $savant->fetch('searchresults.php');
    } else {
        $bugs = 'None';
    }
    $savant->bugs[$allroadmaps->roadmap_version] = $bugs;
    $savant->feature_requests[$allroadmaps->roadmap_version] = $features;
    $savant->roadmap[] = $allroadmaps->toArray();
}
$savant->package = $_GET['package'];
if (isset($_GET['edit'])) {
    $bugdb = Bug_DataObject::bugDB('bugdb_roadmap');
    $bugdb->id = $_GET['edit'];
    if (!$bugdb->find(true)) {
        response_header('Error :: no such roadmap');
        display_bug_error('Unknown roadmap "' . clean($_GET['edit']));
        response_footer();
        exit;
    }
    $savant->info = $bugdb->toArray();
    $savant->isnew = false;
    $savant->display('roadmapform.php');
    exit;
}
if (isset($_GET['new'])) {
    if (isset($_POST['go'])) {
        $bugdb = Bug_DataObject::bugDB('bugdb_roadmap');
        $bugdb->description = $_POST['description'];
        $bugdb->roadmap_version = $_POST['roadmap_version'];
        $bugdb->releasedate = date('Y-m-d H:i:s', strtotime($_POST['releasedate']));
        $bugdb->package = $_GET['package'];
        $bugdb->insert();
    }
    $savant->info = array(
        'package' => clean($_GET['package']),
        'releasedate' => isset($_POST['releasedate']) ?
            date('Y-m-d H:i:s', strtotime($_POST['releasedate'])) : '',
        'roadmap_version' => isset($_POST['roadmap_version']) ? clean($_POST['roadmap_version']) :
            '',
        'description' => isset($_POST['description']) ? clean($_POST['description']) :
            '',
        );
    $savant->isnew = true;
    $savant->display('roadmapform.php');
    exit;
}
$savant->display('roadmap.php');