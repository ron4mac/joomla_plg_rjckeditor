CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',	groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',		groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert',		groups: [ 'insert', 'readmore' ] },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',		groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles',	groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',	groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' }
	];

	// extra (local) plugins
	config.extraPlugins = 'autogrow,readmore,uploadimage';

	// allow 'readmore' and 'pagebreak' content
	config.extraAllowedContent = 'hr[id,class,title,alt]';

	config.autoGrow_minHeight = 200;
	config.autoGrow_maxHeight = 800;
	config.autoGrow_bottomSpace = 30;

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = '';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';

	config.filebrowserWindowWidth = 800;
	config.filebrowserWindowHeight = '50%';
	config.removePlugins = 'about';

};
