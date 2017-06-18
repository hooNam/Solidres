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

JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
JLoader::import('joomla.filesystem.folder');
/**
 * System view class
 *
 * @package     Solidres
 * @subpackage	System
 * @since		0.6.0
 */
class SolidresViewSystem extends JViewLegacy
{
	protected $solidresPlugins = array(
		'content'         => array(
			'solidres'
		),
		'extension'       => array(
			'solidres'
		),
		'system'          => array(
			'solidres'
		),
		'solidres'        => array(
			'simple_gallery',
			'hub',
			'channelmanager',
			'acl',
			'advancedextra',
			'camera_slideshow',
			'complextariff',
			'currency',
			'customfield',
			'discount',
			'facebook',
			'feedback',
			'googleanalytics',
			'googleadwords',
			'housekeeping',
			'ical',
			'invoice',
			'limitbooking',
			'loadmodule',
			'rescode',
			'stream',
			'sms',
			'statistics',
			'experience',
			'experienceinvoice',
		),
		'user'            => array(
			'solidres'
		),
		'solidrespayment' => array(
			'authorizenet',
			'atlantic',
			'cielo',
			'cimb',
			'eway',
			'mollie',
			'mercadopago',
			'migs',
			'offline',
			'paypal',
			'payfast',
			'paypal_pro',
			'postfinance',
			'stripe',
			'unionpay'
		)
	);

	protected $solidresModules = array(
		'mod_sr_checkavailability',
		'mod_sr_currency',
		'mod_sr_camera',
		'mod_sr_coupons',
		'mod_sr_extras',
		'mod_sr_feedbacks',
		'mod_sr_map',
		'mod_sr_quicksearch',
		'mod_sr_roomtypes',
		'mod_sr_statistics',
		'mod_sr_vegas',
		'mod_sr_experience_search',
		'mod_sr_experience_list',
		'mod_sr_advancedsearch',
		'mod_sr_assets',
		'mod_sr_filter',
		'mod_sr_locationmap',
		'mod_sr_myrecentsearches',
	);

	protected $solidresTemplates = array();
	protected $updates = array();

    public function display($tpl = null)
	{
		$this->addToolbar();

		$dbo = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('COUNT(*)')->from($dbo->quoteName('#__sr_reservation_assets'));
		$this->hasExistingData = $dbo->setQuery($query)->loadResult();

		$this->updates = SRUtilities::getUpdates();

		$this->solidresTemplates = $this->get('SolidresTemplates');

		parent::display($tpl);
    }

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$canDo = SolidresHelper::getActions();


		JToolBarHelper::title(JText::_('SR_SUBMENU_SYSTEM'), 'generic.png');

		require_once JPATH_COMPONENT . '/helpers/toolbar.php';
		SRToolBarHelper::customLink('index.php?option=com_solidres', 'JToolbar_Close', 'fa fa-arrow-left');

		if ($canDo->get('core.admin'))
		{
			$url = 'index.php?option=com_solidres&task=system.checkUpdates&' . JSession::getFormToken() . '=1';
			SRToolBarHelper::customLink($url, 'SR_CHECK_UPDATES', 'fa fa-refresh');
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_solidres');
		}
	}
}