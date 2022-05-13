<?php
/*---------------------------------------------------------------------------------------------
说明:本文件主要用于系统后台[生成]-[自动任务]-[远程服务器同步]文件远程发布目录更新配置(config.file.inc.php)
中的文件夹自定义更新配置所用,使用时候如果有特定目录需要进行同步，则需要在这个文件中进行配置,配置方式如下:
$remotefile[] = array(
 'filedir' => '/yourdir', //同步文件夹目录
 'description' => '       //这里是文件夹说明', 
 'dfserv' => '0',         //默认服务器选项,可以在系统后台[系统设置]-[服务器分布/远程 ]中配置
 'state' => '0',          //同步状态,0:未同步 1:已同步
);
-----------------------------------------------------------------------------------------------*/   
global $remotefile;
$remotefile = array();
//以下是必备同步配置项
$remotefile[0] = array(
  'filedir'=>'/a',
  'description'=>'文档HTML默认保存路',
  'dfserv'=>0,
  'state'=>1,
  'issystem'=>1
);
$remotefile[1] = array(
  'filedir'=>'/static',
  'description'=>'图片上传文件默认路径',
  'dfserv'=>0,
  'state'=>1,
  'issystem'=>1
);
$remotefile[2] = array(
  'filedir'=>'/apps/special',
  'description'=>'专题目录',
  'dfserv'=>0,
  'state'=>1,
  'issystem'=>1
);
?>