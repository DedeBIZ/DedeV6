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
$activeurl = $activepath;
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
    <title><?php echo Lang('dialog_select_image');?></title>
    <link rel="stylesheet" href="../../static/web/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/web/font/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../static/web/css/admin.min.css">
    <style>
html{background:#f8f8f8}
.bg{margin:10px;border-radius:.2rem;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}
.napisdiv{left:10;top:10;width:150px;height:100px;position:absolute;z-index:3;display:none}
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
        var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)','i');
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
    <div id="floater" class="napisdiv">
        <a href="javascript:nullLink();" onClick="document.getElementById('floater').style.display='none';"><img src="" id="picview" title="关闭预览"></a>
    </div>
    <table width="100%" align="center" cellpadding="0" cellspacing="1" class="table table-borderless">
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
                    <?php echo Lang('upload');?>：<input type="file" name="imgfile" style="width:46%;border:0">
                    <label><input type="checkbox" name="needwatermark" value="1" <?php if ($photo_markup == '1') echo "checked";?> /> <?php echo Lang('watermark');?></label>
                    <label><input type="checkbox" name="resize" value="1"> <?php echo Lang('zoom_out');?></label>
                    <?php echo Lang('width');?>：<input type="text" name="iwidth" value="<?php echo $cfg_ddimg_width ?>" style="width:46px">
                    <?php echo Lang('height');?>：<input type="text" name="iheight" value="<?php echo $cfg_ddimg_height ?>" style="width:46px">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm"><?php echo Lang('upload');?></button>
                </form>
            </td>
        </tr>
        <tr>
            <td width="50%" class="linerow"><?php echo Lang('preview');?><?php echo Lang('dialog_select_image');?></td>
            <td width="25%" class="linerow"><?php echo Lang('dialog_filesize');?></td>
            <td width="25%" class="linerow"><?php echo Lang('edit_time');?></td>
        </tr>
        <tr>
            <td class="linerow" colspan="4"><?php echo Lang('dialog_select_image_tip');?></td>
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
                <td class='linerow' colspan='2'><a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode($tmp).$addparm."'><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder-symlink' viewBox='0 0 18 18'><path d='m11.798 8.271-3.182 1.97c-.27.166-.616-.036-.616-.372V9.1s-2.571-.3-4 2.4c.571-4.8 3.143-4.8 4-4.8v-.769c0-.336.346-.538.616-.371l3.182 1.969c.27.166.27.576 0 .742z'/><path d='m.5 3 .04.87a1.99 1.99 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14h10.348a2 2 0 0 0 1.991-1.819l.637-7A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2zm.694 2.09A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09l-.636 7a1 1 0 0 1-.996.91H2.826a1 1 0 0 1-.995-.91l-.637-7zM6.172 2a1 1 0 0 1 .707.293L7.586 3H2.19c-.24 0-.47.042-.683.12L1.5 2.98a1 1 0 0 1 1-.98h3.672z'/></svg>".Lang('parent_directory')."</a></td>
                <td colspan='2' class='linerow'>".Lang('current_directory')."：$activepath</td>
                </tr>";
                echo $line;
            } else if (is_dir("$inpath/$file")) {
                if (preg_match("#^_(.*)$#i", $file)) continue;
                if (preg_match("#^\.(.*)$#i", $file)) continue;
                $line = "<tr>
                <td class='linerow' colspan='2'><a href='select_images.php?imgstick=$imgstick&v=$v&f=$f&activepath=".urlencode("$activepath/$file").$addparm."'><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder' viewBox='0 0 18 18'><path d='M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z'/></svg>$file</a></td>
                <td class='linerow'></td>
                <td class='linerow'></td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(".$cfg_imgtype.")#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:;\" onClick=\"ChangeImage('$reurl');\"><img src='$activeurl/$file' class='file-icon'></a><a href=\"javascript:;\" onclick=\"ReturnImg('$reurl');\" $lstyle>$file</a></td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow'>$filetime</td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(jpg)#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:;\" onClick=\"ChangeImage('$reurl');\"><img src='$activeurl/$file' class='file-icon'></a><a href=\"javascript:;\" onclick=\"ReturnImg('$reurl');\" $lstyle>$file</a></td>
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