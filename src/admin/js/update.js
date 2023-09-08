var currentStep = 1;
var hasNewVer = false;
//步骤
function dedeAlter(msg, t = 'info', loading = false) {
	let loadingStr = loading ? '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' : '';
	return `<div class="alert alert-${t}">${loadingStr}
		${msg}
	</div>`;
}
//显示步骤区域
function showStepArea(step) {
	$(".stepArea").hide();
	$(".btnStep").hide();
	$("#stepArea" + step).show();
	$("#btnStep" + step).show();
}
function update() {
	$.get("api.php?action=update", function(rs) {
		if (rs.code === 0) {
			$("#_updateMsg").html(rs.msg);
			if (rs.data.finish === false) {
				setTimeout(() => {
					update();
				}, 500);
			} else {
				currentStep++
				$("#_msgInfo").html('');
				$("#_msgInfo").hide();
				showStepArea(currentStep);
			}
		}
	})
}
function hasNewVersion() {
	$.get("api.php?action=has_new_version", function(rs) {
		try {
			if (rs.code === 0) {
				if (rs.result.HasNew === true) {
					hasNewVer = true;
					$(".updates-dot").show();
				} else {
					hasNewVer = false;
					$(".updates-dot").hide();
				}
			} else {
				$(".updates-dot").hide();
				showStepArea(0);
			}
		} catch (error) {
			console.log("获取软件信息失败")
		}
	})
}
$(document).ready(function() {
	hasNewVersion();
	$("#btnCancel").click(function() {
		currentStep = 1;
		$("#_fileList").html(``);
	})
	$("#btnBackup").click(function() {
		let alertMsg = dedeAlter("正在备份差异文件", 'info', true);
		$("#_msgInfo").html(alertMsg);
		$("#_msgInfo").show();
		$.get("api.php?action=update_backup", function(rs) {
			if (rs.code === 0) {
				alertMsg = dedeAlter(`成功备份差异文件，目录：${rs.data.backupdir}`, 'success');
				$("#_msgInfo").html(alertMsg);
			}
		})
	})
	$("#systemUpdate").click(function() {
		if (hasNewVer === false) {
			currentStep = 5;
			showStepArea(currentStep);
			$('#mdlUpdate').modal('show');
			return;
		}
		$('#mdlUpdate').modal('show');
		showStepArea(currentStep);
		currentStep++;
		$.get("api.php?action=get_changed_files", function(rs) {
			if (rs.code === 0) {
				let fstr = '<ul class="list-group list-group-flush">';
				let i = 1;
				rs.data.files.forEach(file => {
					fstr += `<li class='list-group-item'>第${i}个文件：${file['filename']}</li>`;
					i++;
				});
				fstr += '</ul>';
				$("#_fileList").html(fstr);
				showStepArea(currentStep);
			} else {
				showStepArea(0);
			}
		})
	})
	$('#mdlUpdate').on('hidden.bs.modal', function(event) {
		currentStep = 1;
		$("#_msgInfo").html('');
		$("#_msgInfo").hide();
	})
	$("#btnGoStep3").click(function() {
		currentStep++
		$("#_msgInfo").html('');
		$("#_msgInfo").hide();
		showStepArea(currentStep);
		$.get("api.php?action=get_update_versions", function(rs) {
			if (rs.code === 0) {
				let fstr = '<ul class="list-group list-group-flush">';
				let i = 1;
				rs.result.Versions.forEach(ver => {
					fstr += `<li class='list-group-item'>版本号：${ver.ver}，发布日期：${ver.r} <a href='https://www.zhelixie.com/DedeBIZ/DedeV6/commits/tag/${ver.ver}' class='btn btn-outline-success float-right' target='_blank'>更新记录</a></li>`;
					i++;
				});
				fstr += '</ul>';
				$("#_verList").html(fstr);
			} else {
				showStepArea(0);
			}
		})
	})
	$("#btnGoStep4").click(function() {
		currentStep++
		$("#_msgInfo").html('');
		$("#_msgInfo").hide();
		showStepArea(currentStep);
		update();
	})
	$("#btnOK").click(function() {
		hasNewVersion();
	})
})