<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('behavior.core');

$doTask   = $displayData['doTask'];
$class    = $displayData['class'];
$text     = $displayData['text'];
$btnClass = $displayData['btnClass'];
$iconMapping = array(
	'icon-new' => 'fa fa-plus-circle',
	'icon-new icon-white' => 'fa fa-plus-square-o',
	'icon-apply icon-white' => 'fa fa-pencil-square-o',
	'icon-edit' => 'fa fa-edit',
	'icon-publish' => 'fa fa-check',
	'icon-unpublish' => 'fa fa-close',
	'icon-trash' => 'fa fa-trash',
	'icon-copy' => 'fa fa-copy',
	'icon-cancel' => 'fa fa-times-circle',
	'icon-save' => 'fa fa-check',
	'icon-download' => 'fa fa-download',
	'icon-save-new' => 'fa fa-plus',
	'icon-save-copy' => 'fa fa-clone',
);

?>
<button onclick="<?php echo $doTask; ?>" class="btn-default btn-sm <?php echo $btnClass; ?>">
	<span class="<?php //echo trim($class); ?> <?php echo $iconMapping[trim($class)] ?>"></span>
	<?php echo $text; ?>
</button>
