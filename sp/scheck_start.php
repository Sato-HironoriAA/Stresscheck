<?php
    extract($_REQUEST);

    include_once('cls/ScheckAuth.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');

    session_start();

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $dispMsg = $ins->getSectionValue('warning');

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = $pamObj->getParameter('auth_id');

    // 認証
    $errStr = "";
    if (strlen($authId) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
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

  function fnStart()
  {
    var obj = document.scheck_start;

    obj.method = "post";
    obj.action = "scheck.php";
    obj.submit();
  }

<?php
    if (strlen($errStr) > 0)
    {
        //echo 'alert("' . $errStr . '");';
        echo 'location.href = "index.php";';
    }
?>

  // -->
  </script>
 </head>
 <body>
 <form name="scheck_start">
   <table width="90%" align="center" border="0">
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
   <hr>
   <font size="5">
    <table width="90%" align="center" border="0">
     <tr>
      <td>
       <p>受診時間は15分程度掛かります。(全57問)
          </br>受診を中断する場合は「中断」ボタンを
          押してください。
          </br>次回受診時には、受診済み項目を選択状態
          として表示します。
          </br>また、すべての受診項目が完了した場合は、
          「送信」ボタンを押してください。
          </br>いずれのボタンを押さずにブラウザを
          閉じた場合は、受信内容の保存または
          受診完了となりませんのでご注意ください。
          </br></br>それでは、｢受診開始｣ボタンを押して
          受診を開始してください。</p>
      </td>
     </tr>
    </table>
   </font>
   <br>
   <table align="center" id="headerTable" class="layout" width="90%" border=0>
    <tr>
     <td align="center" nowrap>
      <input type="button" id="nodisButton" onclick="fnStart();" value="受診開始">
     </td>
    </tr>
   </table>
   <br>
   <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
  </form>
 </body>
</html>
