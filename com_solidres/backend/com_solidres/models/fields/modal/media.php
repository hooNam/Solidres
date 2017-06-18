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

class JFormFieldModal_Media extends JFormField
{
	protected $type = 'Modal_Media';

	protected function getInput()
	{
		$html     = array();
		$href     = JUri::base(true) . '/index.php?option=com_solidres&view=medialist&layout=modal&tmpl=component';
		$required = $this->getAttribute('required');
		$required = $required && ($required != '0' || $required != 'false') ? ' required' : '';
		$class    = $this->getAttribute('class', '') . $required . ' input-medium';
		$html[]   = '<div class="input-append input-group' . $required . '">';
		$html[]   = '     <input type="text" name="' . $this->name . '" readonly id="' . $this->id . '" class="' . $class . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $required . '/>';

		if (!empty($this->value))
		{
			$srMedia = SRFactory::get('solidres.media.media');
			$html[]  = '<a href="' . $srMedia->getMediaUrl($this->value) . '" class="btn btn-default sr-photo cboxElement"><i class="fa fa-image"></i></a>';
		}

		$html[] = '     <a href="' . $href . '" class="sr-iframe btn btn-default cboxElement"><span class="icon-file"></span> ' . JText::_('JSELECT') . '</a>';
		$html[] = '     <a href="#" id="' . $this->id . '_clear" class="btn btn-default' . (empty($this->value) ? ' hide' : '') . '"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</a>';
		$html[] = '</div>';

		JFactory::getDocument()->addScriptDeclaration('
			Solidres.jQuery(document).ready(function($){	
				$("#' . $this->id . '_clear").on("click", function(e){
					e.preventDefault();
					$("#' . $this->id . '").val("")
						.next(".sr-photo").hide();
					$(this).addClass("hide");
				});
				$(document).bind("cbox_complete", function(){
                    $(this).find(".cboxIframe").on("load", function(){
                        var iframe = $(this).contents();
                        iframe.on("click", "[data-media-id]", function(){
                            var media = $(this), cb = media.find(".media-checkbox");
                            iframe.find("#medialibrary .media-checkbox").prop("checked", false);
                            iframe.find("#medialibrary [data-media-id]").removeClass("media-selected");
                            media.toggleClass("media-selected");
                            cb.prop("checked", media.hasClass("media-selected"));
                        });
                        iframe.on("dblclick", "[data-media-id]", function(e){
                            e.preventDefault();
                            var media = $(this);
                            if(media.hasClass("media-selected")){
                                $("#' . $this->id . '").val(media.data("mediaValue"));
                                $("#' . $this->id . '_clear").removeClass("hide");
                                $.colorbox.close();
                            };
                        });
                        iframe.on("click", "#media-modal-insert", function(){
                            if(!iframe.find("[data-media-id].media-selected").length){
                                alert("' . JText::_('SR_NO_MEDIA_SELECTED') . '");
                                return false;
                            }
                            iframe.find("[data-media-id].media-selected").trigger("dblclick");
                        });
                    });
				});
			});
		');

		return join("\n", $html);
	}
}