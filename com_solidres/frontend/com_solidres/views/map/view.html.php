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
 * HTML View class for the Solidres component
 *
 * @package   Solidres
 * @since     0.1.0
 */
class SolidresViewMap extends JViewLegacy
{
    protected $info;

	protected $location;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$assetId = $model->getState($model->getName().'.assetId');
		if ($assetId > 0)
		{
			$this->info = $model->getMapInfo();
		}

		$this->location = $model->getState('filter.location');

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');
		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);
		if (SRPlugin::isEnabled('hub'))
		{
			JHtml::stylesheet('plg_solidres_hub/assets/hub.min.css', false, true);
		}

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
    }
}
