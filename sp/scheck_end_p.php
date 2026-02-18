<?php
    extract($_REQUEST);

    include_once('cls/ScheckAuth.php');
    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');
    include_once('cls/AutoJudge.php');
    include_once('cls/IniFile.php');

    session_start();

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $pageName = $ins->getSectionValue('page_name');
    $dispMsg = $ins->getSectionValue('dscheck');

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = $pamObj->getParameter('auth_id');
//    $authId = $_SESSION["scheck_auth_id"];
    $editMode = $pamObj->getParameter('edit_mode');
    $rdoDocInterview = $pamObj->getParameter('rdoDocInterview');

    // 認証
    $errStr = "";
    if (strlen($authId) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }

    if (strlen($errStr) == 0 && strlen($editMode) > 0)
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            if ($editMode == "1")
            {
                $result = $dba->updateData('UPDATE CHECK_RESULT_INFO SET
                                                   DOCTOR_INTERVIEW_OK_FLG="' . $rdoDocInterview . '"
                                            WHERE AUTH_INFO="' . $authId . '"'
                                           );
            }

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
?>
<!DOCTYPE html>
 <html lang="ja">
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=0.7">
  <title><?php echo $gblMsg['COMPANY'] . '  ' . $gblMsg['TITLE']; ?></title>
  <link rel="stylesheet" href="css/scheck_base.css" type="text/css" />
  <style type="text/css">
    div#footerArea {  
        position: fixed !important;  
        position: absolute;
        bottom: 0;  
        left: 0;  
        width: 100%;  
        height: 90px;  
        background-color: #F9EFF1;  
        color: #fff;  
    }  
    div#contentsArea{  
        height: 100%;  
        overflow: auto;  
    }
    body {
      word-wrap:break-word;
    }

    .entry-content pre {
      overflow:scroll;
      word-wrap:normal;
    }
    table {
      word-break:break-all;
    }
  </style>
  <script type="text/javascript">
  <!--
  function fnDownload()
  {
     if (window.confirm("ストレスチェック受診結果をダウンロードします。\r\nよろしいですか？"))
     {
<?php
        echo 'window.open("pdf_create_result.php?auth_id=' . rawurlencode($authId) . '&print_person=1&print_consent=&print_examination=&print_company=&print_group=&company_cd=&busyo_cd=&exa_date_s=&exa_date_e=&print_sts=", "_blank");';
?>
     }
  }

  function fnEnd()
  {
    location.href = "http://kenko-akita.jp";
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'alert("' . $errStr . '");';
        echo 'location.href = "index.php";';
    }

    if (strlen($editMode) > 0)
    {
//        echo 'alert("医師面談の受否設定を行いました。");';
    }
?>

  // -->
  </script>
 </head>
 <body bgcolor="#F9EFF1">
  <form name="scheck_end_p" method="post" action="" target="_top" onsubmit="return false;">
   <!--<div id="headerArea">-->
   <table width="95%" align="center" border="0">
    <tr>
     <td width="20" nowrap>
      <img src="img/company_logo.gif" valign="middle" width=21 height=21 alt="">
     </td>
     <td nowrap>
      <b><?php echo $gblMsg['COMPANY']; ?></b>
     </td>
    </tr>
    <tr>
     <td align=center colspan="2" nowrap>
      <font color="#cc6699" size="5"><b>ストレスチェック</b></font>
     </td>
    </tr>
   </table>
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
                                            T2.COMPANY_NAME,
                                            T1.BUMON_CD,
                                            T3.BUMON_NAME,
                                            T1.IS_EXAMINE,
                                            T1.EXAMINATION_STATUS_CD,
                                            T1.EXAMINEE_DATE_TO,
                                            T4.EXAMINATION_STATUS_STR,
                                            T4.FONT_COLOR,
                                            T5.RESULT_OK_FLG,
                                            T5.DOCTOR_INTERVIEW_OK_FLG,
                                            T6.RESULT_CONSENT_FLG,
                                            T6.DOCTOR_INTERVIEW_FLG,
                                            T6.RESULT_DOWNLOAD_FLG,
                                            T6.RESULT_DOWNLOAD_DATE
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

            $statusCd = $data['EXAMINATION_STATUS_CD'];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
