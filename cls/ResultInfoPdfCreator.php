<?php
include_once('PDF_Japanese_Protection.php');
include_once('IniFile.php');
include_once('JudgeMessage.php');
include_once('AutoJudge.php');
include_once('DbAccessor.php');
include_once('ResultInfoPdfData.php');
include_once('Barcode.php');
include_once('AutoJudgeA_high.php');
include_once('AutoJudgeB_high.php');
include_once('AutoJudgeC_high.php');
include_once('AutoJudgeD_high.php');

class ResultInfoPdfCreator
{
    private $pdf;
    private $cData;
    private $dba;

    public function __construct()
    {
        if (!$this->dba)
        {
            $this->dba = new DbAccessor();
            $this->dba->initialize();
        }
    }

    function __destruct()
    {
        if ($this->dba)
        {
            $this->dba->close();
        }
    }

    /*
    // 個人指定
    */
    public function pdf_create($target, $authId, $companyCd, $busyoCdList, $eDateS, $eDateE, $printSts, $chkStress, $procNendo)
    {
        $this->pdf = new PDF_Japanese_Protection('P', 'mm', 'A4');
        $this->pdf_init();

        $this->cData = new ResultInfoPdfData();

        // 個人情報取得
        $result = $this->cData->getExamineeList($this->dba, $authId, $companyCd, $busyoCdList, $eDateS, $eDateE, $printSts, $chkStress, $procNendo);

        while ($data = $this->dba->getFetchArray($result))
        {
            // 受診値取得
            $resultList = $this->cData->getResultData($this->dba, $data['AUTH_INFO']);

            // 受診結果(個人用)
            if ($target[0] == "1")
            {
                // 閲覧操作をパスワード必須にする
                $str = (intval(date('Ymd', strtotime($data['BYMD2']))) + 
                            intval($data['PERSON_NO']) + intval($data['EXAMINEE_NENDO'])) * 2;
                $pass = (string)$str;
                $this->pdf->SetProtection([], $pass, $pass);

                //テンプレートPDF読み込み
                $pageno = $this->pdf->setSourceFile('pdf_template/result_person.pdf');

                // 1ページ目
                $this->pdf_create_person1($data, $resultList, $procNendo, 0);
                        
                // 2ページ目
                $this->pdf_create_person2($data, $resultList, $procNendo, 0);
                
                // 3ページ目
                $this->pdf_create_person3($data, $resultList, $procNendo, 0);
            }
            
            // 結果通知同意書
            if ($target[1] == "1")
            {
                //テンプレートPDF読み込み
                $pageno = $this->pdf->setSourceFile('pdf_template/result_consentOMR.pdf');

                $this->pdf_create_result_consent($data);
            }

            // 受診結果(事業所用)
            if ($target[3] == "1")
            {
                if ($data['RESULT_OK_FLG'] == "1")
                {
                    //テンプレートPDF読み込み
                    $pageno = $this->pdf->setSourceFile('pdf_template/result_company.pdf');

                    // 1ページ目
                    $this->pdf_create_person1($data, $resultList, $procNendo, 1);
                            
                    // 2ページ目
                    $this->pdf_create_person2($data, $resultList, $procNendo, 1);
                    
                    // 3ページ目
                    $this->pdf_create_person3($data, $resultList, $procNendo, 1);
                }
            }

        }

        // 受診状況一覧表
        if ($target[2] == "1")
        {
            // 部署リスト取得
            $result = $this->cData->getBusyoList($this->dba, $companyCd, $busyoCdList);

            while ($data = $this->dba->getFetchArray($result))
            {
                // 情報取得
                $result2 = $this->cData->getExamineeList2($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                $dataCnt = $this->dba->getCount($result2);

                if ($dataCnt > 0)
                {
                    //テンプレートPDF読み込み
                    $pageno = $this->pdf->setSourceFile('pdf_template/examinee_list.pdf');

                    $result3 = $this->cData->getExamineeCnt($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                    $data3 = $this->dba->getFetchArray($result3);
                    $examineeCnt = $data3[0];

                    $result3 = $this->cData->getNoExamineeCnt($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                    $data3 = $this->dba->getFetchArray($result3);
                    $examineeNoCnt = $data3[0];

                    $result3 = $this->cData->getExecExamineeCnt($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                    $data3 = $this->dba->getFetchArray($result3);
                    $examineeExecCnt = $data3[0];

                    $result3 = $this->cData->getNotExamineeCnt($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                    $data3 = $this->dba->getFetchArray($result3);
                    $examineeNotCnt = $data3[0];

                    $result3 = $this->cData->getConsentOkCnt($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                    $data3 = $this->dba->getFetchArray($result3);
                    $consentOkCnt = $data3[0];

                    $examineeDate = array();
                    $result3 = $this->cData->getExamineeDate($this->dba, $data['COMPANY_CD'], $data['BUMON_CD'], $procNendo);
                    $data3 = $this->dba->getFetchArray($result3);
                    $examineeDate[0] = $data3[0];
                    $examineeDate[1] = $data3[1];

                    $this->pdf_create_examinee_list($result2, $examineeCnt, $examineeNoCnt, $examineeExecCnt, $examineeNotCnt, $consentOkCnt, $examineeDate);
                }
            }
        }

        // 集団的分析結果
        if ($target[4] == "1")
        {
            $this->pdf_create_group($data, $resultList);
        }

        $this->pdf_output();

        // 印刷フラグ更新
        if ($target[0] == "1" && $target[1] == "1")
        {
            $this->cData->updateResultPrint($this->dba, $authId, $companyCd, $busyoCdList, $eDateS, $eDateE, $printSts, $chkStress, $procNendo);
        }
    }

    private function pdf_create_group($data, $resultList)
    {
    
    }

    private function pdf_init()
    {
        // Noticeエラー非表示
        //error_reporting(E_ALL & ~E_NOTICE);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        // 文字書き込み：明朝体
        $this->pdf->SetFont('ipaexm', '', 12);
        $this->pdf->SetTextColor(0, 0, 0);
    }
    
    private function pdf_create_person1($data, $resultList, $procNendo, $mode)
    {
        $this->pdf->AddPage();
        $tplidx = $this->pdf->ImportPage(1);
        $this->pdf->useTemplate($tplidx);

        $this->pdf->SetFont('ipaexm', '', 11);
        $this->pdf->SetTextColor(0,0,0);

        $margin = 3.7;
        $consentDate = $this->convGtJDateNendo($procNendo);
        $xs = 169.5 - 1.5;
        if ($mode == 1)
        {
            $xs = 167.8 - 1.5;
        }
        $this->pdf_text($consentDate, 12, $xs, $margin);

        $this->pdf->SetFont('ipaexm', '', 10);
        $margin = 3.3;
        $this->pdf_text($data['COMPANY_NAME'], 21.5, 19.5, $margin);
        $this->pdf_text($data['BUMON_NAME'], 28, 19.5, $margin);
        $this->pdf_text($data['NAME'] . ' (' . $data['KNAME'] . ')', 34.8, 19.5, $margin);

        $margin = 3.9;

        $jm = JudgeMessage::getInstance();
        $jm->init();

        $globalStr = $jm->getSectionValue('global');

        // ストレッサー
        $doctorStresser = $jm->getValue('stresser', $data['STRESS_STRESSER']);

        // ストレス反応
        $doctorStressReaction = $jm->getValue('stress_reaction', $data['STRESS_REACTION']);

        // ストレス状況
        $stressState = $data['STRESS_STATE'];
        $doctorStressState = $jm->getValue('stress', $this->stress_state_change($stressState));
        $doctorExplain1 = $jm->getValue('stress_info', $this->stress_state_change($stressState));
        //$doctorExplain2 = $jm->getValue('stress_info2', "1");
        $doctorExplain3 = $jm->getValue('stress_info3', $this->stress_state_change($stressState));
        $faceToFaceGuidance = $jm->getValue('face_to_face_guidance', 1);

        try {
            $dba = new DbAccessor();
            $dba->initialize();

            $result = $dba->getData('SELECT COUNT(*)
                         FROM DOCTOR_JUDGE
                         WHERE AUTH_INFO ="' . $data['AUTH_INFO'] . '"');

            $data2 = $this->dba->getFetchArray($result);
            $doctorJudgeCount = $data2[0];

            if ($doctorJudgeCount > 0)
            {
                $result = $dba->getData('SELECT DOCTOR_STRESS_STATE,
                                                        DOCTOR_EXPLAIN_1,
                                                        DOCTOR_STRESS_REACTION,
                                                        DOCTOR_EXPLAIN_2,
                                                        DOCTOR_STRESSER,
                                                        DOCTOR_EXPLAIN_3
                                                 FROM DOCTOR_JUDGE
                                                 WHERE AUTH_INFO ="' . $data['AUTH_INFO'] . '"');

                $data2 = $this->dba->getFetchArray($result);

                $doctorStressState = $data2['DOCTOR_STRESS_STATE'];
                $doctorExplain1 = $data2['DOCTOR_EXPLAIN_1'];
                $doctorStressReaction = $data2['DOCTOR_STRESS_REACTION'];
                //$doctorExplain2 = $data2['DOCTOR_EXPLAIN_2'];
                $doctorStresser = $data2['DOCTOR_STRESSER'];
                $doctorExplain3 = $data2['DOCTOR_EXPLAIN_3'];
            }

            $stressState2 = $data['STRESS_STATE'];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }

        $this->pdf->SetFont('ipaexm', '', 16);
        $this->pdf->SetTextColor(0,0,0);

        // 高ストレス者判定
        $isSressHigh = false;
        if ($stressState2 > 4)
        {
            $isSressHigh = true;
        }
        
        if ($isSressHigh)
        {
            $endPosRow = $this->pdf_text("高ストレス者に該当します。", 75.5, 73, 5.2);
        }
        else
        {
            $endPosRow = $this->pdf_text("高ストレス者に該当しません。", 75.5, 71, 5.2);
        }

        // 医師面談判定
        $isSressHigh = false;
        if ($stressState > 4)
        {
            $isSressHigh = true;
        }

        if ($isSressHigh)
        {
            $faceToFaceGuidance = $jm->getValue('face_to_face_guidance', 2);
            $endPosRow = $this->pdf_text($faceToFaceGuidance, $endPosRow + 15, 40, 5.2);
        }
        else
        {
            $faceToFaceGuidance = $jm->getValue('face_to_face_guidance', 1);
            $endPosRow = $this->pdf_text($faceToFaceGuidance, $endPosRow + 15, 27, 5.2);
        }
        

        $this->pdf->SetFont('ipaexm', '', 12);
        $this->pdf->SetTextColor(0,0,0);

        $rowHeight = 3.8;
        $rowWidth = 17;
/*
        $endPosRow = $this->pdf_text($globalStr["1"], $endPosRow + 10, $rowWidth, $margin);
        $endPosRow = $this->pdf_text("", $endPosRow + $rowHeight, $rowWidth, $margin);
        $this->pdf->SetFont('ipaexm', 'B', 12);
        $endPosRow = $this->pdf_text("【ストレス状況】", $endPosRow + $rowHeight, $rowWidth - 3, $margin);
        $this->pdf->SetFont('ipaexm', '', 12);
        $endPosRow = $this->pdf_text($doctorStressState, $endPosRow + $rowHeight + 2, $rowWidth, $margin);
        $endPosRow = $this->pdf_text("", $endPosRow + $rowHeight, $rowWidth, $margin);
        $this->pdf->SetFont('ipaexm', 'B', 12);
        $endPosRow = $this->pdf_text("【ストレスによっておこる心身の反応】", $endPosRow + $rowHeight, $rowWidth - 3, $margin);
        $this->pdf->SetFont('ipaexm', '', 12);
        $endPosRow = $this->pdf_text($doctorExplain1, $endPosRow + $rowHeight + 2, $rowWidth, $margin);
        $endPosRow = $this->pdf_text("", $endPosRow + $rowHeight, $rowWidth, $margin);
        $endPosRow = $this->pdf_text($doctorStressReaction, $endPosRow + $rowHeight, $rowWidth, $margin);
        $endPosRow = $this->pdf_text("", $endPosRow + $rowHeight, $rowWidth, $margin);
        $this->pdf->SetFont('ipaexm', 'B', 12);
        $endPosRow = $this->pdf_text("【ストレスの原因と考えられる因子】", $endPosRow + $rowHeight, $rowWidth - 3, $margin);
        $this->pdf->SetFont('ipaexm', '', 12);
        $endPosRow = $this->pdf_text($doctorStresser, $endPosRow + $rowHeight + 2, $rowWidth, $margin);
        $endPosRow = $this->pdf_text("", $endPosRow + $rowHeight, $rowWidth, $margin);
        $endPosRow = $this->pdf_text($doctorExplain3, $endPosRow + $rowHeight, $rowWidth, $margin);
*/
        // ==== 文章ブロックを結合 ====
        $block  = $globalStr["1"] . "<br><br>";

        // TCPDF のテキストタグ <b> はテキストモード MultiCell でも解釈される
        $block .= "<b>【ストレス状況】</b><br><br>";
        $block .= $doctorStressState . "<br><br>";

        $block .= "<b>【ストレスによっておこる心身の反応】</b><br><br>";
        $block .= $doctorExplain1 . "<br><br>";
        $block .= $doctorStressReaction . "<br><br>";

        $block .= "<b>【ストレスの原因と考えられる因子】</b><br><br>";
        $block .= $doctorStresser . "<br><br>";
        $block .= $doctorExplain3;

        // ==== 1回だけ描画 ====
        $endPosRow = $this->pdf_text($block, $endPosRow + 10, $rowWidth, $margin);
    }

    private function stress_state_change($stressState)
    {
        $ret = $stressState;

        switch ($stressState){
            case 5:
              $ret = 1;
              break;
            case 6:
              $ret = 2;
              break;
            case 7:
              $ret = 3;
              break;
            case 8:
              $ret = 4;
              break;
            default:
              $ret = $stressState;
        }

        return $ret;
    }

    private function pdf_create_person2($data, $resultList, $procNendo, $mode)
    {
        $aj = new AutoJudge();
        $ajRetA = $aj->getJudgeA($resultList[0], $data['SEX']);
        $ajRetB = $aj->getJudgeB($resultList[1], $data['SEX']);
        $ajRetC = $aj->getJudgeC($resultList[2], $data['SEX']);
        $ajRetD = $aj->getJudgeD($resultList[3], $data['SEX']);

        // 2ページ目
        $this->pdf->AddPage();
        $tplidx = $this->pdf->ImportPage(2);
        $this->pdf->useTemplate($tplidx);

        $this->pdf->SetFont('ipaexm', '', 11);
        $this->pdf->SetTextColor(0,0,0);
        $consentDate = $this->convGtJDateNendo($procNendo);
        $xs = 169.5;
        if ($mode == 1)
        {
            $xs = 167.8;
        }
        $this->pdf_text($consentDate, 12, $xs, 3.7);

        $this->pdf->SetFont('ipaexg', '', 12);
        $this->pdf_text($data['NAME'], 43.5, 23, 4);
        
        $this->pdf->SetFont('ipaexg', '', 20);
        $str = "○";

        // ストレスの原因と考えられる因子
        $y = 68.1;
        $pos = 1;
        foreach ($ajRetA as &$val)
        {
            if ($pos <= 5)
            {
                if ($val == 1)
                {
                    $this->pdf_text($str, $y, 171.3, 0);
                }
                else if ($val == 2)
                {
                    $this->pdf_text($str, $y, 153.5, 0);
                }
                else if ($val == 3)
                {
                    $this->pdf_text($str, $y, 136.3, 0);
                }
                else if ($val == 4)
                {
                    $this->pdf_text($str, $y, 119, 0);
                }
                else if ($val == 5)
                {
                    $this->pdf_text($str, $y, 101.6, 0);
                }
            }
            else
            {
                if ($val == 1)
                {
                    $this->pdf_text($str, $y, 101.6, 0);
                }
                else if ($val == 2)
                {
                    $this->pdf_text($str, $y, 119, 0);
                }
                else if ($val == 3)
                {
                    $this->pdf_text($str, $y, 136.3, 0);
                }
                else if ($val == 4)
                {
                    $this->pdf_text($str, $y, 153.5, 0);
                }
                else if ($val == 5)
                {
                    $this->pdf_text($str, $y, 171.3, 0);
                }
            }
            
            $y += 7.57;
            $pos++;
        }

        // ストレスによっておこる心身の反応
        $y = 146.2;
        $pos = 1;
        foreach ($ajRetB as &$val)
        {
            if ($pos == 1)
            {
                if ($val == 1)
                {
                    $this->pdf_text($str, $y, 101.6, 0);
                }
                else if ($val == 2)
                {
                    $this->pdf_text($str, $y, 119, 0);
                }
                else if ($val == 3)
                {
                    $this->pdf_text($str, $y, 136.3, 0);
                }
                else if ($val == 4)
                {
                    $this->pdf_text($str, $y, 153.5, 0);
                }
                else if ($val == 5)
                {
                    $this->pdf_text($str, $y, 171.3, 0);
                }
            }
            else
            {
                if ($val == 1)
                {
                    $this->pdf_text($str, $y, 171.3, 0);
                }
                else if ($val == 2)
                {
                    $this->pdf_text($str, $y, 153.5, 0);
                }
                else if ($val == 3)
                {
                    $this->pdf_text($str, $y, 136.3, 0);
                }
                else if ($val == 4)
                {
                    $this->pdf_text($str, $y, 119, 0);
                }
                else if ($val == 5)
                {
                    $this->pdf_text($str, $y, 101.6, 0);
                }
            }
            
            $y += 7.54;
            $pos++;
        }

        // ストレス反応に影響を与えるほかの因子１
        $y = 201.4;
        foreach ($ajRetC as &$val)
        {
            if ($val == 1)
            {
                $this->pdf_text($str, $y, 101.6, 0);
            }
            else if ($val == 2)
            {
                $this->pdf_text($str, $y, 119, 0);
            }
            else if ($val == 3)
            {
                $this->pdf_text($str, $y, 136.3, 0);
            }
            else if ($val == 4)
            {
                $this->pdf_text($str, $y, 153.5, 0);
            }
            else if ($val == 5)
            {
                $this->pdf_text($str, $y, 171.3, 0);
            }
            
            $y += 7.58;
        }

        // ストレス反応に影響を与えるほかの因子２
        foreach ($ajRetD as &$val)
        {
            if ($val == 1)
            {
                $this->pdf_text($str, $y, 101.6, 0);
            }
            else if ($val == 2)
            {
                $this->pdf_text($str, $y, 119, 0);
            }
            else if ($val == 3)
            {
                $this->pdf_text($str, $y, 136.3, 0);
            }
            else if ($val == 4)
            {
                $this->pdf_text($str, $y, 153.5, 0);
            }
            else if ($val == 5)
            {
                $this->pdf_text($str, $y, 171.3, 0);
            }
            
            $y += 7.58;
        }

        $ajA = new AutoJudgeA_high();
        $ajB = new AutoJudgeB_high();
        $ajC = new AutoJudgeC_high();
        $ajD = new AutoJudgeD_high();

        $ajRetEvaluationA =$ajA->JudgeTotal($resultList[0], $data['SEX']);
        $ajRetEvaluationB =$ajB->JudgeTotal($resultList[1], $data['SEX']);
        $ajRetEvaluationC =$ajC->JudgeTotal($resultList[2], $data['SEX']);
        $ajRetEvaluationD =$ajD->JudgeTotal($resultList[3], $data['SEX']);

        $ajRetEvaluationTotal = $ajRetEvaluationA + $ajRetEvaluationB + $ajRetEvaluationC + $ajRetEvaluationD;

        $this->pdf->SetFont('ipaexm', '', 12);
        $prtPos = 129;
        if ($ajRetEvaluationA < 10)
        {
            $prtPos = 129.8;
        }
        $this->pdf_text($ajRetEvaluationA, 250.6, $prtPos, 4);
        $this->pdf_text("9", 250.6, 156.5, 4);
        $maxTotal = "42";
        if ($data['SEX'] == 2)
        {
            $maxTotal = "43";
        }
        $this->pdf_text($maxTotal, 250.6, 173, 4);
        $prtPos = 129;
        if ($ajRetEvaluationB < 10)
        {
            $prtPos = 129.8;
        }
        $this->pdf_text($ajRetEvaluationB, 256.8, $prtPos, 4);
        $this->pdf_text("6", 256.8, 156.5, 4);
        $this->pdf_text("30", 256.8, 173, 4);
        $ajRetEvaluationCD = $ajRetEvaluationC + $ajRetEvaluationD;
        $prtPos = 129;
        if ($ajRetEvaluationCD < 10)
        {
            $prtPos = 129.8;
        }
        $this->pdf_text($ajRetEvaluationCD, 263, $prtPos, 4);
        $this->pdf_text("4", 263, 156.5, 4);
        $this->pdf_text("20", 263, 173, 4);
    }

    private function pdf_create_person3($data, $resultList, $procNendo, $mode)
    {
        $aj = new AutoJudge();

        $ajRetA = $aj->getJudgeA($resultList[0], $data['SEX']);
        $ajRetB = $aj->getJudgeB($resultList[1], $data['SEX']);
        $ajRetC = $aj->getJudgeC($resultList[2], $data['SEX']);
        $ajRetD = $aj->getJudgeD($resultList[3], $data['SEX']);

        // 3ページ目
        $this->pdf->AddPage();
        $tplidx = $this->pdf->ImportPage(3);
        $this->pdf->useTemplate($tplidx);

        $this->pdf->SetFont('ipaexm', '', 11);
        $this->pdf->SetTextColor(0,0,0);
        $consentDate = $this->convGtJDateNendo($procNendo);
        $xs = 169.5;
        if ($mode == 1)
        {
            $xs = 167.8;
        }
        $this->pdf_text($consentDate, 12, $xs, 3.7);

        $this->pdf->SetFont('ipaexg', '', 12);
        $this->pdf_text($data['NAME'], 43.5, 23, 4);

        $this->pdf->SetLineWidth(0.5);

        // ストレスの原因と考えられる因子
        $linePos = array();
        $linePos[0] = $this->getLinePos('LINE_A_1');
        $linePos[1] = $this->getLinePos('LINE_A_2');
        $linePos[2] = $this->getLinePos('LINE_A_3');
        $linePos[3] = $this->getLinePos('LINE_A_4');
        $linePos[4] = $this->getLinePos('LINE_A_5');
        $linePos[5] = $this->getLinePos('LINE_A_6');
        $linePos[6] = $this->getLinePos('LINE_A_7');
        $linePos[7] = $this->getLinePos('LINE_A_8');
        $linePos[8] = $this->getLinePos('LINE_A_9');

        $this->pdf->Line($linePos[0][$ajRetA[0]][0], $linePos[0][$ajRetA[0]][1], $linePos[1][$ajRetA[1]][0], $linePos[1][$ajRetA[1]][1]);
        $this->pdf->Line($linePos[1][$ajRetA[1]][0], $linePos[1][$ajRetA[1]][1], $linePos[2][$ajRetA[2]][0], $linePos[2][$ajRetA[2]][1]);
        $this->pdf->Line($linePos[2][$ajRetA[2]][0], $linePos[2][$ajRetA[2]][1], $linePos[3][$ajRetA[3]][0], $linePos[3][$ajRetA[3]][1]);
        $this->pdf->Line($linePos[3][$ajRetA[3]][0], $linePos[3][$ajRetA[3]][1], $linePos[4][$ajRetA[4]][0], $linePos[4][$ajRetA[4]][1]);
        $this->pdf->Line($linePos[4][$ajRetA[4]][0], $linePos[4][$ajRetA[4]][1], $linePos[5][$ajRetA[5]][0], $linePos[5][$ajRetA[5]][1]);
        $this->pdf->Line($linePos[5][$ajRetA[5]][0], $linePos[5][$ajRetA[5]][1], $linePos[6][$ajRetA[6]][0], $linePos[6][$ajRetA[6]][1]);
        $this->pdf->Line($linePos[6][$ajRetA[6]][0], $linePos[6][$ajRetA[6]][1], $linePos[7][$ajRetA[7]][0], $linePos[7][$ajRetA[7]][1]);
        $this->pdf->Line($linePos[7][$ajRetA[7]][0], $linePos[7][$ajRetA[7]][1], $linePos[8][$ajRetA[8]][0], $linePos[8][$ajRetA[8]][1]);
        $this->pdf->Line($linePos[8][$ajRetA[8]][0], $linePos[8][$ajRetA[8]][1], $linePos[0][$ajRetA[0]][0], $linePos[0][$ajRetA[0]][1]);

        // ストレスによっておこる心身の反応
        $linePos = array();
        $linePos[0] = $this->getLinePos('LINE_B_1');
        $linePos[1] = $this->getLinePos('LINE_B_2');
        $linePos[2] = $this->getLinePos('LINE_B_3');
        $linePos[3] = $this->getLinePos('LINE_B_4');
        $linePos[4] = $this->getLinePos('LINE_B_5');
        $linePos[5] = $this->getLinePos('LINE_B_6');

        $this->pdf->Line($linePos[0][$ajRetB[0]][0], $linePos[0][$ajRetB[0]][1], $linePos[1][$ajRetB[1]][0], $linePos[1][$ajRetB[1]][1]);
        $this->pdf->Line($linePos[1][$ajRetB[1]][0], $linePos[1][$ajRetB[1]][1], $linePos[2][$ajRetB[2]][0], $linePos[2][$ajRetB[2]][1]);
        $this->pdf->Line($linePos[2][$ajRetB[2]][0], $linePos[2][$ajRetB[2]][1], $linePos[3][$ajRetB[3]][0], $linePos[3][$ajRetB[3]][1]);
        $this->pdf->Line($linePos[3][$ajRetB[3]][0], $linePos[3][$ajRetB[3]][1], $linePos[4][$ajRetB[4]][0], $linePos[4][$ajRetB[4]][1]);
        $this->pdf->Line($linePos[4][$ajRetB[4]][0], $linePos[4][$ajRetB[4]][1], $linePos[5][$ajRetB[5]][0], $linePos[5][$ajRetB[5]][1]);
        $this->pdf->Line($linePos[5][$ajRetB[5]][0], $linePos[5][$ajRetB[5]][1], $linePos[0][$ajRetB[0]][0], $linePos[0][$ajRetB[0]][1]);

        // ストレスによっておこる心身の反応
        $linePos = array();
        $linePos[0] = $this->getLinePos('LINE_C_1');
        $linePos[1] = $this->getLinePos('LINE_C_2');
        $linePos[2] = $this->getLinePos('LINE_C_3');
        $linePos[3] = $this->getLinePos('LINE_D_1');

        $this->pdf->Line($linePos[0][$ajRetC[0]][0], $linePos[0][$ajRetC[0]][1], $linePos[1][$ajRetC[1]][0], $linePos[1][$ajRetC[1]][1]);
        $this->pdf->Line($linePos[1][$ajRetC[1]][0], $linePos[1][$ajRetC[1]][1], $linePos[2][$ajRetC[2]][0], $linePos[2][$ajRetC[2]][1]);
        $this->pdf->Line($linePos[2][$ajRetC[2]][0], $linePos[2][$ajRetC[2]][1], $linePos[3][$ajRetD[0]][0], $linePos[3][$ajRetD[0]][1]);
        $this->pdf->Line($linePos[3][$ajRetD[0]][0], $linePos[3][$ajRetD[0]][1], $linePos[0][$ajRetC[0]][0], $linePos[0][$ajRetC[0]][1]);
    }

    private function getLinePos($name)
    {
        $linePos = array();
    
        $ins = IniFile::getInstance();
        $ins->init();

        $lineX = $ins->getValue('pdfline', $name . '_X');
        $lineY = $ins->getValue('pdfline', $name . '_Y');
        $lineX = explode(",", $lineX);
        $lineY = explode(",", $lineY);

        for ($i = 1; $i <= count($lineX); $i++)
        {
            $linePos[$i][0] = $lineY[$i];
            $linePos[$i][1] = $lineX[$i];
        }
        
        return $linePos;
    }

    private function pdf_text($str, $row, $col, $margin)
    {
        // 1) IPAex 明朝の沈み補正
        $fontSize = (int)$this->pdf->getFontSizePt();
        $adjustTable = [
            10 => 1.30,
            11 => 1.80,
            12 => 2.00,
            16 => 2.40,
            20 => 2.80,
        ];
        $globalOffset = 3.25;

        $currentY = $row;
        if (isset($adjustTable[$fontSize])) {
            $currentY -= ($adjustTable[$fontSize] + $globalOffset);
        }

        // 2) HTML かどうか判定
        $isHtml = (strpos($str, '<') !== false);

        if ($isHtml) {
            // ==== HTMLブロックは writeHTMLCell ====
            $usableWidth = 160; // 実際の幅に合わせて調整

            $this->pdf->SetXY($col, $currentY);
            $this->pdf->setCellHeightRatio(0.9); // 収まりが悪ければここを微調整

            $this->pdf->writeHTMLCell(
                $usableWidth,
                0,
                $col,
                $currentY,
                $str,
                0,
                1,
                false,
                true,
                'L',
                true
            );

            return $this->pdf->GetY();
        }

        // ==== ここから下は従来のテキストモード ====
        $usableWidth = 160;
        $maxHeight   = 170;

        $h = $this->pdf->getStringHeight($usableWidth, $str);

        if ($h <= $maxHeight) {
            $this->pdf->SetRightMargin(210 - ($col + $usableWidth));
            $this->pdf->SetXY($col, $currentY);

            $beforeY = $this->pdf->GetY();
            $this->pdf->Write(0, $str);
            $afterY  = $this->pdf->GetY();

            return $row + ($afterY - $beforeY);
        }

        $this->pdf->setCellHeightRatio(0.90);
        $this->pdf->SetXY($col, $currentY);

        $this->pdf->MultiCell(
            $usableWidth,
            0,
            $str,
            0,
            'L',
            false,
            1,
            $col,
            $currentY,
            true,
            0,
            false,
            true,
            $maxHeight
        );

        return $this->pdf->GetY();
    }

    private function pdf_output()
    {
        $ins = IniFile::getInstance();
        $ins->init();
        $printMode = $ins->getValue('printmode', 'MODE');

        if ($printMode == "1")
        {
            $this->pdf->Output('ストレスチェック受診結果.pdf', "D");
        }
        else if ($printMode == "2")
        {
            $this->pdf->Output('ストレスチェック受診結果.pdf', "I");
        }

        // PDFクローズ
        $this->pdf->Close();
    }
    
    private function pdf_create_examinee_list($result, $examineeCnt, $examineeNoCnt, $examineeExecCnt, $examineeNotCnt, $consentOkCnt, $examineeDate)
    {
        $margin = 3.8;

        $titleFlg = FALSE;
        //$pageFirst = TRUE;
        $jysuinCnt = 0;
        $noJysuinCnt = 0;
        $consentCnt = 0;
        $dataCnt = 0;
        $hPos = 116.5;
        while ($data = $this->dba->getFetchArray($result))
        {
            $dataCnt++;
            if ($dataCnt == 1 || $dataCnt == 21)
            {
                $this->pdf->AddPage();
                $tplidx = $this->pdf->ImportPage(1);
                $this->pdf->useTemplate($tplidx);
                
                $this->pdf->SetFont('ipaexm', '', 11);
                $this->pdf->SetTextColor(0,0,0);
                
                if ($dataCnt == 21)
                {
                    $hPos = 116.5;
                    $dataCnt = 1;
                    $titleFlg = FALSE;
                }
            }

            if (!$titleFlg)
            {
                $this->pdf_text($data['COMPANY_NAME'], 32.7, 54.3, $margin);
                $this->pdf_text($data['ADDRESS'], 40.1, 60, $margin);
                $this->pdf_text($data['TEL'], 46.4, 60, $margin);
                $this->pdf_text($data['BUMON_NAME'], 57.9, 47, $margin);
                $this->pdf_text($data['BUSYO_ADDRESS'], 65.5, 60, $margin);
                $this->pdf_text($data['BUSYO_TEL'], 71.9, 60, $margin);

                $startDate = $this->convGtJDate(date('Y/m/d', strtotime($examineeDate[0])));
                $endDate = $this->convGtJDate(date('Y/m/d', strtotime($examineeDate[1])));
                $str = $startDate . '～ ' . $endDate;
                $this->pdf_text($str, 83.3, 47, 4.5);
                
                $this->pdf->SetFont('ipaexm', '', 9);
                $this->pdf->SetTextColor(0,0,0);
                
                // 対象者
                $examineeAllCnt = $this->dba->getCount($result);
                $xPos = $this->getXPos($examineeAllCnt, 48.7);
                $this->pdf_text($examineeAllCnt, 96.1, $xPos, $margin);
                // 受診者
                $xPos = $this->getXPos($examineeCnt, 70.9);
                $this->pdf_text($examineeCnt, 96.1, $xPos, $margin);
                // 未受診者
                $xPos = $this->getXPos($examineeNoCnt, 98.2);
                $this->pdf_text($examineeNoCnt, 96.1, $xPos, $margin);
                // 受診中
                $xPos = $this->getXPos($examineeExecCnt, 122.2);
                $this->pdf_text($examineeExecCnt, 96.1, $xPos, $margin);
                // 受診しない
                $xPos = $this->getXPos($examineeNotCnt, 151.5);
                $this->pdf_text($examineeNotCnt, 96.1, $xPos, $margin);
                // 同意者
                $xPos = $this->getXPos($consentOkCnt, 175.5);
                $this->pdf_text($consentOkCnt, 96.1, $xPos, $margin);
                
                $this->pdf->SetFont('ipaexm', '', 11);
                $this->pdf->SetTextColor(0,0,0);

                $titleFlg = TRUE;
            }

            $this->pdf_text($data['NAME'], $hPos, 33.4, $margin);
            
            $val = "男";
            if ($data['SEX'] != 1)
            {
                $val = "女";
            }
            $this->pdf_text($val, $hPos, 87.2, $margin);
            
            $bymd = floor((int)(date('Ymd') - date('Ymd', strtotime($data['BYMD']))) / 10000);
            $this->pdf_text($bymd, $hPos, 101.4, $margin);
            
            $str = date('Y/n/j', strtotime($data['BYMD']));
            $this->pdf_text($str, $hPos, 111.6, $margin);
            
            $val = "未受診";
            $xPos = 140.3;
            if ($data['IS_EXAMINE'] == 0)
            {
                $val = "受診しない";
                $xPos = 136.3;
            }
            else if ($data['EXAMINATION_STATUS_CD'] == "0001")
            {
                $val = "受診中";
                $xPos = 140.3;
            }
            else if (isset($data['CHECK_EDATE']) && $data['EXAMINATION_STATUS_CD'] == "0002")
            {
                $val = "○";
                $xPos = 143.8;
            }
            else
            {
                $val = "未受診";
                $xPos = 140.3;
            }
            $this->pdf_text($val, $hPos, $xPos, $margin);
            
            $val = "未";
            $xPos = 168.4;
            if ($data['IS_EXAMINE'] == 0)
            {
                $val = "－";
            }
            else if ($data['RESULT_OK_FLG'] == 1)
            {
                $val = "○";
            }
            else if ($data['RESULT_OK_FLG'] == 2)
            {
                $val = "同意しない";
                $xPos = 161.2;
            }
            else if ($data['RESULT_OK_FLG'] == 3)
            {
                $val = "再";
            }
            $this->pdf_text($val, $hPos, $xPos, $margin);

            $hPos += 7.96;
        }
    }

    private function getXPos($data, $xPos)
    {
        if (strlen($data) == 2)
        {
            $xPos -= 1.6;
        }
        else if (strlen($data) == 3)
        {
            $xPos -= 3.2;
        }
        else if (strlen($data) == 4)
        {
            $xPos -= 4.8;
        }
        else if (strlen($data) == 5)
        {
            $xPos -= 6.4;
        }
        
        return $xPos;
    }

    private function setFontSize($size)
    {
        $this->pdf->SetFont('ipaexm', '', $size);
        $this->pdf->SetTextColor(0,0,0);
    }

    private function pdf_create_result_consent($data)
    {
        $this->pdf->AddPage();
        $tplidx = $this->pdf->ImportPage(1);
        $this->pdf->useTemplate($tplidx);
        
        $this->pdf->SetFont('ipaexm', '', 12);
        $this->pdf->SetTextColor(0,0,0);

        $name = $data['NAME'] . '(' . $data['KNAME'] . ') 様';
        $this->pdf_text($name, 22, 15, 3.8);

        $consentDate = $this->convGtJDate(date("Y/m/d", time()));
        $this->pdf_text($consentDate, 22, 155, 4.5);

        $fontSize = 10;
        $marge    = 10;
        $x        = 175;
        $y        = 8;
        $height   = 9;
        $width    = 0.25;
        $angle    = 0;
        $code     = $data['EXAMINEE_AUTH_ID'] . ' p5';
        $type     = 'code39';
        $black    = '000000';
        Barcode::fpdf($this->pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);

        $x        = 70;
        $y        = 208;
        $code     = $data['EXAMINEE_AUTH_ID'] . '-1';
        Barcode::fpdf($this->pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);

        $x        = 142;
        $y        = 208;
        $code     = $data['EXAMINEE_AUTH_ID'] . '-2';
        Barcode::fpdf($this->pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);
    }

    private function convGtJDate($src)
    {
        list($year, $month, $day) = explode('/', $src);

        if (!@checkdate($month, $day, $year) || $year < 1869 || strlen($year) !== 4
                || strlen($month) > 2 || strlen($day) > 2) return false;

        try {
            $dba = new DbAccessor();
            $dba->initialize();

            // 
            $result = $dba->getData('SELECT NENGO_FROM,
                                     NENGO_STR
                                     FROM M_NENGO_INFO
                                     WHERE "' . $src . '" BETWEEN NENGO_FROM AND NENGO_TO'
                                   );

            $data = $this->dba->getFetchArray($result);

            $gengo = $data['NENGO_STR'];
            $nengoFromYear = date('Y', strtotime($data['NENGO_FROM']));
            $wayear = $year - ($nengoFromYear - 1);

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }

        switch ($wayear)
        {
            case 1:
                $wadate = $gengo.'元年 '.ltrim($month, "0").'月 '.ltrim($day, "0").'日';
                break;

            default:
                $wadate = $gengo.sprintf("%02d", $wayear).'年 '.ltrim($month, "0").'月 '.ltrim($day, "0").'日';
        }

        return $wadate;
    }

    private function convGtJDateNendo($year)
    {
        if ($year < 1869 || strlen($year) !== 4) return false;

        try {
            $dba = new DbAccessor();
            $dba->initialize();

            // 
            $result = $dba->getData('SELECT NENGO_FROM,
                                     NENGO_STR
                                     FROM M_NENGO_INFO
                                     WHERE DATE_FORMAT(NENGO_FROM, "%Y") <= "' . $year . '"
                                     ORDER BY NENGO_FROM DESC'
                                   );

            $data = $this->dba->getFetchArray($result);

            $gengo = $data['NENGO_STR'];
            $nengoFromYear = date('Y', strtotime($data['NENGO_FROM']));
            $wayear = $year - ($nengoFromYear - 1);

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }

        switch ($wayear)
        {
            case 1:
                $wadate = $gengo.'元年度';
                break;

            default:
                $wadate = $gengo.str_pad($wayear, 2, " ", STR_PAD_LEFT).'年度';
        }

        return $wadate;
    }
}
?>
