CKEDITOR.plugins.add("mimage", {
    icons: "mimage",
    init: function (a) {
        a.addCommand("openMImageDialog",
            {
                exec: function (a) {
                    var posLeft = 100; var posTop = 100;
                    window.open("./dialog/select_mimages.php?f=" + a.name, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + posLeft + ", top=" + posTop);
                }
            });
        a.ui.addButton("MImage",
            {
                label: "插入多图",
                command: "openMImageDialog",
                toolbar: "insert"
            })
    }
});