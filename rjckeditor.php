<?php
/**
 * @package		plg_rjckeditor
 * @copyright	Copyright (C) 2022 RJCreations. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Event\Event;

class PlgEditorRJCkeditor extends CMSPlugin
{
	protected $infront = false;

	public function __construct (&$subject, $config = [])
	{
		$app = Factory::getApplication();
		$app->setHeader('Cross-Origin-Opener-Policy','same-origin-allow-popups',true);
		$this->infront = $app->isClient('site');
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
		$ugrps = Factory::getUser()->groups;
		$ckpkg = '';
		foreach($ugrps as $g=>$s) {
			$cx = ($this->infront ? 'f' : 'b').'_'.$g;
			if (isset($ckgrpcfg[$cx])) $ckpkg = $ckgrpcfg[$cx];
		}
		$ckpkg = $ckpkg ?: ($this->infront ? $this->params->get('ck_package_fe', '') : $this->params->get('ck_package_be', ''));
		$ckpkg = $ckpkg ?: $this->params->get('ck_package', 'standard');
		$plugBase = JUri::root(true).'/plugins/editors/rjckeditor/';
		$doc = Factory::getDocument();
		$doc->addScript('//cdn.ckeditor.com/'.$ckver.'/'.$ckpkg.'/ckeditor.js');
		$doc->addScript($plugBase.'rjckeditor'.(JDEBUG ? '' : '.min').'.js');
		setcookie('rjck_rfmr', Factory::getApplication()->isClient('administrator'), 0, '/');
		$fphp = JDEBUG ? 'dev' : 'index';

		$editcss = '/templates/' . Factory::getApplication()->getTemplate() . '/css/editor.css';
		$tmpl_editor_css = file_exists(JPATH_BASE.$editcss) ? ('CKEDITOR.config.contentsCss = "'.JUri::base().$editcss.'";') : '';

		// provide any custom templates that are in '/media/plg_rjckeditor/templates'
		$tmplpath = JPATH_ROOT.'/media/plg_rjckeditor/templates/';
		$tmpljs = '';
		if (is_dir($tmplpath)) {
			$tmplfiles = [];
			$tmplates = [];
			$basuri = JUri::root(true).'/media/plg_rjckeditor/templates/';
			$fils = array_diff(scandir($tmplpath), ['..','.']);
			foreach ($fils as $fil) {
				if (substr($fil,-3)=='.js') {
					$tmplfiles[] = $basuri.$fil;
					$tmplates[] = substr($fil,0,-3);
				}
			}
			if ($tmplates) {
				$tmpljs .= 'CKEDITOR.config.templates_files = ["'.implode('","',$tmplfiles).'"];'."\n\t";
				$tmpljs .= 'CKEDITOR.config.templates = "'.implode(',',$tmplates).'";';
			}
		}

		$js = 'CKEDITOR.plugins.addExternal("readmore", "'.$plugBase.'plugins/readmore/", "plugin.js");
	CKEDITOR.config.customConfig = "'.$plugBase.'config/config.'.$ckpkg.'.js";
	CKEDITOR.config.filebrowserBrowseUrl = "'.$plugBase.'fileman/'.$fphp.'.php";
	CKEDITOR.config.filebrowserImageBrowseUrl = "'.$plugBase.'fileman/'.$fphp.'.php?type=image";
	CKEDITOR.config.filebrowserUploadUrl = "'.$plugBase.'fileman/php/dropload.php";
	CKEDITOR.config.filebrowserWindowFeatures = "popup";
	'.$tmpl_editor_css.'
	CKEDITOR.config.uploadUrl = "'.$plugBase.'fileman/php/dropload.php";
	CKEDITOR.config.imageUploadUrl = "'.$plugBase.'fileman/php/dropload.php?type=image";
	CKEDITOR.config.baseHref = "'.JUri::root().'";
	'.$tmpljs.'
	CKEDITOR.config.image2_alignClasses = [ "u-align-left", "u-align-center", "u-align-right" ];
	// add methods for xtd buttons
	CKEDITOR.editor.prototype.getValue = function () { return this.getData(); };
	CKEDITOR.editor.prototype.replaceSelection = function (val) { this.insertHtml(val); };';
		$doc->addScriptDeclaration($js);
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
			$doc = Factory::getDocument();
			$js = "\n".'function jInsertEditorText (text, editor) { Joomla.editors.instances[editor].insertHtml(text); }'."\n";
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
	public function onDisplay ($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = [])
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

		$html = [];
		$html[] = "<textarea name=\"$name\" class=\"ckeditor\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = 'CKEDITOR.on( "instanceReady", function( evt ) {';
		$html[] = '	var editor = evt.editor;';
//		$html[] = '	console.log(CKEDITOR.config);';
		$html[] = '	Joomla.editors.instances[\'' . $id . '\'] = editor;';
		$html[] = '})';
		$html[] = '</script>';

		$session = Factory::getSession();
	////**** need to deal with image path for user/frontend/backend/admin etc. (may need to create path)
		$rpath = 'images';
		if ($this->infront) $rpath .= '/'.Factory::getUser()->id;
		$jroot = JUri::root(true);
		$jroot .= ($jroot == '/' ? '' : '/');
		$session->set('RJCK_RFMR', $jroot.':'.$rpath);
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
		if ((int)JVERSION < 4) {
			$return = '';
			$args = ['name' => $name, 'event' => 'onGetInsertMethod'];
	
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
				$return .= LayoutHelper::render('joomla.editors.buttons', $buttons);
			}
	
			return $return;
		}
		
		if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
			$buttonsEvent = new Event(
				'getButtons',
				['editor' => $name, 'buttons' => $buttons]
			);

			$buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
			$buttons = $buttonsResult['result'];

			return LayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return '';
	}

}
