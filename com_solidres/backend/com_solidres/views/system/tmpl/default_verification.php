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
JLoader::import('joomla.filesystem.file');
$files = array(
	'com_solidres' => JPATH_COMPONENT_ADMINISTRATOR . '/checksums'
);
$plugins = array(
	'plg_solidres_acl'                 => JPATH_PLUGINS . '/solidres/acl/checksums',
	'plg_solidres_advancedextra'       => JPATH_PLUGINS . '/solidres/advancedextra/checksums',
	'plg_solidres_camera_slideshow'    => JPATH_PLUGINS . '/solidres/camera_slideshow/checksums',
	'plg_solidres_complextariff'       => JPATH_PLUGINS . '/solidres/complextariff/checksums',
	'plg_solidres_currency'            => JPATH_PLUGINS . '/solidres/currency/checksums',
	'plg_solidres_customfield'         => JPATH_PLUGINS . '/solidres/customfield/checksums',
	'plg_solidres_discount'            => JPATH_PLUGINS . '/solidres/discount/checksums',
	'plg_solidres_experience'          => JPATH_PLUGINS . '/solidres/experience/checksums',
	'plg_solidres_feedback'            => JPATH_PLUGINS . '/solidres/feedback/checksums',
	'plg_solidres_facebook'            => JPATH_PLUGINS . '/solidres/facebook/checksums',
	'plg_solidres_googleanalytics'     => JPATH_PLUGINS . '/solidres/googleanalytics/checksums',
	'plg_solidres_googleadwords'       => JPATH_PLUGINS . '/solidres/googleadwords/checksums',
	'plg_solidres_hub'                 => JPATH_PLUGINS . '/solidres/hub/checksums',
	'plg_solidres_ical'                => JPATH_PLUGINS . '/solidres/ical/checksums',
	'plg_solidres_invoice'             => JPATH_PLUGINS . '/solidres/invoice/checksums',
	'plg_solidres_limitbooking'        => JPATH_PLUGINS . '/solidres/limitbooking/checksums',
	'plg_solidres_loadmodule'          => JPATH_PLUGINS . '/solidres/loadmodule/checksums',
	'plg_solidres_rescode'             => JPATH_PLUGINS . '/solidres/rescode/checksums',
	'plg_solidres_simple_gallery'      => JPATH_PLUGINS . '/solidres/simple_gallery/checksums',
	'plg_solidres_sms'                 => JPATH_PLUGINS . '/solidres/sms/checksums',
	'plg_solidres_statistics'          => JPATH_PLUGINS . '/solidres/statistics/checksums',
	'plg_solidres_stream'              => JPATH_PLUGINS . '/solidres/stream/checksums',
	'plg_solidrespayment_eway'         => JPATH_PLUGINS . '/solidrespayment/eway/checksums',
	'plg_solidrespayment_paypal'       => JPATH_PLUGINS . '/solidrespayment/paypal/checksums',
	'plg_solidrespayment_paypal_pro'   => JPATH_PLUGINS . '/solidrespayment/paypal_pro/checksums',
	'plg_solidrespayment_cielo'        => JPATH_PLUGINS . '/solidrespayment/cielo/checksums',
	'plg_solidrespayment_authorizenet' => JPATH_PLUGINS . '/solidrespayment/authorizenet/checksums',
	'plg_solidrespayment_atlantic'     => JPATH_PLUGINS . '/solidrespayment/atlantic/checksums',
	'plg_solidrespayment_unionpay'     => JPATH_PLUGINS . '/solidrespayment/unionpay/checksums',
	'plg_solidrespayment_offline'      => JPATH_PLUGINS . '/solidrespayment/offline/checksums',
	'plg_solidrespayment_cimb'         => JPATH_PLUGINS . '/solidrespayment/cimb/checksums',
	'plg_solidrespayment_mercadopago'  => JPATH_PLUGINS . '/solidrespayment/mercadopago/checksums',
	'plg_solidrespayment_postfinance'  => JPATH_PLUGINS . '/solidrespayment/postfinance/checksums',
	'plg_solidrespayment_ghl'          => JPATH_PLUGINS . '/solidrespayment/ghl/checksums',
	'plg_solidrespayment_mollie'       => JPATH_PLUGINS . '/solidrespayment/mollie/checksums',
	'plg_solidrespayment_payfast'      => JPATH_PLUGINS . '/solidrespayment/payfast/checksums',
	'plg_solidrespayment_stripe'       => JPATH_PLUGINS . '/solidrespayment/stripe/checksums',
	'plg_user_solidres'                => JPATH_PLUGINS . '/user/solidres/checksums'
);
foreach ($plugins as $key => $path)
{
	list($prefix, $group, $name) = explode('_', $key, 3);
	if (JPluginHelper::isEnabled($group, $name))
	{
		$files[$key] = $path;
	}
}
foreach ($this->solidresModules as $module)
{
	if (JFile::exists(JPATH_ROOT . '/modules/' . $module . '/' . $module . '.php'))
	{
		$files[$module] = JPATH_ROOT . '/modules/' . $module . '/checksums';
	}
}
?>
<div style="clear: both"></div>
<h3>
	<?php echo JText::_('SR_FILE_VERIFICATION'); ?>
	<button type="button" class="btn btn-primary btn-small" id="file-check-verification">
		<i class="icon-cogs"></i> <?php echo JText::_('SR_FILE_VERIFICATION_CHECK'); ?>
	</button>
	<img src="<?php echo SRURI_MEDIA.'/assets/images/ajax-loader2.gif'; ?>" alt="Loading..." id="ajax-loader" class="hide"/>
