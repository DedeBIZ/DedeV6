<?php
/**
 * 会员面板
 * 
 * @version        $id:login.php 8:38 2010年7月9日 tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
$uid = empty($uid) ? "" : RemoveXSS($uid);
if (empty($action)) $action = '';
if (empty($aid)) $aid = '';
$menutype = 'mydede';
if ($uid == '') {
    $iscontrol = 'yes';
    if (!$cfg_ml->IsLogin()) {
        include_once(dirname(__FILE__)."/templets/index-notlogin.htm");
    } else {
        $minfos = $dsql->GetOne("SELECT * FROM `#@__member_tj` WHERE mid='".$cfg_ml->M_ID."';");
        $minfos['totaluse'] = $cfg_ml->GetUserSpace();
        if ($cfg_mb_max > 0) {
            $ddsize = ceil($minfos['totaluse'] / ($cfg_mb_max * 1024 * 1024) * 100);
        } else {
            $ddsize = 0;
        }
        $ddsize = $ddsize > 100? 100 : $ddsize;
        $minfos['totaluse'] = number_format($minfos['totaluse'] / 1024 / 1024, 2);
        require_once(DEDEINC.'/channelunit.func.php');
        //显示最新文档
        $archives = array();
        $sql = "SELECT arc.*, category.namerule, category.typedir, category.moresite, category.siteurl, category.sitepath, mem.userid FROM `#@__archives` arc LEFT JOIN `#@__arctype` category ON category.id=arc.typeid LEFT JOIN `#@__member` mem ON mem.mid=arc.mid WHERE arc.arcrank > -1 ORDER BY arc.sortrank DESC LIMIT 10";
        $dsql->SetQuery($sql);
        $dsql->Execute();
        while ($row = $dsql->GetArray()) {
            $row['htmlurl'] = GetFileUrl($row['id'], $row['typeid'], $row['senddate'], $row['title'], $row['ismake'], $row['arcrank'], $row['namerule'], $row['typedir'], $row['money'], $row['filename'], $row['moresite'], $row['siteurl'], $row['sitepath']);
            $archives[] = $row;
        }
        $dpl = new DedeTemplate();
        $tpl = dirname(__FILE__)."/templets/index.htm";
        $dpl->LoadTemplate($tpl);
        $dpl->display();
    }
} else {
    $_vars = array();
    $uid = HtmlReplace($uid, -1);
    $userid = preg_replace("#[\r\n\t \*%]#", '', $uid);
    $query = "SELECT MB.mid,MB.mtype,MB.userid,MB.uname,MB.sex,MB.rank,MB.email,MB.scores,MB.spacesta,MB.face,MB.logintime,MS.*,MT.*,MB.matt,MR.membername FROM `#@__member` MB LEFT JOIN `#@__member_space` MS on MS.mid=MB.mid LEFT JOIN `#@__member_tj` MT on MT.mid=MB.mid LEFT JOIN `#@__arcrank` MR on MR.rank=MB.rank WHERE MB.userid like '$uid' ";
    $_vars = $dsql->GetOne($query);
    if ($cfg_mb_adminlock == "Y" && $_vars['rank']==10) {
        ShowMsg("无法浏览管理员用户的空间","javascript:;");
        exit();
    }
    if (!is_array($_vars)) {
        ShowMsg("你访问的用户可能已经被删除","javascript:;");
        exit();
    }
    $_vars['face'] = empty($_vars['face'])? $GLOBALS['cfg_mainsite'].'/static/web/img/admin.png' : $_vars['face'];
    $_vars['userid_e'] = urlencode($_vars['userid']);
    $_vars['userurl'] = $cfg_memberurl."/index.php?uid=".$_vars['userid_e'];
    if($_vars['membername']=='开放浏览') $_vars['membername'] = '限制会员';
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_tj` SET homecount=homecount+1 WHERE mid='{$_vars['mid']}' ");
    $tpl = new DedeTemplate();
    $tpl->LoadTemplate(dirname(__FILE__)."/templets/space.htm");
    $tpl->display();
}
?>