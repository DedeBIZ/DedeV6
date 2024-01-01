function checkSubmit(t) {
    if (document.addcontent.title.value == "") {
        ShowMsg("文档标题不能为空");
        document.addcontent.title.focus();
        return false;
    }
    if (document.addcontent.typeid.value == 0) {
        ShowMsg("请选择文档栏目");
        return false;
    }
}
function SelectFile(sform, stype) {
    let s = sform.split(".");
    if (s.length === 2) {
        let frm = document.getElementsByName(s[0]);
        let ipt = document.getElementsByName(s[1]);
        let tmp = document.createElement("input");
        tmp.id = 'field'+s[1];
        tmp.type = "file";
        tmp.style.display = 'none';
        if ($(`#${tmp.id}`).length === 0) {
            $(frm).append(tmp);
        }
        $(`#${tmp.id}`).click();
        $(`#${tmp.id}`).off('change').change(function(val) {
            const f = val.target.files[0];
            var formData = new FormData();
            var fileData = f;
            formData.append('file', fileData);
            $.ajax({
                url: 'api.php?action=upload&type='+stype,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(result) {
                    if (result.code === 0) {
                        $(ipt).val(result.data);
                    } else {
                        ShowMsg("文件上传失败，错误原因："+result.error.message);
                    }
                },
                error: function(xhr, status, error) {
                    ShowMsg("文件上传失败");//处理上传失败后的回调
                }
            });
        })
    }
}
function SelectImage(sform, stype) {
    if (stype == 'big') {
        stype = "litpic";
    }
    SelectFile(sform, stype);
}
function SelectSoft(sform, stype='soft') {
    SelectFile(sform, stype);
}
function SelectMedia(sform, stype='media') {
    SelectFile(sform, stype);
}