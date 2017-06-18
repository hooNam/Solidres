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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
$user            = JFactory::getUser();
$userId          = $user->get('id');
$solidresPlugins = $this->solidresPlugins;
$solidresModules = $this->solidresModules;
$phpSettings     = array();
$config          = JFactory::getConfig();

JFactory::getDocument()->addScriptDeclaration('
	Solidres.jQuery(document).ready(function($){
		$("button[data-extension_id]").on("click", function(){
			var el = $(this), icon = el.find(".fa"), originIcon = icon.attr("class");
			icon.attr("class", "fa fa-spin fa-spinner");
			$.ajax({
				url: "' . JRoute::_('index.php?option=com_solidres&task=system.togglePluginState', false) . '",
				type: "post",
				dataType: "json",
				data: {
					extension_id: parseInt(el.data("extension_id")),
					"' . JSession::getFormToken() . '" : 1
				},
				success: function(data){
					icon.attr("class", originIcon);
					if(data.enabled !== "NULL"){
						if(data.enabled){
							el.prev(".label").removeClass("label-warning").addClass("label-success");
							icon.removeClass("fa-times-circle text-error").addClass("fa-check-circle text-success");
						}else{
							el.prev(".label").removeClass("label-success").addClass("label-warning");
							icon.removeClass("fa-check-circle text-success").addClass("fa-times-circle text-error");
						}
					}
				}
			});
		});
	});
');
?>

<div id="solidres">
    <div class="row-fluid system-info-page">
		<?php echo SolidresHelperSideNavigation::getSideNavigation($this->getName()); ?>
		<div id="sr_panel_right" class="sr_list_view span10">
			<div class="row-fluid">
				<div class="span4">
					<img src="<?php echo JUri::root() ?>/media/com_solidres/assets/images/logo425x90.png"
					     alt="Solidres Logo" class="" />
				</div>
				<div class="span8">
					<div class="alert alert-success">
						Version <?php echo SRVersion::getShortVersion() . ' ' .
						(isset($this->updates['com_solidres']) && version_compare(SRVersion::getBaseVersion(), $this->updates['com_solidres'], 'lt') ? '<a title="New update (v' . $this->updates['com_solidres'] . ') is available" href="https://www.solidres.com/download/show-all-downloads/solidres" target="_blank">[New update (v' . $this->updates['com_solidres'] . ') is available.]</a>' : '') ?>
					</div>
					<div class="alert alert-info">
						If you use Solidres, please post a rating and a review at the
						<a href="http://extensions.joomla.org/extensions/vertical-markets/booking-a-reservations/booking/23594" target="_blank">
							Joomla! Extensions Directory
						</a>
					</div>
				</div>
			</div>

			<?php echo $this->loadTemplate('installsampledata'); ?>

			<?php if (!empty($this->solidresTemplates)): ?>
				<div class="row-fluid">
					<div class="span6">
						<h3>Templates status</h3>
						<table class="table table-condensed table-striped system-table">
							<tbody>
							<?php foreach ($this->solidresTemplates as $template): ?>
								<tr>
									<td>
										<a href="<?php echo JRoute::_('index.php?option=com_templates&view=style&layout=edit&id=' . $template->id, false); ?>"
										   target="_blank">
											<?php echo $template->title; ?>
										</a>
									</td>
									<td>
										<span class="label label-success">
											Version <?php echo $template->manifest->version; ?> is enabled
										</span>
										<i class="fa fa-check-circle text-success"></i>
										<?php if (isset($this->updates['tpl_' . $template->template])
											&& version_compare($template->manifest->version, $this->updates['tpl_' . $template->template], 'lt')
										): ?>
											<span class="new-update">
												<?php echo JText::plural('SR_UPDATE_AVAILABLE_PLURAL', 'https://www.solidres.com/download/show-all-downloads', $this->updates['tpl_' . $template->template]); ?>
											</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			<?php endif; ?>

			<div class="row-fluid">
				<div class="span12">
					<h3>Plugins status</h3>

					<div class="row-fluid">
					<?php
					$breakingP = 1;
					$pluginTotal = 44;
					foreach ($solidresPlugins as $group => $plugins) :
						foreach ($plugins as $plugin) :
							if ( 1 == $breakingP || round($pluginTotal / 2) + 1 == $breakingP) :
								echo '<div class="span6"><table class="table table-condensed table-striped system-table"><tbody>';
							endif;
							$pluginKey = 'plg_'.$group.'_'.$plugin;
							$extTable = JTable::getInstance('Extension');
							$extTable->load(array('name' => $pluginKey));
							$isInstalled = false;
							$url         = JRoute::_('index.php?option=com_plugins&filter_folder=' . $group);
							$isFree      = in_array($pluginKey, array('plg_content_solidres', 'plg_extension_solidres', 'plg_system_solidres', 'plg_solidres_simple_gallery'));

							if ($extTable->extension_id > 0) :
								$isInstalled = true;
								$url         = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extTable->extension_id);
							endif;
								?>
								<tr>
									<td>
										<a href="<?php echo $url; ?>">
											<?php echo $pluginKey ?>
										</a>
										<?php echo $isFree ? '<span class="label label-info">Free</span>' : '' ?>
									</td>
									<td>
										<?php
										if ($isInstalled)
										{
											$pluginInfo = json_decode($extTable->manifest_cache);
											$isEnabled  = (bool) $extTable->get('enabled');
											echo $isEnabled ? '<span class="label label-success">Version ' . $pluginInfo->version . ' is enabled</span>' : '<span class="label label-warning">Version ' . $pluginInfo->version . ' is not enabled</span>';
											echo '<button type="button" class="btn btn-link btn-small" data-extension_id="' . $extTable->extension_id . '"><i class="fa fa-' . ($isEnabled ? 'check-circle text-success' : 'times-circle text-error') . '" style="outline:none"></i></button>';
											if (isset($this->updates[$pluginKey])
												&& version_compare($this->updates[$pluginKey], $pluginInfo->version, 'gt')
											)
											{
												echo '<span class="new-update">'.JText::plural('SR_UPDATE_AVAILABLE_PLURAL', 'https://www.solidres.com/download/show-all-downloads', $this->updates[$pluginKey]).'</span>';
											}
										}
										else
										{
											echo '<span class="label label-important">Not installed</span>';
										}
										?>
									</td>
								</tr>
					<?php
							if ( (round($pluginTotal / 2)) == $breakingP || $pluginTotal == $breakingP) :
								echo '</tbody></table></div>';
							endif;
							$breakingP ++;
						endforeach;
					endforeach ?>
					</div>

					<h3>Modules status</h3>

					<div class="row-fluid">
						<?php
						$breakingP = 1;
						$moduleTotal = 18;
						foreach ($solidresModules as $module) :
							if ( 1 == $breakingP || round($moduleTotal / 2) + 1 == $breakingP) :
								echo '<div class="span6"><table class="table table-condensed table-striped system-table"><tbody>';
							endif;
								$extTable = JTable::getInstance('Extension');
								$extTable->load(array('name' => $module));
								$isInstalled = false;
								if ($extTable->extension_id > 0) :
									$isInstalled = true;
								endif;
								$isFree = in_array($module, array('mod_sr_checkavailability', 'mod_sr_currency'));
								?>
								<tr>
									<td>
										<a href="<?php echo JRoute::_('index.php?option=com_modules&filter_module=' . $module) ?>">
										<?php echo $module ?>
										</a>
										<?php echo $isFree ? '<span class="label label-info">Free</span>' : '' ?>
									</td>
									<td>
										<?php
										if ($isInstalled) :
											$moduleInfo = json_decode($extTable->manifest_cache);
											echo '<span class="label label-success">Version '.$moduleInfo->version.' is installed</span>';
										else :
											echo '<span class="label label-important">Not installed</span>';
										endif;

										if (isset($this->updates[$module])
											&& version_compare($this->updates[$module], $moduleInfo->version, 'gt')
										)
										{
											echo ' <span class="new-update">'.JText::plural('SR_UPDATE_AVAILABLE_PLURAL', 'https://www.solidres.com/download/show-all-downloads', $this->updates[$module]).'</span>';
										}
										?>

									</td>
								</tr>
							<?php
							if ( (round($moduleTotal / 2)) == $breakingP || $moduleTotal == $breakingP) :
								echo '</tbody></table></div>';
							endif;
							$breakingP ++;
						endforeach;
						?>
					</div>

					<h3>System check list</h3>

					<table class="table table-condensed table-striped system-table">
						<thead>
						<tr>
							<th>
								Setting name
							</th>
							<th>
								Status
							</th>
						</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									PHP version is greater than 5.3.10 (PHP 5.6+ is highly recommended)
								</td>
								<td>
									<?php
									if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '>=')) :
										echo '<span class="label label-success">YES</span>';
									else :
										echo '<span class="label label-warning">NO</span>';
									endif;
									?>
								</td>
							</tr>
							<tr>
								<td>
									curl is enabled in your server
								</td>
								<td>
									<?php
									if (extension_loaded('curl') && function_exists('curl_version')) :
										echo '<span class="label label-success">YES</span>';
									else :
										echo '<span class="label label-warning">NO</span>';
									endif;
									?>
								</td>
							</tr>
							<tr>
								<td>
									GD is enabled in your server
								</td>
								<td>
									<?php
									if (extension_loaded('gd') && function_exists('gd_info')) :
										echo '<span class="label label-success">YES</span>';
									else :
										echo '<span class="label label-warning">NO</span>';
									endif;
									?>
								</td>
							</tr>
							<tr>
								<td>
									/media/com_solidres/assets/images/system/thumbnails is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/1')
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<tr>
								<td>
									/media/com_solidres/assets/images/system/thumbnails/1 is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/1')
									? '<span class="label label-success">YES</span>'
									: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<tr>
								<td>
									/media/com_solidres/assets/images/system/thumbnails/2 is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/2')
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $config->get('log_path') ?> is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/2')
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo $config->get('tmp_path') ?> is writable?
								</td>
								<td>
									<?php
									echo is_writable(JPATH_SITE . '/media/com_solidres/assets/images/system/thumbnails/2')
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<?php if (function_exists('apache_get_modules')) : ?>
							<tr>
								<td>
									(Optional) Is Apache mod_deflate is enabled? (this Apache module is needed if you want to use compression feature)
								</td>
								<td>
									<?php
									$apacheModules = apache_get_modules();
									echo in_array('mod_deflate', $apacheModules)
										? '<span class="label label-success">YES</span>'
										: '<span class="label label-warning">NO</span>';
									?>
								</td>
							</tr>
							<?php endif ?>

							<?php if (function_exists('curl_version')) : ?>
								<tr>
									<td>
										(Optional) Does my server support <a href="https://www.paypal-knowledge.com/infocenter/index?page=content&id=FAQ1914&expand=true&locale=en_US" target="_blank">the new PayPal's protocols</a> (TLS 1.2 and HTTP1.1)? If you don't use PayPal, just skip it.
									</td>
									<td>
										<?php
										$ch = curl_init();
										curl_setopt($ch, CURLOPT_URL, "https://tlstest.paypal.com/");
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										$result = curl_exec($ch);
										echo $result == 'PayPal_Connection_OK'
											? '<span class="label label-success">YES</span>'
											: '<span class="label label-warning">NO</span>';
										curl_close($ch);
										?>
									</td>
								</tr>
							<?php endif ?>
						</tbody>
					</table>

					<?php if (extension_loaded('gd') && function_exists('gd_info')): ?>
						<?php echo $this->loadTemplate('regeneratethumbnails'); ?>
					<?php endif; ?>

					<h3>Database check list <a
							href="<?php echo JRoute::_('index.php?option=com_solidres&task=system.databaseFix&' . JSession::getFormToken() . '=1', false); ?>"
							class="btn btn-small btn-primary"><span class="icon-refresh"></span> Fix schema</a></h3>

					<table class="table table-condensed system-table">
						<thead>
						<tr>
							<th>
								Setting name
							</th>
							<th>
								Status
							</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>
								Current Solidres database schema version
							</td>
							<td>
								<?php
								$dbo = JFactory::getDbo();
								$query = $dbo->getQuery(true);
								$query->select('version_id')
									->from($dbo->quoteName('#__schemas'))
									->where($dbo->quoteName('extension_id') . ' = (SELECT extension_id FROM '.$dbo->quoteName('#__extensions').' WHERE element = '.$dbo->quote('com_solidres').')');

								$dbo->setQuery($query);

								$schemaVersion = $dbo->loadResult();
								if (!empty($schemaVersion)) :
									echo '<span class="label label-success">' . $schemaVersion . '</span> Your database is in good state.';
								else :
									echo '<span class="label label-warning">No version found</span> If you are using Solidres pre-installed in some template\'s quickstart package, your quickstart package database could have missing entries which leads to this issue. You should contact them so that they can fix it for you. More info can be found in our <a href="http://www.solidres.com/support/frequently-asked-questions">FAQ - #30</a>';
								endif;
								?>
							</td>
						</tr>
						</tbody>
					</table>

					<h3>Template override check list</h3>

					<?php
					$frontendTemplateNames = JFolder::folders(JPATH_ROOT . '/templates/');
					$overrideCandidates = array_merge(array('com_solidres', 'layouts/com_solidres'), $solidresModules);
					$overridePaths = array();

					foreach ($frontendTemplateNames as $frontendTemplateName) :
						foreach ($overrideCandidates as $candidate) :
							$candidatePath = JPATH_ROOT . '/templates/' . $frontendTemplateName . '/html/' . $candidate;
							if (JFolder::exists($candidatePath)) :
								$overridePaths[$frontendTemplateName][] = $candidatePath;
							endif;
						endforeach;
					endforeach;

					if (!empty($overridePaths)) :
						echo '<p><button type="button" id="off-tmpl-override" class="btn btn-small btn-primary"><i class="fa fa-cog"></i> Disable all template overrides</button></p>';
						echo '<div class="alert alert-info">You are having the following template overrides for Solidres, note that out of date template overrides often cause Solidres not working correctly. If you encounter any issues, especially front end issues, you need to rename or delete those folders first. Always ask your template providers to keep those template overrides up to date with latest Solidres versions.</div>';
						echo JHtml::_('bootstrap.startAccordion', 'plugin-collapse', array('active' => 'plugin-0'));
						$slideIdx = 0;
						foreach ($overridePaths as $templateName => $templateOverridePaths) :
							echo JHtml::_('bootstrap.addSlide', 'plugin-collapse', $templateName, 'collapse-template-' . $slideIdx++);
							foreach ($templateOverridePaths as $templateOverridePath) :
								echo '<p>' . $templateOverridePath . '</p>';
							endforeach;
							echo JHtml::_('bootstrap.endSlide');
						endforeach;
					echo JHtml::_('bootstrap.endAccordion');
					else :
						echo '<div class="alert alert-info">You have no template override for Solidres.</div>';
					endif;
					?>

					<?php if (!empty($overridePaths)): ?>
						<script language="javascript">
							Solidres.jQuery(document).ready(function ($) {
								var
									button = $('#off-tmpl-override'),
									icon = button.children('.fa');
								button.on('click', function () {
									icon.addClass('fa-spin');
									$.ajax({
										url: '<?php echo JRoute::_('index.php?option=com_solidres&task=system.renameOverrideFiles', false); ?>',
										type: 'post',
										data: {
											'<?php echo JSession::getFormToken(); ?>': 1
										},
										success: function (response) {
											icon.removeClass('fa-spin');
											if (response == 'Success') {
												location.reload();
											} else {
												var message = $('<div class="alert alert-error"/>').text(response);
												button.after(message);
												setTimeout(function () {
													message.remove();
												}, 2500);
											}
										}
									});
								});
							});
						</script>
					<?php endif; ?>

					<h3>Important Paths</h3>

					<?php
					echo JHtml::_('bootstrap.startAccordion', 'plugin-collapse', array('active' => 'plugin-0'));

					echo JHtml::_('bootstrap.addSlide', 'plugin-collapse', 'Language files', 'collapse-0');
					$backendLanguageFiles = JFolder::files(JPATH_ROOT . '/administrator/components/com_solidres/language', '.', true, true);
					$frontendLanguageFiles = JFolder::files(JPATH_ROOT . '/components/com_solidres/language', '.', true, true);
					$languageFiles = array_merge($backendLanguageFiles, $frontendLanguageFiles);
					foreach ($languageFiles as $languageFile) :
					echo '<p>' . $languageFile . '</p>';
					endforeach;
					echo JHtml::_('bootstrap.endSlide');

					echo JHtml::_('bootstrap.addSlide', 'plugin-collapse', 'Email templates', 'collapse-1');
					echo '<p>'. JPATH_ROOT . '/components/com_solidres/layouts/emails/reservation_complete_customer_html_inliner.php</p>';
					echo '<p>&nbsp;&nbsp;&nbsp;<i class="fa fa-copy"></i> To override, copy it to: '. JPATH_ROOT . '/templates/YOUR_TEMPLATE_NAME/layouts/com_solidres/emails/reservation_complete_customer_html_inliner.php</p>';
					echo '<p>'. JPATH_ROOT . '/components/com_solidres/layouts/emails/reservation_complete_owner_html_inliner.php</p>';
					echo '<p>&nbsp;&nbsp;&nbsp;<i class="fa fa-copy"></i> To override, copy it to: '. JPATH_ROOT . '/templates/YOUR_TEMPLATE_NAME/layouts/com_solidres/emails/reservation_complete_owner_html_inliner.php</p>';
					echo '<p>'. JPATH_ROOT . '/components/com_solidres/layouts/emails/reservation_note_notification_customer_html_inliner.php</p>';
					echo '<p>&nbsp;&nbsp;&nbsp;<i class="fa fa-copy"></i> To override, copy it to: '. JPATH_ROOT . '/templates/YOUR_TEMPLATE_NAME/layouts/com_solidres/emails/reservation_note_notification_customer_html_inliner.php</p>';

					echo JHtml::_('bootstrap.endSlide');
					echo JHtml::_('bootstrap.addSlide', 'plugin-collapse', 'Invoice & PDF templates', 'collapse-2');
					if (SRPlugin::isEnabled('invoice')) :
						echo '<p>'. JPATH_ROOT . '/plugins/solidres/invoice/layouts/emails/new_invoice_notification_customer_html_inliner.php</p>';
						echo '<p>&nbsp;&nbsp;&nbsp;<i class="fa fa-copy"></i> To override, copy it to: '. JPATH_ROOT . '/templates/YOUR_TEMPLATE_NAME/layouts/com_solidres/emails/new_invoice_notification_customer_html_inliner.php</p>';
						echo '<p>'. JPATH_ROOT . '/plugins/solidres/invoice/layouts/emails/reservation_complete_customer_pdf.php' . ' (the template for PDF file attached in email when reservation was completed)</p>';
						echo '<p>&nbsp;&nbsp;&nbsp;<i class="fa fa-copy"></i> To override, copy it to: '. JPATH_ROOT . '/templates/YOUR_TEMPLATE_NAME/layouts/com_solidres/emails/reservation_complete_customer_pdf.php</p>';
						echo '<p>'. JPATH_ROOT . '/plugins/solidres/invoice/layouts/invoices/invoice_customer_pdf.php' . ' (the template for downloadable PDF invoice)</p>';
						echo '<p>&nbsp;&nbsp;&nbsp;<i class="fa fa-copy"></i> To override, copy it to: '. JPATH_ROOT . '/templates/YOUR_TEMPLATE_NAME/layouts/com_solidres/invoices/invoice_customer_pdf.php</p>';
					endif;
					echo JHtml::_('bootstrap.endSlide');

					echo JHtml::_('bootstrap.endAccordion');
					?>

					<?php echo $this->loadTemplate('verification'); ?>

					<?php echo $this->loadTemplate('logs'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
</div>
<style>
	@media (min-width: 768px) {
		#solidres .system-info-page .row-fluid [class*="span"] {
			margin-left: 2.564102564102564%;
		}

		#solidres .system-info-page .row-fluid [class*="span"]:first-child {
			margin-left: 0;
		}
	}
</style>