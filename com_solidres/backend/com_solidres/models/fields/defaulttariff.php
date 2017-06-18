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

require_once SRPATH_HELPERS.'/helper.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldDefaultTariff extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'DefaultTariff';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$defaultTariff = $this->form->getValue('default_tariff');
		$html = "";
		$dayMapping = array('0' => 'sun', '1' => 'mon', '2' => 'tue', '3' => 'wed', '4' => 'thu', '5' => 'fri', '6' => 'sat' );
		$enabledComplexTariff = SRPlugin::isEnabled('complextariff');

		if (isset($defaultTariff))
		{
			if (is_array($defaultTariff->details))
			{
				foreach ($defaultTariff->details as $detail)
				{
					$html .= '
					<div class="' . SR_UI_GRID_COL_2 . '">
						<p class="add-on">'.JText::_($dayMapping[$detail->w_day]).'</p>
						<input '.($enabledComplexTariff ? '' : 'required' ).' type="text" class="align-right span12" name="jform[default_tariff][' . $defaultTariff->id . '][' . $detail->id . ']['.$detail->w_day.']" value="'.$detail->price.'">
					</div>
				';
				}
			}
		}
		else
		{
			for ($i = 0; $i < 7; $i++)
			{
				$html .= '
					<div class="span2 col-md-2">
						<p class="add-on">'.JText::_($dayMapping[$i]).'</p>
						<input '.($enabledComplexTariff ? '' : 'required' ).' type="text" class="align-right span12" name="jform[default_tariff][0][0]['.$i.']" value="0">
					</div>
				';
			}
		}

		return $html;
	}
}


