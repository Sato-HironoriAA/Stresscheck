<?php

class AutoJudgeB_high
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

        // 活気
        $val = $data[1] + $data[2] + $data[3]; // No.1+No.2+No.3
        $ret += $this->calc_1_M($val);

        // イライラ感
        $val = $data[4] + $data[5] + $data[6]; // No.4+No.5+No.6
        $ret += $this->calc_2_M($val);

        // 疲労感
        $val = $data[7] + $data[8] + $data[9]; // No.7+No.8+No.9
        $ret += $this->calc_3_M($val);
        
        // 不安感
        $val = $data[10] + $data[11] + $data[12]; // No.10+No.11+No.12
        $ret += $this->calc_4_M($val);
        
        // 抑うつ感
        $val = $data[13] + $data[14] + $data[15] + $data[16] + $data[17] + $data[18]; // No.13～No.18の合計
        $ret += $this->calc_5_M($val);
        
        // 身体愁訴
        $val = $data[19] + $data[20] + $data[21] + $data[22] + $data[23] + $data[24] + 
                    $data[25] + $data[26] + $data[27] + $data[28] + $data[29]; // No.19～No.29の合計
        $ret += $this->calc_6_M($val);

        return $ret;
    }

    // 判定（女）
    private function JudgeTotal_W($data)
    {
        $ret = 0;

        // 活気
        $val = $data[1] + $data[2] + $data[3]; // No.1+No.2+No.3
        $ret += $this->calc_1_W($val);

        // イライラ感
        $val = $data[4] + $data[5] + $data[6]; // No.4+No.5+No.6
        $ret += $this->calc_2_W($val);

        // 疲労感
        $val = $data[7] + $data[8] + $data[9]; // No.7+No.8+No.9
        $ret += $this->calc_3_W($val);
        
        // 不安感
        $val = $data[10] + $data[11] + $data[12]; // No.10+No.11+No.12
        $ret += $this->calc_4_W($val);
        
        // 抑うつ感
        $val = $data[13] + $data[14] + $data[15] + $data[16] + $data[17] + $data[18]; // No.13～No.18の合計
        $ret += $this->calc_5_W($val);
        
        // 身体愁訴
        $val = $data[19] + $data[20] + $data[21] + $data[22] + $data[23] + $data[24] + 
                    $data[25] + $data[26] + $data[27] + $data[28] + $data[29]; // No.19～No.29の合計
        $ret += $this->calc_6_W($val);

        return $ret;
    }

/* 男 */
    private function calc_1_M($data)
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
            else if ($data <= 9)
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
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data <= 5)
            {
                $ret = 4;
            }
            else if ($data <= 7)
            {
                $ret = 3;
            }
            else if ($data <= 9)
            {
                $ret = 2;
            }
            else if ($data <= 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_3_M($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data == 4)
            {
                $ret = 4;
            }
            else if ($data <= 7)
            {
                $ret = 3;
            }
            else if ($data <= 10)
            {
                $ret = 2;
            }
            else if ($data <= 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_4_M($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data == 4)
            {
                $ret = 4;
            }
            else if ($data <= 7)
            {
                $ret = 3;
            }
            else if ($data <= 9)
            {
                $ret = 2;
            }
            else if ($data <= 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_5_M($data)
    {
        $ret = 0;

        if ($data >= 6 && $data <= 24)
        {
            if ($data == 6)
            {
                $ret = 5;
            }
            else if ($data <= 8)
            {
                $ret = 4;
            }
            else if ($data <= 12)
            {
                $ret = 3;
            }
            else if ($data <= 16)
            {
                $ret = 2;
            }
            else if ($data <= 24)
            {
                $ret = 1;
            }
        }
        
        return $ret;
    }

    private function calc_6_M($data)
    {
        $ret = 0;

        if ($data >= 11 && $data <= 44)
        {
            if ($data == 11)
            {
                $ret = 5;
            }
            else if ($data <= 15)
            {
                $ret = 4;
            }
            else if ($data <= 21)
            {
                $ret = 3;
            }
            else if ($data <= 26)
            {
                $ret = 2;
            }
            else if ($data <= 44)
            {
                $ret = 1;
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
            else if ($data <= 9)
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
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data <= 5)
            {
                $ret = 4;
            }
            else if ($data <= 8)
            {
                $ret = 3;
            }
            else if ($data <= 10)
            {
                $ret = 2;
            }
            else if ($data <= 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_3_W($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data <= 5)
            {
                $ret = 4;
            }
            else if ($data <= 8)
            {
                $ret = 3;
            }
            else if ($data <= 11)
            {
                $ret = 2;
            }
            else if ($data == 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_4_W($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data == 4)
            {
                $ret = 4;
            }
            else if ($data <= 7)
            {
                $ret = 3;
            }
            else if ($data <= 10)
            {
                $ret = 2;
            }
            else if ($data <= 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_5_W($data)
    {
        $ret = 0;

        if ($data >= 6 && $data <= 24)
        {
            if ($data == 6)
            {
                $ret = 5;
            }
            else if ($data <= 8)
            {
                $ret = 4;
            }
            else if ($data <= 12)
            {
                $ret = 3;
            }
            else if ($data <= 17)
            {
                $ret = 2;
            }
            else if ($data <= 24)
            {
                $ret = 1;
            }
        }
        
        return $ret;
    }

    private function calc_6_W($data)
    {
        $ret = 0;

        if ($data >= 11 && $data <= 44)
        {
            if ($data <= 13)
            {
                $ret = 5;
            }
            else if ($data <= 17)
            {
                $ret = 4;
            }
            else if ($data <= 23)
            {
                $ret = 3;
            }
            else if ($data <= 29)
            {
                $ret = 2;
            }
            else if ($data <= 44)
            {
                $ret = 1;
            }
        }
        
        return $ret;
    }
}
?>
