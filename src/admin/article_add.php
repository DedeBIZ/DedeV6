<?php
/**
 * 发布文档模型
 *
 * @version        $id:article_add.php 2020年9月14日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('a_New,a_AccNew');
require_once(DEDEINC.'/customfields.func.php');
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
if (file_exists(DEDEDATA.'/template.rand.php')) {
    require_once(DEDEDATA.'/template.rand.php');
}
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEINC."/dedetag.class.php");
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    ClearMyAddon();
    $channelid = empty($channelid) ? 0 : intval($channelid);
    $cid = empty($cid) ? 0 : intval($cid);
    if (empty($geturl)) $geturl = '';
    $keywords = $writer = $source = $body = $description = $title = '';
    //获得栏目模型id
    if ($cid > 0 && $channelid == 0) {
        $row = $dsql->GetOne("SELECT channeltype FROM `#@__arctype` WHERE id='$cid';");
        $channelid = $row['channeltype'];
    } else {
        if ($channelid == 0) {
            $channelid = 1;
        }
    }
    //获得栏目模型信息
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid' ");
    //获取文档最大id+1以确定当前权重
    $maxWright = $dsql->GetOne("SELECT id+1 AS cc FROM `#@__archives` ORDER BY id DESC LIMIT 1");
    $maxWright = empty($maxWright)? array('cc'=>1) : $maxWright;
    include DedeInclude("templets/article_add.htm");
    exit();
} else if ($dopost == 'save') {
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (empty($typeid2)) $typeid2 = '';
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    if (empty($click)) $click = ($cfg_arc_click == '-1' ? mt_rand(1000, 6000) : $cfg_arc_click);
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
    $pubdate = GetMkTime($pubdate);
    $senddate = time();
    $sortrank = AddDay($pubdate, $sortup);
    $ismake = $ishtml == 0 ? -1 : 0;
    $title = preg_replace("#\"#", '＂', $title);
    $title = dede_htmlspecialchars(cn_substrR($title, $cfg_title_maxlen));
    $shorttitle = cn_substrR($shorttitle, 36);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 20);
    $source = cn_substrR($source, 30);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = cn_substrR($keywords, 60);
    $filename = trim(cn_substrR($filename, 40));
    $userip = GetIP();
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
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $adminid);
    if (empty($arcID)) {
        ShowMsg("获取主键失败，无法进行后续操作", "-1");
        exit();
    }
    //处理body字段自动摘要、自动提取缩略图等
    $body = AnalyseHtmlBody($body, $description, $litpic, $keywords, 'htmltext');
    //自动分页
    if ($sptype == 'auto') {
        $body = SpLongBody($body, $spsize * 1024, "#p#分页标题#e#");
    }
    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') continue;
                $vs = explode(',', $v);
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $litpic, $keywords, $vs[1]);
                } else {
                    if (!isset(${$vs[0]})) ${$vs[0]} = '';
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
    if ($redirecturl != '' && !preg_match("#j#", $flag)) {
        $flag = ($flag == '' ? 'j' : $flag.',j');
    }
    //跳转网址的文档强制为动态
    if (preg_match("#j#", $flag)) $ismake = -1;
    //保存到主表
    $query = "INSERT INTO `#@__archives` (id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,notpost,description,keywords,filename,dutyadmin,weight) VALUES ('$arcID','$typeid','$typeid2','$sortrank','$flag','$ismake','$channelid','$arcrank','$click','$money','$title','$shorttitle','$color','$writer','$source','$litpic','$pubdate','$senddate','$adminid','$notpost','$description','$keywords','$filename','$adminid','$weight');";
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("数据保存到数据库文档主表出错，请检查数据库字段".str_replace('"', '', $gerr), "javascript:;");
        exit();
    }
    //保存到附加表
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if (empty($addtable)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到模型{$channelid}主表信息，无法完成操作", "javascript:;");
        exit();
    }
    $useip = GetIP();
    $templet = empty($templet) ? '' : $templet;
    $query = "INSERT INTO `{$addtable}` (aid,typeid,redirecturl,templet,userip,body{$inadd_f}) VALUES ('$arcID','$typeid','$redirecturl','$templet','$useip','$body'{$inadd_v})";
    if (!$dsql->ExecuteNoneQuery($query)) {
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("数据保存到数据库附加表出错，请检查数据库字段".str_replace('"', '', $gerr), "javascript:;");
        exit();
    }
    //生成网页
    InsertTags($tags, $arcID);
    $picTitle = false;
    if (count($_SESSION['bigfile_info']) > 0) {
        foreach ($_SESSION['bigfile_info'] as $k => $v) {
            if (!empty($v)) {
                $pictitle = ${'picinfook'.$k};
                $titleSet = '';
                if (!empty($pictitle)) {
                    $picTitle = TRUE;
                    $titleSet = ",title='{$pictitle}'";
                }
                $dsql->ExecuteNoneQuery("UPDATE `#@__uploads` SET arcid='{$arcID}'{$titleSet} WHERE url LIKE '{$v}';");
            }
        }
    }
    $artUrl = MakeArt($arcID, true, true, $isremote);
    if ($artUrl == '') {
        $artUrl = $cfg_phpurl."/view.php?aid=$arcID";
    }
    ClearMyAddon($arcID, $title);
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
    $msg = "<tr>
        <td bgcolor='#f8fafb' align='center'><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览文档</a><a href='article_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布文档</a><a href='archives_do.php?aid=".$arcID."&dopost=editArchives' class='btn btn-success btn-sm'>修改文档</a><a href='catalog_do.php?cid=$typeid&dopost=listArchives' class='btn btn-success btn-sm'>返回文档列表</a></td>
    </tr>";
    $msg = "{$msg}".GetUpdateTest();
    $wintitle = "成功发布文档";
    $wecome_info = "文档管理 - 发布文档";
    $win = new OxWindow();
    $win->AddTitle("成功发布文档");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", false);
    $win->Display();
}
?>