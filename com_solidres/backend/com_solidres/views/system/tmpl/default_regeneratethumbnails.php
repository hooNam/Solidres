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
<div id="sr-generate-thumbnails">
	<h3>
		Image thumbnails
		<button type="button" class="btn-progress btn btn-small btn-primary">
			<i class="fa fa-cog"></i>
			Regenerate
		</button>
	</h3>
	<p>This tool will re-generate all Solidres media's thumbnails (uploaded via Solidres Media Manager) here: /media/com_solidres/assets/images/system/thumbnails </p>
	<div class="progress progress-success progress-striped active">
		<div class="bar" style="width:0%"></div>
	</div>
</div>
<script>
	jQuery(document).ready(function ($) {
		var wrapper = $('#sr-generate-thumbnails'), bar = wrapper.find('.progress>.bar');
		wrapper.on('click', '.btn-progress', function () {
			if (!window.XMLHttpRequest) {
				alert('Your browser\'s not support XMLHttpRequest');
				return;
			}
			var btn = $(this);
			btn.find('>.fa').addClass('fa-spin');
			bar.css('width', '0%').removeClass('hide');
			var xhr = new window.XMLHttpRequest;
			xhr.onreadystatechange = function () {
				if (xhr.readyState == 3 && xhr.status == 200) {
					var progressText = xhr.responseText.replace(/[^0-9\.\%\[\]]/gi, '');
					var matches = /(\[([0-9]+\.?[0-9]*\%)\])$/gmi.exec(progressText);

					if (matches && matches[2]) {
						bar.css('width', matches[2]);
					}
				}

				if (xhr.readyState == 4 && xhr.status == 200) {
					bar.css('width', '100%');
					setTimeout(function () {
						bar.addClass('hide');
					}, 400);
					btn.find('>.fa').removeClass('fa-spin');
				}
			};

			xhr.open('POST', '<?php echo JRoute::_('index.php?option=com_solidres&task=system.progressThumbnails', false); ?>', true);
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhr.send('<?php echo JSession::getFormToken(); ?>=1');

		});
	});
</script>