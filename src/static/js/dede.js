/* Copyright 2020 The DedeBIZ.COM Authors. All rights reserved.
license that can be found in the LICENSE file. */

// 滚动到页面顶部
function gotop() {
    $('html, body').animate({ scrollTop: 0 }, 'slow');
}

//读写cookie函数
function GetCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return null
}

function SetCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()); //使设置的有效时间正确。增加toGMTString()
}

//-------------------------------------------------------------------------------------------
// 全局消息提示框
//-------------------------------------------------------------------------------------------

// 生成一个随机ID
function guid() {
    function S4() {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    }
    return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
}

// 显示对话框，动态创建modal并显示，退出自动销毁窗体
// args是以下结构体
/*
args = {
    title : "",         // 标题，默认是MuEMS
    footer : "",        // 底部按钮，可以自定义按钮
    noClose : false,    // 是否显示右上角关闭按钮，默认显示
}
*/
// 函数会返回一个modalID，通过这个ID可自已定义一些方法
// 这里用到了一个展开语法
// https://developer.mozilla.org/zh-CN/docs/Web/JavaScript/Reference/Operators/Spread_syntax
function ShowMsg(content, ...args) {
    title = "DedeBIZ信息提示";
    if (typeof content == "undefined") content = "";
    modalID = guid();
    var footer = `<button type="button" class="btn btn-outline-success" onClick="CloseModal(\'DedeModal${modalID}\')">确定</button>`;
    var noClose = false;

    if (args.length == 1) {
        // 存在args参数
        if (typeof args[0].title !== 'undefined' && args[0].title != "") {
            title = args[0].title;
        }
        if (typeof args[0].footer !== 'undefined' && args[0].footer != "") {
            footer = args[0].footer;
        }
        if (typeof args[0].noClose !== 'undefined' && args[0].noClose == true) {
            noClose = true;
        }
    }

    String.prototype.replaceAll = function (s1, s2) {
        return this.replace(new RegExp(s1, "gm"), s2);
    }
    footer = footer.replaceAll("~modalID~", modalID);
    content = content.replaceAll("~modalID~", modalID);

    var modal = `<div id="DedeModal${modalID}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="DedeModalLabel${modalID}" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
<div class="modal-content"><div class="modal-header">
<h6 class="modal-title" id="DedeModalLabel${modalID}">${title}</h6>`;
    if (!noClose) {
        modal += `<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
		</button>`;
    }
    modal += `</div><div class="modal-body">${content}</div><div class="modal-footer">${footer}</div></div></div></div>`;
    $("body").append(modal)
    $("#DedeModal" + modalID).modal({
        backdrop: 'static',
        show: true
    });
    $("#DedeModal" + modalID).on('hidden.bs.modal', function (e) {
        $("#DedeModal" + modalID).remove();
    })
    return modalID;
}

// 隐藏并销毁modal
function CloseModal(modalID) {
    $("#" + modalID).modal('hide');
    $("#" + modalID).on('hidden.bs.modal', function (e) {
        if ($("#" + modalID).length > 0) {
            $("#" + modalID).remove();
        }
    })
}

// 在某个元素内显示alert信息
function ShowAlert(ele, content, type, showtime = 3000) {
    let msg = `<div class="alert alert-${type}" role="alert">
        ${content}
    </div>`;
    $(ele).html(msg);
    $(ele).show();
    setTimeout(() => {
        $(ele).html("");
    }, showtime);
}

//-------------------------------------------------------------------------------------------
// 纠错扩展
//-------------------------------------------------------------------------------------------
// 提交纠错信息
function ErrAddSaveDo(modalID) {
    let aid = $("#iptID").val();
    let title = $("#iptTitle").val();
    let type = $("#selType").val();
    let err = $("#iptErr").val();
    let erradd = $("#iptErradd").val();
    let parms = {
        format: "json",
        dopost: "saveedit",
        aid: aid,
        title: title,
        type: type,
        err: err,
        erradd: erradd,
    };
    $("#btnSubmit").attr("disabled", "disabled");
    if (typeof PHPURL === "undefined") {
        const PHPURL = "/plus";
    }
    $.post(PHPURL + "/erraddsave.php", parms, function (data) {
        let result = JSON.parse(data);
        if (result.code === 200) {
            CloseModal(modalID);
        } else {
            ShowAlert("#error-add-alert", `提交失败：${result.msg}`, "danger");
        }
        $("#btnSubmit").removeAttr("disabled");
    });
}

// 错误提示
function ErrorAddSave(id, title) {
    let content = `
    <input type="hidden" value="${id}" class="form-control" id="iptID">
    <div class="form-group">
	<div id="error-add-alert">
	</div>
    <label for="iptTitle" class="col-form-label">标题：</label>
    <input type="text" disabled=true value="${title}" class="form-control" id="iptTitle">
    </div>
    <div class="form-group">
    <label for="message-text" class="col-form-label">错误类型：</label>
    <select id="selType" class="form-control">
            <option value="1">错别字(除的、地、得)</option>
            <option value="2">成语运用不当</option>
            <option value="3">专业术语写法不规则</option>
            <option value="4">产品与图片不符</option>
            <option value="5">事实年代以及内容错误</option>
            <option value="6">技术参数错误</option>
            <option value="7">其他</option>
    </select>
    </div>
    <div class="form-group">
    <label for="message-text" class="col-form-label">错误内容：</label>
    <textarea name="iptErr" class="form-control" id="iptErr"></textarea>
    </div>
    <div class="form-group">
    <label for="message-text" class="col-form-label">修正建议：</label>
    <textarea name="optErradd" class="form-control" id="iptErradd"></textarea>
    </div>
        `;
    let footer = `
        <button type="button" id="btnSubmit" class="btn btn-success" onClick="ErrAddSaveDo('DedeModal~modalID~')">提交</button>
        <button type="button" class="btn btn-outline-success" onClick="CloseModal('DedeModal~modalID~')">确定</button>
        `;
    ShowMsg(content, {
        'footer': footer,
    });
}


// 页面加载触发
$(document).ready(function () {

    window.onscroll = function () { scrollFunction() };

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            $("#btnScrollTop").show();
        } else {
            $("#btnScrollTop").hide();
        }
    }

});