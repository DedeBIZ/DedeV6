<?php
/**
 * 下载
 *
 * @version        $Id: download.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC."/channelunit.class.php");
if (!isset($open)) $open = 0;
//读取链接列表
if ($open == 0) {
    $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
    if ($aid == 0) exit('dedebiz');
    $arcRow = GetOneArchive($aid);
    if ($arcRow['aid'] == '') {
        ShowMsg('无法获取未知文档的信息!', '-1');
        exit();
    }
    extract($arcRow, EXTR_SKIP);
    $cu = new ChannelUnit($arcRow['channel'], $aid);
    if (!is_array($cu->ChannelFields)) {
        ShowMsg('获取文档信息失败', '-1');
        exit();
    }
    $vname = '';
    foreach ($cu->ChannelFields as $k => $v) {
        if ($v['type'] == 'softlinks') {
            $vname = $k;
            break;
        }
    }
    $row = $dsql->GetOne("SELECT $vname FROM `".$cu->ChannelInfos['addtable']."` WHERE aid='$aid'");
    include_once(DEDEINC.'/taglib/channel/softlinks.lib.php');
    $ctag = '';
    $downlinks = ch_softlinks($row[$vname], $ctag, $cu, '', TRUE);
    require_once(DEDETEMPLATE.'/plus/download_links_templet.htm');
    exit();
}
/*------------------------
//提供软件给用户下载(旧模式)
function getSoft_old()
------------------------*/
else if ($open == 1) {
    //更新下载次数
    $id = isset($id) && is_numeric($id) ? $id : 0;
    $link = base64_decode(urldecode($link));
    $linkinfo = parse_url($link);
    if (!$link) {
        ShowMsg('无效地址', 'javascript:;');
        exit;
    }
    $hash = md5($link);
    $rs = $dsql->ExecuteNoneQuery2("UPDATE `#@__downloads` SET downloads = downloads + 1 WHERE hash='$hash' ");
    if ($rs <= 0) {
        $query = " INSERT INTO `#@__downloads`(`hash`,`id`,`downloads`) VALUES('$hash','$id',1); ";
        $dsql->ExecNoneQuery($query);
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__softconfig` ");
    $sites = explode("\n", $row['sites']);
    $allowed = array();
    foreach ($sites as $site) {
        $site = explode('|', $site);
        $domain = parse_url(trim($site[0]));
        if ($domain['host']) {
            $allowed[] = $domain['host'];
        }
    }
    if (!in_array($linkinfo['host'], $allowed)) {
        ShowMsg('非下载地址，禁止访问', 'javascript:;');
        exit;
    }
    header("location:$link");
    exit();
}
/*------------------------
//提供软件给用户下载(新模式)
function getSoft_new()
------------------------*/
else if ($open == 2) {
    $id = intval($id);
    //获得附加表信息
    $row = $dsql->GetOne("SELECT ch.addtable,arc.mid FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$id' ");
    if (empty($row['addtable'])) {
        ShowMsg('找不到所需要的软件资源', 'javascript:;');
        exit();
    }
    $mid = $row['mid'];
    //读取连接列表、下载权限信息
    $row = $dsql->GetOne("SELECT softlinks,daccess,needmoney FROM `{$row['addtable']}` WHERE aid='$id' ");
    if (empty($row['softlinks'])) {
        ShowMsg('找不到所需要的软件资源', 'javascript:;');
        exit();
    }
    $softconfig = $dsql->GetOne("SELECT * FROM `#@__softconfig` ");
    $needRank = $softconfig['dfrank'];
    $needMoney = $softconfig['dfywboy'];
    if ($softconfig['argrange'] == 0) {
        $needRank = $row['daccess'];
        $needMoney = $row['needmoney'];
    }
    //分析连接列表
    require_once(DEDEINC.'/dedetag.class.php');
    $softUrl = '';
    $islocal = 0;
    $dtp = new DedeTagParse();
    $dtp->LoadSource($row['softlinks']);
    if (!is_array($dtp->CTags)) {
        $dtp->Clear();
        ShowMsg('找不到所需要的软件资源', 'javascript:;');
        exit();
    }
    foreach ($dtp->CTags as $ctag) {
        if ($ctag->GetName() == 'link') {
            $link = trim($ctag->GetInnerText());
            $islocal = $ctag->GetAtt('islocal');
            //分析本地链接
            if (!isset($firstLink) && $islocal == 1) $firstLink = $link;
            if ($islocal == 1 && $softconfig['islocal'] != 1) continue;

            //支持http,迅雷下载,ftp,flashget
            if (!preg_match("#^http:\/\/|^thunder:\/\/|^ftp:\/\/|^flashget:\/\/#i", $link)) {
                $link = $cfg_mainsite.$link;
            }
            $dbhash = substr(md5($link), 0, 24);
            if ($uhash == $dbhash) $softUrl = $link;
        }
    }
    $dtp->Clear();
    if (
        $softUrl == '' && $softconfig['ismoresite'] == 1
        && $softconfig['moresitedo'] == 1 && trim($softconfig['sites']) != '' && isset($firstLink)
    ) {
        $firstLink = preg_replace("#http:\/\/([^\/]*)\/#i", '/', $firstLink);
        $softconfig['sites'] = preg_replace("#[\r\n]{1,}#", "\n", $softconfig['sites']);
        $sites = explode("\n", trim($softconfig['sites']));
        foreach ($sites as $site) {
            if (trim($site) == '') continue;
            list($link, $serverName) = explode('|', $site);
            $link = trim(preg_replace("#\/$#", "", $link)).$firstLink;
            $dbhash = substr(md5($link), 0, 24);
            if ($uhash == $dbhash) $softUrl = $link;
        }
    }
    if ($softUrl == '') {
        ShowMsg('找不到所需要的软件资源', 'javascript:;');
        exit();
    }
    //读取文档信息，判断权限
    $arcRow = GetOneArchive($id);
    if ($arcRow['aid'] == '') {
        ShowMsg('无法获取未知文档的信息!', '-1');
        exit();
    }
    extract($arcRow, EXTR_SKIP);
    //处理需要下载权限的软件
    if ($needRank > 0 || $needMoney > 0) {
        require_once(DEDEINC.'/memberlogin.class.php');
        $cfg_ml = new MemberLogin();
        $arclink = $arcurl;
        $arctitle = $title;
        $arcLinktitle = "<a href=\"{$arcurl}\">".$arctitle."</a>";
        $pubdate = GetDateTimeMk($pubdate);
        //会员级别不足
        if (($needRank > 1 && $cfg_ml->M_Rank < $needRank && $mid != $cfg_ml->M_ID)) {
            $dsql->Execute('me', "SELECT * FROM `#@__arcrank` ");
            while ($row = $dsql->GetObject('me')) {
                $memberTypes[$row->rank] = $row->membername;
            }
            $memberTypes[0] = "游客";
            $msgtitle = "您没有权限下载软件：{$arctitle}";
            $moremsg = "这个软件需要 <span class='text-danger'>".$memberTypes[$needRank]."</span> 才能下载，您目前是：<span class='text-danger'>".$memberTypes[$cfg_ml->M_Rank]."</span> ";
            include_once(DEDETEMPLATE.'/plus/view_msg.htm');
            exit();
        }
        //以下为正常情况，自动扣点数
        //如果文章需要金币，检查用户是否浏览过本文档
        if ($needMoney > 0  && $mid != $cfg_ml->M_ID) {
            $sql = "SELECT aid,money FROM `#@__member_operation` WHERE buyid='ARCHIVE".$id."' AND mid='".$cfg_ml->M_ID."'";
            $row = $dsql->GetOne($sql);
            //未购买过此文章
            if (!is_array($row)) {
                //没有足够的金币
                if ($needMoney > $cfg_ml->M_Money || $cfg_ml->M_Money == '') {
                    $msgtitle = "您没有权限下载软件：{$arctitle}";
                    $moremsg = "这个软件需要 <span class='text-danger'>".$needMoney." 金币</span> 才能下载，您目前拥有金币：<span class='text-danger'>".$cfg_ml->M_Money." 个</span> ";
                    include_once(DEDETEMPLATE.'/plus/view_msg.htm');
                    exit(0);
                }
                //有足够金币，记录用户信息
                $inquery = "INSERT INTO `#@__member_operation`(mid,oldinfo,money,mtime,buyid,product,pname,sta)
                  VALUES ('".$cfg_ml->M_ID."','$arctitle','$needMoney','".time()."', 'ARCHIVE".$id."', 'archive','下载软件', 2); ";
                //记录定单
                if (!$dsql->ExecuteNoneQuery($inquery)) {
                    ShowMsg('记录定单失败, 请返回', '-1');
                    exit(0);
                }
                //扣除金币
                $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET money = money - $needMoney WHERE mid='".$cfg_ml->M_ID."'");
            }
        }
    }
    //更新下载次数
    $hash = md5($softUrl);
    $rs = $dsql->ExecuteNoneQuery2("UPDATE `#@__downloads` SET downloads = downloads+1 WHERE hash='$hash' ");
    if ($rs <= 0) {
        $query = " INSERT INTO `#@__downloads`(`hash`, `id`, `downloads`) VALUES('$hash', '$id', 1); ";
        $dsql->ExecNoneQuery($query);
    }
    header("location:{$softUrl}");
    exit();
}//opentype=2