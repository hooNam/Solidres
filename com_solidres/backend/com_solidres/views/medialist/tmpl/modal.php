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

$doc = JFactory::getDocument();
$doc->addStyleDeclaration('
	div[data-media-id]{
		padding: 3px;
		cursor: pointer;
	}
	div[data-media-id].media-selected{
		border: 1px solid #ddd; 
		border-radius:  4px;
	}
');

?>
<div id="solidres">
	<div id="mediamanager">
		<div id="sr-tabs">
			<ul>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=medialist&layout=form&tmpl=component') ?>">
						<?php echo JText::_('Upload media') ?>
					</a>
				</li>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=medialist&layout=modal_library&tmpl=component') ?>">
						<?php echo JText::_('Media library') ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>



<?php
$doc->addScriptDeclaration('
var mediaTabs;
Solidres.jQuery(function($) {
        mediaTabs = $( "#sr-tabs" ).tabs({
            beforeLoad: function( event, ui ) {
            	ui.panel.html("<div class=\"processing\"></div>");
                ui.jqXHR.error(function() {
                    ui.panel.html(
                            "Couldn\'t load this tab. We\'ll try to fix this as soon as possible. " +
                                    "If this wouldn\'t be a demo." );
                });

            },
            load: function( event, ui ) {
            }
        });
    });
');

