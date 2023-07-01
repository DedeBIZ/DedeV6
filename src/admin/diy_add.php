<?php
/**
 * 添加自定义表单
 *
 * @version        $id:diy_add.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_New');
$mysql_version = $dsql->GetVersion();
$mysql_versions = explode(".", trim($mysql_version));
$mysql_version = $mysql_versions[0].".".$mysql_versions[1];
if (empty($action)) {
    $row = $dsql->GetOne("SELECT diyid FROM `#@__diyforms` ORDER BY diyid DESC LIMIT 0,1");
    if (is_array($row)) $newdiyid = $row['diyid'] + 1;
    else $newdiyid = 1;
    include(DEDEADMIN."/templets/diy_add.htm");
} else {
    if (preg_match("#[^0-9-]#", $diyid) || empty($diyid)) {
        ShowMsg("自定义表单id必须为数字", "-1");
        exit();
    }
    if ($table == "") {
        ShowMsg("自定义表单表名不能为空", "-1");
        exit();
    }
    $public = isset($public) && is_numeric($public) ? $public : 0;
    $name = dede_htmlspecialchars($name);
    $row = $dsql->GetOne("SELECT * FROM `#@__diyforms` WHERE diyid='$diyid' OR `table` LIKE '$table' OR name LIKE '$name'");
    if (is_array($row)) {
        ShowMsg("自定义表单diyid和自定义表单表名称数据库已存在，请重新填写", "-1");
        exit();
    }
    if ($cfg_dbtype=="sqlite") {
        $query = "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;";
    } else {
        $query = "SHOW TABLES";
    }
    $dsql->SetQuery($query);
    $dsql->Execute("biz");
    while ($row = $dsql->GetArray("biz")) {
        if (empty($row[0])) $row[0] = '';
        if ($table == $row[0]) {
            showmsg('指定的自定义表单称数据库已存在，请重新填写', '-1');
            exit();
        }
    }
    $sql = "CREATE TABLE IF NOT EXISTS  `$table`(
    `id` int(10) unsigned NOT NULL auto_increment,
    `ifcheck` tinyint(1) NOT NULL default '0',
    ";
    if ($mysql_version < 4.1) {
        $sql .= " PRIMARY KEY (`id`)\r\n) TYPE=MyISAM; ";
    } else {
        $sql .= " PRIMARY KEY (`id`)\r\n) ENGINE=MyISAM DEFAULT CHARSET=".$cfg_db_language."; ";
    }
    if ($dsql->ExecuteNoneQuery($sql)) {
        $query = "INSERT INTO `#@__diyforms` (`diyid`,`name`,`table`,`info`,`listtemplate`,`viewtemplate`,`posttemplate`,`public` ) VALUES ('$diyid','$name','$table','','$listtemplate','$viewtemplate','$posttemplate','$public')";
        $dsql->ExecuteNoneQuery($query);
        showmsg('成功创建一个自定义表单', 'diy_main.php');
    } else {
        showmsg('创建自定义表单失败', '-1');
    }
}
?>