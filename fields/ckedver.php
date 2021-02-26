<?php
/**
 * @package		plg_rjckeditor
 * @copyright	Copyright (C) 2021 RJCreations. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
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

	/**
	 * Method to get a list CKEditor version by getting Github releases.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$gits = Factory::getStream();
		$gits->open('https://api.github.com/repos/ckeditor/ckeditor-releases/tags');
		$releases = json_decode($gits->read());
		$gits->close();

		$options = [];
		foreach ($releases as $r) {
			//if ($r->prerelease) continue;	// don't offer any prereleases
			$tag = $r->name;
			if (preg_match('/standard\/(\d+\.\d+\.\d+)/', $tag, $m)) {
				$options[] = JHtml::_('select.option', $m[1], $m[1]);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), array_slice($options, 0, 20));		//just return the last 20 releases
	}
}
