<?php
/**
 * 栏目操作
 *
 * @version        $id:catalog_do.php 14:31 2010年7月12日 tianya $
 * @package        DedeBIZ.Administrator
 * @copyright      Copyright (c) 2022 DedeBIZ.COM
 * @license        GNU GPL v2 (https://www.dedebiz.com/license)
 * @link           https://www.dedebiz.com
 */
require_once(dirname(__FILE__).'/config.php');
if (empty($dopost)) {
    ShowMsg("请指定一个栏目参数", "catalog_main.php");
    exit();
}
$cid = empty($cid) ? 0 : intval($cid);
$unittype = empty($unittype) ? 0 : intval($unittype);
$channelid = empty($channelid) ? 0 : intval($channelid);
//添加文档
if ($dopost == "addArchives") {
    //默认文档调用发布表单
    if (empty($cid) && empty($channelid)) {
        header("location:article_add.php");
        exit();
    }
    if (!empty($channelid)) {
        //根据模型调用发布表单
        $row = $dsql->GetOne("SELECT addcon FROM `#@__channeltype` WHERE id='$channelid'");
    } else {
        //根据栏目调用发布表单
        $row = $dsql->GetOne("SELECT ch.addcon FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$cid' ");
    }
    $gurl = $row["addcon"];
    if ($gurl == "") {
        ShowMsg("操作失败，正在返回", "catalog_main.php");
        exit();
    }
    //跳转并传递参数
    header("location:{$gurl}?channelid={$channelid}&cid={$cid}");
    exit();
}
//管理文档
else if ($dopost == "listArchives") {
    if (!empty($gurl)) {
        if (empty($arcrank)) {
            $arcrank = '';
        }
        $gurl = str_replace('..', '', $gurl);
        header("location:{$gurl}?arcrank={$arcrank}&cid={$cid}");
        exit();
    }
    if ($cid > 0) {
        $row = $dsql->GetOne("SELECT `#@__arctype`.typename,`#@__channeltype`.typename AS channelname,`#@__channeltype`.id,`#@__channeltype`.mancon FROM `#@__arctype` LEFT JOIN `#@__channeltype` on `#@__channeltype`.id=`#@__arctype`.channeltype WHERE `#@__arctype`.id='$cid'");
        $gurl = $row["mancon"];
        $channelid = $row["id"];
        $typename = $row["typename"];
        $channelname = $row["channelname"];
        if ($gurl == "") {
            ShowMsg("操作失败，正在返回", "catalog_main.php");
            exit();
        }
    } else if ($channelid > 0) {
        $row = $dsql->GetOne("SELECT typename,id,mancon FROM `#@__channeltype` WHERE id='$channelid'");
        $gurl = $row["mancon"];
        $channelid = $row["id"];
        $typename = '';
        $channelname = $row["typename"];
    }
    if (empty($gurl)) $gurl = 'content_list.php';
    header("location:{$gurl}?channelid={$channelid}&cid={$cid}");
    exit();
}
//浏览通用模板目录
else if ($dopost == "viewTemplet") {
    header("location:tpl.php?path=/".$cfg_df_style);
    exit();
}
//浏览单个页面的栏目
else if ($dopost == "viewSgPage") {
    require_once(DEDEINC."/archive/listview.class.php");
    $lv = new ListView($cid);
    $pageurl = $lv->MakeHtml();
    ShowMsg("更新缓冲，请稍后", $pageurl);
    exit();
}
//修改栏目排列顺序
else if ($dopost == "upRank") {
    //检查权限许可
    CheckPurview('t_Edit,t_AccEdit');
    //检查栏目操作许可
    CheckCatalog($cid, "您无权修改本栏目");
    $row = $dsql->GetOne("SELECT reid,sortrank FROM `#@__arctype` WHERE id='$cid'");
    $reid = $row['reid'];
    $sortrank = $row['sortrank'];
    $row = $dsql->GetOne("SELECT sortrank FROM `#@__arctype` WHERE sortrank<=$sortrank AND reid=$reid ORDER BY sortrank DESC ");
    if (is_array($row)) {
        $sortrank = $row['sortrank'] - 1;
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET sortrank='$sortrank' WHERE id='$cid'");
    }
    UpDateCatCache();
    ShowMsg("成功更新栏目排序", "catalog_main.php");
    exit();
}
//检查权限许可
else if ($dopost == "upRankAll") {
    CheckPurview('t_Edit');
    $row = $dsql->GetOne("SELECT id FROM `#@__arctype` ORDER BY id DESC");
    if (is_array($row)) {
        $maxID = $row['id'];
        for ($i = 1; $i <= $maxID; $i++) {
            if (isset(${'sortrank'.$i})) {
                $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET sortrank='".(${'sortrank'.$i})."' WHERE id='{$i}';");
            }
        }
    }
    UpDateCatCache();
    ShowMsg("成功更新栏目排序", "catalog_main.php");
    exit();
}
//更新栏目缓存
else if ($dopost == "upcatcache") {
    UpDateCatCache();
    $sql = " TRUNCATE TABLE `#@__arctiny`";
    $dsql->ExecuteNoneQuery($sql);
    //导入普通模型微数据
    $sql = "INSERT INTO `#@__arctiny` (id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) SELECT id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid FROM `#@__archives` ";
    $dsql->ExecuteNoneQuery($sql);
    //导入自定义模型微数据
    $dsql->SetQuery("SELECT id,addtable FROM `#@__channeltype` WHERE id < -1 ");
    $dsql->Execute();
    $doarray = array();
    while ($row = $dsql->GetArray()) {
        $tb = str_replace('#@__', $cfg_dbprefix, $row['addtable']);
        if (empty($tb) || isset($doarray[$tb])) {
            continue;
        } else {
            $sql = "INSERT INTO `#@__arctiny` (id, typeid, typeid2, arcrank, channel, senddate, sortrank, mid) SELECT aid, typeid, 0, arcrank, channel, senddate, 0, mid FROM `$tb` ";
            $rs = $dsql->ExecuteNoneQuery($sql);
            $doarray[$tb]  = 1;
        }
    }
    ShowMsg("成功更新栏目缓存", "catalog_main.php");
    exit();
}
//获得子类的文档
else if ($dopost == "GetSunListsMenu") {
    $userChannel = $cuserLogin->getUserChannel();
    require_once(DEDEINC."/typelink/typeunit.class.menu.php");
    AjaxHead();
    PutCookie('lastCidMenu', $cid, 3600 * 24, "/");
    $tu = new TypeUnit($userChannel);
    $tu->LogicListAllSunType($cid, "　");
} else if ($dopost == "GetSunLists") {
    require_once(DEDEINC."/typelink/typeunit.class.admin.php");
    AjaxHead();
    PutCookie('lastCid', $cid, 3600 * 24, "/");
    $tu = new TypeUnit();
    $tu->dsql = $dsql;
    $tu->LogicListAllSunType($cid, "");
    $tu->Close();
}
//合并栏目
else if ($dopost == 'unitCatalog') {
    CheckPurview('t_Move');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    require_once(DEDEINC.'/typelink/typelink.class.php');
    require_once(DEDEINC.'/channelunit.func.php');
    if (empty($nextjob)) {
        $typeid = isset($typeid) ? intval($typeid) : 0;
        $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctype` WHERE reid='$typeid' ");
        $tl = new TypeLink($typeid);
        $typename = $tl->TypeInfos['typename'];
        $reid = $tl->TypeInfos['reid'];
        $channelid = $tl->TypeInfos['channeltype'];
        if (!empty($row['dd'])) {
            ShowMsg("栏目".$typename."有子栏目，不能进行合并操作", '-1');
            exit();
        }
        $typeOptions = $tl->GetOptionArray(0, 0, $channelid);
        $wintitle = "合并指定栏目";
        $win = new OxWindow();
        $win->Init('catalog_do.php', '/static/web/js/admin.blank.js', 'POST');
        $win->AddHidden('dopost', 'unitCatalog');
        $win->AddHidden('typeid', $typeid);
        $win->AddHidden('channelid', $channelid);
        $win->AddHidden('nextjob', 'unitok');
        $win->AddTitle("合并目录时不会删除原来的栏目目录，合并后需手动更新目标栏目的文档网页和列表网页，栏目不能有下级子栏目，只允许子级到更高级或同级或不同父级的情况");
        $win->AddItem('您选择的栏目是：', "$typename");
        $win->AddItem('您希望合并到那个栏目', "<select name='unittype'>{$typeOptions}</select>");
        $winform = $win->GetWindow('ok');
        $win->Display();
        exit();
    } else {
        if ($typeid == $unittype) {
            ShowMsg("同一栏目无法合并，请重新合并", '-1');
            exit();
        }
        if (IsParent($unittype, $typeid)) {
            ShowMsg('不能从父类合并到子类', 'catalog_main.php');
            exit();
        }
        $row = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
        $addtable = (empty($row['addtable']) ? '#@__addonarticle' : $row['addtable']);
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `#@__feedback` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid2='$unittype' WHERE typeid2='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `#@__addonspec` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("UPDATE `$addtable` SET typeid='$unittype' WHERE typeid='$typeid' ");
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctype` WHERE id='$typeid' ");
        UpDateCatCache();
        ShowMsg('成功合并指定栏目', 'catalog_main.php');
        exit();
    }
}
//移动栏目
else if ($dopost == 'moveCatalog') {
    CheckPurview('t_Move');
    require_once(DEDEINC.'/libraries/oxwindow.class.php');
    require_once(DEDEINC.'/typelink/typelink.class.php');
    require_once(DEDEINC.'/channelunit.func.php');
    if (empty($nextjob)) {
        $tl = new TypeLink($typeid);
        $typename = $tl->TypeInfos['typename'];
        $reid = $tl->TypeInfos['reid'];
        $channelid = $tl->TypeInfos['channeltype'];
        $typeOptions = $tl->GetOptionArray(0, 0, $channelid);
        $wintitle = "移动指定栏目";
        $win = new OxWindow();
        $win->Init('catalog_do.php', '/static/web/js/admin.blank.js', 'POST');
        $win->AddHidden('dopost', 'moveCatalog');
        $win->AddHidden('typeid', $typeid);
        $win->AddHidden('channelid', $channelid);
        $win->AddHidden('nextjob', 'unitok');
        $win->AddTitle("移动目录时不会删除原来已创建的列表，移动后需重新对栏目创建网页，不允许从父级移动到子级目录，只允许子级到更高级或同级或不同父级的情况");
        $win->AddItem('您选择的栏目是：', "$typename");
        $win->AddItem('您希望移动到那个栏目', "<select name='movetype'>\r\n<option value='0'>移动为顶级栏目</option>\r\n$typeOptions\r\n</select>");
        $winform = $win->GetWindow('ok');
        $win->Display();
        exit();
    } else {
        if ($typeid == $movetype) {
            ShowMsg('移对对象和目标位置相同', 'catalog_main.php');
            exit();
        }
        if (IsParent($movetype, $typeid)) {
            ShowMsg('不能从父类移动到子类', 'catalog_main.php');
            exit();
        }
        $topid = GetTopid($movetype);
        $dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET reid='$movetype',topid='$topid' WHERE id='$typeid' ");
        UpDateCatCache();
        ShowMsg('成功移动目录', 'catalog_main.php');
        exit();
    }
}
//查看跨站调用秘钥
else if ($dopost == 'viewAPI') {
    require_once(DEDEINC.'/typelink/typelink.class.php');
    $typeid = isset($typeid) ? intval($typeid) : 0;
    $tl = new TypeLink($typeid);
    $phpCode = '<?php 
    $typeid = '.$typeid.';
    $row = 10;
    $timestamp = time();
    $apikey = \''.$tl->TypeInfos['apikey'].'\';
    $sign = md5($typeid.$timestamp.$apikey.\'1\'.$row);
    $durl = "'.$cfg_basehost.'/apps/list.php?tid={$typeid}&mod=1&timestamp={$timestamp}&PageNo=1&PageSize={$row}&sign={$sign}";
    $data = json_decode(file_get_contents($durl),true);
    if ($data[\'code\'] === 0) {
    	var_dump($data);
    }
 ?>';
    $gocode = 'package main
import (
    "crypto/md5"
    "encoding/json"
    "fmt"
    "io/ioutil"
    "net/http"
    "strconv"
    "time"
)
func main() {
    typeid := '.$typeid.'
    row := 10
    timestamp := strconv.FormatInt(time.Now().Unix(), 10)
    apikey := "'.$tl->TypeInfos['apikey'].'"
    sign := fmt.Sprintf("%x", md5.Sum([]byte(fmt.Sprintf("%d%s%s%d%d", typeid, timestamp, apikey, 1, row))))
    durl := fmt.Sprintf("'.$cfg_basehost.'/apps/list.php?tid=%d&mod=1&timestamp=%s&PageNo=1&PageSize=%d&sign=%s", typeid, timestamp, row, sign)
    resp, err := http.Get(durl)
    if err != nil {
        fmt.Println(err)
        return
    }
    defer resp.Body.Close()
    body, err := ioutil.ReadAll(resp.Body)
    if err != nil {
        fmt.Println(err)
        return
    }
    var data map[string]interface{}
    if err := json.Unmarshal(body, &data); err != nil {
        fmt.Println(err)
        return
    }
    if data["code"].(float64) == 0 {
        fmt.Printf("%+v", data)
    }
}';
    $pythoncode = 'import hashlib
import json
import time
import urllib.request
typeid = '.$typeid.'
row = 10
timestamp = int(time.time())
apikey = \''.$tl->TypeInfos['apikey'].'\'
sign = hashlib.md5((str(typeid) + str(timestamp) + apikey + \'1\' + str(row)).encode()).hexdigest()
durl = f"'.$cfg_basehost.'/apps/list.php?tid={typeid}&mod=1&timestamp={timestamp}&PageNo=1&PageSize={row}&sign={sign}"
with urllib.request.urlopen(durl) as url:
    data = json.loads(url.read().decode())
if data[\'code\'] == 0:
    print(data)
';
    $jscode = 'const crypto = require(\'crypto\');
const http = require(\'http\');
const typeid = '.$typeid.';
const row = 10;
const timestamp = Math.floor(Date.now() / 1000);
const apikey = \''.$tl->TypeInfos['apikey'].'\';
const sign = crypto.createHash(\'md5\').update(typeid.toString() + timestamp.toString() + apikey + \'1\' + row.toString()).digest(\'hex\');
const durl = `'.$cfg_basehost.'/apps/list.php?tid=${typeid}&mod=1&timestamp=${timestamp}&PageNo=1&PageSize=${row}&sign=${sign}`
http.get(durl, (res) => {
    let data = \'\';
    res.on(\'data\', (chunk) => {
        data += chunk;
    });
    res.on(\'end\', () => {
        const result = JSON.parse(data);
        if (result.code === 0) {
            console.log(result);
        }
    });
}).on(\'error\', (err) => {
    console.log(err);
});';
    $tagcode = '<ul>
    {dede:jsonq url="'.$cfg_basehost.'" row="10" typeid="'.$typeid.'" apikey="'.$tl->TypeInfos['apikey'].'"}
    <li><a href="[field:arcurl/]">[field:fulltitle/]</a></li>
    {/dede:jsonq}
</ul>';
    echo json_encode(array(
        "code"=>0,
        "data"=>array(
            "phpcode"=>htmlspecialchars($phpCode),
            "gocode"=>htmlspecialchars($gocode),
            "pythoncode"=>htmlspecialchars($pythoncode),
            "jscode"=>htmlspecialchars($jscode),
            "tagcode"=>htmlspecialchars($tagcode),
        )
    ));
}
?>