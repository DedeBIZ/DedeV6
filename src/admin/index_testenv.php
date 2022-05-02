<?php
@set_time_limit(0);
require_once(dirname(__FILE__)."/config.php");
AjaxHead();
if (!function_exists('TestWriteable')) {
	//检测是否可写
	function TestWriteable($d, $c = false)
	{
		$tfile = '_write_able.txt';
		$d = preg_replace("/\/$/", '', $d);
		$fp = @fopen($d.'/'.$tfile, 'w');
		if (!$fp) {
			if ($c == false) {
				@chmod($d, 0777);
				return false;
			} else return TestWriteable($d, true);
		} else {
			fclose($fp);
			return @unlink($d.'/'.$tfile) ? true : false;
		}
	}
}
if (!function_exists('TestExecuteable')) {
	//检查是否具目录可执行
	function TestExecuteable($d = '.', $siteuRL = '', $rootDir = '')
	{
		$testStr = '<'.chr(0x3F).'p'.chr(hexdec(68)).chr(112)."\n\r";
		$filename = md5($d).'.php';
		$testStr .= 'function test(){ echo md5(\''.$d.'\');}'."\n\rtest();\n\r";
		$testStr .= chr(0x3F).'>';
		$reval = false;
		if (empty($rootDir)) $rootDir = DEDEROOT;
		if (TestWriteable($d)) {
			@file_put_contents($d.'/'.$filename, $testStr);
			$remoteUrl = $siteuRL.'/'.str_replace($rootDir, '', str_replace("\\", '/', realpath($d))).'/'.$filename;
			$tempStr = @PostHost($remoteUrl);
			$reval = (md5($d) == trim($tempStr)) ? true : false;
			unlink($d.'/'.$filename);
			return $reval;
		} else {
			return -1;
		}
	}
}
if (!function_exists('PostHost')) {
	function PostHost($host, $data = '', $method = 'GET', $showagent = null, $port = null, $timeout = 30)
	{
		$parse = @parse_url($host);
		if (empty($parse)) return false;
		if ((int)$port > 0) {
			$parse['port'] = $port;
		} elseif (!@$parse['port']) {
			$parse['port'] = '80';
		}
		$parse['host'] = str_replace(array('http://', 'https://'), array('', 'ssl://'), "$parse[scheme]://").$parse['host'];
		if (!$fp = @fsockopen($parse['host'], $parse['port'], $errnum, $errstr, $timeout)) {
			return false;
		}
		$method = strtoupper($method);
		$wlength = $wdata = $responseText = '';
		$parse['path'] = str_replace(array('\\', '//'), '/', @$parse['path'])."?".@$parse['query'];
		if ($method == 'GET') {
			$separator = @$parse['query'] ? '&' : '';
			substr($data, 0, 1) == '&' && $data = substr($data, 1);
			$parse['path'] .= $separator.$data;
		} elseif ($method == 'POST') {
			$wlength = "Content-length: ".strlen($data)."\r\n";
			$wdata = $data;
		}
		$write = "$method $parse[path] HTTP/1.0\r\nHost: $parse[host]\r\nContent-type: application/x-www-form-urlencoded\r\n{$wlength}Connection: close\r\n\r\n$wdata";
		@fwrite($fp, $write);
		while ($data = @fread($fp, 4096)) {
			$responseText .= $data;
		}
		@fclose($fp);
		empty($showagent) && $responseText = trim(stristr($responseText, "\r\n\r\n"), "\r\n");
		return $responseText;
	}
}
if (!function_exists('TestAdminPWD')) {
	//返回结果，1.没有修改默认管理员名称，2.没有修改默认管理员用户名和密码，3.没有发现默认账号
	function TestAdminPWD()
	{
		global $dsql;
		//查询栏目表确定栏目所在的目录
		$sql = "SELECT usertype,userid,pwd FROM #@__admin WHERE `userid`='admin'";
		$row = $dsql->GetOne($sql);
		if (is_array($row)) {
			if ($row['pwd'] == 'f297a57a5a743894a0e4') {
				return -2;
			} else {
				return -1;
			}
		} else {
			return 0;
		}
	}
}
if (!function_exists('IsWritable')) {
	//检测是否可写
	function IsWritable($pathfile)
	{
		$isDir = substr($pathfile, -1) == '/' ? true : false;
		if ($isDir) {
			if (is_dir($pathfile)) {
				mt_srand((float)microtime() * 1000000);
				$pathfile = $pathfile.'biz_'.uniqid(mt_rand()).'.tmp';
			} elseif (@mkdir($pathfile)) {
				return IsWritable($pathfile);
			} else {
				return false;
			}
		}
		@chmod($pathfile, 0777);
		$fp = @fopen($pathfile, 'ab');
		if ($fp === false) return false;
		fclose($fp);
		$isDir && @unlink($pathfile);
		return true;
	}
}
//检测权限
$safeMsg = array();
$dirname = str_replace('index_body.php', '', strtolower($_SERVER['PHP_SELF']));
if (preg_match("#[\\|/]admin[\\|/]#", $dirname)) {
	$safeMsg[] = '后台管理目录名称中包含默认名称admin，强烈建议您进行修改';
}
if (IsWritable(DEDEDATA.'/common.inc.php')) {
	$safeMsg[] = '数据安全data/common.inc.php文件，强烈建议以管理员级别设置为644或只读';
}
if (!IsSSL()) {
	$safeMsg[] = '当前站点尚未启用HTTPS，强烈建议您启用';
}
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	$safeMsg[] = "PHP请升级到5.3及以上版本，低版本PHP环境将无法正常使用本系统";
}
$rs = TestAdminPWD();
if ($rs < 0) {
	$linkurl = "<a href='sys_admin_user.php' class='btn btn-success btn-sm'>修改</a>";
	switch ($rs) {
		case -1:
			$msg = "没有修改默认管理员名称admin，强烈建议您进行修改 {$linkurl}";
			break;
		case -2:
			$msg = "没有修改默认管理员名称和密码，强烈建议您进行修改 {$linkurl}";
			break;
	}
	$safeMsg[] = $msg;
}
?>
<?php
if (count($safeMsg) > 0) {
?>
	<div class="alert alert-danger mt-3 mb-3">
		<?php
		$i = 1;
		foreach ($safeMsg as $key => $val) {
		?>
		<div class="py-1"><?php echo $i; ?>.<?php echo $val; ?></div>
		<?php
		$i++;
		}
		?>
	</div>
<?php
}
?>