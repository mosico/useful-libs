<?php
require_once 'BaseDB.php';

class MysqlDB extends BaseDB
{
    const DB_TYPE = 'mysql';
    
    public function __construct($dbName, $type = 'slave')
    {
        parent::__construct($dbName, $type);
        $this->_dbType = self::DB_TYPE;
        $dsn = "mysql:host={$this->_host};port={$this->_port};dbname={$this->_dbname}";
        $this->connect($dsn);
    }

    public function execSql($sql)
    {
        $rs = $this->_connection->exec($sql);
        return $rs;
    }
}