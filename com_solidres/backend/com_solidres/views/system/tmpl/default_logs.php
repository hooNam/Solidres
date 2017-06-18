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

$app     = JFactory::getApplication();
$path    = $app->get('log_path');
$options = array();

if (JFolder::exists($path))
{
	$options = JFolder::files($path, 'php|txt|log', false, true);
}

?>
<h3>
	System logs
</h3>
<div id="sr-system-logs">
	<select>
		<option value="">Select a log file</option>
		<?php foreach ($options as $option): ?>
			<option value="<?php echo htmlspecialchars($option); ?>">
				<?php echo $option; ?>
			</option>
		<?php endforeach; ?>
	</select>
</div>
<script>
	Solidres.jQuery(document).ready(function ($) {
		$('#sr-system-logs>select').on('change', function () {
			var file = $(this).find('>option:selected').val().toString();
			if ($('#sr-system-logs > .file-content').length) {
				var pre = $('#sr-system-logs>.file-content').slideUp().empty();
			} else {
				var pre = $('<div class="file-content"/>');
				$('#sr-system-logs').append(pre);
			}
			if (file != '') {
				var spinner = $('<i class="fa fa-spin fa-spinner"/>');
				$(this).after(spinner);
				$.ajax({
					url: '<?php echo JRoute::_('index.php?option=com_solidres&task=system.getLogFile', false); ?>',
					type: 'post',
					dataType: 'json',
					data: {
						file: file,
						'<?php echo JSession::getFormToken(); ?>': 1
					},
					success: function (response) {
						spinner.remove();
						pre
							.html('<pre style="max-height: 550px; overflow-y:scroll;">' + response.content + '</pre>')
							.slideDown();
					}
				});
			}
		});
	});
</script>