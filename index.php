<?php

    extract($_REQUEST);

    include_once('cls/DbAccessor.php');
    include_once('cls/ScheckAuth.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');
    include_once('cls/IniFile.php');

    $ins = IniFile::getInstance();
    $ins->init();
    $dispMode = $ins->getValue('scheckdisp', 'MODE');

    session_start();

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $dispMsg = $ins->getSectionValue('login');

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $editMode = $pamObj->getParameter('edit_mode');
    $userId = $pamObj->getParameter('txt_userid');
    $password = $pamObj->getParameter('pw_password');
    if (strlen($editMode) == 0)
    {
        $userId = $pamObj->getParameter('userid');
        $password = $pamObj->getParameter('password');
    }

    $ua = $_SERVER['HTTP_USER_AGENT'];
    if(strstr($ua, 'Trident') || strstr($ua, 'MSIE'))
    {
        header("Location:../stresscheck-ie/index.php");
        exit;
    }

    if ((strpos($ua, 'iPhone') != FALSE) ||
        (strpos($ua, 'iPod') != FALSE) ||
        (strpos($ua, 'iPad') != FALSE) ||
        (strpos($ua, 'Android') != FALSE) ||
        (strpos($ua, 'mobile') != FALSE) ||
        is_mobile())
    { 
        header("Location:./sp/index.php?userid=" . $userId . "&password=" . $password);
        exit;
    }

    $stop = 0;

    $errStr = "";
    if (strlen($editMode) > 0)
    {
        // 認証
        $clsAuth = new ScheckAuth();
        $authId = $clsAuth->authentication($userId, $password);

        // 認証成功
        if (strlen($authId) > 0)
        {
            // セッションに認証情報を格納
            $_SESSION["PHP_AUTH_USER"] = $authId;

            // セッションにシステム当年度を格納
            try {
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT NENDO FROM M_NENDO WHERE TONEN_FLG = 1');
                $data = $dba->getFetchArray($result);
                $_SESSION['proc_nendo']  = $data['NENDO'];

                $dba->close();
            } catch (Exception $e) { 
              echo $e->getMessage();
            }

           // 受診期間が過ぎていないか確認
           try { 
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT T1.EXAMINATION_STATUS_CD,
                                                T1.EXAMINEE_DATE_FROM,
                                                T1.EXAMINEE_DATE_TO,
                                                T2.STRESS_STATE,
                                                T3.RESULT_CONSENT_FLG,
                                                T3.DOCTOR_INTERVIEW_FLG,
                                                T3.RESULT_DOWNLOAD_DATE
                                         FROM EXAMINEE_INFO T1
                                              INNER JOIN CHECK_RESULT_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO 
                                              LEFT JOIN AUTH_INFO_J T3 ON T1.COMPANY_CD = T3.COMPANY_CD AND T1.EXAMINEE_NENDO = T3.AUTH_NENDO
                                         WHERE T1.AUTH_INFO = "' . $authId . '"');

                $data = $dba->getFetchArray($result);

                $examinationStatusCd = $data['EXAMINATION_STATUS_CD'];
                $doctorInterviewFlg = $data['DOCTOR_INTERVIEW_FLG'];
                $resultConsentFlg = $data['RESULT_CONSENT_FLG'];
                $stressState = $data['STRESS_STATE'];

                if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())))
                {
                    // 受診期間経過
                    $errStr = $errMsg['ERR_LOGIN_DATE'];

                    if(isset($resultConsentFlg) && strtotime($data['RESULT_DOWNLOAD_DATE']) >= strtotime(date("Y-m-d", time())))
                    {
                        if($examinationStatusCd == "0002")
                        {
                            // 同意設定有無確認
                            $dispmode="END";
                            if ($resultConsentFlg == 1)
                            {
                                $dispmode="DOWNLOAD";
                            }
                            else if ($doctorInterviewFlg == 1 && (isset($stressState) && intval($stressState) > 4))
                            {
                                $dispmode="DOCTOR";
                            }
                            // ダウンロードできるのはペーパーレス運用でかつ受診済みのものに限る
                            header("Location: scheck_main.php?status_cd=" . $examinationStatusCd . "&dispmode=" . $dispmode);
                        }
                    }
                }
                else if (strtotime($data['EXAMINEE_DATE_FROM']) > strtotime(date("Y-m-d", time())))
                {
                    // 受診期間前
                    $errStr = "受診期間前のためログインできません。";
                }
                else
                {
                    $result = $dba->getData('SELECT EXAMINATION_STATUS_CD
                                             FROM EXAMINEE_INFO
                                             WHERE AUTH_INFO ="' . $authId . '"'
                                           );

                    $data = $dba->getFetchArray($result);

                    // セッションに認証情報を格納
                    $_SESSION["PHP_AUTH_USER"] = $authId;

                    if ($dispMode == "1")
                    {
                        header("Location: scheck_main.php?status_cd=" . $data['EXAMINATION_STATUS_CD']);
                        exit;
                    }
                    else
                    {
                        // 注意事項同意画面へ遷移
                        header("Location: scheck_warning.php");
                        exit;
                    }
                }

                $dba->close();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        // 認証失敗
        else
        {
            $errStr = $errMsg['ERR_LOGIN'];
        }
    }
    else
    {
        if (isset($_SESSION))
        {
            $_SESSION = array();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-42000, '/');
            }
            session_destroy();
        }
    }

    function is_mobile()
    {
        $ua = array(
                'DoCoMo',
                'KDDI',
                'DDIPOKET',
                'UP.Browser',
                'J-PHONE',
                'Vodafone',
                'SoftBank',
                );

        foreach ($ua as $val) {
            $str = "/".$val."/i";
            if (preg_match($str, $_SERVER['HTTP_USER_AGENT'])){
                return true;
            }
        }
        return false;
    }
