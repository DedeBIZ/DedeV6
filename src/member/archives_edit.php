<?php
/**
 * 文档编辑器
 * 
 * @version        $Id: archives_edit.php 1 13:52 2010年7月9日Z tianya $
 * @package        DedeBIZ.Member
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0, 0);
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEMEMBER."/inc/inc_catalog_options.php");
require_once(DEDEMEMBER."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 1;
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$mtypesid = isset($mtypesid) && is_numeric($mtypesid) ? $mtypesid : 0;
$menutype = 'content';

/*-------------
function _ShowForm(){  }
--------------*/
if (empty($dopost)) {
    //读取归档信息
    $arcQuery = "SELECT arc.*,ch.addtable,ch.fieldset,arc.mtype as mtypeid,ch.arcsta
       FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel
       WHERE arc.id='$aid' And arc.mid='".$cfg_ml->M_ID."'; ";
    $row = $dsql->GetOne($arcQuery);
    if (!is_array($row)) {
        ShowMsg("读取文档信息出错!", "-1");
        exit();
    } else if ($row['arcrank'] >= 0) {
        $dtime = time();
        $maxtime = $cfg_mb_editday * 24 * 3600;
        if ($dtime - $row['senddate'] > $maxtime) {
            ShowMsg("这篇文档已经锁定，您不能再修改它", "-1");
            exit();
        }
    }
    $addRow = $dsql->GetOne("SELECT * FROM `{$row['addtable']}` WHERE aid='$aid'; ");
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype`  WHERE id='{$row['channel']}'; ");
    include(DEDEMEMBER."/templets/archives_edit.htm");
    exit();
}

/*------------------------------
function _SaveArticle(){  }
------------------------------*/
else if ($dopost == 'save') {
    include(DEDEMEMBER.'/inc/archives_check_edit.php');

    //分析处理附加表数据
    $inadd_f = $inadd_m = '';
    if (!empty($dede_addonfields)) {
        $addonfields = explode(';', $dede_addonfields);
        if (is_array($addonfields)) {
            foreach ($addonfields as $v) {
                if ($v == '') {
                    continue;
                }
                $vs = explode(',', $v);
                if (!isset(${$vs[0]})) {
                    ${$vs[0]} = '';
                }

                //自动摘要和远程图片本地化
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $vs[1]);
                }

                ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], $aid);
                $inadd_m .= ','.$vs[0];
                $inadd_f .= ','.$vs[0]." ='".${$vs[0]}."' ";
            }
        }

        //这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd($cInfos['fieldset'], 'autofield', FALSE);
        if ($fontiterm != $inadd_m) {
            ShowMsg("提交表单同系统配置不相符,请重新提交", "-1");
            exit();
        }
    }

    //处理图片文档的自定义属性
    if ($litpic != '') $flag = 'p';


    //更新数据库的SQL语句
    $upQuery = "UPDATE `#@__archives` SET
              ismake='$ismake',
              arcrank='$arcrank',
              typeid='$typeid',
              title='$title',
              litpic='$litpic',
              description='$description',
              keywords='$keywords',  
              mtype = '$mtypesid',        
              flag='$flag'
     WHERE id='$aid' And mid='$mid'; ";

    if (!$dsql->ExecuteNoneQuery($upQuery)) {
        ShowMsg("把数据保存到数据库主表时出错，请联系管理员".$dsql->GetError(), "-1");
        exit();
    }

    if ($addtable != '') {
        $upQuery = "UPDATE `$addtable` SET typeid='$typeid'{$inadd_f}, userip='$userip' WHERE aid='$aid' ";
        if (!$dsql->ExecuteNoneQuery($upQuery)) {
            ShowMsg("更新附加表 `$addtable`  时出错，请联系管理员", "javascript:;");
            exit();
        }
    }
    $arcrank = empty($arcrank) ? 0 : $arcrank;
    $sortrank = empty($sortrank) ? 0 : $sortrank;
    UpIndexKey($aid, $arcrank, $typeid, $sortrank, $tags);
    $artUrl = MakeArt($aid, TRUE);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$aid";

    //返回成功信息
    $msg = "　　请选择您的后续操作：
        <a href='archives_add.php?cid=$typeid&channelid=$channelid' class='btn btn-secondary btn-sm'>发布新内容</a>
        &nbsp;&nbsp;
        <a href='archives_edit.php?channelid=$channelid&aid=".$aid."' class='btn btn-secondary btn-sm'>查看修改</a>
        &nbsp;&nbsp;
        <a href='$artUrl' target='_blank'>查看内容</a>
        &nbsp;&nbsp;
        <a href='content_list.php?channelid=$channelid' class='btn btn-secondary btn-sm'>管理内容</a>
        ";
    $wintitle = "成功修改内容";
    $wecome_info = "内容管理::修改内容";
    $win = new OxWindow();
    $win->AddTitle("成功修改内容：");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "&nbsp;", false);
    $win->Display();
}
