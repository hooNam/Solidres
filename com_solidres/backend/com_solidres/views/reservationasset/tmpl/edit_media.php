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
$media = $this->form->getValue('media');
?>
<?php if(isset($media) && count($media) > 0) : ?>
	<div class="alert alert-info">
		<i class="icon-lamp"></i> <?php echo JText::_('SR_CHECK_SAVE_DELETE_INFO') ?>
	</div>
<?php else :  ?>
	<div class="alert alert-info">
		<i class="icon-lamp"></i> <?php echo JText::_('SR_NO_MEDIA_FOUND') ?>
	</div>
<?php endif; ?>

<fieldset class="adminform" id="mediafset">
    <ul id="media-holder" class="media-container media-sortable">
	<?php
        if(isset($media)) :
            foreach($media as $item) :
	?>
		<li data-order="<?php echo $item->weight ?>">
            <input type="hidden" name="jform[mediaId][]" value="<?php echo $item->id ?>">
			<img title="<?php echo $item->name ?>"
				 alt="<?php echo $item->name ?>"
				 id="sr_media_<?php echo $item->id ?>"
				 src="<?php echo $this->solidresMedia->getMediaUrl($item->value, 'asset_small')  ?>" />
			<?php echo $item->name ?>
			<p>
				<button type="button" class="btn-remove btn btn-danger btn-mini">
					<i class="fa fa-trash"></i>
				</button>
			</p>
		</li>
	<?php
            endforeach;
        endif;
	?>
    </ul>
</fieldset>