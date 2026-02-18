<?php

class AutoJudgeD
{
    public function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

    // 判定（男）
    public function Judge_M($data)
    {
        $ret = array();

        // 仕事や生活の満足度
        $val = $data[1] + $data[2]; // No.1+No.2
        $ret[0] = $this->calc_1_M($val, 2);

        return $ret;
    }

    // 判定（女）
    public function Judge_W($data)
    {
        $ret = array();

        // 仕事や生活の満足度
        $val = $data[1] + $data[2]; // No.1+No.2
        $ret[0] = $this->calc_1_W($val, 2);

        return $ret;
    }

   /* 男 */
    private function calc_1_M($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
        {
            if ($data == 2)
            {
                $ret = 5;
            }
            else if ($data == 3)
            {
                $ret = 4;
            }
            else if ($data <= 5)
            {
                $ret = 3;
            }
            else if ($data == 6)
            {
                $ret = 2;
            }
            else
            {
                $ret = 1;
            }
        }

        return $ret;
    }

   /* 女 */
    private function calc_1_W($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
        {
            if ($data == 2)
            {
                $ret = 5;
            }
            else if ($data == 3)
            {
                $ret = 4;
            }
            else if ($data <= 5)
            {
                $ret = 3;
            }
            else if ($data == 6)
            {
                $ret = 2;
            }
            else
            {
                $ret = 1;
            }
        }

        return $ret;
    }
}
?>
