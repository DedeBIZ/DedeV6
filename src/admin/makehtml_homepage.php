<?php
/**
 * 更新首页
 *
 * @version        $id:makehtml_homepage.php 2 9:30 2010-11-11 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_MakeHtml');
require_once(DEDEINC."/archive/partview.class.php");
if (empty($dopost)) $dopost = '';
if ($dopost == "view") {
    $pv = new PartView();
    $templet = str_replace("{style}", $cfg_df_style, $templet);
    $pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$templet);
    $pv->Display();
    exit();
} else if ($dopost == "make") {
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        $client = new DedeBizClient();
        $data = $client->AdminPWDExists();
        $data = json_decode($data->data);
        if ($data) {
            $rs = (array)($data->result);
            if ($rs["admin_pwd_exists"] == "false") {
                //设定dedebiz admin密码
                if ($dedebiz_admin == "" || $dedebiz_admin !== $re_dedebiz_admin) {
                    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position:static}</style>";
                    echo "<div class=\"alert alert-danger\">DedeBIZ操作密码为空或两次指定的密码不符</div><br>";
                    $client->Close();
                    exit;
                }
                $data = $client->AdminPWDCreate($dedebiz_admin);
                if ($data->data != "ok") {
                    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position:static}</style>";
                    echo "<div class=\"alert alert-danger\">DedeBIZ设定操作密码失败：${$data}</div><br>";
                    $client->Close();
                    exit;
                }
            } else {
                if ($dedebiz_admin == "") {
                    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position:static}</style>";
                    echo "<div class=\"alert alert-danger\">DedeBIZ操作密码为空</div><br>";
                    $client->Close();
                    exit;
                }
                $data = $client->AdminSetIndexLockState($dedebiz_admin, $lockindex);
                if ($data->data != "ok") {
                    echo "<link rel=\"stylesheet\" href=\"{$cfg_cmsurl}/static/web/css/bootstrap.min.css\"><style>.modal {position:static}</style>";
                    echo "<div class=\"alert alert-danger\">DedeBIZ操作密码失败，填写正确的操作密码</div><br>";
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
        ShowMsg("文件扩展名已被系统禁止", "javascript:;");
        exit();
    }
    $homeFile = DEDEADMIN."/".$position;
    $homeFile = str_replace("\\", "/", $homeFile);
    $homeFile = str_replace("//", "/", $homeFile);
    $fp = fopen($homeFile, "w") or die("您指定的文件名有问题，无法创建文件");
    fclose($fp);
    if ($saveset == 1) {
        $iquery = "UPDATE `#@__homepageset` SET templet='$templet',position='$position' ";
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
        ShowMsg("更新静态首页，<a href='$position' target='_blank'>点击浏览</a>", "javascript:;");
    } else {
        //动态浏览
        if (file_exists($homeFile)) @unlink($homeFile);
        ShowMsg("更新动态首页，<a href='../index.php' target='_blank'>点击浏览</a>", "javascript:;");
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