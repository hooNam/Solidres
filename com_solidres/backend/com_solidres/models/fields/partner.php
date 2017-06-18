<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a partner ID from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldPartner extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Partner';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		JHtml::_('bootstrap.framework');

		$html = array();

		if (SRPlugin::isEnabled('user'))
		{
			$groups   = $this->getGroups();
			$excluded = $this->getExcluded();
			$link     = 'index.php?option=com_solidres&amp;view=customers&amp;layout=modal&amp;tmpl=component&amp;field=' . $this->id
				. (isset($groups) ? ('&amp;groups=' . base64_encode(json_encode($groups))) : '')
				. (isset($excluded) ? ('&amp;excluded=' . base64_encode(json_encode($excluded))) : '');

			// Initialize some field attributes.
			$attr = !empty($this->class) ? ' class="' . $this->class . '"' : '';
			$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
			$attr .= $this->required ? ' required' : '';

			// Build the script.
			$script   = array();
			$script[] = '	function jSelectPartner_' . $this->id . '(id, title) {';
			$script[] = '		var old_id = document.getElementById("' . $this->id . '_id").value;';
			$script[] = '		if (old_id != id) {';
			$script[] = '			document.getElementById("' . $this->id . '_id").value = id;';
			$script[] = '			document.getElementById("' . $this->id . '").value = title;';
			$script[] = '			document.getElementById("' . $this->id . '").className = document.getElementById("' . $this->id . '").className.replace(" invalid" , "");';
			$script[] = '			' . $this->onchange;
			$script[] = '		}';
			$script[] = '		parent.Solidres.jQuery("#' . $this->id . '-modal").modal("hide")';
			$script[] = '	}';

			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

			// Load the current username if available.
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_solidres/tables');
			$table     = JTable::getInstance('Customer', 'SolidresTable');
			$userTable = JTable::getInstance('User');

			if (is_numeric($this->value))
			{
				$table->load($this->value);
				$userTable->load($table->user_id);
			}
			// Handle the special case for "current".
			elseif (strtoupper($this->value) == 'CURRENT')
			{
				// 'CURRENT' is not a reasonable value to be placed in the html
				$this->value = JFactory::getUser()->id;
				$table->load($this->value);
			}
			else
			{
				$table->name = JText::_('JLIB_FORM_SELECT_USER');
			}

			// Create a dummy text field with the user name.
			$html[] = '<div class="input-append">';
			$html[] = '	<input type="text" id="' . $this->id . '" value="' . htmlspecialchars($userTable->username, ENT_COMPAT, 'UTF-8') . '"'
				. ' readonly' . $attr . ' />';

			// Create the user select button.
			if ($this->readonly === false)
			{
				$html[] = '		<a class="btn btn-primary modal_' . $this->id . '" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"'
					. ' href="#' . $this->id . '-modal" data-toggle="modal">';
				$html[] = '<i class="fa fa-user"></i></a>';
			}

			$html[] = '</div>';

			// Create the real field, hidden, that stored the user id.
			$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="' . $this->value . '" />';
		}
		else
		{
			$link = '';
			// Create a dummy text field with the user name.
			$html[] = '<div class="input-append">';
			$html[] = '	<input type="text" id="" value=""'
				. ' readonly />';

			// Create the user select button.
			$this->readonly = false;
			if ($this->readonly === false)
			{
				$html[] = '		<a class="btn btn-primary modal_' . $this->id . '" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"'
					. ' href="#' . $this->id . '-modal" data-toggle="modal">';
				$html[] = '<i class="fa fa-user"></i></a>';
			}

			$html[] = '</div>';

			// Create the real field, hidden, that stored the user id.
			$html[] = '<input type="hidden" id="' . $this->id . '_id" name="' . $this->name . '" value="" />';
		}

		$html[] = '<div id="' . $this->id . '-modal" class="modal hide fade"><div class="modal-body" style="max-height: 430px">';
		$html[] = '<button type="button" class="close btn" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times-circle"></i></button>';
		$html[] = '<iframe width="100%" height="400" src="' . $link . '"></iframe></div></div>';

		return implode("\n", $html);
	}

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		return null;
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 *
	 * @since   1.6
	 */
	protected function getExcluded()
	{
		return null;
	}
}
