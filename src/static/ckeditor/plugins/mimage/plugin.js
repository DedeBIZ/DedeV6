CKEDITOR.plugins.add("mimage", {
    icons: "mimage",
    init: function (a) {
        a.addCommand("openMImageDialog",
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