<?php
class JudgeMessage
{
    private static $instance = null;
    private static $ini_array = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    function __destruct()
    {
        
    }

	public static function init()
    {
        if (is_null(self::$ini_array))
        {
            self::$ini_array = parse_ini_file("message/judge_message.txt", true);
        }
    }
    
    public static function getSectionValue($section)
    {
        return  self::$ini_array[$section];
    }
    
    public static function getValue($section, $name)
    {
        return self::$ini_array[$section][$name];
    }
}
?>
