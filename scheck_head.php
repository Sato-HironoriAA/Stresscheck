<?php
    extract($_REQUEST);

    session_start();

    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $pageName = $ins->getSectionValue('page_name');
    $dispMsg = $ins->getSectionValue('scheck');

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = $_SESSION["PHP_AUTH_USER"];
    $editMode = $pamObj->getParameter('edit_mode');

    // 認証
    $errStr = "";
    if (strlen($_SESSION["PHP_AUTH_USER"]) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }

    if (strlen($errStr) == 0 && strlen($editMode) > 0)
    {
        
    }
?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <link rel=stylesheet type="text/css" href="css/scheck_base.css">
 <title></title>
 <script type="text/javascript">
 <!--
  function fnDownload()
  {
     if (window.confirm("ストレスチェック受診結果をダウンロードします。\r\nよろしいですか？"))
     {
<?php
        echo 'window.open("pdf_create_result.php?auth_id=' . rawurlencode($authId) . '&print_person=1&print_consent=&print_examination=&print_company=&print_group=&company_cd=&busyo_cd=&exa_date_s=&exa_date_e=&print_sts=", "_blank");';
        //echo 'window.close();';
?>
     }
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }
?>
 // -->
 </script>
</head>
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
   <td class="ta-c" colspan="2" nowrap>
    <span class="color-1 fs-32"><b>ストレスチェック</b></span>
   </td>
  </tr>
  </table>
<!--  <table id="tb1" class="layout" width="95%" align="center" border=0>
  <tr>
   <td align="right" nowrap>
    &nbsp;<font size="-1"><a href="index.php" target="_parent"><?php echo $gblMsg['LOGOFF']; ?></a></font>
   </td>
  </tr>
 </table>-->
 <form name="scheck_head" method="post" action="" target="_top" onsubmit="return false;">
  <div class="ta-c">
<?php
    if (strlen($authId) > 0)
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            $result = $dba->getData('SELECT T1.AUTH_INFO,
                                            T1.NAME,
                                            T1.KNAME,
                                            T1.SEX,
                                            T1.BYMD,
                                            T1.COMPANY_CD,
                                            T1.EXAMINEE_DATE_TO,
                                            T2.COMPANY_NAME,
                                            T1.BUMON_CD,
                                            T3.BUMON_NAME,
                                            T1.IS_EXAMINE,
                                            T1.EXAMINATION_STATUS_CD,
                                            T4.EXAMINATION_STATUS_STR,
                                            T4.FONT_COLOR,
                                            T5.RESULT_OK_FLG,
                                            T5.DOCTOR_INTERVIEW_OK_FLG,
                                            T6.RESULT_CONSENT_FLG,
                                            T6.RESULT_DOWNLOAD_FLG,
                                            T6.RESULT_DOWNLOAD_DATE,
                                            T6.DOCTOR_INTERVIEW_FLG
                                 FROM ((EXAMINEE_INFO T1 INNER JOIN
                                        M_COMPANY T2 ON
                                            T1.COMPANY_CD = T2.COMPANY_CD)
                                                INNER JOIN M_BUMON T3 ON T1.COMPANY_CD = T3.COMPANY_CD AND T1.BUMON_CD = T3.BUMON_CD)
                                                    INNER JOIN M_EXAMINATION_STATUS T4 ON T1.EXAMINATION_STATUS_CD = T4.EXAMINATION_STATUS_CD
                                                        INNER JOIN CHECK_RESULT_INFO T5 ON T1.AUTH_INFO = T5.AUTH_INFO
                                                            LEFT JOIN AUTH_INFO_J T6 ON T1.COMPANY_CD = T6.COMPANY_CD AND T1.EXAMINEE_NENDO = T6.AUTH_NENDO
                                 WHERE T1.AUTH_INFO ="' . $authId . '"
                                 ORDER BY T1.PERSON_NO, T1.COMPANY_CD, T1.BUMON_CD');

            $data = $dba->getFetchArray($result);

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
?>

  <table class="m-0" width="80%" style="border-collapse: collapse; border: 1px #1C79C6 solid;">
  <!--後でCSSに設定-->
   <tr class="bgcolor-2" style="font-size: 10pt; border: 1px solid #191970; padding: 1px;">
    <th width="100" style="border: 1px #1C79C6 solid;"><span>名前</span></th>
    <th width="100" style="border: 1px #1C79C6 solid;"><span>カナ氏名</span></th>
    <th width="60" style="border: 1px #1C79C6 solid;"><span>性別</span></th>
    <th width="90" style="border: 1px #1C79C6 solid;"><span>生年月日</span></th>
    <th width="150" style="border: 1px #1C79C6 solid;"><span>事業所名</span></th>
    <th width="100" style="border: 1px #1C79C6 solid;"><span>部署名</span></th>
    <th width="100" style="border: 1px #1C79C6 solid;"><span>受診意思</span></th>
    <th width="100" style="border: 1px #1C79C6 solid;"><span>受診状態</span></th>
<?php
    if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())) && isset($data['RESULT_DOWNLOAD_FLG']))
    {
        echo '<th width="100" style="border: 1px #1C79C6 solid;"><span>受診結果</span></th>';
        if (isset($data['RESULT_CONSENT_FLG']) && $data['RESULT_CONSENT_FLG'] == 1)
        {
            echo '<th width="60" style="border: 1px #1C79C6 solid;"><span>同意設定</span></th>';
        }
        if (isset($data['DOCTOR_INTERVIEW_FLG']) && $data['DOCTOR_INTERVIEW_FLG'] == 1)
        {
            echo '<th width="100" style="border: 1px #1C79C6 solid;"><span>医師面談受否</span></th>';
        }
    }
