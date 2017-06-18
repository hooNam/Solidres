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

JHtml::_('script', 'jui/cms.js', false, true);

JFactory::getDocument()->addScriptDeclaration('
	Solidres.jQuery(document).ready(function($) {
		$("#item-form").validate({onsubmit: false});
		Solidres.options.load({
			targetId: "' . (int) $this->form->getValue('id') . '",
			uriBase: "' . JUri::base(true) . '/",
			target: "reservation_assets",
			token: "' . JSession::getFormToken() . '"
		});
	});

	Joomla.submitbutton = function(task)
	{
		if (task == "reservationasset.cancel" || jQuery("#item-form").valid())
		{
			'. $this->form->getField('description')->save() .'
			Solidres.jQuery("#item-form").validate().resetForm();
			Joomla.submitform(task, document.getElementById("item-form"), false);
		} else {
			alert("' . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '");
		}
	}
');

$plugins = $this->form->getFieldsets('plugins');
?>

<div id="solidres">
    <div class="row-fluid">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_form_view span10">
			<div class="sr-inner">
				<form enctype="multipart/form-data"
				      action="<?php JRoute::_('index.php?option=com_solidres&view=reservationassets'); ?>" method="post"
				      name="adminForm" id="item-form" class="form-horizontal">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('SR_NEW_GENERAL_INFO')?></a></li>
						<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING')?></a></li>
						<li><a href="#roomtype" data-toggle="tab"><?php echo JText::_('SR_ASSET_ROOM_TYPE')?></a></li>
						<li><a href="#media" data-toggle="tab"><?php echo JText::_('SR_MEDIA')?></a></li>
						<li><a href="#extra" data-toggle="tab"><?php echo JText::_('SR_ASSET_EXTRA')?></a></li>
						<li><a href="#customfields" data-toggle="tab"><?php echo JText::_('SR_CUSTOM_FIELDS')?></a></li>
						<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('SR_METADATA')?></a></li>
                        <li><a href="#payment" data-toggle="tab"><?php echo JText::_('SR_PAYMENTMETHODS')?></a></li>
						<li><a href="#facility" data-toggle="tab"><?php echo JText::_('SR_FACILITY')?></a></li>
						<li><a href="#theme" data-toggle="tab"><?php echo JText::_('SR_THEME')?></a></li>
						<?php if (SRPlugin::isEnabled('tripconnect')) : ?>
						<li><a href="#amenities" data-toggle="tab"><?php echo JText::_('SR_AMENITIES')?></a></li>
						<?php endif ?>
						<?php if (SRPlugin::isEnabled('channelmanager')) : ?>
						<li><a href="#channelmanager" data-toggle="tab"><?php echo JText::_('SR_CHANNEL_MANAGER')?></a></li>
						<?php endif ?>
						<?php if (count($plugins)): ?>
							<li><a href="#plugins" data-toggle="tab"><?php echo JText::_('SR_PLUGINS'); ?></a></li>
						<?php endif; ?>
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
						<div class="tab-pane" id="roomtype">
							<?php echo $this->loadTemplate('roomtype'); ?>
						</div>
						<div class="tab-pane" id="media">
							<?php echo $this->loadTemplate('media'); ?>
						</div>
						<div class="tab-pane" id="extra">
							<?php echo $this->loadTemplate('extra')?>
						</div>
						<div class="tab-pane" id="customfields">
							<?php echo $this->loadTemplate('customfields') ?>
						</div>
						<div class="tab-pane" id="metadata">
							<?php echo $this->loadTemplate('metadata') ?>
						</div>
                        <div class="tab-pane" id="payment">
							<?php echo $this->loadTemplate('payments') ?>
                        </div>
						<div class="tab-pane" id="facility">
							<?php echo $this->loadTemplate('facility') ?>
						</div>
						<div class="tab-pane" id="theme">
							<?php echo $this->loadTemplate('theme') ?>
						</div>
						<div class="tab-pane" id="amenities">
							<?php echo $this->loadTemplate('amenities') ?>
						</div>
						<div class="tab-pane" id="channelmanager">
							<?php echo $this->loadTemplate('channelmanager') ?>
						</div>
						<?php if (count($plugins)): ?>
							<div class="tab-pane" id="plugins">
								<div class="tab-pane" id="plugins">
									<?php
									echo JHtml::_('bootstrap.startAccordion', 'plugin-collapse', array('active' => 'plugin-0'));
									$i = 0;
									foreach ($plugins as $name => $fieldSet)
									{
										echo JHtml::_('bootstrap.addSlide', 'plugin-collapse', JText::_($fieldSet->label), 'collapse-' . $i++);
										foreach ($this->form->getFieldset($name) as $field)
										{
											echo $field->renderField();
										}
										echo JHtml::_('bootstrap.endSlide');
									}
									echo JHtml::_('bootstrap.endAccordion'); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="tab-pane" id="stream">
							<?php if (SRPlugin::isEnabled('stream')): ?>
								<?php SolidresStreamHelper::displayByScope('reservationasset', $this->form->getValue('id')); ?>
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