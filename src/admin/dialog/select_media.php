<?php
/**
 * 多媒体选择
 *
 * @version        $Id: select_media.php 2022-07-01 tianya $
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
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <title><?php echo Lang('dialog_media_select');?></title>
    <link rel="stylesheet" href="../../static/web/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../static/web/font/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../static/web/css/admin.min.css">
    <style>
html{background:#f8f8f8}
.bg{margin:10px;border-radius:.2rem;box-shadow:0 .125rem .25rem rgba(0,0,0,.075)}
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
        var funcNum = <?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>;
        if (window.opener.CKEDITOR != null && funcNum != 1) {
            window.opener.CKEDITOR.tools.callFunction(funcNum, reimg);
        }
        window.close();
    }
    </script>
    <table width="100%" align="center" cellspacing="0" cellpadding="2" class="table table-borderless">
        <tr>
            <td colspan="3">
                <form action="select_media_post.php" method="POST" enctype="multipart/form-data" name="myform">
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="job" value="upload">
                    <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>">
                    上传：<input type="file" name="uploadfile" style="width:50%;border:0">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm"><?php echo Lang('upload');?></button>
                </form>
            </td>
        </tr>
        <tr>
            <td width="50%" align="center" class="linerow"><?php echo Lang('dialog_media_name_select');?></td>
            <td width="25%" align="center" class="linerow"><?php echo Lang('filesize');?></td>
            <td width="25%" align="center" class="linerow"><?php echo Lang('edit_time');?></td>
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
            //判断文件类型并作处理
            if ($file == ".") continue;
            else if ($file == "..") {
                if ($activepath == "") continue;
                $tmp = preg_replace("#[\/][^\/]*$#i", "", $activepath);
                $line = "<tr>
                <td class='linerow'><a href='select_media.php?f=$f&activepath=".urlencode($tmp).$addparm."'><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder-symlink' viewBox='0 0 18 18'><path d='m11.798 8.271-3.182 1.97c-.27.166-.616-.036-.616-.372V9.1s-2.571-.3-4 2.4c.571-4.8 3.143-4.8 4-4.8v-.769c0-.336.346-.538.616-.371l3.182 1.969c.27.166.27.576 0 .742z'/><path d='m.5 3 .04.87a1.99 1.99 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14h10.348a2 2 0 0 0 1.991-1.819l.637-7A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2zm.694 2.09A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09l-.636 7a1 1 0 0 1-.996.91H2.826a1 1 0 0 1-.995-.91l-.637-7zM6.172 2a1 1 0 0 1 .707.293L7.586 3H2.19c-.24 0-.47.042-.683.12L1.5 2.98a1 1 0 0 1 1-.98h3.672z'/></svg>".Lang('parent_directory')."</a></td>
                <td colspan='2' class='linerow'>".Lang('current_directory')."：$activepath</td>
                </tr>";
                echo $line;
            } else if (is_dir("$inpath/$file")) {
                if (preg_match("#^_(.*)$#i", $file)) continue;
                if (preg_match("#^\.(.*)$#i", $file)) continue;
                $line = "<tr>
                <td class='linerow'><a href=select_media.php?f=$f&activepath=".urlencode("$activepath/$file").$addparm."><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder' viewBox='0 0 18 18'><path d='M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z'/></svg>$file</a></td>
                <td class='linerow'></td>
                <td class='linerow'></td>
                </tr>";
                echo "$line";
            } else if (preg_match("#\.(wmv|avi)#i", $file)) {
                $reurl = "$activeurl/$file";
                $reurl = preg_replace("#^\.\.#", "", $reurl);
                $reurl = $reurl;
                if ($file == $comeback) $lstyle = " class='text-danger' ";
                else  $lstyle = "";
                $line = "<tr>
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-file-earmark-play' viewBox='0 0 18 18'><path d='M6 6.883v4.234a.5.5 0 0 0 .757.429l3.528-2.117a.5.5 0 0 0 0-.858L6.757 6.454a.5.5 0 0 0-.757.43z'/><path d='M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z'/></svg>$file</a></td>
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
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-file-music' viewBox='0 0 18 18'><path d='M10.304 3.13a1 1 0 0 1 1.196.98v1.8l-2.5.5v5.09c0 .495-.301.883-.662 1.123C7.974 12.866 7.499 13 7 13c-.5 0-.974-.134-1.338-.377-.36-.24-.662-.628-.662-1.123s.301-.883.662-1.123C6.026 10.134 6.501 10 7 10c.356 0 .7.068 1 .196V4.41a1 1 0 0 1 .804-.98l1.5-.3z'/><path d='M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z'/></svg>$file</a></td>
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
                <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\"><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-filetype-mp3' viewBox='0 0 18 18'>
                <path fill-rule='evenodd' d='M14 4.5V14a2 2 0 0 1-2 2v-1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5Zm-4.911 9.67h-.443v-.609h.422a.688.688 0 0 0 .322-.073.558.558 0 0 0 .22-.2.505.505 0 0 0 .076-.284.49.49 0 0 0-.176-.392.652.652 0 0 0-.442-.15.74.74 0 0 0-.252.041.625.625 0 0 0-.193.112.496.496 0 0 0-.179.349H7.71c.006-.157.04-.302.102-.437.063-.135.153-.252.27-.352.117-.101.26-.18.428-.237.17-.057.364-.086.583-.088.279-.002.52.042.723.132.203.09.36.214.472.372a.91.91 0 0 1 .173.539.833.833 0 0 1-.12.478.96.96 0 0 1-.619.439v.041a1.008 1.008 0 0 1 .718.434.909.909 0 0 1 .144.521c.002.19-.037.359-.117.507a1.104 1.104 0 0 1-.329.378c-.14.101-.302.18-.486.234-.182.053-.376.08-.583.08-.3 0-.558-.051-.77-.153a1.206 1.206 0 0 1-.487-.41 1.094 1.094 0 0 1-.178-.563h.726a.457.457 0 0 0 .106.258.664.664 0 0 0 .249.179.98.98 0 0 0 .357.067.903.903 0 0 0 .384-.076.598.598 0 0 0 .252-.217.56.56 0 0 0 .088-.319.556.556 0 0 0-.334-.522.81.81 0 0 0-.372-.079ZM.706 15.925v-2.66h.038l.952 2.16h.516l.946-2.16h.038v2.66h.715v-3.999h-.8l-1.14 2.596h-.026l-1.14-2.596H0v4h.706Zm5.458-3.999h-1.6v4h.792v-1.342h.803c.287 0 .53-.058.732-.173.203-.118.357-.276.463-.475a1.42 1.42 0 0 0 .161-.677c0-.25-.053-.475-.158-.677a1.175 1.175 0 0 0-.46-.477 1.4 1.4 0 0 0-.733-.179Zm.545 1.333a.795.795 0 0 1-.085.381.574.574 0 0 1-.237.24.793.793 0 0 1-.375.082h-.66v-1.406h.66c.219 0 .39.06.513.182.123.12.184.295.184.521Z'/></svg>$file</a></td>
                <td class='linerow'>$filesize KB</td>
                <td class='linerow' align='center'>$filetime</td>
                </tr>";
                echo "$line";
            }
        }//End Loop
        ?>
    </table>
</body>
</html>