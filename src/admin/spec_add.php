<?php
/**
 * 发布专题
 *
 * @version        $id:spec_add.php 16:22 2010年7月20日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('a_New,a_AccNew');
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if (empty($dopost)) $dopost = '';
if ($dopost != 'save') {
    require_once(DEDEINC.'/dedetag.class.php');
    require_once(DEDEADMIN.'/inc/inc_catalog_options.php');
    ClearMyAddon();
    $channelid = -1;
    $cid = isset($cid) && is_numeric($cid) ? $cid : 0;
    //获得栏目模型信息
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid' ");
    include DedeInclude("templets/spec_add.htm");
    exit();
} else if ($dopost == 'save') {
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (empty($click)) $click = ($cfg_arc_click == '-1' ? mt_rand(1000,6000) : $cfg_arc_click);
    $channelid = -1;
    $money = 0;
    if (!isset($tags)) $tags = '';
    //处理自定义字段会用到这些变量
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    //对保存的文档进行处理
    if (empty($writer)) $writer = $cuserLogin->getUserName();
    if (empty($source)) $source = '未知';
    $pubdate = GetMkTime($pubdate);
    $senddate = time();
    $sortrank = AddDay($pubdate, $sortup);
    if ($ishtml == 0) $ismake = -1;
    else $ismake = 0;
    $title = preg_replace('#"#', '＂', $title);
    $title = cn_substrR($title, $cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle, 36);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 20);
    $source = cn_substrR($source, 30);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = cn_substrR($keywords, 60);
    $filename = trim(cn_substrR($filename, 40));
    $isremote  = 0;
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (!TestPurview('a_Check,a_AccCheck,a_MyCheck')) $arcrank = -1;
    $adminid = $cuserLogin->getUserID();
    //处理上传的缩略图
    if (empty($ddisremote)) {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('none', $picname, $ddisremote);
    //生成文档id
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $adminid);
    if (empty($arcID)) {
        ShowMsg("无法获得主键，无法进行后续操作", "-1");
        exit();
    }
    //保存到主表
    $inQuery = "INSERT INTO `#@__archives` (id,typeid,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,notpost,description,keywords,filename) VALUES ('$arcID','$typeid','$sortrank','$flag','$ismake','$channelid','$arcrank','$click','$money','$title','$shorttitle','$color','$writer','$source','$litpic','$pubdate','$senddate','$adminid','$notpost','$description','$keywords','$filename');";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        echo $inQuery;
        $gerr = $dsql->GetError();
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("数据保存到数据库主表`#@__archives`时出错，请检查数据库字段".str_replace('"', '', $gerr), "javascript:;");
        exit();
    }
    //专题节点列表
    $arcids = array();
    $notelist = '';
    for ($i = 1; $i <= $cfg_specnote; $i++) {
        if (!empty(${'notename'.$i})) {
            $notename = str_replace("'", "", trim(${'notename'.$i}));
            $arcid = trim(${'arcid'.$i});
            $col = trim(${'col'.$i});
            $imgwidth = trim(${'imgwidth'.$i});
            $imgheight = trim(${'imgheight'.$i});
            $titlelen = trim(${'titlelen'.$i});
            $infolen = trim(${'infolen'.$i});
            $listtmp = trim(${'listtmp'.$i});
            $noteid = trim(${'noteid'.$i});
            $isauto = trim(${'isauto'.$i});
            $keywords = str_replace("'", "", trim(${'keywords'.$i}));
            $typeid = trim(${'typeid'.$i});
            if (!empty(${'rownum'.$i}))  $rownum = trim(${'rownum'.$i});
            else $rownum = 0;
            $arcid = preg_replace("#[^0-9,]#", "", $arcid);
            $ids = explode(",", $arcid);
            $okids = "";
            if (is_array($ids)) {
                foreach ($ids as $mid) {
                    $mid = trim($mid);
                    if ($mid == "") continue;
                    if (!isset($arcids[$mid])) {
                        if ($okids == "") {
                            $okids .= $mid;
                        } else {
                            $okids .= ",".$mid;
                        }
                        $arcids[$mid] = 1;
                    }
                }
            }
            $notelist .= "{dede:specnote imgheight=\\'$imgheight\\' imgwidth=\\'$imgwidth\\' infolen=\\'$infolen\\' titlelen=\\'$titlelen\\' col=\\'$col\\' idlist=\\'$okids\\' name=\\'$notename\\' noteid=\\'$noteid\\' isauto=\'$isauto\' rownum=\\'$rownum\\' keywords=\\'$keywords\\' typeid=\\'$typeid\\'} $listtmp {/dede:specnote}\r\n";
        }
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
    if ($litpic != '' && !preg_match('#p#', $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    $useip = GetIP();
    //加入附加表
    $inQuery = "INSERT INTO `#@__addonspec` (aid,typeid,userip,templet,note{$inadd_f}) VALUES ('$arcID','$typeid','$useip','$templet','$notelist'{$inadd_v});";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE id='$arcID'");
        ShowMsg("数据保存到数据库附加表时出错，请检查数据库字段", "-1");
        exit();
    }
    //生成网页
    InsertTags($tags, $arcID);
    $artUrl = MakeArt($arcID, TRUE, TRUE, $isremote);
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
    $msg = "请选择您的后续操作：<a href='spec_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布专题</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>修改专题</a><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览专题</a><a href='content_s_list.php' class='btn btn-success btn-sm'>管理专题</a>";
    $wintitle = "成功发布专题";
    $wecome_info = "专题管理::发布专题";
    $win = new OxWindow();
    $win->AddTitle("成功发布专题");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", FALSE);
    $win->Display();
}
?>