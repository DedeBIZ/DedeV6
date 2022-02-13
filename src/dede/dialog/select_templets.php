<?php
/**
 * 模板选择框
 *
 * @version        $Id: select_templets.php 1 9:43 2010年7月8日Z tianya $
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
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <title>模板管理器</title>
    <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/base.css">
    <style>
html{background:#f2f2f2}
body{margin:0;line-height:1.5;font:12px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif}
a{text-decoration:none!important}
table{background:#fff}
.bg{margin:10px;border-radius:.2rem;box-shadow:0 1px 2px 0 rgba(0,0,0,.05)}
.linerow{border-bottom:1px solid #eee!important}
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
    <table width="100%" border="0" cellpadding="0" cellspacing="1" align="center" class="table table-borderless">
        <tr>
            <td width="50%" class="linerow">选择文件</td>
            <td width="20%" class="linerow">文件大小</td>
            <td width="30%" class="linerow">修改时间</td>
        </tr>
        <?php
        $dh = dir($inpath);
        $ty1 = "";
        $ty2 = "";
        while ($file = $dh->read()) {
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
                    $line = "\n<tr>
                    <td class='linerow'><a href='select_templets.php?f=$f&activepath=".urlencode($tmp)."'><img src='img/dir2.gif'>上级目录</a></td>
                    <td colspan='2' class='linerow'>当前目录:$activepath</td>
                    </tr>\r\n";
                      echo $line;
                } else if (is_dir("$inpath/$file")) {
                    if (preg_match("#^_(.*)$#i", $file)) continue;
                    if (preg_match("#^\.(.*)$#i", $file)) continue;
                    $line = "\n<tr>
                    <td class='linerow'><a href=select_templets.php?f=$f&activepath=".urlencode("$activepath/$file")."><img src='img/dir.gif'>$file</a></td>
                    <td class='linerow'></td>
                    <td class='linerow'></td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(htm|html)#i", $file)) {
                    if ($file == $comeback) $lstyle = " style='color:red' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "\n<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='img/htm.gif'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(css)#i", $file)) {
                    if ($file == $comeback) $lstyle = " style='color:red' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."/#", "", $reurl);
                    $line = "\n<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='img/css.gif'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(js)#i", $file)) {
                    if ($file == $comeback) $lstyle = " style='color:red' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "\n<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='img/js.gif'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(jpg)#i", $file)) {
                    if ($file == $comeback) $lstyle = " style='color:red' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "\n<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='img/jpg.gif'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(gif|png)#i", $file)) {
                    if ($file == $comeback) $lstyle = " style='color:red' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "\n<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='img/gif.gif'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(txt)#i", $file)) {
                    if ($file == $comeback) $lstyle = " style='color:red' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "\n<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='img/txt.gif'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td></tr>";
                    echo "$line";
                }
            }//End Loop
            $dh->close();
            ?>
            <tr>
                <td colspan="3">
                    <table width="100%">
                        <form action="select_templets_post.php" method="POST" enctype="multipart/form-data" name="myform">
                        <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                        <input type="hidden" name="f" value="<?php echo $f ?>">
                        <input type="hidden" name="job" value="upload">
                            <tr>
                                <td>
                                    上传：<input type="file" name="uploadfile" style="width:260px;border:none">
                                    改名：<input type="text" name="filename" style="width:160px">
                                    <button type="submit" name="sb1" class="btn btn-success btn-sm">确定</button>
                                </td>
                            </tr>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>