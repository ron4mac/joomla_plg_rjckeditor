CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.filebrowserWindowWidth = 800;
	config.filebrowserWindowHeight = '50%';
	config.removePlugins = 'about,bidi,iframe,flash,font,language,exportpdf,print,preview,save,smiley';
	config.removeButtons = '';

	// extra (local) plugins
	config.extraPlugins = 'readmore';

	// allow 'readmore' and 'pagebreak' content
	config.extraAllowedContent = 'hr[id,class,title,alt]';
};
