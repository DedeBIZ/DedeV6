<?php
/**
 * 文档页
 *
 * @version        $id:view.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC.'/archive/archives.class.php');
$t1 = ExecTime();
if (empty($okview)) $okview = '';
if (isset($arcID)) $aid = $arcID;
if (!isset($dopost)) $dopost = '';
$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if ($aid == 0) die("dedebiz");
$arc = new Archives($aid);
if ($arc->IsError) ParamError();
//检查阅读权限
$needMoney = $arc->Fields['money'];
$needRank = $arc->Fields['arcrank'];
require_once(DEDEINC.'/memberlogin.class.php');
$cfg_ml = new MemberLogin();
if ($needRank < 0 && $arc->Fields['mid'] != $cfg_ml->M_ID) {
    ShowMsg('文档未审核，暂时无法浏览', 'javascript:;');
    exit();
}
//设置了权限限制的文档
//arctitle msgtitle moremsg
if ($needMoney > 0 || $needRank > 1) {
    $arctitle = $arc->Fields['title'];
    $arclink = $cfg_phpurl.'/view.php?aid='.$arc->ArcID;
    $arcLinktitle = "<a href=\"{$arclink}\">".$arctitle."</a>";
    $description =  $arc->Fields["description"];
    $pubdate = GetDateTimeMk($arc->Fields["pubdate"]);
    //会员级别不足
    if (($needRank > 1 && $cfg_ml->M_Rank < $needRank && $arc->Fields['mid'] != $cfg_ml->M_ID)) {
        $dsql->Execute('me', "SELECT * FROM `#@__arcrank`");
        while ($row = $dsql->GetObject('me')) {
            $memberTypes[$row->rank] = $row->membername;
        }
        $memberTypes[0] = "游客或没权限会员";
        $msgtitle = "您没有权限浏览文档：{$arctitle} ";
        $moremsg = "该文档需要<span class='text-primary'>".$memberTypes[$needRank]."</span>才能浏览，您目前等级是<span class='text-primary'>".$memberTypes[$cfg_ml->M_Rank]."</span>";
        include_once(DEDETEMPLATE.'/apps/view_msg.htm');
        exit();
    }
    //需要金币的情况
    if ($needMoney > 0  && $arc->Fields['mid'] != $cfg_ml->M_ID) {
        $sql = "SELECT aid,money FROM `#@__member_operation` WHERE buyid='ARCHIVE".$aid."' AND mid='".$cfg_ml->M_ID."'";
        $row = $dsql->GetOne($sql);
        //未购买过此文档
        if (!is_array($row)) {
            if ($cfg_ml->M_Money == '' || $needMoney > $cfg_ml->M_Money) {
                $msgtitle = "您没有权限浏览文档：{$arctitle} ";
                $moremsg = "该文档需要<span class='text-primary'>".$needMoney."</span>金币才能浏览，您目前金币<span class='text-primary'>".$cfg_ml->M_Money."</span><a class='btn btn-success btn-sm ml-2' href='{$GLOBALS['cfg_cmsurl']}/user/buy.php' target='_blank'>前去充值</a>";
                include_once(DEDETEMPLATE.'/apps/view_msg.htm');
                $arc->Close();
                exit();
            } else {
                if ($dopost == 'buy') {
                    $inquery = "INSERT INTO `#@__member_operation` (mid,oldinfo,money,mtime,buyid,product,pname,sta) VALUES ('".$cfg_ml->M_ID."','$arctitle','$needMoney','".time()."','ARCHIVE".$aid."','archive','购买内容',2); ";
                    if ($dsql->ExecuteNoneQuery($inquery)) {
                        $inquery = "UPDATE `#@__member` SET money=money-$needMoney WHERE mid='".$cfg_ml->M_ID."'";
                        if (!$dsql->ExecuteNoneQuery($inquery)) {
                            showmsg('购买失败, 请返回', -1);
                            exit;
                        }
                        showmsg('购买成功，购买扣点不会重扣金币', $GLOBALS['cfg_cmsurl'].'/apps/view.php?aid='.$aid);
                        exit;
                    } else {
                        showmsg('购买失败，请返回', -1);
                        exit;
                    }
                }
                $msgtitle = "扣金币购买阅读";
                $moremsg = "该文档需要付费<span class='text-primary'>".$needMoney."</span>金币，您目前金币<span class='text-primary'>".$cfg_ml->M_Money."</span><a class='btn btn-success btn-sm ml-2' href='{$GLOBALS['cfg_cmsurl']}/apps/view.php?aid=".$aid."&dopost=buy' target='_blank'>确认付点阅读</a>";
                include_once($cfg_basedir.$cfg_templets_dir."/apps/view_msg.htm");
                $arc->Close();
                exit();
            }
        }
    } //金币处理付处理
}
$arc->Display();
if (DEBUG_LEVEL === TRUE) {
    $queryTime = ExecTime() - $t1;
    echo "<div style='position:relative;padding:.75rem 1.25rem;margin-bottom:1rem;width:auto;font-size:14px;color:#721c24;background:#f8d7da;border-color:#f5c6cb;border:1px solid transparent;border-radius:.5rem'>页面加载总消耗时间：{$queryTime}</div>\r\n";
}
?>