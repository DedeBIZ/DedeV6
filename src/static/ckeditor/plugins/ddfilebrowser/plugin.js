CKEDITOR.plugins.add("ddfilebrowser", {
    icons: "ddfilebrowser",
    init: function (a) {
        a.addCommand("openDDFileBrowser",
            {
                exec: function (a) {
                    var w = 800;
                    var h = 600;
                    var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screenX;
                    var dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screenY;
                
                    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
                
                    var systemZoom = width / window.screen.availWidth;
                    var posLeft = (width - w) / 2 / systemZoom + dualScreenLeft;
                    var posTop = (height - h) / 2 / systemZoom + dualScreenTop;
                    window.open("./dialog/select_soft.php?f=" + a.name + "&noeditor=yes", "popUpImagesWin", "scrollbars=yes,resizable=yes,statebar=no,width=800,height=600,left=" + posLeft + ", top=" + posTop);
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