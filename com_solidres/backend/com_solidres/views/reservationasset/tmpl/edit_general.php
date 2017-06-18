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

$partnerName = $this->form->getValue('partner_name');
$address = array('address_1', 'address_2', 'city', 'postcode');
$geocoding_address = array();
foreach ($address as $add) :
	if ($this->form->getValue($add, '') != '' ) :
		$geocoding_address[] = $this->form->getValue($add);
	endif;
endforeach;
?>
<fieldset>
	<div class="control-group">
		<?php echo $this->form->getLabel('name'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('name'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('alias'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('alias'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('category_id'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('category_id'); ?>
		</div>
	</div>

	<?php echo $this->form->renderField('category_name'); ?>

	<div class="control-group">
		<?php echo $this->form->getLabel('partner_id'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('partner_id'); ?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('address_1'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('address_1'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('address_2'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('address_2'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('city'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('city'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('postcode'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('postcode'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('email'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('email'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('website'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('website'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('phone'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('phone'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('fax'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('fax'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('country_id'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('country_id'); ?>
		</div>
	</div>
	<div class="control-group">
		<?php echo $this->form->getLabel('geo_state_id'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('geo_state_id'); ?>
		</div>
	</div>

    <div class="control-group">
		<?php echo $this->form->getLabel('currency_id'); ?>
        <div class="controls">
			<?php echo $this->form->getInput('currency_id'); ?>
        </div>
    </div>

	<div class="control-group">
		<?php echo $this->form->getLabel('tax_id');?>
		<div class="controls">
			<?php echo $this->form->getInput('tax_id');?>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('booking_type');?>
		<div class="controls">
			<?php echo $this->form->getInput('booking_type');?>
		</div>
	</div>

	<div class="coordinates">
		<div class="control-group">
			<label></label>
			<div class="controls">
				<div class="map_canvas"></div>
				<input id="geocomplete" type="text" placeholder="" value="<?php echo implode(',', $geocoding_address) ?>" />
				<button class="btn" id="find" type="button"><?php echo JText::_('SR_GEOCODING_FIND') ?></button>
				<button class="btn" data-lat="<?php echo !empty($this->lat) ? $this->lat : '' ?>" data-lng="<?php echo !empty($this->lng)  ? $this->lng : '' ?>" id="update" type="button" style="display:none;"><?php echo JText::_('SR_GEOCODING_UPDATE') ?></button>
			</div>
		</div>

		<div class="control-group">
			<?php echo $this->form->getLabel('lat');?>
			<div class="controls">
				<?php echo $this->form->getInput('lat');?>
			</div>
		</div>

		<div class="control-group">
			<?php echo $this->form->getLabel('lng');?>
			<div class="controls">
				<?php echo $this->form->getInput('lng');?>
			</div>
		</div>
	</div>

	<div class="control-group">
		<?php echo $this->form->getLabel('description'); ?>
		<div class="controls">
			<?php echo $this->form->getInput('description'); ?>
		</div>
	</div>

</fieldset>