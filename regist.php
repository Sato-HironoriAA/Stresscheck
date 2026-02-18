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

?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css" rel="stylesheet" />
 <link rel="stylesheet" type="text/css" href="css/scheck_base.css">
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
 <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
 <title><?php echo $gblMsg['COMPANY'] . '  ' . $gblMsg['TITLE']; ?></title>
 <script type="text/javascript">
 <!--
  jQuery( function() {
     jQuery( '.calender' ) . datepicker( {
         showOn: "button",
         buttonImage: "js/calendar.png",
         buttonImageOnly: true,
         changeMonth: true,
         changeYear: true
     } );
  } );

  function fnRegist()
  {
     if(window.confirm("利用者登録を行います。\r\nよろしいですか？"))
     {
       location.href = "index.php";
     }
  }

  function fnCancel()
  {
   location.href = "index.php";
  }
 // -->
 </script>
</head>
<body bgcolor="#F5FFFA">
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
 <hr>
 <br>
 <form name="login" method="post">
  <font size="3">
  <center>
  <b>＜利用者登録＞</b>
  </center>
  <br>
  <center>
  利用者登録の手順は<a href="">こちら</a>
  </center>
  <br>
  <center>
  <table align="center">
<?php
    $disabled = "";
    if ($stop == 1)
    {
        $disabled = " disabled";
    }
?>
   <tr>
    <td width=80 nowrap>
     事業所名
    </td>
   </tr>
   <tr>
    <td width=150 nowrap>
    <input name="txt_no" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=23 value="サンプル事業所" disabled>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     受診者用ID&nbsp;<img src="img/required.png" valign="middle" width="25 height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=60 nowrap>
    <input name="txt_no" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=10 value="" <?php echo $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     氏名&nbsp;<img src="img/required.png" valign="middle" width="28" height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=170 nowrap>
    姓&nbsp;<input name="txt_name" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=8  placeholder="健診" value="" <?php echo $disabled; ?>>
    &nbsp;&nbsp;&nbsp;
    名&nbsp;<input name="txt_name" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=8  placeholder="太郎" value="" <?php echo $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     氏名（フリガナ）&nbsp;<img src="img/required.png" valign="middle" width="28" height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=170 nowrap>
     セイ&nbsp;<input name="txt_name" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=8 placeholder="ケンシン" value="" <?php echo $disabled; ?>>
     &nbsp;&nbsp;&nbsp;
     メイ&nbsp;<input name="txt_name" type="text" tabindex=1 style="ime-mode: disabled" autocomplete="off" size=8 placeholder="タロウ" value="" <?php echo $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     性別&nbsp;<img src="img/required.png" valign="middle" width="28" height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=150 nowrap>
    <input name="rdo_name" type="radio" value="0" checked>男
    <input name="rdo_name" type="radio" value="1">女
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     生年月日&nbsp;<img src="img/required.png" valign="middle" width="28" height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=150 nowrap>
<?php
    $downloadDateDisp = "";
    $disabled = "";
    $calender = 'class="calender"';
?>
    <input type="text" name="dt_seinen" maxlength="10" readonly size="10" placeholder="yyyy/mm/dd" value="<?php echo $seinenDateDisp; ?>" <?php echo $calender . ' ' . $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     パスワード&nbsp;<img src="img/required.png" valign="middle" width="25" height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     <input name="pw_password" type="password" tabindex=2 style="ime-mode: disabled" autocomplete="off" size=15 maxlength="8" <?php echo $disabled; ?>>
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     パスワード（確認）&nbsp;<img src="img/required.png" valign="middle" width="25" height="16" alt="">
    </td>
   </tr>
   <tr>
    <td width=80 nowrap>
     <input name="pw_password" type="password" tabindex=2 style="ime-mode: disabled" autocomplete="off" size=15 maxlength="8" <?php echo $disabled; ?>>
    </td>
   </tr>
<!--
   <tr>
    <td width=150 colspan=2 nowrap>
    <input type="checkbox" name="chk_dsp" value="0" <?php echo $disabled; ?>>パスワードを表示する。
    </td>
   </tr>
-->
  </table>
  <br>
<?php
    $disabledId = "nodisButton";
    $disabled = "";
    if ($stop == 1)
    {
        $disabledId = "disButton";
        $disabled = " disabled";
    }
?>
   <input type="button" id="<?php echo $disabledId; ?>" onclick="fnRegist();" value="登録" <?php echo $disabled; ?>>
   <input type="button" id="<?php echo $disabledId; ?>" onclick="fnCancel();" value="キャンセル" <?php echo $disabled; ?>>
  </center>
  </font>
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
