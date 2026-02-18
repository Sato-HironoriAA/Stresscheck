<?php
include_once('DbAccessor.php');

class ResultInfoPdfData
{
    public function __construct()
    {

    }

    function __destruct()
    {

    }

    /*
    // 部署リスト取得
    */
    public function getBusyoList($dba, $companyCd, $busyoCdList)
    {
        $whereStr = $this->createWhereStr("", 
                                          $companyCd, 
                                          $busyoCdList,
                                          "");

        try { 
            $result = $dba->getData('SELECT T1.COMPANY_CD,
                                            T1.COMPANY_NAME,
                                            T2.BUMON_CD,
                                            T2.BUMON_NAME
                                     FROM M_COMPANY T1
                                          INNER JOIN M_BUMON T2 ON T1.COMPANY_CD = T2.COMPANY_CD'
                                         . $whereStr .
                                         ' ORDER BY T1.COMPANY_CD, T2.BUMON_CD');

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診者情報取得(判定確定済み)
    */
    public function getExamineeList($dba, $authId, $companyCd, $busyoCdList, $eDateS, $eDateE, $printSts, $chkStress, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $whereStr = $this->createWhereStr($authId, 
                                               $companyCd, 
                                               $busyoCdList, 
                                               " WHERE T2.EXAMINEE_NENDO =" . $nendo . " 
                                                   AND T2.IS_EXAMINE = 1
                                                   AND (T1.JUDGE_FIXED = 1 OR T1.JUDGE_FIXED = 2)
                                                   AND T2.EXAMINATION_STATUS_CD = '0002' 
                                                   AND T2.EXAMINEE_DEL_FLG = 0");

            if (strlen($eDateS) > 0 && strlen($eDateE) > 0)
            {
                $whereStr .= ' AND T1.CHECK_EDATE BETWEEN "' . date('Y/n/j H:i:s', strtotime($eDateS . "00:00:00")) . '" AND "' . date('Y/n/j H:i:s', strtotime($eDateE . "23:59:59")) . '"';
            }
            else if (strlen($eDateS) > 0)
            {
                $whereStr .= ' AND T1.CHECK_EDATE >= "' . date('Y/n/j H:i:s', strtotime($eDateS . "00:00:00")) . '"';
            }
            else if (strlen($eDateE) > 0)
            {
                $whereStr .= ' AND T1.CHECK_EDATE <= "' . date('Y/n/j H:i:s', strtotime($eDateE . "23:59:59")) . '"';
            }
            if (strlen($printSts) > 0)
            {
                if ($printSts == "1")
                {
                    $whereStr .= ' AND T1.RESULT_PRINT_FLG = 0';
                }
                else if ($printSts == "2")
                {
                    $whereStr .= ' AND T1.RESULT_PRINT_FLG = 1';
                }
            }
            if (is_array($chkStress) && count($chkStress) > 0)
            {
                $whereStr .= ' AND (';
                foreach ($chkStress as $val)
                {
                    $whereStr .= ' T1.STRESS_STATUS_CD = "' . $val . '" OR';
                }
                $whereStr = rtrim($whereStr, ' OR');
                $whereStr .= ')';
            }

            $result = $dba->getData('SELECT T1.AUTH_INFO,
                                            T2.NAME,
                                            T2.KNAME,
                                            T2.SEX,
                                            T2.BYMD,
                                            T2.BYMD2,
                                            T2.PERSON_NO,
                                            T2.EXAMINEE_NENDO,
                                            T2.COMPANY_CD,
                                            T2.EXAMINATION_STATUS_CD,
                                            T2.IS_EXAMINE,
                                            T3.COMPANY_NAME,
                                            T3.ADDRESS,
                                            T3.TEL,
                                            T2.BUMON_CD,
                                            T4.BUMON_NAME,
                                            T1.STRESS_STRESSER,
                                            T1.STRESS_REACTION,
                                            T1.STRESS_STATE,
                                            T5.EXAMINEE_AUTH_ID,
                                            T1.RESULT_OK_FLG
                                     FROM (CHECK_RESULT_INFO T1 
                                             INNER JOIN EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO
                                             INNER JOIN M_COMPANY T3 ON T2.COMPANY_CD = T3.COMPANY_CD
                                             INNER JOIN M_BUMON T4 ON T2.COMPANY_CD = T4.COMPANY_CD AND T2.BUMON_CD = T4.BUMON_CD
                                             INNER JOIN EXAMINEE_AUTH_INFO T5 ON T1.AUTH_INFO = T5.AUTH_INFO)'
                                         . $whereStr .
                                         ' ORDER BY T2.COMPANY_CD, T2.BUMON_CD, T2.PERSON_NO');

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診者情報（印刷フラグ）更新(判定確定済み)
    */
    public function updateResultPrint($dba, $authId, $companyCd, $busyoCdList, $eDateS, $eDateE, $printSts, $chkStress, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $whereStr .= $this->createWhereStr($authId, 
                                               $companyCd, 
                                               $busyoCdList, 
                                               " WHERE T2.EXAMINEE_NENDO =" . $nendo . " 
                                                   AND T2.IS_EXAMINE = 1
                                                   AND (T1.JUDGE_FIXED = 1 OR T1.JUDGE_FIXED = 2)
                                                   AND T2.EXAMINATION_STATUS_CD = '0002' 
                                                   AND T2.EXAMINEE_DEL_FLG = 0");

            if (strlen($eDateS) > 0 && strlen($eDateE) > 0)
            {
                $whereStr .= ' AND T1.CHECK_EDATE BETWEEN "' . date('Y/n/j H:i:s', strtotime($eDateS . "00:00:00")) . '" AND "' . date('Y/n/j H:i:s', strtotime($eDateE . "23:59:59")) . '"';
            }
            else if (strlen($eDateS) > 0)
            {
                $whereStr .= ' AND T1.CHECK_EDATE >= "' . date('Y/n/j H:i:s', strtotime($eDateS . "00:00:00")) . '"';
            }
            else if (strlen($eDateE) > 0)
            {
                $whereStr .= ' AND T1.CHECK_EDATE <= "' . date('Y/n/j H:i:s', strtotime($eDateE . "23:59:59")) . '"';
            }
            if (strlen($printSts) > 0)
            {
                if ($printSts == "1")
                {
                    $whereStr .= ' AND T1.RESULT_PRINT_FLG = 0';
                }
                else if ($printSts == "2")
                {
                    $whereStr .= ' AND T1.RESULT_PRINT_FLG = 1';
                }
            }
            if (is_array($chkStress) && count($chkStress) > 0)
            {
                $whereStr .= ' AND (';
                foreach ($chkStress as $val)
                {
                    $whereStr .= ' T1.STRESS_STATUS_CD = "' . $val . '" OR';
                }
                $whereStr = rtrim($whereStr, ' OR');
                $whereStr .= ')';
            }

            $result = $dba->updateData('UPDATE CHECK_RESULT_INFO T1 
                                                INNER JOIN EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO
                                                SET T1.RESULT_PRINT_FLG = 1'
                                            . $whereStr
                                      );

        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診者情報取得(全登録者)
    */
    public function getExamineeList2($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("", 
                                              $companyCd, 
                                              $busyoCdList,
                                              " WHERE T2.EXAMINEE_NENDO =" . $nendo . " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT T1.AUTH_INFO,
                                            T2.NAME,
                                            T2.SEX,
                                            T2.BYMD,
                                            T2.COMPANY_CD,
                                            T2.EXAMINATION_STATUS_CD,
                                            T2.EXAMINEE_DATE_FROM,
                                            T2.EXAMINEE_DATE_TO,
                                            T2.IS_EXAMINE,
                                            T3.COMPANY_NAME,
                                            T3.ADDRESS,
                                            T3.TEL,
                                            T2.BUMON_CD,
                                            T4.BUMON_NAME,
                                            T4.ADDRESS AS BUSYO_ADDRESS,
                                            T4.TEL AS BUSYO_TEL,
                                            T1.RESULT_OK_FLG,
                                            T1.CHECK_EDATE
                                     FROM ((CHECK_RESULT_INFO T1 
                                             INNER JOIN EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO)
                                             INNER JOIN M_COMPANY T3 ON T2.COMPANY_CD = T3.COMPANY_CD)
                                             INNER JOIN M_BUMON T4 ON T2.COMPANY_CD = T4.COMPANY_CD AND T2.BUMON_CD = T4.BUMON_CD'
                                         . $whereStr .
                                         ' ORDER BY T2.COMPANY_CD, T2.BUMON_CD, T2.PERSON_NO');

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診済み者数取得
    */
    public function getExamineeCnt($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("", 
                                              $companyCd, 
                                              $busyoCdList,
                                              " WHERE T1.CHECK_EDATE IS NOT NULL
                                                  AND T2.EXAMINATION_STATUS_CD = '0002'
                                                  AND T2.IS_EXAMINE = 1
                                                  AND T2.EXAMINEE_NENDO =" . $nendo .
                                                 " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT COUNT(*)
                                     FROM CHECK_RESULT_INFO T1 INNER JOIN 
                                          EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                         . $whereStr
                                    );

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 未受診対象者数取得
    */
    public function getNoExamineeCnt($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("",
                                               $companyCd, 
                                               $busyoCdList,
                                               " WHERE T2.EXAMINATION_STATUS_CD = '0000'
                                                   AND T2.IS_EXAMINE = 1
                                                   AND T2.EXAMINEE_NENDO =" . $nendo .
                                                  " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT COUNT(*)
                                     FROM CHECK_RESULT_INFO T1 INNER JOIN 
                                          EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                         . $whereStr
                                    );

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診中対象者数取得
    */
    public function getExecExamineeCnt($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("",
                                               $companyCd, 
                                               $busyoCdList,
                                               " WHERE T2.EXAMINATION_STATUS_CD = '0001'
                                                   AND T2.IS_EXAMINE = 1
                                                   AND T2.EXAMINEE_NENDO =" . $nendo .
                                                  " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT COUNT(*)
                                     FROM CHECK_RESULT_INFO T1 INNER JOIN 
                                          EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                         . $whereStr
                                    );

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診しない対象者数取得
    */
    public function getNotExamineeCnt($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("",
                                               $companyCd, 
                                               $busyoCdList,
                                               " WHERE T2.IS_EXAMINE = 0
                                                   AND T2.EXAMINEE_NENDO =" . $nendo .
                                                  " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT COUNT(*)
                                     FROM CHECK_RESULT_INFO T1 INNER JOIN 
                                          EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                         . $whereStr
                                    );

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 結果通知同意者者数取得
    */
    public function getConsentOkCnt($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("",
                                               $companyCd, 
                                               $busyoCdList,
                                               " WHERE T1.RESULT_OK_FLG = 1
                                                   AND T2.EXAMINEE_NENDO =" . $nendo .
                                                  " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT COUNT(*)
                                     FROM CHECK_RESULT_INFO T1 INNER JOIN 
                                          EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                         . $whereStr
                                    );

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診期間取得
    */
    public function getExamineeDate($dba, $companyCd, $busyoCd, $procNendo)
    {
        try { 
            // 当年度を取得
            $nendo = $procNendo;

            $busyoCdList[0] = $busyoCd;

            $whereStr = $this->createWhereStr("",
                                               $companyCd, 
                                               $busyoCdList,
                                               " WHERE T2.EXAMINEE_NENDO = " . $nendo .
                                                  " AND T2.EXAMINEE_DEL_FLG = 0");

            $result = $dba->getData('SELECT MIN(T2.EXAMINEE_DATE_FROM) AS EXAMINEE_DATE_FROM,
                                            MAX(T2.EXAMINEE_DATE_TO) AS EXAMINEE_DATE_TO
                                     FROM CHECK_RESULT_INFO T1 INNER JOIN 
                                          EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                         . $whereStr
                                    );

            return $result;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    /*
    // 受診値取得
    */
    public function getResultData($dba, $authId)
    {
        $retResultList = array();
        
        try { 
            $result = $dba->getData('SELECT CHECK_RESULT_LOB
                                     FROM CHECK_RESULT
                                     WHERE AUTH_INFO = "' . $authId . '"'
                                   );

            $data = $dba->getFetchArray($result);

            $resultList = unserialize($data['CHECK_RESULT_LOB']);

            $keys = array_keys($resultList);

            $pos = 0;
            foreach ($keys as &$key)
            {
                $retResultList[$pos] = $resultList[$key];
                $pos++;
            }

            return $retResultList;
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    private function createWhereStr($authId, $companyCd, $busyoCdList, $whereStr)
    {
        $retWhereStr = $whereStr;
        if (strlen($authId) > 0)
        {
            if (strlen($retWhereStr) > 0)
            {
                $retWhereStr .= ' AND T1.AUTH_INFO ="' . $authId . '" ';
            }
            else
            {
                $retWhereStr .= ' WHERE T1.AUTH_INFO ="' . $authId . '" ';
            }
        }
        if (strlen($companyCd) > 0)
        {
            if (strlen($retWhereStr) > 0)
            {
                $retWhereStr .= ' AND T2.COMPANY_CD ="' . $companyCd . '" ';
            }
            else
            {
                $retWhereStr .= ' WHERE T2.COMPANY_CD ="' . $companyCd . '" ';
            }
        }
        if (isset($busyoCdList))
        {
            if (strlen($retWhereStr) > 0)
            {
                $retWhereStr .= ' AND ';
            }
            else
            {
                $retWhereStr .= ' WHERE ';
            }

            if (count($busyoCdList) == 1)
            {
                $retWhereStr .= ' T2.BUMON_CD ="' . $busyoCdList[0] . '" ';
            }
            else if (count($busyoCdList) == 2)
            {
                if ($busyoCdList[0] > $busyoCdList[1])
                {
                    $retWhereStr .= ' T2.BUMON_CD BETWEEN "' . $busyoCdList[1] . '" AND "' . $busyoCdList[0] . '"';
                }
                else
                {
                    $retWhereStr .= ' T2.BUMON_CD BETWEEN "' . $busyoCdList[0] . '" AND "' . $busyoCdList[1] . '"';
                }
            }
        }

        return $retWhereStr;
    }
}
?>
