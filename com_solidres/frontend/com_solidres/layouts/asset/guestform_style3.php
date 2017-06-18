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

$selectedCustomerTitle = !empty($displayData['reservationDetails']->guest["customer_title"]) ? $displayData['reservationDetails']->guest["customer_title"] : '';
$user = JFactory::getUser();
$isFrontEnd = JFactory::getApplication()->isSite();
$disableCustomerRegistration = true;
if (isset($displayData['reservationDetails']->asset_params['disable_customer_registration'])) :
	$disableCustomerRegistration = $displayData['reservationDetails']->asset_params['disable_customer_registration'];
endif;
if (!isset($displayData['reservationDetails']->hub_dashboard)) :
	$displayData['reservationDetails']->hub_dashboard = 0;
endif;
$isGuestMakingReservation = JFactory::getApplication()->isSite() && !$displayData['reservationDetails']->hub_dashboard;
?>

<form enctype="multipart/form-data"
	  id="sr-reservation-form-guest"
	  class="sr-reservation-form form-stacked sr-validate"
	  action="<?php echo JUri::base() ?>index.php?option=com_solidres&task=reservation<?php echo $isFrontEnd ? '' : 'base' ?>.process&step=guestinfo&format=json"
	  method="POST">

<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-top">

	<div class="<?php echo SR_UI_GRID_COL_8 ?>">
		<div class="inner">
			<?php if ($isFrontEnd) : ?>
			<p><?php echo JText::_('SR_GUEST_INFO_STEP_NOTICE') ?></p>
			<?php endif ?>
		</div>
	</div>

	<div class="<?php echo SR_UI_GRID_COL_4 ?>">
		<div class="inner">
			<div class="btn-group">
				<button type="button" class="btn btn-default reservation-navigate-back" data-step="guestinfo" data-prevstep="room">
					<i class="fa fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
				</button>
				<button data-step="guestinfo" type="submit" class="btn btn-success">
					<i class="fa fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
				</button>
			</div>
		</div>
	</div>
</div>

<?php if ($isFrontEnd) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
	<div class="<?php echo SR_UI_GRID_COL_12 ?>">
		<h3><?php echo JText::_('SR_GUEST_INFORMATION') ?></h3>
	</div>
</div>

<?php endif ?>

