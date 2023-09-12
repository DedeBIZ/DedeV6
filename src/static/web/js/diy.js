function SelectImage(sform, stype) {
    let s = sform.split(".");
    if (s.length === 2) {
        let frm = document.getElementsByName(s[0]);
        let ipt = document.getElementsByName(s[1]);
        let tmp = document.createElement("input");
        tmp.id = "field" + s[1];
        tmp.type = "file";
        tmp.style.display = "none";
        if ($(`#${tmp.id}`).length === 0) {
            $(frm).append(tmp);
        }
        $(`#${tmp.id}`).click();
        $(`#${tmp.id}`).off("change").change(function (val) {
            const f = val.target.files[0];
            var formData = new FormData();
            var fileData = f;
            formData.append("file", fileData);
            $.ajax({
                url: '../user/api.php?action=upload&type=litpic',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (result) {
                    if (result.code === 0) {
                        $(ipt).val(result.data);
                    } else {
                        ShowMsg("文件上传失败，错误原因："+result.error.message);
                    }
                },
                error: function (xhr, status, error) {
                    ShowMsg("文件上传失败");//处理上传失败后的回调
                }
            });
        })
    }
}
$(document).ready(function() {
    $('.datepicker').daterangepicker({
        "singleDatePicker": true,
        "autoApply": true,
        "showDropdowns": true,
        "linkedCalendars": false,
        "timePicker": true,
        "timePicker24Hour": true,
        "showCustomRangeLabel": false,
        ranges: {
            '今日': [moment(), moment()],
            '昨日': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '本月': [moment().startOf('month'), moment().startOf('month')],
            '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').startOf('month')]
        },
        "locale": {
            format: 'YYYY-MM-DD HH:mm',
            applyLabel: '确定',
            cancelLabel: '取消',
            daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
            monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            firstDay: 1
        }
    }, function (start) {
        $(this).val(start.format("YYYY-MM-DD HH:mm"));
    });
});