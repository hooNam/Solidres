<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

class JFormFieldModal_Solidres extends JFormField
{
	protected $type = 'Modal_Solidres';

	protected function getInput()
	{
		JFactory::getLanguage()->load('com_solidres', JPATH_ADMINISTRATOR . '/components/com_solidres');

		$view = strtolower($this->getAttribute('view', 'reservationassets'));
		$name = 'name';

		switch ($view)
		{
			case 'reservationassets':
				$table = '#__sr_reservation_assets';
				break;

			case 'coupons':
				$table = '#__sr_coupons';
				$name  = 'coupon_name';
				break;

			case 'extras':
				$table = '#__sr_extras';
				break;

			case 'roomtypes':
				$table = '#__sr_room_types';
				break;

			default:
				throw new Exception('Invalid modal view type: ' . ucfirst($view));

				return false;
		}

		$multiple = $this->getAttribute('multiple', 'false');
		$src      = JUri::root(true) . '/administrator/index.php?option=com_solidres&view=' . $view . '&tmpl=component';
		$html     = array('<div id="' . $this->id . '_modal" class="modal hide fade solidres-modal" tabindex="-1" role="dialog">');
		$document = JFactory::getDocument()
			->addScriptDeclaration('var multiple_' . $this->id . ' = ' . $multiple . ';');
		JText::script('SR_NO_ITEMS_SELECT_ALERT');
		JText::script('SR_CLEAR');
		SRHtml::_('jquery.ui');
		$document->addScriptDeclaration('
				Solidres.jQuery(document).ready(function($){
					var modal = $("#' . $this->id . '_modal");		
					var view = $("#' . $this->id . '_view");		
					view.find(".list[data-sortable]").sortable({
						update: function(event, ui){
							var input = $("input.' . $this->id . '"), pos = 0;
							view.find(".list[data-sortable]>li>a[data-id]").each(function(){
								input.eq(pos++).val($(this).data("id"));	
							});
						}
					});
					view.find(".list[data-sortable]").disableSelection();
					Solidres.removeModalRecord = function (el){
						var input = $("input.' . $this->id . '");
						if(input.length > 1){
							input.each(function(){
								if($(this).val() == $(el).data("id")){
									$(this).remove();
									return;
								}
							});
						}else {
							if(input.length == 1){
								input.eq(0).val("").attr("disabled", "disabled");
								$("#' . $this->id . '_view").val("");
							}
						}						
						$(el).parents("li").remove();
					};
					modal.on("shown.bs.modal", function(){
						$(this).find(".modal-body > iframe").off().remove();	
						var iframe = $(this)
							.find(".modal-body").append("<iframe src=\'' . $src . '\' width=\'100%\' height=\'400\'></iframe>")
							.find(">iframe");	
						iframe.on("load", function(){
							var el = $(this).contents();
							var form = el.find("body #sr_panel_right")
								.addClass("' . SR_UI_GRID_COL_12 . '")
								.removeClass("' . SR_UI_GRID_COL_10 . '")
								.find("#adminForm").attr("action", "index.php?option=com_solidres&view=' . $view . '&tmpl=component");												
							var nameIndex = "' . $view . '" == "coupons" ? 2 : 3;							
							var selectRecords = function(multiple, action){								
								var cid = [];
								var input = $("input.' . $this->id . '");
								form.find("input[name=\'cid[]\']:checked").each(function(){
									cid.push($(this).val());									
								});							
								if(multiple){	
									if(!cid.length && action == "insert"){
										alert(Joomla.JText._("SR_NO_ITEMS_SELECT_ALERT"));
										return;
									}
									
									if(action == "clear"){
										input.eq(0)
											.val("")
											.attr("disabled", "disabled")
											.siblings("input[type=\'hidden\']").remove();	
										view.find(".list").empty();
									}else{
										var row, list = view.find(".list");
										
										input.each(function(){
											var pk = $(this).val().toString();
											var index = cid.indexOf(pk);
											if(index > -1){
												cid.splice(index, 1);
											}
										});
										
										for(var i = 0; i < cid.length; i++){										
											row = form.find("input[name=\'cid[]\'][value=\'" + cid[i] + "\']").parents("tr:eq(0)");	
											var checkbox = row.find("td:eq(" + nameIndex + ")");
											
											if(!checkbox.length){
												continue;
											}
											
											list.append(
												"<li style=\'cursor: pointer\'><i class=\'fa fa-sort\'></i> " + checkbox.get(0).innerText
												+ " <a href=\'#\' onclick=\'Solidres.removeModalRecord(this);\' class=\'text-error text-danger\'"
												+ " data-id=\'" + cid[i] + "\'> <i class=\'fa fa-times-circle\'></i></a></li>"
											);										
											
											var newInput = $("input.' . $this->id . ':last");	
											
											newInput.after(newInput.clone().prop("disabled", false).val(cid[i]));	
											
											if(newInput[0].hasAttribute("disabled") || parseInt(newInput.val()) < 1){
												newInput.remove();
											}
										}					
									}
																
								}else{		
									if(action == "clear"){
										input.val("");
										view.val("");
									}
									else{		
										input.val(cid[0]);
										view.val(form.find("input[name=\'cid[]\'][value=\'" + cid[0] + "\']")
											.parents("tr:eq(0)")
											.find("td:eq(" + nameIndex + ")").get(0).innerText);
										var btnClear = view.siblings("a.btn-clear");
										if(!btnClear.length){
											btnClear = $("<a href=\'#\' class=\'btn btn-default btn-clear\'/>")
												.html("<i class=\'fa fa-times-circle\'></i> " + Joomla.JText._("SR_CLEAR"))
												.attr("onclick", "Solidres.removeModalRecord(this)");												
											view.siblings(".btn:last").after(btnClear);
										}
										
										btnClear.attr("data-id", input.val());
									}
								}			
								
								modal.modal("hide");
							};			
																
							el.find("body #sr_panel_left").remove();		
											
							$("#' . $this->id . '_btn_clear").unbind().on("click", function(e){
								e.preventDefault();							
								selectRecords(multiple_' . $this->id . ', "clear");
							});
							if(multiple_' . $this->id . '){
								form.find("td > a").each(function(){
									var link = $(this), txt = link.text();
									link.parent("td").html(txt);
								});
								$("#' . $this->id . '_btn_insert").unbind().on("click", function(e){
									e.preventDefault();										
									selectRecords(true, "insert");
								});							
							}else{
								form.find("thead>tr>th > input[name=\'checkall-toggle\']")
									.parent("th").addClass("hide")
									.prev("th").addClass("hide");
								form.find("tbody>tr").each(function(){
									$(this).find(">td input[name=\'cid[]\']")
										.parent("td").addClass("hide")
										.prev("td").addClass("hide");
								});
								form.find("td>a").unbind().on("click", function(e){
									e.preventDefault();				
									form.find("input[name=\'cid[]\']").prop("checked", false);
									$(this).parents("tr:eq(0)")
										.find("input[name=\'cid[]\']").prop("checked", true);
									selectRecords(false, "insert");
								});
							}												
						});	
					});													
				});
			');

		if (SR_UI == 'bs3')
		{
			$html[] = '<div class="modal-dialog modal-lg" role="document"><div class="modal-content">';
		}

		$html[] = '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">';
		$html[] = '<span aria-hidden="true">&times;</span></button>';
		$html[] = '<h4 class="modal-title">' . JText::_('SR_' . strtoupper($view) . '_SELECT') . '</h4></div>';
		$html[] = '<div class="modal-body"></div>';
		$html[] = '<div class="modal-footer"><div class="btn-group">';

		if ($multiple == 'true')
		{
			$html[] = '<button type="button" id="' . $this->id . '_btn_insert" class="btn btn-primary"><i class="fa fa-plus"></i> ' . JText::_('SR_INSERT') . '</button>';
		}

		$html[] = '<button type="button" id="' . $this->id . '_btn_clear" class="btn btn-warning"><i class="fa fa-trash"></i> ' . JText::_('SR_CLEAR') . '</button>';
		$html[] = '</div></div>';

		if (SR_UI == 'bs3')
		{
			$html[] = '</div></div>';
		}

		$html[] = '</div>';

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id, a.' . $name)
			->from($db->qn($table, 'a'));

		if ($view == 'extras' || $view == 'coupons')
		{
			$query->order('a.' . $name . ' ASC');
		}
		else
		{
			$query->order('a.ordering ASC');
		}

		if ($multiple == 'true')
		{
			$html[] = '<div id="' . $this->id . '_view">';
			$html[] = '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#' . $this->id . '_modal">' . JText::_('SR_SELECT') . '</button>';
			$html[] = '<ol class="list" data-sortable style="margin-top: 12px">';

			if (empty($this->value))
			{
				$this->value = array();
			}
			elseif (is_numeric($this->value))
			{
				$this->value = array((int) $this->value);
			}
			elseif (is_string($this->value) && strpos($this->value, ',') !== false)
			{
				$this->value = explode(',', $this->value);
			}

			$hiddenHtml = array();

			if (!empty($this->value))
			{
				$query->where('a.id IN (' . join(',', ArrayHelper::toInteger((array) $this->value)) . ')');
				$db->setQuery($query);
				$rows = $db->loadObjectList('id');

				foreach ((array) $this->value as $id)
				{
					if(!isset($rows[$id]))
					{
						continue;
					}

					$row    = $rows[$id];
					$html[] = '<li style="cursor: pointer"><i class="fa fa-sort"></i> ' . $row->{$name};
					$html[] = ' <a href="#" onclick="Solidres.removeModalRecord(this)" class="text-error text-danger" ';
					$html[] = 'data-id="' . (int) $row->id . '"> <i class="fa fa-times-circle"></i></a></li>';
					$hiddenHtml[] = '<input type="hidden" name="' . $this->name . '" class="' . $this->id . '" value="' . (int) $row->id . '"/>';
				}
			}
			else
			{
				$hiddenHtml[] = '<input type="hidden" name="' . $this->name . '" class="' . $this->id . '" value="0"/>';
			}

			$html[] = '</ol></div>'. join("\n", $hiddenHtml);

		}
		else
		{
			if (is_array($this->value))
			{
				$this->value = $this->value[0];
			}

			$query->where('a.id = ' . (int) $this->value);
			$db->setQuery($query);
			$row     = $db->loadObject();
			$preview = $row ? htmlspecialchars($row->{$name}, ENT_QUOTES, 'UTF-8') : '';
			$html[]  = '<div class="' . SR_UI_INPUT_APPEND . '">';
			$html[]  = '<input type="text" readonly id="' . $this->id . '_view" value="' . $preview . '"/>';
			$html[]  = (SR_UI == 'bs3' ? '<div class="input-group-btn">' : '');
			$html[]  = '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#' . $this->id . '_modal">' . JText::_('SR_SELECT') . '</button>';

			if (!empty($row->id))
			{
				$html[] = ' <a href="#" onclick="Solidres.removeModalRecord(this)" class="btn btn-default btn-clear"';
				$html[] = 'data-id="' . (int) $row->id . '"> <i class="fa fa-times-circle"></i> ' . JText::_('SR_CLEAR') . '</a>';
			}

			$html[] = '</div>' . (SR_UI == 'bs3' ? '</div>' : '');
			$html[] = '<input type="hidden" name="' . $this->name . '" class="' . $this->id . '" value="' . (int) $this->value . '"/>';
		}

		return join("\n", $html);
	}
}