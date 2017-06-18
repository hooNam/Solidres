<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$input     = JFactory::getApplication()->input;
$field     = $input->getCmd('field');
$function  = 'jSelectPartner_' . $field;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=customers&layout=modal&tmpl=component&groups=' . $input->get('groups', '', 'BASE64') . '&excluded=' . $input->get('excluded', '', 'BASE64'));?>" method="post" name="adminForm" id="adminForm">

	<fieldset class="filter">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input class="inputbox"
				       type="text"
				       name="filter_customer_fullname"
				       id="filter_customer_fullname"
				       value="<?php echo $this->state->get('filter.customer_fullname'); ?>"
				       placeholder="<?php echo JText::_('SR_FILTER_FULL_NAME_SEARCH'); ?>"
				/>
				<input class="inputbox"
				       type="text"
				       name="filter_customer_username"
				       id="filter_customer_username"
				       value="<?php echo $this->state->get('filter.customer_username'); ?>"
				       placeholder="<?php echo JText::_('SR_FILTER_USERNAME_SEARCH'); ?>"
				/>
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" data-placement="bottom" onclick="document.getElementById('filter_customer_fullname').value='';document.getElementById('filter_customer_username').value='';this.form.submit();"><span class="icon-remove"></span></button>
				<?php if ($input->get('required', 0, 'int') != 1 ) : ?>
					<button type="button" class="btn"
					        onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('', '');">
						<?php echo JText::_('JOPTION_NO_USER'); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
	</fieldset>


	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th class="left">
					<?php echo JHtml::_('grid.sort', 'SR_HEADING_CUSTOMER_FULLNAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" width="25%">
					<?php echo JHtml::_('grid.sort', 'SR_HEADING_CUSTOMER_USERNAME', 'a.username', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap" width="25%">
					<?php echo JText::_('SR_HEADING_CUSTOMER_GROUP_NAME'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
			$i = 0;

			foreach ($this->items as $item) :
				$fullName = $item->firstname. ' ' . $item->middlename .' '. $item->lastname;
				$groupName = is_null($item->group_name) ? JText::_('SR_GENERAL_CUSTOMER_GROUP') : $item->group_name;
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->jusername)); ?>');">
						<?php echo $fullName; ?></a>
				</td>
				<td align="center">
					<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->jusername)); ?>');">
					<?php echo $item->jusername; ?></a>
				</td>
				<td align="left">
					<?php echo $groupName; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
