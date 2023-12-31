<?php
require_once(dirname(__FILE__)."/config.php");
include(DEDEDATA.'/mark/inc_photowatermark_config.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="user-scalable=no,width=device-width,initial-scale=1.0,maximum-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="format-detection" content="telephone=no">
        <title>插入多图</title>
        <link rel="stylesheet" href="/static/web/css/font-awesome.min.css">
        <link rel="stylesheet" href="/static/web/css/bootstrap.min.css">
        <link rel="stylesheet" href="/static/web/css/admin.css">
    </head>
    <body>
        <div id="wrap">
            <div id="topbar">
                <label><input type="checkbox" name="isWater" id="isWater" <?php if ($photo_markup == '1') echo 'checked';?>> 是否水印</label>
                <button class="btn btn-success btn-sm addfile">添加图片</button>
                <button class="btn btn-success btn-sm removeall">清空图片</button>
                <button class="btn btn-success btn-sm upall">全部上传</button>
            </div>
            <ul id="file_list"></ul>
        </div>
        <script>
            var axupimgs = {};
            axupimgs.res = [];//存放本地文件的数组
            var blobInfo = {file:null}
            blobInfo.blob = function() {
                return this.file;
            }
            var upload_handler = async(blobInfo, succFun, failFun) => {
                var file = blobInfo.blob();
                formData = new FormData();
                formData.append('upload', file, file.name);
                formData.append('format', "json");
                if (document.querySelector('#isWater').checked) {
                    formData.append('needwatermark', 1);
                } else {
                    //formData.append('needwatermark', 0);
                }
                let res = await fetch('select_images_post.php', {
                    method: 'POST',
                    body: formData
                });
                let data = await res.json();
                if (typeof data.msg !== 'undefined' && data.msg !== '') {
                    alert(data.msg)
                    return;
                }
                succFun(data.url);
            };
            var upload_base_path = axupimgs.images_upload_base_path;
            //为列表添加排序
            function reSort() {
                document.querySelectorAll('#file_list li').forEach((el, i) => {
                    el.setAttribute('data-num', i);
                });
            }
            function isFileImage(file) {
                return file && file['type'].split('/')[0] === 'image';
            }
            function addList(files) {
                var files_sum = files.length;
                var vDom = document.createDocumentFragment();
                for (let i=0;i<files_sum;i++) {
                    let file = files[i];
                    if (!isFileImage(file)) {
                        alert("选择非图片文件无法上传")
                        return;
                    }
                    let blobUrl = window.URL.createObjectURL(file)
                    axupimgs.res.push({file:file,blobUrl:blobUrl,url:''});
                    let li = document.createElement('li');
                    li.setAttribute('class','up-no');
                    li.setAttribute('data-time',file.lastModified);
                    li.innerHTML='<div class="picbox"><img src="'+blobUrl+'"></div><div class="namebox"><span>'+file.name+'</span></div><div class="tools"><a class="remove"></a></div>';
                    vDom.appendChild(li);
                }
                document.querySelector('#file_list').appendChild(vDom);
                //reSort();
            }
            //清空列表
            document.querySelector('#topbar .removeall').addEventListener('click', () => {
                axupimgs.res=[]
                document.querySelectorAll('#file_list li').forEach((el, i) => {
                    el.parentNode.removeChild(el)
                });
            });
            //拖拽添加
            document.addEventListener('dragover', (e) => {
                e.stopPropagation();
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });
            document.addEventListener('drop', (e) => {
                e.stopPropagation();
                e.preventDefault();
                if (!e.dataTransfer.files) {
                    return false;
                }
                var dropfiles = e.dataTransfer.files;
                if (!(dropfiles.length > 0)) {
                    return false;
                }
                var exts='.png,.gif,.jpg,.jpeg'.replace(/(\s)+/g,'').toLowerCase().split(',');
                var files=[];
                for ( let file of dropfiles ) {
                    ext = file.name.split('.');
                    ext = '.'+ext[ext.length-1];
                    for (let s of exts) {
                        if (s==ext) {
                            files.push(file);
                            break;
                        }
                    }
                }
                if (files.length > 0) {
                    addList(files)
                }
            });
            //添加文件
            document.querySelector('#topbar .addfile').addEventListener('click', () => {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('multiple', 'multiple');
                input.setAttribute('accept', axupimgs.axupimgs_filetype);
                input.click();
                input.onchange = function() {
                    var files = this.files;
                    addList(files);
                }
            });
            var file_i = 0;
            function upAllFiles(n) {
                var len = axupimgs.res.length;
                file_i = n;
                if (len == n) {
                    file_i=0;
                    document.querySelector('#topbar .upall').innerText='全部上传';
                    //返回
                    axupimgs.res.forEach((v, k) => {
                        let addonHTML = `<img src='${v.url}'>`;
                        window.opener.CKEDITOR.instances["<?php echo $f ?>"].insertHtml(addonHTML);
                    })
                    window.close();
                    return true;
                }
                if (axupimgs.res[n].url!='') {
                    n++;
                    upAllFiles(n)
                } else {
                    blobInfo.file=axupimgs.res[n].file;
                    blobInfo.isWater = document.querySelector('#isWater').checked;
                    upload_handler(blobInfo, function(url) {
                        if (upload_base_path) {
                            if (upload_base_path.slice(-1)=='/' && url.substr(0,1)=='/') {
                                url = upload_base_path + url.slice(1);
                            } else if (upload_base_path.slice(-1)!='/' && url.substr(0,1)!='/') {
                                url = upload_base_path + '/' + url;
                            } else {
                                url = upload_base_path + url;
                            }
                        }
                        axupimgs.res[file_i].url = url;
                        filename = url.split('/').pop();
                        var li = document.querySelectorAll('#file_list li')[file_i];
                        li.setAttribute('class','up-over');
                        li.querySelector('.namebox span').innerText = filename;
                        n++
                        upAllFiles(n);
                    },function(err) {
                        document.querySelector('#topbar .upall').innerText='全部上传';
                        document.querySelectorAll('#file_list li.up-now').forEach((el,i) => {
                            el.setAttribute('class','up-no');
                        });
                        alert(err);
                    });
                }    
            }
            document.querySelector('#topbar .upall').addEventListener('click', (e) => {
                if (e.target.innerText!='全部上传') {
                    return false;
                }
                if (axupimgs.res.length > 0) {
                    document.querySelectorAll('#file_list li.up-no').forEach((el, i) => {
                        el.classList ? el.classList.add('up-now') : el.className+=' up-now';
                    });
                    e.target.innerText='上传中';
                    upAllFiles(0);
                }
            });
            var observ_flist = new MutationObserver( (muList, observe) => {
                if (muList[0].addedNodes.length > 0) {
                    muList[0].addedNodes.forEach((el) => {
                        el.querySelector('.remove').addEventListener('click', (e) => {
                            var li = e.target.parentNode.parentNode;
                            var n = li.getAttribute('data-num');
                            var el = document.querySelectorAll('#file_list li')[n];
                            el.parentNode.removeChild(el);
                            axupimgs.res.splice(n, 1);
                        });
                    });
                }
                reSort();
            });
            observ_flist.observe(document.querySelector('#file_list'),{childList:true});
        </script>
    </body>
</html>