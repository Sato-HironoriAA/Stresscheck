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

    if (strlen($errStr) == 0)
    {
        $ins = IniFile::getInstance();
        $ins->init();
        $docJudgeMode = $ins->getValue('doctorjudge', 'JUDGE');

        $ins = MessageInfo::getInstance();
        $ins->init();
        $gblMsg = $ins->getSectionValue('global_message');
        $errMsg = $ins->getSectionValue('error_message');
        $pageName = $ins->getSectionValue('page_name');
        $dispMsg = $ins->getSectionValue('dscheck');

        // パラメータ取得
        $pamObj = new Parameter($_GET, $_POST);
        $auth_id = $_SESSION["PHP_AUTH_USER"];
        $editMode = $pamObj->getParameter('edit_mode');
        $isNoExamaine = $pamObj->getParameter('chk_no_examine');

        if (strlen($isNoExamaine) == 0)
        {
            $isNoExamaine = "1";
        }

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

        if (strlen($auth_id) > 0 && strlen($editMode) > 0)
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
                                                    IS_EXAMINE = 1,
                                                    UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                    UPDATE_ID = "' . $userId .'"
                                           WHERE AUTH_INFO = "' . $auth_id . '"'
                                          );

                $result = $dba->updateData('UPDATE EXAMINEE_INFO SET
                                                IS_EXAMINE = ' . $isNoExamaine . ',
                                                UPDATE_DATE = "' . date("Y-m-d", time()) .'",
                                                UPDATE_ID = "' . $userId .'"
                                       WHERE AUTH_INFO = "' . $auth_id . '"'
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
                                                     WHERE AUTH_INFO = "' . $auth_id . '"'
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
                                               WHERE AUTH_INFO = "' . $auth_id . '"'
                                              );

                    if ($editMode == "2")
                    {
                        $result = $dba->getData('SELECT COMPANY_CD,
                                                        SEX
                                                 FROM EXAMINEE_INFO
                                                 WHERE AUTH_INFO = "' . $auth_id . '"'
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
                                                 WHERE AUTH_INFO = "' . $auth_id . '"'
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
                                     WHERE AUTH_INFO = "' . $auth_id . '"'
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
<head>
 <meta http-equiv="content-type" content="text/html;charset=utf-8">
 <link rel="stylesheet" type="text/css" href="css/scheck_base.css">
 <title><?php echo $gblMsg['TITLE']; ?></title>
 <style type="text/css">
    div.tabbox {
       margin: 0px;
       padding: 0px;
       width: 1100px;
    }

    div.tabbox ul.tabs {
       margin:  0px;
       padding: 0px;
       border-radius: 12px 12px 0px 0px;
    }
    div.tabbox ul.tabs li {
       margin: 0px;
       padding: 0px;
       list-style-type: none;
       float: left;
       width: 120px;
       background-repeat: no-repeat;
       background-position: left top;
       border-radius: 12px 12px 0px 0px;
    }
    div.tabbox ul.tabs a {
       display: block;
       padding: 5px 0px;
       height: 20px;
       text-align: center;
       text-decoration: none;
       background-repeat: no-repeat;
       background-position: right top;
       border-radius: 12px 12px 0px 0px;
    }

    div.tabbox ul.tabs li.tab {
       background-color: gray;
    }
    div.tabbox ul.tabs a:link,
    div.tabbox ul.tabs a:visited {
       color: white;
    }
    div.tabbox ul.tabs a:hover {

       color: yellow;
       text-decoration: underline;
    }

    div.tabbox div.tab {
       //height: 800px;
       overflow: hidden;
       clear: left;
    }
<?php
    foreach ($groupList as &$val)
    {
        echo 'div.tabbox div#tab' . $val[0];
        echo ' {border: 0px solid blue; background-color: #F9EFF1';
        echo '}';
    }
?>

    div.tabbox div.tab p { margin: 0.5em; }
    div.tabbox div.tab p.tabhead {
       font-weight: bold; border-bottom: 3px double gray;
    }
 </style>
 <script type="text/javascript">
 <!--
  function fnSuspended()
  {
<?php
    if ($noExamine != 1)
    {
        echo 'var obj = document.dscheck;';
        echo 'if (obj.chk_no_examine.checked)';
        echo '{';
        echo '    alert("中断する場合は、｢受診しない｣のチェックを外してください。");';
        echo '    return;';
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
        echo 'var obj = document.dscheck;';
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

  function fnCancel()
  {
    if(window.confirm("<?php echo $dispMsg['CFM_CENCEL']; ?>")){
      window.close();
    }
  }

  function fnSubmit(mode)
  {
      var obj = document.dscheck;

      obj.edit_mode.value = mode;
      obj.method = "post";
      obj.action = "scheck.php";
      obj.target = "frame2";
      obj.submit();
  }

  function fnInputCheck()
  {
    var radioList = [];
    for(n = 0; n <= document.dscheck.length - 1; n++)
    {
        if(document.dscheck.elements[n].type == "radio")
        {
            var name = document.dscheck.elements[n].name;
            var chk = document.dscheck.elements[n].checked;

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

  function FocusTab(tabname, tabcolor) {
<?php
    foreach ($groupList as &$val)
    {
        echo 'document.getElementById(\'tab' . $val[1] . '\').style.backgroundColor = \'gray\';';
    }
?>
      if(tabname) {
          document.getElementById(tabname).style.backgroundColor = tabcolor;
      }
  }

  function ChangeTab(tabname) {
<?php
    foreach ($groupList as &$val)
    {
      echo 'document.getElementById(\'tab' . $val[0] . '\').style.display = \'none\';';
    }
?>
      if(tabname) {
         document.getElementById(tabname).style.display = 'block';
      }
  }

<?php
    if (strlen($errStr) > 0)
    {
        echo 'top.location.href = "index.php";';
    }
    
    if (strlen($editMode) > 0)
    {
        if ($isNoExamaine == "0")
        {
            $editMode = "3";
        }
        echo 'top.location.href = "scheck_end.php?edit_mode=' . $editMode . '";';
    }
?>
 // -->
 </script>
</head>
<body>
 <form name="dscheck" method="post" onsubmit="return false;">
<?php
    if (strlen($auth_id) > 0)
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
                                 WHERE T1.AUTH_INFO ="' . $auth_id . '"
                                 ORDER BY T1.PERSON_NO, T1.COMPANY_CD, T1.BUMON_CD');

            $data = $dba->getFetchArray($result);

            $statusCd = $data['EXAMINATION_STATUS_CD'];

            $dba->close();
        } catch (Exception $e) { 
          echo $e->getMessage();
        }
    }
?>
  <table align="center" id="headerTable" class="layout" width="85%" border=0>
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

        echo '<td><font size="3"><input type="checkbox" name="chk_no_examine" value="0"' . $checked . '><b>&nbsp;&nbsp;受診しない&nbsp;&nbsp;</b></td>';
        echo '<td><font color="red">' . $exaMsg . '</font></td>';
    }
?>
   </tr>
  </table>
  <br>
  <table align="center" id="headerTable" class="layout" width="85%" border=0>
   <tr>
    <td align="left" width="70%" nowrap>
     Ａ項目からＤ項目まで全ての項目にお答えください。
    </td>
    <td align="center" width="120" nowrap>
<?php
    $btnDisabled = "nodisButton";
    $disabled = "";
    if ($statusCd == '0002' && $data['IS_EXAMINE'] == "1")
    {
        $btnDisabled = "disButton";
        $disabled = " disabled";
    }
?>
     <input type="button" id="<?php echo $btnDisabled; ?>" onclick="fnSuspended();" value="<?php echo $dispMsg['BTN_SPD']; ?>" <?php echo $disabled; ?>>
    </td>
    <td align="center" width="120" nowrap>
     <input type="button" id="nodisButton" onclick="fnSend();" value="<?php echo $dispMsg['BTN_SEND']; ?>">
    </td>
    <td></td>
   </tr>
  </table>
  <div class="tabbox">
   <table align="center" id="headerTable" class="layout" width="85%" border=0>
    <tr>
     <td>
      <ul class="tabs">
<?php
    foreach ($groupList as &$val)
    {
        echo '<li class="tab" id="tab' . $val[1] . '"><a href="#tab' . $val[0] . '" onclick="FocusTab(\'tab' . $val[1] . '\',\'#49a9d4\'); ChangeTab(\'tab' . $val[0] . '\'); window.scrollTo(0, 0); return false;">' . $val[1] . '項目</a></li>';
    }
?>
      </ul>
     </td>
    </tr>
   </table>
<?php
try {
    $dba = new DbAccessor();
    $dba->initialize();

    $grpPos = 0;
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

        echo '<div id="tab' . $group[0] . '" class="tab">';
        echo '<font size="4">';
        echo '<table id="main" align="center" width="85%" border=0>';

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
                echo '<tr>';
                echo ' <td height="30" colspan=5 nowrap>';
                $space = "";
                if ($data['CHECK_ITEM_TITLE_FLG'] != 1)
                {
                    echo '<br>&nbsp;';
                }
                echo '   <b>' . $data['CHECK_ITEM_STR'] . '</b>';
                echo ' </td>';
                echo '</tr>';

                if ($data['CHECK_ITEM_TITLE_FLG'] == 1)
                {
                    echo '<tr>';
                    echo ' <td height="30" nowrap></td>';
                    foreach ($arrSelectItem as &$value)
                    {
                        echo ' <td height="30" align="center" width="120" nowrap>' . $value[1] . '</td>';
                    }
                    echo '</tr>';
                }
            }
            else
            {
                if ($pos == 10)
                {
                    echo '<tr>';
                    echo ' <td height="30" nowrap></td>';
                    foreach ($arrSelectItem as &$value)
                    {
                        echo ' <td height="30" align="center" width="120" nowrap>' . $value[1] . '</td>';
                    }
                    echo '</tr>';

                    $pos = 0;
                }

                echo '<tr>';
                echo ' <td height="30" nowrap>';
                echo '   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $data['CHECK_ITEM_NO'] . '.' . $data['CHECK_ITEM_STR'];
                echo ' </td>';
                
                $chkPos = 1;
                foreach ($arrSelectItem as &$value)
                {
                    $checked = "";
                    if ($resultList[$group[0]][$data['CHECK_ITEM_NO']] == $chkPos)
                    {
                        $checked = " checked";
                    }
                    $chkPos++;
                
                    echo ' <td height="30" align="center" nowrap><input type="radio" style="width:15px;height:15px;vertical-align:top;" name="rdo_' . $group[0] . '_' . $data['CHECK_ITEM_NO'] . '" value="' . $value[2] . '"' . $checked . '></td>';
                }
                echo '</tr>';

                $pos++;
            }
            
        }

        echo '<tr>';
        echo '<td colspan="2" align="right">';
        if ($grpPos > 0)
        {
            echo '<a href="#tab' . $groupList[$grpPos-1][0] . '" onclick="FocusTab(\'tab' . $groupList[$grpPos-1][1] . '\',\'#49a9d4\'); ChangeTab(\'tab' . $groupList[$grpPos-1][0] . '\'); window.scrollTo(0, 0); return false;">前の項目へ</a>';
            echo '&nbsp;&nbsp;&nbsp;';
        }

        if ($grpPos < 3)
        {
            echo '<a href="#tab' . $groupList[$grpPos+1][0] . '" onclick="FocusTab(\'tab' . $groupList[$grpPos+1][1] . '\',\'#49a9d4\'); ChangeTab(\'tab' . $groupList[$grpPos+1][0] . '\'); window.scrollTo(0, 0); return false;">次の項目へ</a>';
        }
        echo '</td>';
        echo '</tr>';
        $grpPos++;

        echo '</table>';
        echo '</font>';
        echo ' </div>';
    }

    $dba->close();
} catch (Exception $e) { 
  echo $e->getMessage();
}
?>
  <br>
    <script type="text/javascript">
    <!--
       // デフォルトのタブを選択
       FocusTab('tab<?php echo $groupList[0][1]; ?>', '#49a9d4');
       ChangeTab('tab<?php echo $groupList[0][0]; ?>');
    // --></script>
  <input type="hidden" name="edit_mode" value="">
  <input type="hidden" name="auth_id" value="<?php echo $auth_id; ?>">
 </form>
</body>
</html>