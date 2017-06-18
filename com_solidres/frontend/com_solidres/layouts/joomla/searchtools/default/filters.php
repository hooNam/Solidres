<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
JFactory::getDocument()->addStyleDeclaration('.js-stools-field-filter .input-append, .js-stools-field-filter input{margin-bottom: 0}')
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) :
		$show_label = (bool)$field->getAttribute('showlabel');
		?>
		<?php if ($fieldName != 'filter_search') : ?>
			<div class="js-stools-field-filter">
				<?php echo $field->input; ?>
				<?php if($field->getAttribute('type') == 'calendar'): ?>
				<button type="submit" class="btn calendar-submit">
					<i class="icon-filter"></i>
				</button>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
