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

$srMedia = SRFactory::get('solidres.media.media');

JFactory::getDocument()->addStyleDeclaration('
.ui-widget button.btn {
  font-size: 13px;
}
');
$col     = 6;
$spanNum = 12 / (int) $col;

?>

<form action="<?php JRoute::_('index.php?option=com_solidres'); ?>" method="post" name="adminForm"
      id="medialibraryform">
	<div class="<?php echo SR_UI_GRID_CONTAINER; ?>">
		<div class="<?php echo SR_UI_GRID_COL_6; ?>">
			<button id="media-library-delete" class="toolbar btn" type="submit">
				<i class="icon-remove"></i>
				<?php echo JText::_('SR_MEDIA_DELETE_BTN') ?>
			</button>
			<button type="button" id="media-modal-insert" class="toolbar btn">
				<i class="icon-ok"></i>
				<?php echo JText::_('SR_MEDIA_INSERT_BTN') ?>
			</button>
		</div>
		<div class="<?php echo SR_UI_GRID_COL_6; ?>">
			<div class="input-append pull-right">
				<input id="mediasearch" type="text" name="q" value="">
				<button class="btn" type="submit"><?php echo JText::_('SR_SEARCH') ?></button>
				<button class="btn" type="reset"><?php echo JText::_('SR_RESET') ?></button>
			</div>
		</div>
	</div>

	<div id="media-messsage"></div>

	<div class="pagination">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>

	<div id="medialibrary" class="clearfix">
		<?php
		if ($this->items) :
			for ($i = 0, $total = count($this->items); $i <= $total; $i++) :

				if ($i % $col == 0 && $i == 0) :
					echo '<div class="row-fluid media-lib-row">';
				elseif ($i % $col == 0 && $i != $total) :
					echo '</div><div class="row-fluid media-lib-row">';
				elseif ($i == $total) :
					echo '</div>';
				endif;

				if ($i < $total) :
					$item = $this->items[$i];
					echo '<div class="span' . $spanNum . '" data-media-id="' . $item->id . '" data-media-value="' . htmlspecialchars($item->value, ENT_COMPAT, 'UTF-8') . '">';

					if ($srMedia->isImage($item->mime_type)) :
						echo '<img id="sr_media_' . $item->id . '" title="' . $item->name . '" alt="' . $item->name . '" src="' . $srMedia->getMediaUrl($item->value, 'asset_small') . '" />';
					elseif ($srMedia->isDocument($item->mime_type)) :
						echo '<img id="sr_media_' . $item->id . '" title="' . $item->name . '" alt="' . $item->name . '" src="' . SRURI_MEDIA . '/assets/images/document.png" />';
					elseif ($srMedia->isVideo($item->mime_type)) :
						echo '<img id="sr_media_' . $item->id . '" title="' . $item->name . '" alt="' . $item->name . '" src="' . SRURI_MEDIA . '/assets/images/video.png" />';
					endif;

					echo '<label><input class="media-checkbox" type="checkbox" name="media[]" value="' . $item->id . '" /> ' . substr($item->name, 0, 20) . '</label>';
				endif;

				echo '</div>';
			endfor;
		else :
			echo '<div class="alert alert-notice">' . JText::_('SR_SEARCH_FOUND_NOTHING') . '</div>';
		endif;
		?>

	</div>


	<input type="hidden" name="task" value="media.delete"/>
	<input type="hidden" name="format" value="json"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

