<?php
/**
 * 统计程序
 *
 * @version        $id:statistics.php$
 * @package        DedeBIZ.Site
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__)."/../system/common.inc.php");
require_once(DEDEINC."/libraries/statistics.class.php");
if (empty($dopost)) $dopost = '';
$stat = new DedeStatistics;
if ($dopost == "stat") {
    $rs = $stat->Record();
    $result = array(
        "code" => 200,
        "data" => "success",
    );
    echo json_encode($result);
    exit;
}
$v = $stat->GetStat();
echo $v;
?>