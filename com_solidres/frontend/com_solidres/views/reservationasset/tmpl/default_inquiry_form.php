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

<?php if (@$this->item->params['disable_online_booking'] && @$this->item->params['show_inquiry_form']): ?>
	<!-- Quick book form -->
	<form id="sr-inquiry-form" class="form-horizontal">
		<div class="well">
			<div class="control-group">
				<div class="control-label">
					<label for="inquiry_form_fullname" class="text-left"><?php echo JText::_('SR_FULLNAME'); ?></label>
				</div>
				<div class="controls">
					<input name="inquiry_form_fullname" type="text" id="inquiry_form_fullname"
					       class="input-block-level form-control"/>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="inquiry_form_email" class="text-left"><?php echo JText::_('SR_EMAIL'); ?></label>
				</div>
				<div class="controls">
					<input name="inquiry_form_email" type="text" id="inquiry_form_email" class="input-block-level form-control"/>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="inquiry_form_phone" class="text-left"><?php echo JText::_('SR_PHONE'); ?></label>
				</div>
				<div class="controls">
					<input name="inquiry_form_phone" type="text" id="inquiry_form_phone" class="input-block-level form-control"/>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="inquiry_form_message" class="text-left"><?php echo JText::_('SR_MESSAGE'); ?></label>
				</div>
				<div class="controls">
				<textarea name="inquiry_form_message" cols="25" rows="5" id="inquiry_form_message"
				          class="input-block-level form-control"></textarea>
				</div>
			</div>
			<?php if (@$this->item->params['use_captcha']):
				JPluginHelper::importPlugin('captcha', 'recaptcha');
				$dispatcher = JEventDispatcher::getInstance();
				$dispatcher->trigger('onInit', array('sr-inquiry-form-captcha'));
				$results = $dispatcher->trigger('onDisplay', array(null, 'sr-inquiry-form-captcha', 'class="sr-form-captcha"'));
				?>
				<div class="controls" style="margin-bottom: 10px">
					<?php echo $results[0]; ?>
				</div>
			<?php endif; ?>
			<div class="control-group action">
				<div class="controls">
					<button type="submit" class="btn btn-primary btn-large" id="sr-inquiry-button">
						<?php echo JText::_('SR_SEND_MESSAGE'); ?>
					</button>
				</div>
			</div>
		</div>
	</form>
	<script>
		Solidres.jQuery(document).ready(function ($) {
			var submit = function () {
				$('#sr-inquiry-form').validate({
					rules: {
						inquiry_form_fullname: {
							required: true
						},
						inquiry_form_email: {
							required: true,
							email: true
						},
						inquiry_form_phone: {
							required: true
						},
						inquiry_form_message: {
							required: true
						}
					},
					submitHandler: function (form) {
						var
							button = $('#sr-inquiry-button'),
							icon = $('<i class="fa fa-spinner fa-spin"/>');
						button.prepend(icon);
						$.ajax({
							url: '<?php echo JRoute::_('index.php?option=com_solidres&task=reservation.requestBooking', false); ?>',
							type: 'post',
							data: {
								'<?php echo JSession::getFormToken(); ?>': 1,
								'format': 'json',
								'g-recaptcha-response': $('#sr-inquiry-form textarea[name="g-recaptcha-response"]').val(),
								'assetId': <?php echo (int) $this->item->id; ?>,
								'fullname': $('[name="inquiry_form_fullname"]').val(),
								'email': $('[name="inquiry_form_email"]').val(),
								'phone': $('[name="inquiry_form_phone"]').val(),
								'message': $('[name="inquiry_form_message"]').val()

							},
							dataType: 'json',
							success: function (response) {
								icon.remove();
								var alert = $('<div class="alert alert-' + response.status + '"/>');
								alert.text(response.message);
								$('#sr-inquiry-form')
									.slideUp()
									.after(alert);
								setTimeout(function () {
									alert.slideUp();
									if (response.status == 'error') {
										// We need refresh to reset recaptcha
										location.reload();
									}
								}, 5000);
							}
						});
						return false;
					}
				});
			};

			$('#sr-inquiry-button').on('click', submit);
		});
	</script>
<?php endif; ?>
