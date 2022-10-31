<?php
/**
 * 模板选择
 *
 * @version        $Id: select_templets.php 1 9:43 2010年7月8日 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($activepath)) {
    $activepath = '';
}
$cfg_txttype = 'htm|html|tpl|txt|dtp';
$activepath = str_replace('.', '', $activepath);
$activepath = preg_replace("#\/{1,}#", '/', $activepath);
$templetdir  = $cfg_templets_dir;
if (strlen($activepath) < strlen($templetdir)) {
    $activepath = $templetdir;
}
$inpath = $cfg_basedir.$activepath;
$activeurl = '..'.$activepath;
if (!is_dir($inpath)) {
    die('No Exsits Path');
}
if (empty($f)) {
    $f = 'form1.enclosure';
}
if (empty($comeback)) {
    $comeback = '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang;?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <title>选择模板</title>
    <link rel="stylesheet" href="../../static/web/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/web/font/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../static/web/css/admin.css">
    <style>
html{background:#f8f8f8}
.bg{margin:10px;border-radius:.2rem;box-shadow:0 1px 2px 0 rgba(0,0,0,.05)}
.biz-td{border-bottom:1px solid #eee!important}
    </style>
</head>
<body class="bg">
    <script>
    function nullLink() {
        return;
    }
    function ReturnValue(reimg) {
        window.opener.document.<?php echo $f ?>.value = reimg;
        if (document.all) window.opener = true;
        window.close();
    }
    </script>
    <table width="100%" cellpadding="0" cellspacing="1" align="center" class="table table-borderless icon">
        <tr>
            <td colspan="3">
                <form action="select_templets_post.php" method="POST" enctype="multipart/form-data" name="myform">
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="job" value="upload">
                    上传：<input type="file" name="uploadfile" style="width:50%;border:none">
                    改名：<input type="text" name="filename" class="biz-input-sm">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm">保存</button>
                </form>
            </td>
        </tr>
        <tr>
            <td width="50%" class="biz-td">选择文件</td>
            <td width="20%" class="biz-td">文件大小</td>
            <td class="biz-td">修改时间</td>
        </tr>
        <?php
        $dh = scandir($inpath);
        $ty1 = "";
        $ty2 = "";
        foreach ($dh as $file) {
            //计算文件大小和创建时间
            if ($file != "." && $file != ".." && !is_dir("$inpath/$file")) {
                $filesize = filesize("$inpath/$file");
                $filesize = $filesize / 1024;
                if ($filesize != "")
                    if ($filesize < 0.1) {
                        @list($ty1, $ty2) = split("\.", $filesize);
                        $filesize = $ty1.".".substr($ty2, 0, 2);
                    } else {
                        @list($ty1, $ty2) = split("\.", $filesize);
                        $filesize = $ty1.".".substr($ty2, 0, 1);
                    }
                    $filetime = filemtime("$inpath/$file");
                    $filetime = MyDate("Y-m-d H:i", $filetime);
                }
                //判断文件类型并作处理
                if ($file == ".") continue;
                else if ($file == "..") {
                    if ($activepath == "") continue;
                    $tmp = preg_replace("#[\/][^\/]*$#", "", $activepath);
                    $line = "<tr>
                    <td class='biz-td'><a href='select_templets.php?f=$f&activepath=".urlencode($tmp)."'><img src='../../static/web/img/dir2.gif'>上级目录</a></td>
                    <td colspan='2' class='biz-td'>当前目录：$activepath</td>
                    </tr>\r\n";
                      echo $line;
                } else if (is_dir("$inpath/$file")) {
                    if (preg_match("#^_(.*)$#i", $file)) continue;
                    if (preg_match("#^\.(.*)$#i", $file)) continue;
                    $line = "<tr>
                    <td class='biz-td'><a href=select_templets.php?f=$f&activepath=".urlencode("$activepath/$file")."><img src='../../static/web/img/dir.gif'>$file</a></td>
                    <td class='biz-td'></td>
                    <td class='biz-td'></td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(htm|html)#i", $file)) {
                    if ($file == $comeback) $lstyle = "class='text-danger'";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='biz-td'>
                        <img src='../../static/web/img/htm.gif'>
                        <a href=\"javascript:ReturnValue('$reurl');\" $lstyle>$file</a>
                    </td>
                    <td class='biz-td'>$filesize KB</td>
                    <td class='biz-td'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(css)#i", $file)) {
                    if ($file == $comeback) $lstyle = "class='text-danger'";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."/#", "", $reurl);
                    $line = "<tr>
                    <td class='biz-td'>
                        <img src='../../static/web/img/css.gif'>
                        <a href=\"javascript:ReturnValue('$reurl');\" $lstyle>$file</a>
                    </td>
                    <td class='biz-td'>$filesize KB</td>
                    <td class='biz-td'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(js)#i", $file)) {
                    if ($file == $comeback) $lstyle = "class='text-danger'";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='biz-td'>
                        <img src='../../static/web/img/js.gif'>
                        <a href=\"javascript:ReturnValue('$reurl');\" $lstyle>$file</a>
                    </td>
                    <td class='biz-td'>$filesize KB</td>
                    <td class='biz-td'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(jpg)#i", $file)) {
                    if ($file == $comeback) $lstyle = "class='text-danger'";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='biz-td'>
                        <img src='$reurl'>
                        <a href=\"javascript:ReturnValue('$reurl');\" $lstyle>$file</a>
                    </td>
                    <td class='biz-td'>$filesize KB</td>
                    <td class='biz-td'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(gif|png)#i", $file)) {
                    if ($file == $comeback) $lstyle = "class='text-danger'";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='biz-td'>
                        <img src='$reurl'>
                        <a href=\"javascript:ReturnValue('$reurl');\" $lstyle>$file</a>
                    </td>
                    <td class='biz-td'>$filesize KB</td>
                    <td class='biz-td'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(txt)#i", $file)) {
                    if ($file == $comeback) $lstyle = "class='text-danger'";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='biz-td'>
                        <img src='../../static/web/img/txt.gif'>
                        <a href=\"javascript:ReturnValue('$reurl');\" $lstyle>$file</a>
                    </td>
                    <td class='biz-td'>$filesize KB</td>
                    <td class='biz-td'>$filetime</td></tr>";
                    echo "$line";
                }
            }//End Loop
            ?>
        </table>
    </body>
</html>