<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
	<div class="<?php echo SR_UI_GRID_COL_6 ?>">
		<fieldset>
			<div class="form-group">
				<label for="firstname">
					<?php echo JText::_("SR_CUSTOMER_TITLE") ?>
				</label>
				<?php
				echo JHtml::_("select.genericlist", $displayData['customerTitles'], "jform[customer_title]", array("class" => 'form-control span12', 'required'), "value", "text", $selectedCustomerTitle, "")
				?>
			</div>
			<div class="form-group">
				<label for="firstname">
					<?php echo JText::_("SR_FIRSTNAME") ?>
				</label>
				<input id="firstname"
					   required
					   name="jform[customer_firstname]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_firstname"]) ? $displayData['reservationDetails']->guest["customer_firstname"] : "") ?>"/>
			</div>
			<div class="form-group">
				<label for="middlename">
					<?php echo JText::_("SR_MIDDLENAME") ?>
				</label>
				<input id="middlename"
					   name="jform[customer_middlename]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_middlename"]) ? $displayData['reservationDetails']->guest["customer_middlename"] : "") ?>"/>

			</div>
			<div class="form-group">
				<label for="lastname">
					<?php echo JText::_("SR_LASTNAME") ?>
				</label>
				<input id="lastname"
					   required
					   name="jform[customer_lastname]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_lastname"]) ? $displayData['reservationDetails']->guest["customer_lastname"] : "") ?>"/>
			</div>
			<div class="form-group"><label for="email">
					<?php echo JText::_("SR_EMAIL") ?>
				</label>
				<input id="email"
					   required
					   name="jform[customer_email]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_email"]) ? $displayData['reservationDetails']->guest["customer_email"] : "") ?>"/></div>
			<div class="form-group">
				<label for="phonenumber">
					<?php echo JText::_("SR_PHONENUMBER") ?>
				</label>
				<input id="phonenumber"
					   required
					   name="jform[customer_phonenumber]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_phonenumber"]) ? $displayData['reservationDetails']->guest["customer_phonenumber"] : "") ?>"/>
			</div>
			<div class="form-group">
				<label for="mobilephone">
					<?php echo JText::_("SR_MOBILEPHONE") ?>
				</label>
				<input id="mobilephone"
					   name="jform[customer_mobilephone]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_mobilephone"]) ? $displayData['reservationDetails']->guest["customer_mobilephone"] : "") ?>"/></div>
			<div class="form-group">
				<label for="company">
					<?php echo JText::_("SR_COMPANY") ?>
				</label>
				<input id="company"
					   name="jform[customer_company]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_company"]) ? $displayData['reservationDetails']->guest["customer_company"] : "") ?>"/>
			</div>
			<div class="form-group">
				<label for="address1">
					<?php echo JText::_("SR_ADDRESS_1") ?>
				</label>
				<input id="address1"
					   required
					   name="jform[customer_address1]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_address1"]) ? $displayData['reservationDetails']->guest["customer_address1"] : "") ?>"/>

			</div>
			<div class="form-group">
				<label for="address2">
					<?php echo JText::_("SR_ADDRESS_2") ?>
				</label>
				<input id="address2"
					   name="jform[customer_address2]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_address2"]) ? $displayData['reservationDetails']->guest["customer_address2"] : "") ?>"/>
			</div>
		</fieldset>
	</div>

	<div class="<?php echo SR_UI_GRID_COL_6 ?>">
		<fieldset>
			<div class="form-group">
				<label for="vat_number">
					<?php echo JText::_("SR_VAT_NUMBER") ?>
				</label>
				<input id="vat_number"
					   name="jform[customer_vat_number]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_vat_number"]) ? $displayData['reservationDetails']->guest["customer_vat_number"] : "") ?>"/>
			</div>
			<div class="form-group">
				<label for="city"><?php echo JText::_("SR_CITY") ?></label>
				<input id="city"
					   required
					   name="jform[customer_city]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_city"]) ? $displayData['reservationDetails']->guest["customer_city"] : "") ?>"/>
			</div>
			<div class="form-group">
				<label for="zip"><?php echo JText::_("SR_ZIP") ?></label>
				<input id="zip"
					   name="jform[customer_zipcode]"
					   type="text"
					   class="form-control span12"
					   value="<?php echo (isset($displayData['reservationDetails']->guest["customer_zipcode"]) ? $displayData['reservationDetails']->guest["customer_zipcode"] : "") ?>"/>
			</div>
			<div class="form-group">
				<label for="jform[country_id]"><?php echo JText::_("SR_COUNTRY") ?></label>

				<?php
				$selectedCountryId = isset($displayData['reservationDetails']->guest["customer_country_id"]) ? $displayData['reservationDetails']->guest["customer_country_id"] : 0;
				echo JHtml::_("select.genericlist", $displayData['countries'], "jform[customer_country_id]", array("class" => "country_select " . SR_UI_GRID_COL_12, 'required' => 'required'), "value", "text", $selectedCountryId, "country");
				?>
			</div>
			<div class="form-group">
				<label for="jform[customer_geo_state_id]"><?php echo JText::_("SR_STATE") ?></label>
				<?php
				$selectedGeoStateId = isset($displayData['reservationDetails']->guest["customer_geo_state_id"]) ? $displayData['reservationDetails']->guest["customer_geo_state_id"] : 0;

				echo JHtml::_("select.genericlist", $displayData['geoStates'], "jform[customer_geo_state_id]", array("class" => "state_select span12"), "value", "text", $selectedGeoStateId, "state");
				?>
			</div>
			<div class="form-group">
				<label for="note"><?php echo JText::_("SR_NOTE") ?></label>
				<textarea id="note" name="jform[note]" rows="10" cols="30" placeholder="<?php echo JText::_("SR_RESERVATION_NOTE") ?>"
						  class="span12 form-control"><?php echo (isset($displayData['reservationDetails']->guest["note"]) ? $displayData['reservationDetails']->guest["note"] : "") ?></textarea>
			</div>
			<?php if (SRPlugin::isEnabled('user') && $user->get('id') <= 0 && (isset($disableCustomerRegistration) && !$disableCustomerRegistration)) : ?>
			<div class="form-group">
				<label class="checkbox">
					<input id="register_an_account_form" type="checkbox"> <?php echo JText::_('SR_REGISTER_WITH_US_TEXT') ?>
				</label>
				<div class="register_an_account_form" style="display: none">
					<label for="username">
						<?php echo JText::_("SR_USERNAME") ?>
					</label>
					<input id="username"
						   name="jform[customer_username]"
						   type="text"
						   class="form-control span12"
						   value=""/>

					<label for="password">
						<?php echo JText::_("SR_PASSWORD") ?>
					</label>
					<input id="password"
						   name="jform[customer_password]"
						   type="password"
						   class="form-control span12"
						   value=""
						   autocomplete="off"
					/>
				</div>
			</div>
			<?php endif ?>
		</fieldset>
	</div>
