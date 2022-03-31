function AddNew()
{
    $DE('addTab').style.display = 'block';
}
function CloseTab(tb)
{
    $DE(tb).style.display = 'none';
}
function ListAll(){
    $DE('editTab').style.display = 'block';
	fetch('index_body.php?dopost=editshow').then(resp=>resp.text()).then((d)=>{
		$DE('editTabBody').innerHTML = d;
	});
}
function ShowWaitDiv(){
    $DE('loaddiv').style.display = 'block';
    return true;
}
$(function(){
    $.get("index_testenv.php", function (data){
        if (data !== ''){
            $("#tips").html(data);
        }
    });
    $.get("index_body.php?dopost=get_articles", function (data){
        if (data !== ''){
            $("#newarticles").html(data);
        }
    });
});
//Dedebiz info
var dedebizInfo;
function ViewDedeBIZ(){
    console.log(dedebizInfo);
    ShowMsg(`
    <table width="100%" class="table table-borderless">
        <tbody>
            <tr>
                <td style="width:50%">版本号：</td>
                <td>V${dedebizInfo.result.server_version}</td>
            </tr>
            <tr>
                <td style="width:50%">运行时间：</td>
                <td>${dedebizInfo.result.server_run_time}</td>
            </tr>
            <tr>
                <td style="width:50%">服务器系统：</td>
                <td>${dedebizInfo.result.server_goos}（${dedebizInfo.result.server_goarch}）</td>
            </tr>
            <tr>
                <td style="width:50%">内存占用：</td>
                <td>${dedebizInfo.result.server_memory_usage}%</td>
            </tr>
        </tbody>
    </table>
    `);
}
function LoadServer(){
    $.get("index_body.php?dopost=system_info", function(data){
        let rsp = JSON.parse(data);
        if (rsp.code === 200){
            let infoStr = `<table width="100%" class="table table-borderless"><tbody>`;
            if (typeof rsp.result.domain !== "undefined"){
                infoStr += `
                <tr>
                    <td style="width:50%">授权域名：</td>
                    <td>${rsp.result.domain} <a href="${cfg_biz_dedebizUrl}/auth/?domain=${rsp.result.domain}" class="btn btn-success btn-sm">查看</a></td>
                </tr>
                `;
            }
            if (typeof rsp.result.title !== "undefined"){
                infoStr += `
                <tr>
                    <td style="width:50%">站点名称：</td>
                    <td>${rsp.result.title}</td>
                </tr>
                `;
            }
            if (typeof rsp.result.stype !== "undefined"){
                infoStr += `
                <tr>
                    <td style="width:50%">站点类型：</td>
                    <td>${rsp.result.stype}</td>
                </tr>
                `;
            }
            if (typeof rsp.result.auth_version !== "undefined" && typeof rsp.result.auth_at !== "undefined"){
                infoStr += `
                <tr>
                    <td style="width:50%">授权版本：</td>
                    <td>V${rsp.result.auth_version}.x.x（时间：${rsp.result.auth_at}）</td>
                </tr>
                `;
            }
            if (rsp.result.core === null || rsp.result.core.code != 200){
                //下面是DedeBIZ Core组件信息
                infoStr += `
                <tr>
                    <td style="width:50%">版本组件：</td>
                    <td><a href="${cfg_biz_dedebizUrl}/start?code=-1008" target="_blank" class="btn btn-danger btn-sm">如何启动组件</a></td>
                </tr>
                `;
            } else {
                dedebizInfo = JSON.parse(rsp.result.core.data);
                infoStr += `
                <tr>
                    <td style="width:50%">版本组件：</td>
                    <td><a href="javascript:ViewDedeBIZ()" class="btn btn-success btn-sm">查看组件信息</a></td>
                </tr>
                `;
            }
            infoStr += "</tbody></table>";
            $("#_systeminfo").html(infoStr);
        } else {
            $("#_systeminfo").html(`
            <table width="100%" class="table table-borderless">
                <tbody>
                    <tr>
                        <td style="width:60%">尚未启动商业版服务，原因：${rsp.msg}</td>
                        <td style="text-align:right">当前版本：社区版<a href="${cfg_biz_dedebizUrl}/start?code=${rsp.code}" target="_blank" class="btn btn-success btn-sm" style="margin-left:10px">升级商业版</a></td>
                    </tr>
                    <tr>
                        <td colspan="2">如果您已购买商业版授权，您可以在我们的授权中心查询到相信关授权信息，如果查询结果与实际授权不符，则说明您可能购买了非法商业授权，请及时与我们取得联系，谢谢。</td>
                    </tr>
                </tbody>
            </table>
            `);
        }
    });
}
Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
        "S": this.getMilliseconds() //毫秒 
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}
function LoadStat() {
    $.get("index_body.php?dopost=get_statistics", function(data){
        let rsp = JSON.parse(data);
        if (rsp.code == 200) {
            var tpv = parseInt(rsp.result.pv);
            var tuv = parseInt(rsp.result.uv);
            var tip = parseInt(rsp.result.ip);
            var tvv = parseInt(rsp.result.vv);
            $("#today_pv").html(tpv);
            $("#today_uv").html(tuv);
            $("#today_ip").html(tip);
            $("#today_vv").html(tvv);
            $.get("index_body.php?dopost=get_statistics&sdate=-1", function(data){
                let rsp = JSON.parse(data);
                if (rsp.code == 200) {
                    $("#total_pv").html(parseInt(rsp.result.pv)+tpv);
                    $("#total_uv").html(parseInt(rsp.result.uv)+tuv);
                    $("#total_ip").html(parseInt(rsp.result.ip)+tip);
                    $("#total_vv").html(parseInt(rsp.result.vv)+tvv);
                }
            });
        }
    });
    var d = new Date();
    d.setDate(d.getDate() - 1);
    var s = d.Format("yyyy-MM-dd");
    s = s.replaceAll("-","");
    $.get("index_body.php?dopost=get_statistics&sdate="+s, function(data){
        let rsp = JSON.parse(data);
        if (rsp.code == 200) {
            $("#yestoday_pv").html(rsp.result.pv);
            $("#yestoday_uv").html(rsp.result.uv);
            $("#yestoday_ip").html(rsp.result.ip);
            $("#yestoday_vv").html(rsp.result.vv);
        }
    });
}
async function LoadStatChart() {
    const ctx = document.getElementById('statChart').getContext('2d');
    let labels = [];
    let pvs = [];
    let ips = [];
    let uvs = [];
    let vvs = [];
    for (let i = 15; i > 0; i--) {
        var d = new Date();
        d.setDate(d.getDate() - i);
        var s = d.Format("yyyy-MM-dd");
        labels.push(d.Format("MM-dd"));
        s = s.replaceAll("-","");
        let resp = await fetch("index_body.php?dopost=get_statistics&sdate="+s);
        let data = await resp.json();
        if (data.code == 200) {
            pvs.push(typeof data.result.pv=="undefined"? 0 : data.result.pv);
            ips.push(typeof data.result.ip=="undefined"? 0 : data.result.ip);
            uvs.push(typeof data.result.uv=="undefined"? 0 : data.result.uv);
            vvs.push(typeof data.result.vv=="undefined"? 0 : data.result.vv);
        }
    }
    const myChart = new Chart(ctx, {
        type: 'line',
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `${cfg_webname}流量统计图`
                }
            }
        },
        data: {
            labels: labels,
            datasets: [{
                label: 'IP',
                data: ips,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor :'rgba(255, 99, 132, 0.2)',
                borderWidth: 1
            },
            {
                label: 'PV',
                data: pvs,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor :'rgba(54, 162, 235, 0.2)',
                borderWidth: 1
            },{
                label: 'UV',
                data: uvs,
                borderColor: 'rgba(255, 206, 86, 1)',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderWidth: 1
            },{
                label: '访问次数',
                data: vvs,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 1
            }
        ]
        },
    });
}
$(document).ready(function(){
    LoadServer();
    LoadStat();
    LoadStatChart();
    setInterval(function(){
        LoadServer();
    }, 60000)
});