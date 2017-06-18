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

require_once JPATH_SITE . '/components/com_solidres/helpers/route.php';

$solidresMedia = SRFactory::get('solidres.media.media');
?>

<div id="solidres" class="<?php echo SR_UI ?> single_room_type_view">

	<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
		<div class="<?php echo SR_UI_GRID_COL_12 ?>">
			<h3><?php echo $this->item->name; ?></h3>
		</div>
	</div>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
		<div class="<?php echo SR_UI_GRID_COL_12 ?>">
			<?php echo $this->item->description; ?>
		</div>
	</div>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
		<div class="<?php echo SR_UI_GRID_COL_12 ?>">
			<div class="unstyled more_desc" id="more_desc_<?php echo $this->item->id ?>">
				<?php
				if (!empty($this->item->roomtype_custom_fields['room_facilities'])) :
					echo '<p><strong>'. JText::_('SR_ROOM_FACILITIES') .':</strong> '.  $this->item->roomtype_custom_fields['room_facilities'] .'</p>';
				endif;

				if (!empty($this->item->roomtype_custom_fields['room_size'])) :
					echo '<p><strong>'. JText::_('SR_ROOM_SIZE') .':</strong> '.  $this->item->roomtype_custom_fields['room_size'] .'</p>';
				endif;

				if (!empty($this->item->roomtype_custom_fields['bed_size'])) :
					echo '<p><strong>'. JText::_('SR_BED_SIZE') .':</strong> '.  $this->item->roomtype_custom_fields['bed_size'] .'</p>';
				endif;

				if (!empty($this->item->roomtype_custom_fields['taxes'])) :
					echo '<p><strong>'. JText::_('SR_TAXES') .':</strong> '.  $this->item->roomtype_custom_fields['taxes'] .'</p>';
				endif;

				if (!empty($this->item->roomtype_custom_fields['prepayment'])) :
					echo '<p><strong>'. JText::_('SR_PREPAYMENT') .':</strong> '.  $this->item->roomtype_custom_fields['prepayment'] .'</p>';
				endif;
				?>
			</div>
		</div>
	</div>

	<div class="<?php echo SR_UI_GRID_CONTAINER ?> call_to_action">
		<div class="<?php echo SR_UI_GRID_COL_12 ?>">
			<p>
				<a class="btn btn-default btn-large"
				   href="<?php echo SolidresHelperRoute::getReservationAssetRoute($this->item->reservation_asset_id, $this->item->id);?>">
					<?php echo JText::_('SR_SINGLE_ROOM_TYPE_VIEW_CALL_TO_ACTION') ?>
				</a>
			</p>
		</div>
	</div>

	<?php echo $this->defaultGallery; ?>

</div>