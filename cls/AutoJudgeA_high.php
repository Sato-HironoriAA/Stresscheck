<?php

class AutoJudgeA_high
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

        // 心理的な仕事の負担(量)
        $val = 15 - ($data[1] + $data[2] + $data[3]); // 15-(No.1+No.2+No.3)
        $ret += $this->calc_1_M($val);

        // 心理的な仕事の負担(質)
        $val = 15 - ($data[4] + $data[5] + $data[6]); // 15-(No.4+No.5+No.6)
        $ret += $this->calc_1_M($val);

        // 自覚的な身体的負担度
        $val = 5 - $data[7]; // 5-No.7
        $ret += $this->calc_2_M($val);
        
        // 職場の対人関係でのストレス
        $val = (10 - ($data[12] + $data[13])) + $data[14]; // 10-(No.12+No.13)+No.14
        $ret += $this->calc_3_M($val);
        
        // 職場環境によるストレス
        $val = 5 - $data[15]; // 5-No.15
        $ret += $this->calc_4_M($val);
        
        // 仕事のコントロール度
        $val = 15 - ($data[8] + $data[9] + $data[10]); // 15-(No.8+No.9+No.10)
        $ret += $this->calc_5_M($val);
        
        // あなたの技術の活用度
        $val = $data[11]; // No.11
        $ret += $this->calc_6_M($val);
        
        // あなたが感じている仕事の適正度
        $val = 5 - $data[16]; // 5-No.16
        $ret += $this->calc_7_M($val);
        
        // 働きがい
        $val = 5 - $data[17]; // 5-No.17
        $ret += $this->calc_7_M($val);
        
        return $ret;
    }

    // 判定（女）
    private function JudgeTotal_W($data)
    {
        $ret = 0;

        // 心理的な仕事の負担(量)
        $val = 15 - ($data[1] + $data[2] + $data[3]); // 15-(No.1+No.2+No.3)
        $ret += $this->calc_1_W($val);

        // 心理的な仕事の負担(質)
        $val = 15 - ($data[4] + $data[5] + $data[6]); // 15-(No.4+No.5+No.6)
        $ret += $this->calc_2_W($val);

        // 自覚的な身体的負担度
        $val = 5 - $data[7]; // 5-No.7
        $ret += $this->calc_3_W($val);
        
        // 職場の対人関係でのストレス
        $val = (10 - ($data[12] + $data[13])) + $data[14]; // 10 -(No.12+No.13)+No.14
        $ret += $this->calc_4_W($val);
        
        // 職場環境によるストレス
        $val = 5 - $data[15]; // 5-No.15
        $ret += $this->calc_5_W($val);
        
        // 仕事のコントロール度
        $val = 15 - ($data[8] + $data[9] + $data[10]); // 15-(No.8+No.9+No.10)
        $ret += $this->calc_6_W($val);
        
        // あなたの技術の活用度
        $val = $data[11]; // No.11
        $ret += $this->calc_7_W($val);
        
        // あなたが感じている仕事の適正度
        $val = 5 - $data[16]; // 5-No.16
        $ret += $this->calc_8_W($val);
        
        // 働きがい
        $val = 5 - $data[17]; // 5-No.17
        $ret += $this->calc_8_W($val);

        return $ret;
    }

/* 男 */
    private function calc_1_M($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
        {
            if ($data <= 5)
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
            else if ($data == 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }

    private function calc_2_M($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
            if ($data == 1)
            {
                $ret = 4;
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

    private function calc_4_M($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
            if ($data == 1)
            {
                $ret = 4;
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
        }
        
        return $ret;
    }
    
    private function calc_5_M($data)
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

    private function calc_6_M($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
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
        }

        return $ret;
    }
    
    private function calc_7_M($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
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
        }
        
        return $ret;
    }

/* 女 */
    private function calc_1_W($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
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
            else if ($data == 12)
            {
                $ret = 1;
            }
        }

        return $ret;
    }
    
    private function calc_2_W($data)
    {
        $ret = 0;

        if ($data >= 3 && $data <= 12)
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

        if ($data >= 1 && $data <= 4)
        {
            if ($data == 1)
            {
                $ret = 4;
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
    
    private function calc_5_W($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
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
        }

        return $ret;
    }

    private function calc_6_W($data)
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
    
    private function calc_7_W($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
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
        }

        return $ret;
    }
    
    private function calc_8_W($data)
    {
        $ret = 0;

        if ($data >= 1 && $data <= 4)
        {
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
        }
        
        return $ret;
    }
}
?>
