<?php if (!defined('DEDEINC')) exit('dedecms');
/**
 * 管理员后台基本函数
 *
 * @version        $Id:inc_fun_funAdmin.php 1 13:58 2010年7月5日Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2020, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */

/**
 *  获取拼音信息
 *
 * @access    public
 * @param     string  $str  字符串
 * @param     int  $ishead  是否为首字母
 * @param     int  $isclose  解析后是否释放资源
 * @return    string
 */
function SpGetPinyin($str, $ishead = 0, $isclose = 1)
{
    global $pinyins;
    global $cfg_bizcore_appid, $cfg_bizcore_key, $cfg_bizcore_hostname, $cfg_bizcore_port;
    global $cfg_soft_lang;
    $restr = '';
    if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
        if ($cfg_soft_lang == "utf-8") {
            $str = gb2utf8($str);
        }
        $client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
        $client->appid = $cfg_bizcore_appid;
        $client->key = $cfg_bizcore_key;
        $data = $client->Pinyin($str, "");
        $restr = $data->data;
        $client->Close();
    } else {
        $str = trim($str);
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (@count($pinyins) == 0) {
            $fp = fopen(DEDEINC . '/data/pinyin.dat', 'r');
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
            }
            fclose($fp);
        }
        for ($i = 0; $i < $slen; $i++) {
            if (ord($str[$i]) > 0x80) {
                $c = $str[$i] . $str[$i + 1];
                $i++;
                if (isset($pinyins[$c])) {
                    if ($ishead == 0) {
                        $restr .= $pinyins[$c];
                    } else {
                        $restr .= $pinyins[$c][0];
                    }
                } else {
                    $restr .= "_";
                }
            } else if (preg_match("/[a-z0-9]/i", $str[$i])) {
                $restr .= $str[$i];
            } else {
                $restr .= "_";
            }
        }
        if ($isclose == 0) {
            unset($pinyins);
        }
    }

    return $restr;
}


/**
 *  创建目录
 *
 * @access    public
 * @param     string  $spath 目录名称
 * @return    string
 */
function SpCreateDir($spath)
{
    global $cfg_dir_purview, $cfg_basedir, $cfg_ftp_mkdir, $isSafeMode;
    if ($spath == '') {
        return true;
    }
    $flink = false;
    $truepath = $cfg_basedir;
    $truepath = str_replace("\\", "/", $truepath);
    $spaths = explode("/", $spath);
    $spath = "";
    foreach ($spaths as $spath) {
        if ($spath == "") {
            continue;
        }
        $spath = trim($spath);
        $truepath .= "/" . $spath;
        if (!is_dir($truepath) || !is_writeable($truepath)) {
            if (!is_dir($truepath)) {
                $isok = MkdirAll($truepath, $cfg_dir_purview);
            } else {
                $isok = ChmodAll($truepath, $cfg_dir_purview);
            }
            if (!$isok) {
                echo "创建或修改目录：" . $truepath . " 失败！<br>";
                CloseFtp();
                return false;
            }
        }
    }
    CloseFtp();
    return true;
}

function jsScript($js)
{
    $out = "<script type=\"text/javascript\">";
    $out .= "//<![CDATA[\n";
    $out .= $js;
    $out .= "\n//]]>";
    $out .= "</script>\n";

    return $out;
}

/**
 *  获取编辑器
 *
 * @access    public
 * @param     string  $fname 表单名称
 * @param     string  $fvalue 表单值
 * @param     string  $nheight 内容高度
 * @param     string  $etype 编辑器类型
 * @param     string  $gtype 获取值类型
 * @param     string  $isfullpage 是否全屏
 * @return    string
 */
function SpGetEditor($fname, $fvalue, $nheight = "350", $etype = "Basic", $gtype = "print", $isfullpage = "false", $bbcode = false)
{
    global $cfg_ckeditor_initialized;
    if (!isset($GLOBALS['cfg_html_editor'])) {
        $GLOBALS['cfg_html_editor'] = 'fck';
    }
    if ($gtype == "") {
        $gtype = "print";
    }
    if ($GLOBALS['cfg_html_editor'] == 'fck') {
        require_once(DEDEINC . '/FCKeditor/fckeditor.php');
        $fck = new FCKeditor($fname);
        $fck->BasePath        = $GLOBALS['cfg_cmspath'] . '/include/FCKeditor/';
        $fck->Width        = '100%';
        $fck->Height        = $nheight;
        $fck->ToolbarSet    = $etype;
        $fck->Config['FullPage'] = $isfullpage;
        if ($GLOBALS['cfg_fck_xhtml'] == 'Y') {
            $fck->Config['EnableXHTML'] = 'true';
            $fck->Config['EnableSourceXHTML'] = 'true';
        }
        $fck->Value = $fvalue;
        if ($gtype == "print") {
            $fck->Create();
        } else {
            return $fck->CreateHtml();
        }
    } else if ($GLOBALS['cfg_html_editor'] == 'ckeditor') {
        $addConfig = "";
        if (defined("DEDEADMIN")) {
            $addConfig = ",{filebrowserImageUploadUrl:'./dialog/select_images_post.php',filebrowserUploadUrl:'./dialog/select_media_post.php?ck=1'}";
            // $addConfig = ",{filebrowserImageUploadUrl:'./dialog/select_images_post.php'}";
        }
        $code = <<<EOT
<script src="{$GLOBALS['cfg_static_dir']}/ckeditor/ckeditor.js"></script>
<textarea id="{$fname}" name="{$fname}" rows="8" cols="60">{$fvalue}</textarea>
<script>
var editor = CKEDITOR.replace('{$fname}'{$addConfig});
</script>
EOT;
        if ($gtype == "print") {
            echo $code;
        } else {
            return $code;
        }
    } else {
        /*
        // ------------------------------------------------------------------------
        // 当前版本,暂时取消dedehtml编辑器的支持
        // ------------------------------------------------------------------------
        require_once(DEDEINC.'/htmledit/dede_editor.php');
        $ded = new DedeEditor($fname);
        $ded->BasePath        = $GLOBALS['cfg_cmspath'].'/include/htmledit/' ;
        $ded->Width        = '100%' ;
        $ded->Height        = $nheight ;
        $ded->ToolbarSet = strtolower($etype);
        $ded->Value = $fvalue ;
        if($gtype=="print")
        {
            $ded->Create();
        }
        else
        {
            return $ded->CreateHtml();
        }
        */
    }
}

/**
 *  获取更新信息
 *
 * @return    void
 */
function SpGetNewInfo()
{
    global $cfg_version_detail, $dsql;
    $nurl = $_SERVER['HTTP_HOST'];
    if (preg_match("#[a-z\-]{1,}\.[a-z]{2,}#i", $nurl)) {
        $nurl = urlencode($nurl);
    } else {
        $nurl = "test";
    }
    $phpv = phpversion();
    $sp_os = PHP_OS;
    $mysql_ver = $dsql->GetVersion();
    $add_query = '';
    $query = "SELECT COUNT(*) AS dd FROM `#@__member` ";
    $row1 = $dsql->GetOne($query);
    if ($row1) $add_query .= "&mcount={$row1['dd']}";
    $query = "SELECT COUNT(*) AS dd FROM `#@__arctiny` ";
    $row2 = $dsql->GetOne($query);
    if ($row2) $add_query .= "&acount={$row2['dd']}";

    $offUrl = DEDEBIZURL . "/version?version={$cfg_version_detail}&formurl={$nurl}&phpver={$phpv}&os={$sp_os}&mysqlver={$mysql_ver}{$add_query}";
    return $offUrl;
}
