<?php
/**
 * 多媒体选择
 *
 * @version        $Id: select_media.php 1 9:43 2010年7月8日Z tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($activepath)) {
    $activepath = '';
}
$noeditor = isset($noeditor) ? $noeditor : '';
$activepath = str_replace('.', '', $activepath);
$activepath = preg_replace("#\/{1,}#", '/', $activepath);
if (strlen($activepath) < strlen($cfg_other_medias)) {
    $activepath = $cfg_other_medias;
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
$addparm = '';
if (!empty($CKEditor)) {
    $addparm = '&CKEditor='.$CKEditor;
}
if (!empty($CKEditorFuncNum)) {
    $addparm .= '&CKEditorFuncNum='.$CKEditorFuncNum;
}
if (!empty($noeditor)) {
    $addparm .= '&noeditor=yes';
}
?>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=<?php echo $cfg_soft_lang; ?>'>
    <title>选择多媒体</title>
    <link rel="stylesheet" href="../../static/web/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/web/font/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../static/web/css/admin.css">
    <style>
html{background:#f6f6f6}
table{background:#fff}
a{text-decoration:none!important}
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
        if (window.opener.document.<?php echo $f ?> != null) {
            window.opener.document.<?php echo $f ?>.value = reimg;
        }
        var funcNum = <?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1; ?>;
        if (window.opener.CKEDITOR != null && funcNum != 1) {
            window.opener.CKEDITOR.tools.callFunction(funcNum, reimg);
        }
        window.close();
    }
    </script>
    <table width="100%" align="center" cellspacing="0" cellpadding="2" class="table table-borderless">
        <tr>
            <td colspan="3" height="26">
                <form action="select_media_post.php" method="POST" enctype="multipart/form-data" name="myform">
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="job" value="upload">
                    <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1; ?>">
                    上传：<input type="file" name="uploadfile" style="width:50%;border:none">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm">保存</button>
                </form>
            </td>
        </tr>
        <tr>
            <td width="55%" align="center" class="linerow">点击名称选择文件</td>
            <td width="15%" align="center" class="linerow">文件大小</td>
            <td width="30%" align="center" class="linerow">最后修改时间</td>
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
                $tmp = preg_replace("#[\/][^\/]*$#i", "", $activepath);
                $line = "<tr>
                <td class='linerow'><a href='select_media.php?f=$f&activepath=".urlencode($tmp).$addparm."'><img src='../../static/web/img/dir2.gif'>上级目录</a></td>
                <td colspan='2' class='linerow'>当前目录：$activepath</td>
                </tr>";
                echo $line;
            } else if (is_dir("$inpath/$file")) {
                if (preg_match("#^_(.*)$#i", $file)) continue;
                if (preg_match("#^\.(.*)$#i", $file)) continue;
                $line = "<tr>
                <td class='linerow'><a href=select_media.php?f=$f&activepath=".urlencode("$activepath/$file").$addparm."><img src='../../static/web/img/dir.gif'>$file</a></td>
                <td class='linerow'></td>
                <td class='linerow'></td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(swf|fly|fla|flv)#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><img src='../../static/web/img/flash.gif'>$file</a></td>
                <td class='linerow'>$filesize KB</td>
                <td align='center' class='linerow'>$filetime</td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(wmv|avi)#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><img src='../../static/web/img/wmv.gif'>$file</a></td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow' align='center'>$filetime</td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(rm|rmvb|mp3|mp4)#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><img src='../../static/web/img/rm.gif'>$file</a></td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow' align='center'>$filetime</td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(mp3|wma)#", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><img src='../../static/web/img/mp3.gif'>$file</a></td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow' align='center'>$filetime</td>
                </tr>";
                echo "$line";
            }
        }//End Loop
        $dh->close();
        ?>
    </table>
</body>
</html>