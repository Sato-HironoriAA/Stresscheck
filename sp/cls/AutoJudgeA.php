<?php

class AutoJudgeA
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

        // 心理的な仕事の負担(量)
        $val = $data[1] + $data[2] + $data[3]; // No.1+No.2+No.3
        $ret[0] = $this->calc_1_M($val, 3);

        // 心理的な仕事の負担(質)
        $val = $data[4] + $data[5] + $data[6]; // No.4+No.5+No.6
        $ret[1] = $this->calc_2_M($val, 3);

        // 自覚的な身体的負担度
        $val = $data[7]; // No.7
        $ret[2] = $this->calc_3_M($val);
        
        // 職場の対人関係でのストレス
        $val = $data[12] + $data[13] + (5 - $data[14]); // No.12+No.13+(5 - No.14)
        $ret[3] = $this->calc_4_M($val, 3);
        
        // 職場環境によるストレス
        $val = $data[15]; // No.15
        $ret[4] = $this->calc_5_M($val);
        
        // 仕事のコントロール度
        $val = $data[8] + $data[9] + $data[10]; // No.8+No.9+No.10
        $ret[5] = $this->calc_6_M($val, 3);
        
        // あなたの技術の活用度
        $val = $data[11]; // No.11
        $ret[6] = $this->calc_7_M($val);
        
        // あなたが感じている仕事の適正度
        $val = $data[16]; // No.16
        $ret[7] = $this->calc_8_M($val);
        
        // 働きがい
        $val = $data[17]; // No.17
        $ret[8] = $this->calc_9_M($val);
        
        return $ret;
    }

    // 判定（女）
    public function Judge_W($data)
    {
        $ret = array();

        // 心理的な仕事の負担(量)
        $val = $data[1] + $data[2] + $data[3]; // No.1+No.2+No.3
        $ret[0] = $this->calc_1_W($val, 3);

        // 心理的な仕事の負担(質)
        $val = $data[4] + $data[5] + $data[6]; // No.4+No.5+No.6
        $ret[1] = $this->calc_2_W($val, 3);

        // 自覚的な身体的負担度
        $val = $data[7]; // No.7
        $ret[2] = $this->calc_3_W($val);
        
        // 職場の対人関係でのストレス
        $val = $data[12] + $data[13] + (5 - $data[14]); // No.12+No.13+(5 - No.14)
        $ret[3] = $this->calc_4_W($val, 3);
        
        // 職場環境によるストレス
        $val = $data[15]; // No.15
        $ret[4] = $this->calc_5_W($val);
        
        // 仕事のコントロール度
        $val = $data[8] + $data[9] + $data[10]; // No.8+No.9+No.10
        $ret[5] = $this->calc_6_W($val, 3);
        
        // あなたの技術の活用度
        $val = $data[11]; // No.11
        $ret[6] = $this->calc_7_W($val);
        
        // あなたが感じている仕事の適正度
        $val = $data[16]; // No.16
        $ret[7] = $this->calc_8_W($val);
        
        // 働きがい
        $val = $data[17]; // No.17
        $ret[8] = $this->calc_9_W($val);
        
        return $ret;
    }

/* 男 */
    private function calc_1_M($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
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
            else
            {
                $ret = 5;
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
            else
            {
                $ret = 5;
            }
        }
        
        return $ret;
    }
    
    private function calc_3_M($data)
    {
        return $data;
    }
    
    private function calc_4_M($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
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
            else
            {
                $ret = 5;
            }
        }

        return $ret;
    }

    private function calc_5_M($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 1;
        }
        else if ($data == 2)
        {
            $ret = 2;
        }
        else if ($data == 3)
        {
            $ret = 3;
        }
        else if ($data == 4)
        {
            $ret = 4;
        }
        
        return $ret;
    }
    
    private function calc_6_M($data, $threshold)
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
    
    private function calc_7_M($data)
    {
        return $data;
    }
    
    private function calc_8_M($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 5;
        }
        else if ($data == 2)
        {
            $ret = 3;
        }
        else if ($data == 3)
        {
            $ret = 2;
        }
        else if ($data == 4)
        {
            $ret = 1;
        }
        
        return $ret;
    }
    
    private function calc_9_M($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 5;
        }
        else if ($data == 2)
        {
            $ret = 3;
        }
        else if ($data == 3)
        {
            $ret = 2;
        }
        else if ($data == 4)
        {
            $ret = 1;
        }
        
        return $ret;
    }

/* 女 */
    private function calc_1_W($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
        {
            if ($data == 3)
            {
                $ret = 1;
            }
            else if ($data <= 5)
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
            else
            {
                $ret = 5;
            }
        }
        
        return $ret;
    }

    private function calc_2_W($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
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
            else
            {
                $ret = 5;
            }
        }
        
        return $ret;
    }
    
    private function calc_3_W($data)
    {
        return $data;
    }
    
    private function calc_4_W($data, $threshold)
    {
        $ret = 0;
        if ($data >= $threshold)
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
            else
            {
                $ret = 5;
            }
        }

        return $ret;
    }

    private function calc_5_W($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 1;
        }
        else if ($data == 2)
        {
            $ret = 2;
        }
        else if ($data == 3)
        {
            $ret = 3;
        }
        else if ($data == 4)
        {
            $ret = 5;
        }
        
        return $ret;
    }
    
    private function calc_6_W($data, $threshold)
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
    
    private function calc_7_W($data)
    {
        return $data;
    }
    
    private function calc_8_W($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 5;
        }
        else if ($data == 2)
        {
            $ret = 3;
        }
        else if ($data == 3)
        {
            $ret = 2;
        }
        else if ($data == 4)
        {
            $ret = 1;
        }
        
        return $ret;
    }
    
    private function calc_9_W($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 5;
        }
        else if ($data == 2)
        {
            $ret = 3;
        }
        else if ($data == 3)
        {
            $ret = 2;
        }
        else if ($data == 4)
        {
            $ret = 1;
        }
        
        return $ret;
    }
}
?>
