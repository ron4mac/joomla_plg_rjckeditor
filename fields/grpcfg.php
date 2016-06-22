<?php
defined('JPATH_PLATFORM') or die;

class JFormFieldGrpcfg extends JFormField
{

	protected $type = 'Grpcfg'; //the form field type

	protected $classes = '';	//additional classes
	protected $extras = '';		//additional attributes

	protected $pkgs = array(
			'' => 'Default',
			'basic' => 'Basic',
			'standard' => 'Standard',
			'standard-all' => 'Standard(all)',
			'full' => 'Full',
			'full-all' => 'Full(all)'
			);

	public function __construct ($form = null)
	{
		JFactory::getDocument()->addStyleDeclaration('#rjckgcfg th { text-align: center; }
#rjckgcfg th, #rjckgcfg td { padding: 4px 4px; }
.rjckcsel { width:auto; }');
		parent::__construct($form);
	}

	protected function getInput ()
	{
		$groups = $this->getGroups();
		$html = '<table id="rjckgcfg"><tr><th style="text-align:left;">Group</th><th>Frontend</th><th>Backend</th></tr>'."\n";
		foreach ($groups as $grp) {
			$html .= '<tr>'.$this->grpsels($grp['id'], $grp['title']).'</tr>'."\n";
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

	private function grpsels ($id, $title)
	{
		$html = '<td style="text-align:left;padding-right:2em">'.$title.'</td>';
		$html .= $this->cfgsel($id, 'f');
		$html .= $this->cfgsel($id, 'b');
		return $html;
	}

	private function cfgsel ($id, $fb)
	{
		$vx = $fb.'_'.$id;
		$html = '<td align="center"><select name="'.$this->name.'['.$vx.']" class="rjckcsel">';
		foreach ($this->pkgs as $v=>$d) {
			$html .= '<option value="'.$v.'"';
			$html .= (isset($this->value[$vx]) && $this->value[$vx] == $v) ? ' selected' : '';
			$html .= '>'.$d.'</option>';
		}
		return $html . '</select></td>';
	}

	private function getGroups ()
	{
		$db = JFactory::getDbo();
		$query = $db->setQuery('SELECT title,id FROM #__usergroups ORDER BY title');
		return $db->loadAssocList();
	}

}
