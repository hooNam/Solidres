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

JFactory::getDocument()->addScriptDeclaration('
	Solidres.jQuery(document).ready(function($) {
		$("#item-form").validate({onsubmit: false});
		Solidres.options.load({
			targetId: "' . (int) $this->form->getValue('id') . '",
			uriBase: "' . JUri::base(true) . '/",
			target: "roomtype",
			token: "' . JSession::getFormToken() . '"
		});
	});

	Joomla.submitbutton = function(task)
	{
		if (task != "roomtype.cancel") {
			if (Solidres.jQuery("input[name^=\"jform[rooms]\"]").length == 0) {
				alert("'. JText::_('SR_NO_ROOMS_CREATED') .'");
				return false;
			}
		}

		if (task == "roomtype.cancel" || Solidres.jQuery("#item-form").valid())
		{
			'. $this->form->getField('description')->save() .'
			Solidres.jQuery("#item-form").validate().resetForm();
			Joomla.submitform(task, document.getElementById("item-form"), false);
		} else {
			alert("' . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '");
		}
	}
');

?>
<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_form_view span10">
			<div class="sr-inner">
				<form enctype="multipart/form-data"
				      action="<?php JRoute::_('index.php?option=com_solidres&view=roomtypes'); ?>"
				      method="post"
				      name="adminForm"
				      id="item-form"
				      class="form-validate form-horizontal">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('SR_NEW_GENERAL_INFO')?></a></li>
						<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING')?></a></li>
						<li><a href="#tariff" data-toggle="tab"><?php echo JText::_('SR_ROOM_TYPE_TARIFF')?></a></li>
						<li><a href="#room" data-toggle="tab"><?php echo JText::_('SR_ROOM_TYPE_ROOM')?></a></li>
						<li><a href="#media" data-toggle="tab"><?php echo JText::_('SR_MEDIA')?></a></li>
						<li><a href="#customfields" data-toggle="tab"><?php echo JText::_('SR_CUSTOM_FIELDS')?></a></li>
						<li><a href="#facility" data-toggle="tab"><?php echo JText::_('SR_FACILITY')?></a></li>
						<?php if (SRPlugin::isEnabled('tripconnect')) : ?>
						<li><a href="#amenities" data-toggle="tab"><?php echo JText::_('SR_AMENITIES')?></a></li>
						<?php endif ?>
						<?php if (SRPlugin::isEnabled('channelmanager')) : ?>
							<li><a href="#channelmanager" data-toggle="tab"><?php echo JText::_('SR_CHANNEL_MANAGER')?></a></li>
						<?php endif ?>
						<li><a href="#ical" data-toggle="tab"><?php echo JText::_('SR_ICAL_LABEL'); ?></a></li>
						<li><a href="#stream" data-toggle="tab"><?php echo JText::_('SR_STREAM') ?></a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="general">
							<?php echo $this->loadTemplate('general'); ?>
						</div>

						<div class="tab-pane" id="publishing">
							<?php echo $this->loadTemplate('publishing'); ?>
							<?php echo $this->loadTemplate('params'); ?>
						</div>
						<div class="tab-pane" id="tariff">
							<?php echo $this->loadTemplate('tariff'); ?>
						</div>
						<div class="tab-pane" id="room">
							<?php echo $this->loadTemplate('room'); ?>
						</div>
						<div class="tab-pane" id="media">
							<?php echo $this->loadTemplate('media') ?>
						</div>
						<div class="tab-pane" id="customfields">
							<?php echo $this->loadTemplate('customfields') ?>
						</div>
						<div class="tab-pane" id="facility">
							<?php echo $this->loadTemplate('facility') ?>
						</div>
						<div class="tab-pane" id="amenities">
							<?php echo $this->loadTemplate('amenities') ?>
						</div>
						<?php if (SRPlugin::isEnabled('channelmanager')) : ?>
						<div class="tab-pane" id="channelmanager">
							<?php echo $this->loadTemplate('channelmanager') ?>
						</div>
						<?php endif ?>
						<div class="tab-pane" id="ical">
							<?php if (SRPlugin::isEnabled('ical')): ?>
								<?php foreach ($this->form->getFieldset('ical') as $field): ?>
									<?php echo $field->renderField(); ?>
								<?php endforeach; ?>
							<?php else: ?>
								<div class="alert alert-info">
									This feature allows you listen to all Solidres's events and record them
								</div>
								<div class="alert alert-success">
									<strong>Notice:</strong> plugin <strong>Ical</strong> is not installed or enabled.
									<a target="_blank"
									   href="https://www.solidres.com/subscribe/levels">Become
										a subscriber and download it now.</a>
								</div>
							<?php endif; ?>
						</div>
						<div class="tab-pane" id="stream">
							<?php if (SRPlugin::isEnabled('stream')): ?>
								<?php SolidresStreamHelper::displayByScope('roomtype', $this->form->getValue('id')); ?>
							<?php else: ?>
								<div class="alert alert-info">
									This feature allows you listen to all Solidres's events and record them
								</div>
								<div class="alert alert-success">
									<strong>Notice:</strong> plugin <strong>Stream</strong> is not installed or enabled.
									<a target="_blank"
									   href="https://www.solidres.com/subscribe/levels">Become
										a subscriber and download it now.</a>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<input type="hidden" name="task" value="" />
					<?php echo JHtml::_('form.token'); ?>
				</form>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>
	