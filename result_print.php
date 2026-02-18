<?php

    extract($_REQUEST);

    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/FunctionCheck.php');
    include_once('cls/Parameter.php');
    include_once('cls/ProcNendo.php');

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $pageName = $ins->getSectionValue('page_name');
    $dispMsg = $ins->getSectionValue('result_print');

    session_start();

    $errStr = "";
    $clsAuth = new FunctionCheck();
    $funcChk = $clsAuth->check("0006");
    // ログインなし
    if ($funcChk < 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }
    // 権限なし
    else if ($funcChk == 0)
    {
        $errStr = $errMsg['ERR_AUTH'];
    }

    // 処理年度を取得
    $clsProc = new ProcNendo();
    $procNendo = $clsProc->getProcNendo();
    if ($procNendo == -1)
    {
        $errStr = "年度が取得できません。ログインし直してください。";
    }

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = rawurldecode($pamObj->getParameter('auth_id'));
    $companyCd = $pamObj->getParameter('company_cd');
    $busyoCd = $pamObj->getParameter('busyo_cd');
    $busyoCdS = $pamObj->getParameter('busyo_cd_s');
    $busyoCdE = $pamObj->getParameter('busyo_cd_e');
    $editMode = $pamObj->getParameter('edit_mode');
    $printMode[0] = $pamObj->getParameter('chk_print_person');
    $printMode[1] = $pamObj->getParameter('chk_print_consent');
    $printMode[2] = $pamObj->getParameter('chk_print_examination');
    $printMode[3] = $pamObj->getParameter('chk_print_company');
    $printMode[4] = $pamObj->getParameter('chk_print_group');
    $goBack = $pamObj->getParameter('go_back');
    $companyNm = rawurldecode($pamObj->getParameter('company_nm'));
    $companyPtn = $pamObj->getParameter('company_ptn');
    $pageNo = $pamObj->getParameter('txt_page_no');
    $companyCdSort = $pamObj->getParameter('company_cd_sort');
    $companyNmSort = $pamObj->getParameter('company_nm_sort');
    $busyoCdSort = $pamObj->getParameter('busyo_cd_sort');
    $busyoNmSort = $pamObj->getParameter('busyo_nm_sort');
    $eDateS = $pamObj->getParameter('exa_date_s');
    $eDateE = $pamObj->getParameter('exa_date_e');
    $printSts = $pamObj->getParameter('print_sts');
    $chkStress = $pamObj->getParameterArray('chk_stress');

    $dataCount = 1;
    try {
        $dba = new DbAccessor();
        $dba->initialize();

        // 当年度を取得
        $nendo = $procNendo;

        $whereStr = ' WHERE T2.EXAMINATION_STATUS_CD = "0002" AND T2.EXAMINEE_DEL_FLG = 0 AND T2.IS_EXAMINE = 1 AND T2.EXAMINEE_NENDO = ' . $nendo;
        $whereStr = getWhereStr($whereStr, $authId, $companyCd, $busyoCd, $busyoCdS, $busyoCdE, $eDateS, $eDateE, $printSts, $chkStress);

        $result = $dba->getData('SELECT COUNT(*)
                                 FROM CHECK_RESULT_INFO T1
                                         INNER JOIN EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                 . $whereStr);

        $data = $dba->getFetchArray($result);

        $printPersonCnt = $data[0];

        $whereStr = $whereStr . ' AND RESULT_OK_FLG = 1';

        $result = $dba->getData('SELECT COUNT(*)
                                 FROM CHECK_RESULT_INFO T1
                                         INNER JOIN EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO'
                                 . $whereStr);

        $data = $dba->getFetchArray($result);

        $printCompanyCnt = $data[0];

        $whereStr = ' WHERE EXAMINEE_DEL_FLG = 0 AND EXAMINEE_NENDO = ' . $nendo;
        $whereStr = getWhereStr2($whereStr, $authId, $companyCd, $busyoCd, $busyoCdS, $busyoCdE);
    
        $result = $dba->getData('SELECT COUNT(*)
                                 FROM EXAMINEE_INFO'
                                 . $whereStr);
        
        $data = $dba->getFetchArray($result);
        
        $printExaminationCnt = $data[0];

        $dba->close();
    } catch (Exception $e) { 
      echo $e->getMessage();
    }

    function getWhereStr($whereStr, $authId, $companyCd, $busyoCd, $busyoCdS, $busyoCdE, $eDateS, $eDateE, $printSts, $chkStress)
    {
        if (strlen($authId) > 0)
        {
            $whereStr .= ' AND T1.AUTH_INFO ="' . $authId . '"';
        }
        if (strlen($companyCd) > 0)
        {
            $whereStr .= ' AND T2.COMPANY_CD = "' . $companyCd . '"';

            if (strlen($busyoCd) > 0)
            {
                $whereStr .= ' AND T2.BUMON_CD = "' . $busyoCd . '"';
            }
            else if (strlen($busyoCdS) > 0 && strlen($busyoCdE) > 0)
            {
                if ($busyoCdS > $busyoCdE)
                {
                    $whereStr .= ' AND T2.BUMON_CD BETWEEN "' . $busyoCdE . '" AND "' . $busyoCdS . '"';
                }
                else
                {
                    $whereStr .= ' AND T2.BUMON_CD BETWEEN "' . $busyoCdS . '" AND "' . $busyoCdE . '"';
                }
            }
        }
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

        return $whereStr;
    }

    function getWhereStr2($whereStr, $authId, $companyCd, $busyoCd, $busyoCdS, $busyoCdE)
    {
        if (strlen($authId) > 0)
        {
            $whereStr .= ' AND AUTH_INFO ="' . $authId . '"';
        }
        if (strlen($companyCd) > 0)
        {
            $whereStr .= ' AND COMPANY_CD = "' . $companyCd . '"';

            if (strlen($busyoCd) > 0)
            {
                $whereStr .= ' AND BUMON_CD = "' . $busyoCd . '"';
            }
            else if (strlen($busyoCdS) > 0 && strlen($busyoCdE) > 0)
            {
                if ($busyoCdS > $busyoCdE)
                {
                    $whereStr .= ' AND BUMON_CD BETWEEN "' . $busyoCdE . '" AND "' . $busyoCdS . '"';
                }
                else
                {
                    $whereStr .= ' AND BUMON_CD BETWEEN "' . $busyoCdS . '" AND "' . $busyoCdE . '"';
                }
            }
        }
        
        return $whereStr;
    }

