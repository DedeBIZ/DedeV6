<?php
/**
 * 文档编辑
 *
 * @version        $id:article_edit.php 14:12 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('a_Edit,a_AccEdit,a_MyEdit');
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (file_exists(DEDEDATA.'/template.rand.php')) {
    require_once(DEDEDATA.'/template.rand.php');
}
if (empty($dopost)) $dopost = '';
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
if ($dopost != 'save') {
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    require_once(DEDEINC."/dedetag.class.php");
    ClearMyAddon();
    //读取归档信息
    $query = "SELECT ch.typename AS channelname,ar.membername AS rankname,arc.* FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel LEFT JOIN `#@__arcrank` ar ON ar.`rank`=arc.arcrank WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($query);
    if (!is_array($arcRow)) {
        ShowMsg("读取文档基本信息出错", "-1");
        exit();
    }
    $query = "SELECT * FROM `#@__channeltype` WHERE id='".$arcRow['channel']."'";
    $cInfos = $dsql->GetOne($query);
    if (!is_array($cInfos)) {
        ShowMsg("读取栏目配置信息出错", "javascript:;");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addRow = $dsql->GetOne("SELECT * FROM `$addtable` WHERE aid='$aid'");
    if (!is_array($addRow)) {
        ShowMsg("读取附加信息出错", "javascript:;");
        exit();
    }
    $channelid = $arcRow['channel'];
    $tags = GetTags($aid);
    include DedeInclude("templets/article_edit.htm");
    exit();
}
/*--------------------------------
function __save(){  }
-------------------------------*/
else if ($dopost == 'save') {
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (empty($typeid2)) $typeid2 = 0;
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    if (empty($typeid)) {
        ShowMsg("请指定文档的栏目", "-1");
        exit();
    }
    if (empty($channelid)) {
        ShowMsg("文档为非指定的类型，请检查您发布文档的表单是否合法", "-1");
        exit();
    }
    if (!CheckChannel($typeid, $channelid)) {
        ShowMsg("您所选择的栏目与当前模型不相符，请选择白色的选项", "-1");
        exit();
    }
    if (!TestPurview('a_Edit')) {
        if (TestPurview('a_AccEdit')) {
            CheckCatalog($typeid, "对不起，您没有操作栏目<span class='text-primary'>{$typeid}</span>文档权限");
        } else {
            CheckArcAdmin($id, $cuserLogin->getUserID());
        }
    }
    //对保存的文档进行处理
    $pubdate = GetMkTime($pubdate);
    $sortrank = AddDay($pubdate, $sortup);
    $ismake = $ishtml == 0 ? -1 : 0;
    $autokey = 1;
    $title = dede_htmlspecialchars(cn_substrR($title, $cfg_title_maxlen));
    $shorttitle = cn_substrR($shorttitle, 36);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 20);
    $source = cn_substrR($source, 30);
    $description = cn_substrR($description, 250);
    $keywords = trim(cn_substrR($keywords, 60));
    $filename = trim(cn_substrR($filename, 40));
    $isremote  = 0;
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!TestPurview('a_Check,a_AccCheck,a_MyCheck')) {
        $arcrank = -1;
    }
    $adminid = $cuserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('none', $picname, $ddisremote);
    //分析body里的文档
    $body = AnalyseHtmlBody($body, $description, $litpic, $keywords, 'htmltext');
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
    if ($redirecturl != '' && !preg_match("#j#", $flag)) {
        $flag = ($flag == '' ? 'j' : $flag.',j');
    }
    //跳转网址的文档强制为动态
    if (preg_match("#j#", $flag)) $ismake = -1;
    //更新数据库的SQL语句
    $query = "UPDATE `#@__archives` SET typeid='$typeid',typeid2='$typeid2',sortrank='$sortrank',flag='$flag',click='$click',ismake='$ismake',arcrank='$arcrank',money='$money',title='$title',color='$color',writer='$writer',source='$source',litpic='$litpic',pubdate='$pubdate',notpost='$notpost',description='$description',keywords='$keywords',shorttitle='$shorttitle',filename='$filename',dutyadmin='$adminid',weight='$weight'WHERE id='$id'; ";
    if (!$dsql->ExecuteNoneQuery($query)) {
        ShowMsg('数据保存到数据库主表`#@__archives`时出错，请检查数据库字段', -1);
        exit();
    }
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if ($addtable != '') {
        $useip = GetIP();
        $templet = empty($templet) ? '' : $templet;
        $iquery = "UPDATE `$addtable` SET typeid='$typeid',body='$body'{$inadd_f},redirecturl='$redirecturl',templet='$templet',userip='$useip' WHERE aid='$id'";
        if (!$dsql->ExecuteNoneQuery($iquery)) {
            ShowMsg("数据保存到数据库附加表时出错，请检查数据库字段", "javascript:;");
            exit();
        }
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($id, true, true, $isremote);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$id";
    }
    ClearMyAddon($id, $title);
    //自动更新关联文档
    if (is_array($automake)) {
        foreach ($automake as $key => $value) {
            if (isset(${$key}) && !empty(${$key})) {
                $ids = explode(",", ${$key});
                foreach ($ids as $id) {
                    MakeArt($id, true, true, $isremote);
                }
            }
        }
    }
    //返回成功信息
    $msg = "请选择您的后续操作：<a href='article_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布新文档</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>修改文档</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>查看文档</a><a href='catalog_do.php?cid=$typeid&dopost=listArchives' class='btn btn-success btn-sm'>管理文档</a>$backurl";
    $wintitle = "成功修改文档";
    $wecome_info = "文档管理::修改文档";
    $win = new OxWindow();
    $win->AddTitle("成功修改文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "&nbsp;", false);
    $win->Display();
}
?>