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
    $rdoInterview = $pamObj->getParameter('rdoInterview');

    if (strlen($errStr) == 0 && strlen($editMode) > 0)
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            if ($editMode == "1")
            {
                $result = $dba->updateData('UPDATE CHECK_RESULT_INFO SET
                                                   DOCTOR_INTERVIEW_OK_FLG	="' . $rdoInterview . '"
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
                                        T2.DOCTOR_INTERVIEW_OK_FLG,
                                        T3.RESULT_DOWNLOAD_FLG,
                                        T3.RESULT_DOWNLOAD_DATE
                                 FROM EXAMINEE_INFO T1 INNER JOIN CHECK_RESULT_INFO T2 ON T1.AUTH_INFO = T2.AUTH_INFO
                                     INNER JOIN AUTH_INFO_J T3 ON T1.EXAMINEE_NENDO = T3.AUTH_NENDO AND T1.COMPANY_CD = T3.COMPANY_CD
                                 WHERE T1.AUTH_INFO ="' . $authId . '"');

        $data = $dba->getFetchArray($result);

        $examineeDateTo = $data['EXAMINEE_DATE_TO'];
        $interviewOk = $data['DOCTOR_INTERVIEW_OK_FLG'];
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
   function fnInterviewOK() {
     var obj = document.scheck_doctor_interview;

     if (obj.rdoInterview.value)
     {
       if (window.confirm("医師の面接指導についての受否を設定します。\r\nよろしいですか？"))
       {
         obj.edit_mode.value = "1";
         obj.method = "post";
         obj.action = "scheck_doctor_interview.php";
         obj.target = "frame2";
         obj.submit();
       }
     }
     else
     {
        alert("いずれかの受否設定を選択してください。");
     }
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }

    if (strlen($editMode) > 0)
    {
        echo 'alert("医師の面接指導についての受否設定を行いました。");';
        echo 'top.frame1.location.href="scheck_head.php";';
    }
?>
 // -->
 </script>
</head>
<body>
 <form name="scheck_doctor_interview" method="post" onsubmit="return false;">
  <center>
  <table align="center" width="70%" border="0">
   <tr>
    <td align="center">
     <font size="3">医師の面接指導についての受否設定<br /><br />
             ＜あなたのストレスの程度について＞<br />
             &nbsp;&nbsp;&nbsp;高ストレス者に該当します。<br /><br />
             ＜面接指導の要否について＞<br />
             医師の面接指導を受けていただくことをおすすめします。<br /><br />
             &nbsp;&nbsp;&nbsp;医師の面接指導を<br />
<?php
    $checkedOK = "";
    $checkedNG = "";
    if ($interviewOk == 1)
    {
        $checkedOK = " checked";
        $checkedNG = "";
    }
    else if ($interviewOk == 2)
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
             <input type="radio" value="1" name="rdoInterview" <?php echo $checkedOK . $disabled; ?>>受けます。 　 　（どちらかを選択） 　 　<input type="radio" value="2" name="rdoInterview" <?php echo $checkedNG . $disabled; ?>>受けません。<br /></p></font>
    </td>
   </tr>
  </table>
  <br>
  <table align="center" width="70%" border="0">
   <tr>
    <td align="center">
     <input type="button" onclick="fnInterviewOK();" value="設定" <?php  echo $disButton . $disabled; ?>>
    </td>
   </tr>
  </table>
  </center>
  <br>
  <input type="hidden" name="edit_mode" value="">
 </form>
</body>
</html>