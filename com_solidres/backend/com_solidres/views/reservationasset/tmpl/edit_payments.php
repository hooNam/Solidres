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

$user	= JFactory::getUser();
$userId	= $user->get('id');

$fields = $this->form->getFieldsets('payments');
echo JHtml::_('bootstrap.startAccordion', 'paymentOptions', array('active' => 'collapse0'));
$i = 0;
foreach ($fields as $name => $fieldSet) :
	$class = isset($fieldSet->class) && !empty($fieldSet->class) ? $fieldSet->class : '';
	echo JHtml::_('bootstrap.addSlide', 'paymentOptions', JText::_($fieldSet->label), 'collapse' . $i++, $class);

	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>

	<?php foreach ($this->form->getFieldset($name) as $field) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label; ?>
			</div>
			<div class="controls">
				<?php echo $field->input; ?>
			</div>
		</div>
	<?php endforeach;

	echo JHtml::_('bootstrap.endSlide');
	endforeach;
echo JHtml::_('bootstrap.endAccordion');
