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

jimport('solidres.version');

/**
 * Solidres Side Navigation Helper class
 *
 * @package     Solidres
 */
class SolidresHelperSideNavigation
{
	public static $extention = 'com_solidres';
	
	/**
	 * Display the side navigation bar, ACL aware
	 * 
	 * @return  string the html representation of side navigation
	 */
	public static function getSideNavigation($viewName = null)
	{
		JHtml::_('behavior.framework', true);
		$input = JFactory::getApplication()->input;
		if (null === $viewName)
		{
			$viewName = $input->get('view', '', 'cmd');
		}
		$disabled = $input->get('disablesidebar', '0', 'int');
		JLoader::register('SRSystemHelper', JPATH_LIBRARIES . '/solidres/system/helper.php');
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$solidresConfig = JComponentHelper::getParams('com_solidres');

		if ($disabled) return;

		$updateInfo = SRUtilities::getUpdates();

		$updateCount = 0;
		if (is_array($updateInfo) && !empty($updateInfo) && !isset($updateInfo['data']))
		{
			$updateCount = count($updateInfo);
		}

		$menuStructure['SR_SUBMENU_ASSET'] = array(
			'0.0' => array('SR_SUBMENU_ASSETS_CATEGORY', 'index.php?option=com_categories&extension=com_solidres'),
			'1.0' => array('SR_SUBMENU_ASSETS_LIST', 'index.php?option=com_solidres&view=reservationassets'),
			'2.0' => array('SR_SUBMENU_ROOM_TYPE_LIST', 'index.php?option=com_solidres&view=roomtypes')
		);

		$menuStructure['SR_SUBMENU_CUSTOMER'] = array(
			'0.0' => array('SR_SUBMENU_CUSTOMERS_LIST', 'index.php?option=com_solidres&view=customers'),
			'1.0' => array('SR_SUBMENU_CUSTOMERGROUPS_LIST', 'index.php?option=com_solidres&view=customergroups'),
			'2.0' => array('SR_SUBMENU_CUSTOM_FIELDS', 'index.php?option=com_solidres&view=customfields&context=com_solidres.customer')
		);

		$menuStructure['SR_SUBMENU_RESERVATION'] = array(
			'0.0' => array('SR_SUBMENU_RESERVATIONS_LIST', 'index.php?option=com_solidres&view=reservations')
		);

		$menuStructure['SR_SUBMENU_COUPON_EXTRA'] = array(
			'0.0' => array('SR_SUBMENU_COUPONS_LIST', 'index.php?option=com_solidres&view=coupons'),
			'1.0' => array('SR_SUBMENU_EXTRAS_LIST', 'index.php?option=com_solidres&view=extras')
		);

		if (SRPlugin::isEnabled('feedback'))
		{
			$menuStructure['SR_SUBMENU_CUSTOMER_FEEDBACK'] = array(
				'0.0' => array('SR_SUBMENU_COMMENT_LIST', 'index.php?option=com_solidres&view=feedbacks'),
				'1.0' => array('SR_SUBMENU_CONDITION_LIST', 'index.php?option=com_solidres&view=feedbackconditions'),
				'2.0' => array('SR_SUBMENU_CUSTOMER_FEEDBACK_TYPE_LIST', 'index.php?option=com_solidres&view=feedbacktypes'),
				'3.0' => array('SR_SUBMENU_FEEDBACK_TYPE_VALUES', 'index.php?option=com_solidres&view=feedbacktypevalues')
			);
		}
		if (SRPlugin::isEnabled('hub') && JComponentHelper::getParams('com_solidres')->get('enableSubscription', 0))
		{
			$menuStructure['SR_SUBMENU_SUBSCRIPTIONS'] = array(
				'0.0' => array('SR_SUBMENU_SUBSCRIPTIONS_LEVELS', 'index.php?option=com_solidres&view=subscriptionlevels'),
				'1.0' => array('SR_SUBMENU_COUPONS_LIST', 'index.php?option=com_solidres&view=subscriptioncoupons'),
				'2.0' => array('SR_SUBMENU_SUBSCRIPTIONS_UPGRADES', 'index.php?option=com_solidres&view=subscriptionupgrades'),
				'3.0' => array('SR_SUBMENU_SUBSCRIPTIONS_LIST', 'index.php?option=com_solidres&view=subscriptions'),
				'4.0' => array('SR_SUBMENU_SUBSCRIPTION_EMAIL_LIST', 'index.php?option=com_solidres&view=subscriptionemails'),
				'5.0' => array('SR_SUBMENU_COMMISSION_RATES_LIST', 'index.php?option=com_solidres&view=commissionrates'),
				'6.0' => array('SR_SUBMENU_COMMISSIONS_LIST', 'index.php?option=com_solidres&view=commissions')
			);
		}

		$menuStructure['SR_SUBMENU_SYSTEM'] = array(
			'0.0'  => array('SR_SUBMENU_CURRENCIES_LIST', 'index.php?option=com_solidres&view=currencies'),
			'1.0'  => array('SR_SUBMENU_COUNTRY_LIST', 'index.php?option=com_solidres&view=countries'),
			'2.0'  => array('SR_SUBMENU_STATE_LIST', 'index.php?option=com_solidres&view=states'),
			'3.0'  => array('SR_SUBMENU_TAX_LIST', 'index.php?option=com_solidres&view=taxes'),
			'4.0'  => array('SR_SUBMENU_EMPLOYEES', 'index.php?option=com_users'),
			'5.0'  => array('SR_SUBMENU_LIMITBOOKINGS', 'index.php?option=com_solidres&view=limitbookings'),
			'6.0'  => array('SR_SUBMENU_DISCOUNTS', 'index.php?option=com_solidres&view=discounts'),
			'7.0'  => array('SR_SUBMENU_FACILITIES', 'index.php?option=com_solidres&view=facilities'),
			'7.5'  => array('SR_ACCESS_CONTROLS', 'index.php?option=com_solidres&view=acl'),
			'8.0'  => array('SR_SUBMENU_THEMES', 'index.php?option=com_solidres&view=themes'),
			'9.0' => array('SR_SUBMENU_SYSTEM', 'index.php?option=com_solidres&view=system')
		);

		$iconMap = array(
			'asset'             => 'fa fa-home',
			'customer'          => 'fa fa-users',
			'reservation'       => 'fa fa-key',
			'coupon_extra'      => 'fa fa-ticket',
			'customer_feedback' => 'fa fa-comments',
			'subscriptions'     => 'fa fa-money',
			'system'            => 'fa fa-gear'
		);

		if ($solidresConfig->get('enable_reservation_live_refresh', 1) == 1)
		{
			$script = '
			Solidres.jQuery(function($) {
				intervalId = setInterval(function () {
					Solidres.jQuery.ajax({
						method: "GET",
						url: "index.php?option=com_solidres&task=reservations.countUnread&format=json",
						dataType: "JSON"
					})
					.success(function (data) {
						if (data.count > 0) {
							$("#sr_submenu_reservations_list").text("' . JText::_($menuStructure['SR_SUBMENU_RESERVATION']['0.0'][0]) . '" + " (" + data.count + ")" );
						}
					});
				}, '.($solidresConfig->get('reservation_live_refresh_interval', 15)  * 1000).');
			});
			';
			JFactory::getDocument()->addScriptDeclaration($script);
		}

		if ($updateCount > 0)
		{
			$script = '
			Solidres.jQuery(function($) {
				$("#sr_submenu_system").html("' . JText::_($menuStructure['SR_SUBMENU_SYSTEM']['9.0'][0]) . '" + " <span title=\"'. JText::plural('SR_SYSTEM_UPDATE_FOUND', $updateCount) .'\" class=\"badge badge-warning\">" + ' . $updateCount . ' + "</span>" );
			});
			';
			JFactory::getDocument()->addScriptDeclaration($script);
		}
		
		JFactory::getApplication()->triggerEvent('onSolidresSideNavPrepare', array(&$menuStructure, &$iconMap));

		foreach ($menuStructure as &$menus)
		{
			ksort($menus);
		}

		$displayData = array(
			'updateInfo'    => $updateInfo,
			'menuStructure' => $menuStructure,
			'iconMap'       => $iconMap,
			'viewName'      => $viewName
		);

		return SRLayoutHelper::render('solidres.navigation', $displayData);
	}
}