<?php

include_once('cls/DbAccessor.php');
include_once('cls/AuthCrypt.php');

class ScheckAuth
{
    public function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

	public function authentication($id, $passwd)
    {
        if ((isset($id) && strlen($id) > 0)
            && (isset($passwd) && strlen($passwd) > 0))
        {
            try { 
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT AUTH_INFO
                                         FROM EXAMINEE_AUTH_INFO
                                         WHERE EXAMINEE_AUTH_ID = "' . $id . '"');

                $data = $dba->getFetchArray($result);

                $obj = new AuthCrypt();
                $decStr = $obj->crypt_decode($passwd, $data['AUTH_INFO']);

                $authStr = substr($decStr, 0, strlen($id));
                if ($authStr == $id)
                {
                    return $data['AUTH_INFO'];
                }
                else
                {
                    return "";
                }

                $dba->close();
            } catch (Exception $e) {
              return "";
            }
        }
    }
}
?>
