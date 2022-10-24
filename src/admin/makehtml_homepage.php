<?php
/**
 * 生成首页
 *
 * @version        $Id: makehtml_homepage.php 2 9:30 2010-11-11 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
use DedeBIZ\Archive\PartView;
use DedeBIZ\libraries\DedeBIZ;
use DedeBIZ\Login\UserLogin;
require_once(dirname(__FILE__)."/config.php");
UserLogin::CheckPurview('sys_MakeHtml');
if (empty($dopost)) $dopost = '';
if ($dopost == "view") {
    $pv = new PartView();
    $templet = str_replace("{style}", $cfg_df_style, $templet);
    $pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$templet);
    $pv->Display();
    exit();
} else if ($dopost == "make") {
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBIZ($cfg_bizcore_hostname, $cfg_bizcore_port);
        $client->appid = $cfg_bizcore_appid;
        $client->key = $cfg_bizcore_key;
        $data = $client->AdminPWDExists();
        $data = json_decode($data->data);
        if ($data) {
            $rs = (array)($data->result);
            if ($rs["admin_pwd_exists"] == "false") {
                //设定dedebiz admin密码
                if ($dedebiz_admin == "" || $dedebiz_admin !== $re_dedebiz_admin) {
                    echo DedeAlert(Lang("admin_auth_pwd_not_same"),ALERT_DANGER);
                    $client->Close();
                    exit;
                }
                $data = $client->AdminPWDCreate($dedebiz_admin);
                if ($data->data != "ok") {
                    echo DedeAlert(Lang("admin_auth_err_pwd",array('data'=>$data)),ALERT_DANGER);
                    $client->Close();
                    exit;
                }
            } else {
                if ($dedebiz_admin == "") {
                    echo DedeAlert(Lang("admin_auth_err_pwd_isempty"),ALERT_DANGER);
                    $client->Close();
                    exit;
                }
                $data = $client->AdminSetIndexLockState($dedebiz_admin, $lockindex);
                if ($data->data != "ok") {
                    echo DedeAlert(Lang("admin_auth_err_pwd_failed"),ALERT_DANGER);
                    $client->Close();
                    exit;
                }
            }
        }
        $client->Close();
    }
    $remotepos = empty($remotepos) ? '/index.html' : $remotepos;
    $serviterm = empty($serviterm) ? "" : $serviterm;
    if (preg_match('#\.(php|pl|cgi|asp|aspx|jsp|php5|php4|php3|shtm|shtml)$#i', trim($position))) {
        ShowMsg(Lang("media_ext_forbidden"), "javascript:;");
        exit();
    }
    $homeFile = DEDEADMIN."/".$position;
    $homeFile = str_replace("\\", "/", $homeFile);
    $homeFile = str_replace("//", "/", $homeFile);
    $fp = fopen($homeFile, "w") or die(DedeAlert(Lang('makehtml_homepage_err_filename'),ALERT_DANGER));
    fclose($fp);
    if ($saveset == 1) {
        $iquery = "UPDATE `#@__homepageset` SET templet='$templet',position='$position'";
        $dsql->ExecuteNoneQuery($iquery);
    }
    //判断首页生成模式
    if ($showmod == 1) {
        //需要生成静态
        $templet = str_replace("{style}", $cfg_df_style, $templet);
        $pv = new PartView();
        $GLOBALS['_arclistEnv'] = 'index';
        $pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$templet);
        $pv->SaveToHtml($homeFile);
        echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
        echo "<div class=\"alert alert-success\">".Lang('makehtml_homepage_success_make')."：".$position." <a href='{$position}' target='_blank' class='btn btn-success btn-sm'>".Lang('browser')."</a></div>";
    } else {
        //动态浏览
        if (file_exists($homeFile)) @unlink($homeFile);
        echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position: static;}</style>";
        echo "<div class=\"alert alert-success\">".Lang('makehtml_homepage_success_nomake')."：<a href='../index.php' target='_blank' class='btn btn-success btn-sm'>".Lang('browser')."</a></div>";
    }
    $iquery = "UPDATE `#@__homepageset` SET showmod='$showmod'";
    $dsql->ExecuteNoneQuery($iquery);
    if ($serviterm == "") {
        $config = array();
    } else {
        list($servurl, $servuser, $servpwd) = explode(',', $serviterm);
        $config = array(
            'hostname' => $servurl, 'username' => $servuser,
            'password' => $servpwd, 'debug' => 'TRUE'
        );
    }
    exit();
}
$row  = $dsql->GetOne("SELECT * FROM `#@__homepageset`");
include DedeInclude('templets/makehtml_homepage.htm');
?>