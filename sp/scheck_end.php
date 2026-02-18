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
    $editMode = $pamObj->getParameter('edit_mode');

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

  function fnEnd()
  {
    location.href = "http://kenko-akita.jp";
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
 <form name="scheck_end">
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
<?php
    if ($editMode == "1")
    {
        echo '<p><img src="img/warning.png" valign="middle" width=36 height=34 alt=""><br />
             受診を中断しました。<br />
             一度、送信した方も、<br />
             未受診（又は受診中）に<br />
             戻されました。<br />
             ＊途中まで受診した内容は<br />
             　保存しています。<br /><br />
             受診を完了する場合は、<br />
             再度ログインして、<br />
             送信ボタンを押して受診を<br />
             完了してください。<br /></p>';
    }
    else if ($editMode == "2")
    {
        echo '<p>受診結果を送信しました。<br />受診案内は結果票を確認する際にも使用しますので<br /><span style="text-decoration:underline; text-decoration-thickness:3px; text-decoration-color:#FF0000;">受診終了後も保管してください。</span></p>';
    }
    else if ($editMode == "3")
    {
        echo '<p>受診結果（受診しない）を送信しました。<br />受診期間内であれば受診する事ができます。</p>';
    }
    else if ($editMode == "4")
    {
        echo '<p>ご回答いただいたストレス調査票の結果から、“あなたのストレスプロフィール”を作成しています。<br />
                 このプロフィールから、あなたのストレスの状態をおおよそ把握していただくことが出来ると思います。<br />
                 結果をごらんいただき、ご自分の心の健康管理にお役立てください。<br />
                 詳しいストレス度や、それに伴うこころの問題については、この結果のみで判断することはできません。<br />
                 ご心配な方は専門家にご相談下さい。</p>';
    }
?>
      </td>
     </tr>
    </table>
   <br>
   <table align="center" id="headerTable" class="layout" width="90%" border=0>
    <tr>
     <td align="center" nowrap>
      <input type="button" id="nodisButton" onclick="fnEnd();" value="終了する">
     </td>
    </tr>
   </table>
   </font>
   <br>
   <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
  </form>
 </body>
</html>
