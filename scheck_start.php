<?php
    extract($_REQUEST);

    session_start();

    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');
    include_once('cls/AutoJudge.php');

    // 認証
    $errStr = "";
    if (strlen($_SESSION["PHP_AUTH_USER"]) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }
?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 <meta http-equiv="content-style-type" content="text/css" />
 <meta http-equiv="content-script-type" content="text/javascript" />
 <title>ストレスチェック</title>
 <link rel="stylesheet" href="css/appearance.css" type="text/css" />
 <link rel="stylesheet" type="text/css" href="css/scheck_base.css">
 <meta name="apple-mobile-web-app-capable" content="yes" />
 <meta name="viewport" content="maximum-scale=1" />
 <link rel="apple-touch-icon" href="img/apple-touch-icon.png" />
 <script type="text/javascript">
 <!--
  function fnStart()
  {
    var obj = document.scheck_start;

    obj.method = "post";
    obj.action = "scheck.php";
    obj.target = "frame2";
    obj.submit();
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }
?>

 -->
 </script>
</head>
<body>
 <form name="scheck_start" method="post" action="">
  <center>
  <table align="center" width="70%" border="0">
   <tr>
    <td>
     <font size="3"><p>受診時間は15分程度掛かります。（全５７問）<br /><br />
            受診を中断する場合は「中断」ボタンを押してください。<br />
            次回受診時には、受診済み項目を選択状態として表示します。<br /><br />
            すべての受診項目が完了した場合は、「送信」ボタンを押してください。<br /><br />
            いずれのボタンを押さずにブラウザを閉じた場合は、<br />
            受信内容の保存または受診完了となりませんのでご注意ください。<br /><br /><br />
            それでは、｢受診開始｣ボタンを押して受診を開始してください。</p></font>
    </td>
   </tr>
  </table>
  </center>
  <br>
  <table align="center" id="headerTable" class="layout" width="90%" border=0>
   <tr>
    <td align="center" nowrap>
     <input type="button" id="nodisButton" onclick="fnStart();" value="受診開始">
    </td>
   </tr>
  </table>
  <br>
 </form>
</body>
</html>
