<?php
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
CheckPurview('plus_Mail');
if(!isset($dopost)) $dopost = '';
if($dopost=="send"){
	//邮件发送函数
	function sendmail($email, $mailtitle, $mailbody)
	{
		global $cfg_sendmail_bysmtp, $cfg_smtp_server, $cfg_smtp_port, $cfg_smtp_usermail,  $cfg_smtp_password, $cfg_webname;
		global $cfg_bizcore_appid,$cfg_bizcore_key,$cfg_bizcore_hostname,$cfg_bizcore_port;
		if (!empty($cfg_bizcore_appid) && !empty($cfg_bizcore_key)) {
			$client = new DedeBizClient($cfg_bizcore_hostname, $cfg_bizcore_port);
			$client->appid = $cfg_bizcore_appid;
			$client->key = $cfg_bizcore_key;
			$client->MailSend($email,$mailtitle,$mailtitle,$mailbody);
			$client->Close();
		} else {
			if($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server))
			{
				$mailtype = 'HTML';
				require_once(DEDEINC.'/mail.class.php');
				$smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
				$smtp->debug = false;
				if(!$smtp->smtp_sockopen($cfg_smtp_server)){
					ShowMsg('邮件发送失败,请联系管理员','-1');
					exit();
				}
				$smtp->sendmail($email,$cfg_webname,$cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
			}else{
				@mail($email, $mailtitle, $mailbody, $headers);
			}
		}
	}
	
	$row=$dsql->GetOne("SELECT * FROM `#@__mail_title` WHERE id=$id");
	$mailtitle=$row['title'];
	$mailbody=$row['content'];
	
	$sql="SELECT m.email FROM `#@__member` AS m LEFT JOIN `#@__mail_order` AS o ON o.mid=m.mid WHERE o.typeid=$typeid";
	$db->Execute('me',$sql);
	while($row = $db->GetArray()){
		$mails[]=$row;
	}
	$email="";
	foreach($mails as $mail){
		$email.=$mail['email'].",";
	}
	
	$mailto=$email;
	
	sendmail($mailto,$mailtitle,$mailbody);
	
	$sendtime = time();
	$inquery = "UPDATE `#@__mail_title` SET count=count+1,sendtime='$sendtime',state='1' WHERE id=$id";
  	if($dsql->ExecuteNoneQuery($inquery)){
		ShowMsg('邮件已成功发送','mail_title_send.php');
		exit();
	}
}else{
	
	function GetState($state){
		if($state=="0") return "<span style='color:#e74d58'>未发送</span>";
		else return "已发送";
	}
	
	function GetSendTimeMk($mktime){
		if($mktime=="0") return "<span style='color:#e74d58'>未发送</span>";
		else return MyDate('Y-m-d H:i:s',$mktime);
	}
	
	function GetCount($typeid){
		global $dsql;
		$row=$dsql->GetOne("SELECT COUNT(typeid) as cc FROM `#@__mail_order`");
		return $row['cc'];
	}
	
	$sql  = "SELECT t.*,p.typename FROM `#@__mail_title` AS t LEFT JOIN `#@__mail_type` AS p ON t.typeid=p.id ORDER BY t.id desc";
	$dlist = new DataListCP();
	$dlist->SetTemplet(DEDEADMIN."/templets/mail_title_send.htm");
	$dlist->SetSource($sql);
	$dlist->display();
}
