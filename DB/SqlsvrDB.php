<?php
require_once 'BaseDB.php';

class SqlsvrDB extends BaseDB
{
    const DB_TYPE = 'sqlsvr';
    
    public function __construct($dbName, $type = 'slave')
    {
        parent::__construct($dbName, $type);
        $this->_dbType = self::DB_TYPE;
        $portParam = $this->_port ? ",{$this->_port}" : '';
        $dsn = "sqlsrv:Server={$this->_host}{$portParam};Database={$this->_dbname}";
        $this->connect($dsn);
    }

}