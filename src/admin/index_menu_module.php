<?php
/**
 * 快捷发布菜单
 *
 * @version        $Id: index_memnu_module.php 1 23:44 2011/2/16 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022, DedeBIZ.COM
 * @license        https://www.dedebiz.com/license
 * @link           https://www.dedebiz.com
 */
require(dirname(__FILE__)."/config.php");
if ($cuserLogin->adminStyle != 'dedecms') {
	header("location:index_menu.php?openitem=100");
	exit();
}
require(DEDEADMIN.'/inc/inc_menu_module.php');
require(DEDEADMIN.'/inc/inc_menu_func.php');
?>
<html>
<head>
	<title>DedeBIZ menu</title>
	<link rel="stylesheet" href="../static/web/css/admin.css" type="text/css" />
	<link rel="stylesheet" href="css/menuold.css" type="text/css" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>" />
	<base target="main" />
</head>
<script language="javascript">
	function showHide(objname) {
		var obj = document.getElementById(objname);
		if (obj.style.display == 'block' || obj.style.display == '') obj.style.display = 'none';
		else obj.style.display = 'block';
	}
</script>
<base target="main">
<body leftmargin="0" topmargin="0" target="main">
	<table width='100%' height="100%" border='0' cellspacing='0' cellpadding='0'>
		<tr>
			<td style='padding-left:3px;padding-top:8px' valign="top">
				<?php
				GetMenus($cuserLogin->getUserRank(), 'module');
				?>
			</td>
		</tr>
	</table>
	<table width="120" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td height="6"></td>
		</tr>
	</table>
</body>
</html>