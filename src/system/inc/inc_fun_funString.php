<?php
if (!defined('DEDEINC')) exit ('dedebiz');
require_once DEDEINC."/libraries/HTMLPurifier/HTMLPurifier.auto.php";
function SpHtml2Text($html)
{
   // 初始化 HTMLPurifier 配置
   static $purifier = null;
   if ($purifier === null) {
	   $config = HTMLPurifier_Config::createDefault();

	   // 禁止所有 HTML 标签，只允许文本
	   $config->set('HTML.Allowed', '');

	   // 配置缓存
	   $cacheDir = DEDEDATA.'/cache';
	   $config->set('Cache.SerializerPath', $cacheDir);

	   $purifier = new HTMLPurifier($config);
   }

   // 过滤掉所有 HTML，只保留纯文本
   $cleanText = $purifier->purify($html);

   // 进一步去除可能的额外空格和换行符
   $cleanText = trim($cleanText);
   $cleanText = preg_replace("/[\r\n\t ]+/", ' ', $cleanText);

   return $cleanText;
}
?>