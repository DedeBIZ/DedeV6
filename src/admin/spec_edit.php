<?php
/**
 * 修改专题
 *
 * @version        $id:spec_edit.php 16:22 2010年7月20日 tianya $
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
    $channelid = -1;
    //读取归档信息
    $arcQuery = "SELECT ch.typename as channelname,ar.membername as rankname,arc.* FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel LEFT JOIN `#@__arcrank` ar ON ar.`rank`=arc.arcrank WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        ShowMsg("读取文档信息出错", "-1");
        exit();
    }
    $query = "SELECT * FROM `#@__channeltype` WHERE id='-1'";
    $cInfos = $dsql->GetOne($query);
    if (!is_array($cInfos)) {
        ShowMsg("读取栏目信息出错", "javascript:;");
        exit();
    }
    $addRow = $dsql->GetOne("SELECT * FROM `#@__addonspec` WHERE aid='$aid'");
    $tags = GetTags($aid);
    include DedeInclude("templets/spec_edit.htm");
} else if ($dopost == 'save') {
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    $flag = isset($flags) ? join(',', $flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1 : 0;
    if (!isset($tags)) $tags = '';
    $channelid = -1;
    //处理自定义字段会用到这些变量
    if (!isset($autokey)) $autokey = 0;
    if (!isset($remote)) $remote = 0;
    if (!isset($dellink)) $dellink = 0;
    if (!isset($autolitpic)) $autolitpic = 0;
    //对保存的文档进行处理
    $pubdate = GetMkTime($pubdate);
    $senddate = GetMkTime($senddate);
    $sortrank = AddDay($pubdate, $sortup);
    if ($ishtml == 0) $ismake = -1;
    else $ismake = 0;
    $title = cn_substrR($title, $cfg_title_maxlen);
    $shorttitle = cn_substrR($shorttitle, 255);
    $color =  cn_substrR($color, 7);
    $writer =  cn_substrR($writer, 255);
    $source = cn_substrR($source, 255);
    $description = cn_substrR($description, $cfg_auot_description);
    $keywords = trim(cn_substrR($keywords, 255));
    $filename = trim(cn_substrR($filename, 50));
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
                $inadd_f .= ",`{$vs[0]}` = '".${$vs[0]}."'";
            }
        }
    }
    //处理图片文档的自定义属性
    if ($litpic != '' && !preg_match('#p#', $flag)) {
        $flag = ($flag == '' ? 'p' : $flag.',p');
    }
    $inQuery = "UPDATE `#@__archives` SET typeid='$typeid',sortrank='$sortrank',flag='$flag',ismake='$ismake',arcrank='$arcrank',click='$click',title='$title',color='$color',writer='$writer',source='$source',litpic='$litpic',pubdate='$pubdate',senddate='$senddate',notpost='$notpost',description='$description',keywords='$keywords',shorttitle='$shorttitle',filename='$filename' WHERE id='$id'; ";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("数据保存到数据库文档主表出错，请检查数据库字段", "-1");
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
            if (isset(${'noteid'.$i})) {
                $noteid = trim(${'noteid'.$i});
            } else {
                $noteid = $i;
            }
            if (isset(${'isauto'.$i})) {
                $isauto = trim(${'isauto'.$i});
            } else {
                $isauto = 0;
            }
            if (isset(${'keywords'.$i})) {
                $keywords = str_replace("'", "", trim(${'keywords'.$i}));
            } else {
                $keywords = '';
            }
            if (!empty(${'typeid'.$i})) {
                $ttypeid = trim(${'typeid'.$i});
            } else {
                $ttypeid = 0;
            }
            if (!empty(${'rownum'.$i})) {
                $rownum = trim(${'rownum'.$i});
            } else {
                $rownum = 0;
            }
            $arcid = preg_replace("#[^0-9,]#", "", $arcid);
            $ids = explode(",", $arcid);
            $okids = '';
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
            $notelist .= "{dede:specnote imgheight=\\'$imgheight\\' imgwidth=\\'$imgwidth\\' infolen=\\'$infolen\\' titlelen=\\'$titlelen\\' col=\\'$col\\' idlist=\\'$okids\\' name=\\'$notename\\' noteid=\\'$noteid\\' isauto=\'$isauto\' rownum=\\'$rownum\\' keywords=\\'$keywords\\' typeid=\\'$ttypeid\\'} $listtmp {/dede:specnote}\r\n";
        }
    }
    //更新附加表
    $inQuery = "UPDATE `#@__addonspec` SET typeid ='$typeid',note='$notelist'{$inadd_f},templet='$templet' WHERE aid='$id';";
    if (!$dsql->ExecuteNoneQuery($inQuery)) {
        ShowMsg("数据保存到数据库附加表出错，请检查数据库字段", "-1");
        exit();
    }
    //生成网页
    UpIndexKey($id, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($id, TRUE, TRUE, $isremote);
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
    $msg = "<tr>
        <td align='center'><a href='$artUrl' target='_blank' class='btn btn-success btn-sm'>浏览专题</a><a href='spec_add.php?cid=$typeid' class='btn btn-success btn-sm'>发布专题</a><a href='archives_do.php?aid=".$id."&dopost=editArchives' class='btn btn-success btn-sm'>修改专题</a><a href='content_s_list.php' class='btn btn-success btn-sm'>返回专题列表</a></td>
    </tr>";
    $wintitle = "成功修改专题";
    $win = new OxWindow();
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", FALSE);
    $win->Display();
}
?>