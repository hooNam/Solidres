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

$dateCheckIn = JDate::getInstance();
$dateCheckOut = JDate::getInstance();
$showDateInfo = !empty($this->checkin) && !empty($this->checkout);
?>

	<div class="availability-search">
		<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
			<div class="<?php echo SR_UI_GRID_COL_12 ?>">
				<h3><i class="fa fa-check-square"></i> <?php echo JText::_('SR_AVAILABLE_ROOMS')?></h3>
			</div>
		</div>
	</div>

	<?php if ($this->checkin && $this->checkout && count($this->item->roomTypes) > 0) : ?>
	<div class="availability-search-info">
		<?php
		/*if ( $this->item->totalOccupancyMax >= ($this->item->roomsOccupancyOptionsAdults + $this->item->roomsOccupancyOptionsChildren) ) :
			echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO',
				$this->item->totalAvailableRoom,
				$this->checkinFormatted ,
				$this->checkoutFormatted,
				$this->item->roomsOccupancyOptionsAdults,
				$this->item->roomsOccupancyOptionsChildren
			);
		endif;*/

		if ($this->item->roomsOccupancyOptionsAdults == 0 && $this->item->roomsOccupancyOptionsChildren == 0) :
			echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG4',
				$this->item->totalAvailableRoom,
				$this->checkinFormatted ,
				$this->checkoutFormatted
			);
		else :
			if ($this->item->totalOccupancyMax >= ($this->item->roomsOccupancyOptionsAdults + $this->item->roomsOccupancyOptionsChildren) && $this->item->totalAvailableRoom > 0) :
				if ($this->item->totalAvailableRoom >= $this->item->roomsOccupancyOptionsCount) :
					echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG1',
						$this->item->totalAvailableRoom,
						$this->checkinFormatted ,
						$this->checkoutFormatted,
						$this->item->roomsOccupancyOptionsAdults,
						$this->item->roomsOccupancyOptionsChildren
					);
				else:
					echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG2',
						$this->item->totalAvailableRoom,
						$this->checkinFormatted ,
						$this->checkoutFormatted,
						$this->item->roomsOccupancyOptionsAdults,
						$this->item->roomsOccupancyOptionsChildren
					);
				endif;
			else :
				echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG3',
					$this->checkinFormatted ,
					$this->checkoutFormatted,
					$this->item->roomsOccupancyOptionsAdults,
					$this->item->roomsOccupancyOptionsChildren
				);

			endif;
		endif;
		?>
		<a class="" href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.startOver&id='. $this->item->id . '&Itemid=' . $this->itemid, false ) ?>"><i class="fa fa-refresh"></i> <?php echo JText::_('SR_SEARCH_RESET')?></a>
	</div>
	<?php endif; ?>

	<form id="sr-checkavailability-form-component"
		  action="<?php echo JRoute::_('index.php?option=com_solidres&view=reservationasset&id=' . $this->item->id.'&Itemid='.$this->itemid, false); ?>"
		  method="GET"
		>

		<input type="hidden"
			   name="checkin"
			   value="<?php echo isset($this->checkin) ? $this->checkin : $dateCheckIn->add(new DateInterval('P'.($this->minDaysBookInAdvance).'D'))->setTimezone($this->timezone)->format('d-m-Y', true) ?>"
			   />

		<input type="hidden"
			   name="checkout"
			   value="<?php echo isset($this->checkout) ? $this->checkout : $dateCheckOut->add(new DateInterval('P'.($this->minDaysBookInAdvance + $this->minLengthOfStay).'D'))->setTimezone($this->timezone)->format('d-m-Y', true) ?>"
			   />
		<input type="hidden" name="Itemid" value="<?php echo $this->itemid ?>" />
		<input type="hidden" name="id" value="<?php echo $this->item->id ?>" />
		<input type="hidden" name="task" value="reservationasset.checkavailability" />
		<input type="hidden" name="option" value="com_solidres" />
		<input type="hidden" name="ts" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>