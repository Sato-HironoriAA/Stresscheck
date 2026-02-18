<?php

    extract($_REQUEST);

    include_once('cls/Parameter.php');

    session_start();

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $statusCd = $pamObj->getParameter('status_cd');
    $dispmode = $pamObj->getParameter('dispmode'); 

    // 認証
    $errStr = "";
    if (strlen($_SESSION["PHP_AUTH_USER"]) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }
?>
<!DOCTYPE html>
 <head>
  <meta http-equiv="content-type" content="text/html;charset=utf-8">
  <title>ストレスチェック</title>
 <link rel="stylesheet" type="text/css" href="css/scheck_base.css">
  <script type="text/javascript">
  <!--

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }
?>

  //-->
  </script>
 </head>
 <div style="width: 100%; height: 180px;">
  <iframe src="scheck_head.php" name="frame1" width="100%" style="height: 100%; border: 0;"></iframe>
 </div>
 <div style="width: 100%; height: calc( 100vh - 180px - 9px );">
<?php
    if ($statusCd != "0000")
    {
        if ($dispmode == "DOWNLOAD")
        {
            // 
            echo '<iframe src="scheck_download.php" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        } 
        else if ($dispmode == "DOCTOR")
        {
            // 
            echo '<iframe src="scheck_doctor_interview.php" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
        else if ($dispmode == "END")
        {
            // 
            echo '<iframe src="scheck_end.php?edit_mode=4" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
        else
        {
            // 
            echo '<iframe src="scheck.php" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
    }
    else
    {
        if ($dispmode == "DOWNLOAD")
        {
            // 
            echo '<iframe src="scheck_download.php" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
        else if ($dispmode == "DOCTOR")
        {
            // 
            echo '<iframe src="scheck_doctor_interview.php" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
        else if ($dispmode == "END")
        {
            // 
            echo '<iframe src="scheck_end.php?edit_mode=4" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
        else
        {
            // 
            echo '<iframe src="scheck_warning.php" name="frame2" width="100%" style="height: 100%; border: 0; border-top: solid 2px #333;"></iframe>';
        }
    }
?>
 </div>
</html>
