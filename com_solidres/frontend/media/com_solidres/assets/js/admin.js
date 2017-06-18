/*------------------------------------------------------------------------
 Solidres - Hotel booking extension for Joomla
 ------------------------------------------------------------------------
 @Author    Solidres Team
 @Website   http://www.solidres.com
 @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
 @License   GNU General Public License version 3, or later
 ------------------------------------------------------------------------*/

Solidres.context = 'backend';

Solidres.jQuery(function($) {
    $( '#jform_partner_name' ).autocomplete({
        source: 'index.php?option=com_solidres&task=customers.find&format=json',
        minLength: 3,
        select: function(event, ui) {
            var a = $('#jform_partner_id');
            if( a.length ) {
                a.val(ui.item.id);
            } else {
                var b = $('<input />', {
                    'type'  : 'hidden',
                    'value' : ui.item.id,
                    'name'  : 'jform[partner_id]',
                    'id'    : 'jform_partner_id'
                });
                b.insertAfter( $( this ) );
            }
        }
    });

    $(".filter_checkin_checkout").datepicker({
        numberOfMonths : 1,
        showButtonPanel : true,
        dateFormat : "dd-mm-yy",
		firstDay: 1
    });

	$(".ui-datepicker").addClass("notranslate");

    $('#customer-modal-form').submit(function(event) {
        event.preventDefault();
        var form = $(this),
            url  = form.attr( 'action' );
        $.post(
            url,
            form.serialize(),

            function( data )
            {
                if(data.saved)
                {
                    var msg = [
                        '<div id="system-message-container">',
                        '<dl id="system-message">',
                        '<dt class="message">Message</dt>',
                        '<dd class="message message">',
                        '<ul><li>Item successfully saved.</li></ul>',
                        '</dd></dl></div>',
                        '<input type="hidden" id="partner_id" name="partner_id" value="'+ data.customer_id + '" />',
                        '<input type="hidden" value="'+ data.firstname + ' ' + data.middlename + ' ' + data.lastname + '" name="jform[partner_name]" id="partner_name"/>'
                    ].join("");

                    $('#customer-modal-form').before(msg);

                    $.ajax({
                        url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&format=json&task=customer.sendEmail&cId=' + data.customer_id,
                        success : function(jsonObj) {
                            if (jsonObj == true) {
                                var msg = [
                                    '<div id="system-message-container">',
                                    '<dl id="system-message">',
                                    '<dt class="message">Message</dt>',
                                    '<dd class="message message">',
                                    '<ul><li>Email send successfully</li></ul>',
                                    '</dd></dl></div>'
                                ].join("");
                                $('#customer-modal-form').before(msg);
                            } else {
                                var msg = [
                                    '<div id="system-message-container">',
                                    '<dl id="system-message">',
                                    '<dt class="message">Message</dt>',
                                    '<dd class="message message">',
                                    '<ul><li>Can not send email</li></ul>',
                                    '</dd></dl></div>'
                                ].join("");
                                $('#customer-modal-form').before(msg);
                            }
                        }
                    });
                }
                else {

                }
            },
            "json"
        );
    });

    $('#insert-customer').click(function() {
        var partner_name   = $('#partner_name').val();
        var partner_id     = $('#partner_id').val();
        $('#jform_partner_name', 	window.parent.document).val(partner_name);
        $('#jform_partner_id', 		window.parent.document).val(partner_id);
        parent.jQuery.fn.colorbox.close();
    });

    $(".close-colorbox").click(function() {
        parent.jQuery.fn.colorbox.close();
    });

 	var changeTaxSelectStatus = function() {
		if ($(".asset_tax_select").length) {
			if ($(".asset_tax_select").val() > 0) {
				$('.tax_select').removeAttr('disabled');
			} else {
				$('.tax_select').attr('disabled', 'disabled');
			}
		}

		if ($(".country_select").length) {
			if ($(".country_select").val() > 0) {
				$('.tax_select').removeAttr('disabled');
			} else {
				$('.tax_select').attr('disabled', 'disabled');
			}
		}
	};

	changeTaxSelectStatus();

	$(".asset_tax_select").change(function() {
		$.ajax({
			url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=taxes.find&id=' + $(this).val(),
			success : function(html) {
				$('.tax_select').empty().html(html);
			}
		});

		changeTaxSelectStatus();
	});

    $(".country_select").change(function() {
        $.ajax({
            url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&format=json&task=states.find&id=' + $(this).val(),
            success : function(html) {
                $('.state_select').empty().html(html);
            }
        });

		$.ajax({
			url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=taxes.find&country_id=' + $(this).val(),
			success : function(html) {
				$('.tax_select').empty().html(html);
			}
		});

		changeTaxSelectStatus();
    });

    $("#reservationnote-form").submit(function(e) {
        e.preventDefault();

        var form = $(this),
            url  = form.attr( 'action'),
            submitBtn = form.find('button[type=submit]'),
            processingIndicator = form.find('div.processing');

        submitBtn.attr('disabled', 'disabled');
        submitBtn.addClass('nodisplay');
        processingIndicator.removeClass('nodisplay');
        processingIndicator.addClass('active');
        $.post(
            url,
            form.serialize(),
            function( data ) {
                if(data.status) {
                    submitBtn.removeClass('nodisplay');
                    submitBtn.removeAttr('disabled', 'disabled');
                    processingIndicator.addClass('nodisplay');
                    processingIndicator.removeClass('active');
                    $('.reservation-note-holder').append(
                        $('<div class="reservation-note-item"><p class="info">'
                            + data.created_date + ' by '
                            + data.created_by_username + '</p>'
                            + '<p>' + Joomla.JText._('SR_RESERVATION_NOTE_NOTIFY_CUSTOMER') + ': ' + data.notify_customer +  ' | '
                            + '' + Joomla.JText._('SR_RESERVATION_NOTE_DISPLAY_IN_FRONTEND') + ': ' + data.visible_in_frontend +  '</p>'
                            + '<p>' + data.text  + '</p></div>'
                        )) ;
                    form.children('textarea').val('');
                    form.find('input[type="checkbox"]').prop('checked', false);
                }
            },
            "json"
        );
    });

    $('#solidres').on('click', '.tariff-modal', function(e) {
        e.preventDefault();
        $.colorbox({href: $(this).attr('href'), inline: false, width:"80%", height:"80%", iframe: true});
    });

    $('#sr_side_navigation.disabled li>a').on('click', function (e) {
        e.preventDefault();
    });
    var nav = $('#sr_panel_left');
    if ($.cookie('sr_item_active') == '#sr_panel_left') {
        nav.addClass('showIcon');
    }
    nav.find('li>ul>.active').parents('.sr_toggle').addClass('active');
    nav.on('click', '.sr_toggle .sr_indicator', function (e) {
        e.preventDefault();
        $(this).siblings('ul').slideToggle('fast');
    });
    nav.find('.sr_toggle').hover(
        function () {
            $(this).addClass('hover').siblings('.active').addClass('not');
        },
        function () {
            $(this).removeClass('hover').siblings('.active').removeClass('not');
        }
    );
    $('#sr-toggle').on('click', function (e) {
        e.preventDefault();
        nav.toggleClass('showIcon');
        $.cookie('sr_item_active', nav.hasClass('showIcon') ? '#sr_panel_left' : '');
        toggleSideNav();
    });
    var toggleSideNav = function () {
        if (nav.hasClass('showIcon')) {
            $('#sr_panel_right').removeClass('span10').addClass('showIcon');
            $('#sr-toggle i').removeClass().addClass('fa fa-chevron-circle-right')
        } else {
            $('#sr_panel_right').addClass('span10').removeClass('showIcon');
            $('#sr-toggle i').removeClass().addClass('fa fa-chevron-circle-left')
        }
    };
    toggleSideNav();
});