<?php
    include_once('cls/DbAccessor.php');
    include_once('cls/IniFile.php');
    include_once('cls/ResultInfoPdfCreator.php');
    include_once('cls/Parameter.php');
    include_once('cls/ProcNendo.php');

    $ins = IniFile::getInstance();
    $ins->init();
    $printMode = $ins->getValue('printmode', 'MODE');

    // パラメータ取得
    $pamObj = new Parameter($_GET, $_POST);
    $authId = rawurldecode($pamObj->getParameter('auth_id'));
    $companyCd = $pamObj->getParameter('company_cd');
    $busyoCds = $pamObj->getParameter('busyo_cd');
    $delFlg = $pamObj->getParameter('del_flg');
    $eDateS = $pamObj->getParameter('exa_date_s');
    $eDateE = $pamObj->getParameter('exa_date_e');
    $printSts = $pamObj->getParameter('print_sts');
    $printTarget[0] = $pamObj->getParameter('print_person');
    $printTarget[1] = $pamObj->getParameter('print_consent');
    $printTarget[2] = $pamObj->getParameter('print_examination');
    $printTarget[3] = $pamObj->getParameter('print_company');
    $printTarget[4] = $pamObj->getParameter('print_group');
    $chkStress = $pamObj->getParameterArray('chk_stress');

    session_start();

    set_time_limit(120);

    // 処理年度を取得
    $clsProc = new ProcNendo();
    $procNendo = $clsProc->getProcNendo();
    if ($procNendo == -1)
    {
        exit();
    }

    if (strlen($busyoCds) > 0)
    {
        $busyoCdList = explode(",", $busyoCds);
    }

    $pdf = new ResultInfoPdfCreator();
    $pdf->pdf_create($printTarget, $authId, $companyCd, $busyoCdList, $eDateS, $eDateE, $printSts, $chkStress, $procNendo);

    if ($printMode == "1")
    {
        exit();
    }
?>
