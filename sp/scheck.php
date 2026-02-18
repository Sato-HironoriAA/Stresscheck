<?php
    extract($_REQUEST);

    include_once('cls/ScheckAuth.php');
    include_once('cls/DbAccessor.php');
    include_once('cls/MessageInfo.php');
    include_once('cls/Parameter.php');
    include_once('cls/AutoJudge.php');
    include_once('cls/IniFile.php');

    $ins = MessageInfo::getInstance();
    $ins->init();
    $gblMsg = $ins->getSectionValue('global_message');
    $errMsg = $ins->getSectionValue('error_message');
    $pageName = $ins->getSectionValue('page_name');
    $dispMsg = $ins->getSectionValue('dscheck');

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = $pamObj->getParameter('auth_id');
    $editMode = $pamObj->getParameter('edit_mode');
    $isNoExamaine = $pamObj->getParameter('chk_no_examine');

    if (strlen($isNoExamaine) == 0)
    {
        $isNoExamaine = "1";
    }

    // 認証
    $errStr = "";
    if (strlen($authId) == 0)
    {
        $errStr = $errMsg['ERR_AUTH_LOGIN'];
    }

    if (strlen($errStr) == 0)
    {
        $ins = IniFile::getInstance();
        $ins->init();
        $docJudgeMode = $ins->getValue('doctorjudge', 'JUDGE');

        $name = "";
        $groupList = array();
        $checkList = array();
        $checkSelectList = array();
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

            // 受診しない非表示フラグ取得
            $result = $dba->getData('SELECT NO_EXAMINEE_FLG
                                     FROM EXAMINEE_INFO
                                     WHERE AUTH_INFO = "' . $auth_id . '"'
                                    );

            $data = $dba->getFetchArray($result);
            $noExamine = $data['NO_EXAMINEE_FLG'];

            // 受診項目グループリストを取得
            $result = $dba->getData('SELECT CHECK_ITEM_GROUP_CD,
                                            CHECK_ITEM_GROUP_STR
                                     FROM M_CHECK_ITEM_GROUP
                                     ORDER BY CHECK_ITEM_GROUP_CD');

            $pos = 0;
            while ($data = $dba->getFetchArray($result))
            {
                $groupList[$pos][0] = $data['CHECK_ITEM_GROUP_CD'];
                $groupList[$pos][1] = $data['CHECK_ITEM_GROUP_STR'];
                $pos++;
            }

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }

        if (strlen($authId) > 0 && strlen($editMode) > 0)
        {
            try {
                $dba = new DbAccessor();
                $dba->initialize();

                // 受診状況更新
                $editModeUp = "0001";
                if ($editMode == "2")
                {
                    $editModeUp = "0002";
                }

                $result = $dba->updateData('UPDATE EXAMINEE_INFO SET
                                                    EXAMINATION_STATUS_CD = "' . $editModeUp . '",
                                                    UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                    UPDATE_ID = "' . $userId .'"
                                           WHERE AUTH_INFO = "' . $authId . '"'
                                          );

                $result = $dba->updateData('UPDATE EXAMINEE_INFO SET
                                                IS_EXAMINE = ' . $isNoExamaine . ',
                                                UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                UPDATE_ID = "' . $userId .'"
                                       WHERE AUTH_INFO = "' . $authId . '"'
                                      );

                if ($isNoExamaine == "0")
                {

                    $time = microtime();
                    $time_list = explode(' ',$time);
                    $time_micro = explode('.', $time_list[0]);

                    $result = $dba->updateData('UPDATE CHECK_RESULT_INFO SET
                                                                CHECK_UDATE = "' . date("Y-m-d", time()) .'",
                                                                CHECK_EDATE = "' . date("Y-m-d H:i:s", time()) .'",
                                                                CHECK_EDATE_M = "' . substr($time_micro[1], 0, 3) .'",
                                                                UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                                UPDATE_ID = "' . $userId .'"
                                                     WHERE AUTH_INFO = "' . $authId . '"'
                                                    );

                    $result = $dba->updateData('UPDATE CHECK_RESULT SET
                                                       CHECK_RESULT_LOB = NULL,
                                                       UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                       UPDATE_ID = "' . $userId .'"
                                               WHERE AUTH_INFO = "' . $auth_id . '"'
                                              );

                    $result = $dba->getData('SELECT COUNT(*)
                                             FROM DOCTOR_JUDGE
                                             WHERE AUTH_INFO ="' . $auth_id . '"');

                    $data = $dba->getFetchArray($result);
                    if ($data[0] > 0)
                    {
                        $result = $dba->deleteData('DELETE FROM DOCTOR_JUDGE
                                                    WHERE AUTH_INFO = "' . $auth_id . '"');
                    }
                }
                else
                {
                    $resultList = array();
                    $resultA = array();
                    $resultB = array();
                    $resultC = array();
                    foreach ($groupList as &$group)
                    {
                        $result = $dba->getData('SELECT CHECK_ITEM_NO
                                                 FROM M_CHECK_ITEM
                                                 WHERE CHECK_ITEM_GROUP_CD = "' . $group[0] . '" AND
                                                       CHECK_ITEM_TITLE_FLG = 0
                                                 ORDER BY CHECK_ITEM_NO'
                                           );

                        while ($data = $dba->getFetchArray($result))
                        {
                            $name = 'rdo_' . $group[0] . '_' . $data['CHECK_ITEM_NO'];
                            $value = 0;

                            if (isset($_POST[$name]) && strlen($_POST[$name]) > 0)
                            {
                                $value = $_POST[$name];
                            }
                            $resultList[$group[0]][$data['CHECK_ITEM_NO']] = $value;
                        }
                        if ($group[1] == 'A')
                        {
                            $resultA = $resultList[$group[0]];
                        }
                        if ($group[1] == 'B')
                        {
                            $resultB = $resultList[$group[0]];
                        }
                        if ($group[1] == 'C')
                        {
                            $resultC = $resultList[$group[0]];
                        }
                    }

                    $result = $dba->updateData('UPDATE CHECK_RESULT SET
                                                        CHECK_RESULT_LOB = \'' . serialize($resultList) . '\',
                                                        UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                        UPDATE_ID = "' . $userId .'"
                                               WHERE AUTH_INFO = "' . $authId . '"'
                                              );

                    if ($editMode == "2")
                    {
                        $result = $dba->getData('SELECT COMPANY_CD,
                                                        SEX
                                                 FROM EXAMINEE_INFO
                                                 WHERE AUTH_INFO = "' . $authId . '"'
                                               );

                        $data = $dba->getFetchArray($result);
                        
                        $sex = $data['SEX'];

                        // ストレス状況
                        $aj = new AutoJudge();
                        $stress = $aj->getJudge($resultA, $resultB, $resultC, $sex, $data['COMPANY_CD']);
                        $stressStatus = sprintf("%04d", $stress);

                        // ストレッサー
                        $stresser = $aj->getJudgeAStresser($resultA, $sex);
                        
                        // ストレス反応
                        $reaction = $aj->getJudgeBReaction($resultB, $sex);

                        $docJudge = "";
                        if (strlen($docJudgeMode) > 0 && $docJudgeMode == "0")
                        {
                            $docJudge = " JUDGE_FIXED = 1,";
                        }

                        $time = microtime();
                        $time_list = explode(' ',$time);
                        $time_micro = explode('.', $time_list[0]);

                        $result = $dba->updateData('UPDATE CHECK_RESULT_INFO SET'
                                                            . $docJudge . '
                                                            STRESS_STATUS_CD = \'' . $stressStatus . '\',
                                                            STRESS_STATE = \'' . $stress . '\',
                                                            STRESS_STRESSER = \'' . $stresser . '\',
                                                            STRESS_REACTION = \'' . $reaction . '\',
                                                            CHECK_UDATE = "' . date("Y-m-d", time()) .'",
                                                            CHECK_EDATE = "' . date("Y-m-d H:i:s", time()) .'",
                                                            CHECK_EDATE_M = "' . substr($time_micro[1], 0, 3) .'",
                                                            UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                            UPDATE_ID = "' . $userId .'"
                                                 WHERE AUTH_INFO = "' . $authId . '"'
                                                );
                    }
                }

                $dba->close();
            } catch (Exception $e) { 
              echo $e->getMessage();
            }
        }

        $noData = TRUE;
        try {
            $dba = new DbAccessor();
            $dba->initialize();

            // 前回受診値を取得
            $result = $dba->getData('SELECT CHECK_RESULT_LOB
                                     FROM CHECK_RESULT
                                     WHERE AUTH_INFO = "' . $authId . '"'
                                    );

            if ($dba->getCount($result) > 0)
            {
                $data = $dba->getFetchArray($result);

                if (!is_null($data['CHECK_RESULT_LOB']))
                {
                    $noData = FALSE;
                    $resultList = unserialize($data['CHECK_RESULT_LOB']);
                }
            }

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
  <meta name="viewport" content="width=device-width,initial-scale=0.7">
  <title><?php echo $gblMsg['COMPANY'] . '  ' . $gblMsg['TITLE']; ?></title>
  <link rel="stylesheet" href="css/scheck_base.css" type="text/css" />
  <style type="text/css">
/*    body{  
        margin: 0;  
        padding: 0px 0 50px 0;  
    }  
    html body{  
        overflow: hidden;  
    }
    div#headerArea {  
        position: fixed !important;  
        position: absolute;  
        top: 0;  
        left: 0;  
        width: 100%;  
        height: 350px;  
        background-color: #F9EFF1;  
        color: #000;  
    }
*/
    div#footerArea {  
        position: fixed !important;  
        position: absolute;
        bottom: 0;  
        left: 0;  
        width: 100%;  
        height: 90px;  
        background-color: #F9EFF1;  
        color: #fff;  
    }  
    div#contentsArea{  
        height: 100%;  
        overflow: auto;  
    }
    body {
      word-wrap:break-word;
    }

    .entry-content pre {
      overflow:scroll;
      word-wrap:normal;
    }
    table {
      word-break:break-all;
    }
  </style>
  <script type="text/javascript">
  <!--
  function fnSuspended()
  {
<?php
    if ($noExamine != 1)
    {
        echo 'var obj = document.scheck;';
        echo 'if (obj.chk_no_examine.checked)';
        echo '{';
        echo '  alert("中断する場合は、｢受診しない｣のチェックを外してください。");';
        echo '  return;';
        echo '}';
    }
?>

      if(window.confirm("<?php echo $dispMsg['CFM_SPD']; ?>"))
      {
          fnSubmit("1");
      }
  }

  function fnSend()
  {
<?php
    if ($noExamine != 1)
    {
        echo 'var obj = document.scheck;';
        echo 'if (obj.chk_no_examine.checked)';
        echo '{';
        echo '  if(window.confirm("｢受診しない｣を送信します。\r\n受診した内容は全て破棄されますが\r\nよろしいですか？"))';
        echo '  {';
        echo '    fnSubmit("2");';
        echo '  }';
        echo '  return;';
        echo '}';
    }   
?>

      var errVal = fnInputCheck();
      if (errVal.length > 0)
      {
        alert("受診していない項目があります。\r\n" + errVal);
        return;
      }

      if(window.confirm("<?php echo $dispMsg['CFM_SEND']; ?>"))
      {
        fnSubmit("2");
      }
  }

  function fnSubmit(mode)
  {
      var obj = document.scheck;

      obj.edit_mode.value = mode;
      obj.method = "post";
      obj.action = "scheck.php";
      obj.submit();
  }

  function fnInputCheck()
  {
    var radioList = [];
    for(n = 0; n <= document.scheck.length - 1; n++)
    {
        if(document.scheck.elements[n].type == "radio")
        {
            var name = document.scheck.elements[n].name;
            var chk = document.scheck.elements[n].checked;

            if (name in radioList)
            {
                if (radioList[name] == true)
                {
                    continue;
                }
                radioList[name] = chk;
            }
            else
            {
                radioList[name] = chk;
            }
        }
    }

    var errVal = "";
    
    var errA = false;
    var errB = false;
    var errC = false;
    var errD = false;
    
    for(var key in radioList)
    {
        if (!radioList[key])
        {
            var splData = key.split("_");

            if (splData[1] == "0001" && !errA)
            {
                errA = true;
                errVal += "\r\nA項目 No.";
            }
            else if (splData[1] == "0002" && !errB)
            {
                if (errVal.length > 0)
                {
                    errVal = errVal.substr(0, (errVal.length - 1));
                }

                errB = true;
                errVal += "\r\nB項目 No.";
            }
            else if (splData[1] == "0003" && !errC)
            {
                if (errVal.length > 0)
                {
                    errVal = errVal.substr(0, (errVal.length - 1));
                }

                errC = true;
                errVal += "\r\nC項目 No.";
            }
            else if (splData[1] == "0004" && !errD)
            {
                if (errVal.length > 0)
                {
                    errVal = errVal.substr(0, (errVal.length - 1));
                }

                errD = true;
                errVal += "\r\nD項目 No.";
            }
            errVal += splData[2] + ",";
        }
    }

    if (errVal.length > 0)
    {
        if (errVal.slice(-1) == ",")
        {
            errVal = errVal.substr(0, (errVal.length - 1));
        }
    }

    return errVal;
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'alert("' . $errStr . '");';
        echo 'location.href = "index.php";';
    }
?>

  // -->
  </script>
 </head>
 <body bgcolor="#F9EFF1">
  <form name="scheck">
   <!--<div id="headerArea">-->
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
                                            T1.IS_EXAMINE,
                                            T1.EXAMINATION_STATUS_CD,
                                            T4.EXAMINATION_STATUS_STR,
                                            T4.FONT_COLOR
                                 FROM ((EXAMINEE_INFO T1 INNER JOIN
                                        M_COMPANY T2 ON
                                            T1.COMPANY_CD = T2.COMPANY_CD)
                                                INNER JOIN M_BUMON T3 ON T1.COMPANY_CD = T3.COMPANY_CD AND T1.BUMON_CD = T3.BUMON_CD)
                                                    INNER JOIN M_EXAMINATION_STATUS T4 ON T1.EXAMINATION_STATUS_CD = T4.EXAMINATION_STATUS_CD
                                 WHERE T1.AUTH_INFO ="' . $authId . '"
                                 ORDER BY T1.PERSON_NO, T1.COMPANY_CD, T1.BUMON_CD');

            $data = $dba->getFetchArray($result);

            $statusCd = $data['EXAMINATION_STATUS_CD'];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
?>
   <font size="5">
   <div id="contentsArea">
   <table width="90%" align="center" border="1" cellspacing="0" cellpadding="0" style="margin-left:30px;">
    <tr>
     <td align="center" width="80" nowrap>
      名前
     </td>
     <td nowrap>
      <?php echo $data['NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      名前カナ
     </td>
     <td nowrap>
      <?php echo $data['KNAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
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
     <td align="center" width="80" nowrap>
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
     <td align="center" width="80" nowrap>
      事業所名
     </td>
     <td nowrap>
      <?php echo $data['COMPANY_NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      部署名
     </td>
     <td nowrap>
      <?php echo $data['BUMON_NAME']; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      受診意思
     </td>
<?php
    $isExamine = "";
    if (!$data['IS_EXAMINE'])
    {
        $isExamine = "しない";
    }
?>
     <td nowrap>
      <?php echo $isExamine; ?>
     </td>
    </tr>
    <tr>
     <td align="center" width="80" nowrap>
      受診状態
     </td>
     <td nowrap>
      <?php echo $data['EXAMINATION_STATUS_STR']; ?>
     </td>
    </tr>
   </table>
   <br>
   <table width="90%" align="center" border="1" cellspacing="0" cellpadding="0" style="margin-left:30px;">
    <tr>
<?php
    if ($noExamine != 1)
    {
        $checked = "";
        $exaMsg = "※受診を希望しない場合は｢受診しない｣にチェックを入れ、送信ボタンを押下してください。<br>
                   　(受診中または受診が完了している場合であっても、受診した内容は全て破棄されます。)";
        if ($data['IS_EXAMINE'] == "0")
        {
            $checked = " checked";
            $exaMsg = "※受診を希望する受診者の場合は｢受診しない｣のチェックを外し、全ての受診項目の入力を行ってから送信ボタンを押下してください。<br>
                       　(一時中断する場合は、同様に｢受診しない｣のチェックを外し、中断ボタンを押下してください。)";
        }

        echo '<td width="155"><input type="checkbox" name="chk_no_examine" value="0" style="width:27px;height:27px;vertical-align:middle;"' . $checked . '><b>&nbsp;&nbsp;受診しない</b></td>';
        echo '<td><font color="red">' . $exaMsg . '</td>';
    }
?>
    </tr>
   </table>
   </font>
   <hr>
   <!--</div>-->
    <font size="6">
    <table width="90%" align="center" style="margin-left:30px;" border=0>
     <tr>
      <td align="left">
       Ａ項目からＤ項目まで全ての項目にお答えください。
      </td>
     </tr>
<?php
    try {
        $dba = new DbAccessor();
        $dba->initialize();

        foreach ($groupList as &$group)
        {
            // 受診項目選択値を取得
            $result = $dba->getData('SELECT CHECK_ITEM_SELECT_CD,
                                            CHECK_ITEM_SELECT_STR,
                                            CHECK_ITEM_SELECT_VAL
                                     FROM M_CHECK_ITEM_SELECT
                                     WHERE CHECK_ITEM_GROUP_CD = "' . $group[0] . '"
                                     ORDER BY CHECK_ITEM_SELECT_CD');

            $pos = 0;
            $arrSelectItem = array();
            while ($data = $dba->getFetchArray($result))
            {
                $arrSelectItem[$pos][0] = $data['CHECK_ITEM_SELECT_CD'];
                $arrSelectItem[$pos][1] = $data['CHECK_ITEM_SELECT_STR'];
                $arrSelectItem[$pos][2] = $data['CHECK_ITEM_SELECT_VAL'];
                $pos++;
            }

            // 受診項目を取得
            $result = $dba->getData('SELECT CHECK_ITEM_CD,
                                            CHECK_ITEM_NO,
                                            CHECK_ITEM_TITLE_FLG,
                                            CHECK_ITEM_STR
                                     FROM M_CHECK_ITEM
                                     WHERE CHECK_ITEM_GROUP_CD = "' . $group[0] . '"
                                     ORDER BY CHECK_ITEM_CD'
                                   );

            $pos = 0;
            $chkPos = 0;
            while ($data = $dba->getFetchArray($result))
            {
                if ($data['CHECK_ITEM_TITLE_FLG'] != 0)
                {
                    if ($data['CHECK_ITEM_TITLE_FLG'] == 1)
                    {
                        echo '<tr height="20"><td></td></tr>';
                    }
                    echo '<tr>';
                    echo '<td>';
                    echo '<b>' . $data['CHECK_ITEM_STR'] . '</b>';
                    echo '</td>';
                    echo '</tr>';
                    if ($data['CHECK_ITEM_TITLE_FLG'] == 1)
                    {
                        echo '<tr height="10"><td></td></tr>';
                    }
                }
                else
                {
                    echo '<tr>';
                    echo '<td>';
                    //echo getDispValue($data['CHECK_ITEM_NO'] . '.' . $data['CHECK_ITEM_STR']);
                    echo $data['CHECK_ITEM_NO'] . '.' . $data['CHECK_ITEM_STR'];
                    echo '</td>';

                    $chkPos = 1;
                    foreach ($arrSelectItem as &$value)
                    {
                        $checked = "";
                        if ($resultList[$group[0]][$data['CHECK_ITEM_NO']] == $chkPos)
                        {
                            $checked = " checked";
                        }
                        $chkPos++;

                        echo '<tr height="40">';
                        echo '<td align="left" style="padding-left:10px;">';
                        echo '&nbsp;<input type="radio" align="" style="width:27px;height:27px;vertical-align:top;" name="rdo_' . $group[0] . '_' . $data['CHECK_ITEM_NO'] . '" value="' . $value[2] . '"' . $checked . '>' . str_replace("<br>", "", $value[1]);
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '<tr height="10"><td></td></tr>';
                }
            }
        }

        $dba->close();
    } catch (Exception $e) { 
      echo $e->getMessage();
    }

    $btnDisabled = "nodisButton";
    $disabled = "";
    if ($statusCd == '0002' && $data['IS_EXAMINE'] == "1")
    {
        $btnDisabled = "disButton";
        $disabled = " disabled";
    }

    function getDispValue($data)
    {
        return mb_wordwrap($data, 15, "<br>&nbsp;&nbsp;&nbsp;", true);
    }

    function mb_wordwrap($string, $width=75, $break="\n", $cut = false)
    {
        if (!$cut)
        {
            $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.',}\b#U';
        }
        else
        {
            $regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.'}#';
        }
        $string_length = mb_strlen($string,'UTF-8');
        $cut_length = ceil($string_length / $width);
        $i = 1;
        $return = '';
        while ($i < $cut_length)
        {
            preg_match($regexp, $string,$matches);
            $new_string = $matches[0];
            $return .= $new_string.$break;
            $string = substr($string, strlen($new_string));
            $i++;
        }
        return $return.$string;
    }
?>
     <tr>
      <td>以上で受診項目は終了です。</td>
     </tr>
    </table>
    <br>
<!--
    <table align="center" id="headerTable" class="layout" width="95%" border=0 style="margin-left:50px;">
     <tr>
      <td align="left" width="130" nowrap>
       <input type="button" id="<?php echo $btnDisabled; ?>" onclick="fnSend();" value="<?php echo $dispMsg['BTN_SEND']; ?>" <?php echo $disabled; ?>>
      </td>
      <td></td>
     </tr>
    </table>
-->
    </div>
    <br>
    <hr>
    <div id="footerArea" class="disabled">
    <table align="center" id="headerTable" class="layout" width="95%" border=0 style="margin-left:50px;">
     <tr><td height="30"></td></tr>
     <tr>
      <td align="left" nowrap>
       <input type="button" id="<?php echo $btnDisabled; ?>" onclick="fnSuspended();" value="<?php echo $dispMsg['BTN_SPD']; ?>" <?php echo $disabled; ?>>
       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
       <input type="button" id="nodisButton" onclick="fnSend();" value="<?php echo $dispMsg['BTN_SEND']; ?>">
      </td>
     </tr>
    </table>
    </div>
   </font>
   <input type="hidden" name="edit_mode" value="">
   <input type="hidden" name="auth_id" value="<?php echo $authId; ?>">
  </form>
  <script type="text/javascript">
  <!--

<?php
    if (strlen($editMode) > 0)
    {
        if ($isNoExamaine == "0")
        {
            $editMode = "3";
        }
        echo 'var obj = document.scheck;';
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
