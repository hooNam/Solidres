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

/**
 * View to edit a reservation asset.
 *
 * @package     Solidres
 * @subpackage	ReservationAsset
 * @since		0.1.0
 */
class SolidresViewReservationAsset extends JViewLegacy
{
	protected $form;

	public function display($tpl = null)
	{
		$this->form	= $this->get('Form');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->lat = $this->form->getValue('lat', '');
		$this->lng = $this->form->getValue('lng', '');
		$this->solidresMedia = SRFactory::get('solidres.media.media');

		JHtml::stylesheet('com_solidres/assets/main.min.css', false, true);
		SRHtml::_('jquery.geocomplete');
		JFactory::getDocument()->addScriptDeclaration('
			Solidres.jQuery(function($){
				$("#geocomplete").geocomplete({
					map: ".map_canvas",
					details: "",
					location: '.(!empty($this->lat) && !empty($this->lng) ? json_encode(array($this->lat, $this->lng)) : 'false').',
					markerOptions: {
						draggable: true
					}
				});

				$("#geocomplete").bind("geocode:dragged", function(event, latLng){
					$("#update").attr("data-lat", latLng.lat());
					$("#update").attr("data-lng", latLng.lng());
					$("#update").show();
				});

				$("#geocomplete").bind("geocode:result", function(event, result){
					var lat = result.geometry.location.lat();
					var lng = result.geometry.location.lng();
					lat = lat.toString().substr(0, 17);
					lng = lng.toString().substr(0, 17);
					$("input#jform_lat").val(lat);
					$("input#jform_lng").val(lng);
					$("#update").attr("data-lat", lat);
					$("#update").attr("data-lng", lng);
					$("#update").show();
				});

				$("#update").click(function(){
					$("input#jform_lat").val($(this).attr("data-lat"));
					$("input#jform_lng").val($(this).attr("data-lng"));
				});

				$("#reset").click(function(){
					$("#geocomplete").geocomplete("resetMarker");
					$("#update").hide();
					return false;
				});

				$("#find").click(function(){
					$("#geocomplete").trigger("geocode");
				});

				$(".geocoding").keyup(function() {
					var str = [];
					$(".geocoding").each(function() {
						var val = $(this).val();
						if (val != "") {
							str.push(val);
						}
					});
					$("#geocomplete").val(str.join(", "));
				});
			});
		');

		JLoader::register('SRSystemHelper', JPATH_LIBRARIES . '/solidres/system/helper.php');
		JHtml::_('behavior.tabstate');

		$this->addToolbar();
		
		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		include JPATH_COMPONENT.'/helpers/toolbar.php';
		$user		= JFactory::getUser();
		$id = $this->form->getValue('id');
		$isNew		= ($id == 0);
		$checkedOut	= !($this->form->getValue('checked_out') == 0 || $this->form->getValue('checked_out') == $user->get('id'));
		$canDo		= SolidresHelper::getActions('', $id);
		
		if($isNew)
		{
			JToolBarHelper::title(JText::_('SR_ADD_NEW_ASSET'), 'generic.png');
		}
		else
		{
			JToolBarHelper::title(JText::sprintf('SR_EDIT_ASSET', $this->form->getValue('name')), 'generic.png');
		}
		
		JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
		
		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			JToolBarHelper::apply('reservationasset.apply', 'JToolbar_Apply');
			JToolBarHelper::save('reservationasset.save', 'JToolbar_Save');
			JToolBarHelper::addNew('reservationasset.save2new', 'JToolbar_Save_and_new');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom('reservationasset.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}
		if ($menuId = (int)$this->form->getValue('menu_id'))
		{
			$bar    = JToolBar::getInstance();
			$app    = JApplicationCms::getInstance('site');
			$router = $app::getRouter('site');
			$uri    = $router->build('index.php?Itemid=' . $menuId);
			$html   = '<a href="' . str_replace('administrator/', '', $uri->toString()) . '" class="btn btn-small" target="_blank">';
			$html .= '   <i class="fa fa-eye"></i> ' . JText::_('SR_VIEW_MENU_IN_FRONEND');
			$html .= '</a>';
			$bar->appendButton('Custom', $html);
		}
		
		if (empty($id))
		{
			JToolBarHelper::cancel('reservationasset.cancel', 'JToolbar_Cancel');
		}
		else
		{
			JToolBarHelper::cancel('reservationasset.cancel', 'JToolbar_Close');
		}
		
		SRToolBarHelper::mediaManager();
		JToolBarHelper::divider();
	}
}
