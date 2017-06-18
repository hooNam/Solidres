/*------------------------------------------------------------------------
 Solidres - Hotel booking extension for Joomla
 ------------------------------------------------------------------------
 @Author    Solidres Team
 @Website   http://www.solidres.com
 @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
 @License   GNU General Public License version 3, or later
 ------------------------------------------------------------------------*/

if (typeof(Solidres) === 'undefined') {
    var Solidres = {};
}

Solidres.context = 'frontend';

Solidres.setCurrency = function(id) {
    Solidres.jQuery.ajax({
        type: 'POST',
        url: window.location.pathname,
        data: 'option=com_solidres&format=json&task=currency.setId&id='+parseInt(id),
        success: function(msg) {
            location.reload(true);
        }
    });
};

function isAtLeastOnRoomTypeSelected() {
    var numberRoomTypeSelected = 0;
    Solidres.jQuery(".room-form").each(function () {
        if (Solidres.jQuery(this).children().length > 0) {
            numberRoomTypeSelected++;
            return;
        }
    });

    if (numberRoomTypeSelected > 0) {
        Solidres.jQuery('#sr-reservation-form-room button[type="submit"]').removeAttr('disabled');
    } else {
        Solidres.jQuery('#sr-reservation-form-room button[type="submit"]').attr('disabled', 'disabled');
    }
};

