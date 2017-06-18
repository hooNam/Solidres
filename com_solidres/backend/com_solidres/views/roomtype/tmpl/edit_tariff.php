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

?>


<?php if (SRPlugin::isEnabled('user') && SRPlugin::isEnabled('complextariff')) : ?>
<div class="alert alert-info">
	<?php echo JText::_('SR_NOTICE_FOR_COMPLEX_TARIFF_PLUGIN') ?>
</div>

<iframe class="tariff-wrapper" src="index.php?option=com_solidres&view=tariff&layout=edit&tmpl=component&id=<?php echo $this->form->getValue('id') ?>&currency_id=<?php echo $this->form->getValue('currency')->id ?>&reservation_asset_id=<?php echo $this->form->getValue('reservation_asset_id') ?>#tariffs">
</iframe>

<?php else : ?>
	<div class="alert alert-info">
		This feature allows you to configure more flexible tariff, more info can be found <a target="_blank" href="http://www.solidres.com/features-highlights#feature-complextariff">here</a>.
	</div>

	<div class="alert alert-success">
		<strong>Notice:</strong> <strong>plugin Complex Tariff</strong> and <strong>plugin User</strong> are not installed or enabled. <a target="_blank" href="https://www.solidres.com/subscribe/levels">Become a subscriber and download them now.</a>
	</div>
<?php endif ?>