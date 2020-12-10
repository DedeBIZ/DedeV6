CKEDITOR.plugins.add("ddfilebrowser", { 
    icons: "ddfilebrowser", 
    init: function (a) { 
        a.addCommand("openDDFileBrowser", 
        {
            exec: function (a) 
            {
                if ($Nav() == 'IE') { var posLeft = window.event.clientX - 200; var posTop = window.event.clientY - 50; }
                else { var posLeft = 100; var posTop = 100; }
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