<?php
/**
 * 图片选择
 *
 * @version        $Id: select_images.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
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
$iseditor = isset($iseditor) ? $iseditor : '';
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
    <meta charset="<?php echo $cfg_soft_lang;?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <title>选择图片</title>
    <link rel="stylesheet" href="../../static/web/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/web/font/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../static/web/css/admin.css">
    <style>
html{background:#f6f6f6}
.bg{margin:10px;border-radius:.2rem;box-shadow:0 1px 2px 0 rgba(0,0,0,.05)}
.napisdiv{left:10;top:10;width:150px;height:100px;position:absolute;z-index:3;display:none}
.linerow{border-bottom:1px solid #eee!important}
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
        <a href="javascript:nullLink();" onClick="document.getElementById('floater').style.display='none';"><img src="../../static/web/img/picviewnone.gif" id="picview" title="关闭预览"></a>
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
            } else if (typeof window.opener.CKEDITOR.instances["<?php echo $f ?>"] !== "undefined") {
                let addonHTML = `<img src='${reimg}'>`;
                window.opener.CKEDITOR.instances["<?php echo $f ?>"].insertHtml(addonHTML);
            }
        }
        window.close();
    }
    </script>
    <table width="100%" cellpadding="0" cellspacing="1" align="center" class="table table-borderless icon">
        <tr>
            <td colspan="4">
                <form action="select_images_post.php" method="POST" enctype="multipart/form-data" name="myform">
                    <?php $noeditor = !empty($noeditor) ? "<input type='hidden' name='noeditor' value='yes'>" : ''; echo $noeditor;?>
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="v" value="<?php echo $v ?>">
                    <input type="hidden" name="imgstick" value="<?php echo $imgstick ?>">
                    <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>">
                    <input type="hidden" name="job" value="upload">
                    上传：<input type="file" name="imgfile" style="width:50%;border:none">
                    <label><input type="checkbox" name="needwatermark" value="1" class="np" <?php if ($photo_markup == '1') echo "checked";?> /> 水印 </label>
                    <label><input type="checkbox" name="resize" value="1" class="np"> 缩小 </label>
                    宽：<input type="text" name="iwidth" value="<?php echo $cfg_ddimg_width ?>" style="width:50px">
                    高：<input type="text" name="iheight" value="<?php echo $cfg_ddimg_height ?>" style="width:50px">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm">上传</button>
                </form>
            </td>
        </tr>
        <tr>
            <td class="linerow" colspan="4">点击图片预览，点击图片名选择图片，显示图片后点击该图片关闭预览</td>
        </tr>
        <tr>
            <td width="8%" class="linerow">预览</td>
            <td width="42%" class="linerow">选择图片</td>
            <td width="20%" class="linerow">文件大小</td>
            <td class="linerow">修改时间</td>
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
                        @list($ty1, $ty2) = explode("\.", $filesize);
                        $filesize = $ty1.".".substr($ty2, 0, 2);
                    } else {
                        @list($ty1, $ty2) = explode("\.", $filesize);
                        $filesize = $ty1.".".substr($ty2, 0, 1);
                    }
                $filetime = filemtime("$inpath/$file");
                $filetime = MyDate("Y-m-d H:i", $filetime);
            }
            if ($file == ".") continue;
            else if ($file == "..") {
                if ($activepath == "") continue;
                $tmp = preg_replace("#[\/][^\/]*$#i", "", $activepath);
                $line = "<tr>
                <td colspan='2' class='linerow'><a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode($tmp).$addparm."'><img src='../../static/web/img/dir2.gif'>上级目录</a></td>
                <td colspan='2' class='linerow'>当前目录：$activepath</td>
                </tr>";
                echo $line;
            } else if (is_dir("$inpath/$file")) {
                if (preg_match("#^_(.*)$#i", $file)) continue;
                if (preg_match("#^\.(.*)$#i", $file)) continue;
                $line = "<tr>
                <td colspan='2' class='linerow'><a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode("$activepath/$file").$addparm."'><img src='../../static/web/img/dir.gif'>$file</a></td>
                <td class='linerow'></td>
                <td class='linerow'></td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(".$cfg_imgtype.")#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = "class='text-danger'";
                else  $lstyle = "";
                $line = "<tr>
                <td colspan='2' class='linerow'>
                    <a href=\"javascript:;\" onClick=\"ChangeImage('$reurl');\"><img src='$reurl'></a>
                    <a href=\"javascript:;\" onclick=\"ReturnImg('$reurl');\" $lstyle>$file</a>
                </td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow'>$filetime</td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(jpg)#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = "class='text-danger'";
                else  $lstyle = "";
                $line = "<tr>
                <td colspan='2' class='linerow'>
                    <a href=\"javascript:;\" onClick=\"ChangeImage('$reurl');\"><img src='$reurl'></a>
                    <a href=\"javascript:;\" onclick=\"ReturnImg('$reurl');\" $lstyle>$file</a>
                </td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow'>$filetime</td>
                </tr>";
                echo "$line";
            }
        }//End Loop
        ?>
        </tr>
    </table>
</body>
</html>