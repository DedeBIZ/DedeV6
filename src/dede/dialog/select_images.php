<?php
/**
 * 图片选择框
 *
 * @version        $Id: select_images.php 1 9:43 2010年7月8日Z tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2021, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__) . "/config.php");
include(DEDEDATA . '/mark/inc_photowatermark_config.php');
if (empty($activepath)) {
    $activepath = '';
}
if (empty($imgstick)) {
    $imgstick = '';
}
$noeditor = isset($noeditor) ? $noeditor : '';
$activepath = str_replace('.', '', $activepath);
$activepath = preg_replace("#\/{1,}#", '/', $activepath);
if (strlen($activepath) < strlen($cfg_medias_dir)) {
    $activepath = $cfg_medias_dir;
}
$inpath = $cfg_basedir . $activepath;
$activeurl = '..' . $activepath;
if (empty($f)) {
    $f = 'form1.picname';
}
$f = RemoveXSS($f);
if (empty($v)) {
    $v = 'picview';
}
if (empty($comeback)) {
    $comeback = '';
}
$addparm = '';
if (!empty($CKEditor)) {
    $addparm = '&CKEditor=' . $CKEditor;
    $f = $CKEditor;
}
if (!empty($CKEditorFuncNum)) {
    $addparm .= '&CKEditorFuncNum=' . $CKEditorFuncNum;
}
if (!empty($noeditor)) {
    $addparm .= '&noeditor=yes';
}
?>
<html>
<head>
    <meta charset="<?php echo $cfg_soft_lang; ?>">
    <title>图片浏览器</title>
    <link rel="stylesheet" href="../../static/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/base.css">
    <style>
html{background:#f2f2f2}
body{margin:0;line-height:22px;font:12px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif}
a{text-decoration:none!important}
table{background:#fff}
.bg{margin:10px;border-radius:2px;box-shadow:0 1px 2px 0 rgba(0,0,0,.05)}
.linerow{border-bottom:1px solid #eee!important}
.napisdiv{left:40;top:10;width:150px;height:100px;position:absolute;z-index:3;display:none}
    </style>
    <script>
    function nullLink() {
        return;
    }
    function ChangeImage(surl) {
        document.getElementById('picview').src = surl;
    }
    </script>
</head>
<body class="bg">
    <div id="floater" class="napisdiv">
        <a href="javascript:nullLink();" onClick="document.getElementById('floater').style.display='none';"><img src="img/picviewnone.gif" id='picview' alt="关闭预览"></a>
    </div>
    <script src="../js/float.js"></script>
    <script>
    function nullLink() {
        return;
    }
    function ChangeImage(surl) {
        document.getElementById('floater').style.display = 'block';
        document.getElementById('picview').src = surl;
    }
    function TNav() {
        if (window.navigator.userAgent.indexOf("MSIE") >= 1) return 'IE';
        else if (window.navigator.userAgent.indexOf("Firefox") >= 1) return 'FF';
        else return "OT";
    }
    //获取地址参数
    function getUrlParam(paramName) {
        var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i');
        var match = window.location.search.match(reParam);
        return (match && match.length > 1) ? match[1] : '';
    }
    function ReturnImg(reimg) {
        var funcNum = getUrlParam('CKEditorFuncNum');
        if (funcNum > 1) {
            var fileUrl = reimg;
            window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
        }
        if (window.opener.document.<?php echo $f ?> != null) {
            window.opener.document.<?php echo $f ?>.value = reimg;
            if (window.opener.document.getElementById('div<?php echo $v ?>')) {
                if (TNav() == 'IE') {
                    //window.opener.document.getElementById('div<?php echo $v ?>').filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = reimg;
                    window.opener.document.getElementById('div<?php echo $v ?>').src = reimg;
                    window.opener.document.getElementById('div<?php echo $v ?>').style.width = '150px';
                    window.opener.document.getElementById('div<?php echo $v ?>').style.height = '100px';
                } else
                    window.opener.document.getElementById('div<?php echo $v ?>').style.backgroundImage = "url(" + reimg + ")";
                } else if (window.opener.document.getElementById('<?php echo $v ?>')) {
                window.opener.document.getElementById('<?php echo $v ?>').src = reimg;
            }
            //适配新的缩略图
            if (window.opener.document.getElementById('litPic')) {
                window.opener.document.getElementById('litPic').src = reimg;
            }
            if (document.all) window.opener = true;
        }
        window.close();
    }
    </script>
    <table width="100%" border="0" cellpadding="0" cellspacing="1" align="center" class="table table-borderless">
        <tr>
            <td colspan="4">
                <table width="100%" border="0" cellspacing="0" cellpadding="2">
                    <tr>
                        <td width="10%" class="linerow">预览</td>
                        <td width="40%" class="linerow">选择图片</td>
                        <td width="20%" class="linerow">文件大小</td>
                        <td width="30%" class="linerow">修改时间</td>
                    </tr>
                    <tr>
                        <td class="linerow" colspan="4">点击V预览图片，点击图片名选择图片，显示图片后点击该图片关闭预览</td>
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
                                    $filesize = $ty1 . "." . substr($ty2, 0, 2);
                                } else {
                                    @list($ty1, $ty2) = split("\.", $filesize);
                                    $filesize = $ty1 . "." . substr($ty2, 0, 1);
                                }
                            $filetime = filemtime("$inpath/$file");
                            $filetime = MyDate("Y-m-d H:i", $filetime);
                        }
                        if ($file == ".") continue;
                        else if ($file == "..") {
                            if ($activepath == "") continue;
                            $tmp = preg_replace("#[\/][^\/]*$#i", "", $activepath);
                            $line = "\n<tr>
                            <td class='linerow' colspan='2'>
                            <a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=" . urlencode($tmp) . $addparm . "'><img src='img/dir2.gif'>上级目录</a></td>
                            <td colspan='2' class='linerow'>当前目录:$activepath</td>
                            </tr>";
                            echo $line;
                        } else if (is_dir("$inpath/$file")) {
                            if (preg_match("#^_(.*)$#i", $file)) continue; #屏蔽FrontPage扩展目录和linux隐蔽目录
                            if (preg_match("#^\.(.*)$#i", $file)) continue;
                            $line = "\n<tr>
                            <td class='linerow' colspan='2'>
                            <a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=" . urlencode("$activepath/$file") . $addparm . "'><img src='img/dir.gif'>$file</a></td>
                            <td class='linerow'></td>
                            <td class='linerow'></td>
                            </tr>";
                            echo "$line";
                        } else if (preg_match("#\.(" . $cfg_imgtype . ")#i", $file)) {
                            $reurl = "$activeurl/$file";
                            $reurl = preg_replace("#^\.\.#", "", $reurl);
                            $reurl = $reurl;
                            if ($file == $comeback) $lstyle = " style='color:red' ";
                            else  $lstyle = "";
                            $line = "\n<tr>
                            <td class='linerow'><a href=\"#\" onClick=\"ChangeImage('$reurl');\"><img src='img/picviewnone.gif'></a>
                            </td>
                            <td class='linerow'><a href=# onclick=\"ReturnImg('$reurl');\" $lstyle><img src='img/gif.gif'>$file</a></td>
                            <td class='linerow'>$filesize KB</td>
                            <td class='linerow'>$filetime</td>
                            </tr>";
                            echo "$line";
                        } else if (preg_match("#\.(jpg)#i", $file)) {
                            $reurl = "$activeurl/$file";
                            $reurl = preg_replace("#^\.\.#", "", $reurl);
                            $reurl = $reurl;
                            if ($file == $comeback) $lstyle = " style='color:red' ";
                            else  $lstyle = "";
                            $line = "\n<tr>
                            <td class='linerow'><a href=\"#\" onClick=\"ChangeImage('$reurl');\"><img src='img/picviewnone.gif'></a></td>
                            <td class='linerow'><a href=# onclick=\"ReturnImg('$reurl');\" $lstyle><img src='img/jpg.gif'>$file</a></td>
                            <td class='linerow'>$filesize KB</td>
                            <td class='linerow'>$filetime</td>
                            </tr>";
                            echo "$line";
                        }
                    } //End Loop
                    $dh->close();
                    ?>
                    <tr>
                        <td colspan="4">
                            <table width="100%">
                                <form action="select_images_post.php" method="POST" enctype="multipart/form-data" name="myform">
                                    <?php $noeditor = !empty($noeditor) ? "<input type='hidden' name='noeditor' value='yes'>" : '';
                                    echo $noeditor; ?>
                                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                                    <input type="hidden" name="f" value="<?php echo $f ?>">
                                    <input type="hidden" name="v" value="<?php echo $v ?>">
                                    <input type="hidden" name="imgstick" value="<?php echo $imgstick ?>">
                                    <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1; ?>">
                                    <input type="hidden" name="job" value="upload">
                                    <tr>
                                        <td>
                                            上传：<input type="file" name="imgfile" style="width:160px;border:none">
                                            <label><input type="checkbox" name="needwatermark" value="1" class="np" <?php if ($photo_markup == '1') echo "checked"; ?> /> 水印 </label>
                                            <label><input type="checkbox" name="resize" value="1" class="np"> 缩小 </label>
                                            宽：<input type="text" name="iwidth" value="<?php echo $cfg_ddimg_width ?>" style="width:46px">
                                            高：<input type="text" name="iheight" value="<?php echo $cfg_ddimg_height ?>" style="width:46px">
                                            <button type="submit" name="sb1" class="btn btn-success btn-sm">确定</button>
                                        </td>
                                    </tr>
                                </form>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>