</h3>

<div id="file-verification">

</div>
<script>
	Solidres.jQuery(document).ready(function ($) {
		$('#file-check-verification').on('click', function () {
			$('#ajax-loader').removeClass('hide');
			$.ajax({
				url: '<?php echo JRoute::_('index.php?option=com_solidres&task=system.checkVerification', false); ?>',
				type: 'post',
				dataType: 'json',
				data: {
					'files': '<?php echo base64_encode(serialize($files)); ?>',
					'<?php echo JSession::getFormToken(); ?>': 1
				},
				success: function (response) {
					var _packages = response.data, html = '';
					for (_package in _packages) {
						var hasChange = _packages[_package].removed.length > 0 || _packages[_package].modified.length > 0 || _packages[_package].new.length > 0;
						if (hasChange) {
							html += '<div class="well"><h4 class="label label-info">' + _package.toUpperCase() + '</h4>';
							if (_packages[_package].removed.length > 0) {
								html += '<h5 class="text-error"><?php echo strtoupper(JText::_('SR_FILE_VERIFICATION_REMOVED')); ?></h5>';
								for (var i = 0, n = _packages[_package].removed.length; i < n; i++) {
									html += '<div class="text-error"><i class="icon-file"></i>' + _packages[_package].removed[i] + '</div>';
								}
							}
							if (_packages[_package].modified.length > 0) {
								html += '<h5 class="text-warning"><?php echo strtoupper(JText::_('SR_FILE_VERIFICATION_MODIFIED')); ?></h5>';
								for (var i = 0, n = _packages[_package].modified.length; i < n; i++) {
									html += '<div class="text-warning"><i class="icon-file"></i>' + _packages[_package].modified[i] + '</div>';
								}
							}
							if (_packages[_package].new.length > 0) {
								html += '<h5 class="text-success"><?php echo strtoupper(JText::_('SR_FILE_VERIFICATION_NEW')); ?></h5>';
								for (var i = 0, n = _packages[_package].new.length; i < n; i++) {
									html += '<div class="text-success"><i class="icon-file"></i>' + _packages[_package].new[i] + '</div>';
								}
							}
							html += '</div>';
						}
					}
					$('#ajax-loader').addClass('hide');
					$('#file-verification').html(html);
				}
			});
			
			var el = $(this);
			$('html, body').animate({
				scrollTop: el.offset().top
			}, 800);
		});
	});
</script>