<?php
/**
 * 选择多媒体
 *
 * @version        $id:select_media.php 9:43 2010年7月8日 tianya $
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
        <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
        <title>选择多媒体</title>
        <link rel="stylesheet" href="/static/web/css/font-awesome.min.css">
        <link rel="stylesheet" href="/static/web/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/web/css/admin.css">
    </head>
    <body class="p-3">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form name="myform" action="select_media_post.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <?php $noeditor = !empty($noeditor) ? "<input type='hidden' name='noeditor' value='yes'>" : ''; echo $noeditor;?>
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="job" value="upload">
                    <input type="hidden" name="CKEditorFuncNum" value="<?php echo isset($CKEditorFuncNum) ? $CKEditorFuncNum : 1;?>">
                    <input type="file" name="uploadfile">
                    <button type="submit" class="btn btn-success btn-sm">上传</button>
                </form>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header">选择多媒体</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless icon">
                        <thead>
                            <tr>
                                <td scope="col">文件名称</td>
                                <td scope="col">文件大小</td>
                                <td scope="col">修改时间</td>
                            </tr>
                        </thead>
                        <tbody>
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
                                    $line = "<tr>
                                        <td><a href='select_media.php?f=$f&activepath=".urlencode($tmp).$addparm."'><img src='/static/web/img/icon_dir2.png'> 返回上级</a></td>
                                        <td colspan='2'>当前目录：$activepath</td>
                                    </tr>";
                                    echo $line;
                                } else if (is_dir("$inpath/$file")) {
                                    if (preg_match("#^_(.*)$#i", $file)) continue;
                                    if (preg_match("#^\.(.*)$#i", $file)) continue;
                                    $line = "<tr>
                                        <td colspan='3'><a href=select_media.php?f=$f&activepath=".urlencode("$activepath/$file").$addparm."><img src='/static/web/img/icon_dir.png'> $file</a></td>
                                    </tr>";
                                    echo "$line";
                                } else if (preg_match("#\.(swf|fly|fla|flv)#i", $file)) {
                                    $reurl = "$activeurl/$file";
                                    $reurl = preg_replace("#^\.\.#", "", $reurl);
                                    if ($cfg_remote_site == 'Y' && $remoteuploads == 1) {
                                       $reurl = $remoteupUrl.$reurl;
                                    } else {
                                        $reurl = $reurl;
                                    }
                                    if ($file == $comeback) $lstyle = "class='text-danger'";
                                    else $lstyle = '';
                                    $line = "<tr>
                                        <td><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='/static/web/img/icon_flash.png'> $file</a></td>
                                        <td>$filesize KB</td>
                                        <td align='center'>$filetime</td>
                                    </tr>";
                                    echo "$line";
                                } else if (preg_match("#\.(wmv|avi)#i", $file)) {
                                    $reurl = "$activeurl/$file";
                                    $reurl = preg_replace("#^\.\.#", "", $reurl);
                                    if ($cfg_remote_site == 'Y' && $remoteuploads == 1) {
                                       $reurl = $remoteupUrl.$reurl;
                                    } else {
                                        $reurl = $reurl;
                                    }
                                    if ($file == $comeback) $lstyle = "class='text-danger'";
                                    else $lstyle = '';
                                    $line = "<tr>
                                        <td><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='/static/web/img/icon_video.png'> $file</a></td>
                                        <td>$filesize KB</td>
                                        <td align='center'>$filetime</td>
                                    </tr>";
                                    echo "$line";
                                } else if (preg_match("#\.(rm|rmvb|mp3|mp4)#i", $file)) {
                                    $reurl = "$activeurl/$file";
                                    $reurl = preg_replace("#^\.\.#", "", $reurl);
                                    if ($cfg_remote_site == 'Y' && $remoteuploads == 1) {
                                       $reurl = $remoteupUrl.$reurl;
                                    } else {
                                        $reurl = $reurl;
                                    }
                                    if ($file == $comeback) $lstyle = "class='text-danger'";
                                    else $lstyle = '';
                                    $line = "<tr>
                                        <td><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='/static/web/img/icon_rm.png'> $file</a></td>
                                        <td>$filesize KB</td>
                                        <td align='center'>$filetime</td>
                                    </tr>";
                                    echo "$line";
                                } else if (preg_match("#\.(mp3|wma)#", $file)) {
                                    $reurl = "$activeurl/$file";
                                    $reurl = preg_replace("#^\.\.#", "", $reurl);
                                    if ($cfg_remote_site == 'Y' && $remoteuploads == 1) {
                                       $reurl = $remoteupUrl.$reurl;
                                    } else {
                                        $reurl = $reurl;
                                    }
                                    if ($file == $comeback) $lstyle = "class='text-danger'";
                                    else $lstyle = '';
                                    $line = "<tr>
                                        <td><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='/static/web/img/icon_music.png'> $file</a></td>
                                        <td>$filesize KB</td>
                                        <td align='center'>$filetime</td>
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
    </body>
</html>