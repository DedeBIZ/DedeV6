Date.prototype.Format = function(fmt) {
	var o = {
		"M+" : this.getMonth() + 1, //月份 
		"d+" : this.getDate(), //日 
		"h+" : this.getHours(), //小时 
		"m+" : this.getMinutes(), //分 
		"s+" : this.getSeconds(), //秒 
		"q+" : Math.floor((this.getMonth() + 3) / 3), //季度 
		"S" : this.getMilliseconds(), //毫秒 
	};
	if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	for (var k in o)
		if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	return fmt;
}
function LoadStat() {
	$.get("index_body.php?dopost=get_statistics",function(data) {
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
				$.get("index_body.php?dopost=get_statistics&sdate=-1",function(data) {
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
			console.log("加载流量统计数据失败")
		}
	});
	var d = new Date();
	d.setDate(d.getDate() - 1);
	var s = d.Format("yyyy-MM-dd");
	s = s.replaceAll("-", "");
	$.get("index_body.php?dopost=get_statistics&sdate=" + s,function(data) {
		try {
			let rsp = JSON.parse(data);
			if (rsp.code == 200) {
				$("#yestoday_pv").html(rsp.result.pv);
				$("#yestoday_uv").html(rsp.result.uv);
				$("#yestoday_ip").html(rsp.result.ip);
				$("#yestoday_vv").html(rsp.result.vv);
			}
		} catch (error) {
			console.log("加载流量统计数据失败")
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
	Chart.defaults.font.size = 14;
	Chart.defaults.color = '#545b62';
	const myChart = new Chart(ctx, {
		type: 'line',
		options: {
			responsive: true,
			maintainAspectRatio: false,
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
					lineTension: .5,
					borderColor: 'rgba(54, 162, 235, 1)',
					backgroundColor: 'rgba(54, 162, 235, 0.1)',
					borderWidth: 2,
				}, {
					label: 'UV',
					data: uvs,
					lineTension: .5,
					borderColor: 'rgba(255, 206, 86, 1)',
					backgroundColor: 'rgba(255, 206, 86, 0.1)',
					borderWidth: 2,
				}, {
					label: 'IP',
					data: ips,
					lineTension: .5,
					borderColor: 'rgba(255, 99, 132, 1)',
					backgroundColor: 'rgba(255, 99, 132, 0.1)',
					borderWidth: 2,
				}, {
					label: 'VV',
					data: vvs,
					lineTension: .5,
					borderColor: 'rgba(75, 192, 192, 1)',
					backgroundColor: 'rgba(75, 192, 192, 0.1)',
					borderWidth: 2,
				}
			]
		},
	});
}
$(document).ready(function() {
	$.get("index_testenv.php",function(data) {
		if (data !== '') {
			$("#body-tips").html(data);
		}
	});
	$.get("index_body.php?dopost=get_articles",function(data) {
		if (data !== '') {
			$("#system-word").html(data);
		}
	});
	$(function() {
		var dedebizInfo;
		$.get("index_body.php?dopost=system_info",function(data) {
			let rsp = JSON.parse(data);
			if (rsp.code === 200) {
				if (rsp.result.core.code === 200) {
					dedebizInfo = JSON.parse(rsp.result.core.data);
				} else {
					dedebizInfo = false;
				}
				let infoStr = `<table class="table table-borderless">`;
				if (typeof rsp.result.domain !== "undefined") {
					infoStr += `<tr>
						<td width="25%">
							<div class="web-info no-wrap">
								<p>授权域名</p>
								<span>${rsp.result.domain}</span>
							</div>
						</td>
						<td width="25%">
							<div class="web-info no-wrap">
								<p>站点名称</p>
								<span>${rsp.result.title}</span>
							</div>
						</td>
						<td width="25%">
							<div class="web-info no-wrap">
								<p>授权证书</p>
								<span><a href="${cfg_biz_dedebizUrl}/auth/?domain=${rsp.result.domain}" target="_blank">查看证书</a></span>
							</div>
						</td>
						<td width="25%">
							<div class="web-info no-wrap">
								<p>授权时间</p>
								<span>${rsp.result.auth_at}</span>
							</div>
						</td>
					</tr>`;
				}
				infoStr += "</table>";
				$("#system-info").html(infoStr);
			} else {
				$("#system-info").html(`<table class="table table-borderless">
					<tr>
						<td>
							<div class="web-info no-wrap">
								<p>${rsp.msg}</p>
								<span>您已购买了商业版授权，登录DedeBIZ官网会员中心可查看相关授权信息</span>
							</div>
						</td>
					</tr>
				</table>`);
			}
		});
	});
	LoadStat();
	LoadStatChart();
});