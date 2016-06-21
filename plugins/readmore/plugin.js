/**
 * @file Plugin for inserting Joomla readmore
 */
( function() {

CKEDITOR.plugins.add( 'readmore',
{
	requires: ['fakeobjects'],
	init: function (editor)
	{
		CKEDITOR.addCss(
			'#system-readmore' +
			'{' +
			'background-image: url(' + CKEDITOR.getUrl( this.path + 'icons/readmore.gif' ) + ');' +
			'background-position: center center;' +
			'background-repeat: no-repeat;' +
			'clear: both;' +
			'display: block;' +
			'float: none;' +
			'width: 100%;' +
			'border-top: #FF0000 1px dotted;' +
			'border-bottom: #FF0000 1px dotted;' +
			'height: 5px;' +
			'}'
			);
		// Register the toolbar buttons.
		editor.ui.addButton('ReadMore',
		{
			label: 'Insert Readmore',
			hidpi: true,
			icon: this.path + 'icons/readmoreButton.gif',
			command: 'readmore',
			toolbar: 'insert'
		});
		editor.addCommand('readmore',
		{
			canUndo: false,
			context: 'hr',
			allowedContent: 'hr[id,class]',
			requiredContent: 'hr',
			exec: function ()
			{
				var hrs = editor.document.getElementsByTag('hr');
				for (var i = 0, len = hrs.count(); i < len; i++) {
					var hr = hrs.getItem(i);
					if (hr.getId() == 'system-readmore') {
						alert('There is already a Read more... link that has been inserted. Only one such link is permitted. Use {pagebreak} to split the page up further');
						return;
					}
				}
				insertComment('readmore');
			}
		});

		function insertComment (text)
		{
			editor.insertHtml('<hr id="system-readmore" class="cke_joomla_' + text +'" />');
		}
	}
});

} )();