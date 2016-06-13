<?php
defined('_JEXEC') or die;

class PlgEditorRJCkeditor extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'media/plg_rjckeditor/ckeditor/';

	/**
	 * Initialises the Editor.
	 *
	 * @return  string  JavaScript Initialization string.
	 */
	public function onInit()
	{
		$plugBase = JUri::root().'plugins/editors/rjckeditor/';
	//	JHtml::_('behavior.framework');
		JHtml::_('script', '//cdn.ckeditor.com/4.5.9/standard/ckeditor.js', false, false, false, false);
	//	JHtml::_('script', $this->_basePath . 'ckeditor.js', false, false, false, false);
	//	JHtml::_('script', $plugBase.'rjckeditor.js', false, false, false, false);
	//	JHtml::_('stylesheet', $this->_basePath . 'css/codemirror.css');
	//	JHtml::_('stylesheet', $this->_basePath . 'css/configuration.css');
		$bparms = JFactory::getUser()->id . '::' . JUri::root(true) . '/images';
		setcookie('rjck_juid', base64_encode($bparms), 0, '/');

		return '<script type="text/javascript">
CKEDITOR.config.filebrowserBrowseUrl = "'.$plugBase.'fileman/dev.php";
CKEDITOR.config.filebrowserImageBrowseUrl = "'.$plugBase.'fileman/dev.php?type=image";
CKEDITOR.config.removePlugins = "about";
</script>';
	//	return '<script>CKEDITOR.config.filebrowserBrowseUrl = "plugins/editors/rjckeditor/rjfilebrowser/core/connector/php/connector.php";</script>';
	//	return '<script>rjcked_init();</script>';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string Javascript
	 */
	public function onSave($id)
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
	public function onGetContent($id)
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
	public function onSetContent($id, $content)
	{
		return "Joomla.editors.instances['$id'].setData($content);\n";
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return  boolean
	 */
	public function onGetInsertMethod()
	{
		static $done = false;

		// Do this only once.
		if (!$done)
		{
			$done = true;
			$doc = JFactory::getDocument();
			$js = "\tfunction jInsertEditorText(text, editor)
				{
					Joomla.editors.instances[editor].insertHtml(text);\n
			}";
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
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true,
		$id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id))
		{
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width))
		{
			$width .= 'px';
		}

		if (is_numeric($height))
		{
			$height .= 'px';
		}

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);

		$options	= new stdClass;
/*
		$options->mode = $mode;
		$options->smartIndent = true;

		// Enabled the line numbers.
		if ($this->params->get('lineNumbers') == "1")
		{
			$options->lineNumbers = true;
		}

		if ($this->params->get('autoFocus') == "1")
		{
			$options->autofocus	= true;
		}

		if ($this->params->get('autoCloseBrackets') == "1")
		{
			$options->autoCloseBrackets	= $autoCloseBrackets;
		}

		if ($this->params->get('autoCloseTags') == "1")
		{
			$options->autoCloseTags	= $autoCloseTags;
		}

		if ($this->params->get('matchTags') == "1")
		{
			$options->matchTags = $matchTags;
			JHtml::_('script', $this->_basePath . 'js/matchtags.js', false, false, false, false);
		}

		if ($this->params->get('matchBrackets') == "1")
		{
			$options->matchBrackets = $matchBrackets;
			JHtml::_('script', $this->_basePath . 'js/matchbrackets.js', false, false, false, false);
		}

		if ($this->params->get('marker-gutter') == "1")
		{
			$options->foldGutter = $fold;
			$options->gutters = array('CodeMirror-linenumbers', 'CodeMirror-foldgutter', 'breakpoints');
			JHtml::_('script', $this->_basePath . 'js/foldcode.js', false, false, false, false);
			JHtml::_('script', $this->_basePath . 'js/foldgutter.js', false, false, false, false);
		}

		if ($this->params->get('theme', '') == 'ambiance')
		{
			$options->theme	= 'ambiance';
			JHtml::_('stylesheet', $this->_basePath . 'css/ambiance.css');
		}

		if ($this->params->get('lineWrapping') == "1")
		{
			$options->lineWrapping = true;
		}

		if ($this->params->get('tabmode', '') == 'shift')
		{
			$options->tabMode = 'shift';
		}
*/
		$html = array();
		$html[]	= "<textarea name=\"$name\" class=\"ckeditor\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = 'CKEDITOR.on( "instanceReady", function( evt ) {';
		$html[] = '	var editor = evt.editor;';
		$html[] = '	console.log(CKEDITOR.removePlugins);';
		$html[] = '	console.log(CKEDITOR.config);';
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
		$html[] = '		Joomla.editors.instances[\'' . $id . '\'] = editor;';
		$html[] = '})';
		$html[] = '</script>';

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
	protected function _displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$results = (array) $this->update($args);

		if ($results)
		{
			foreach ($results as $result)
			{
				if (is_string($result) && trim($result))
				{
					$return .= $result;
				}
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);

			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
	}
}
