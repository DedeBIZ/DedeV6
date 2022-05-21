$(function () {
	//左侧菜单开关
	LeftMenuToggle();
});
function LeftMenuToggle() {
	//左侧菜单开关
	$("#togglemenu").click(function () {
		if ($("body").attr("class") == "showmenu") {
			$("body").attr("class", "hidemenu");
			$(this).html('<i class="fa fa-indent"></i>');
		} else {
			$("body").attr("class", "showmenu");
			$(this).html('<i class="fa fa-dedent"></i>');
		}
	});
}