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
        header("Location:../../stresscheck-ie/sp/index.php");
        exit;
    }

    $statusCd = "0000";
    $errStr = "";
    if (strlen($editMode) > 0)
    {
        // 認証
        $clsAuth = new ScheckAuth();
        $authId = $clsAuth->authentication($userId, $password);

        if (strlen($authId) > 0)
        {
           // 受診期間が過ぎていないか確認
           try { 
                $dba = new DbAccessor();
                $dba->initialize();

                $result = $dba->getData('SELECT EXAMINEE_DATE_FROM,
                                                EXAMINEE_DATE_TO
                                         FROM EXAMINEE_INFO
                                         WHERE AUTH_INFO = "' . $authId . '"');

                $data = $dba->getFetchArray($result);

                if (strtotime($data['EXAMINEE_DATE_TO']) < strtotime(date("Y-m-d", time())))
                {
                    // 受診期間経過
                    $errStr = $errMsg['ERR_LOGIN_DATE'];
                }
                else if (strtotime($data['EXAMINEE_DATE_FROM']) > strtotime(date("Y-m-d", time())))
                {
                    // 受診期間前
                    $errStr = "受診期間前ためログインできません。";
                }
                else
                {
                    $result = $dba->getData('SELECT EXAMINATION_STATUS_CD
                                             FROM EXAMINEE_INFO
                                             WHERE AUTH_INFO ="' . $authId . '"'
                                           );

                    $data = $dba->getFetchArray($result);
                    $statusCd = $data['EXAMINATION_STATUS_CD'];
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
?>
<!DOCTYPE html>
 <html lang="ja">
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?php echo $gblMsg['COMPANY'] . '  ' . $gblMsg['TITLE']; ?></title>
  <link rel="stylesheet" href="css/scheck_base.css" type="text/css" />
  <script type="text/javascript">
  <!--
  function fnLogin()
  {
    var obj = document.login;

    obj.edit_mode.value = "1";
    obj.method = "post";
    obj.action = "index.php";

    obj.submit();
  }
  // -->
  </script>
 </head>
 <body>
 <form name="login">
   <table width="90%" align="center" border="0">
    <tr>
     <td align="center" nowrap>
      <font size="5"><b>
       <img src="img/company_logo.gif" width=25 height=25 alt="" style="vertical-align:-3px;">
       <?php echo $gblMsg['COMPANY']; ?>
      </b></font>
     </td>
    </tr>
    <tr>
     <td align="center" nowrap>
      <font color="#cc6699" size="5"><b>ストレスチェック</b></font>
     </td>
    </tr>
   </table>
   <hr>
  <table style="margin-left:auto;margin-right:auto;">
   <tr>
    <td align="left" width="300">
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
   <font size="5">
    <table width="90%" align="center" border="0">
     <tr>
      <td align="center">
       受診者用ID/パスワードを<br>入力して下さい。
      </td>
<?php
    $disabled = "";
/*
    if ($stop == 1)
    {
        $disabled = " disabled";
    }
*/
?>
     <tr>
      <td align="center">
       受診者用ID
       <input name="txt_userid" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=23 value="<?php echo $userId; ?>" <?php echo $disabled; ?>>
      </td>
     </tr>
     <tr>
      <td align="center">
       パスワード
       <input name="pw_password" type="password" tabindex=2 style="ime-mode: disabled" autocomplete="off" size=23 maxlength="8" value="<?php if (strlen($editMode) == 0) {echo $password;} ?>" <?php echo $disabled; ?>>
      </td>
     </tr>
     <tr>
      <td align="center">
       <font color="red" size="2"><?php echo $errStr ?></font>
      </td>
     </tr>
    </table>
   </font>
   <br>
   <table align="center" id="headerTable" class="layout" width="90%" border=0>
    <tr>
     <td align="center" nowrap>
<?php
    $disabledId = "nodisButton";
    $disabled = "";
/*
    if ($stop == 1)
    {
        $disabledId = "disButton";
        $disabled = " disabled";
    }
*/
?>
      <input type="button" id="<?php echo $disabledId; ?>" onclick="fnLogin();" value="ログイン" <?php echo $disabled; ?>>
     </td>
    </tr>
   </table>
   <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
   <input type="hidden" name="edit_mode" value="">
   <br>
   <table width="90%" border="0" cellpadding="2" cellspacing="0" title="このマークは、ウェブサイトを安心してご利用いただける安全の証です。">
    <tr>
     <td width="135" align="right" valign="top"></td>
     <td width="135" align="right" valign="top"><!-- DigiCert Seal HTML -->
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
  <script type="text/javascript">
  <!--

<?php
    if (strlen($editMode) > 0 && strlen($errStr) == 0)
    {
        echo 'var obj = document.login;';
        echo 'obj.method = "post";';
        if ($dispMode == "1")
        {
            if ($statusCd != "0000")
            {
                echo 'obj.action = "scheck.php";';
            }
            else
            {
                echo 'obj.action = "scheck_warning.php";';
            }
        }
        else
        {
            echo 'obj.action = "warning.php";';
        }
        echo 'obj.submit();';
    }
?>

  -->
  </script>
 </body>
</html>
