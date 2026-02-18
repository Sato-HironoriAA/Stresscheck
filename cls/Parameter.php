<?php

class Parameter
{
    private $get = null;
    private $post = null;

    public function __construct($get, $post)
    {
        $this->get = $get;
        $this->post = $post;
    }

    function __destruct()
    {
    }

    public function getParameter($name)
    {
        $retVal = "";

        if (isset($this->get[$name]) && strlen($this->get[$name]) > 0)
        {
            $retVal = $this->get[$name];
        }
        if (isset($this->post[$name]) && strlen($this->post[$name]) > 0)
        {
            $retVal = $this->post[$name];
        }

        return $retVal;
    }

    public function getParameterArray($name)
    {
        $retVal = "";

        if (isset($this->get[$name]) && count($this->get[$name]) > 0)
        {
            $retVal = $this->get[$name];
        }
        if (isset($this->post[$name]) && count($this->post[$name]) > 0)
        {
            $retVal = $this->post[$name];
        }

        return $retVal;
    }
}
?>
