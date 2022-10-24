<?php
/**
 * 模板选择
 *
 * @version        $Id: select_templets.php 2022-07-01 tianya $
 * @package        DedeBIZ.Dialog
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
if (empty($activepath)) {
    $activepath = '';
}
$cfg_txttype = 'htm|html|tpl|txt|dtp';
$activepath = str_replace('.', '', $activepath);
$activepath = preg_replace("#\/{1,}#", '/', $activepath);
$templetdir  = $cfg_templets_dir;
if (strlen($activepath) < strlen($templetdir)) {
    $activepath = $templetdir;
}
$inpath = $cfg_basedir.$activepath;
$activeurl = $activepath;
if (!is_dir($inpath)) {
    die('No Exsits Path');
}
if (empty($f)) {
    $f = 'form1.enclosure';
}
if (empty($comeback)) {
    $comeback = '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <title><?php echo Lang('dialog_template_select');?></title>
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
        window.opener.document.<?php echo $f ?>.value = reimg;
        if (document.all) window.opener = true;
        window.close();
    }
    </script>
    <table width="100%" align="center" cellpadding="0" cellspacing="1" class="table table-borderless">
        <tr>
            <td colspan="3">
                <form action="select_templets_post.php" method="POST" enctype="multipart/form-data" name="myform">
                    <input type="hidden" name="activepath" value="<?php echo $activepath ?>">
                    <input type="hidden" name="f" value="<?php echo $f ?>">
                    <input type="hidden" name="job" value="upload">
                    <?php echo Lang('upload');?>：<input type="file" name="uploadfile" style="width:50%;border:0">
                    <?php echo Lang('rename');?>：<input type="text" name="filename" style="width:160px">
                    <button type="submit" name="sb1" class="btn btn-success btn-sm"><?php echo Lang('save');?></button>
                </form>
            </td>
        </tr>
        <tr>
            <td width="50%" class="linerow"><?php echo Lang('dialog_template_select');?></td>
            <td width="25%" class="linerow"><?php echo Lang('filesize');?></td>
            <td width="25%" class="linerow"><?php echo Lang('edit_time');?></td>
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
                    $tmp = preg_replace("#[\/][^\/]*$#", "", $activepath);
                    $line = "<tr>
                    <td class='linerow'><a href='select_templets.php?f=$f&activepath=".urlencode($tmp)."'><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder-symlink' viewBox='0 0 18 18'><path d='m11.798 8.271-3.182 1.97c-.27.166-.616-.036-.616-.372V9.1s-2.571-.3-4 2.4c.571-4.8 3.143-4.8 4-4.8v-.769c0-.336.346-.538.616-.371l3.182 1.969c.27.166.27.576 0 .742z'/><path d='m.5 3 .04.87a1.99 1.99 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14h10.348a2 2 0 0 0 1.991-1.819l.637-7A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2zm.694 2.09A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09l-.636 7a1 1 0 0 1-.996.91H2.826a1 1 0 0 1-.995-.91l-.637-7zM6.172 2a1 1 0 0 1 .707.293L7.586 3H2.19c-.24 0-.47.042-.683.12L1.5 2.98a1 1 0 0 1 1-.98h3.672z'/></svg>".Lang('parent_directory')."</a></td>
                    <td colspan='2' class='linerow'>".Lang('current_directory')."：$activepath</td>
                    </tr>\r\n";
                      echo $line;
                } else if (is_dir("$inpath/$file")) {
                    if (preg_match("#^_(.*)$#i", $file)) continue;
                    if (preg_match("#^\.(.*)$#i", $file)) continue;
                    $line = "<tr>
                    <td class='linerow'><a href=select_templets.php?f=$f&activepath=".urlencode("$activepath/$file")."><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-folder' viewBox='0 0 18 18'><path d='M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z'/></svg>$file</a></td>
                    <td class='linerow'></td>
                    <td class='linerow'></td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(htm|html)#i", $file)) {
                    if ($file == $comeback) $lstyle = " class='text-danger' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-filetype-html' viewBox='0 0 18 18'><path fill-rule='evenodd' d='M14 4.5V11h-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5Zm-9.736 7.35v3.999h-.791v-1.714H1.79v1.714H1V11.85h.791v1.626h1.682V11.85h.79Zm2.251.662v3.337h-.794v-3.337H4.588v-.662h3.064v.662H6.515Zm2.176 3.337v-2.66h.038l.952 2.159h.516l.946-2.16h.038v2.661h.715V11.85h-.8l-1.14 2.596H9.93L8.79 11.85h-.805v3.999h.706Zm4.71-.674h1.696v.674H12.61V11.85h.79v3.325Z'/></svg>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(css)#i", $file)) {
                    if ($file == $comeback) $lstyle = " class='text-danger' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."/#", "", $reurl);
                    $line = "<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-filetype-css' viewBox='0 0 18 18'><path fill-rule='evenodd' d='M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM3.397 14.841a1.13 1.13 0 0 0 .401.823c.13.108.289.192.478.252.19.061.411.091.665.091.338 0 .624-.053.859-.158.236-.105.416-.252.539-.44.125-.189.187-.408.187-.656 0-.224-.045-.41-.134-.56a1.001 1.001 0 0 0-.375-.357 2.027 2.027 0 0 0-.566-.21l-.621-.144a.97.97 0 0 1-.404-.176.37.37 0 0 1-.144-.299c0-.156.062-.284.185-.384.125-.101.296-.152.512-.152.143 0 .266.023.37.068a.624.624 0 0 1 .246.181.56.56 0 0 1 .12.258h.75a1.092 1.092 0 0 0-.2-.566 1.21 1.21 0 0 0-.5-.41 1.813 1.813 0 0 0-.78-.152c-.293 0-.551.05-.776.15-.225.099-.4.24-.527.421-.127.182-.19.395-.19.639 0 .201.04.376.122.524.082.149.2.27.352.367.152.095.332.167.539.213l.618.144c.207.049.361.113.463.193a.387.387 0 0 1 .152.326.505.505 0 0 1-.085.29.559.559 0 0 1-.255.193c-.111.047-.249.07-.413.07-.117 0-.223-.013-.32-.04a.838.838 0 0 1-.248-.115.578.578 0 0 1-.255-.384h-.765ZM.806 13.693c0-.248.034-.46.102-.633a.868.868 0 0 1 .302-.399.814.814 0 0 1 .475-.137c.15 0 .283.032.398.097a.7.7 0 0 1 .272.26.85.85 0 0 1 .12.381h.765v-.072a1.33 1.33 0 0 0-.466-.964 1.441 1.441 0 0 0-.489-.272 1.838 1.838 0 0 0-.606-.097c-.356 0-.66.074-.911.223-.25.148-.44.359-.572.632-.13.274-.196.6-.196.979v.498c0 .379.064.704.193.976.131.271.322.48.572.626.25.145.554.217.914.217.293 0 .554-.055.785-.164.23-.11.414-.26.55-.454a1.27 1.27 0 0 0 .226-.674v-.076h-.764a.799.799 0 0 1-.118.363.7.7 0 0 1-.272.25.874.874 0 0 1-.401.087.845.845 0 0 1-.478-.132.833.833 0 0 1-.299-.392 1.699 1.699 0 0 1-.102-.627v-.495ZM6.78 15.29a1.176 1.176 0 0 1-.111-.449h.764a.578.578 0 0 0 .255.384c.07.049.154.087.25.114.095.028.201.041.319.041.164 0 .301-.023.413-.07a.559.559 0 0 0 .255-.193.507.507 0 0 0 .085-.29.387.387 0 0 0-.153-.326c-.101-.08-.256-.144-.463-.193l-.618-.143a1.72 1.72 0 0 1-.539-.214 1 1 0 0 1-.351-.367 1.068 1.068 0 0 1-.123-.524c0-.244.063-.457.19-.639.127-.181.303-.322.527-.422.225-.1.484-.149.777-.149.304 0 .564.05.779.152.217.102.384.239.5.41.12.17.187.359.2.566h-.75a.56.56 0 0 0-.12-.258.624.624 0 0 0-.246-.181.923.923 0 0 0-.37-.068c-.216 0-.387.05-.512.152a.472.472 0 0 0-.184.384c0 .121.047.22.143.3a.97.97 0 0 0 .404.175l.621.143c.217.05.406.12.566.211.16.09.285.21.375.358.09.148.135.335.135.56 0 .247-.063.466-.188.656a1.216 1.216 0 0 1-.539.439c-.234.105-.52.158-.858.158-.254 0-.476-.03-.665-.09a1.404 1.404 0 0 1-.478-.252 1.13 1.13 0 0 1-.29-.375Z'/></svg>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(js)#i", $file)) {
                    if ($file == $comeback) $lstyle = " class='text-danger' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-filetype-js' viewBox='0 0 18 18'><path fill-rule='evenodd' d='M14 4.5V14a2 2 0 0 1-2 2H8v-1h4a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM3.186 15.29a1.176 1.176 0 0 1-.111-.449h.765a.578.578 0 0 0 .255.384c.07.049.153.087.249.114.095.028.202.041.319.041.164 0 .302-.023.413-.07a.559.559 0 0 0 .255-.193.507.507 0 0 0 .085-.29.387.387 0 0 0-.153-.326c-.101-.08-.255-.144-.462-.193l-.619-.143a1.72 1.72 0 0 1-.539-.214 1.001 1.001 0 0 1-.351-.367 1.068 1.068 0 0 1-.123-.524c0-.244.063-.457.19-.639.127-.181.303-.322.528-.422.224-.1.483-.149.776-.149.305 0 .564.05.78.152.216.102.383.239.5.41.12.17.186.359.2.566h-.75a.56.56 0 0 0-.12-.258.624.624 0 0 0-.247-.181.923.923 0 0 0-.369-.068c-.217 0-.388.05-.513.152a.472.472 0 0 0-.184.384c0 .121.048.22.143.3a.97.97 0 0 0 .405.175l.62.143c.218.05.406.12.566.211.16.09.285.21.375.358.09.148.135.335.135.56 0 .247-.063.466-.188.656a1.216 1.216 0 0 1-.539.439c-.234.105-.52.158-.858.158-.254 0-.476-.03-.665-.09a1.404 1.404 0 0 1-.478-.252 1.13 1.13 0 0 1-.29-.375Zm-3.104-.033A1.32 1.32 0 0 1 0 14.791h.765a.576.576 0 0 0 .073.27.499.499 0 0 0 .454.246c.19 0 .33-.055.422-.164.092-.11.138-.265.138-.466v-2.745h.79v2.725c0 .44-.119.774-.357 1.005-.236.23-.564.345-.984.345a1.59 1.59 0 0 1-.569-.094 1.145 1.145 0 0 1-.407-.266 1.14 1.14 0 0 1-.243-.39Z'/></svg>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(jpg)#i", $file)) {
                    if ($file == $comeback) $lstyle = " class='text-danger' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='$activeurl/$file' class='file-icon'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(gif|png)#i", $file)) {
                    if ($file == $comeback) $lstyle = " class='text-danger' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><img src='$activeurl/$file' class='file-icon'>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td>
                    </tr>";
                    echo "$line";
                } else if (preg_match("#\.(txt)#i", $file)) {
                    if ($file == $comeback) $lstyle = " class='text-danger' ";
                    else  $lstyle = "";
                    $reurl = "$activeurl/$file";
                    $reurl = preg_replace("#\.\.#", "", $reurl);
                    $reurl = preg_replace("#".$templetdir."\/#", "", $reurl);
                    $line = "<tr>
                    <td class='linerow'><a href=\"javascript:ReturnValue('$reurl');\" $lstyle><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' class='bi bi-filetype-txt' viewBox='0 0 18 18'><path fill-rule='evenodd' d='M14 4.5V14a2 2 0 0 1-2 2h-2v-1h2a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5L14 4.5ZM1.928 15.849v-3.337h1.136v-.662H0v.662h1.134v3.337h.794Zm4.689-3.999h-.894L4.9 13.289h-.035l-.832-1.439h-.932l1.228 1.983-1.24 2.016h.862l.853-1.415h.035l.85 1.415h.907l-1.253-1.992 1.274-2.007Zm1.93.662v3.337h-.794v-3.337H6.619v-.662h3.064v.662H8.546Z'/></svg>$file</a></td>
                    <td class='linerow'>$filesize KB</td>
                    <td class='linerow'>$filetime</td></tr>";
                    echo "$line";
                }
            }//End Loop
            ?>
        </table>
    </body>
</html>