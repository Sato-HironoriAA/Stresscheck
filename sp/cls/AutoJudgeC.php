<?php

class AutoJudgeC
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

        // 上司からのサポート
        $val = $data[1] + $data[4] + $data[7]; // No.1+No.4+No.7
        $ret[0] = $this->calc_1_M($val, 3);

        // 同僚からのサポート
        $val = $data[2] + $data[5] + $data[8]; // No.2+No.5+No.8
        $ret[1] = $this->calc_2_M($val, 3);

        // 家族や友人からのサポート
        $val = $data[3] + $data[6] + $data[9]; // No.3+No.6+No.9
        $ret[2] = $this->calc_3_M($val, 3);

        return $ret;
    }

    // 判定（女）
    public function Judge_W($data)
    {
        $ret = array();

        // 上司からのサポート
        $val = $data[1] + $data[4] + $data[7]; // No.1+No.4+No.7
        $ret[0] = $this->calc_1_W($val, 3);

        // 同僚からのサポート
        $val = $data[2] + $data[5] + $data[8]; // No.2+No.5+No.8
        $ret[1] = $this->calc_2_W($val, 3);

        // 家族や友人からのサポート
        $val = $data[3] + $data[6] + $data[9]; // No.3+No.6+No.9
        $ret[2] = $this->calc_3_W($val, 3);

        return $ret;
    }

/* 男 */
    private function calc_1_M($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
        {
            if ($data <= 4)
            {
                $ret = 5;
            }
            else if ($data <= 6)
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
            else
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_2_M($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
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
            else
            {
                $ret = 1;
            }
        }

        return $ret;
    }
    
    private function calc_3_M($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
        {
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data <= 5)
            {
                $ret = 4;
            }
            else if ($data == 6)
            {
                $ret = 3;
            }
            else if ($data <= 8)
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
            if ($data <= 4)
            {
                $ret = 5;
            }
            else if ($data <= 7)
            {
                $ret = 4;
            }
            else if ($data <= 9)
            {
                $ret = 3;
            }
            else if ($data <= 11)
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

    private function calc_2_W($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
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
            else
            {
                $ret = 1;
            }
        }

        return $ret;
    }
    
    private function calc_3_W($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
        {
            if ($data == 3)
            {
                $ret = 5;
            }
            else if ($data <= 5)
            {
                $ret = 4;
            }
            else if ($data == 6)
            {
                $ret = 3;
            }
            else if ($data <= 8)
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