?>
   </tr>
   <tr>
<?php
    echo '<td width="60" align="left" style="border: 1px #1C79C6 solid;" nowrap>' . $data['NAME'] . '</td>';
    echo '<td width="60" align="left" style="border: 1px #1C79C6 solid;" nowrap>' . $data['KNAME'] . '</td>';
    $sex = '男';
    if ($data['SEX'] == 2)
    {
        $sex = '女';
    }
    echo '<td width="20" align="center" style="border: 1px #1C79C6 solid;" nowrap>' . $sex . '</td>';
    echo '<td width="40" align="center" style="border: 1px #1C79C6 solid;" nowrap>' . $data['BYMD'] . '</td>';
    echo '<td width="100" align="left" style="border: 1px #1C79C6 solid;" nowrap>' . $data['COMPANY_NAME'] . '</td>';
    echo '<td width="80" align="left" style="border: 1px #1C79C6 solid;" nowrap>' . $data['BUMON_NAME'] . '</td>';
    $isExamine = "";
    if (!$data['IS_EXAMINE'])
    {
        $isExamine = "しない";
    }
    echo '<td width="80" align="center" style="border: 1px #1C79C6 solid;" nowrap>' . $isExamine . '</td>';

    $status = "未受診";
    if ($data['EXAMINATION_STATUS_CD'] == "0001")
    {
        $status = "受診中";
    }
    else if ($data['EXAMINATION_STATUS_CD'] == "0002")
    {
        $status = "受診完了";
    }
    echo '<td width="80" align="center" style="border: 1px #1C79C6 solid;" nowrap>' . $status . '</td>';
    if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())) && isset($data['RESULT_DOWNLOAD_FLG']))
    {
        if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())) 
            && $data['EXAMINATION_STATUS_CD'] == "0002"
                && $data['RESULT_DOWNLOAD_FLG'] == 1 
                    && strtotime($data['RESULT_DOWNLOAD_DATE']) >= strtotime(date("Y-m-d", time())))
        {
            echo '<td width="100" align="center" style="border: 1px #1C79C6 solid;" nowrap><input type="button" id="nodisButton" onclick="fnDownload();" value="ダウンロード"></td>';
        }
        else
        {
            echo '<td width="100" align="center" style="border: 1px #1C79C6 solid;" nowrap><input type="button" id="disButton" value="ダウンロード" disabled></td>';
        }

        if (isset($data['RESULT_CONSENT_FLG']) && $data['RESULT_CONSENT_FLG'] == 1)
        {
            $val = "未設定";
            if ($data['RESULT_OK_FLG'] == 1)
            {
                $val = "同意する";
            }
            else if ($data['RESULT_OK_FLG'] == 2)
            {
                $val = "同意しない";
            }
            else if ($data['RESULT_OK_FLG'] == 3)
            {
                $val = "再";
            }
            echo '<td width="80" align="center" style="border: 1px #1C79C6 solid;" nowrap>' . $val . '</td>';
        }
        if (isset($data['DOCTOR_INTERVIEW_FLG']) && $data['DOCTOR_INTERVIEW_FLG'] == 1)
        {
            $val = "未設定";
            if ($data['DOCTOR_INTERVIEW_OK_FLG'] == 1)
            {
                $val = "受ける";
            }
            else if ($data['DOCTOR_INTERVIEW_OK_FLG'] == 2)
            {
                $val = "受けない";
            }
            echo '<td width="80" align="center" style="border: 1px #1C79C6 solid;" nowrap>' . $val . '</td>';
        }
    }
?>
   </tr>
  </table>
  <table class="m-0" width="80%" style="border-collapse: collapse; border: 0px #1C79C6 solid;">
   </tr>
   <tr>
    <td align="right">
     <font color="#FF0000"><b>※ダウンロードした受診結果ファイルのパスワードはログインパスワードと同一です。</b></font>
    </td>
   </tr>
  </table>
  </div>
  <input type="hidden" name="edit_mode" value="">
 </form>
</body>
</html>
