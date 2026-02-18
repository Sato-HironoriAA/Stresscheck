<?php
    extract($_REQUEST);

    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');
    include_once('cls/AutoJudge.php');
    include_once('cls/IniFile.php');

    session_start();

    // 認証
    $errStr = "";
    if (strlen($_SESSION["PHP_AUTH_USER"]) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = $_SESSION["PHP_AUTH_USER"];
    $editMode = $pamObj->getParameter('edit_mode');
    $rdoResult = $pamObj->getParameter('rdoResult');

    if (strlen($errStr) == 0 && strlen($editMode) > 0)
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            if ($editMode == "1")
            {
                $result = $dba->updateData('UPDATE CHECK_RESULT_INFO SET
                                                   RESULT_OK_FLG="' . $rdoResult . '"
                                            WHERE AUTH_INFO="' . $authId . '"'
                                           );
            }

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

   try { 
        $dba = new DbAccessor();
        $dba->initialize();

        $result = $dba->getData('SELECT T1.EXAMINEE_DATE_TO,
                                        T2.RESULT_OK_FLG,
                                        T3.RESULT_DOWNLOAD_FLG,
                                        T3.RESULT_DOWNLOAD_DATE
                                 FROM EXAMINEE_INFO T1 INNER JOIN CHECK_RESULT_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO
                                     INNER JOIN AUTH_INFO_J T3 ON T1.EXAMINEE_NENDO = T3.AUTH_NENDO AND T1.COMPANY_CD = T3.COMPANY_CD
                                 WHERE T1.AUTH_INFO ="' . $authId . '"');

        $data = $dba->getFetchArray($result);

        $examineeDateTo = $data['EXAMINEE_DATE_TO'];
        $resultOK = $data['RESULT_OK_FLG'];
        $resultDownloadFlg = $data['RESULT_DOWNLOAD_FLG'];
        $resultDownloadDate = $data['RESULT_DOWNLOAD_DATE'];

        $dba->close();
    } catch (Exception $e) { 
      echo $e->getMessage();
    }
?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <link rel=stylesheet type="text/css" href="css/scheck_base.css">
 <title><?php echo $gblMsg['TITLE']; ?></title>
 <script type="text/javascript">
 <!--
   function fnResultOK() {
     var obj = document.scheck_download;

     if (obj.rdoResult.value)
     {
       if (window.confirm("ストレスチェック受診結果提供についての同意を設定します。\r\nよろしいですか？"))
       {
         obj.edit_mode.value = "1";
         obj.method = "post";
         obj.action = "scheck_download.php";
         obj.target = "frame2";
         obj.submit();
       }
     }
     else
     {
        alert("いずれかの同意設定を選択してください。");
     }
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }

    if (strlen($editMode) > 0)
    {
        echo 'alert("同意設定を行いました。");';
        echo 'top.frame1.location.href="scheck_head.php";';
    }
?>
 // -->
 </script>
</head>
<body>
 <form name="scheck_download" method="post" onsubmit="return false;">
  <center>
  <table align="center" width="70%" border="0">
   <tr>
    <td>
     <font size="3"><p>ストレスチェック受診結果提供についての同意設定<br /><br />
            【同意について】<br />
             &nbsp;&nbsp;&nbsp;労働安全衛生法第６６条の１０の２では<br />
             「あらかじめ当該検査を受けた労働者の同意を得ないで、当該労働者の検査の結果を事業者に提供してはならない。」<br />
             とされております。<br />
             この同意書は、ストレスチェックを受診した方が、結果の内容を事業者へ提供するか否かの同意を確認するものです。<br />
             ストレスチェックの結果内容をご確認のうえ、事業者への提供を同意される場合は「同意します」<br />
             同意しない場合は「同意しません」のどちらかを選択し「設定」ボタンを押下してください。<br />
             <br />
             &nbsp;&nbsp;&nbsp;私は、ストレスチェック受診結果内容の提供について、<br />
             <br />
             １．秋田県総合保健事業団がストレスチェック受診結果内容を事業者へ提供すること<br />
             ２．事業者への提供により不都合が生じた場合は、秋田県総合保健事業団に一切の責任はないこと<br />
             <br />
             &nbsp;&nbsp;&nbsp;上記１及び２に<br />
             <br />
<?php
    $checkedOK = "";
    $checkedNG = "";
    if ($resultOK == 1)
    {
        $checkedOK = " checked";
        $checkedNG = "";
    }
    else if ($resultOK == 2)
    {
        $checkedOK = "";
        $checkedNG = " checked";
    }

    $disabled = " disabled";
    $disButton = ' id="disButton"';
    if (strtotime($examineeDateTo) < strtotime(date("Y-m-d", time())) && $resultDownloadFlg == 1)
    {
        $disabled = "";
        $disButton = ' id="nodisButton"';
    }
?>
             <input type="radio" value="1" name="rdoResult" <?php echo $checkedOK . $disabled; ?>>同意します。　 　 　 　（どちらかを選択）　 　 　<input type="radio" value="2" name="rdoResult" <?php echo $checkedNG . $disabled; ?>>同意しません。<br /></p></font>
    </td>
   </tr>
  </table>
  <br>
  <table align="center" width="70%" border="0">
   <tr>
    <td align="center">
     <input type="button" onclick="fnResultOK();" value="設定" <?php  echo $disButton . $disabled; ?>>
    </td>
   </tr>
  </table>
  </center>
  <br>
  <input type="hidden" name="edit_mode" value="">
 </form>
</body>
</html>