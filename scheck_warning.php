<?php
    extract($_REQUEST);

    session_start();

    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $pageName = $ins->getSectionValue('page_name');
    $dispMsg = $ins->getSectionValue('check');

    // 認証
    $errStr = "";
    if (strlen($_SESSION["PHP_AUTH_USER"]) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $auth_id = $_SESSION["PHP_AUTH_USER"];
    $editMode = $pamObj->getParameter('edit_mode');

    if (strlen($editMode) > 0 && $editMode == "3")
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            // 受診者ID取得
            $result = $dba->getData('SELECT EXAMINEE_AUTH_ID
                                     FROM EXAMINEE_AUTH_INFO
                                     WHERE AUTH_INFO = "' . $auth_id . '"'
                                    );

            $data = $dba->getFetchArray($result);
            $userId = $data['EXAMINEE_AUTH_ID'];

            $result = $dba->updateData('UPDATE EXAMINEE_INFO SET
                                                EXAMINATION_STATUS_CD = "0002",
                                                IS_EXAMINE = 0,
                                                UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                UPDATE_ID = "' . $userId .'"
                                       WHERE AUTH_INFO = "' . $auth_id . '"'
                                      );

            $time = microtime();
            $time_list = explode(' ',$time);
            $time_micro = explode('.', $time_list[0]);

            $result = $dba->updateData('UPDATE CHECK_RESULT_INFO SET
                                                        CHECK_UDATE = "' . date("Y-m-d", time()) .'",
                                                        CHECK_EDATE = "' . date("Y-m-d H:i:s", time()) .'",
                                                        CHECK_EDATE_M = "' . substr($time_micro[1], 0, 3) .'",
                                                        UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                        UPDATE_ID = "' . $userId .'"
                                             WHERE AUTH_INFO = "' . $auth_id . '"'
                                            );

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }

    try {
        $dba = new DbAccessor();
        $dba->initialize();

        $result = $dba->getData('SELECT NO_EXAMINEE_FLG
                                 FROM EXAMINEE_INFO
                                 WHERE AUTH_INFO ="' . $auth_id . '"');

        $data = $dba->getFetchArray($result);

        $noExamine = $data['NO_EXAMINEE_FLG'];

        $dba->close();
    } catch (Exception $e) { 
      echo $e->getMessage();
    }
?>
<!DOCTYPE html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <link rel=stylesheet type="text/css" href="css/scheck_base.css">
 <link rel="stylesheet" href="css/appearance.css" type="text/css" />
 <title>ストレスチェック受診システム</title>
 <script type="text/javascript">
 <!--
  function fnCheck()
  {
     var obj = document.scheck_warning;

     obj.method = "post";
     obj.action = "scheck_start.php";
     obj.target = "frame2";
     obj.submit();
  }

  function fnNoCheck()
  {
     if(window.confirm("受診しないを送信します。\r\nよろしいですか？"))
     {
       var obj = document.scheck_warning;

       obj.edit_mode.value = "3";
       obj.method = "post";
       obj.action = "scheck_warning.php";
       obj.target = "_top";
       obj.submit();
     }
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }
    
    if (strlen($editMode) > 0)
    {
        echo 'top.location.href = "scheck_end.php?edit_mode=' . $editMode . '";';
    }
?>

 // -->
 </script>
</head>
<body bgcolor="#F9EFF1">
 <form name="scheck_warning">
  <center>
  <table align="center" width="70%" border="0">
   <tr height=20></tr>
   <tr>
    <td colspan="2" align="left">
      <font size="4"><b>ストレスチェック受診サイトご利用上の注意事項</b></font>
    </td>
   </tr>
  </table>
  <br>
  <table border="1" width="70%" align="center" cellspacing="0">
   <tr nowrap>
    <td colspan="2">
     <font size="3"><p>ストレスチェック受診Webサイト（以下、当サイト）をご利用される前に、<br>
     以下のご利用条件をお読み頂きますようお願いします。<br>
     当サイトの利用者は、以下のご利用条件に同意されたものといたします。</p></font>
    </td>
   </tr>
  </table>
  <br><br>
  <table border=0 width="70%" align="center">
   <tr>
    <td colspan="2" nowrap>
     <font size="3"><b>【１．入力情報の暗号化】</b></font>
    </td>
   </tr>
   <tr>
    <td nowrap>
     <font size="3"><p>当サイトは、暗号化（SSL:Secure Sockets Layer）を用いており、安全に受診できます。</p></font>
    </td>
   </tr>
   <tr style="height:15px;"></tr>
   <tr>
    <td colspan="2" nowrap>
     <font size="3"><b>【２．システム利用時間について】</b></font>
    </td>
   </tr>
   <tr nowrap>
    <td nowrap>
     <font size="3"><p>システムメンテナンスを除き、365日24時間利用できます。<br>
     システムメンテナンス時は、当サイトを利用できませんが、その場合は、事前にログイン画面へ表示し周知します。</p></font>
    </td>
   </tr>
   <tr style="height:15px;"></tr>
   <tr>
    <td colspan="2" nowrap>
     <font size="3"><b>【３．ストレスチェック受診について】</b></font>
    </td>
   </tr>
   <tr nowrap>
    <td nowrap>
     <font size="3"><p>当サイトでの受診は、受診案内文に記載された受診期間のみ利用可能となります。<br>
     受診期間を経過した場合は、ログインできませんので受診期間内の利用をお願いします。</p></font>
    </td>
   </tr>
   <tr style="height:15px;"></tr>
   <tr>
    <td colspan="2" nowrap>
     <font size="3"><b>【４．通信費について】</b></font>
    </td>
   </tr>
   <tr>
    <td nowrap>
     <font size="3"><p>当サイトの利用にあたっては、別途通信料がかかり、受診者さまのご負担となります。<br>
     通信定額プランへ加入していない場合や通信定額プランの対象外となる場合がありますので、<br>
     ご注意ください。<br>
     特に、従来の携帯電話からの利用の場合は、通信契約によって高額な通信料が発生する場合がありますので、<br>
     ご契約の電話事業会社等へ確認を行ってください。</p></font>
    </td>
   </tr>
   <tr style="height:15px;"></tr>
   <tr>
    <td colspan="2" nowrap>
     <font size="3"><b>【５．推奨環境】</b></font>
    </td>
   </tr>
   <tr nowrap>
    <td nowrap>
     <font size="3"><p>ＰＣサイトを表示できるフルブラウザによる利用をお願いします。</p></font>
    </td>
   </tr>
  </table>
  </center>
  <br>
  <table style="margin-left:auto;margin-right:auto;" id="headerTable" class="layout" width="400" border=0>
   <tr>
    <td align="center">
     <input type="button" id="nodisButton" onclick="fnCheck();" value="受診する">
    </td>
    <td align="center">
<?php
    if ($noExamine != 1)
    {
      echo '<input type="button" id="nodisButton" onclick="fnNoCheck();" value="受診しない">';
    }
?>
    </td>
   </tr>
  </table>
  <br>
  <input type="hidden" name="edit_mode" value="">
 </form>
</body>
</html>

