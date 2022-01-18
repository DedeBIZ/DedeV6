<?php

/**
 * 单表模型发布器
 * 
 * @version        $Id: archives_sg_add.php 1 13:52 2010年7月9日Z tianya $
 * @package        DedeBIZ.Member
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/userlogin.class.php");
require_once(DEDEINC."/customfields.func.php");
require_once(dirname(__FILE__)."/inc/inc_catalog_options.php");
require_once(dirname(__FILE__)."/inc/inc_archives_functions.php");
$channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 1;
$typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
$mtypesid = isset($mtypesid) && is_numeric($mtypesid) ? $mtypesid : 0;
$menutype = 'content';

/*-------------
function _ShowForm(){  }
--------------*/
if (empty($dopost)) {
    $cInfos = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid'; ");
    if (!is_array($cInfos)) {
        ShowMsg('模型不存在', '-1');
        exit();
    }

    //如果限制了会员级别或类型，则允许游客投稿选项无效
    if ($cInfos['sendrank'] > 0 || $cInfos['usertype'] != '') {
        CheckRank(0, 0);
    }

    //检查会员等级和类型限制
    if ($cInfos['sendrank'] > $cfg_ml->M_Rank) {
        $row = $dsql->GetOne("SELECT membername FROM `#@__arcrank` WHERE rank='".$cInfos['sendrank']."' ");
        ShowMsg("对不起，需要[".$row['membername']."]才能在这个频道发布文档！", "-1", "0", 5000);
        exit();
    }
    if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
        ShowMsg("对不起，需要[".$cInfos['usertype']."帐号]才能在这个频道发布文档！", "-1", "0", 5000);
        exit();
    }
    include(DEDEMEMBER."/templets/archives_sg_add.htm");
    exit();
}
/*------------------------------
function _SaveArticle(){  }
------------------------------*/ else if ($dopost == 'save') {
    include_once(DEDEINC."/image.func.php");
    include_once(DEDEINC."/oxwindow.class.php");
    $svali = GetCkVdValue();
    if (preg_match("/3/", $safe_gdopen)) {
        if (strtolower($vdcode) != $svali || $svali == '') {
            ResetVdValue();
            ShowMsg('验证码错误！', '-1');
            exit();
        }
    }

    // 校验CSRF
    CheckCSRF();

    $flag = '';
    $autokey = $remote = $dellink = $autolitpic = 0;
    $userip = GetIP();

    if ($typeid == 0) {
        ShowMsg('请指定文档隶属的栏目！', '-1');
        exit();
    }

    $query = "SELECT tp.ispart,tp.channeltype,tp.issend,ch.issend AS cissend,ch.sendrank,ch.arcsta,ch.addtable,ch.fieldset,ch.usertype
          FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$typeid' ";
    $cInfos = $dsql->GetOne($query);

    //检测栏目是否有投稿权限
    if ($cInfos['issend'] != 1 || $cInfos['ispart'] != 0  || $cInfos['channeltype'] != $channelid || $cInfos['cissend'] != 1) {
        ShowMsg("你所选择的栏目不支持投稿！", "-1");
        exit();
    }

    //检查频道设定的投稿许可权限
    if ($cInfos['sendrank'] > $cfg_ml->M_Rank) {
        $row = $dsql->GetOne("Select membername From #@__arcrank where rank='".$cInfos['sendrank']."' ");
        ShowMsg("对不起，需要[".$row['membername']."]才能在这个频道发布文档！", "-1", "0", 5000);
        exit();
    }

    if ($cInfos['usertype'] != '' && $cInfos['usertype'] != $cfg_ml->M_MbType) {
        ShowMsg("对不起，需要[".$cInfos['usertype']."]才能在这个频道发布文档！", "-1", "0", 5000);
        exit();
    }

    //文档的默认状态
    if ($cInfos['arcsta'] == 0) {
        $arcrank = 0;
    } else if ($cInfos['arcsta'] == 1) {
        $arcrank = 0;
    } else {
        $arcrank = -1;
    }

    //对保存的内容进行处理
    $sortrank = $senddate = $pubdate = time();
    $title = cn_substrR(HtmlReplace($title, 1), $cfg_title_maxlen);
    $mid = $cfg_ml->M_ID;
    $description = empty($description) ? "" : $description;

    //分析处理附加表数据
    $inadd_f = $inadd_v = '';
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
                if (!isset(${$vs[0]})) {
                    ${$vs[0]} = '';
                }

                //自动摘要和远程图片本地化
                if ($vs[1] == 'htmltext' || $vs[1] == 'textdata') {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]}, $description, $vs[1]);
                }

                ${$vs[0]} = GetFieldValueA(${$vs[0]}, $vs[1], 0);

                $inadd_f .= ',`'.$vs[0].'`';
                $inadd_v .= " ,'".${$vs[0]}."' ";
            }
        }

        // 这里对前台提交的附加数据进行一次校验
        $fontiterm = PrintAutoFieldsAdd($cInfos['fieldset'], 'autofield', FALSE);
        if ($fontiterm != str_replace('`', '', $inadd_f)) {
            ShowMsg("提交表单同系统配置不相符,请重新提交！", "-1");
            exit();
        }
    }

    //生成文档ID
    $arcID = GetIndexKey($arcrank, $typeid, $sortrank, $channelid, $senddate, $mid);
    if (empty($arcID)) {
        ShowMsg("无法获得主键，因此无法进行后续操作！", "-1");
        exit();
    }

    //保存到附加表
    $addtable = trim($cInfos['addtable']);
    if (empty($addtable)) {
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
        ShowMsg("没找到当前模型[{$channelid}]的主表信息，无法完成操作。", "javascript:;");
        exit();
    } else {
        $inquery = "INSERT INTO `{$addtable}`(aid,typeid,arcrank,mid,channel,title,senddate,litpic,userip{$inadd_f}) Values('$arcID','$typeid','$arcrank','$mid','$channelid','$title','$senddate','','$userip'{$inadd_v})";
        if (!$dsql->ExecuteNoneQuery($inquery)) {
            $gerr = $dsql->GetError();
            $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$arcID'");
            ShowMsg("把数据保存到数据库附加表 `{$addtable}` 时出错，请联系管理员！", "javascript:;");
            exit();
        }
    }

    //增加积分
    $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET scores=scores+{$cfg_sendarc_scores} WHERE mid='".$cfg_ml->M_ID."' ; ");

    //生成HTML
    $artUrl = MakeArt($arcID, true);
    if ($artUrl == '') $artUrl = $cfg_phpurl."/view.php?aid=$arcID";

    ClearMyAddon($arcID, $title);

    //返回成功信息
    $msg = "
    　　请选择你的后续操作：
        <a href='archives_sg_add.php?channelid=$channelid' class='btn btn-secondary btn-sm'>继续发布内容</a>
        &nbsp;&nbsp;
        <a href='$artUrl' target='_blank' class='btn btn-secondary btn-sm'>查看内容</a>
        &nbsp;&nbsp;
        <a href='archives_sg_edit.php?channelid=$channelid&aid=$arcID' class='btn btn-secondary btn-sm'>更改内容</a>
        &nbsp;&nbsp;
        <a href='content_sg_list.php?channelid={$channelid}' class='btn btn-secondary btn-sm'>已发布内容管理</a>
        ";
    $wintitle = "成功发布内容！";
    $wecome_info = "内容管理::发布内容";
    $win = new OxWindow();
    $win->AddTitle("成功发布内容：");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand", "&nbsp;", false);
    $win->Display();
}
