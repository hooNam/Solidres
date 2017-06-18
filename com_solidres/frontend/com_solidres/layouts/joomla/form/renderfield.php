<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 *    $options         : (array)  Optional parameters
 *    $label           : (string) The html code for the label (not required if $options['hiddenLabel'] is true)
 *    $input           : (string) The input field html code
 */

if (!empty($options['showonEnabled']))
{
	JHtml::_('jquery.framework');
	JHtml::_('script', 'jui/cms.js', false, true);
}

$class = empty($options['class']) ? '' : ' ' . $options['class'];
$rel   = empty($options['rel']) ? '' : ' ' . $options['rel'];

global $uiAppendScript;

if (true !== $uiAppendScript)
{
	$uiAppendScript = true;

	if (SR_UI == 'bs3')
	{
		JFactory::getDocument()->addScriptDeclaration('
				Solidres.jQuery(document).ready(function($){				
					$(".bs3 .form-group input[type=\'text\'],"
						+ ".bs3 .form-group input[type=\'email\'],"
						+ ".bs3 .form-group input[type=\'password\'],"
						+ ".bs3 .form-group select,"
						+ ".bs3 .form-group textarea").addClass("form-control");
					$(".bs3 .form-group .input-append").addClass("input-group")
						.find(">.btn").addClass("btn-default")
						.find(".icon-calendar").addClass("fa fa-calendar").removeClass("icon-calendar");
					var modal = $(".bs3 .form-group [id^=\'articleSelectjform\']").removeClass("hide").hide();						
					if(modal.find(">.modal-dialog").length == 0){
						modal.each(function(){
							var el = $(this), dialog = $("<div class=\'modal-dialog modal-lg\'></div>").append("<div class=\'modal-content\'></div>");
							dialog.find(">.modal-content").append(el.find(">.modal-header, >.modal-body, >.modal-footer"));
							dialog.find(".modal-footer .btn").addClass("btn-default");													
							el.append(dialog);							
						});						
					}
					modal.on("DOMSubtreeModified", function(){
						$(this).find("iframe").on("load", function(){
							var frame = $(this).contents();
							frame.find("body .icon-search").addClass("fa fa-search").removeClass("icon-search");
							frame.find("body .icon-publish").addClass("fa fa-check").removeClass("icon-publish");
							frame.find("body .icon-unpublish").addClass("fa fa-times-circle").removeClass("icon-unpublish");
						});
					});
				});
		')
			->addStyleDeclaration('
			@media (min-width: 992px){
				.bs3 .modal-dialog {
	                width: 900px;
				}	
			}
			.bs3 .modal-dialog .modal-body{
				padding: 0;
			}
			.bs3 .modal-dialog iframe{
				border: none;
				width: 100%;
			}
		');
	}
}

?>
<?php if (empty($options['input_only'])): ?>
	<div class="<?php echo SR_UI_FORM_ROW; ?><?php echo $class; ?>"<?php echo $rel; ?>>
		<?php if (empty($options['hiddenLabel'])) : ?>
			<?php if ('bs2' == SR_UI) : ?><div class="<?php echo SR_UI_FORM_LABEL ?>"><?php endif ?>
			<?php echo $label; ?>
			<?php if ('bs2' == SR_UI) : ?></div><?php endif ?>
		<?php endif; ?>
		<div
			class="<?php echo SR_UI_FORM_FIELD ?> <?php echo (isset($options['hiddenLabel']) && 'bs3' == SR_UI) ? ' col-sm-offset-2' : ''; ?>">
			<?php echo $input; ?>
		</div>
	</div>
<?php else: ?>
	<?php echo $input; ?>
<?php endif; ?>