<?php
/**
 * @version        $id:article_select_sw.php 8:26 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
header("Pragma:no-cache");
header("Cache-Control:no-cache");
header("Expires:0");
//来源列表
if ($t == 'source') {
    $m_file = DEDEDATA."/admin/source.txt";
    $allsources = file($m_file);
    echo "<div class='coolbg4'>[<a href=\"javascript:OpenMyWin('article_source_edit.php');ClearDivCt('mysource');\">设置</a>]";
    echo "[<a href='javascript:;' onclick='javascript:HideObj(\"mysource\");ChangeFullDiv(\"hide\");'>关闭</a>]</div>\r\n<div class='wsselect'>\r\n";
    foreach ($allsources as $v) {
        $v = trim($v);
        if ($v != "") {
            echo "<a href='javascript:;' onclick='javascript:PutSource(\"$v\")'>$v</a> | \r\n";
        }
    }
    echo "</div>";
} else {
    //作者列表
    $m_file = DEDEDATA."/admin/writer.txt";
    echo "<div class='coolbg4'>[<a href=\"javascript:OpenMyWin('article_writer_edit.php');ClearDivCt('mywriter');\">设置</a>]";
    echo "[<a href='javascript:;' onclick='javascript:HideObj(\"mywriter\");ChangeFullDiv(\"hide\");'>关闭</a>]</div>\r\n<div class='wsselect'>\r\n";
    if (filesize($m_file) > 0) {
        $fp = fopen($m_file, 'r');
        $str = fread($fp, filesize($m_file));
        fclose($fp);
        $strs = explode(',', $str);
        foreach ($strs as $str) {
            $str = trim($str);
            if ($str != "") {
                echo "<a href='javascript:;' onclick='javascript:PutWriter(\"$str\")'>$str</a> | ";
            }
        }
    }
    echo "</div>";
}
?>