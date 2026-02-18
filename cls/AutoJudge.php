<?php

include_once('AutoJudgeA.php');
include_once('AutoJudgeB.php');
include_once('AutoJudgeC.php');
include_once('AutoJudgeD.php');
include_once('AutoJudgeA_high.php');
include_once('AutoJudgeB_high.php');
include_once('AutoJudgeC_high.php');
include_once('AutoJudgeD_high.php');

class AutoJudge
{
    public function __construct()
    {
        
    }

    function __destruct()
    {
        
    }

    // ストレス状況
    public function getJudge($dataA, $dataB, $dataC, $sex, $companyCd)
    {
        $ret = 0;
     
        $judgeAStresser = $this->getJudgeAStresserNum($dataA, $sex);
        $judgeBReaction = $this->getJudgeBReactionNum($dataB, $sex);

        if ($judgeAStresser == 0 && $judgeBReaction == 0)
        {
            if ($this->getJudgeHigh($dataA, $dataB, $dataC, $sex, $companyCd))
            {
                $ret = 5;
            }
            else
            {
                $ret = 1;
            }
        }
        else if ($judgeAStresser == 1 && $judgeBReaction == 0)
        {
            if ($this->getJudgeHigh($dataA, $dataB, $dataC, $sex, $companyCd))
            {
                $ret = 6;
            }
            else
            {
                $ret = 2;
            }
        }
        else if ($judgeAStresser == 0 && $judgeBReaction == 1)
        {
            if ($this->getJudgeHigh($dataA, $dataB, $dataC, $sex, $companyCd))
            {
                $ret = 7;
            }
            else
            {
                $ret = 3;
            }
        }
        else if ($judgeAStresser == 1 && $judgeBReaction == 1)
        {
            if ($this->getJudgeHigh($dataA, $dataB, $dataC, $sex, $companyCd))
            {
                $ret = 8;
            }
            else
            {
                $ret = 4;
            }
        }
        
        return $ret;
    }