?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <title>ストレスチェック受診システム</title>
 <link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css" rel="stylesheet" />
 <link rel="stylesheet" type="text/css" href="css/scheck_base.css">
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
 <script type="text/javascript">
 <!--
  jQuery( function() {
     jQuery( '#calender' ) . datepicker( {
         showOn: "button",
         buttonImage: "js/calendar.png",
         buttonImageOnly: true
     } );
  } );

  function fnPrint()
  {
    returnValue = 0;
    var obj = document.result_print;

    if ((obj.chk_print_person.checked && obj.chk_print_company.checked)
        || (obj.chk_print_consent.checked && obj.chk_print_company.checked))
    {
        alert("結果通知書(個人向け)と結果通知書(事業所向け)は\r\n同時に印刷できません。");
        return;
    }

    if (!obj.chk_print_person.checked
          && !obj.chk_print_consent.checked
            && !obj.chk_print_examination.checked
              && !obj.chk_print_company.checked)
    {
        alert("<?php echo $dispMsg['ERR_PRINT']; ?>");
        return;
    }

    if (obj.chk_print_examination.checked && obj.txt_print_examination.value > 1000)
    {
        alert("受診状況一覧表の対象人数が1000件を超えています。\r\n部署の範囲指定を見直してください。");
        return;
    }

    if (window.confirm("<?php echo $dispMsg['CFM_PRINT']; ?>"))
    {
      obj.edit_mode.value = "1";
<?php
if (strlen($authId) > 0 || strlen($busyoCd) > 0)
{
    //echo 'obj.go_back.value = "0";';
}
else
{
    echo 'obj.go_back.value = "1";';
}
?>
      obj.method = "post";
      obj.action = "result_print.php";
      obj.submit();
    }
  }

<?php
    if (strlen($authId) > 0 || strlen($busyoCd) > 0)
    {
        echo 'function fnCancel()';
        echo '{';
        echo '    returnValue = 0;';
        echo '    close();';
        echo '}';
    }
    else
    {
        echo 'function fnBack()';
        echo '{';
        echo '    var obj = document.result_print;';
        echo '    obj.method = "post";';
        echo '    obj.action = "result_print_company.php";';
        echo '    obj.submit();';
        echo '}';
    }
