<?php
defined('_JEXEC') or die;

class PlgEditorRJCkeditor extends JPlugin
{
	/**
	 * Initialises the Editor.
	 *
	 * @return  string  JavaScript Initialization string.
	 */
	public function onInit ()
	{
		$ckpkg = $this->params->get('ck_package', 'standard');
		$ckver = $this->params->get('ck_version', '4.5.9');
		$plugBase = JUri::root().'plugins/editors/rjckeditor/';
		$doc = JFactory::getDocument();
		$doc->addScript('//cdn.ckeditor.com/'.$ckver.'/'.$ckpkg.'/ckeditor.js');
		$doc->addScript('plugins/editors/rjckeditor/rjckeditor.js');

		return '<script type="text/javascript">
	CKEDITOR.config.customConfig = "/joom3dev/plugins/editors/rjckeditor/config/config.'.$ckpkg.'.js";
	CKEDITOR.config.filebrowserBrowseUrl = "'.$plugBase.'fileman/index.php";
	CKEDITOR.config.filebrowserImageBrowseUrl = "'.$plugBase.'fileman/index.php?type=image";
</script>';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string Javascript
	 */
	public function onSave ($id)
	{
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getData();\n";
	}

	/**
	 * Get the editor content.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string  Javascript
	 */
	public function onGetContent ($id)
	{
		return "Joomla.editors.instances['$id'].getData();\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param   string  $id       The id of the editor field.
	 * @param   string  $content  The content to set.
	 *
	 * @return  string  Javascript
	 */
	public function onSetContent ($id, $content)
	{
		return "Joomla.editors.instances['$id'].setData($content);\n";
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return  boolean
	 */
	public function onGetInsertMethod ()
	{
		static $done = false;

		// Do this only once.
		if (!$done) {
			$done = true;
			$doc = JFactory::getDocument();
			$js = "\nfunction jInsertEditorText (text, editor) { Joomla.editors.instances[editor].insertHtml(text); }\n";
			$doc->addScriptDeclaration($js);
		}

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   integer  $col      The number of columns for the textarea.
	 * @param   integer  $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    Unused
	 * @param   object   $author   Unused
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string  HTML Output
	 */
	public function onDisplay ($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id)) {
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width)) {
			$width .= 'px';
		}

		if (is_numeric($height)) {
			$height .= 'px';
		}

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);

		$html = array();
		$html[]	= "<textarea name=\"$name\" class=\"ckeditor\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = 'CKEDITOR.on( "instanceReady", function( evt ) {';
		$html[] = '	var editor = evt.editor;';
		$html[] = '	console.log(CKEDITOR.config);';
//		$html[] = 'CKEDITOR.replace( "'.$id.'", { extraPlugins: "rjimage" } );';
//		$html[] = '	CKEDITOR.replace( "'.$id.'",';
//		$html[] = '	{';
//		$html[] = '		filebrowserBrowseUrl : "/browser/browse.php",';
//		$html[] = '		filebrowserImageBrowseUrl : "/browser/browse.php?type=Images",';
//		$html[] = '		filebrowserUploadUrl : "/uploader/upload.php",';
//		$html[] = '		filebrowserImageUploadUrl : "/uploader/upload.php?type=Images"';
//		$html[] = '	});';
//		$html[] = '	console.log(evt);';
//		$html[] = '(function() {';
//		$html[] = '		var editor = CKEDITOR.use( "'.$id.'" );';
//		$html[] = '		editor.setOption("extraKeys", {';
//		$html[] = '			"Ctrl-Q": function(cm) {';
//		$html[] = '				setFullScreen(cm, !isFullScreen(cm));';
//		$html[] = '			},';
//		$html[] = '			"Esc": function(cm) {';
//		$html[] = '				if (isFullScreen(cm)) setFullScreen(cm, false);';
//		$html[] = '			}';
//		$html[] = '		});';
//		$html[] = '		editor.on("gutterClick", function(cm, n) {';
//		$html[] = '			var info = cm.lineInfo(n)';
//		$html[] = '			cm.setGutterMarker(n, "breakpoints", info.gutterMarkers ? null : makeMarker())';
//		$html[] = '		})';
//		$html[] = '		function makeMarker() {';
//		$html[] = '			var marker = document.createElement("div")';
//		$html[] = '			marker.style.color = "#822"';
//		$html[] = '			marker.innerHTML = "●"';
//		$html[] = '			return marker';
//		$html[] = '		}';
		$html[] = '	Joomla.editors.instances[\'' . $id . '\'] = editor;';
		$html[] = '})';
		$html[] = '</script>';

		$session = JFactory::getSession();
	////**** need to deal with image path for user/frontend/backend/admin etc. (may need to create path)
		$session->set('RJCK_RFMR', JUri::root(true).'/images/'.JFactory::getUser()->id.'/');
		return implode("\n", $html);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     The editor name
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   string  $asset    The object asset
	 * @param   object  $author   The author.
	 *
	 * @return  string HTML
	 */
	protected function _displayButtons ($name, $buttons, $asset, $author)
	{
		$return = '';

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$results = (array) $this->update($args);

		if ($results) {
			foreach ($results as $result) {
				if (is_string($result) && trim($result)) {
					$return .= $result;
				}
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);
			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
	}
}