Solidres.jQuery(function($) {
    if (document.getElementById('sr-reservation-form-room')) {
        $('#sr-reservation-form-room').validate();
    }

    if (document.getElementById("sr-checkavailability-form")) {
        $("#sr-checkavailability-form").validate();
    }

	$(".roomtype-quantity-selection").change(function() {
		isAtLeastOnRoomTypeSelected();
	});

    if (document.getElementById("sr-availability-form")) {
        $("#sr-availability-form").validate();
    }

	function toggleRoomTypeDetails(target) {
		var room_type_details = $('div.' + target );
		if (room_type_details.hasClass('hidden')) {
			room_type_details.removeClass('hidden');
		} else {
			room_type_details.addClass('hidden');
		}
	}

	var currenthash = window.location.hash;
	if (currenthash.indexOf('room_type_details_handler') > -1) {
		toggleRoomTypeDetails(currenthash.substring(1));
	}

	$('.coupon').on('click', '#apply-coupon', function () {
        $.ajax({
            type: 'POST',
            url: window.location.pathname,
            data: 'option=com_solidres&format=json&task=coupon.applyCoupon&coupon_code=' + $('#coupon_code').val() + '&raid=' + $('input[name="id"]').val(),
            success: function(response) {
                if (response.status) {
                    location.reload(true);
                }
            },
            dataType: 'JSON'
        });
    });

    $('#sr-remove-coupon').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: window.location.pathname,
            data: 'option=com_solidres&format=json&task=reservation.removeCoupon&id=' + $(this).data('couponid'),
            success: function(response) {
                if (response.status) {
                    location.reload(true);
                } else {
                    alert(Joomla.JText._('SR_CAN_NOT_REMOVE_COUPON'));
                }
            },
            dataType: 'JSON'
        });
    });

    $('button.load-calendar').click(function() {
        var self = $(this);
        var id = self.data('roomtypeid');
		var target = $('#availability-calendar-' + id);

		self.empty().html('<i class="fa fa-calendar"></i> ' + Joomla.JText._('SR_PROCESSING'));
		self.attr('disabled', 'disabled');

		if (target.children().length == 0) {
			$.ajax({
				url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=reservationasset.getAvailabilityCalendar&id=' + id,
				success : function(html) {
					self.removeAttr('disabled');
					if (html.length > 0) {
						target.show().html(html);
						self.empty().html('<i class="fa fa-calendar"></i> ' + Joomla.JText._('SR_AVAILABILITY_CALENDAR_CLOSE'))
					}
				}
			});
		} else {
			target.empty().hide();
			self.empty().html('<i class="fa fa-calendar"></i> ' + Joomla.JText._('SR_AVAILABILITY_CALENDAR_VIEW'));
			self.removeAttr('disabled');
		}
    });

    function loadRoomForm(self) {
        var rtid = self.data('rtid');
        var raid = self.data('raid');
		var tariffid = self.data('tariffid');
		var adjoininglayer = self.data('adjoininglayer');

        $.ajax({
            type: 'GET',
            url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=reservationasset.getRoomTypeForm',
            data: {rtid: rtid, raid: raid, tariffid: tariffid, quantity: self.val() > 0 ? self.val() : 1, adjoininglayer: adjoininglayer},
            success: function(data) {
                self.parent().find('.processing').css({'display': 'none'});
                $('#room-form-' + rtid + '-' + tariffid).empty().show().html(data);
                $('.sr-reservation-form').validate();
				var updateChildAgeDropdown = false; // trigger change at this time will update the child age form too, we dont want that!
                $('#solidres .room #room-form-' + rtid + '-' + tariffid + ' .trigger_tariff_calculating').trigger('change', [updateChildAgeDropdown]);
                isAtLeastOnRoomTypeSelected();
            }
        });
    }

    // In case the page is reloaded, we have to reload the previous submitted room type selection form
    $('.roomtype-quantity-selection').each(function() {
        var self = $(this);
        if ( self.val() > 0) {
            self.parent().find('.processing').css({'display': 'block'});
			$('#selected_tariff_' + self.data('rtid') + '_' + self.data('tariffid')).removeAttr("disabled");
            loadRoomForm(self);
        }
    });

    $('.roomtype-quantity-selection').change(function() {
        var self = $(this);
		var tariffid = self.data('tariffid');
		var rtid = self.data('rtid');
		var totalRoomsLeft = self.data('totalroomsleft');
		var currentQuantity = parseInt(self.val());
		var currentSelectedRoomTypeRooms = 0;
		var totalSelectableRooms = 0;
        if ( currentQuantity > 0) {
			self.parent().find('.processing').css({'display': 'block'});
			$('#selected_tariff_' + rtid + '_' + tariffid).removeAttr("disabled");
            loadRoomForm(self);
        } else {
            $('#room-form-' + rtid + '-' + tariffid).empty().hide();
			$('input[name="jform[selected_tariffs][' + rtid + ']"]').attr("disabled", "disabled");
			$('#selected_tariff_' + rtid + '_' + tariffid).attr("disabled", "disabled");
            isAtLeastOnRoomTypeSelected();
        }

		$('.quantity_' + rtid).each(function() {
			var s = $(this);
			var val = parseInt(s.val());
			if (val > 0) {
				currentSelectedRoomTypeRooms += val;
			}
		});

		totalSelectableRooms = totalRoomsLeft - currentSelectedRoomTypeRooms;

		$('.quantity_' + rtid).each(function() {
			var s = $(this);
			var val = parseInt(s.val());
			var from = 0;
			if (val > 0) {
				from = val + totalSelectableRooms;
			} else {
				from = totalSelectableRooms;
			}
			disableOptions(s, from);
		});

		if (totalSelectableRooms > 0 && totalSelectableRooms < totalRoomsLeft) {
			$('#num_rooms_available_msg_' + rtid).empty().text(Joomla.JText._('SR_ONLY_' + totalSelectableRooms + '_LEFT'));
		} else if (totalSelectableRooms == 0) {
			$('#num_rooms_available_msg_' + rtid).empty();
		} else {
			$('#num_rooms_available_msg_' + rtid).empty().text($('#num_rooms_available_msg_' + rtid).data('original-text'));
		}
    });

    $('.roomtype-reserve').click(function() {
        var self = $(this);
        var tariffid = self.data('tariffid');
        var rtid = self.data('rtid');
        if ( $("#room-form-" + rtid + "-" + tariffid).children().length == 0) {
            $('#room_type_row_' + rtid + ' .room-form').empty().hide();
            self.siblings('.processing').css({'display': 'block'});
            $('#selected_tariff_' + rtid + '_' + tariffid).removeAttr("disabled");
            loadRoomForm(self);
        } else {
            $('#room-form-' + rtid + '-' + tariffid).empty().hide();
            $('input[name="jform[selected_tariffs][' + rtid + ']"]').attr("disabled", "disabled");
            $('#selected_tariff_' + rtid + '_' + tariffid).attr("disabled", "disabled");
        }
        isAtLeastOnRoomTypeSelected();
    });

    function disableOptions(selectEl, from) {
        $('option', selectEl).each(function() {
            var val = parseInt($(this).attr('value'));
            if (val > from) {
                $(this).attr('disabled', 'disabled');
            } else {
                $(this).removeAttr('disabled');
            }
        });
    }

    $('.guestinfo').on('click', 'input:checkbox', function() {
        var self = $(this);
        if (self.is(':checked')) {
            $('.' + self.data('target') ).removeAttr('disabled');
        } else {
            $('.' + self.data('target') ).attr('disabled', 'disabled');
        }
    });

    $('.room-form').on('click', 'input:checkbox', function() {
        var self = $(this);
        if (self.is(':checked')) {
            $('.' + self.data('target') ).removeAttr('disabled');
        } else {
            $('.' + self.data('target') ).attr('disabled', 'disabled');
        }
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
				}
			},
			"json"
		);
	});

	$('.trigger_checkinoutform').click(function() {
		var self = $(this);
		var tariffId = self.data('tariffid');
		var roomtypeId = self.data('roomtypeid');
		var oldLabel = self.text();

		if (tariffId != '') {
			$('.checkinoutform').empty();
			self.text(Joomla.JText._('SR_PROCESSING'));
			$.ajax({
				type: 'GET',
				data: {Itemid : self.data('itemid'), id: self.data('assetid'), roomtype_id: roomtypeId, tariff_id : tariffId},
				url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=reservationasset.getCheckInOutForm',
				success: function(data) {
					$('.checkinoutform').empty();
					$('#checkinoutform-' + roomtypeId + '-' + tariffId).show().empty().html(data);
					$('#room-form-' + roomtypeId + '-' + tariffId).empty();
					self.text(oldLabel);
				}
			});
		}
	});

	$('#solidres').on('click', '.searchbtn', function() {
		var tariffid = $(this).data('tariffid');
		var roomtypeid = $(this).data('roomtypeid');
        if (Solidres.options.get('AutoScroll') == 1) {
            $('#sr-checkavailability-form-component').attr('action', $('#sr-checkavailability-form-component').attr('action') + '#tariff-box-' + roomtypeid + '-' + tariffid);
        }
		$('#sr-checkavailability-form-component input[name=checkin]').val($('#tariff-box-' + roomtypeid + '-' + tariffid + ' input[name="checkin"]').val());
		$('#sr-checkavailability-form-component input[name=checkout]').val($('#tariff-box-' + roomtypeid + '-' + tariffid + ' input[name="checkout"]').val());
		$('#sr-checkavailability-form-component input[name=ts]').val($('input[name=fts]').val());
		$('#sr-checkavailability-form-component').submit();
	});

	$('.toggle_more_desc').click(function() {
		var self = $(this);
		$('#more_desc_' + self.data('target')).toggle();
		if ($('#more_desc_' + self.data('target')).is(':visible')) {
			self.empty().html('<i class="fa fa-eye"></i> ' + Joomla.JText._('SR_HIDE_MORE_INFO'));
		} else {
			self.empty().html('<i class="fa fa-eye-slash"></i> ' + Joomla.JText._('SR_SHOW_MORE_INFO'));
		}

	});

	$('#sr-reservation-form-room').on('click', '.checkin_roomtype', function() {
		if (!$(this).hasClass("disabledCalendar")) {
			$('.checkin_datepicker_inline').slideToggle('slow', function() {
				if ($(this).is(":hidden")) {
					$(".checkout_roomtype").removeClass("disabledCalendar");
				} else {
					$(".checkout_roomtype").addClass("disabledCalendar");
				}
			});
		}
	});
    
	$('#sr-reservation-form-room').on('click', '.checkout_roomtype', function() {
		if (!$(this).hasClass("disabledCalendar")) {
			$('.checkout_datepicker_inline').slideToggle('slow', function() {
				if ($(this).is(":hidden")) {
					$(".checkin_roomtype").removeClass("disabledCalendar");
				} else {
					$(".checkin_roomtype").addClass("disabledCalendar");
				}
			});
		}
	});

	$('.guestinfo').on('click', '#register_an_account_form', function() {
		var self = $(this);
		if (self.is(':checked')) {
			$('.' + self.attr('id') ).show();
		} else {
			$('.' + self.attr('id') ).hide();
		}
	});

	$('.toggle-tariffs').click(function() {
		var self = $(this);
		var target = $('#tariff-holder-' + self.data('roomtypeid'));
		target.toggle();
		if (target.is(":hidden")) {
			self.html('<i class="fa fa-expand"></i> ' + Joomla.JText._('SR_SHOW_TARIFFS'));
		} else {
			self.html('<i class="fa fa-compress"></i> ' + Joomla.JText._('SR_HIDE_TARIFFS'));
		}
	});

	var hash = location.hash;

	if (hash.indexOf('tariff-box') > -1) {
		var $el = $(hash),
			x = 1500,
			originalColor = $el.css("backgroundColor"),
			targetColor = $el.data("targetcolor");

		$el.css("backgroundColor", "#" + targetColor);
		setTimeout(function(){
			$el.css("backgroundColor", originalColor);
		}, x);
	}

    $('.filter_checkin_checkout').datepicker({
        numberOfMonths : 1,
        showButtonPanel : true,
        dateFormat : 'dd-mm-yy',
        firstDay: 1
    });
    
    $('#toggle_login_form').click(function () {
        $('#solidres-inline-login-form').toggle();
    });

    $('html').click(function(e) {
        if (!$(e.target).hasClass('datefield')
            &&
            $(e.target).closest('div.ui-datepicker').length == 0
            &&
            $(e.target).closest('a.ui-datepicker-prev').length == 0
            &&
            $(e.target).closest('a.ui-datepicker-next').length == 0
            &&
            $(e.target).closest('div.ui-datepicker-buttonpane').length == 0
        ) {
            $(".datepicker_inline").hide();
            $(".datefield").removeClass("disabledCalendar");
        }
    });
});