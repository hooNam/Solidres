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

if (!isset($this->item->params['show_facilities'])) :
	$this->item->params['show_facilities'] = 1;
endif;

if (!isset($this->item->params['show_policies'])) :
	$this->item->params['show_policies'] = 1;
endif;

?>

<?php if ($this->item->params['show_facilities']) : ?>
<h3><?php echo JText::_('SR_CUSTOMFIELD_FACILITIES') ?></h3>

<?php if (isset($this->item->reservationasset_extra_fields['general'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
	<div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_GENERAL') ?></div>
	<div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['general']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['activities'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_ACTIVITIES') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['activities']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['services'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_SERVICES') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['services']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['internet'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_INTERNET') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['internet']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['parking'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_PARKING') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['parking']?></div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if ($this->item->params['show_policies']) : ?>
<h3><?php echo JText::_('SR_CUSTOMFIELD_POLICIES') ?></h3>

<?php if (isset($this->item->reservationasset_extra_fields['checkin_time'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_CHECKIN') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['checkin_time']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['checkout_time'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_CHECKOUT') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['checkout_time']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['cancellation_prepayment'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_CANCELLATION_PREPAYMENT') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['cancellation_prepayment']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['children_and_extra_beds'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_CHILDREN_EXTRA_BEDS') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['children_and_extra_beds']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['pets'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_PETS') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['pets']?></div>
</div>
<?php endif; ?>

<?php if (isset($this->item->reservationasset_extra_fields['accepted_credit_cards'])) : ?>
<div class="<?php echo SR_UI_GRID_CONTAINER ?> custom-field-row">
    <div class="<?php echo SR_UI_GRID_COL_2 ?> info-heading"><?php echo JText::_('SR_CUSTOMFIELD_ACCEPTED_CREDIT_CARDS') ?></div>
    <div class="<?php echo SR_UI_GRID_COL_10 ?>"><?php echo $this->item->reservationasset_extra_fields['accepted_credit_cards']?></div>
</div>
<?php endif; ?>
<?php endif; ?>
