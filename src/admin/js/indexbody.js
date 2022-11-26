function AddNew() {
    $DE('addTab').style.display = 'block';
}
function CloseTab(tb) {
    $DE(tb).style.display = 'none';
}
function ListAll() {
    $DE('editTab').style.display = 'block';
    fetch('index_body.php?dopost=editshow').then(resp => resp.text()).then((d) => {
        $DE('editTabBody').innerHTML = d;
    });
}
function ShowWaitDiv() {
    $DE('loaddiv').style.display = 'block';
    return true;
}
function DedeCopyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        var textarea = document.createElement('textarea');
        document.body.appendChild(textarea);
        //隐藏此输入框
        textarea.style.position = 'fixed';
        textarea.style.clip = 'rect(0 0 0 0)';
        textarea.style.top = '10px';
        textarea.style.display = 'none';
        textarea.value = text;
        textarea.select();
        document.execCommand('copy', true);
        document.body.removeChild(textarea);
    }
}
$(function () {
    $.get("index_testenv.php", function (data) {
        if (data !== '') {
            $("#body-tips").html(data);
        }
    });
    $.get("index_body.php?dopost=get_articles", function (data) {
        if (data !== '') {
            $("#system-word").html(data);
        }
    });
});
function Copyinfo() {
    var val = document.getElementById('text');
    window.getSelection().selectAllChildren(val);
    document.execCommand("Copy");
    alert("成功复制环境配置信息");
}
//Dedebiz info
var dedebizInfo;
function ViewDedeBIZ() {
    console.log(dedebizInfo);
    ShowMsg(`
    <table class="table table-borderless w-100">
        <tr>
            <td width="120">版本号：</td>
            <td>V${dedebizInfo.result.server_version}</td>
            <td width="120">服务器系统：</td>
            <td>${dedebizInfo.result.server_goos}（${dedebizInfo.result.server_goarch}）</td>
        </tr>
        <tr>
            <td>运行时间：</td>
            <td>${dedebizInfo.result.server_run_time}</td>
            <td>内存占用：</td>
            <td>${dedebizInfo.result.server_memory_usage}%</td>
        </tr>
    </table>
    `);
}
function LoadServer() {
    $.get("index_body.php?dopost=system_info", function (data) {
        let rsp = JSON.parse(data);
        if (rsp.code === 200) {
            if (rsp.result.core.code === 200) {
                dedebizInfo = JSON.parse(rsp.result.core.data);
            }
            
            let infoStr = `<table class="table table-borderless w-100">`;
            if (typeof rsp.result.domain !== "undefined") {
                infoStr += `
                <tr>
                    <td width="90">授权域名：</td>
                    <td>${rsp.result.domain}</td>
                    <td width="90">授权版本：</td>
                    <td>${rsp.result.auth_version}.x.x（时间：${rsp.result.auth_at}）</td>
                </tr>
                <tr>
                    <td>站点名称：</td>
                    <td>${rsp.result.title}（${rsp.result.stype}）</td>
                    <td>证书组件：</td>
                    <td>
                        <a href="${cfg_biz_dedebizUrl}/auth/?domain=${rsp.result.domain}" target="_blank" class="btn btn-success btn-sm">授权证书</a>
                        <a href="javascript:ViewDedeBIZ()" class="btn btn-primary btn-sm">组件信息</a>
                    </td>
                </tr>
                `;
            }
            infoStr += "</table>";
            $("#system-info").html(infoStr);
        } else {
            $("#system-info").html(`
            <table class="table table-borderless w-100">
                <tr>
                    <td>${rsp.msg}</td>
                </tr>
                <tr>
                    <td>如果您已购买商业版授权，可以在我们的授权中心查询到相信关授权信息，如果查询结果与实际授权不符，则说明您可能购买了非法商业授权，请及时与我们取得联系。</td>
                </tr>
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
    $.get("index_body.php?dopost=get_statistics", function (data) {
        try {
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
                $.get("index_body.php?dopost=get_statistics&sdate=-1", function (data) {
                    let rsp = JSON.parse(data);
                    if (rsp.code == 200) {
                        $("#total_pv").html(parseInt(rsp.result.pv) + tpv);
                        $("#total_uv").html(parseInt(rsp.result.uv) + tuv);
                        $("#total_ip").html(parseInt(rsp.result.ip) + tip);
                        $("#total_vv").html(parseInt(rsp.result.vv) + tvv);
                    }
                });
            }
        } catch (error) {
            console.log("加载统计信息失败")
        }
    });
    var d = new Date();
    d.setDate(d.getDate() - 1);
    var s = d.Format("yyyy-MM-dd");
    s = s.replaceAll("-", "");
    $.get("index_body.php?dopost=get_statistics&sdate=" + s, function (data) {
        try {
            let rsp = JSON.parse(data);
            if (rsp.code == 200) {
                $("#yestoday_pv").html(rsp.result.pv);
                $("#yestoday_uv").html(rsp.result.uv);
                $("#yestoday_ip").html(rsp.result.ip);
                $("#yestoday_vv").html(rsp.result.vv);
            }
        } catch (error) {
            console.log("加载统计信息失败")
        }
    });
}
async function LoadStatChart() {
    const ctx = document.getElementById('statChart').getContext('2d');
    let labels = [];
    let dates = [];
    let pvs = [];
    let ips = [];
    let uvs = [];
    let vvs = [];
    for (let i = 15; i > 0; i--) {
        var d = new Date();
        d.setDate(d.getDate() - i);
        var s = d.Format("yyyy-MM-dd");
        labels.push(d.Format("MM-dd"));
        s = s.replaceAll("-", "");
        dates.push(s);
    }
    let resp = await fetch("index_body.php?dopost=get_statistics_multi&sdates=" + dates.join(","));
    let data = await resp.json();
    if (data.code == 200) {
        data.result.forEach(e => {
            pvs.push(typeof e.pv == "undefined" ? 0 : e.pv);
            ips.push(typeof e.ip == "undefined" ? 0 : e.ip);
            uvs.push(typeof e.uv == "undefined" ? 0 : e.uv);
            vvs.push(typeof e.vv == "undefined" ? 0 : e.vv);
        });
    }
    const myChart = new Chart(ctx, {
        type: 'line',
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        },
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'PV',
                    data: pvs,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 1
                }, {
                    label: 'UV',
                    data: uvs,
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderWidth: 1
                }, {
                    label: 'IP',
                    data: ips,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderWidth: 1
                }
                , {
                    label: 'VV',
                    data: vvs,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1
                }
            ]
        },
    });
}
$(document).ready(function () {
    LoadServer();
    LoadStat();
    LoadStatChart();
    setInterval(function () {
        LoadServer();
    }, 60000)
});