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

if (!isset($this->item->params['only_show_reservation_form']))
{
	$this->item->params['only_show_reservation_form'] = 0;
}

$fbStars = '';
for ($i = 1; $i <= $this->item->rating; $i++) :
	$fbStars .= '&#x2605;';
endfor;

$this->document->addCustomTag('<meta property="og:title" content="'.$fbStars . ' ' . $this->item->name . ', ' . $this->item->city . ', ' . $this->item->country_name .'"/>');
$this->document->addCustomTag('<meta property="og:type" content="place"/>');
$this->document->addCustomTag('<meta property="og:url" content="'.JRoute::_('index.php?option=com_solidres&view=reservationasset&id='.$this->item->id, true, true).'"/>');
if (isset($this->item->media[0]))
{
	$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[0]->value . '"/>');
}

if (isset($this->item->media[1]))
{
	$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[1]->value . '"/>');
}

if (isset($this->item->media[2]))
{
	$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[2]->value . '"/>');
}

$this->document->addCustomTag('<meta property="og:site_name" content="'.JFactory::getConfig()->get( 'sitename' ).'"/>');
$this->document->addCustomTag('<meta property="og:description" content="'.strip_tags($this->item->description).'"/>');
$this->document->addCustomTag('<meta property="place:location:latitude"  content="'.$this->item->lat.'" />');
$this->document->addCustomTag('<meta property="place:location:longitude" content="'.$this->item->lng.'" /> ');
?>
	<div id="solidres" class="<?php echo SR_UI ?> reservation_asset_style">
		<div class="reservation_asset_item clearfix">
			<?php if ($this->item->params['only_show_reservation_form'] == 0 ) : ?>
			<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
				<div class="<?php echo SR_UI_GRID_COL_4 ?>">
					<div class="asset-info">
						<div class="asset-name">
							<h3><?php echo $this->escape($this->item->name); ?></h3>
						</div>
						<div class="asset-rating">
							<?php for ($i = 1; $i <= $this->item->rating; $i++) : ?>
								<i class="fa fa-star"></i>
							<?php endfor ?>
						</div>
						<div class="asset-address_1">
							<a class="show_map" href="<?php echo JRoute::_('index.php?option=com_solidres&task=map.show&id='.$this->item->id) ?>">
								<i class="fa fa-map-marker"></i>
								<?php
								echo $this->item->address_1 .', '.
									(!empty($this->item->city) ? $this->item->city.', ' : '').
									(!empty($this->item->postcode) ? $this->item->postcode.', ' : '').
									$this->item->country_name
								?>
							</a>
						</div>
						<div class="asset-address_2"><?php echo $this->item->address_2;?></div>

						<div class="asset-wish-list"><?php echo $this->events->afterDisplayAssetName; ?></div>

						<div class="asset-call-action">
							<a href="#form" class="btn btn-large btn-lg btn-block btn-primary" title="Reserve now">
								<?php echo JText::_('SR_BOOK_NOW');?>
							</a>
						</div>

						<div class="asset-contact">
							<p><i class="fa fa-envelope fa-fw" title="<?php echo JText::_('SR_EMAIL')?>"></i> <a href="mailto:<?php echo $this->item->email;?>"><?php echo $this->item->email;?></a></p>
							<p><i class="fa fa-phone-square fa-fw" title="<?php echo JText::_('SR_PHONE')?>"></i> <?php echo $this->item->phone;?></p>
							<p><i class="fa fa-fax fa-fw" title="<?php echo JText::_('SR_FAX')?>"></i> <?php echo $this->item->fax;?></p>
							<p><i class="fa fa-globe fa-fw" title="<?php echo JText::_('SR_WEBSITE')?>"></i> <a href="<?php echo $this->item->website;?>" target="_blank"><?php echo $this->item->website;?></a></p>
						</div>

						<div class="asset-social clearfix">
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['facebook_link'])
								&& $this->item->reservationasset_extra_fields['facebook_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['facebook_link'];?>" target="_blank"><i class="fa fa-facebook-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['twitter_link'])
								&& $this->item->reservationasset_extra_fields['twitter_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['twitter_link'];?>" target="_blank"><i class="fa fa-twitter-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['linkedin_link'])
								&& $this->item->reservationasset_extra_fields['linkedin_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['linkedin_link'];?>" target="_blank"><i class="fa fa-linkedin-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['gplus_link'])
								&& $this->item->reservationasset_extra_fields['gplus_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['gplus_link'];?>" target="_blank"><i class="fa fa-google-plus-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['tumblr_link'])
								&& $this->item->reservationasset_extra_fields['tumblr_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['tumblr_link'];?>" target="_blank"><i class="fa fa-tumblr-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['foursquare_link'])
								&& $this->item->reservationasset_extra_fields['foursquare_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['foursquare_link'];?>" target="_blank"><i class="fa fa-foursquare"></i> </a>
							<?php	endif;
							?>

							<?php
							if ( !empty($this->item->reservationasset_extra_fields['pinterest_link'])
								&& $this->item->reservationasset_extra_fields['pinterest_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['pinterest_link'];?>" target="_blank"><i class="fa fa-pinterest-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['slideshare_link'])
								&& $this->item->reservationasset_extra_fields['slideshare_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['slideshare_link'];?>" target="_blank"><i class="fa fa-slideshare"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['vimeo_link'])
								&& $this->item->reservationasset_extra_fields['vimeo_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['vimeo_link'];?>" target="_blank"><i class="fa fa-vimeo-square"></i> </a>
							<?php	endif;
							?>
							<?php
							if ( !empty($this->item->reservationasset_extra_fields['youtube_link'])
								&& $this->item->reservationasset_extra_fields['youtube_show']== 1) : ?>
								<a href="<?php echo $this->item->reservationasset_extra_fields['youtube_link'];?>" target="_blank"> <i class="fa fa-youtube-square"></i> </a>
							<?php	endif;
							?>
						</div>
					</div>
				</div>
				<div class="<?php echo SR_UI_GRID_COL_8 ?>">
					<div class="asset-gallery">
						<?php echo $this->defaultGallery; ?>
					</div>
				</div>
			</div>

			<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
				<div class="<?php echo SR_UI_GRID_COL_12 ?>">
					<div class="asset-tabs">
						<?php
						$tabTitle = array();
						$tabPane = array();

						if (!empty($this->item->description)) :
							$tabTitle[] = '<li class="active"><a href="#asset-desc" data-toggle="tab">'.JText::_('SR_DESCRIPTION').'</a></li>';
							$tabPane[] = '<div class="tab-pane active" id="asset-desc">'.$this->item->text.'</div>';
						endif;

						if (isset($this->item->feedbacks->render) && !empty($this->item->feedbacks->render)) :
							$activeClass = empty($tabTitle) ? 'active' : '';
							$tabTitle[] = '<li class="'.$activeClass.'"><a href="#asset-feedbacks" data-toggle="tab">'.JText::_('SR_RESERVATION_FEEDBACKS').'</a></li>';
							$tabPane[] = '<div class="tab-pane '.$activeClass.'" id="asset-feedbacks">'.$this->item->feedbacks->render.'</div>';
							$tabTitle[] = '<li><a href="#asset-feedback-scores" data-toggle="tab">'.JText::_('SR_FEEDBACK_SCORES').'</a></li>';
							$tabPane[] = '<div class="tab-pane" id="asset-feedback-scores">'.$this->item->feedbacks->scores.'</div>';
						endif;

						?>

						<?php if (!empty($tabTitle)) : ?>
						<ul class="nav nav-tabs">
							<?php echo join("\n", $tabTitle); ?>
						</ul>
						<?php endif ?>

						<?php if (!empty($tabPane)) : ?>
						<div class="tab-content">
							<?php echo join("\n", $tabPane); ?>
						</div>
						<?php endif ?>
					</div>
				</div>
			</div>
			<?php endif ?>

			<?php echo $this->events->beforeDisplayAssetForm; ?>		
			<?php if ( SRPlugin::isEnabled('user') && $this->showLoginBox ) : ?>
			<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
				<div class="<?php echo SR_UI_GRID_COL_12 ?>">
					<div class="alert alert-info sr-login-form">
						<?php
						if (!JFactory::getUser()->get('id')) :
							echo $this->loadTemplate('login');
						else:
							echo $this->loadTemplate('userinfo');
						endif;
						?>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
				<div class="<?php echo SR_UI_GRID_COL_12 ?>">
					<?php echo $this->loadTemplate('roomtype' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : '')); ?>
				</div>
			</div>

			<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
				<div class="<?php echo SR_UI_GRID_COL_12 ?>">
					<?php echo $this->loadTemplate('information' . ((defined('SR_LAYOUT_STYLE') && SR_LAYOUT_STYLE != '') ? '_' . SR_LAYOUT_STYLE : '')); ?>
				</div>
            </div>

			<?php echo $this->events->afterDisplayAssetForm; ?>
			<?php if ($this->showPoweredByLink) : ?>
			<div class="<?php echo SR_UI_GRID_CONTAINER ?>">
				<div class="<?php echo SR_UI_GRID_COL_12 ?>">
					<p class="powered">
						Powered by <a target="_blank" title="Solidres - A hotel booking extension for Joomla & WordPress" href="http://www.solidres.com">Solidres</a>
					</p>
				</div>
			</div>
			<?php endif ?>
		</div>
	</div>