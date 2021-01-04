CKEDITOR.plugins.add("ddfilebrowser", {
    icons: "ddfilebrowser",
    init: function (a) {
        a.addCommand("openDDFileBrowser",
            {
                exec: function (a) {
                    var posLeft = 100; var posTop = 100;
                    window.open("./dialog/select_soft.php?f=" + a.name, "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=600,height=400,left=" + posLeft + ", top=" + posTop);
                }
            });
        a.ui.addButton("DDFileBrowser",
            {
                label: "插入附件",
                command: "openDDFileBrowser",
                toolbar: "insert"
            })
    }
});