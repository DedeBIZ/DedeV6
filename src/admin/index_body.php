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
/*---------------------------------
载入右边内容
function _getRightSide() {   }
---------------------------------*/
else if ($dopost == 'getRightSide') {
    $query = " SELECT COUNT(*) AS dd FROM `#@__member` ";
    $row1 = $dsql->GetOne($query);
    $query = " SELECT COUNT(*) AS dd FROM `#@__feedback` ";
    $row2 = $dsql->GetOne($query);
    $chArrNames = array();
    $query = "SELECT id, typename FROM `#@__channeltype` ";
    $dsql->Execute('c', $query);
    while ($row = $dsql->GetArray('c')) {
        $chArrNames[$row['id']] = $row['typename'];
    }
    $query = "SELECT COUNT(channel) AS dd, channel FROM `#@__arctiny` GROUP BY channel ";
    $allArc = 0;
    $chArr = array();
    $dsql->Execute('a', $query);
    while ($row = $dsql->GetArray('a')) {
        $allArc += $row['dd'];
        $row['typename'] = $chArrNames[$row['channel']];
        $chArr[] = $row;
    }
?>
    <table width="100%" class="table table-borderless">
        <tr>
            <td class="nline" style="width:50%;text-align:left">会员数：</td>
            <td class="nline" style="text-align:left"><?php echo $row1['dd']; ?></td>
        </tr>
        <tr>
            <td class="nline" style="text-align:left">文档数：</td>
            <td class="nline" style="text-align:left"><?php echo $allArc; ?></td>
        </tr>
        <?php
        foreach ($chArr as $row) {
        ?>
            <tr>
                <td class="nline" style="text-align:left"><?php echo $row['typename']; ?>：</td>
                <td class="nline" style="text-align:left"><?php echo $row['dd']; ?></td>
            </tr>
        <?php
        }
        ?>
        <tr>
            <td style="text-align:left">评论数：</td>
            <td style="text-align:left"><?php echo $row2['dd']; ?></td>
        </tr>
    </table>
<?php
exit();
} else if ($dopost == 'getRightSideNews') {
    $query = "SELECT arc.id, arc.arcrank, arc.title, arc.channel, ch.editcon  FROM `#@__archives` arc
        LEFT JOIN `#@__channeltype` ch ON ch.id = arc.channel
        WHERE arc.arcrank<>-2 ORDER BY arc.id DESC LIMIT 0, 6 ";
    $arcArr = array();
    $dsql->Execute('m', $query);
    while ($row = $dsql->GetArray('m')) {
        $arcArr[] = $row;
    }
    AjaxHead();
?>
    <table width="100%" class="table table-borderless">
        <?php
        foreach ($arcArr as $row) {
            if (trim($row['editcon']) == '') {
                $row['editcon'] = 'archives_edit.php';
            }
            $linkstr = "·<a href='{$row['editcon']}?aid={$row['id']}&channelid={$row['channel']}'>{$row['title']}</a>";
            if ($row['arcrank'] == -1) $linkstr .= "<span style='color:#dc3545'>(未审核)</span>";
        ?>
        <tr>
            <td class="nline"><?php echo $linkstr; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>
<?php
exit;
} else if ($dopost == 'setskin') {
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