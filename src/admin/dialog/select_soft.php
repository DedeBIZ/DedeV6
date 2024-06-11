<?php
/**
 * 选择软件
 *
 * @version        $id:select_soft.php 9:43 2010年7月8日 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($activepath)) {
    $activepath = '';
}
$noeditor = isset($noeditor) ? $noeditor : '';
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
        <title>选择软件</title>
        <link rel="stylesheet" href="/static/web/css/font-awesome.min.css">
        <link rel="stylesheet" href="/static/web/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/web/css/admin.css">
    </head>
    <body>
        <div class="upload-bg">
            <div class="card shadow-sm">
                <div class="card-header">选择软件</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless icon">
                            <thead>
                                <tr>
                                    <td colspan="3">
                                        <form name="myform"  action="select_soft_post.php" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                                            <?php $noeditor = !empty($noeditor) ? "<input type='hidden' name='noeditor' value='yes'>" : ''; echo $noeditor;?>
                                            <input type="hidden" name="f" value="<?php echo $f ?>">
                                            <input type="hidden" name="job" value="upload">
                                            <input type="file" name="uploadfile">
                                            <label>改名：<input type="text" name="newname" class="admin-input-sm"></label>
                                            <button type="submit" class="btn btn-success btn-sm">保存</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="col">文件名称</td>
                                    <td scope="col">文件大小</td>
                                    <td scope="col">修改时间</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                        		$dh = scandir($inpath);
                        		$ty1 = $ty2 = '';
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
                                        $line = "<tr>
                                        <td><a href='select_soft.php?f=$f&activepath=".urlencode($tmp).$addparm."'><img src='/static/web/img/icon_dir2.png'> 上级目录</a></td>
                                        <td colspan='2'>当前目录：$activepath</td>
                                        </tr>\r\n";
                                        echo $line;
                                    } else if (is_dir("$inpath/$file")) {
                                        if (preg_match("#^_(.*)$#i", $file)) continue;
                                        if (preg_match("#^\.(.*)$#i", $file)) continue;
                                        $line = "<tr>
                                        <td colspan='3'><a href=select_soft.php?f=$f&activepath=".urlencode("$activepath/$file").$addparm."><img src='/static/web/img/icon_dir.png'> $file</a></td>
                                        </tr>";
                                        echo "$line";
                                    } else if (preg_match("#\.(zip|rar|tgr.gz)#i", $file)) {
                                        if ($file == $comeback) $lstyle = "class='text-danger'";
                                        else  $lstyle = '';
                                        $reurl = "$activeurl/$file";
                                        $reurl = preg_replace("#^\.\.#", "", $reurl);
                                        $reurl = $reurl;
                                        $line = "<tr>
                                        <td><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='/static/web/img/icon_zip.png'> $file</a></td>
                                        <td>$filesize KB</td>
                                        <td>$filetime</td>
                                        </tr>";
                                        echo "$line";
                                    } else {
                                        if ($file == $comeback) $lstyle = "class='text-danger'";
                                        else  $lstyle = '';
                                        $reurl = "$activeurl/$file";
                                        $reurl = preg_replace("#^\.\.#", "", $reurl);
                                        $reurl = $reurl;
                                        $line = "<tr>
                                        <td><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='/static/web/img/icon_exe.png'> $file</a></td>
                                        <td>$filesize KB</td>
                                        <td>$filetime</td>
                                        </tr>";
                                        echo "$line";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
                let addonHTML = `<a href='${reimg}' target='_blank'><img src='<?php echo $cfg_cmspath ?>/static/web/img/icon_addon.png'>附件：${reimg}</a>`;
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
    </body>
</html>