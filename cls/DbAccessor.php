<?php
include_once('IniFile.php');
class DbAccessor
{
    //private static $con;
    

    public function __construct()
    {
        $this->initialize();
        
    }

    function __destruct()
    {
        $this->close();
    }

    public function initialize()
    {
        $ins = IniFile::getInstance();
        $ins->init();
        $connectInfo = $ins->getSectionValue('database');

        $this->dbName = $connectInfo['NAME'];

        $this->con = mysqli_connect($connectInfo['URL'], $connectInfo['ID'], $connectInfo['PASS']);
        if (!$this->con) {
            throw new Exception('not db connect');
        }
    }
    
    public function getData($sql)
    {
        $result = mysqli_select_db($this->con, $this->dbName);
        if (!$result) {
          throw new Exception('not get data');
        }

        $result = mysqli_query($this->con, 'SET NAMES utf8');
        if (!$result) {
          throw new Exception('not char change');
        }

        $result = mysqli_query($this->con, $sql);

        return $result;
    }
    
    public function getFetchArray($result)
    {
        return mysqli_fetch_array($result);
    }
    
    public function insertData($sql)
    {
        $result = mysqli_select_db($this->con, $this->dbName);
        if (!$result) {
          throw new Exception('not get data');
        }

        $result = mysqli_query($this->con, 'SET NAMES utf8');
        if (!$result) {
          throw new Exception('not char change');
        }

        $result = mysqli_query($this->con, $sql);

        return $result;
    }
    
    public function updateData($sql)
    {
        $result = mysqli_select_db($this->con, $this->dbName);
        if (!$result) {
          throw new Exception('not get data');
        }

        $result = mysqli_query($this->con, 'SET NAMES utf8');
        if (!$result) {
          throw new Exception('not char change');
        }

        $result = mysqli_query($this->con, $sql);

        return $result;
    }
    
    public function deleteData($sql)
    {
        $result = mysqli_select_db($this->con, $this->dbName);
        if (!$result) {
          throw new Exception('not get data');
        }

        $result = mysqli_query($this->con, 'SET NAMES utf8');
        if (!$result) {
          throw new Exception('not char change');
        }

        $result = mysqli_query($this->con, $sql);

        return $result;
    }
    
    public function getCount($result)
    {
        return mysqli_num_rows($result);
    }
    
    public function close()
    {
        if (!$this->con)
        {
            $this->con = mysqli_close($this->con);
            if (!$this->con)
            {
              throw new Exception('not db close');
            }
        }
    }
}
?>
