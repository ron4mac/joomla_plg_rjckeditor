<?php
/**
 * @package		plg_rjckeditor
 * @copyright	Copyright (C) 2022 RJCreations. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('combo');

class JFormFieldCkedver extends JFormFieldCombo
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Ckedver';

	// cache the release (tags) list
	protected static $releases = null;

	/**
	 * Method to get a list CKEditor version by getting Github releases.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		// get the releases from github if not yet gotten
		if (empty(self::$releases)) {
			$gits = Factory::getStream();
			$gits->open('https://api.github.com/repos/ckeditor/ckeditor-releases/tags');
			self::$releases = json_decode($gits->read());
			$gits->close();
		}

		$options = [];
		foreach (self::$releases as $r) {
			//if ($r->prerelease) continue;	// don't offer any prereleases
			$tag = $r->name;
			if (preg_match('/standard\/(\d+\.\d+\.\d+)/', $tag, $m)) {
				$options[] = HTMLHelper::_('select.option', $m[1], $m[1]);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), array_slice($options, 0, 20));		//just return the most recent 20 releases
	}
}
