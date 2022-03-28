<?php
/**
 * 管理后台首页主体
 *
 * @version        $Id: index_body.php 1 11:06 2010年7月13日Z tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__).'/config.php');
require(DEDEINC.'/image.func.php');
require(DEDEINC.'/dedetag.class.php');
//默认主页
if (empty($dopost)) {
    require(DEDEINC.'/inc/inc_fun_funAdmin.php');
    $verLockFile = DEDEDATA.'/admin/ver.txt';
    $fp = fopen($verLockFile, 'r');
    $upTime = trim(fread($fp, 64));
    fclose($fp);
    $oktime = substr($upTime, 0, 4).'-'.substr($upTime, 4, 2).'-'.substr($upTime, 6, 2);
    $offUrl = SpGetNewInfo();
    $dedecmsidc = DEDEDATA.'/admin/idc.txt';
    $fp = fopen($dedecmsidc, 'r');
    $dedeIDC = fread($fp, filesize($dedecmsidc));
    fclose($fp);
    include DedeInclude('templets/index_body.htm');
    exit();
}
else if ($dopost == 'setskin') {
    $cskin = empty($cskin) ? 1 : $cskin;
    $skin = !in_array($cskin, array(1, 2, 3, 4)) ? 1 : $cskin;
    $skinconfig = DEDEDATA.'/admin/skin.txt';
    PutFile($skinconfig, $skin);
} elseif ($dopost == 'get_seo') {
    //直接采用DedeBIZ重写方法
    exit;
} elseif ($dopost == "system_info") {
    if (!extension_loaded("openssl")) {
        echo json_encode(array(
            "code" => -1001,
            "msg" => "PHP不支持OpenSSL，无法完成商业版授权",
            "result" => null,
        ));
        exit;
    }
    if (empty($cfg_auth_code)) {
        echo json_encode(array(
            "code" => -1002,
            "msg" => "当前站点尚未购买商业版授权",
            "result" => null,
        ));
        exit;
    }
    openssl_public_decrypt(base64_decode($cfg_auth_code), $decotent, DEDEPUB);
    $core_info = new stdClass;
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
        $client->appid = $cfg_bizcore_appid;
        $client->key = $cfg_bizcore_key;
        $core_info = $client->SystemInfo();
        $client->Close();
    }
    if (!empty($decotent)) {
        $res = json_decode($decotent);
        if (isset($res->sid)) {
            echo json_encode(array(
                "code" => 200,
                "msg" => "",
                "result" => array(
                    "domain" => $res->domain,
                    "title" => $res->title,
                    "stype" => $res->stype == 1 ? "企业单位" : "个人",
                    "auth_version" => $res->auth_version,
                    "auth_at" => date("Y-m-d", $res->auth_at),
                    "core" => $core_info,
                ),
            ));
        }
    }
} elseif ($dopost == 'get_statistics') {
    require_once(DEDEINC."/libraries/statistics.class.php");
    //获取统计信息
    $sdate = empty($sdate) ? 0 : intval($sdate);
    $stat = new DedeStatistics;
    $rs = $stat->GetInfoByDate($sdate);
    echo json_encode(array(
        "code" => 200,
        "msg" => "",
        "result" => $rs,
    ));
    exit;
} 
?>