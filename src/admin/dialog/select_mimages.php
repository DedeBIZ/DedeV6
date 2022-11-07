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
<style>
body{margin:0;line-height:1.5;font:12px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;color:#545b62;background:#fff}
ul{margin:0;padding:0;list-style:none}
input[type=radio],input[type=checkbox]{margin:0;height:auto;box-shadow:none;outline:none;vertical-align:text-top}
button+button{margin-left:10px}
#wrap{padding:10px}
#topbar{padding:10px 0;border-bottom:1px solid #ccc;text-align:right}
#topbar button{display:inline-block;border:0;padding:.25rem .5rem;line-height:1.5;font-size:12px;color:#fff;background:#1eb867;border-color:#1eb867;border-radius:.25rem;transition:all .6s;text-align:center}
.topbar button+.topbar button{margin-left:10px}
#topbar button:focus{background:#006829;border-color:#005b24;box-shadow:0 0 0 0.2rem rgba(38,159,86,.5);outline:none}
#file_list{display:grid;grid-gap:10px;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));padding-top:10px}
#file_list:empty:after{content:'可以直接拖拽文件到这里'}
#file_list li{position:relative;display:block;vertical-align:top;padding:10px;border-radius:.25rem}
#file_list li.up-now:after{content:'';position:absolute;top:0;left:0;display:block;width:100%;height:100%;background:rgba(255,255,255,0.8) url(loading.gif) center center no-repeat;border-radius:.25rem;z-index:999}
#file_list li:hover{background:#f5f5f5}
#file_list li .picbox{display:flex;flex:0 0 auto;justify-content:center;overflow:hidden;position:relative;width:100%;padding-top:90%;align-items:center}
#file_list li .picbox img{display:block;max-width:100%;max-height:100%;position:absolute;top:50%;left:50%;transform:translateX(-50%) translateY(-50%);border-radius:.25rem}
#file_list li .namebox{padding:10px;display:flex;justify-content:center;align-items:flex-start}
#file_list li.up-over .picbox:after{content:url(data:image/svg+xml;%20charset=utf8,%3Csvg%20viewBox%3D%220%200%201024%201024%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%3E%3Cpath%20d%3D%22M512%200C229.376%200%200%20229.376%200%20512s229.376%20512%20512%20512%20512-229.376%20512-512S794.624%200%20512%200z%22%20fill%3D%22%234AC711%22%3E%3C%2Fpath%3E%3Cpath%20d%3D%22M855.552%20394.752l-358.4%20358.4a50.9952%2050.9952%200%200%201-72.192%200l-204.8-204.8c-18.944-19.968-18.944-51.2%200-71.168a50.5344%2050.5344%200%200%201%2072.192-1.024L460.8%20644.608l322.048-322.048c19.968-18.944%2051.2-18.944%2071.168%200%2020.48%2019.456%2020.992%2051.712%201.536%2072.192z%22%20fill%3D%22%23FFFFFF%22%3E%3C%2Fpath%3E%3C%2Fsvg%3E);position:absolute;bottom:10px;right:0;z-index:9}
#file_list li .tools{display:none;position:absolute;bottom:12px;right:10px;z-index:99}
#file_list li:hover .tools{display:block}
#file_list li .tools .remove{cursor:pointer}
#file_list li .tools .remove:after{content:url(data:image/svg+xml;%20charset=utf8,%3Csvg%20width=%2224%22%20height=%2224%22%20viewBox=%220%200%2024%2024%22%20xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath%20d=%22M17%206h3a1%201%200%200%201%200%202h-1v11a3%203%200%200%201-3%203H8a3%203%200%200%201-3-3V8H4a1%201%200%201%201%200-2h3V5a3%203%200%200%201%203-3h4a3%203%200%200%201%203%203v1zm-2%200V5a1%201%200%200%200-1-1h-4a1%201%200%200%200-1%201v1h6zm2%202H7v11a1%201%200%200%200%201%201h8a1%201%200%200%200%201-1V8zm-8%203a1%201%200%200%201%202%200v6a1%201%200%200%201-2%200v-6zm4%200a1%201%200%200%201%202%200v6a1%201%200%200%201-2%200v-6z%22%3E%3C/path%3E%3C/svg%3E)}
</style>
</head>
<body>
<div id="wrap">
	<div id="topbar">
        <label><input type="checkbox" name="isWater" id="isWater" <?php if ($photo_markup == '1') echo "checked";?>> 是否水印</label>
        <button class="addfile">添加文件</button>
        <button class="upall">全部上传</button>
        <button class="removeall">清空列表</button>
    </div>
	<ul id="file_list"></ul>
</div>
<script>
	var axupimgs={};
	axupimgs.res = [];//存放本地文件的数组
	var blobInfo = {file:null}
	blobInfo.blob = function(){return this.file;}
	var upload_handler = async(blobInfo, succFun, failFun)=>{
        var file = blobInfo.blob();
        formData = new FormData();
        formData.append('upload', file, file.name);
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
        succFun(data.url);
    };
	var upload_base_path = axupimgs.images_upload_base_path;
	//为列表添加排序
	function reSort(){
        document.querySelectorAll('#file_list li').forEach((el,i)=>{
            el.setAttribute('data-num',i);
        });
	}
    function isFileImage(file) {
        return file && file['type'].split('/')[0] === 'image';
    }
    function addList(files){
        var files_sum = files.length;
        var vDom = document.createDocumentFragment();
        for(let i=0;i<files_sum;i++){
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
    document.querySelector('#topbar .removeall').addEventListener('click',()=>{
        axupimgs.res=[]
        document.querySelectorAll('#file_list li').forEach((el,i)=>{
            el.parentNode.removeChild(el)
        });
    });
    //拖拽添加
    document.addEventListener('dragover', (e)=>{
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    document.addEventListener('drop', (e)=>{
        e.stopPropagation();
        e.preventDefault();
        if (!e.dataTransfer.files){return false;}
        var dropfiles = e.dataTransfer.files;
        if (!(dropfiles.length>0)){return false;}
        var exts=axupimgs.axupimgs_filetype.replace(/(\s)+/g,'').toLowerCase().split(',');
        var files=[];
        for( let file of dropfiles ){
            ext = file.name.split('.');
            ext = '.'+ext[ext.length-1];
            for(let s of exts){
                if (s==ext){
                    files.push(file);
                    break;
                }
            }
        }
        if (files.length>0){ addList(files) }
    });
    //添加文件
    document.querySelector('#topbar .addfile').addEventListener('click',()=>{
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
	function upAllFiles(n){
		var len = axupimgs.res.length;
		file_i = n;
		if (len == n){
			file_i=0;
            document.querySelector('#topbar .upall').innerText='全部上传';
            //返回
            console.log(axupimgs.res);
            axupimgs.res.forEach((v,k)=>{
                let addonHTML = `<img src='${v.url}'/>`;
                window.opener.CKEDITOR.instances["<?php echo $f ?>"].insertHtml(addonHTML);
            })
            window.close();
			return true;
		}
		if ( axupimgs.res[n].url!='' ){
			n++;
			upAllFiles(n)
		} else {
			blobInfo.file=axupimgs.res[n].file;
            blobInfo.isWater = document.querySelector('#isWater').checked;
			upload_handler(blobInfo,function(url){
				if (upload_base_path){
					if (upload_base_path.slice(-1)=='/' && url.substr(0,1)=='/' ){
						url = upload_base_path + url.slice(1);
					}else if (upload_base_path.slice(-1)!='/' && url.substr(0,1)!='/' ){
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
			},function(err){
                document.querySelector('#topbar .upall').innerText='全部上传';
                document.querySelectorAll('#file_list li.up-now').forEach((el,i)=>{
                    el.setAttribute('class','up-no');
                });
                alert(err);
            });
		}	
	}
    document.querySelector('#topbar .upall').addEventListener('click',(e)=>{
        if (e.target.innerText!='全部上传'){return false;}
        if (axupimgs.res.length>0){
            document.querySelectorAll('#file_list li.up-no').forEach((el,i)=>{
                el.classList ? el.classList.add('up-now') : el.className+=' up-now';
            });
            e.target.innerText='上传中';
            upAllFiles(0);
        }
    });
    var observ_flist = new MutationObserver( (muList,observe)=>{
        if (muList[0].addedNodes.length>0){
            muList[0].addedNodes.forEach((el)=>{
                el.querySelector('.remove').addEventListener('click',(e)=>{
                    var li = e.target.parentNode.parentNode;
                    var n = li.getAttribute('data-num');
                    var el = document.querySelectorAll('#file_list li')[n];
                    el.parentNode.removeChild(el);
                    axupimgs.res.splice(n,1);
                });
            });
        }
        reSort();
    });
    observ_flist.observe(document.querySelector('#file_list'),{childList:true});
</script>
</body>
</html>