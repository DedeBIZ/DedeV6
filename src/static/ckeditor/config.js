﻿/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function (config) {
	config.toolbarGroups = [
		{ name: 'document', groups: ['mode', 'document', 'doctools'] },
		{ name: 'clipboard', groups: ['clipboard', 'undo'] },
		{ name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing'] },
		{ name: 'forms', groups: ['forms'] },
		{ name: 'basicstyles', groups: ['basicstyles', 'cleanup', 'list', 'indent', 'blocks'] },
		'/',
		{ name: 'paragraph', groups: ['align', 'bidi', 'paragraph','textindent'] },
		{ name: 'links', groups: ['links'] },
		{ name: 'insert', groups: ['insert'] },
		'/',
		{ name: 'styles', groups: ['styles'] },
		{ name: 'colors', groups: ['colors'] },
		{ name: 'tools', groups: ['tools'] }
	];

	config.extraPlugins = 'html5video,dedepagebreak,textindent';

	config.removeButtons = 'About,ShowBlocks,Iframe,Flash,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField';

	// config.filebrowserImageUploadUrl = "./dialog/select_images_post.php";
};