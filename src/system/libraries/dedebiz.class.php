<?php
if (!defined('DEDEINC')) exit('dedebiz');
//用于DedeBIZ商业组件通信
define("DEDEBIZ", true);
class DedeBizClient
{
    var $socket;
    var $appid;
    var $key;
    var $err;
    function __construct($ipaddr, $port)
    {
        $this->err = "";
        if (!function_exists("socket_create")) {
            $this->err = (object)array(
                "code" => -1,
                "data" => null,
                "msg" => "请在php.ini开启extension=sockets",
            );
            return;
        }
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $rs = @socket_connect($this->socket, $ipaddr, $port);
        if (!$rs) {
            $this->err = (object)array(
                "code" => -1,
                "data" => null,
                "msg" => "连接DedeBiz商业组件服务失败\r\n",
            );
            return;
        }
    }
    function request(&$req)
    {
        //进行签名
        $this->MakeSign($req);
        $str = json_encode($req);
        $length = strlen($str);
        $s = @socket_write($this->socket, $str, $length);
        if (!$s) {
            return (object)array(
                "code" => -1,
                "data" => null,
                "msg" => "请求DedeBiz商业组件服务失败\r\n",
            );
        }
        if (!empty($this->err)) {
            return $this->err;
        }
        $msg = "";
        while (($str = socket_read($this->socket, 1024)) !== FALSE) {
            $msg .= $str;
            if (strlen($str) < 1024) {
                break;
            }
        }
        return $this->CheckSign($msg);
    }
    //用户获取当前服务器状态信息
    function SystemInfo()
    {
        $req = array(
            "method" => "system_info",
        );
        return $this->request($req);
    }
    //检测是否连接
    function Ping($i)
    {
        $req = array(
            "method" => "ping",
            "parms" => array(
                "name" => "www.dedebiz.com",
            )
        );
        return $this->request($req);
    }
    //发送邮件
    function MailSend($to, $subject, $title, $content="", $quote="", $link_url="", $link_title="")
    {
        $req = array(
            "method" => "main_send",
            "parms" => array(
                "to" => $to,
                "subject" => $subject,
                "title" => $title,
                "content" => $content,
                "quote" => $quote,
                "link_url" => $link_url,
                "link_title" => $link_title,
            )
        );
        return $this->request($req);
    }
    //获取一个管理员信息
    function AdminGetOne()
    {
        $req = array(
            "method" => "admin_get_one",
            "parms" => array(
                "name" => "admin",
            )
        );
        return $this->request($req);
    }
    //检查管理员密码是否存在
    function AdminPWDExists()
    {
        $req = array(
            "method" => "admin_pwd_exists",
            "parms" => array(
                "name" => "admin",
            )
        );
        return $this->request($req);
    }
    //创建DedeBIZ授权密码
    function AdminPWDCreate($pwd)
    {
        $req = array(
            "method" => "admin_pwd_create",
            "parms" => array(
                "pwd" => $pwd,
            )
        );
        return $this->request($req);
    }
    //设置首页锁定状态
    function AdminSetIndexLockState($pwd, $state)
    {
        $req = array(
            "method" => "admin_set_index_lock_state",
            "parms" => array(
                "pwd" => $pwd,
                "lock_state" => $state,
            )
        );
        return $this->request($req);
    }
    //缓存：$key键 $val值 $d缓存时间
    function CacheSet($key, $val, $duration)
    {
        $req = array(
            "method" => "cache_set",
            "parms" => array(
                "k" => $key,
                "v" => $val,
                "d" => (string)$duration,
            )
        );
        return $this->request($req);
    }
    //获取缓存文档：$key键
    function CacheGet($key)
    {
        $req = array(
            "method" => "cache_get",
            "parms" => array(
                "k" => $key,
            )
        );
        return $this->request($req);
    }
    //删除缓存文档：$key键
    function CacheDel($key)
    {
        $req = array(
            "method" => "cache_del",
            "parms" => array(
                "k" => $key,
            )
        );
        return $this->request($req);
    }
    //获取分词结果：$key键
    function Spliteword($body)
    {
        $req = array(
            "method" => "spliteword",
            "parms" => array(
                "body" => $body,
            )
        );
        return $this->request($req);
    }
    //获取分词结果：$body文档 $sep分隔符
    function Pinyin($body, $sep)
    {
        $req = array(
            "method" => "pinyin",
            "parms" => array(
                "body" => $body,
                "sep" => $sep,
            )
        );
        return $this->request($req);
    }
    //拼接规则就是method+
    function MakeSign(&$req)
    {
        if (empty($req['timestamp'])) {
            $req['timestamp'] = time();
        }
        if (isset($req['parms']) && count($req['parms']) > 0) {
            ksort($req['parms']);
        }
        $pstr = "appid={$this->appid}method={$req['method']}key={$this->key}";
        if (isset($req['parms']) && count($req['parms']) > 0) {
            foreach ($req['parms'] as $key => $value) {
                $pstr .= "$key=$value";
            }
        }
        $pstr .= "timestamp={$req['timestamp']}";
        $req['sign'] = hash("sha256", $pstr);
    }
    //校验返回数据是否正确
    function CheckSign(&$msg)
    {
        $rsp = json_decode($msg);
        if (!is_object($rsp)) {
            return null;
        }
        $str = sprintf("appid=%skey=%scode=%dmsg=%sdata=%stimestamp=%d", $this->appid, $this->key, $rsp->code, $rsp->msg, $rsp->data, $rsp->timestamp);
        if (hash("sha256", $str) === $rsp->sign) {
            return $rsp;
        } else {
            return null;
        }
    }
    //关闭通信接口，一次页面操作后一定记得要关闭连接，否则会占用系统资源
    function Close()
    {
        //这里避免重复释放
        try {
            if (strtolower(get_resource_type($this->socket)) === "socket") {
                socket_close($this->socket);
            }
            return true;
        } catch (TypeError $e) {
            return false;
        } catch (Exception $e) {
            return false;
         }
        
    }
    function __destruct()
    {
        $this->Close();
    }
}
?>