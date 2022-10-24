<?php
if (!defined('DEDEINC')) exit('dedebiz');
/**
 * 文档操作处理函数
 * 
 * @version        $Id: inc_batchup.php 1 13:52 2010年7月9日Z tianya $
 * @package        DedeBIZ.User
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(DEDEINC."/channelunit.func.php");
/**
 *  删除文档
 *
 * @access    public
 * @param     int  $aid  文档id
 * @return    bool
 */
function DelArc($aid)
{
    global $dsql, $cfg_cookie_encode, $cfg_ml, $cfg_upload_switch, $cfg_medias_dir;
    $aid = intval($aid);
    //读取文档信息
    $arctitle = '';
    $arcurl = '';
    $arcQuery = "SELECT arc.*,ch.addtable,tp.typedir,tp.typename,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,ch.nid FROM `#@__archives` arc LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        return false;
    }
    //删除数据库的内容
    $dsql->ExecuteNoneQuery(" DELETE FROM `#@__arctiny` WHERE id='$aid' ");
    if ($arcRow['addtable'] != '') {
        //判断删除文档附件变量是否开启；
        if ($cfg_upload_switch == 'Y') {
            //判断文档属性；
            switch ($arcRow['nid']) {
                case "image":
                    $nid = "imgurls";
                    break;
                case "article":
                    $nid = "body";
                    break;
                case "soft":
                    $nid = "softlinks";
                    break;
                case "shop":
                    $nid = "body";
                    break;
                default:
                    $nid = "";
                    break;
            }
            if ($nid != "") {
                $row = $dsql->GetOne("SELECT $nid FROM ".$arcRow['addtable']." WHERE aid = '$aid'");
                $licp = $dsql->GetOne("SELECT litpic FROM `#@__archives` WHERE id = '$aid'");
                if ($licp['litpic'] != "") {
                    $litpic = DEDEROOT.$licp['litpic'];
                    if (file_exists($litpic) && !is_dir($litpic)) {
                        @unlink($litpic);
                    }
                }
                $tmpname = '/(\\'.$cfg_medias_dir.'.+?)(\"| )/';
                //取出文档附件
                preg_match_all("$tmpname", $row["$nid"], $delname);
                //移出重复附件
                $delname = array_unique($delname['1']);
                foreach ($delname as $var) {
                    $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE url='$var' AND mid = '$cfg_ml->M_ID'");
                    $upname = DEDEROOT.$var;
                    if (file_exists($upname) && !is_dir($upname)) @unlink($upname);
                }
            }
        }
        $dsql->ExecuteNoneQuery("DELETE FROM `".$arcRow['addtable']."` where aid='$aid' ");
    }
    $dsql->ExecuteNoneQuery(" DELETE FROM `#@__archives` where id='$aid' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__taglist ` where aid='$aid'");
    //删除HTML
    if ($arcRow['ismake'] == -1 || $arcRow['arcrank'] != 0 || $arcRow['typeid'] == 0 || $arcRow['money'] > 0) {
        return TRUE;
    }
    $arcurl = GetFileUrl(
        $arcRow['id'],
        $arcRow['typeid'],
        $arcRow['senddate'],
        $arcRow['title'],
        $arcRow['ismake'],
        $arcRow['arcrank'],
        $arcRow['namerule'],
        $arcRow['typedir'],
        $arcRow['money'],
        $arcRow['filename']
    );
    if (!preg_match("#\?#", $arcurl)) {
        $htmlfile = GetTruePath().str_replace($GLOBALS['cfg_basehost'], '', $arcurl);
        if (file_exists($htmlfile) && !is_dir($htmlfile)) {
            @unlink($htmlfile);
            $arcurls = explode(".", $htmlfile);
            $sname = $arcurls[count($arcurls) - 1];
            $fname = preg_replace("#(\.$sname)$#", "", $htmlfile);
            for ($i = 2; $i <= 100; $i++) {
                $htmlfile = $fname."_$i".".".$sname;
                if (file_exists($htmlfile) && !is_dir($htmlfile)) @unlink($htmlfile);
                else break;
            }
        }
    }
    //删除文本文件
    $filename = DEDEDATA."/textdata/".(ceil($aid / 5000))."/{$aid}-".substr(md5($cfg_cookie_encode), 0, 16).".txt";
    if (is_file($filename)) @unlink($filename);
    return TRUE;
}
/**
 *  删除不带主表内容模型的数据
 *
 * @access    public
 * @param     int  $aid  文档id
 * @return    string
 */
function DelArcSg($aid)
{
    global $dsql, $cfg_cookie_encode, $cfg_ml, $cfg_upload_switch, $cfg_medias_dir;
    $aid = intval($aid);
    //读取文档信息
    $arctitle = '';
    $arcurl = '';
    $arcQuery = "SELECT arc.id,arc.typeid,arc.senddate,arc.arcrank,ch.addtable,ch.nid,tp.typedir,tp.typename,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath FROM `#@__arctiny` arc LEFT JOIN `#@__arctype` tp on tp.id=arc.typeid LEFT JOIN `#@__channeltype` ch on ch.id=arc.channel WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($arcQuery);
    if (!is_array($arcRow)) {
        return FALSE;
    }
    //删除数据库的内容
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` where id='$aid' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `".$arcRow['addtable']."` where aid='$aid' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__taglist ` where aid='$aid'");
    //删除HTML
    if ($arcRow['arcrank'] != 0 || $arcRow['typeid'] == 0) {
        return TRUE;
    }
    $arcurl = GetFileUrl(
        $arcRow['id'],
        $arcRow['typeid'],
        $arcRow['senddate'],
        '',
        1,
        $arcRow['arcrank'],
        $arcRow['namerule'],
        $arcRow['typedir'],
        0,
        ''
    );
    if (!preg_match("#\?#", $arcurl)) {
        $htmlfile = GetTruePath().str_replace($GLOBALS['cfg_basehost'], '', $arcurl);
        if (file_exists($htmlfile) && !is_dir($htmlfile)) {
            @unlink($htmlfile);
            $arcurls = explode(".", $htmlfile);
            $sname = $arcurls[count($arcurls) - 1];
            $fname = preg_replace("#(\.$sname)$#", "", $htmlfile);
            for ($i = 2; $i <= 100; $i++) {
                $htmlfile = $fname."_$i".".".$sname;
                if (file_exists($htmlfile) && !is_dir($htmlfile)) @unlink($htmlfile);
                else break;
            }
        }
    }
    //删除文本文件
    $filenameh = DEDEDATA."/textdata/".(ceil($aid / 5000))."/{$aid}-".substr(md5($cfg_cookie_encode), 0, 16).".txt";
    return TRUE;
}
/**
 *  获取真实路径
 *
 * @return    string
 */
function GetTruePath()
{
    $truepath = $GLOBALS["cfg_basedir"];
    return $truepath;
}