<?php

class ExceptionalMysqli extends mysqli
{

    public function __construct()
    {
        $args = func_get_args();
        eval("parent::__construct(" . join(',', array_map('MpmStringHelper::addSingleQuotes', $args)) . ");");
        if ($this->connect_errno)
        {
            throw new DatabaseConnectionException($this->connect_error);
        }
    }
    
    public function query($query, $resultMode = MYSQLI_STORE_RESULT)
    {
        $result = parent::query($query, $resultMode);
        if ($this->errno)
        {
            throw new MalformedQueryException($this->error);
        }
        return $result;
    }
    
    public function beginTransaction()
    {
        $this->autocommit(false);
    }

    public function exec($sql)
    {
        return $this->query($sql);
    }

}


?>
