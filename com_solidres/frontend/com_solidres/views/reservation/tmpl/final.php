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

// Get some data from successful reservation
if (!isset($this->reservation)) :
$paymentMethodMessage = $this->app->getUserState($this->context . '.payment_method_message');
?>
<div id="solidres">
	<div class="alert alert-success">
		<?php
		if (!empty($paymentMethodMessage)) :
			echo $paymentMethodMessage;
		else :
			echo JText::sprintf('SR_RESERVATION_COMPLETE',
				$this->app->getUserState($this->context.'.customer_firstname'),
				$this->app->getUserState($this->context.'.code'),
				$this->app->getUserState($this->context.'.customeremail'),
				$this->app->getUserState($this->context.'.reservation_asset_name'),
				JUri::root()
			);
		endif;
		?>
	</div>
</div>
<?php
$this->app->setUserState($this->context . '.payment_method_message', NULL);
$this->app->setUserState($this->context . '.payment_method_custom_email_content', NULL);
else :
?>
	<div id="solidres">
		
		<h3><?php echo JText::_('SR_ASSET_INFO') ?></h3>

		<table class="table table-striped">
			<thead></thead>
			<tbody>
			<tr>
				<td>
					<?php echo JText::_('SR_CONFIRMATION_ASSET_NAME') ?>	
				</td>
				<td>
					<?php echo $this->asset->name ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('SR_CONFIRMATION_ASSET_ADDRESS') ?>
				</td>
				<td>
					<?php echo $this->asset->address_1 .', '.
					           (!empty($this->asset->city) ? $this->asset->city.', ' : '').
					           (!empty($this->asset->postcode) ? $this->asset->postcode.', ' : '').
					           $this->asset->country_name ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('SR_CONFIRMATION_ASSET_EMAIL') ?>
				</td>
				<td>
					<?php echo $this->asset->email ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('SR_CONFIRMATION_ASSET_PHONE') ?>
				</td>
				<td>
					<?php echo $this->asset->phone ?>
				</td>
			</tr>
			</tbody>
		</table>

		<h3><?php echo JText::_('SR_BOOKING_INFO') ?></h3>
		
		<table class="table table-striped">
			<thead></thead>
			<tbody>
				<tr>
					<td>
						<?php echo JText::_('SR_CONFIRMATION_BOOKING_NUMBER') ?>
					</td>
					<td>
						<?php echo $this->reservation->code ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('SR_CONFIRMATION_EMAIL') ?>
					</td>
					<td>
						<?php echo $this->reservation->customer_email ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('SR_CONFIRMATION_BOOKING_DETAILS') ?>
					</td>
					<td>
						<?php
						if (!isset($this->reservation->booking_type)) :
							$this->reservation->booking_type = 0;
						endif;

						if ($this->reservation->booking_type == 0) :
							echo JText::plural('SR_NIGHTS', $this->lengthOfStay);
						else :
							echo JText::plural('SR_DAYS', $this->lengthOfStay + 1);
						endif;
						?>,

						<?php echo JText::plural('SR_CONFIRMATION_BOOKING_ROOM_NUM', count($this->reservation->reserved_room_details)) ?>

					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('SR_CONFIRMATION_CHECKIN') ?>
					</td>
					<td>
						<?php echo $this->reservation->checkin ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('SR_CONFIRMATION_CHECKOUT') ?>
					</td>
					<td>
						<?php echo $this->reservation->checkout ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('SR_CONFIRMATION_TOTAL_PRICE') ?>
					</td>
					<td>
						<?php
						JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
						$baseCurrency = new SRCurrency($this->reservation->total_price_tax_incl - $this->reservation->total_discount, $this->reservation->currency_id);
						echo $baseCurrency->format()
						?>
					</td>
				</tr>
			</tbody>
		</table>

		<h3><?php echo JText::_('SR_BOOKING_CONFIRMATION_ROOM_DETAILS') ?> </h3>

		<table>
			<thead></thead>
			<tbody>
			<?php
			$reservedRoomDetails = $this->reservation->reserved_room_details;
			foreach($reservedRoomDetails as $room) : ?>
				<dl>
					<dt>
						<?php echo $room->room_type_name ?>
						(
						<?php
						echo JText::plural('SR_BOOKING_CONFIRMATION_ADULTS', $room->adults_number ) . ' ' . JText::_('SR_AND') . ' ' . JText::plural('SR_BOOKING_CONFIRMATION_CHILDREN', $room->children_number )
						?>
						)
					</dt>
					<dd><?php echo JText::_("SR_BOOKING_CONFIRMATION_GUEST_FULLNAME") ?>: <?php echo $room->guest_fullname ?></dd>
					<dd>
						<?php
						if (is_array($room->other_info)) :
							foreach ($room->other_info as $info) :
								if (substr($info->key, 0, 7) == 'smoking') :
									echo JText::_('SR_BOOKING_CONFIRMATION_'.$info->key) . ': ' . ($info->value == '' ? JText::_('SR_NO_PREFERENCES') : ($info->value == 1 ? JText::_('SR_YES'): JText::_('SR_NO') )  ) ;
								endif;
							endforeach;
						endif
						?>
					</dd>
					<dd>
						<?php
						$roomPriceCurrency = clone $baseCurrency;
						$roomPriceCurrency->setValue(isset($room->room_price_tax_incl) ? $room->room_price_tax_incl : $room->room_price);
						echo JText::_('SR_BOOKING_CONFIRMATION_ROOM_COST') . ': ' . $roomPriceCurrency->format();
						?>
					</dd>
				</dl>
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
<?php

endif;