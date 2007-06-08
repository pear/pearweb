<?php
require dirname(dirname(__FILE__)) . '/mockDB/DB.php';
set_include_path(dirname(dirname(dirname(__FILE__))) . '/include' . PATH_SEPARATOR .
    get_include_path());
require_once 'DB.php';
$_ENV['PEAR_DATABASE_DSN'] = 'mock://pear:pear@localhost/pear';
// so we do local
$_SERVER['SERVER_NAME'] = 'localhost';
require_once 'pear-prepend.php';
require_once 'Damblan/Karma.php';
require_once 'Log.php';
require_once 'Log/observer.php';

class Damblan_TestLog extends Log
{
    function log($message, $priority = null)
    {
        $this->_announce(array('priority' => $priority, 'message' => $message));
    }
}

class MockObserver extends Log_Observer
{
    public $events = array();
    function notify($event)
    {
        $this->events[] = $event;
    }
}

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh = DB::connect(PEAR_DATABASE_DSN, $options);
$mock = new mockDB_core;
$dbh->setMock($mock);
$t = new Damblan_TestLog('', '', array(), PEAR_LOG_DEBUG);
$o = new MockObserver;
$t->attach($o);
// fake auth_user
$auth_user = new stdClass;
$auth_user->handle = 'cellog';
$karma = new Damblan_Karma($dbh, $t, $o);
require dirname(dirname(__FILE__)) . '/phpt_test.php5.inc';
$phpt = new PEAR_PHPTest;

?>