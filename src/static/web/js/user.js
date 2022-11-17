function checkSubmit(t) {
    if (document.addcontent.title.value == "") {
        ShowMsg(`${t}不能为空`);
        document.addcontent.title.focus();
        return false;
    }
    if (document.addcontent.typeid.value == 0) {
        ShowMsg("隶属栏目必须选择");
        return false;
    }
}