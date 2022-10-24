<?php
/**
 * 软件选择
 *
 * @version        $Id: select_soft.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($activepath)) {
    $activepath = '';
}
$activepath = str_replace('.', '', $activepath);
$activepath = preg_replace("#\/{1,}#", '/', $activepath);
if (strlen($activepath) < strlen($cfg_soft_dir)) {
    $activepath = $cfg_soft_dir;
}
$inpath = $cfg_basedir.$activepath;
$activeurl = '..'.$activepath;
if (empty($f)) {
    $f = 'form1.enclosure';
}
if (!is_dir($inpath)) {
    die('No Exsits Path');
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
    <title><?php echo Lang('dialog_soft_select');?></title>
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
        var funcNum = <?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>;
        if (window.opener.CKEDITOR != null && funcNum != 1) {
            window.opener.CKEDITOR.tools.callFunction(funcNum, reimg);
        }
        if (typeof window.opener.CKEDITOR.instances["<?php echo $f ?>"] !== "undefined") {
            let addonHTML = `<a href='${reimg}' target='_blank'>附件：${reimg}</a>`;
            window.opener.CKEDITOR.instances["<?php echo $f ?>"].insertHtml(addonHTML);
        }
        if (window.opener.document.<?php echo $f ?> != null) {
            window.opener.document.<?php echo $f ?>.value = reimg;
            window.close();
            return
        }
        window.close();
    }
    </script>
    <table width="100%" align="center" cellpadding="0" cellspacing="1" class="table table-borderless">
        <tr>
            <td colspan="3">
                <form action="select_soft_post.php" method="POST" enctype="multipart/form-data" name='myform'>
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="job" value="upload">
                    <?php echo Lang('upload');?>：<input type="file" name="uploadfile" size="24" style="width:50%;border:0">
                    <?php echo Lang('rename');?>：<input type="text" name="newname" style="width:160px">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm"><?php echo Lang('save');?></button>
                </form>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <table width="100%" cellspacing="0" cellpadding="2">
                    <tr>
                        <td width="50%" class="linerow"><?php echo Lang('dialog_soft_select');?></td>
                        <td width="25%" class="linerow"><?php echo Lang('filesize');?></td>
                        <td width="25%" class="linerow"><?php echo Lang('edit_time');?></td>
                    </tr>
                    <?php
					$dh = scandir($inpath);
					$ty1 = $ty2 = "";
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
                            <td class='linerow'><a href='select_soft.php?f=$f&activepath=".urlencode($tmp).$addparm."'><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder-symlink' viewBox='0 0 18 18'><path d='m11.798 8.271-3.182 1.97c-.27.166-.616-.036-.616-.372V9.1s-2.571-.3-4 2.4c.571-4.8 3.143-4.8 4-4.8v-.769c0-.336.346-.538.616-.371l3.182 1.969c.27.166.27.576 0 .742z'/><path d='m.5 3 .04.87a1.99 1.99 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14h10.348a2 2 0 0 0 1.991-1.819l.637-7A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2zm.694 2.09A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09l-.636 7a1 1 0 0 1-.996.91H2.826a1 1 0 0 1-.995-.91l-.637-7zM6.172 2a1 1 0 0 1 .707.293L7.586 3H2.19c-.24 0-.47.042-.683.12L1.5 2.98a1 1 0 0 1 1-.98h3.672z'/></svg>".Lang('parent_directory')."</a></td>
                            <td colspan='2' class='linerow'>".Lang('current_directory')."：$activepath</td>
                            </tr>\r\n";
                            echo $line;
                        } else if (is_dir("$inpath/$file")) {
                            if (preg_match("#^_(.*)$#i", $file)) continue;
                            if (preg_match("#^\.(.*)$#i", $file)) continue;
                            $line = "<tr>
                            <td class='linerow'><a href=select_soft.php?f=$f&activepath=".urlencode("$activepath/$file").$addparm."><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder' viewBox='0 0 18 18'><path d='M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z'/></svg>$file</a></td>
                            <td class='linerow'></td>
                            <td class='linerow'></td>
                            </tr>";
                            echo "$line";
                        } else if (preg_match("#\.(zip|rar|tgr.gz)#i", $file)) {
                            if ($file == $comeback) $lstyle = " class='text-danger' ";
                            else  $lstyle = "";
                            $reurl = "$activeurl/$file";
                            $reurl = preg_replace("#^\.\.#", "", $reurl);
                            $reurl = $reurl;
                            $line = "<tr>
                            <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-file-zip' viewBox='0 0 18 18'><path d='M6.5 7.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v.938l.4 1.599a1 1 0 0 1-.416 1.074l-.93.62a1 1 0 0 1-1.109 0l-.93-.62a1 1 0 0 1-.415-1.074l.4-1.599V7.5zm2 0h-1v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.93-.62-.4-1.598a1 1 0 0 1-.03-.243V7.5z'/><path d='M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm5.5-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9v1H8v1h1v1H8v1h1v1H7.5V5h-1V4h1V3h-1V2h1V1z'/></svg>$file</a></td>
                            <td class='linerow'>$filesize KB</td>
                            <td class='linerow'>$filetime</td>
                            </tr>";
                            echo "$line";
                        } else {
                            if ($file == $comeback) $lstyle = " class='text-danger' ";
                            else  $lstyle = '';
                            $reurl = "$activeurl/$file";
                            $reurl = preg_replace("#^\.\.#", "", $reurl);
                            $reurl = $reurl;
                            $line = "<tr>
                            <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-filetype-exe' viewBox='0 0 18 18'><path fill-rule='evenodd' d='M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM2.575 15.202H.785v-1.073H2.47v-.606H.785v-1.025h1.79v-.648H0v3.999h2.575v-.647ZM6.31 11.85h-.893l-.823 1.439h-.036l-.832-1.439h-.931l1.227 1.983-1.239 2.016h.861l.853-1.415h.035l.85 1.415h.908l-1.254-1.992L6.31 11.85Zm1.025 3.352h1.79v.647H6.548V11.85h2.576v.648h-1.79v1.025h1.684v.606H7.334v1.073Z'/></svg>$file</a></td>
                            <td class='linerow'>$filesize KB</td>
                            <td class='linerow'>$filetime</td>
                            </tr>";
                            echo "$line";
                        }
                    }//End Loop
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="3"><?php echo Lang('dialog_soft_select_tip');?></td>
        </tr>
    </table>
</body>
</html>