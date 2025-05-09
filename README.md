## Dedev6

DedeBIZ系统基于PHP7版本开发，具有很强的可扩展性，并且完全开放源代码。DedeBIZ支持采用现流行的Go语言设计开发，拥有简单易用、灵活扩展特性之外更安全、高效。模板设计制作简单，一直是系统一大特点，延续之前标签，同时采用响应式模板引擎Bootstrap作为系统模板渲染引擎，让搭建跨终端和移动端全媒体站点更简单。

## 版本说明

Dedev6.x是一个LTS版本，支持将到2025年10月截止，目前Dedev6已经发布，可以[点击下载](https://www.dedebiz.com/download)获取

![DedeBIZ](docs/dedebiz.png)

## 参与开源

访问[代码托管](https://www.dedebiz.com/git)，可以看到我们已经将代码托管在几个知名代码托管平台，可以通过提交Pull requests的方式来贡献您的力量

## v7.0 Roadmap

我们将会收集、整理新的功能需求制定新的Roadmap

[开发者](https://www.dedebiz.com/developer)可以到[工单管理](https://www.zhelixie.com/DedeBiz/DedeBIZ/issues)中进行交流反馈

普通用户可以通过微信公众号或者邮件的方式进行反馈，详见本页底部资源

在这里，可以查看版本[更新记录](docs/changelog.md)

## 平台需求

1.Windows 平台

IIS/Apache/Nginx + PHP5.3+/PHP7/PHP8 + MySQL5/8/10

2.Linux/Unix 平台

Apache/Nginx + PHP5.3+/PHP7 + MySQL5/8/10 (PHP必须在非安全模式下运行)

建议使用平台：Linux + Apache2.4 + PHP7.4 + MySQL8.0

3.PHP必须环境或启用的系统函数

CURL：数据采集

Fileinfo：文件上传安全校验

GD扩展库：图像验证码、水印、二维码生成

MySQL扩展库：数据存储

OpenSSL：支持DedeBIZ商业支持

Sockets：支持接入DedeBIZ商业组件模块

4.基本目录结构及文件

```
./docs              文档及协议
./src               系统源代码
..|_/a              默认网页文件存放目录[必须可写入]
..|_/admin          默认后台管理目录[可任意改名]
..|_/apps           插件扩展程序目录[不可写入，可执行]
..|_/data           系统缓存或其它可写入数据存放目录[必须可写入，但不可执行，建议关闭对外访问权限]
..|_/install        程序安装目录，安装完后可删除[安装时必须有可写入权限]
..|_/static         静态资源存放目录[必须可写入，无需执行]
..|_/system         类库文件目录[建议关闭对外访问权限]
..|_/theme          系统默认内核模板目录[建议关闭对外访问权限]
..|_/user           会员目录
..|_/index.php      入口文件
..|_/license.txt    GPLv2开源许可协议
./tools             系统工具
..|_/resetpwd.php   管理员密码修改工具（如需重置放至站点根目录，用完删除）
```

5.PHP环境容易碰到的不兼容性问题

  * data目录没写入权限，导致系统session无法使用，这将导致无法登录管理后台（直接表现为验证码不能正常显示）；

  * php的上传的临时文件夹没设置好或没写入权限，这会导致文件上传的功能无法使用；
  
  * 出现莫名的错误，如安装时显示空白，这样能是由于系统没装载mysql扩展导致的，对于初级用户，建议采用命令行工具来运行测试站点；

## 程序安装使用

1.下载程序解压到本地目录;

2.上传程序目录中的`/src`到网站根目录；

3.运行`http://www.yourname.com/install/index.php`(yourname表示您的域名),按照安装提速说明进行程序安装；

详细安装步骤可以查看[帮助文档](https://www.dedebiz.com/help)

## DedeBIZ命令行工具

在程序包中，我们增加了一个命令行工具`dedebiz`，通过这个命令行工具，我们可以完成例如：静态文件生成、快速搭建开发环境、更新系统等功能

保证我们系统PATH目录中含有`php`命令，如果是Linux系统，我们可以赋予`dedebiz`文件可执行的权限

运行`php dedebiz`命令（如果系统中有bash命令行，直接可以执行`./dedebiz`）我们可以看到如下信息：

```
NAME:
	DedeBIZ命令行工具
USAGE:
	php ./dedebiz command [arguments...]
COMMANDS:
	serv,s 运行DedeBIZ开发服务
	make,m 更新网页
	update,u 更新到最新系统
	help,h Shows 帮助
	quick,q 快速开始一个开发环境
	tdata 生成测试数据
	pwd 更改管理员密码
WEBSITE:
	https://www.dedebiz.com/help/
```
想要快速启动站点，运行`./dedebiz s`，根据提示打开浏览器地址即可快速使用系统

如果我们想要生成静态文件，可以执行`./dedebiz m o`，便可以自动生成

除了上面的“程序安装使用”中的步骤，我们也可以通过命令行工具快速开始一个本地开发的站点，执行脚本`./dedebiz q`，就可以快速初始化一个本地开发的站点，非常方便

![DedeBIZ命令行工具](docs/dedebiz_cli.gif)

## 版权信息

详细参考：[DedeBIZ站点授权协议](https://www.dedebiz.com/license)

我们对DedeBIZ系统授权的态度是“鼓励但不强制”，购买授权是对知识产权的尊重，是对我们技术服务的认可

## 相关资源

- [DedeBIZ](https://www.dedebiz.com)

- [帮助中心](https://www.dedebiz.com/help)

- [DedeBIZ商业支持](https://www.dedebiz.com)

- [代码托管](https://www.dedebiz.com/git)

- 微信公众号：dedebiz

![微信公众号：dedebiz](docs/dedebiz_wechat_qr.jpg)

- 邮箱：support#dedebiz.com