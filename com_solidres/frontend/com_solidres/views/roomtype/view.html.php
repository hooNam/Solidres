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
 * @package     Solidres
 * @since		0.1.0
 */
class SolidresViewRoomType extends JViewLegacy
{
	public function display($tpl = null)
	{
		$model = $this->getModel();

		$this->item	= $model->getItem();
		$this->config = JComponentHelper::getParams('com_solidres');

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('extension');
		JPluginHelper::importPlugin('solidres');

		// Trigger the data preparation event.
		$dispatcher->trigger('onRoomTypePrepareData', array('com_solidres.roomtype', $this->item));

		$this->_prepareDocument();

		$this->defaultGallery = '';
		$defaultGallery       = $this->config->get('default_gallery', 'simple_gallery');
		if (SRPlugin::isEnabled($defaultGallery))
		{
			$layout = SRLayoutHelper::getInstance();
			$layout->addIncludePath(SRPlugin::getLayoutPath($defaultGallery));
			$this->defaultGallery = $layout->render('gallery.default', array('media' => $this->item->media));
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document like adding meta tags/site name per ReservationAsset
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		if ($this->item->name)
		{
			$this->document->setTitle($this->item->name);
		}
	}
}