<?php
/**
 * 选择图片
 *
 * @version        $id:select_images.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
include(DEDEDATA.'/mark/inc_photowatermark_config.php');
if (empty($activepath)) {
    $activepath = '';
}
if (empty($imgstick)) {
    $imgstick = '';
}
$noeditor = isset($noeditor) ? $noeditor : '';
$iseditor = isset($iseditor) ? intval($iseditor) : '';
$activepath = str_replace('.', '', $activepath);
$activepath = preg_replace("#\/{1,}#", '/', $activepath);
if (strlen($activepath) < strlen($cfg_image_dir)) {
    $activepath = $cfg_image_dir;
}
$inpath = $cfg_basedir.$activepath;
$activeurl = '..'.$activepath;
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
    $addparm = '&CKEditor='.$CKEditor;
    $f = $CKEditor;
}
if (!empty($CKEditorFuncNum)) {
    $addparm .= '&CKEditorFuncNum='.$CKEditorFuncNum;
}
if (!empty($noeditor)) {
    $addparm .= '&noeditor=yes';
}
if (!empty($iseditor)) {
    $addparm .= '&iseditor='.$iseditor;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
        <title>选择图片</title>
        <link rel="stylesheet" href="/static/web/css/font-awesome.min.css">
        <link rel="stylesheet" href="/static/web/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/web/css/admin.css">
        <script src="/static/web/js/jquery.min.js"></script>
    </head>
    <body class="body-bg">
        <div class="upload-box shadow-sm">
            <table align="center" class="table icon">
                <tr>
                    <td colspan="3">
                        <form name="myform" action="select_images_post.php" method="POST" enctype="multipart/form-data">
                            <?php $noeditor = !empty($noeditor) ? "<input type='hidden' name='noeditor' value='yes'>" : ''; echo $noeditor;?>
                            <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                            <input type="hidden" name="f" value="<?php echo $f ?>">
                            <input type="hidden" name="v" value="<?php echo $v ?>">
                            <input type="hidden" name="iseditor" value="<?php echo $iseditor ?>">
                            <input type="hidden" name="imgstick" value="<?php echo $imgstick ?>">
                            <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>">
                            <input type="hidden" name="job" value="upload">
                            <input type="file" name="imgfile" class="w-50">
                            <label><input type="checkbox" name="needwatermark" value="1" <?php if ($photo_markup == '1') echo 'checked';?>> 水印</label>
                            <label><input type="checkbox" name="resize" value="1"> 缩小</label>
                            <label>宽：<input type="text" name="iwidth" value="<?php echo $cfg_ddimg_width ?>" class="admin-input-xs"></label>
                            <label>高：<input type="text" name="iheight" value="<?php echo $cfg_ddimg_height ?>" class="admin-input-xs"></label>
                            <button type="submit" name="sb1" class="btn btn-success btn-sm">上传</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">点击图片预览，再点击图片关闭预览，点击文件名选择图片</td>
                </tr>
                <tr>
                    <td width="40%">选择图片</td>
                    <td width="26%">文件大小</td>
                    <td>修改时间</td>
                </tr>
                <?php
                $dh = scandir($inpath);
                $ty1 = '';
                $ty2 = '';
                foreach ($dh as $file) {
                    //计算文件大小和创建时间
                    if ($file != "." && $file != ".." && !is_dir("$inpath/$file")) {
                        $filesize = filesize("$inpath/$file");
                        $filesize = $filesize / 1024;
                        if ($filesize != "")
                        if ($filesize < 0.1) {
                            @list($ty1, $ty2) = explode("\.", $filesize);
                            $filesize = $ty1.".".substr($ty2, 0, 2);
                        } else {
                            @list($ty1, $ty2) = explode("\.", $filesize);
                            $filesize = $ty1.".".substr($ty2, 0, 1);
                        }
                        $filetime = filemtime("$inpath/$file");
                        $filetime = MyDate("Y-m-d H:i:s", $filetime);
                    }
                    if ($file == ".") continue;
                    else if ($file == "..") {
                        if ($activepath == "") continue;
                        $tmp = preg_replace("#[\/][^\/]*$#i", "", $activepath);
                        $line = "<tr>
                        <td colspan='2'><a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode($tmp).$addparm."'><img src='/static/web/img/icon_dir2.png'> 上级目录</a></td>
                        <td>当前目录：$activepath</td>
                        </tr>";
                        echo $line;
                    } else if (is_dir("$inpath/$file")) {
                        if (preg_match("#^_(.*)$#i", $file)) continue;
                        if (preg_match("#^\.(.*)$#i", $file)) continue;
                        $line = "<tr>
                        <td colspan='3'><a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode("$activepath/$file").$addparm."'><img src='/static/web/img/icon_dir.png'> $file</a></td>
                        </tr>";
                        echo "$line";
                    } else if (preg_match("#\.(".$cfg_imgtype.")#i", $file)) {
                        $reurl = "$activeurl/$file";
                        $reurl = preg_replace("#^\.\.#", "", $reurl);
                        $reurl = $reurl;
                        if ($file == $comeback) $lstyle = "text-danger";
                        else  $lstyle = '';
                        $line = "<tr>
                        <td>
                            <a href='$reurl' onclick=\"ReturnImg('$reurl');\" class=\"tipsimg $lstyle\"><img src='$reurl' title='$file'> $file</a>
                        </td>
                        <td>$filesize KB</td>
                        <td>$filetime</td>
                        </tr>";
                        echo "$line";
                    } else if (preg_match("#\.(jpg)#i", $file)) {
                        $reurl = "$activeurl/$file";
                        $reurl = preg_replace("#^\.\.#", "", $reurl);
                        $reurl = $reurl;
                        if ($file == $comeback) $lstyle = "text-danger";
                        else  $lstyle = '';
                        $line = "<tr>
                        <td><a href='$reurl' onclick=\"ReturnImg('$reurl');\" class=\"tipsimg $lstyle\"><img src='$reurl' title='$file'> $file</a></td>
                        <td>$filesize KB</td>
                        <td>$filetime</td>
                        </tr>";
                        echo "$line";
                    }
                }//End Loop
                ?>
                </tr>
            </table>
        </div>
        <script>
        function nullLink() {
            return;
        }
        //获取地址参数
        function getUrlParam(paramName) {
            var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i');
            var match = window.location.search.match(reParam);
            return (match && match.length > 1) ? match[1] : '';
        }
        function ReturnImg(reimg) {
            var funcNum = getUrlParam('CKEditorFuncNum');
            var iseditor = parseInt(getUrlParam('iseditor'));
            if (funcNum > 1) {
                var fileUrl = reimg;
                window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
            }
            if (iseditor==1) {
                let addonHTML = `<img src='${reimg}'>`;
                window.opener.CKEDITOR.instances["<?php echo $f ?>"].insertHtml(addonHTML);
            } else {
                if (window.opener.document.<?php echo $f ?> != null) {
                    window.opener.document.<?php echo $f ?>.value = reimg;
                    if (window.opener.document.getElementById('div<?php echo $v ?>')) {
                        window.opener.document.getElementById('<?php echo $v ?>').src = reimg;
                    }
                    //适配新的缩略图
                    if (window.opener.document.getElementById('litPic')) {
                        window.opener.document.getElementById('litPic').src = reimg;
                    }
                    if (document.all) window.opener = true;
                } else if (typeof window.opener.CKEDITOR.instances["<?php echo $f ?>"] !== "undefined") {
                    let addonHTML = `<img src='${reimg}'>`;
                    window.opener.CKEDITOR.instances["<?php echo $f ?>"].insertHtml(addonHTML);
                }
            }
            window.close();
        }
        $(function() {
            var x = 10;
            var y = 10;
            $(".tipsimg").mouseover(function(e) {
                var toolimg = "<div id='toolimg'><img src='" + this.href + "'></div>";
                $("body").append(toolimg);
                $("#toolimg").css({
                    "top": (e.pageY + y) + "px",
                    "left": (e.pageX + x) + "px"
                }).show("fast");
            }).mouseout(function() {
                $("#toolimg").remove();
            }).mousemove(function(e) {
                $("#toolimg").css({
                    "top": (e.pageY + y) + "px",
                    "left": (e.pageX + x) + "px"
                });
            });
        });
        </script>
    </body>
</html>