    // ストレス状況
    private function getJudgeHigh($dataA, $dataB, $dataC, $sex, $companyCd)
    {
        $ret = false;

        $ajA = new AutoJudgeA_high();
        $ajB = new AutoJudgeB_high();
        $ajC = new AutoJudgeC_high();

        $ajRetEvaluationA =$ajA->JudgeTotal($dataA, $sex);
        $ajRetEvaluationB =$ajB->JudgeTotal($dataB, $sex);
        $ajRetEvaluationC =$ajC->JudgeTotal($dataC, $sex);

        // 高ストレス判定基準値の初期値を取得
        $ins = IniFile::getInstance();
        $ins->init();
        $judge1 = $ins->getValue('highstressjudge', 'JUDGE1');
        $judge2 = $ins->getValue('highstressjudge', 'JUDGE2');
        $judge3 = $ins->getValue('highstressjudge', 'JUDGE3');

        $dataCount = 0;
        try {
            $dba = new DbAccessor();
            $dba->initialize();
            $result = $dba->getData('SELECT COUNT(*)
                                     FROM M_HIGHSTRESS_JUDGE_VAL
                                     WHERE COMPANY_CD = "' . $companyCd . '"');

            $data = $dba->getFetchArray($result);
            $dataCount = $data[0];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }

        if ($dataCount > 0)
        {
            try {
                $dba = new DbAccessor();
                $dba->initialize();
                $result = $dba->getData('SELECT JUDGE1,
                                                JUDGE2,
                                                JUDGE3
                                         FROM M_HIGHSTRESS_JUDGE_VAL
                                         WHERE COMPANY_CD = "' . $companyCd . '"');

                $data = $dba->getFetchArray($result);

                $dba->close();
            } catch (Exception $e) { 
              echo $e->getMessage();
            }

            $judge1 = $data['JUDGE1'];
            $judge2 = $data['JUDGE2'];
            $judge3 = $data['JUDGE3'];
        }

        if ($ajRetEvaluationB <= $judge1)
        {
            $ret = true;
        }
        else if (($ajRetEvaluationA + $ajRetEvaluationC) <= $judge2
                    && $ajRetEvaluationB <= $judge3)
        {
            $ret = true;
        }

        return $ret;
    }

    // A項目素点換算値取得
    public function getJudgeA($data, $sex)
    {
        $obj = new AutoJudgeA();
        if ($sex == 1)
        {
            return $obj->Judge_M($data);
        }
        else
        {
            return $obj->Judge_W($data);
        }
    }

    // B項目素点換算値取得
    public function getJudgeB($data, $sex)
    {
        $obj = new AutoJudgeB();
        if ($sex == 1)
        {
            return $obj->Judge_M($data);
        }
        else
        {
            return $obj->Judge_W($data);
        }
    }

    // C項目素点換算値取得
    public function getJudgeC($data, $sex)
    {
        $obj = new AutoJudgeC();
        if ($sex == 1)
        {
            return $obj->Judge_M($data);
        }
        else
        {
            return $obj->Judge_W($data);
        }
    }

    // D項目素点換算値取得
    public function getJudgeD($data, $sex)
    {
        $obj = new AutoJudgeD();
        if ($sex == 1)
        {
            return $obj->Judge_M($data);
        }
        else
        {
            return $obj->Judge_W($data);
        }
    }

    public function getJudgeAStresserNum($data, $sex)
    {
        $ret = 0;

        $judgeRet = $this->getJudgeA($data, $sex);

        // 量的負担
        $ret += $this->getJudgeNum($judgeRet[0]);

        // 質的負担
        $ret += $this->getJudgeNum($judgeRet[1]);

        // 身体的負荷
        $ret += $this->getJudgeNum2($judgeRet[2]);

        // 対人関係上
        $ret += $this->getJudgeNum($judgeRet[3]);

        // コントロール
        $ret += $this->getJudgeNum($judgeRet[5]);

        if ($ret > 0)
        {
            $ret = 1;
        }

        return $ret;
    }

    public function getJudgeBReactionNum($data, $sex)
    {
        $ret = 0;

        // 
        $judgeRet = $this->getJudgeB($data, $sex);

        // 活気
        $ret += $this->getJudgeNum($judgeRet[0]);

        // イライラ感
        $ret += $this->getJudgeNum($judgeRet[1]);

        // 疲労感
        $ret += $this->getJudgeNum($judgeRet[2]);

        // 不安感
        $ret += $this->getJudgeNum($judgeRet[3]);

        // 抑うつ感
        $ret += $this->getJudgeNum($judgeRet[4]);

        // 身体愁訴
        $ret += $this->getJudgeNum($judgeRet[5]);

        if ($ret > 0)
        {
            $ret = 1;
        }

        return $ret;
    }

    // ストレッサー
    public function getJudgeAStresser($dataA, $sex)
    {
        $ret = "";

        $judgeRet = $this->getJudgeA($dataA, $sex);

        // 量的負担
        $ret .= $this->getJudgeStr($judgeRet[0]);

        // 質的負担
        $ret .= $this->getJudgeStr($judgeRet[1]);

        // 身体的負荷
        $ret .= $this->getJudgeStr2($judgeRet[2]);

        // 対人関係上
        $ret .= $this->getJudgeStr($judgeRet[3]);

        // コントロール
        $ret .= $this->getJudgeStr($judgeRet[5]);

        return $ret;
    }

    // ストレス反応
    public function getJudgeBReaction($dataB, $sex)
    {
        $ret = "";

        // 
        $judgeRet = $this->getJudgeB($dataB, $sex);

        // 活気
        $ret .= $this->getJudgeStr($judgeRet[0]);

        // イライラ感
        $ret .= $this->getJudgeStr($judgeRet[1]);

        // 疲労感
        $ret .= $this->getJudgeStr($judgeRet[2]);

        // 不安感
        $ret .= $this->getJudgeStr($judgeRet[3]);

        // 抑うつ感
        $ret .= $this->getJudgeStr($judgeRet[4]);

        // 身体愁訴
        $ret .= $this->getJudgeStr($judgeRet[5]);

        return $ret;
    }

    private function getJudgeStr($data)
    {
        $ret = "0";
        if ($data == 1 || $data == 2)
        {
            $ret = "1";
        }
        
        return $ret;
    }

    private function getJudgeStr2($data)
    {
        $ret = "0";
        if ($data == 1)
        {
            $ret = "1";
        }
        
        return $ret;
    }

    private function getJudgeNum($data)
    {
        $ret = 0;
        if ($data == 1 || $data == 2)
        {
            $ret = 1;
        }

        return $ret;
    }

    private function getJudgeNum2($data)
    {
        $ret = 0;
        if ($data == 1)
        {
            $ret = 1;
        }

        return $ret;
    }
}
?>
