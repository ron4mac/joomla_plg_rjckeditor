<?php
defined('JPATH_PLATFORM') or die;

class JFormFieldGrpcfg extends JFormField
{

	protected $type = 'Grpcfg'; //the form field type

	protected $classes = '';	//additional classes
	protected $extras = '';	//additional attributes

	// JFactory::getDocument()->addScriptDeclaration(

	protected function getInput()
	{
		$groups = $this->getGroups();
	//	echo'<pre>';var_dump($groups);echo'</pre>';
		$html = '<table><tr><th rowspan="2" align="left" style="vertical-align:bottom">Group</th><th colspan="4">Frontend</th><th colspan="4">Backend</th></tr>'."\n";
		$html .= '<tr><th width="10%">Default</th><th width="10%">Basic</th><th width="10%">Standard</th><th width="10%">Full</th><th width="10%">Default</th><th width="10%">Basic</th><th width="10%">Standard</th><th width="10%">Full</th></tr>'."\n";
		foreach ($groups as $grp) {
			$html .= '<tr>'.$this->grprads($grp['id'], $grp['title']).'</tr>'."\n";
		}
		$html .= '</table>';

		$class = '';
		$extra = '';
		if ($this->required) {
			$class .= ' required';
			$this->classes .= $class;
			$extra .= ' required="required"';
			$this->extras .= $extra;
		}
		$this->extras .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		return $html;
	}

	private function grprads ($id, $title)
	{
		$html = '<td>'.$title.'</td>';
		$html .= $this->cfgrad($id, 'f', '', true);
		$html .= $this->cfgrad($id, 'f', 'basic');
		$html .= $this->cfgrad($id, 'f', 'standard');
		$html .= $this->cfgrad($id, 'f', 'full');
		$html .= $this->cfgrad($id, 'b', '', true);
		$html .= $this->cfgrad($id, 'b', 'basic');
		$html .= $this->cfgrad($id, 'b', 'standard');
		$html .= $this->cfgrad($id, 'b', 'full');
		return $html;
	}

	private function cfgrad ($id, $fb, $val, $chk=false)
	{
		$vx = $fb.'_'.$id;
		$html = '<td align="center"><input type="radio" name="'.$this->name.'['.$vx.']" value="'.$val.'"';
		$html .= ($this->value[$vx] == $val) ? ' checked' : '';
		return $html . ' /></td>';
	}

	private function getGroups ()
	{
		$db = JFactory::getDbo();
		$query = $db->setQuery('SELECT title,id FROM #__usergroups ORDER BY title');
		return $db->loadAssocList();
	}

}
