<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

/**
 * Supports an HTML select list of geo state
 *
 * @package     Solidres
 * @subpackage	Room
 * @since		1.6
 */
class JFormFieldGeoState extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'GeoState';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$html = array();

		$country_id = (int) $this->form->getValue('country_id', 0);
		$selectedId	= (int) $this->form->getValue('geo_state_id', 0);

		if ($this->name == 'jform[contact_geo_state_id]')
		{
			$country_id = (int) $this->form->getValue('contact_country_id', 0);
			$selectedId	= (int) $this->form->getValue('contact_geo_state_id', 0);
		}

		$options = SolidresHelper::getGeoStateOptions($country_id);

		$html[] = JHtml::_('select.genericlist', $options, $this->name, 'class="state_select '.$this->class.'"','value','text', $selectedId);
        
		return implode($html);
	}
	
}