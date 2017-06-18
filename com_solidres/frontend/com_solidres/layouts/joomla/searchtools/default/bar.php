<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options'])) {
    $data['options'] = new Registry($data['options']);
}

// Options
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');
?>

<?php if (!empty($filters['filter_search'])) : ?>
	<?php echo 'bs3' == SR_UI ? '<div class="row">' : '' ?>
		<?php if ($searchButton) : ?>
		<?php echo 'bs3' == SR_UI ? '<div class="col-md-6">' : '' ?>
			<label for="filter_search" class="element-invisible">
				<?php echo JText::_('JSEARCH_FILTER'); ?>
			</label>
			<div class="<?php echo 'bs2' == SR_UI ? 'btn-wrapper' : ''?> input-append input-group">
				<?php echo $filters['filter_search']->input; ?>
				<?php if ($filters['filter_search']->description) : ?>
					<?php JHtmlBootstrap::tooltip('#filter_search', array('title' => JText::_($filters['filter_search']->description))); ?>
				<?php endif; ?>
				<span class="input-group-btn">
				    <button type="submit" class="btn btn-default hasTooltip"
				            title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
		                <span class="<?php echo 'bs2' == SR_UI ? 'icon-search' : '' ?> fa fa-search"></span>
		            </button>
	            </span>
			</div>
		<?php echo 'bs3' == SR_UI ? '</div>' : '' ?>
		<?php endif; ?>

        <?php if ($filterButton) : ?>
		<?php echo 'bs3' == SR_UI ? '<div class="col-md-6">' : '' ?>
            <div class="btn-wrapper">
                <button type="button" class="btn btn-default hasTooltip js-stools-btn-filter"
                        title="<?php echo JHtml::tooltipText('JSEARCH_TOOLS_DESC'); ?>">
                    <?php echo JText::_('JSEARCH_TOOLS'); ?> <span class="caret"></span>
                </button>
            </div>
			<div class="btn-wrapper">
				<button type="button" class="btn btn-default hasTooltip js-stools-btn-clear"
				        onclick="jQuery('.js-stools-field-filter input').val('');"
				        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">

					<i class="fa fa-times"></i>
				</button>
			</div>
		<?php echo 'bs3' == SR_UI ? '</div>' : '' ?>
        <?php endif; ?>
	<?php echo 'bs3' == SR_UI ? '</div>' : '' ?>
<?php endif;
