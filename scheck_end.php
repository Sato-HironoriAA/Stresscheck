<?php
    extract($_REQUEST);

    include_once('cls/Parameter.php');

    session_start();

    // 認証
    $errStr = "";
    if (strlen($_SESSION["PHP_AUTH_USER"]) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $editMode = $pamObj->getParameter('edit_mode');
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

  function fnEnd()
  {
    top.location.href = "http://kenko-akita.jp";
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
 <form name="scheck_end" method="post" onsubmit="return false;">
  <div id="contents">
   <div id="screen">
    <div id="header">
    </div>
    <div id="main">
     <div class="stage">
      <div class="card i1">
       <div class="cover">
        <div class="title"></div>
        <div class="lead">
<?php
    if ($editMode == "1")
    {
        echo '<p><img src="img/warning.png" valign="middle" width=36 height=34 alt=""><br />
              受診を中断しました。<br />
              一度、送信した方も、未受診（又は受診中）に戻されました。<br />
              （＊途中まで受診した内容は保存しています。）<br /><br />
              受診を完了する場合は、<br />
              再度ログインして、送信ボタンを押して受診を完了してください。</p>';
    }
    else if ($editMode == "2")
    {
        echo '<p>受診結果を送信しました。<br />受診案内は結果票を確認する際にも使用しますので<br /><span style="text-decoration:underline; text-decoration-thickness:3px; text-decoration-color:#FF0000;">受診終了後も保管してください。</span></p>';
    }
    else if ($editMode == "3")
    {
        echo '<p>受診結果（受診しない）を送信しました。<br />受診期間内であれば受診する事ができます。<br /></p>';
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
        </div>
        <div class="next" onclick="fnEnd();">終了する</div>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
 </form>
</body>
</html>
