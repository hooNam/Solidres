<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * -----------------
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
 * @var   array   $checkedOptions Options that will be set as checked.
 * @var   boolean $hasValue       Has this field a value assigned?
 * @var   array   $options        Options available for this field.
 *
 * @var   string  $preview        The preview image relative path
 * @var   integer $previewHeight  The image preview height
 * @var   integer $previewWidth   The image preview width
 * @var   string  $asset          The asset text
 * @var   string  $authorField    The label text
 * @var   string  $folder         The folder text
 * @var   string  $link           The link text
 */
extract($displayData);

// Load the modal behavior script.
JHtml::_('behavior.modal', 'a.modal_popup');

// Include jQuery
JHtml::_('jquery.framework');
JHtml::_('script', 'media/mediafield-mootools.min.js', true, true, false, false, true);

// Tooltip for INPUT showing whole image path
$options = array(
	'onShow' => 'jMediaRefreshImgpathTip',
);

JHtml::_('behavior.tooltip', '.hasTipImgpath', $options);

if (!empty($class))
{
	$class .= ' hasTipImgpath';
}
else
{
	$class = 'hasTipImgpath';
}

$attr = '';

$attr .= ' title="' . htmlspecialchars('<span id="TipImgpath"></span>', ENT_COMPAT, 'UTF-8') . '"';

// Initialize some field attributes.
$attr .= !empty($class) ? ' class="input-small field-media-input ' . $class . '"' : ' class="input-small"';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';

// Initialize JavaScript field attributes.
$attr .= !empty($onchange) ? ' onchange="' . $onchange . '"' : '';

// The text field.
echo '<div class="' . (SR_UI == 'bs2' ? 'input-prepend input-append' : 'input-group') . '">';

// The Preview.
$showPreview   = true;
$showAsTooltip = false;

switch ($preview)
{
	case 'no': // Deprecated parameter value
	case 'false':
	case 'none':
		$showPreview = false;
		break;

	case 'yes': // Deprecated parameter value
	case 'true':
	case 'show':
		break;
	case 'tooltip':
	default:
		$showAsTooltip = true;
		$options       = array(
			'onShow' => 'jMediaRefreshPreviewTip',
		);
		JHtml::_('behavior.tooltip', '.hasTipPreview', $options);
		break;
}

// Pre fill the contents of the popover
if ($showPreview)
{
	if ($value && file_exists(JPATH_ROOT . '/' . $value))
	{
		$src = JUri::root() . $value;
	}
	else
	{
		$src = '';
	}

	$width  = $previewWidth;
	$height = $previewHeight;
	$style  = '';
	$style .= ($width > 0) ? 'max-width:' . $width . 'px;' : '';
	$style .= ($height > 0) ? 'max-height:' . $height . 'px;' : '';

	$imgattr = array(
		'id'    => $id . '_preview',
		'class' => 'media-preview',
		'style' => $style,
	);

	$img             = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $imgattr);
	$previewImg      = '<div id="' . $id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
	$previewImgEmpty = '<div id="' . $id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
		. JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

}

echo '	<input type="text" name="' . $name . '" id="' . $id . '" value="'
	. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" readonly="readonly"' . $attr . ' data-basepath="'
	. JUri::root() . '" style="border-radius: 4px 0 0 4px; margin-bottom: 0"/>';

?>
<div class="<?php echo SR_UI == 'bs3' ? ' input-group-btn' : ''; ?>"
     style="width: auto;<?php echo SR_UI == 'bs2' ? ' display: inline-block' : ''; ?>; vertical-align: middle;">
	<?php
	if ($showAsTooltip)
	{
		echo '<button type="button" class="btn btn-default media-preview" style="border-radius: 0">';
		$tooltip = $previewImgEmpty . $previewImg;
		$options = array(
			'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
			'text'  => '<i class="fa fa-eye"></i>',
			'class' => 'hasTipPreview'
		);

		echo JHtml::tooltip($tooltip, $options);
		echo '</button>';
	}
	else
	{
		echo '<button type="button" class="media-preview" style="height: auto">';
		echo ' ' . $previewImgEmpty;
		echo ' ' . $previewImg;
		echo '</button>';
	}
	?>
	<a class="modal_popup btn btn-default"
	   title="<?php echo JText::_('JLIB_FORM_BUTTON_SELECT'); ?>" href="
<?php echo ($readonly ? ''
			: ($link ? $link
				: 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=' . $asset . '&amp;author='
				. $authorField) . '&amp;fieldid=' . $id . '&amp;folder=' . $folder) . '"'
		. ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}"'; ?>>
 <?php echo JText::_('JLIB_FORM_BUTTON_SELECT'); ?></a>
 <a class=" btn btn-default
	   hasTooltip" title="<?php echo JText::_('JLIB_FORM_BUTTON_CLEAR'); ?>" href="#" onclick="jInsertFieldValue('',
	'<?php echo $id; ?>'); return false;">
	<i class="fa fa-times"></i>
	</a>
</div>
</div>