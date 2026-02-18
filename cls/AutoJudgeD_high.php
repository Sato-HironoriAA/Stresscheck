<?php

class AutoJudgeD_high
{
    public function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

    public function JudgeTotal($data, $sex)
    {
        if ($sex == 1)
        {
            return $this->JudgeTotal_M($data);
        }
        else
        {
            return $this->JudgeTotal_W($data);
        }
    }

    // 判定（男）
    private function JudgeTotal_M($data)
    {
        $ret = 0;

        // 仕事や生活の満足度
        $val = 10 - ($data[1] + $data[2]); // 10-(No.1+No.2)
        $ret = $this->calc_1_M($val);

        return $ret;
    }

    // 判定（女）
    private function JudgeTotal_W($data)
    {
        $ret = 0;

        // 仕事や生活の満足度
        $val = 10 - ($data[1] + $data[2]); // 10-(No.1+No.2)
        $ret = $this->calc_1_W($val);

        return $ret;
    }

   /* 男 */
    private function calc_1_M($data)
    {
        $ret = 0;

        if ($data >= 2 && $data <= 8)
        {
            if ($data <= 3)
            {
                $ret = 1;
            }
            else if ($data == 4)
            {
                $ret = 2;
            }
            else if ($data <= 6)
            {
                $ret = 3;
            }
            else if ($data == 7)
            {
                $ret = 4;
            }
            else if ($data == 8)
            {
                $ret = 5;
            }
        }

        return $ret;
    }

   /* 女 */
    private function calc_1_W($data)
    {
        $ret = 0;

        if ($data >= 2 && $data <= 8)
        {
            if ($data <= 3)
            {
                $ret = 1;
            }
            else if ($data == 4)
            {
                $ret = 2;
            }
            else if ($data <= 6)
            {
                $ret = 3;
            }
            else if ($data == 7)
            {
                $ret = 4;
            }
            else if ($data == 8)
            {
                $ret = 5;
            }
        }

        return $ret;
    }
}
?>