?>

<?php
    if (strlen($errStr) > 0)
    {
        echo 'alert("' . $errStr . '");';
        echo 'window.close();';
    }

    if (strlen($editMode) > 0)
    {
        $busyoCdList = "";
        if (strlen($busyoCd) > 0)
        {
            $busyoCdList = $busyoCd;
        }
        else if (strlen($busyoCdS) > 0 && strlen($busyoCdE) > 0)
        {
            $busyoCdList = $busyoCdS . ',' . $busyoCdE;
        }

        $printStress = "";
        $pos = 0;
        if (is_array($chkStress) && count($chkStress) > 0)
        {
            foreach ($chkStress as $val)
            {
                $printStress .= '&chk_stress[' . $pos . ']=' . $val;
                $pos++;
            }
        }

        echo 'window.open("pdf_create_result.php?auth_id=' . rawurlencode($authId) . '&print_person=' . $printMode[0] . '&print_consent=' . $printMode[1] . '&print_examination=' . $printMode[2] . '&print_company=' . $printMode[3] . '&print_group=' . $printMode[4] . '&company_cd=' . $companyCd . '&busyo_cd=' . $busyoCdList . '&exa_date_s=' . $eDateS . '&exa_date_e=' . $eDateE . '&print_sts=' . $printSts . $printStress . '", "", "dialogWidth=800px; dialogHeight=480px");';
        echo 'returnValue = 1;';
        echo 'window.opener.document.result_list.submit();';
        if (strlen($authId) > 0)
        {
            echo 'window.close();';
        }
    }
?>
 // -->
 </script>
</head>
<base target="_self">
<body>
 <table width="95%" class="m-0">
  <tr>
   <td width="20" nowrap>
    <img src="img/company_logo.gif" valign="middle" width="21" height="21" alt="">
   </td>
   <td nowrap>
    <b><?php echo $gblMsg['COMPANY']; ?></b>
   </td>
  </tr>
  <tr>
   <td colspan="2" nowrap>
    <hr color="#191970" width="100%" size="3">
   </td>
  </tr>
  <tr>
   <td class="ta-c" colspan="2" nowrap>
    <b><span class="color-1"><?php echo $gblMsg['TITLE_SUB']; ?></span><span class="color-2"><?php echo '&nbsp;&nbsp;&nbsp;' . $procNendo . '年度'; ?></span></b>
   </td>
  </tr>
  <tr>
   <td colspan="2" nowrap>
    <hr color="#191970" width="100%" size="3">
   </td>
  </tr>
  </table>
 <table id="tb1" class="layout m-0" width="95%">
  <tr>
   <td class="ta-s" nowrap>&nbsp;<b><span size="-1"><?php echo $pageName['RESSULT_PRINT']; ?></span></b>
   </td>
  </tr>
 </table>
 <hr>
 <form name="result_print" method="post" onsubmit="return false;">
  <table id="main" class="m-0" width="60%">
