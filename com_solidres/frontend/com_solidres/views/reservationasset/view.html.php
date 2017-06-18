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
class SolidresViewReservationAsset extends JViewLegacy
{
	protected $item;
	protected $solidresCurrency;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$this->config = JComponentHelper::getParams('com_solidres');
		$this->systemConfig = JFactory::getConfig();
		$this->showPoweredByLink = $this->config->get('show_solidres_copyright', '1');
		$this->showFrontendTariffs = $this->config->get('show_frontend_tariffs', '1');
		$this->app = JFactory::getApplication();

		$this->item	= $model->getItem();

		if ($this->item->params['access-view'] == false)
		{
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$this->checkin = $model->getState('checkin');
		$this->checkout = $model->getState('checkout');
		$this->adults = $model->getState('adults');
		$this->children = $model->getState('children');
		$this->countryId = $model->getState('country_id');
		$this->geoStateId = $model->getState('geo_state_id');
		$this->roomTypeObj = SRFactory::get('solidres.roomtype.roomtype');
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');
		$this->stayLength = SRUtilities::calculateDateDiff($this->checkin, $this->checkout);
		$this->document = JFactory::getDocument();
		$this->context = 'com_solidres.reservation.process';
		$this->coupon  = JFactory::getApplication()->getUserState($this->context . '.coupon');
		$this->tzoffset = $this->systemConfig->get('offset');
		$this->selectedRoomTypes = $this->app->getUserState($this->context . '.room');
		$this->showTaxIncl = $this->config->get('show_price_with_tax', 0);
		$this->selectedTariffs = $this->app->getUserState($this->context . '.current_selected_tariffs');
		$this->solidresCurrency = new SRCurrency(0, $this->item->currency_id);

		$this->timezone = new DateTimeZone($this->tzoffset);
		$this->minDaysBookInAdvance = $this->config->get('min_days_book_in_advance', 0);
		$this->maxDaysBookInAdvance = $this->config->get('max_days_book_in_advance', 0);
		$this->minLengthOfStay = $this->config->get('min_length_of_stay', 1);
		$this->dateFormat = $this->config->get('date_format', 'd-m-Y');
		$this->showLoginBox = $this->config->get('show_login_box', 0);
		$this->solidresMedia = SRFactory::get('solidres.media.media');
		$this->solidresStyle = (defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? SR_LAYOUT_STYLE : 'style1' ;
		$this->item->text = $this->item->description;

		$activeMenu = JFactory::getApplication()->getMenu()->getActive();
		$this->itemid = NULL;

		if (isset($activeMenu))
		{
			$this->itemid = $activeMenu->id ;
		}

		$datePickerMonthNum = $this->config->get('datepicker_month_number', 3);
		$weekStartDay = $this->config->get('week_start_day', 1);

		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.framework');
		SRHtml::_('jquery.colorbox', 'show_map', '95%', '90%', 'true', 'false');

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);
		JHtml::stylesheet('com_solidres/assets/'.$this->solidresStyle.'.min.css', false, true);
		JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
		$this->document->addScriptDeclaration('
			Solidres.jQuery(function ($) {
				$(".sr-photo").colorbox({rel:"sr-photo", transition:"fade", width: "98%", height: "98%", className: "colorbox-w"});
				var minLengthOfStay = '.$this->minLengthOfStay.';
				var checkout_component = $(".checkout_component").datepicker({
					minDate : "+' . ( $this->minDaysBookInAdvance + $this->minLengthOfStay ). '",
					numberOfMonths : '.$datePickerMonthNum.',
					showButtonPanel : true,
					dateFormat : "dd-mm-yy",
					firstDay: '.$weekStartDay.'
				});
				var checkin_component = $(".checkin_component").datepicker({
					minDate : "+' . ($this->minDaysBookInAdvance ) . 'd",
					'.($this->maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($this->maxDaysBookInAdvance) . '",' : '' ).'
					numberOfMonths : '.$datePickerMonthNum.',
					showButtonPanel : true,
					dateFormat : "dd-mm-yy",
					onSelect : function() {
						var checkoutMinDate = $(this).datepicker("getDate", "+1d");
						checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
						checkout_component.datepicker( "option", "minDate", checkoutMinDate );
						checkout_component.datepicker( "setDate", checkoutMinDate);
					},
					firstDay: '.$weekStartDay.'
				});
				$(".ui-datepicker").addClass("notranslate");
			});

			Solidres.child_max_age_limit = '.$this->config->get('child_max_age_limit', 17).';
		');

		if (!empty($this->checkin) && !empty($this->checkout))
		{
			$this->checkinFormatted = JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true);
			$this->checkoutFormatted = JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true);
			$this->document->addScriptDeclaration('
				Solidres.jQuery(function ($) {
					isAtLeastOnRoomTypeSelected();
				});
			');
		}

		JText::script('SR_CAN_NOT_REMOVE_COUPON');
		JText::script('SR_SELECT_AT_LEAST_ONE_ROOMTYPE');
		JText::script('SR_ERROR_CHILD_MAX_AGE');
		JText::script('SR_AND');
		JText::script('SR_TARIFF_BREAK_DOWN');
		JText::script('SUN');
		JText::script('MON');
		JText::script('TUE');
		JText::script('WED');
		JText::script('THU');
		JText::script('FRI');
		JText::script('SAT');
		JText::script('SR_NEXT');
		JText::script('SR_BACK');
		JText::script('SR_PROCESSING');
		JText::script('SR_CHILD');
		JText::script('SR_CHILD_AGE_SELECTION_JS');
		JText::script('SR_CHILD_AGE_SELECTION_1_JS');
		JText::script('SR_ONLY_1_LEFT');
		JText::script('SR_ONLY_2_LEFT');
		JText::script('SR_ONLY_3_LEFT');
		JText::script('SR_ONLY_4_LEFT');
		JText::script('SR_ONLY_5_LEFT');
		JText::script('SR_ONLY_6_LEFT');
		JText::script('SR_ONLY_7_LEFT');
		JText::script('SR_ONLY_8_LEFT');
		JText::script('SR_ONLY_9_LEFT');
		JText::script('SR_ONLY_10_LEFT');
		JText::script('SR_ONLY_11_LEFT');
		JText::script('SR_ONLY_12_LEFT');
		JText::script('SR_ONLY_13_LEFT');
		JText::script('SR_ONLY_14_LEFT');
		JText::script('SR_ONLY_15_LEFT');
		JText::script('SR_ONLY_16_LEFT');
		JText::script('SR_ONLY_17_LEFT');
		JText::script('SR_ONLY_18_LEFT');
		JText::script('SR_ONLY_19_LEFT');
		JText::script('SR_ONLY_20_LEFT');
		JText::script('SR_SHOW_MORE_INFO');
		JText::script('SR_HIDE_MORE_INFO');
		JText::script('SR_AVAILABILITY_CALENDAR_CLOSE');
		JText::script('SR_AVAILABILITY_CALENDAR_VIEW');
		JText::script('SR_PROCESSING');
		JText::script('SR_USERNAME_EXISTS');
		JText::script('SR_SHOW_TARIFFS');
		JText::script('SR_HIDE_TARIFFS');

		JPluginHelper::importPlugin('solidres');
		JPluginHelper::importPlugin('content');
		$this->dispatcher = JEventDispatcher::getInstance();
		$this->dispatcher->trigger('onContentPrepare', array('com_solidres.asset', &$this->item, &$this->item->params));
		$this->dispatcher->trigger('onSolidresAssetViewLoad', array(&$this->item));
		$this->events = new stdClass;
		$this->events->afterDisplayAssetName  = join("\n", $this->dispatcher->trigger('onSolidresAfterDisplayAssetName', array(&$this->item, &$this->item->params)));
		$this->events->beforeDisplayAssetForm = join("\n", $this->dispatcher->trigger('onSolidresBeforeDisplayAssetForm', array(&$this->item, &$this->item->params)));
		$this->events->afterDisplayAssetForm  = join("\n", $this->dispatcher->trigger('onSolidresAfterDisplayAssetForm', array(&$this->item, &$this->item->params)));

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}
		$this->defaultGallery = '';
		$defaultGallery       = $this->config->get('default_gallery', 'simple_gallery');
		if (SRPlugin::isEnabled($defaultGallery))
		{
			$layout = SRLayoutHelper::getInstance();
			$layout->addIncludePath(SRPlugin::getLayoutPath($defaultGallery));
			$this->defaultGallery = $layout->render('gallery.default' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : ''), array('media' => $this->item->media, 'asset_name' => $this->item->name));
		}

		$this->_prepareDocument();
		if (SRPlugin::isEnabled('user'))
		{
			array_push($this->_path['template'], SRPlugin::getSitePath('user') . '/views/reservationasset/tmpl');
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_solidres_category_'.$this->item->category_id, JPATH_COMPONENT);

		parent::display((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? SR_LAYOUT_STYLE : null );
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
			$this->document->setTitle($this->item->name . ', ' .  $this->item->city . ', ' . $this->item->country_name . ' | ' . $this->item->address_1);
		}

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}

		if ($this->item->metadata)
		{
			foreach ($this->item->metadata as $k => $v)
			{
				if ($v)
				{
					$this->document->setMetadata($k, $v);
				}
			}
		}
	}

	/**
	 * Get the min price from a given tariff and show the formatted result
	 *
	 * @param $tariff
	 *
	 * @return string
	 */
	protected function getMinPrice($tariff)
	{
		$tariffSuffix = '';
		$min = null;
		$stayLength = 0;
		if ($tariff->type == 0 || $tariff->type == 2) :
			$tariffSuffix .= JText::_('SR_TARIFF_SUFFIX_PER_ROOM');
		else :
			$tariffSuffix .= JText::_('SR_TARIFF_SUFFIX_PER_PERSON');
		endif;

		switch ($tariff->type)
		{
			case 0: // rate per room per night
				if ($tariff->mode == 1)
				{
					foreach ($tariff->details['per_room'] as $month => $details)
					{
						foreach ($details as $detail)
						{
							if (!isset($min) || $min->price > $detail->price)
							{
								$min = $detail;
							}
						}
					}
				}
				else
				{
					$min = array_reduce($tariff->details['per_room'], function($t1, $t2) {
						return $t1->price < $t2->price ? $t1 : $t2;
					}, array_shift($tariff->details['per_room']));
				}

				$stayLength = 1;
				break;
			case 1: // rate per person per night
				if ($tariff->mode == 1)
				{
					$min = array_reduce($tariff->details['adult1'], function($t1, $t2) {
						return array_reduce($t2, function($t3, $t4) {
							return $t3->price < $t4->price ? $t3 : $t4;
						}, array_shift($t2));
					}, array_shift($tariff->details['adult1']));
				}
				else
				{
					$min = array_reduce($tariff->details['adult1'], function($t1, $t2) {
						return $t1->price < $t2->price ? $t1 : $t2;
					}, array_shift($tariff->details['adult1']));
				}

				$stayLength = 1;
				break;
			case 2: // package per room
				$min = $tariff->details['per_room'][0];
				$stayLength = $tariff->d_min;
				break;
			case 3: // package per person
				$min = $tariff->details['adult1'][0];
				$stayLength = $tariff->d_min;
				break;
			default:
				break;

		}

		// Calculate tax amount
		$totalImposedTaxAmount = 0;
		if ($this->showTaxIncl)
		{
			if (count($this->item->taxes) > 0)
			{
				foreach ($this->item->taxes as $taxType)
				{
					$totalImposedTaxAmount += $min->price * $taxType->rate;
				}
			}
		}


		$minCurrency = clone $this->solidresCurrency;
		$minCurrency->setValue($min->price + $totalImposedTaxAmount);

		$tariffSuffix .= JText::plural( $this->item->booking_type == 0 ? 'SR_TARIFF_SUFFIX_NIGHT_NUMBER' : 'SR_TARIFF_SUFFIX_DAY_NUMBER', $stayLength);

		return '<span class="starting_from">' . JText::_('SR_STARTING_FROM') . '</span><span class="min_tariff">' . $minCurrency->format() . '</span><span class="tariff_suffix">' . $tariffSuffix . '</span>';
	}
}
