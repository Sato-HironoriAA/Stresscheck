<?php

class AutoJudgeC_high
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

        // 上司からのサポート
        $val = 15 - ($data[1] + $data[4] + $data[7]); // 15-(No.1+No.4+No.7)
        $ret += $this->calc_1_M($val);

        // 同僚からのサポート
        $val = 15 - ($data[2] + $data[5] + $data[8]); // 15-(No.2+No.5+No.8)
        $ret += $this->calc_2_M($val);

        // 家族や友人からのサポート
        $val = 15 - ($data[3] + $data[6] + $data[9]); // 15-(No.3+No.6+No.9)
        $ret += $this->calc_3_M($val);

        return $ret;
    }

    // 判定（女）
    private function JudgeTotal_W($data)
    {
        $ret = 0;

        // 上司からのサポート
        $val = 15 - ($data[1] + $data[4] + $data[7]); // 15-(No.1+No.4+No.7)
        $ret += $this->calc_1_W($val);

        // 同僚からのサポート
        $val = 15 - ($data[2] + $data[5] + $data[8]); // 15-(No.2+No.5+No.8)
        $ret += $this->calc_2_W($val);

        // 家族や友人からのサポート
        $val = 15 - ($data[3] + $data[6] + $data[9]); // 15-(No.3+No.6+No.9)
        $ret += $this->calc_3_W($val);

        return $ret;
    }

/* 男 */
    private function calc_1_M($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data <= 4)
            {
                $ret = 1;
            }
            else if ($data <= 6)
            {
                $ret = 2;
            }
            else if ($data <= 8)
            {
                $ret = 3;
            }
            else if ($data <= 10)
            {
                $ret = 4;
            }
            else if ($data <= 12)
            {
                $ret = 5;
            }
        }

        return $ret;
    }

    private function calc_2_M($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data <= 5)
            {
                $ret = 1;
            }
            else if ($data <= 7)
            {
                $ret = 2;
            }
            else if ($data <= 9)
            {
                $ret = 3;
            }
            else if ($data <= 11)
            {
                $ret = 4;
            }
            else if ($data == 12)
            {
                $ret = 5;
            }
        }

        return $ret;
    }
    
    private function calc_3_M($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data <= 6)
            {
                $ret = 1;
            }
            else if ($data <= 8)
            {
                $ret = 2;
            }
            else if ($data == 9)
            {
                $ret = 3;
            }
            else if ($data <= 11)
            {
                $ret = 4;
            }
            else if ($data == 12)
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

        if ($data >= 3 && $data <= 12)
        {
            if ($data == 3)
            {
                $ret = 1;
            }
            else if ($data <= 5)
            {
                $ret = 2;
            }
            else if ($data <= 7)
            {
                $ret = 3;
            }
            else if ($data <= 10)
            {
                $ret = 4;
            }
            else if ($data <= 12)
            {
                $ret = 5;
            }
        }

        return $ret;
    }

    private function calc_2_W($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data <= 5)
            {
                $ret = 1;
            }
            else if ($data <= 7)
            {
                $ret = 2;
            }
            else if ($data <= 9)
            {
                $ret = 3;
            }
            else if ($data <= 11)
            {
                $ret = 4;
            }
            else if ($data == 12)
            {
                $ret = 5;
            }
        }

        return $ret;
    }
    
    private function calc_3_W($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data <= 6)
            {
                $ret = 1;
            }
            else if ($data <= 8)
            {
                $ret = 2;
            }
            else if ($data == 9)
            {
                $ret = 3;
            }
            else if ($data <= 11)
            {
                $ret = 4;
            }
            else if ($data == 12)
            {
                $ret = 5;
            }
        }
        
        return $ret;
    }
}
?>
