<?php
/**
 * 管理后台首页主体
 *
 * @version        $id:index_body.php 11:06 2010年7月13日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
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
} elseif ($dopost == 'get_articles'){
?>
<table class="table table-borderless">
    <?php
    $query = "SELECT arc.id, arc.arcrank, arc.title, arc.typeid, arc.pubdate, arc.channel, ch.editcon, tp.typename FROM `#@__archives` arc LEFT JOIN `#@__channeltype` ch ON ch.id = arc.channel LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id WHERE arc.arcrank<>-2 ORDER BY arc.id DESC LIMIT 0,12";
    $arcArr = array();
    $dsql->Execute('m', $query);
    while($row = $dsql->GetArray('m'))
    {
        $arcArr[] = $row;
    }
    ?>
    <?php
    if (count($arcArr) > 1)
    {
        foreach($arcArr as $row)
        {
            if (trim($row['editcon'])==''){
                $row['editcon'] = 'archives_edit.php';
            }
            $rowarcrank = $row['arcrank']==-1? " <span class='text-danger'>[未审核]</span>":"";
            $pubdate = GetDateMk($row['pubdate']);
            echo "<tr><td><a href='{$row['editcon']}?aid={$row['id']}&channelid={$row['channel']}'>{$row['title']}</a>{$rowarcrank}</td><td width='90'>{$pubdate}</td></tr>";
        }
    } else {
    ?>
    <tr><td colspan="2">暂无文档</td></tr>
    <?php }?>
</table>
<?php
    exit;
} elseif ($dopost == "system_info") {
    if (!extension_loaded("openssl")) {
        echo json_encode(array(
            "code" => -1001,
            "msg" => "PHP不支持OpenSSL，无法完成商业版授权。",
            "result" => null,
        ));
        exit;
    }
    if (empty($cfg_auth_code)) {
        echo json_encode(array(
            "code" => -1002,
            "msg" => "您的站点已授权社区版，获取官方技术支持，请选择<a href='https://www.dedebiz.com/auth' class='text-primary'>商业版</a>。",
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
}  elseif ($dopost == 'get_statistics_multi') {
    require_once(DEDEINC."/libraries/statistics.class.php");
    //获取统计信息
    $sdates = empty($sdates) ? array() : explode(",",preg_replace("[^\d\,]","",$sdates)) ;
    $stat = new DedeStatistics;
    $rs = $stat->GetInfoByDateMulti($sdates);
    echo json_encode(array(
        "code" => 200,
        "msg" => "",
        "result" => $rs,
    ));
    exit;
} elseif ($dopost == 'safe_mode') {
    $safemsg = "系统运行模式为安全模式，模板管理、标签管理、数据库管理、模块管理等功能已暂停，如果您需要这些功能，在/system/common.inc.php文件大约第10行代码找到DEDEBIZ_SAFE_MODE后面值TRUE修改为FALSE恢复使用";
    $unsafemsg = "系统运行模式为开发模式，模板管理、标签管理、数据库管理、模块管理等功能已恢复，如果您不需要这些功能，在/system/common.inc.php文件大约第10行代码找到DEDEBIZ_SAFE_MODE后面值FALSE修改为TRUE暂停使用";
    $modeStr = DEDEBIZ_SAFE_MODE? $safemsg : $unsafemsg;
    ShowMsg($modeStr, "javascript:;");
    exit;
}
?>