</div>

<?php
// Show Per Booking Extras
if (count($displayData['extras'])) :
?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
	<div class="<?php echo SR_UI_GRID_COL_12 ?>">
		<h3><?php echo JText::_('SR_ENHANCE_YOUR_STAY') ?></h3>
	</div>
</div>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
		<div class="<?php echo SR_UI_GRID_COL_12 ?>">
			<?php
			foreach ( $displayData['extras'] as $extra ) :
				$extraInputCommonName = 'jform[extras][' . $extra->id . ']';
				$checked = '';
				$disabledCheckbox = '';
				$disabledSelect = 'disabled="disabled"';
				$alreadySelected = false;
				if ( isset( $displayData['reservationDetails']->guest['extras'] ) ) :
					$alreadySelected = array_key_exists( $extra->id, (array) $displayData['reservationDetails']->guest['extras'] );
				endif;

				if ( $extra->mandatory == 1 || $alreadySelected ) :
					$checked = 'checked="checked"';
				endif;

				if ( $extra->mandatory == 1 ) :
					$disabledCheckbox = 'disabled="disabled"';
					$disabledSelect   = '';
				endif;

				if ( $alreadySelected && $extra->mandatory == 0 ) :
					$disabledSelect = '';
				endif;
				?>
				<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
					<div class="<?php echo SR_UI_GRID_COL_12 ?> extras_row_guestform">
					<input <?php echo $checked ?> <?php echo $disabledCheckbox ?> type="checkbox"
																				  data-target="guest_extra_<?php echo $extra->id ?>"/>

					<?php
					if ( $extra->mandatory == 1 ) :
						?>
						<input type="hidden" name="<?php echo $extraInputCommonName ?>[quantity]" value="1"/>
						<?php
					endif;
					?>
					<select class="<?php echo SR_UI_GRID_COL_2 ?> guest_extra_<?php echo $extra->id ?>"
							name="<?php echo $extraInputCommonName ?>[quantity]"
						<?php echo $disabledSelect ?>>
						<?php
						for ( $quantitySelection = 1; $quantitySelection <= $extra->max_quantity; $quantitySelection ++ ) :
							$checked = '';
							if ( isset( $displayData['reservationDetails']->guest['extras'][ $extra->id ]['quantity'] ) ) :
								$checked = ( $displayData['reservationDetails']->guest['extras'][ $extra->id ]['quantity'] == $quantitySelection ) ? 'selected="selected"' : '';
							endif;
							?>
							<option <?php echo $checked ?>
								value="<?php echo $quantitySelection ?>"><?php echo $quantitySelection ?></option>
							<?php
						endfor;
						?>
					</select>
					<span>
								<?php echo $extra->name ?>
						<a href="javascript:void(0)"
						   class="toggle_extra_details"
						   data-target="extra_details_<?php echo $extra->id ?>">
									<?php echo JText::_( 'SR_EXTRA_MORE_DETAILS' ) ?>
								</a>
							</span>
					<span class="extra_details" id="extra_details_<?php echo $extra->id ?>"
						  style="display: none">
								<?php if ( $extra->charge_type == 3 || $extra->charge_type == 5 || $extra->charge_type == 6 ) : ?>
									<span>
									<?php echo JText::_( 'SR_EXTRA_PRICE_ADULT' ) . ': ' . $extra->currencyAdult->format() . ' (' . JText::_( SRExtra::$chargeTypes[ $extra->charge_type ] ) . ')' ?>
								</span>
									<span>
									<?php echo JText::_( 'SR_EXTRA_PRICE_CHILD' ) . ': ' . $extra->currencyChild->format() . ' (' . JText::_( SRExtra::$chargeTypes[ $extra->charge_type ] ) . ')' ?>
								</span>
								<?php else : ?>
									<span>
									<?php echo JText::_( 'SR_EXTRA_PRICE' ) . ': ' . $extra->currency->format() . ' (' . JText::_( SRExtra::$chargeTypes[ $extra->charge_type ] ) . ')' ?>
								</span>
								<?php endif; ?>
						<span>
									<?php echo $extra->description ?>
								</span>
							</span>
					</div>
				</div>
				<?php
			endforeach;
			endif;
			?>
		</div>
