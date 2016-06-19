
(function($) {

// jam in top,right,bottom,left margin setting for inserted image

CKEDITOR.on('dialogDefinition', function (ev) {
	// Take the dialog name and its definition from the event data.
	var dialogName = ev.data.name;	console.log(dialogName);
	var dialogDefinition = ev.data.definition;	console.log(dialogDefinition);

	// **************************************
	// IMAGE DIALOG
	// **************************************
	if (dialogName == 'image') {

		// **************************************
		// IMAGE INFO TAB
		// **************************************
		var imageInfoTab = dialogDefinition.getContents('info');

		// remove the hspace and vspace fields
		imageInfoTab.remove('txtHSpace');
		imageInfoTab.remove('txtVSpace');

		// setup constants and other vars (recreating some functionality)
		var IMAGE = 1,
			LINK = 2,
			PREVIEW = 4,
			CLEANUP = 8,
			regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
			regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
			pxLengthRegex = /^\d+px$/;

		// function to update preview
		var updatePreview = function (dialog) {
			//Don't load before onShow.
			if (!dialog.originalElement || !dialog.preview)
			return 1;

			// Read attributes and update imagePreview;
			dialog.commitContent(PREVIEW, dialog.preview);
			return 0;
		}

		// function to commit changes internally

		// Avoid recursions.
		var incommit;

		// Synchronous field values to other impacted fields is required, e.g. border
		// size change should alter inline-style text as well.
		function commitInternally (targetFields) {
			if (incommit) return;
			incommit = 1;

			var dialog = this.getDialog(),
				element = dialog.imageElement;
			if (element) {
				// Commit this field and broadcast to target fields.
				this.commit(IMAGE, element);

				targetFields = [].concat(targetFields);
				var length = targetFields.length,
					field;
				for (var i = 0; i < length; i++) {
					field = dialog.getContentElement.apply(dialog, targetFields[ i ].split(':'));
					// May cause recursion.
					field && field.setup(IMAGE, element);
				}
			}

			incommit = 0;
		}

		// new margin fields
		imageInfoTab.add( {
			type: 'fieldset',
			label: '&nbsp;Margins&nbsp;',
			children:
				[
				{
				type: 'vbox',
				padding: 1,
				width: '100px',
				label: 'Margins',
				align: 'center',
				children:
				[
				// margin-top
				{
					type: 'text',
					id: 'txtMarginTop',
					width: '40px',
					labelLayout: 'horizontal',
					label: 'Top',
					'default': '',
					onKeyUp: function () { updatePreview(this.getDialog()); },
					onChange: function () { commitInternally.call(this, 'advanced:txtdlgGenStyle'); },
					validate: CKEDITOR.dialog.validate.integer(ev.editor.lang.image.validateVSpace),
					setup: function (type, element) {
						if (type == IMAGE) {
							var value,
								marginTopPx,
								marginTopStyle = element.getStyle('margin-top');
							marginTopStyle = marginTopStyle && marginTopStyle.match(pxLengthRegex);
							marginTopPx = parseInt(marginTopStyle, 10);
							value = marginTopPx;
							isNaN(parseInt(value, 10)) && (value = element.getAttribute('vspace'));
							this.setValue(value);
						}
					},
					commit: function (type, element, internalCommit) {
						var value = parseInt(this.getValue(), 10);
						if (type == IMAGE || type == PREVIEW) {
							if (!isNaN(value)) {
								element.setStyle('margin-top', CKEDITOR.tools.cssLength(value));
							} else if (!value && this.isChanged()) {
								element.removeStyle('margin-top');
							}
							if (!internalCommit && type == IMAGE) element.removeAttribute('vspace');
						} else if (type == CLEANUP) {
							element.removeAttribute('vspace');
							element.removeStyle('margin-top');
						}
					}
				}, // end margin-top

				// margin-right
				{
					type: 'text',
					id: 'txtMarginRight',
					width: '40px',
					labelLayout: 'horizontal',
					label: 'Right',
					'default': '',
					onKeyUp: function () { updatePreview(this.getDialog()); },
					onChange: function () { commitInternally.call(this, 'advanced:txtdlgGenStyle'); },
					validate: CKEDITOR.dialog.validate.integer(ev.editor.lang.image.validateHSpace),
					setup: function (type, element) {
						if (type == IMAGE) {
							var value,
								marginRightPx,
								marginRightStyle = element.getStyle('margin-right');
							marginRightStyle = marginRightStyle && marginRightStyle.match(pxLengthRegex);
							marginRightPx = parseInt(marginRightStyle, 10);
							value = marginRightPx;
							isNaN(parseInt(value, 10)) && (value = element.getAttribute('hspace'));
							this.setValue(value);
						}
					},
					commit: function (type, element, internalCommit) {
						var value = parseInt(this.getValue(), 10);
						if (type == IMAGE || type == PREVIEW) {
							if (!isNaN(value)) {
								element.setStyle('margin-right', CKEDITOR.tools.cssLength(value));
							} else if (!value && this.isChanged()) {
								element.removeStyle('margin-right');
							}
							if (!internalCommit && type == IMAGE) element.removeAttribute('hspace');
						} else if (type == CLEANUP) {
							element.removeAttribute('hspace');
							element.removeStyle('margin-right');
						}
					}
				}, // end margin-right

				// margin-bottom
				{
					type: 'text',
					id: 'txtMarginBottom',
					width: '40px',
					labelLayout: 'horizontal',
					label: 'Bottom',
					'default': '',
					onKeyUp: function () { updatePreview(this.getDialog()); },
					onChange: function () { commitInternally.call(this, 'advanced:txtdlgGenStyle'); },
					validate: CKEDITOR.dialog.validate.integer(ev.editor.lang.image.validateVSpace),
					setup: function (type, element) {
						if (type == IMAGE) {
							var value,
								marginBottomPx,
								marginBottomStyle = element.getStyle('margin-bottom');
							marginBottomStyle = marginBottomStyle && marginBottomStyle.match(pxLengthRegex);
							marginBottomPx = parseInt(marginBottomStyle, 10);
							value = marginBottomPx;
							isNaN(parseInt(value, 10)) && (value = element.getAttribute('vspace'));
							this.setValue(value);
						}
					},
					commit: function (type, element, internalCommit) {
						var value = parseInt(this.getValue(), 10);
						if (type == IMAGE || type == PREVIEW) {
							if (!isNaN(value)) {
								element.setStyle('margin-bottom', CKEDITOR.tools.cssLength(value));
							} else if (!value && this.isChanged()) {
								element.removeStyle('margin-bottom');
							}
							if (!internalCommit && type == IMAGE) element.removeAttribute('vspace');
						} else if (type == CLEANUP) {
							element.removeAttribute('vspace');
							element.removeStyle('margin-bottom');
						}
					}
				}, // end margin-bottom

				// margin-left
				{
					type: 'text',
					id: 'txtMarginLeft',
					width: '40px',
					labelLayout: 'horizontal',
					label: 'Left',
					'default': '',
					onKeyUp: function () { updatePreview(this.getDialog()); },
					onChange: function () { commitInternally.call(this, 'advanced:txtdlgGenStyle'); },
					validate: CKEDITOR.dialog.validate.integer(ev.editor.lang.image.validateHSpace),
					setup: function (type, element) {
						if (type == IMAGE) {
							var value,
								marginLeftPx,
								marginLeftStyle = element.getStyle('margin-left');
							marginLeftStyle = marginLeftStyle && marginLeftStyle.match(pxLengthRegex);
							marginLeftPx = parseInt(marginLeftStyle, 10);
							value = marginLeftPx;
							isNaN(parseInt(value, 10)) && (value = element.getAttribute('hspace'));
							this.setValue(value);
						}
					},
					commit: function (type, element, internalCommit) {
						var value = parseInt(this.getValue(), 10);
						if (type == IMAGE || type == PREVIEW) {
							if (!isNaN(value)) {
								element.setStyle('margin-left', CKEDITOR.tools.cssLength(value));
							} else if (!value && this.isChanged()) {
								element.removeStyle('margin-left');
							}
							if (!internalCommit && type == IMAGE) element.removeAttribute('hspace');
						} else if (type == CLEANUP) {
							element.removeAttribute('hspace');
							element.removeStyle('margin-left');
						}
					}
				} // end margin-left
				]
				}
			]
		}, 'txtBorder');

		// this syntax chokes in Safari and others (I think "default" is reserved)
		//imageInfoTab.get('txtBorder').default = '0';
		
		// this syntax works...
		// set default border to zero
		var imageTxtBorder = imageInfoTab.get('txtBorder');
		imageTxtBorder['default'] = '0';

	}
});

})(/*jQuery*/);