?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <link rel="stylesheet" type="text/css" href="css/scheck_base.css">
 <title><?php echo $gblMsg['COMPANY'] . '  ' . $gblMsg['TITLE']; ?></title>
 <script type="text/javascript">
 <!--

  function fnLogin()
  {
     var obj = document.login;

     obj.edit_mode.value = 1;
     obj.method = "post";
     obj.action = "index.php";
     obj.submit();
  }
  
  function fnRegist()
  {
     var obj = document.login;
     obj.method = "post";
     obj.action = "regist.php";
     obj.submit();
  }
 // -->
 </script>
</head>
<body bgcolor="#F5FFFA">
 <table width="95%" align="center" border="0">
  <tr>
   <td align="center" nowrap>
    <font size="6"><b>
     <img src="img/company_logo.gif" width=34 height=34 alt="" style="vertical-align:-4px;">
     <?php echo $gblMsg['COMPANY']; ?>
    </b></font>
   </td>
  </tr>
  <tr>
   <td align="center" nowrap>
    <font color="#cc6699" size="6"><b>ストレスチェック</b></font>
   </td>
  </tr>
 </table>
 <hr>
 <br>
 <form name="login" method="post">
  <table style="margin-left:auto;margin-right:auto;">
   <tr>
    <td>
<?php
    try {
        $dba = new DbAccessor();
        $dba->initialize();

        $result = $dba->getData('SELECT COUNT(*)
                                 FROM MAINTENANCE_INFO
                                 WHERE STR_TO_DATE(concat(STOP_DATE_FROM, " " , STOP_TIME_FROM, ":00:00"), "%Y-%m-%d %T")<= STR_TO_DATE("' . date("Y-m-d H:i:s", time()) . '", "%Y-%m-%d %T") 
                                     AND STR_TO_DATE(concat(STOP_DATE_TO, " " , STOP_TIME_TO, ":00:00"), "%Y-%m-%d %T") >= STR_TO_DATE("' . date("Y-m-d H:i:s", time()) . '", "%Y-%m-%d %T")'
                               );

        $data = $dba->getFetchArray($result);
        if ($data[0] > 0)
        {
            $stop = 1;
            echo '  <font size="4" color="red"><b>メンテナンス中のため、使用できません。</b></font>';
        }
        else
        {
            $result = $dba->getData('SELECT INFO_STR
                                     FROM MAINTENANCE_INFO
                                     WHERE "' . date("Y-m-d", time()) . '" BETWEEN INFO_FROM AND INFO_TO'
                                   );

            $data = $dba->getFetchArray($result);
            if (isset($data['INFO_STR']) && strlen($data['INFO_STR']) > 0)
            {
                echo '<font size="4" color="blue"><b>' . $data['INFO_STR'] . '</b></font>';
            }
        }

        $dba->close();
    } catch (Exception $e) { 
      echo $e->getMessage();
    }