?>
   <font size="5">
   <div id="contentsArea">
   <table width="90%" align="center" border="1" cellspacing="0" cellpadding="0" style="margin-left:30px;">
    <tr>
     <td align="center" width="80" nowrap>
      名前
     </td>
     <td nowrap>
      <?php echo $data['NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      名前カナ
     </td>
     <td nowrap>
      <?php echo $data['KNAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      性別
     </td>
     <td nowrap>
<?php
    $sex = '男';
    if ($data['SEX'] == 2)
    {
        $sex = '女';
    }
?>
      <?php echo $sex; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      生年月日
     </td>
     <td nowrap>
<?php
    $bYmd = date('Y/n/j', strtotime($data['BYMD']));
?>
      <?php echo $bYmd; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      事業所名
     </td>
     <td nowrap>
      <?php echo $data['COMPANY_NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      部署名
     </td>
     <td nowrap>
      <?php echo $data['BUMON_NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      受診意思
     </td>
<?php
    $isExamine = "";
    if (!$data['IS_EXAMINE'])
    {
        $isExamine = "しない";
    }
?>
     <td nowrap>
      <?php echo $isExamine; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      受診状態
     </td>
     <td nowrap>
      <?php echo $data['EXAMINATION_STATUS_STR']; ?>
     </td>
    </tr>
<?php
    if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())) && isset($data['RESULT_DOWNLOAD_FLG']))
    {
        echo '<tr><td align="center" width="80" nowrap>受診結果</td>';
        if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())) 
            && $data['EXAMINATION_STATUS_CD'] == "0002"
                && $data['RESULT_DOWNLOAD_FLG'] == 1 
                    && strtotime($data['RESULT_DOWNLOAD_DATE']) >= strtotime(date("Y-m-d", time())))
        {
            echo '<td nowrap><input type="button" id="nodisButton" onclick="fnDownload();" value="ダウンロード"></td>';
        }
        else
        {
            echo '<td nowrap><input type="button" id="disButtonW" value="ダウンロード" disabled></td>';
        }
        if (isset($data['DOCTOR_INTERVIEW_FLG']) && $data['DOCTOR_INTERVIEW_FLG'] == 1)
        {
            echo '<tr><td align="center" width="80" nowrap>医師面談</td>';
            $val = "未設定";
            if ($data['DOCTOR_INTERVIEW_OK_FLG'] == 1)
            {
                $val = "受ける";
            }
            else if ($data['DOCTOR_INTERVIEW_OK_FLG'] == 2)
            {
                $val = "受けない";
            }
            echo '<td nowrap>' .$val . '</td></tr>';
        }
    }
?>
   </table>
   </font>
   <hr>
   <table align="center" width="90%" border="0">
    <tr>
     <td>
      <font size="4"><p>ご回答いただいたストレス調査票の結果から、“あなたのストレスプロフィール”を作成しています。<br />
                 このプロフィールから、あなたのストレスの状態をおおよそ把握していただくことが出来ると思います。<br />
                 結果をごらんいただき、ご自分の心の健康管理にお役立てください。<br />
                 詳しいストレス度や、それに伴うこころの問題については、この結果のみで判断することはできません。<br />
                 ご心配な方は専門家にご相談下さい。</p></font>
     </td>
    </tr>
   </table>
   <br>
   <hr>
   <div id="footerArea" class="disabled">
   <table align="center" id="headerTable" class="layout" width="95%" border=0 style="margin-left:50px;">
    <tr><td height="30"></td></tr>
    <tr>
     <td align="left" nowrap>
      <input type="button" id="nodisButton" onclick="fnEnd();" value="終了する">
     </td>
    </tr>
   </table>
   </div>
   <input type="hidden" name="edit_mode" value="">
   <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
  </form>
 </body>
</html>
