<?php
defined('_JEXEC') or die;

class PlgEditorRJCkeditor extends JPlugin
{
	protected $infront = false;

	public function __construct(&$subject, $config = array())
	{
		$this->infront = JFactory::getApplication()->isSite();
		parent::__construct($subject, $config);
	}

	/**
	 * Initialises the Editor.
	 *
	 * @return  string  JavaScript Initialization string.
	 */
	public function onInit ()
	{
		$ckver = $this->params->get('ck_version', '4.5.9');
		$ckgrpcfg = get_object_vars($this->params->get('ck_grp_cfg', (object) ''));
		$ugrps = JFactory::getUser()->groups;
		$ckpkg = '';
		foreach($ugrps as $g=>$s) {
			$cx = ($this->infront ? 'f' : 'b').'_'.$g;
			if (isset($ckgrpcfg[$cx])) $ckpkg = $ckgrpcfg[$cx];
		}
		$ckpkg = $ckpkg ?: ($this->infront ? $this->params->get('ck_package_fe', '') : $this->params->get('ck_package_be', ''));
		$ckpkg = $ckpkg ?: $this->params->get('ck_package', 'standard');
		$plugBase = JUri::root().'plugins/editors/rjckeditor/';
		$doc = JFactory::getDocument();
		$doc->addScript('//cdn.ckeditor.com/'.$ckver.'/'.$ckpkg.'/ckeditor.js');
		$doc->addScript($plugBase.'rjckeditor.js');
		setcookie('rjck_rfmr', JFactory::getApplication()->isAdmin(), 0, '/');

		return '<script type="text/javascript">
	CKEDITOR.plugins.addExternal("readmore", "'.$plugBase.'plugins/readmore/", "plugin.js");
	CKEDITOR.config.customConfig = "'.$plugBase.'config/config.'.$ckpkg.'.js";
	CKEDITOR.config.filebrowserBrowseUrl = "'.$plugBase.'fileman/index.php";
	CKEDITOR.config.filebrowserImageBrowseUrl = "'.$plugBase.'fileman/index.php?type=image";
	CKEDITOR.config.filebrowserUploadUrl = "'.$plugBase.'fileman/php/dropload.php";

	CKEDITOR.config.uploadUrl = "'.$plugBase.'fileman/php/dropload.php";
	CKEDITOR.config.imageUploadUrl = "'.$plugBase.'fileman/php/dropload.php?type=image";
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
		$html[] = "<textarea name=\"$name\" class=\"ckeditor\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = 'CKEDITOR.on( "instanceReady", function( evt ) {';
		$html[] = '	var editor = evt.editor;';
//		$html[] = '	console.log(CKEDITOR.config);';
		$html[] = '	Joomla.editors.instances[\'' . $id . '\'] = editor;';
		$html[] = '})';
		$html[] = '</script>';

		$session = JFactory::getSession();
	////**** need to deal with image path for user/frontend/backend/admin etc. (may need to create path)
		$rpath = JUri::root(true).'/images/';
		if ($this->infront) $rpath .= JFactory::getUser()->id.'/';
		$session->set('RJCK_RFMR', $rpath);
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
		if (class_exists('JLayoutHelper')) {
			return $this->_displayButtons32($name, $buttons, $asset, $author);
		} else {
			return $this->_displayButtons25($name, $buttons, $asset, $author);
		}
	}

	private function _displayButtons32 ($name, $buttons, $asset, $author)
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

	private function _displayButtons25 ($name, $buttons, $asset, $author)
	{
		// Load modal popup behavior
		JHtml::_('behavior.modal', 'a.modal-button');

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$return = '';
		$results = (array) $this->update($args);

		if ($results) {
			foreach ($results as $result) {
				if (is_string($result) && trim($result)) {
					$return .= $result;
				}
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			/*
			 * This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			 */
			$return .= "\n<div id=\"editor-xtd-buttons\">\n";

			foreach ($results as $button) {
				/*
				 * Results should be an object
				 */
				if ( $button->get('name') ) {
					$modal		= ($button->get('modal')) ? ' class="modal-button"' : null;
					$href		= ($button->get('link')) ? ' href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? ' onclick="'.$button->get('onclick').'"' : 'onclick="IeCursorFix(); return false;"';
					$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');
					$return .= '<div class="button2-left"><div class="' . $button->get('name')
						. '"><a' . $modal . ' title="' . $title . '"' . $href . $onclick . ' rel="' . $button->get('options')
						. '">' . $button->get('text') . "</a></div></div>\n";
				}
			}

			$return .= "</div>\n";
		}

		return $return;
	}

}