?>
    </td>
   </tr>
  </table>
  <br>
  <font size="3">
  <center>
   受診者用ID/パスワードを入力して下さい。
  </center>
  <center>
  <table align="center">
   <tr>
    <td colspan="3" align="center" width="300" height="5">
    </td>
   </tr>
<?php
    $disabled = "";
    if ($stop == 1)
    {
        $disabled = " disabled";
    }
?>
   <tr>
    <td width=80 nowrap>
     <b>受診者用ID</b>
    </td>
    <td width=150 nowrap>
    <input name="txt_userid" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=23 value="<?php echo $userId ?>" <?php echo $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     <b><?php echo $dispMsg['MSG_PASSWD']; ?></b>
    </td>
    <td width=150 nowrap>
     <input name="pw_password" type="password" tabindex=2 style="ime-mode: disabled" autocomplete="off" size=23 maxlength="8" <?php echo $disabled; ?>>
    </td>
    <td valign="top">
    </td>
   </tr>
   <tr>
    <td colspan="2" align="center">
     <font color="red"><?php echo $errStr ?></font>
    </td>
   </tr>
  </table>
  </center>
  </font>
  <center>
<?php
    $disabledId = "nodisButton";
    $disabled = "";
    if ($stop == 1)
    {
        $disabledId = "disButton";
        $disabled = " disabled";
    }
?>
   <input type="button" id="<?php echo $disabledId; ?>" onclick="fnLogin();" value="<?php echo $dispMsg['BTN_LOGIN']; ?>" <?php echo $disabled; ?>>
<!--
   <br>
   <br>
   <br>
   利用者登録がお済でない方は<a href="regist.php">こちら</a>
   <br>
-->
<!--   <input type="button" id="<?php echo $disabledId; ?>" onclick="fnRegist();" value="利用者登録" <?php echo $disabled; ?>> -->
  </center>
  <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
  <input type="hidden" name="edit_mode" value="1">
  <br>
  <table width="90%" border="0" cellpadding="2" cellspacing="0" title="このマークは、ウェブサイトを安心してご利用いただける安全の証です。" border=1>
   <tr>
    <td width="135" align="right" valign="top"></td><td width="135" align="right" valign="top"><!-- DigiCert Seal HTML -->
<!-- Place HTML on your site where the seal should appear -->
<div id="DigiCertClickID_50kOY0BW"></div>

<!-- DigiCert Seal Code -->
<!-- Place with DigiCert Seal HTML or with other scripts -->
<script type="text/javascript">
	var __dcid = __dcid || [];
	__dcid.push({"cid":"DigiCertClickID_50kOY0BW","tag":"50kOY0BW","seal_format":"dynamic"});
	(function(){var cid=document.createElement("script");cid.async=true;cid.src="//seal.digicert.com/seals/cascade/seal.min.js";var s = document.getElementsByTagName("script");var ls = s[(s.length - 1)];ls.parentNode.insertBefore(cid, ls.nextSibling);}());
</script><br /></td>
   </tr>
  </table>
 </form>
</body>
</html>
