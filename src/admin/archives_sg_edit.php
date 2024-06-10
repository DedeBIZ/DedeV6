<?php
/**
 * 修改单表文档模型
 *
 * @version        $id:archives_sg_edit.php 8:26 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('a_Edit,a_AccEdit,a_MyEdit');
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    require_once(DEDEINC."/dedetag.class.php");
    ClearMyAddon();
    $aid = intval($aid);
    //读取归档信息
    $arcQuery = "SELECT ch.*,arc.* FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch on ch.id=arc.channel WHERE arc.id='$aid' ";
    $cInfos = $dsql->GetOne($arcQuery);
    if (!is_array($cInfos)) {
        ShowMsg("读栏目模型信息出错", "-1");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addRow = $dsql->GetOne("SELECT arc.*,ar.membername as rankname FROM `$addtable` arc LEFT JOIN `#@__arcrank` ar on ar.`rank`=arc.arcrank WHERE arc.aid='$aid'");
    $channelid = $cInfos['channel'];
    $tags = GetTags($aid);
    include DedeInclude('templets/archives_sg_edit.htm');
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
    if (!TestPurview('a_Edit')) {
        CheckCatalog($typeid, "您没有操作栏目{$typeid}文档权限");
    }
    //对保存的文档进行处理
    if (empty($flags)) $flag = '';
    else $flag = join(',', $flags);
    $title = cn_substrR($title, $cfg_title_maxlen);
    $isremote  = 0;
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!TestPurview('a_Check,a_AccCheck,a_MyCheck')) $arcrank = -1;
    $adminid = $cuserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) $ddisremote = 0;
    $litpic = GetDDImage('none', $picname, $ddisremote);
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
                    ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $id);
                }
                $inadd_f .= ",`{$vs[0]}` = '".${$vs[0]}."'";
            }
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match("#p#", $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $iquery = "UPDATE `$addtable` SET typeid='$typeid',arcrank='$arcrank',title='$title',flag='$flag',litpic='$litpic'{$inadd_f} WHERE aid='$id' ";
        if (!$dsql->ExecuteNoneQuery($iquery)) {
            ShowMsg("数据保存到数据库附加表出错，请检查数据库字段", "javascript:;");
            exit();
        }
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, '');
    $artUrl = MakeArt($id, TRUE, TRUE, $isremote);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$id";
    ClearMyAddon($id, $title);
    //返回成功信息
    $msg = "<tr>
        <td align='center'><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='archives_sg_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>修改文档</a><a href='catalog_do.php?cid=$typeid&channelid={$channelid}&dopost=listArchives' class='btn btn-success btn-sm'>返回文档列表</a></td>
    </tr>";
    $wintitle = "成功修改分类文档";
    $win = new OxWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display();
}
?>