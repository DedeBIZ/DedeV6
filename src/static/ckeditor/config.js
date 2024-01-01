/**
 * @license Copyright (c) 2003-2022, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */
CKEDITOR.editorConfig = function(config) {
	config.language = 'zh-cn';
	config.toolbarGroups = [
		{ name: 'mode', groups: ['mode', 'document', 'doctools'] },
		{ name: 'cleanup', groups: ['undo', 'cleanup'] },
		{ name: 'styles', groups: ['styles'] },
		{ name: 'colors', groups: ['colors'] },
		{ name: 'paragraph', groups: ['align', 'paragraph', 'textindent', 'indent'] },
		{ name: 'basicstyles', groups: ['basicstyles', 'list','blocks'] },
		{ name: 'editing', groups: ['find', 'editing'] },
		{ name: 'links', groups: ['links'] },
		{ name: 'insert', groups: ['insert'] },
		{ name: 'tools', groups: ['tools'] }
	];
	config.height = 350;
	config.removePlugins = 'exportpdf,div,scayt';
	config.removeButtons = 'Save,Styles,Font,NewPage,Print,Preview,Templates,Smiley,About,ShowBlocks,Iframe,Flash,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField';
};