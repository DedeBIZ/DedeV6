<?php
/**
 * 图片测试
 *
 * @version        $id:album_testhtml.php 8:26 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
AjaxHead();
$myhtml = UnicodeUrl2Gbk(stripslashes($myhtml));
echo "<div class='coolbg61'>[<a href='javascript:;' onclick='javascript:HideObj(\"_myhtml\")'>关闭</a>]</div>\r\n";
preg_match_all("/(src|SRC)=[\"|'| ]{0,}(http:\/\/(.*)\.(gif|jpg|jpeg|png))/isU", $myhtml, $img_array);
$img_array = array_unique($img_array[2]);
echo "<div class='coolbg62'><xmp>";
echo "捕获的图片：\r\n";
print_r($img_array);
echo "</xmp></div>\r\n";
?>