<?php
    $company = "";
    $bumon = "";
    $name = "";

    if (strlen($authId) > 0)
    {
        try { 
            $dba = new DbAccessor();
            $dba->initialize();

            $result = $dba->getData('SELECT T1.AUTH_INFO,
                                            T2.NAME,
                                            T2.COMPANY_CD,
                                            T3.COMPANY_NAME,
                                            T2.BUMON_CD,
                                            T4.BUMON_NAME,
                                            T1.RESULT_OK_FLG,
                                            T1.RESULT_PRINT_FLG,
                                            T1.STRESS_STATUS_CD,
                                            T5.STRESS_STATUS_STR
                                     FROM CHECK_RESULT_INFO T1 
                                             INNER JOIN EXAMINEE_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO
                                             INNER JOIN M_COMPANY T3 ON T2.COMPANY_CD = T3.COMPANY_CD
                                             INNER JOIN M_BUMON T4 ON T2.COMPANY_CD = T4.COMPANY_CD AND T2.BUMON_CD = T4.BUMON_CD
                                             INNER JOIN M_STRESS_STATUS T5 ON T1.STRESS_STATUS_CD = T5.STRESS_STATUS_CD
                                     WHERE T1.AUTH_INFO ="' . $authId . '"'
                                   );

            $data = $dba->getFetchArray($result);

            $company = $data['COMPANY_NAME'];
            $bumon = $data['BUMON_NAME'];
            $name = $data['NAME'];
            $printSts = "1";
            if ($data['RESULT_PRINT_FLG'])
            {
                $printSts = "2";
            }
            $stressDsip = $data['STRESS_STATUS_STR'];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
    else
    {
        if (strlen($companyCd) > 0)
        {
            try { 
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT COMPANY_NAME
                                         FROM M_COMPANY
                                         WHERE COMPANY_CD ="' . $companyCd . '"');

                $data = $dba->getFetchArray($result);

                $company = $data['COMPANY_NAME'];

                $dba->close();
            } catch (Exception $e) { 
              echo $e->getMessage();
            }
        }

        if (strlen($companyCd) > 0 && strlen($busyoCd) > 0)
        {
            try { 
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT BUMON_NAME
                                         FROM M_BUMON
                                         WHERE COMPANY_CD = "' . $companyCd . '" 
                                            AND BUMON_CD ="' . $busyoCd . '"');

                $data = $dba->getFetchArray($result);

                $bumon = $data['BUMON_NAME'];

                $dba->close();
            } catch (Exception $e) { 
              echo $e->getMessage();
            }
        }
    }

    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>' . $dispMsg['COL_COMPANY'] . '：</td>';
    echo ' <td nowrap>';
    echo '  <input type="text" size="50" value="' . $company . '" readonly>';
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>' . $dispMsg['COL_BUSYO'] . '：</td>';
    echo ' <td nowrap>';
    if (strlen($bumon) > 0)
    {
        echo '  <input type="text" size="50" value="' . $bumon . '" readonly>';
    }
    else
    {
        echo '  <input type="text" size="50" value="" disabled>';
    }
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>部署範囲：</td>';
    echo ' <td nowrap>';

    try {
        $dba = new DbAccessor();
        $dba->initialize();

        $result = $dba->getData('SELECT BUMON_NAME
                                 FROM M_BUMON
                                 WHERE COMPANY_CD = "' . $companyCd . '"
                                   AND BUMON_CD = "' . $busyoCdS . '"'
                               );

        $data = $dba->getFetchArray($result);
        $busyoNameS = $data['BUMON_NAME'];

        $result = $dba->getData('SELECT BUMON_NAME
                                 FROM M_BUMON
                                 WHERE COMPANY_CD = "' . $companyCd . '"
                                   AND BUMON_CD = "' . $busyoCdE . '"'
                               );

        $data = $dba->getFetchArray($result);
        $busyoNameE = $data['BUMON_NAME'];

        $dba->close();
    } catch (Exception $e) { 
      echo $e->getMessage();
    }

    if (strlen($busyoCdS) > 0)
    {
        echo '  <input type="text" name="" size="50" value="' . $busyoCdS . ':' . $busyoNameS . '" readonly>';
    }
    else
    {
        echo '  <input type="text" name="" size="50" value="" disabled>';
    }
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td></td>';
    echo ' <td class="ta-s">';
    echo '～';
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td></td>';
    echo ' <td nowrap>';
    if (strlen($busyoCdE) > 0)
    {
        echo '  <input type="text" name="" size="50" value="' . $busyoCdE . ':' . $busyoNameE . '" readonly>';
    }
    else
    {
        echo '  <input type="text" name="" size="50" value="" disabled>';
    }
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>受診完了日：</td>';
    echo ' <td nowrap>';
    if (strlen($eDateS) > 0 || strlen($eDateE) > 0 || strlen($printSts) > 0)
    {
        echo '  <input type="text" name="" size="10" value="' . $eDateS . '" readonly>';
        echo '&nbsp;～';
        echo '  <input type="text" name="" size="10" value="' . $eDateE . '" readonly>';
    }
    else
    {
        echo '  <input type="text" name="" size="10" value="" disabled>';
        echo '&nbsp;～';
        echo '  <input type="text" name="" size="10" value="" disabled>';
    }
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>結果通知印刷：</td>';
    echo ' <td nowrap>';
    if (strlen($printSts) > 0)
    {
        $value = "";
        if ($printSts == "1")
        {
            $value = "未";
        }
        else if ($printSts == "2")
        {
            $value = "済";
        }
        echo '  <input type="text" name="" size="3" value="' . $value . '" readonly>';
    }
    else
    {
        echo '  <input type="text" name="" size="3" value="" disabled>';
    }
    echo ' </td>';
    echo '</tr>';
    echo '<tr>';
    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>' . $dispMsg['COL_NM'] . '：</td>';
    if (strlen($name) > 0)
    {
        echo ' <td nowrap><input type="text" size="50" value="' . $name . '" readonly></td>';
    }
    else
    {
        echo ' <td nowrap><input type="text" size="50" value="" disabled></td>';
    }
    echo '</tr>';
    echo '<tr>';
    echo ' <td width="120" class="ta-e" nowrap>ストレス状態：</td>';
    if (strlen($authId) == 0)
    {
        $stressDsip = "";
        if (is_array($chkStress) && count($chkStress) > 0)
        {
            $stressStrArray = array();
            try {
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT STRESS_STATUS_CD,
                                                STRESS_STATUS_STR
                                         FROM M_STRESS_STATUS
                                         ORDER BY STRESS_STATUS_CD'
                                       );

                while ($data = $dba->getFetchArray($result))
                {
                    $stressStrArray[$data['STRESS_STATUS_CD']] = $data['STRESS_STATUS_STR'] . ', ';
                }

                $dba->close();
            } catch (Exception $e) { 
              echo $e->getMessage();
            }

            asort($chkStress);
            foreach ($chkStress as $val)
            {
                $stressDsip .= $stressStrArray[$val];
            }
        }
    }
    echo '<td nowrap>' . rtrim($stressDsip, ', ') .'</td>';
    echo '</tr>';
?>
   <tr><td height="30" valign="bottom">【個 人 向 け】</td></tr>
   <tr>
    <!--<td width="120" class="ta-e" nowrap><?php echo $dispMsg['COL_RESULT_PERSON']; ?>：</td>-->
    <td width="120" class="ta-e" nowrap>結果通知書：</td>
    <td nowrap>
<?php
    $checked = " checked";
    $disabled = "";
    if ($printPersonCnt == 0)
    {
        $checked = "";
        $disabled = " disabled";
    }
    if (strlen($editMode) > 0 && strlen($printMode[0]) == 0)
    {
        $checked = "";
    }
?>
     <input type="checkbox" name="chk_print_person" value="1" <?php echo $checked . ' ' . $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width="120" class="ta-e" nowrap><?php echo $dispMsg['COL_RESULT_CONSENT']; ?>：</td>
    <td nowrap>
<?php
    $checked = " checked";
    $disabled = "";
    if ($printPersonCnt == 0)
    {
        $checked = "";
        $disabled = " disabled";
    }
    if (strlen($editMode) > 0 && strlen($printMode[1]) == 0)
    {
        $checked = "";
    }
?>
     <input type="checkbox" name="chk_print_consent" value="1" <?php echo $checked . ' ' . $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width="120" class="ta-e" nowrap>印刷対象件数：</td>
    <td nowrap>
     <input type="text" value="<?php echo $printPersonCnt ?>" size="2" readonly>件
    </td>
   </tr>
   <tr><td height="30" valign="bottom">【事 業 所 向 け】</td></tr>
   <tr>
    <!--<td width="120" class="ta-e" nowrap><?php echo $dispMsg['COL_RESULT_COMPANY']; ?>：</td>-->
    <td width="120" class="ta-e" nowrap>結果通知書：</td>
    <td nowrap>
<?php
    $checked = "";
    $disabled = "";
    if ($printCompanyCnt == 0)
    {
        $checked = "";
        $disabled = " disabled";
    }
    if (strlen($editMode) > 0 && $printMode[3] == "1")
    {
        $checked = " checked";
    }
    echo '<input type="checkbox" name="chk_print_company" value="1"' . $disabled . $checked . '>';
    echo '<span color="red">※結果通知の同意がある受診者のみ印字します。</span>';
?>
    </td>
   </tr>
   <tr>
    <td width="120" class="ta-e" nowrap>印刷対象件数：</td>
    <td nowrap>
     <input type="text" value="<?php echo $printCompanyCnt ?>" size="2" readonly>件
    </td>
   </tr>
   <tr>
    <td width="120" class="ta-e" nowrap><?php echo $dispMsg['COL_EXAMINATION']; ?>：</td>
    <td nowrap>
<?php
    $checked = " checked";
    $disabled = "";
    if (strlen($authId) > 0)
    {
        $checked = "";
        $disabled = " disabled";
    }
    if (strlen($editMode) > 0 && strlen($printMode[2]) == 0)
    {
        $checked = "";
    }
?>
     <input type="checkbox" name="chk_print_examination" value="1" <?php echo $disabled . $checked; ?>>
    </td>
   </tr>
   <tr>
    <td width="120" class="ta-e" nowrap>印刷対象者数：</td>
    <td nowrap>
     <input type="text" name="txt_print_examination" value="<?php echo $printExaminationCnt ?>" size="2" readonly>人
    </td>
   </tr>
  </table>
  <br>
  <table id="main" class="m-0" width="40%">
   <tr>
    <td nowrap>
<?php
    $disabled = 'id="nodisButton"';
    if ($printCnt == 0)
    {
        $disabled = 'id="disButton" disabled';
    }
?>
     <!--<input type="button" <?php echo $disabled; ?> onclick="fnPrint('<?php echo $authId; ?>','<?php echo $companyCd; ?>','<?php echo $busyoCd; ?>');" value="<?php echo $dispMsg['BTN_PRINT']; ?>">-->
     <input type="button" id="nodisButton" onclick="fnPrint('<?php echo $authId; ?>','<?php echo $companyCd; ?>','<?php echo $busyoCd; ?>');" value="<?php echo $dispMsg['BTN_PRINT']; ?>">
    </td>
    <td nowrap>
<?php
    if (strlen($goBack) == 0)
    {
        echo '<input type="button" id="nodisButton" onclick="fnCancel();" value="キャンセル">';
    }
    else
    {
        echo '<input type="button" id="nodisButton" onclick="fnBack();" value="戻る">';
    }
?>
    </td>
   </tr>
  </table>
  <br>
  <div class="ta-c">
    <div id="footerFixed">
        <?php echo $gblMsg['COPYRIGHT']; ?>
    </div>
  </div>
  <input type="hidden" name="edit_mode" value="">
  <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
  <input type="hidden" name="company_cd" value="<?php echo $companyCd; ?>">
  <input type="hidden" name="busyo_cd_s" value="<?php echo $busyoCdS; ?>">
  <input type="hidden" name="busyo_cd_e" value="<?php echo $busyoCdE; ?>">
  <input type="hidden" name="exa_date_s" value="<?php echo $eDateS; ?>">
  <input type="hidden" name="exa_date_e" value="<?php echo $eDateE; ?>">
  <input type="hidden" name="print_sts" value="<?php echo $printSts; ?>">
<?php
    if (strlen($authId) == 0)
    {
        foreach ($chkStress as $val)
        {
            echo '<input type="hidden" name="chk_stress[]" value="' . $val . '">';
        }
    }
?>
  <input type="hidden" name="busyo_cd" value="<?php echo $busyoCd; ?>">
  <input type="hidden" name="company_nm" value="<?php echo rawurlencode($companyNm); ?>">
  <input type="hidden" name="company_ptn" value="<?php echo $companyPtn; ?>">
  <input type="hidden" name="company_cd_sort" value="<?php echo $companyCdSort; ?>">
  <input type="hidden" name="company_nm_sort" value="<?php echo $companyNmSort; ?>">
  <input type="hidden" name="busyo_cd_sort" value="<?php echo $busyoCdSort; ?>">
  <input type="hidden" name="busyo_nm_sort" value="<?php echo $busyoNmSort; ?>">
  <input type="hidden" name="txt_page_no" value="<?php echo $pageNo; ?>">
  <input type="hidden" name="go_back" value="">
 </form>
 <script type="text/javascript">
 <!--
<?php
    if (strlen($editMode) == 0 && strlen($goBack) > 0)
    {
        if ($printPersonCnt > 1000)
        {
            echo 'alert("印刷件数が1000件を超えています。\r\n部署の範囲指定を見直してください。");';
            echo 'fnBack();';
        }
        else if ($printCompanyCnt > 1000)
        {
            echo 'alert("印刷件数が1000件を超えています。\r\n部署の範囲指定を見直してください。");';
            echo 'fnBack();';
        }
    }
?>
 // -->
 </script>
</body>
</html>
