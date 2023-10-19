<?php
/**
 * 发布分类文档模型
 *
 * @version        $id:archives_sg_add.php 8:26 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('a_New,a_AccNew');
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEINC."/dedetag.class.php");
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    ClearMyAddon();
    $channelid = empty($channelid) ? 0 : intval($channelid);
    $cid = empty($cid) ? 0 : intval($cid);
    //获得栏目模型id
    if ($cid > 0 && $channelid == 0) {
        $row = $dsql->GetOne("SELECT channeltype FROM `#@__arctype` WHERE id='$cid';");
        $channelid = $row['channeltype'];
    } else {
        if ($channelid == 0) {
            ShowMsg("无法识别模型信息，因此无法操作", "-1");
            exit();
        }
    }
    //获得栏目模型信息
    $cInfos = $dsql->GetOne(" SELECT * FROM `#@__channeltype` WHERE id='$channelid' ");
    $channelid = $cInfos['id'];
    include DedeInclude("templets/archives_sg_add.htm");
    exit();
} else if ($dopost == 'save') {
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    if (trim($title) == '') {
        ShowMsg("文档标题不能为空", "-1");
        exit();
    }
    if (empty($typeid)) {
        ShowMsg("请选择文档栏目", "-1");
        exit();
    }
    if (empty($channelid)) {
        ShowMsg("文档为非指定类型，请检查您发布文档是否正确", "-1");
        exit();
    }
    if (!CheckChannel($typeid, $channelid)) {
        ShowMsg("您所选择的栏目与当前模型不相符，请重新选择", "-1");
        exit();
    }
    if (!TestPurview('a_New')) {
        CheckCatalog($typeid, "您没有操作栏目{$typeid}权限");
    }
    //对保存的文档进行处理
    if (empty($writer)) $writer = $cuserLogin->getUserName();
    if (empty($source)) $source = '未知';
    if (empty($flags)) $flag = '';
    else $flag = join(',', $flags);
    $senddate = time();
    $title = cn_substrR($title, $cfg_title_maxlen);
    $isremote  = 0;
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!TestPurview('a_Check,a_AccCheck,a_MyCheck')) $arcrank = -1;
    $adminid = $cuserLogin->getUserID();
    $userip = GetIP();
    if (empty($ddisremote)) $ddisremote = 0;
    $litpic = GetDDImage('none', $picname, $ddisremote);
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $senddate, $channelid, $senddate, $adminid);
    if (empty($arcID)) {
        ShowMsg("获取主键失败，无法进行后续操作", "-1");
        exit();
    }
    //分析处理附加表数据
    $inadd_f = '';
    $inadd_v = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        $inadd_f = '';
        $inadd_v = '';
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') {
                    continue;
                }
                $vs = explode(',', $v);
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') //网页文本特殊处理
                {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                } else {
                    if (!isset(${$vs[0]})) {
                        ${$vs[0]} = '';
                    }
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $arcID);
                }
                $inadd_f .= ','.$vs[0];
                $inadd_v .= " ,'".${$vs[0]}."' ";
            }
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    //保存到附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if (!empty($addtable)) {
        $query = "INSERT INTO `{$addtable}` (aid,typeid,channel,arcrank,mid,click,title,senddate,flag,litpic,userip{$inadd_f}) VALUES ('$arcID','$typeid','$channelid','$arcrank','$adminid','0','$title','$senddate','$flag','$litpic','$userip'{$inadd_v})";
        if (!$dsql->ExecuteNoneQuery($query)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg("数据保存到数据库附加表出错，请检查数据库字段".str_replace('"', '', $gerr), "javascript:;");
            exit();
        }
    }
    //生成网页
    $artUrl = MakeArt($arcID, TRUE, TRUE, $isremote);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }
    ClearMyAddon($arcID, $title);
    //返回成功信息
    $msg = "<tr>
        <td bgcolor='#f8fafb' align='center'><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览分类文档</a><a href='archives_sg_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布分类文档</a><a href='archives_do.php?aid=".$arcID."&dopost=editArchives' class='btn btn-success btn-sm'>修改分类文档</a><a href='content_sg_list.php?cid=$typeid&channelid={$channelid}&dopost=listArchives' class='btn btn-success btn-sm'>管理分类文档</a>$backurl</td>
    </tr>";
    $wintitle = "成功发布分类文档";
    $wecome_info = "文档管理 - 发布分类文档";
    $win = new OxWindow();
    $win->AddTitle("成功发布分类文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display();
}
?>