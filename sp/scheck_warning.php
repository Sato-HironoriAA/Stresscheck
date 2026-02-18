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

    if (strlen($editMode) > 0 && $editMode == "3")
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            // 受診者ID取得
            $result = $dba->getData('SELECT EXAMINEE_AUTH_ID
                                     FROM EXAMINEE_AUTH_INFO
                                     WHERE AUTH_INFO = "' . $authId . '"'
                                    );

            $data = $dba->getFetchArray($result);
            $userId = $data['EXAMINEE_AUTH_ID'];

            $result = $dba->updateData('UPDATE EXAMINEE_INFO SET
                                                EXAMINATION_STATUS_CD = "0002",
                                                IS_EXAMINE = 0,
                                                UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                UPDATE_ID = "' . $userId .'"
                                       WHERE AUTH_INFO = "' . $authId . '"'
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
  function fnCheck()
  {
    var obj = document.scheck_warning;

    obj.method = "post";
    obj.action = "scheck_start.php";
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
        //echo 'alert("' . $errStr . '");';
        echo 'location.href = "index.php";';
    }
?>

  // -->
  </script>
 </head>
 <body>
 <form name="scheck_warning">
   <table width="95%" align="center" border="0">
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
<!--
<?php
    if (strlen($authId) > 0)
    {
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            $result = $dba->getData('SELECT T1.AUTH_INFO,
                                            T1.NAME,
                                            T1.KNAME,
                                            T1.SEX,
                                            T1.BYMD,
                                            T1.COMPANY_CD,
                                            T2.COMPANY_NAME,
                                            T1.BUMON_CD,
                                            T3.BUMON_NAME,
                                            T1.EXAMINATION_STATUS_CD,
                                            T4.EXAMINATION_STATUS_STR,
                                            T4.FONT_COLOR,
                                            T1.NO_EXAMINEE_FLG
                                 FROM ((EXAMINEE_INFO T1 INNER JOIN
                                        M_COMPANY T2 ON
                                            T1.COMPANY_CD = T2.COMPANY_CD)
                                                INNER JOIN M_BUMON T3 ON T1.COMPANY_CD = T3.COMPANY_CD AND T1.BUMON_CD = T3.BUMON_CD)
                                                    INNER JOIN M_EXAMINATION_STATUS T4 ON T1.EXAMINATION_STATUS_CD = T4.EXAMINATION_STATUS_CD
                                 WHERE T1.AUTH_INFO ="' . $authId . '"
                                 ORDER BY T1.PERSON_NO, T1.COMPANY_CD, T1.BUMON_CD');

            $data = $dba->getFetchArray($result);

            $statusCd = $data['EXAMINATION_STATUS_CD'];
            $noExamine = $data['NO_EXAMINEE_FLG'];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
?>
   <font size="4">
   <table width="100%" align="center" border="1" cellspacing="0" cellpadding="0" style="margin-left:20px;">
    <tr>
     <td align="center" width="70" nowrap>
      名前
     </td>
     <td nowrap>
      <?php echo $data['NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="70" nowrap>
      名前カナ
     </td>
     <td nowrap>
      <?php echo $data['KNAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="70" nowrap>
      性別
     </td>
     <td nowrap>
<?php
    $sex = '男';
    if ($data['SEX'] == 2)
    {
        $sex = '女';
    }
?>
      <?php echo $sex; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="60" nowrap>
      生年月日
     </td>
     <td nowrap>
<?php
    $bYmd = date('Y/n/j', strtotime($data['BYMD']));
?>
      <?php echo $bYmd; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="70" nowrap>
      事業所名
     </td>
     <td nowrap>
      <?php echo $data['COMPANY_NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="70" nowrap>
      部署名
     </td>
     <td nowrap>
      <?php echo $data['BUMON_NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="70" nowrap>
      受診状態
     </td>
     <td nowrap>
      <?php echo $data['EXAMINATION_STATUS_STR']; ?>
     </td>
    </tr>
   </table>
   </font>
-->
   <hr width="100%" align="center" style="margin-left:20px;">
   <font size="5">
    <table width="90%" align="left" border="0" style="margin-left:20px; margin-right:20px;">
     <tr>
      <td>
       <p>ストレスチェック受診Webサイト（以下、当サイト）をご利用される前に以下のご利用条件をお読みください。</p>
      </td>
     </tr>
     <tr>
      <td>
       <p><b>【１．入力情報の暗号化】</b></p>
       <p>当サイトは、暗号化（SSL:Secure Sockets Layer）を用いており、安全に受診できます。</p>
      </td>
     </tr>
     <tr>
      <td>
       <p><b>【２．システム利用時間について】</b></p>
       <p>システムメンテナンスを除き、365日24時間利用できます。
          </br>システムメンテナンス時は当サイトを利用できませんが、その場合は事前にログイン画面へ表示し周知します。</p>
      </td>
     </tr>
     <tr>
      <td>
       <p><b>【３．ストレスチェック受診について】</b></p>
       <p>当サイトでの受診は、受診案内文に記載された受診期間のみ利用可能となります。
          </br>受診期間を経過した場合は、受診できませんので受診期間内の利用をお願いします。</p>
      </td>
     </tr>
     <tr>
      <td>
       </p><b>【４．通信費について】</b></p>
       <p>当サイトの利用にあたっては、別途通信料がかかり、受診者さまのご負担となります。
          </br>通信定額プランへ加入していない場合や通信定額プランの対象外となる場合がありますので、ご注意ください。
          </br>特に、従来の携帯電話からの利用の場合は、通信契約によって高額な通信料が発生する場合がありますので、ご契約の電話事業会社等へ確認を行ってください。</p>
      </td>
     </tr>
     <tr>
      <td>
       <p><b>【５．推奨環境】</b></p>
       <p>ＰＣサイトを表示できるフルブラウザによる利用をお願いします。</p>
      </td>
     </tr>
    </table>
   </font>
   <br><br>
   <table align="center" id="headerTable" class="layout" width="90%" border="0" style="margin-left:20px; margin-right:20px;">
    <tr>
     <td align="left" width="50%" nowrap>
      <input type="button" id="nodisButton" onclick="fnCheck();" value="受診する">
     </td>
     <td align="right" width="50%" nowrap>
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
   <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
   <input type="hidden" name="edit_mode" value="">
  </form>
  <script type="text/javascript">
  <!--

<?php
    if (strlen($editMode) > 0)
    {
        echo 'var obj = document.scheck_warning;';
        echo 'obj.edit_mode.value=' . $editMode . ';';
        echo 'obj.method = "post";';
        echo 'obj.action = "scheck_end.php";';
        echo 'obj.submit();';
    }
?>

  -->
  </script>
 </body>
</html>
