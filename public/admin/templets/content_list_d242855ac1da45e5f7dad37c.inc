<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html>
<html>
<head>
<meta charset="<?php echo $cfg_soft_lang; ?>">
<title>文档管理</title>
<link rel="stylesheet" href="../static/css/bootstrap.min.css">
<link rel="stylesheet" href="../static/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="css/base.css">
<script src="../static/js/jquery.js"></script>
<script src="../static/js/bootstrap.bundle.js"></script>
<script src="../static/js/dedeajax2.js"></script>
<script src="js/ieemu.js"></script>
<script src="js/context_menu.js"></script>
<script src="js/main.js"></script>
<script src="js/list.js"></script>
<style>
.btn+.btn{margin-left:10px}
</style>
</head>
<body leftmargin="8" topmargin="8" background="images/allbg.gif" onLoad="ContextMenu.intializeContextMenu()">
	<table width="98%" cellspacing="0" cellpadding="0" align="center" class="table maintable table-bordered mt-3">
		<tr>
			<td>
				<button type="button" class="btn btn-success btn-sm" onClick="location='catalog_do.php?channelid=<?php echo $GLOBALS['channelid']; ?>&cid=<?php echo $GLOBALS['cid']; ?>&dopost=addArchives';">添加文档</button>
				<?php if($adminid==$mid){ ?>
				<button type="button" class="btn btn-success btn-sm" onClick="location='content_list.php?cid=<?php echo $GLOBALS['cid']; ?>&mid=0';">全部文档</button>
				<?php
}
else{
?>
				<button type="button" class="btn btn-success btn-sm" onClick="location='content_list.php?cid=<?php echo $GLOBALS['cid']; ?>&mid=<?php echo $cuserLogin->getUserID(); ?>';">我的文档</button>
				<?php }  echo $GLOBALS['CheckUserSend']; ?>
				<button type="button" class="btn btn-success btn-sm" onClick="location='catalog_main.php';">栏目管理</button>
				<button type="button" class="btn btn-success btn-sm" name="bb1" onClick="location='makehtml_list.php?cid=<?php echo $GLOBALS['cid']; ?>';">更新列表</button>
				<button type="button" class="btn btn-success btn-sm" name="bb2" onClick="location='makehtml_archives.php?cid=<?php echo $GLOBALS['cid']; ?>';">更新文档</button>
				<button type="button" class="btn btn-success btn-sm" name="bb1" onClick="location='recycling.php?cid=<?php echo $GLOBALS['cid']; ?>';">文档回收站</button>
			</td>
		</tr>
	</table>
	<form name="form3" action="content_list.php" method="get">
		<input type="hidden" name="dopost" value="listArchives">
		<table width="98%" cellpadding="1" cellspacing="1" align="center" class="mb-3" style="margin-top:10px;border:1px solid #dee2e6">
			<tr>
				<td style="background:#f8f8f8">
					<table cellpadding="0" cellspacing="0" height="36">
						<tr>
							<td>
								<select name="cid" style="margin:0 10px;width:160px">
									<option value="0">选择栏目</option>
									<?php echo $GLOBALS['optionarr']; ?>
								</select>
							</td>
							<td>关键字：</td>
							<td><input name="keyword" type="text" value="<?php echo $GLOBALS['keyword']; ?>" style="margin-right:10px;width:260px"></td>
							<td>
								<select name="orderby" style="margin-right:10px;width:160px">
									<option value="id">选择排序</option>
									<option value="pubdate">更新时间</option>
									<option value="sortrank">置顶权值</option>
									<option value="click">点击量</option>
									<option value="scores">评论积分</option>
									<option value="lastpost">最后评论</option>
								</select>
							</td>
							<td>
								<select name="flag" style="margin-right:10px;width:160px">
									<option value="">选择属性</option>
									<?php echo $GLOBALS['flagsArr']; ?>
								</select>
							</td>
							<td><button type="submit" class="btn btn-success btn-sm">搜索</button></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
	<form name="form2">
		<table width="98%" cellpadding="2" cellspacing="1" bgcolor="#CFCFCF" align="center" class="table table-bordered maintable" style="margin-top:10px">
			<tr>
				<td height="30" colspan="10" style="margin-left:10px;background:#f8f8f8"><?php echo $GLOBALS['positionname']; ?>文档列表（使用鼠标右键弹出菜单）</td>
			</tr>
			<tr align="center" bgcolor="#FBFCE2" height="26">
				<td width="6%">ID</td>
				<td width="6%">选择</td>
				<td width="30%">文章标题</td>
				<td width="10%">更新时间</td>
				<td width="14%">类目</td>
				<td width="6%">点击</td>
				<td width="6%">HTML</td>
				<td width="6%">权限</td>
				<td width="6%">发布人</td>
				<td width="10%">操作</td>
			</tr>
			<?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?>
			<tr align="center" bgcolor="#ffffff" height="26" align="center" onMouseMove="javascript:this.bgColor='#FCFDEE';" onMouseOut="javascript:this.bgColor='#ffffff';">
				<td><?php echo $fields['id']; ?></td>
				<td><input name="arcID" type="checkbox" id="arcID" value="<?php echo $fields['id']; ?>" class="np"></td>
				<td align="left">
					<span id="arc<?php echo $fields['id']; ?>">
						<a href="archives_do.php?aid=<?php echo $fields['id']; ?>&dopost=editArchives" oncontextmenu="ShowMenu(event,this,<?php echo $fields['id']; ?>,'<?php echo str_replace('\'','',$fields['title']); ?>')"><?php echo $fields['title']; ?></a>
					</span>
					<?php echo IsCommendArchives($fields['flag']); ?>
				</td>
				<td><?php echo GetDateMk($fields['senddate']); ?></td>
				<td><a href="content_list.php?cid=<?php echo $fields['typeid']; ?>"><?php echo GetTypename($fields['typeid']); ?></a></td>
				<td><?php echo $fields['click']; ?></td>
				<td><?php echo IsHtmlArchives($fields['ismake']); ?></td>
				<td><?php echo GetRankName($fields['arcrank']); ?></td>
				<td><?php echo GetMemberName($fields['mid']); ?></td>
				<td>
					<a href="javascript:;" title="属性" class="btn btn-success btn-sm" onClick="QuickEdit('<?php echo $fields['id']; ?>',event,this);"><i class="fa fa-cogs" aria-hidden="true"></i></a>
					<a href="javascript:;" title="编辑" class="btn btn-success btn-sm" onClick="editArc(<?php echo $fields['id']; ?>);"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
					<a href="javascript:;" title="预览" class="btn btn-success btn-sm" onClick="viewArc(<?php echo $fields['id']; ?>);"><i class="fa fa-globe" aria-hidden="true"></i></a>
				</td>
			</tr>
			<?php
}
}?>
			<tr>
				<td height="36" colspan="10" style="margin-left:10px;background:#ffffff">
					<a class="btn btn-success btn-sm" href="javascript:selAll()">全选</a>
					<a class="btn btn-success btn-sm" href="javascript:noSelAll()">取消</a>
					<a class="btn btn-success btn-sm" href="javascript:updateArc(0)">更新</a>
					<a class="btn btn-success btn-sm" href="javascript:checkArc(0)">审核</a>
					<a class="btn btn-success btn-sm" href="javascript:adArc(0)">推荐</a>
					<a class="btn btn-success btn-sm" href="javascript:;" onClick="moveArc(event,this,<?php echo (empty($channelid)?0:$channelid); ?>)">移动</a>
					<a class="btn btn-danger btn-sm" href="javascript:delArc(0)">删除</a>
					<?php if($channelid==1) echo "<a class='btn btn-success btn-sm' href='javascript:kwArc(0)'>自动关键字</a>"; ?>
					<a class="btn btn-success btn-sm" href="javascript:;" onClick="cAtts('attsAdd',event,this)">增加属性</a>
					<a class="btn btn-danger btn-sm" href="javascript:;" onClick="cAtts('attsDel',event,this)">删除属性</a>
				</td>
			</tr>
			<tr>
				<td height="36" colspan="10" align="center" style="background:#f8f8f8"><?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '6';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>
</td>
			</tr>
		</table>
	</form>
</body>
</html>