</div>
<?php
// Show available payment methods
$solidresPaymentConfigData = new SRConfig(array('scope_id' => $displayData['assetId']));

$availablePaymentPlugins = array('paylater', 'bankwire');
foreach ($displayData['solidresPaymentPlugins'] as $paymentPlugin) :
	$availablePaymentPlugins[] = $paymentPlugin->element;
endforeach;

$availablePaymentPluginsCount = 0;
foreach ($availablePaymentPlugins as $plugin) :
	$enabled = $solidresPaymentConfigData->get('payments/' . $plugin . '/' . $plugin . '_enabled');
	if ($enabled) :
		$availablePaymentPluginsCount ++;
	endif;
endforeach;

if (!$isGuestMakingReservation) :
	if (!$displayData['isNew']) :
		$processOnlinePaymentCheck = '';
	else :
		$processOnlinePaymentCheck = 'checked';
	endif;
endif;
?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?>" <?php echo $availablePaymentPluginsCount == 0 ? 'style="display: none"' : '' ?>>
	<div class="<?php echo SR_UI_GRID_COL_12 ?>">
		<h3>
			<?php echo JText::_('SR_PAYMENT_INFO') ?>
			<?php if (!$isGuestMakingReservation) : ?>
			<input type="checkbox" name="jform[processonlinepayment]" value="1"
			       id="processonlinepayment" data-target="payment_method_wrapper"
				<?php echo $processOnlinePaymentCheck ?>
			/>
			<?php echo JText::_('SR_RESERVATION_AMEND_PROCESS_ONLINE_PAYMENT') ?>
			<?php endif ?>
		</h3>
	</div>
</div>

