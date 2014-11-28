<?php

require_once 'dbConfig.php';

abstract class BaseDB
{
    protected $_connection = null;
    protected $_statementss = array();
    protected $_curStatement = null;
    protected $_curSql = '';
    protected $_dbType = 'mysql';
    
    protected $_host = '';
    protected $_user = '';
    protected $_password = '';
    protected $_dbname = '';
    protected $_port = '';
    
    public function __construct($dbName, $type = 'slave')
    {
        $dbName = strtoupper(trim($dbName));
        $type = strtoupper(trim($type));
        
        if (!defined("{$type}DB_{$dbName}_HOST")) {
            $type = 'SLAVE';
            $dbName = 'SIMULATOR';
            var_dump('Dont defined db const, use simulator as default');
        }
        
        $this->_host = constant("{$type}DB_{$dbName}_HOST");
        $this->_user = constant("{$type}DB_{$dbName}_USER");
        $this->_password = constant("{$type}DB_{$dbName}_PW");
        $this->_port = (int) constant("{$type}DB_{$dbName}_PORT");
        $this->_dbname = constant("{$type}DB_{$dbName}_DB");
    }
    
    public function connect($dsn)
    {
        $this->_connection = new PDO($dsn, $this->_user, $this->_password);
        $err = $this->pdoError();
        if (!$this->_connection) {
            echo "Connect to DB Error, Host: {$this->_host}, User: {$this->_user}, DB: {$this->_dbname}, Port: {$this->_port} <br>";
            echo "Error Info: $err <br>";
            exit;
        }
        if ($err) {
            echo "Connect to DB Occur An Error, Host: {$this->_host}, Info: $err <br>";
        }
        $this->_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->_connection->setAttribute(PDO::ATTR_TIMEOUT, 1000);
    }
    
    public function getOne($sql, $parament = array())
    {
        $this->_curSql = $sql;
        $result = null;
        if (!empty($parament)) {
            $sqlHash = md5($sql);
            // prepare
            if (!isset($this->_statements[$sqlHash])) {
                $this->_statements[$sqlHash] = $this->_connection->prepare($sql);
                $this->showErrorInfo();
            }
            // execute
            $this->_statements[$sqlHash]->execute($parament);
            $this->showErrorInfo();
            $this->_curStatement = $this->_statements[$sqlHash];
        } else {
            $this->_curStatement = $this->_connection->query($sql);
        }

        $this->showErrorInfo();
        $row = $this->_curStatement->fetch();
        $result = empty($row) ? null : $row;
        return $result;
    }
    
    public function getList($sql, $parament = array())
    {
        $result = null;
        $sth = null;
        $this->_curSql = $sql;
        if (!empty($parament)) {
            $sqlHash = md5($sql);
            // prepare
            if (!isset($this->_statements[$sqlHash])) {
                $this->_statements[$sqlHash] = $this->_connection->prepare($sql);
                $this->showErrorInfo();
            }
            // execute
            $this->_statements[$sqlHash]->execute($parament);
            $this->showErrorInfo();
            $sth = $this->_statements[$sqlHash];
        } else {
            $sth = $this->_connection->query($sql);
        }
        
        $this->showErrorInfo();
        $row = $sth->fetchAll();
        $result = empty($row) ? array() : $row;
        return $result;
    }
    
    public function showErrorInfo($isExit = true)
    {
        $err = '';
        $pdoErr = $this->pdoError();
        $staErr = $this->statementError();
        if ($pdoErr) $err .= "PDO Error Info: $pdoErr <br>";
        if ($staErr) $err .= "Statement Error Info: $staErr <br>";
        if ($err) {
            echo $err, "SQL: {$this->_curSql} <br>";
            if ($isExit) exit();
        }
    }
    
    public function pdoError()
    {
        $err = '';
        if ($this->_connection) {
            $tmp = $this->_connection->errorInfo();
            if (!empty($tmp[2])) $err = $tmp[2];
        }
        return $err;
    }
    
    public function statementError()
    {
        $err = '';
        if ($this->_curStatement) {
            $tmp = $this->_curStatement->errorInfo();
            if (!empty($tmp[2])) $err = $tmp[2];
        }
        return $err;
    }
    
    public function getConnection()
    {
        return $this->_connection;
    }
    
    public function __destruct()
    {
        
    }
}