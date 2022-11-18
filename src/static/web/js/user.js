function checkSubmit(t) {
    if (document.addcontent.title.value == "") {
        ShowMsg(`${t}不能为空`);
        document.addcontent.title.focus();
        return false;
    }
    if (document.addcontent.typeid.value == 0) {
        ShowMsg("请您选择文档所属栏目");
        return false;
    }
}