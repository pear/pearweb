<?php
require_once 'MDB2/Schema.php';
class pearweb_postinstall
{
    var $lastversion;
    var $dsn;
    /**
     * Frontend object
     * @var PEAR_Frontend
     * @access private
     */
    var $_ui;

    function init(&$config, &$pkg, $lastversion)
    {
        $this->_ui = &PEAR_Frontend::singleton();
        $this->lastversion = $lastversion;
        return true;
    }

    function run($answers, $phase)
    {
        switch ($phase) {
            case 'askdb' :
                if ($answers['yesno'] != 'y') {
                    $this->_ui->skipParamgroup('init');
                }
                return true;
                break;
            case 'init' :
                PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
                if (PEAR::isError($err = MDB2::loadFile('Driver' . DIRECTORY_SEPARATOR .
                      $answers['driver']))) {
                    PEAR::popErrorHandling();
                    $this->_ui->outputData('ERROR: Unknown MDB2 driver "' .
                        $answers['driver'] . '": ' .
                        $err->getUserInfo() . '. Be sure you have installed ' .
                        'MDB2_Driver_' . $answers['driver']);
                    return false;
                }
                PEAR::popErrorHandling();
                return true;
                return $this->initializeDatabase($answers);
                break;
            case 'askhttpd' :
                if ($answers['yesno'] != 'y') {
                    $this->_ui->skipParamgroup('httpdconf');
                }
                return true;
                break;
            case 'httpdconf' :
                $this->_ui->outputData('httpdconf');
                return true;
                break;
        }
        return true;
    }

    function initializeDatabase($answers)
    {
        $this->dsn = array(
            'phptype' => $answers['driver'],
            'username' => $answers['user'],
            'password' => $answers['password'],
            'hostspec' => $answers['host'],
            'database' => $answers['database']);
        $a = MDB2_Schema::factory($this->dsn,
            array('idxname_format' => '%s',
                  'seqname_format' => 'id',
                  'quote_identifier' => true));
        $c = $a->parseDatabaseDefinitionFile(
            realpath('@web-dir@/sql/pearweb_mdb2schema.xml'));
        $c['name'] = $answers['database'];
        $c['create'] = 1;
        $c['overwrite'] = 0;
        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $err = $a->createDatabase($c);
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($err)) {
            $this->_ui->outputData($err->getUserInfo());
            $this->_ui->outputData($err->getMessage());
            return false;
        }
        return true;
    }
}