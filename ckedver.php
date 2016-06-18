<?php
// No direct access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldCkedver extends JFormFieldList
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
		$gits = JFactory::getStream();
		$gits->open('https://api.github.com/repos/ckeditor/ckeditor-releases/tags');
		$releases = json_decode($gits->read());
		$gits->close();

		$options = array();
		foreach ($releases as $r) {
			//if ($r->prerelease) continue;	// don't offer any prereleases
			$tag = $r->name;
			if (preg_match('/standard\/(\d+\.\d+\.\d+)/', $tag, $m)) {
				$options[] = JHtml::_('select.option', $m[1], $m[1]);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), array_slice($options, 0, 10));		//just return the last 10 releases
	}
}