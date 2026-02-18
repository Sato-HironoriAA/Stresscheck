<?php

session_start();

class ProcNendo
{
    public function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

    function getProcNendo()
    {
        if (isset($_SESSION['proc_nendo']) && strlen($_SESSION['proc_nendo']) > 0)
        {
            return $_SESSION['proc_nendo'];
        }
        else
        {
            return -1;
        }
    }
}
?>
