var $ = jQuery;
var thespeed = 5;
var navIE = document.all && navigator.userAgent.indexOf("Firefox") == -1;
var myspeed = 0;
$(function () {
	//左侧菜单开关
	LeftMenuToggle();
	//全部功能开关
	AllMenuToggle();
	//取消菜单链接虚线
	$(".head").find("a").click(function () { $(this).blur() });
	$(".menu").find("a").click(function () { $(this).blur() });
}).keydown(function (event) {//快捷键
	if (event.keyCode == 116) {
	}
	if (event.keyCode == 27) {
		$("#qucikmenu").slideToggle("fast")
	}
});
function LeftMenuToggle() {//左侧菜单开关
	$("#togglemenu").click(function () {
		if ($("body").attr("class") == "showmenu") {
			$("body").attr("class", "hidemenu");
			$(this).html("<i class='fa fa-bars'></i> 显示菜单");
		} else {
			$("body").attr("class", "showmenu");
			$(this).html("<i class='fa fa-bars'></i> 隐藏菜单");
		}
	});
}
function AllMenuToggle() {//全部功能开关
	mask = $(".pagemask,.iframemask,.allmenu");
	$("#allmenu").click(function () {
		mask.show();
	});
	mask.click(function () { mask.hide(); });
}
function AC(act) {
	mlink = $("a[id='" + act + "']");
	if (mlink.size() > 0) {
		box = mlink.parents("div[id^='menu_']");
		boxid = box.attr("id").substring(5, 128);
		if ($("body").attr("class") != "showmenu") $("#togglemenu").click();
		if (mlink.attr("_url")) {
			$("#menu").find("div[id^=menu]").hide();
			box.show();
			mlink.addClass("thisclass").blur().parents("#menu").find("ul li a").not(mlink).removeClass("thisclass");
			if ($("#mod_" + boxid).attr("class") == "") {
				$("#nav").find("a").removeClass("thisclass");
				$("#nav").find("a[id='mod_" + boxid + "']").addClass("thisclass").blur();
			}
			main.location.href = mlink.attr("_url");
		} else if (mlink.attr("_open") && mlink.attr("_open") != undefined) {
			window.open(mlink.attr("_open"));
		}
	}
}
/*********************
 * 滚动按钮设置
*********************/
function scrollwindow() {
	parent.frames['menu'].scrollBy(0, myspeed);
}
function initializeIT() {
	if (myspeed != 0) {
		scrollwindow();
	}
}