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
        <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
        <title>选择图片</title>
        <link rel="stylesheet" href="/static/web/css/font-awesome.min.css">
        <link rel="stylesheet" href="/static/web/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/web/css/admin.css">
        <script src="/static/web/js/jquery.min.js"></script>
    </head>
    <body class="p-3">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form name="myform" action="select_images_post.php" method="POST" enctype="multipart/form-data">
                    <?php $noeditor = !empty($noeditor) ? "<input type='hidden' name='noeditor' value='yes'>" : ''; echo $noeditor;?>
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="v" value="<?php echo $v ?>">
                    <input type="hidden" name="iseditor" value="<?php echo $iseditor ?>">
                    <input type="hidden" name="imgstick" value="<?php echo $imgstick ?>">
                    <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>">
                    <input type="hidden" name="job" value="upload">
                    <input type="file" name="imgfile">
                    <label><input type="checkbox" name="needwatermark" value="1" <?php if ($photo_markup == '1') echo 'checked';?>> 水印</label>
                    <label><input type="checkbox" name="resize" value="1"> 缩小</label>
                    <label><input type="text" name="iwidth" value="<?php echo $cfg_ddimg_width ?>" class="admin-input-xs"> 宽</label>
                    <label><input type="text" name="iheight" value="<?php echo $cfg_ddimg_height ?>" class="admin-input-xs"> 高</label>
                    <button type="submit" class="btn btn-success btn-sm">上传</button>
                </form>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">选择图片</div>
            <div class="card-body opt-img">
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
                        $filetime = MyDate("Y-m-d H:i:s", $filetime);
                    }
                    //判断文件类型并作处理
                    if ($file == ".") continue;
                    else if ($file == "..") {
                        if ($activepath == "") continue;
                        $tmp = preg_replace("#[\/][^\/]*$#i", "", $activepath);
                        $line = "<div class='d-flex justify-content-between align-items-center mb-3'>
                            <a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode($tmp).$addparm."' class='btn btn-success btn-sm'>返回上级</a>
                            <span>当前目录：$activepath</span>
                        </div>";
                        echo $line;
                    } else if (is_dir("$inpath/$file")) {
                        if (preg_match("#^_(.*)$#i", $file)) continue;
                        if (preg_match("#^\.(.*)$#i", $file)) continue;
                        $line = "<div class='list dir'>
                            <a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode("$activepath/$file").$addparm."'>
                                <img src='/static/web/img/icon_dir.png'>
                            </a>
                            <span>$file</span>
                        </div>";
                        echo "$line";
                    } else if (preg_match("#\.(".$cfg_imgtype.")#i", $file)) {
                        $reurl = "$activeurl/$file";
                        $reurl = preg_replace("#^\.\.#", "", $reurl);
                        if ($cfg_remote_site == 'Y' && $remoteuploads == 1) {
                           $reurl = $remoteupUrl.$reurl;
                        } else {
                            $reurl = $reurl;
                        }
                        if ($file == $comeback) $lstyle = "class='text-danger'";
                        else $lstyle = '';
                        $line = "<div class='list'>
                            <a href='$reurl' onclick=\"ReturnImg('$reurl');\">
                                <img src='$reurl' title='$file'>
                            </a>
                            <span $lstyle>$file</span>
                        </div>";
                        echo "$line";
                        
                    } else if (preg_match("#\.(jpg)#i", $file)) {
                        $reurl = "$activeurl/$file";
                        $reurl = preg_replace("#^\.\.#", "", $reurl);
                        if ($cfg_remote_site == 'Y' && $remoteuploads == 1) {
                           $reurl = $remoteupUrl.$reurl;
                        } else {
                            $reurl = $reurl;
                        }
                        if ($file == $comeback) $lstyle = "class='text-danger'";
                        else $lstyle = '';
                        $line = "<div class='list'>
                            <a href='$reurl' onclick=\"ReturnImg('$reurl');\">
                                <img src='$reurl' title='$file'>
                            </a>
                            <span $lstyle>$file</span>
                        </div>";
                        echo "$line";
                    }
                }
                ?>
            </div>
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
        </script>
    </body>
</html>