<div class="<?php echo SR_UI_GRID_CONTAINER ?> payment_method_wrapper"
	<?php echo ($availablePaymentPluginsCount == 0 || (!$isGuestMakingReservation && $processOnlinePaymentCheck == '')) ? 'style="display: none"' : '' ?>>
	<div class="<?php echo SR_UI_GRID_COL_12 ?>">
		<ul class="unstyled list-unstyled payment_method_list">
		<?php
		JLoader::register('SRUtilities', SRPATH_LIBRARY . '/utilities/utilities.php');

		if ($solidresPaymentConfigData->get('payments/paylater/paylater_enabled')) :
			$checkPayLater = '';
			if (isset($displayData['reservationDetails']->guest["payment_method_id"])) :
				if ($displayData['reservationDetails']->guest["payment_method_id"] == "paylater") :
					$checkPayLater = "checked";
				endif;
			else :
				if ($solidresPaymentConfigData->get('payments/paylater/paylater_is_default') == 1):
					$checkPayLater = "checked";
				endif;
			endif;
			?>
			<li>
				<input id="payment_method_paylater" type="radio" class="payment_method_radio"
					   name="jform[payment_method_id]"
					   value="paylater" <?php echo $checkPayLater ?>/>
				<span class="popover_payment_methods"
					  data-content="<?php echo SRUtilities::translateText($solidresPaymentConfigData->get('payments/paylater/paylater_frontend_message')) ?>"
					  data-title="<?php echo JText::_("SR_PAYMENT_METHOD_PAYLATER") ?>">
					<?php echo JText::_("SR_PAYMENT_METHOD_PAYLATER") ?>

					<i class="fa fa-question-circle "></i>
				</span>
			</li>
		<?php
		endif;

		if ($solidresPaymentConfigData->get('payments/bankwire/bankwire_enabled')) :
			$checkBankWire = '';
			if (isset($displayData['reservationDetails']->guest["payment_method_id"])) :
				if ($displayData['reservationDetails']->guest["payment_method_id"] == "bankwire") :
					$checkBankWire = "checked";
				endif;
			else :
				if ($solidresPaymentConfigData->get('payments/bankwire/bankwire_is_default') == 1):
					$checkBankWire = "checked";
				endif;
			endif;
			?>
			<li>
				<input id="payment_method_bankwire" class="payment_method_radio" type="radio"
					   name="jform[payment_method_id]"
					   value="bankwire" <?php echo $checkBankWire ?> />
				<span class="popover_payment_methods"
					  data-content="<?php echo SRUtilities::translateText($solidresPaymentConfigData->get('payments/bankwire/bankwire_frontend_message')) ?>"
					  data-title="<?php echo JText::_("SR_PAYMENT_METHOD_BANKWIRE") ?>">
					<?php echo JText::_("SR_PAYMENT_METHOD_BANKWIRE") ?>
					<i class="fa fa-question-circle"></i>
				</span>
			</li>
		<?php
		endif;

		// For extra payment methods provide via plugins
		foreach ($displayData['solidresPaymentPlugins'] as $paymentPlugin) :
			$paymentPluginId = $paymentPlugin->element;

			if ($solidresPaymentConfigData->get('payments/' . $paymentPluginId . '/' . $paymentPluginId . '_enabled')) :
				$checked = '';
				if (isset($displayData['reservationDetails']->guest["payment_method_id"])) :
					if ($displayData['reservationDetails']->guest["payment_method_id"] == $paymentPluginId) :
						$checked = "checked";
					endif;
				else :
					if ($solidresPaymentConfigData->get("payments/$paymentPluginId/{$paymentPluginId}_is_default") == 1):
						$checked = "checked";
					endif;
				endif;

				// Load custom payment plugin field template if it is available, otherwise just render it normally
				$fieldTemplatePath = JPATH_PLUGINS . '/solidrespayment/' . $paymentPluginId . '/form/field.php';
				if (file_exists($fieldTemplatePath)) :
					@ob_start();
					include $fieldTemplatePath;
					echo @ob_get_clean();
				else :
					?>
					<li>
						<input id="payment_method_<?php echo $paymentPluginId ?>"
							   type="radio"
							   name="jform[payment_method_id]"
							   value="<?php echo $paymentPluginId ?>"
							   class="payment_method_radio"
							<?php echo $checked ?>
							/>
						<span class="popover_payment_methods"
							  data-content="<?php echo SRUtilities::translateText($solidresPaymentConfigData->get('payments/' . $paymentPluginId . '/' . $paymentPluginId . '_frontend_message')) ?>"
							  data-title="<?php echo JText::_("SR_PAYMENT_METHOD_" . $paymentPluginId) ?>">
							<?php echo JText::_("SR_PAYMENT_METHOD_" . $paymentPluginId) ?>
							<i class="fa fa-question-circle"></i>
						</span>
					</li>
				<?php
				endif;

			endif;
		endforeach;
		?>
		</ul>
	</div>
</div>

<div class="<?php echo SR_UI_GRID_CONTAINER ?> button-row button-row-bottom">
	<div class="<?php echo SR_UI_GRID_COL_8 ?>">
		<div class="inner">
			<?php if ($isFrontEnd) : ?>
			<p><?php echo JText::_('SR_GUEST_INFO_STEP_NOTICE') ?></p>
			<?php endif ?>
		</div>
	</div>
	<div class="<?php echo SR_UI_GRID_COL_4 ?>">
		<div class="inner">
			<div class="btn-group">
				<button type="button" class="btn btn-default reservation-navigate-back" data-step="guestinfo" data-prevstep="room">
					<i class="fa fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
				</button>
				<button data-step="guestinfo" type="submit" class="btn btn-success">
					<i class="fa fa-arrow-right"></i> <?php echo JText::_('SR_NEXT') ?>
				</button>
			</div>
		</div>
	</div>
</div>

<?php echo JHtml::_("form.token") ?>
<input type="hidden" name="jform[next_step]" value="confirmation"/>
</form>