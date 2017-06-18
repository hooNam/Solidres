/*------------------------------------------------------------------------
 Solidres - Hotel booking extension for Joomla
 ------------------------------------------------------------------------
 @Author    Solidres Team
 @Website   http://www.solidres.com
 @Copyright Copyright (C) 2013 - 2017 Solidres. All Rights Reserved.
 @License   GNU General Public License version 3, or later
 ------------------------------------------------------------------------*/

Solidres.options = {
	data: {},
	'get': function(key, def) {
		return typeof this.data[key.toUpperCase()] !== 'undefined' ? this.data[key.toUpperCase()] : def;
	},
	load: function(object) {
		for (var key in object) {
			this.data[key.toUpperCase()] = object[key];
		}
		return this;
	}
};
function isAtLeastOneRoomSelected () {
    var numberRoomTypeSelected = 0;
    Solidres.jQuery(".reservation_room_select").each(function() {
        if (Solidres.jQuery(this).is(':checked')) {
            numberRoomTypeSelected ++;
            return;
        }
    });

    if (numberRoomTypeSelected > 0) {
        Solidres.jQuery('#sr-reservation-form-room button[type="submit"]').removeAttr('disabled');
    } else {
        Solidres.jQuery('#sr-reservation-form-room button[type="submit"]').attr('disabled', 'disabled');
    }
};
function ajaxProgressMedia(iframe) {
    var $ = Solidres.jQuery;
    if (iframe) {
        var
            targetId = window.parent.Solidres.options.get('targetId'),
            token = window.parent.Solidres.options.get('token'),
            uriBase = window.parent.Solidres.options.get('uriBase'),
            target = window.parent.Solidres.options.get('target'),
            mediaList = $('#item-form', window.parent.document).find('input[name="jform[mediaId][]"]');
    } else {
        var
            targetId = Solidres.options.get('targetId'),
            token = Solidres.options.get('token'),
            uriBase = Solidres.options.get('uriBase'),
            target = Solidres.options.get('target'),
            mediaList = $('#item-form').find('input[name="jform[mediaId][]"]');
    }
    if (mediaList.length && targetId && targetId > 0 && token) {
        var mediaKeys = [];
        mediaList.each(function () {
            mediaKeys.push($(this).val());
        });
        $.ajax({
            url: uriBase + 'index.php?option=com_solidres&task=media.ajaxProgressMedia&format=json',
            type: 'post',
            dataType: 'json',
            data: {
                targetId: targetId,
                mediaKeys: mediaKeys,
                target: target,
                token: token
            },
            success: function (response) {
                console.log(response);
            }
        });
    }
}
Solidres.jQuery(function($) {
	$('#solidres').on('click', '.reservation-navigate-back', function() {
		$('.reservation-tab').removeClass('active');
		$('.reservation-single-step-holder').removeClass('nodisplay').addClass('nodisplay');
		var self = $(this);
		var currentstep = self.data('step');
		var prevstep = self.data('prevstep');
		var active = $('.' + prevstep).removeClass('nodisplay');
		active.find('button[type=submit]').removeAttr('disabled');
		$('.reservation-tab-' + prevstep).addClass('active').removeClass('complete');
		$('.reservation-tab-' + prevstep + ' span.badge').removeClass('badge-success').addClass('badge-info');
		$('.reservation-tab-' + currentstep + ' span.badge').removeClass('badge-info');
	});

	$('.confirmation').on('click', '#termsandconditions', function() {
		var self = $(this),
			submitBtn = $('.confirmation').find('button[type=submit]');
		if (self.is(':checked')) {
			submitBtn.removeAttr('disabled');
		} else {
			submitBtn.attr('disabled', 'disabled');
		}
	});

	$('#media-select-all').click(function() {
		$('.media-checkbox').prop('checked', true);
	});

	$('#media-deselect-all').click(function() {
		$('.media-checkbox').prop('checked', false);
	});

	if ($('.media-sortable').length) {
		$('.media-sortable').sortable({
            placeholder: "media-sortable-placeholder",
            update: function (event, ui) {
                ajaxProgressMedia(false);
            }
		});
		$('.media-sortable').disableSelection();
	}

	$('#media-library-delete').click(function(e) {
		var form = $('#medialibraryform');
		form.off('submit').on('submit', function(event) {
			event.preventDefault();
			var self = $(this), url = self.attr( 'action' );
			$.post( url, self.serialize(), function(response) {
				$.each(response, function(key, val) {
					$('#sr_media_' + val).parent().remove();
				});
				$('#media-messsage').empty().append(
					'<div class="alert alert-success">' + response.length + ' media deleted.' + '</div>'
				);
				$.ajax({
					url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=medialist.show&format=json',
					data: {start: self.data('start'), limit: 5},
					dataType: 'JSON',
					success: function(data) {
						$( "#medialibrary").empty().html(data.html);
						$( "#medialibraryform .pagination").empty().html(data.pagination);
					}
				});
			}, 'json');
		});
	});

	$('#medialibraryform').on('click', ' .pagination ul li a', function (e) {
		e.preventDefault();
		$('#medialibraryform .pagination ul li').removeClass('active');
		var self = $(this);
		var q = $('#medialibraryform #mediasearch');
		self.parent().addClass('active');
		$.ajax({
			url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=medialist.show&format=json',
			data: {start: self.data('start'), limit: 5, q: q.val()},
			dataType: 'JSON',
			success: function(data) {
				$( "#medialibrary").empty().html(data.html);
				$( "#medialibraryform .pagination").empty().html(data.pagination);
			}
		});
	});

	$('#medialibraryform').submit(function (e) {
		e.preventDefault();
		$('#medialibraryform .pagination ul li').removeClass('active');
		var self = $(this);
		var q = $('#medialibraryform #mediasearch');
		self.parent().addClass('active');
		$.ajax({
			url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=medialist.show&format=json',
			data: {start: self.data('start'), limit: 5, q: q.val()},
			dataType: 'JSON',
			success: function(data) {
				$( "#medialibrary").empty().html(data.html);
				$( "#medialibraryform .pagination").empty().html(data.pagination);
			}
		});
	});

	$('#media-library-insert').click(function(e) {
		e.preventDefault();
		$('#medialibrary input:checked').each(function() {
			if(window.parent !== null) {
				// Only insert if it was not inserted before
				var media = $(this).parent().prev();
				var mediaCssID = media.attr('id');
				var mediaName = media.attr('title');
				if($('#'+mediaCssID, window.parent.document).length == 0) {
					var a = $('<li>');
					var b = media.clone();
					var c = $('<input/>',{
                        'type': 'hidden',
                        'name': 'jform[mediaId][]',
						'value': mediaCssID.substring(9)
					});
                    var d = $('<button/>', {
                        'type': 'button',
                        'class': 'btn btn-mini btn-danger btn-remove',
                        'html': '<i class="fa fa-trash"></i>'
                    });
                    $('#media-holder', window.parent.document).append(a.append(b,c,mediaName,$('<p>').append(d)));
				}
			}
		});

        if (window.parent) {
            ajaxProgressMedia(true);
        }
		parent.jQuery.fn.colorbox.close();
    });

    $(document).on('click', '#media-holder .btn-remove', function () {
        var el = $(this),
            targetId = Solidres.options.get('targetId'),
            target = Solidres.options.get('target'),
            token = Solidres.options.get('token'),
            uriBase = Solidres.options.get('uriBase');
        if (targetId && targetId > 0 && token) {
            el.find('>.fa').removeClass('fa-trash').addClass('fa-spin fa-spinner');
            $.ajax({
                url: uriBase + 'index.php?option=com_solidres&task=media.ajaxRemoveMedia&format=json',
                type: 'post',
                dataType: 'json',
                data: {
                    targetId: targetId,
                    target: target,
                    mediaId: el.parent().siblings('input[name="jform[mediaId][]"]').val(),
                    token: token
                },
                success: function (response) {
                    if (response.status) {
                        el.parents('li').remove();
                    } else {
                        var message = $('<p class="label label-error">' + response.message + '</label>');
                        el.after(message);
                        window.setTimeout(function () {
                            message.remove();
                        }, 3000);
                    }
                }
            });
        }else{
            el.parents('li').remove();
        }
	});

	$('#solidres .guestinfo').on('change', '.country_select', function() {
		$.ajax({
			url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&format=json&task=states.find&id=' + $(this).val(),
			success : function(html) {
				$('.state_select').empty();
				if (html.length > 0) {
					$('.state_select').html(html);
				}
			}
		});
	});

	$('#solidres .room').on('change', '.trigger_tariff_calculating', function(event, updateChildAgeDropdown) {
		var self = $(this);
		var raid = self.data('raid');
		var roomtypeid = self.data('roomtypeid');
		var roomindex = self.data('roomindex');
		var roomid = self.data('roomid');
		var tariffid = self.attr('data-tariffid');
		var adjoininglayer = self.attr('data-adjoininglayer');

		if (Solidres.context == "frontend" && Solidres.options.get('Hub_Dashboard') != 1) {
			var  target = roomtypeid + '_' + tariffid + '_' + roomindex;
		} else {
			var  target = roomtypeid + '_' + tariffid + '_' + roomid;
		}

		var adult_number = 1;
		if ($("select.adults_number[data-identity='" + target + "']").length) {
			adult_number = $("select.adults_number[data-identity='" + target + "']").val();
		}
		var child_number = 0;
		if ($("select.children_number[data-identity='" + target + "']").length) {
			child_number = $("select.children_number[data-identity='" + target + "']").val();
		}

		if (typeof updateChildAgeDropdown === 'undefined' || updateChildAgeDropdown === null ) {
			updateChildAgeDropdown = true;
		}

		if ( !updateChildAgeDropdown && self.hasClass('reservation-form-child-quantity') ) {
			return;
		}

		if (self.hasClass('reservation-form-child-quantity') && child_number >= 1 ) {
			return;
		}

		var data = {};
		data.raid = raid;
		data.room_type_id = roomtypeid;
		data.room_index = roomindex;
		data.room_id = roomid;
		data.adult_number = adult_number;
		data.child_number = child_number;
		data.tariff_id = tariffid;
		data.adjoining_layer = adjoininglayer;
        data.extras = [];

		for (var i = 0; i < child_number; i++) {
			var prop_name = 'child_age_' + target + '_' + i;
			data[prop_name] = $('.' + prop_name).val();
		}

		var roomExtrasCheckboxes = $(".extras_row_roomtypeform_" + target + " input[type='checkbox']");

        if (roomExtrasCheckboxes.length) {
            roomExtrasCheckboxes.each(function() {
                if (this.checked) {
                    data[$(this).attr('data-target')] = $(this).parent().find('select').val();
                    data.extras.push($(this).attr('data-extraid'));
                }
            });
        }

		$.ajax({
			type: 'GET',
			url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=reservationasset' + (Solidres.context == "frontend" ? "" : "base") + '.calculateTariff&format=json',
			data: data,
			success: function(data) {
				if (!data.room_index_tariff.code && !data.room_index_tariff.value) {
					$( '.tariff_' +  target ).text('0');
				} else {
					$( '.tariff_' +  target ).text(data.room_index_tariff.formatted);
					$( '#breakdown_' + target ).empty().html(data.room_index_tariff_breakdown_html);
				}
			},
			dataType: "json"
		});
	});

	$('#solidres').on('click', '.toggle_breakdown', function() {
		var target = $(this).attr('data-target');
		$('#breakdown_' + target).toggle();
	});

	$('#solidres').on('click', '.toggle_extra_details', function() {
		var target = $(this).data('target');
		$('#' + target).toggle();
	});

	$('#solidres').on('click', '.toggle_extracost_confirmation', function() {
		var target = $('.extracost_confirmation');
		var self = $(this);
		target.toggle();
		if (target.is(":hidden")) {
			$('.extracost_row').removeClass().addClass('nobordered extracost_row');
		} else {
			$('.extracost_row').removeClass().addClass('nobordered extracost_row first');
		}
	});

	$('#solidres').on('change', '.reservation-form-child-quantity', function (event, updateChildAgeDropdown) {
		if (typeof updateChildAgeDropdown === 'undefined' || updateChildAgeDropdown === null ) {
			updateChildAgeDropdown = true;
		}
		if (!updateChildAgeDropdown) {
			return;
		}
		var self = $(this);
		var quantity = self.val();
		var html = '';
		var raid = self.data('raid');
		var roomtypeid = self.data('roomtypeid');
		var roomid = self.data('roomid');
		var roomindex = self.data('roomindex');
		var tariffid = self.data('tariffid');
        var child_age_holder = self.parent().find('.child-age-details');

        if (quantity > 0) {
            child_age_holder.removeClass('nodisplay');
		} else {
            child_age_holder.addClass('nodisplay');
		}

		for (var i = 0; i < quantity; i ++) {
			html += '<li>' + Joomla.JText._('SR_CHILD') + ' ' + (i + 1) +
			' <select name="jform[room_types][' + roomtypeid + '][' + tariffid + ']['+ (Solidres.context == "frontend" && Solidres.options.get('Hub_Dashboard') != 1 ? roomindex : roomid) +'][children_ages][]" ' +
			'data-raid="' + raid + '"' +
			'data-roomtypeid="' + roomtypeid + '"' +
			'data-roomid="' + roomid + '"' +
			'data-roomindex="' + roomindex + '"' +
			'data-tariffid="' + tariffid + '"' +
			'required ' +
			'class="span6 child_age_' + roomtypeid + '_' + tariffid + '_' + (Solidres.context == "frontend" && Solidres.options.get('Hub_Dashboard') != 1 ? roomindex : roomid) + '_' + i + ' trigger_tariff_calculating"> ';

			html += '<option value=""></option>';

			for (var age = 1; age <= Solidres.child_max_age_limit; age ++) {
				html += '<option value="' + age + '">' +
				(age > 1 ? age + ' ' + Joomla.JText._('SR_CHILD_AGE_SELECTION_JS') : age + ' ' + Joomla.JText._('SR_CHILD_AGE_SELECTION_1_JS'))  +
				'</option>';
			}

			html += '</select></li>';
		}

        child_age_holder.find('ul').empty().append(html);
	});

	var submitReservationForm = function(form) {
		var self = $(form),
			url = self.attr( 'action'),
			formHolder = self.parent('.reservation-single-step-holder'),
			submitBtn = self.find('button[type=submit]'),
			currentStep = submitBtn.data('step');

		submitBtn.attr('disabled', 'disabled');
		submitBtn.html('<i class="fa fa-arrow-right"></i> ' + Joomla.JText._('SR_PROCESSING'));
		if ($("div.wizard").length > 0) {
			$('html, body').animate({
				scrollTop: $("div.wizard").offset().top
			}, 700);
		}
		$.post( url, self.serialize(), function(data) {
			if (data.status == 1) {
				$.ajax({
					type: 'GET',
					cache: false,
					url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=reservation' + (Solidres.context == 'backend' ? 'base' : '') + '.progress&next_step='+data.next_step,
					success: function(response) {
						formHolder.addClass('nodisplay');
						submitBtn.removeClass('nodisplay');
						submitBtn.html('<i class="fa fa-arrow-right"></i> ' + Joomla.JText._('SR_NEXT'));
						var next = $('.' + data.next_step);
						next.removeClass('nodisplay');
						next.empty().append(response);
						if (data.next == 'payment') {
							$.metadata.setType("attr", "validate");
						}
						location.hash = '#form';
						$('.reservation-tab').removeClass('active');
						$('.reservation-tab-' + currentStep).addClass('complete');
						$('.reservation-tab-' + currentStep + ' span.badge').removeClass('badge-info').addClass('badge-success');
						$('.reservation-tab-' + data.next_step).addClass('active');
						$('.reservation-tab-' + data.next_step + ' span.badge').addClass('badge-info');
						var next_form = next.find('form.sr-reservation-form');
						if (next_form.attr('id') == 'sr-reservation-form-guest') {
							next_form.validate({
								rules: {
									'jform[customer_email]': {required: true, email: true},
									'jform[payment_method]': {required: true},
									'jform[customer_password]': {require: false, minlength: 8},
									'jform[customer_username]': {
										required: false,
										remote: {
											url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=user.check&format=json',
											type: 'POST',
											data: {
												username: function () {
													return $('#username').val();
												}
											}
										}
									}
								},
								messages: {
									'jform[customer_username]': {
										remote: Joomla.JText._('SR_USERNAME_EXISTS')
									}
								}
							});
							$(".popover_payment_methods").popover({
								"trigger": "click",
								"placement": "bottom"
							});

							$('.extra_desc_tips').popover('destroy');
							$('.extra_desc_tips').popover({
								html: true,
								placement: "bottom",
								trigger: "click"
							});

                            if (typeof onSolidresAfterSubmitReservationForm === 'function') {
                                onSolidresAfterSubmitReservationForm();
                            }
						} else {
							next_form.validate();
						}

						if (next.hasClass('confirmation')) {
							$('.toggle_room_confirmation').click(function () {
								var self = $(this);
								$('#rc_' + self.data('target')).toggle();
							});
						}
					}
				});
			}
		}, "json");
	}

	$('#solidres').on('submit', 'form.sr-reservation-form', function (event) {
		event.preventDefault();
		submitReservationForm(this);
	});

    $('.roomtype-reserve-exclusive').click(function () {
        var self = $(this);
        var tariffid = self.data('tariffid');
        var rtid = self.data('rtid');
        self.siblings('input[name="jform[room_types][' + rtid + '][' + tariffid + '][1][adults_number]"]').removeAttr('disabled');
        submitReservationForm(document.getElementById('sr-reservation-form-room'));
    });

	$.fn.srRoomType = function(params) {
		params = $.extend( {}, params);

		var bindDeleteRoomRowEvent = function() {
			$('.delete-room-row').unbind().click(function() {
				removeRoomRow(this);
			});
		};

		bindDeleteRoomRowEvent();

		removeRoomRow = function(delBtn) {
			var thisDelBtn  = $(delBtn),
				nextSpan    = thisDelBtn.next(),
				btnId       = thisDelBtn.attr('id');

			nextSpan.addClass('ajax-loading');
			if(btnId != null) {
				roomId = btnId.substring(16);
				$.ajax({
					url     : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=roomtype' + (Solidres.context == 'frontend' ? 'frontend' : '') + '.checkRoomReservation&tmpl=component&format=json&id=' + roomId,
					context : document.body,
					dataType: "JSON",
					success : function(rs){
						nextSpan.removeClass('ajax-loading');
						if(!rs) {
							// This room can NOT be deleted
							nextSpan.addClass('delete-room-row-error');
							nextSpan.html(Joomla.JText._('SR_FIELD_ROOM_CAN_NOT_DELETE_ROOM') +
							' <a class="room-confirm-delete" data-roomid="' + roomId + '" href="#">Yes</a> | <a class="room-cancel-delete" href="#">No</a>');
							$('.tier-room').on('click', '.room-confirm-delete', function() {
								$.ajax({
									url     : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=roomtype' + (Solidres.context == 'frontend' ? 'frontend' : '') + '.removeRoomPermanently&tmpl=component&format=json&id=' + roomId,
									context : document.body,
									dataType: "JSON",
									success : function(rs){
										if(!rs) {

										} else {
											// This room can be deleted
											thisDelBtn.parent().parent().remove();
										}
									}
								});
							});
							$('.tier-room').on('click', '.room-cancel-delete', function() {
								nextSpan.html('');
							});
						} else {
							// This room can be deleted
							thisDelBtn.parent().parent().remove();
						}
					}
				});
			} else {
				// New room, can be deleted since it has not had any relationship with Reservation yet
				thisDelBtn.parent().parent().remove();
			}
		},

			initRoomRow = function() {
				var rowIdRoom   = params.rowIdRoom,
					currentId   = 'tier-room-' + rowIdRoom,
					htmlStr     = '';
				$('#room_tbl tbody').append('<tr id="' + currentId + '" class="tier-room"></tr>');
				var a   = $('#' + currentId);
				htmlStr += '<td><a class="delete-room-row btn btn-default"><i class="fa fa-minus"></i></a></td>';
				htmlStr += '<td><input type="text" name="jform[rooms][' + rowIdRoom + '][label]" required />';
				htmlStr += '<input type="hidden" name="jform[rooms][' + rowIdRoom + '][id]" value="new" /></td>';

				a.append(htmlStr);
				bindDeleteRoomRowEvent();
			};

		$('#new-room-tier').click( function(event) {
			event.preventDefault();
			initRoomRow();
			params.rowIdRoom ++;
		});

		return this;
	};

	$('#jform_reservation_asset_id').change( function(event) {
		$.ajax({
			url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&format=json&task=coupons' + (Solidres.context == 'frontend' ? 'frontend' : '') + '.find&id=' + $(this).val(),
			success : function(html) {
				$('#coupon-selection-holder').empty().html(html);
			}
		});
		$.ajax({
			url : Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&format=json&task=extras' + (Solidres.context == 'frontend' ? 'frontend' : '') + '.find&id=' + $(this).val(),
			success : function(html) {
				$('#extra-selection-holder').empty().html(html);
			}
		});
	});

	$('#solidres').on('change', '.occupancy_max_constraint', function() {
		var self = $(this);
		var max = self.data('max');
        var min = self.data('min');
		var roomtypeid = self.data('roomtypeid');
		var leftover = 0;
		var totalSelectable = 0;
		var roomindex = self.data('roomindex');
		var roomid = self.data('roomid');
		var tariffid = self.attr('data-tariffid');

		if (Solidres.context == "frontend") {
			var  target = roomindex + '_' + tariffid + '_' + roomtypeid;
		} else {
			var  target = roomid + '_' + tariffid + '_' + roomtypeid;
		}

		if (max > 0) {
			$('.occupancy_max_constraint_' + target).each(function () {
				var s = $(this);
				var val = parseInt(s.val());
				if (val > 0) {
					leftover += val;
				}
			});

			totalSelectable = max - leftover;

			$('.occupancy_max_constraint_' + target).each(function() {
				var s = $(this);
				var val = parseInt(s.val());
				var from = 0;
				if (val > 0) {
					from = val + totalSelectable;
				} else {
					from = totalSelectable;
				}
				disableOptions(s, from);
			});
		}

        if (min > 0) {
            var totalAdultChildNumber = 0;
            $('.occupancy_max_constraint_' + target).each(function() {
                var s = $(this);
                var val = parseInt(s.val());
                if (val > 0) {
                    totalAdultChildNumber += val;
                }
            });
            if (totalAdultChildNumber < min) {
                $('#error_' + target).show();
                $('.occupancy_max_constraint_' + target).addClass('warning');
                $('#sr-reservation-form-room button[type="submit"]').attr('disabled', 'disabled');
            } else {
                $('#error_' + target).hide();
                $('.occupancy_max_constraint_' + target).removeClass('warning');
                $('#sr-reservation-form-room button[type="submit"]').removeAttr('disabled', 'disabled');
            }
        }
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

	$('#solidres').on('click', '.reservation_room_select', function() {
		var self = $(this);
		var room_selection_details = $('#room_selection_details_' + self.val());
		var priceTable = $('#room' + self.val() + ' dl dt table');
		var span = $('#room' + self.val() + ' dl dt label span');
		if (self.is(':checked')) {
			room_selection_details.show();
			priceTable.show();
			span.addClass('label-success');
			room_selection_details.children('select.tariff_selection').removeAttr('disabled');
			room_selection_details.children('input.guest_fullname').removeAttr('disabled');
			room_selection_details.children('select.adults_number').removeAttr('disabled');
			room_selection_details.children('select.children_number').removeAttr('disabled');
			$('#room_selection_details_' + self.val() + ' .extras_row_roomtypeform').each(function() {
				var li = $(this);
				var chk = li.children('input:checkbox');
				if (chk.is(':checked')) {
					var sel = li.children('select');
					sel.removeAttr('disabled');
				}
			});
		} else {
			room_selection_details.hide();
			priceTable.hide();
			span.removeClass('label-success');
			room_selection_details.children('select.tariff_selection').attr('disabled', 'disabled');
			room_selection_details.children('input.guest_fullname').attr('disabled', 'disabled');
			room_selection_details.children('select.adults_number').attr('disabled', 'disabled');
			room_selection_details.children('select.children_number').attr('disabled', 'disabled');
			room_selection_details.find('input:hidden').attr('disabled', 'disabled');
			room_selection_details.find('.extras_row_roomtypeform select').attr('disabled', 'disabled');
		}

		isAtLeastOneRoomSelected();
	});

	$('#solidres').on('click', '.room input:checkbox, .guestinfo input:checkbox', function() {
		var self = $(this);
		if (self.is(':checked')) {
			$('.' + self.data('target') ).removeAttr('disabled');
            $('.' + self.data('target') ).trigger('change');
		} else {
			$('.' + self.data('target') ).attr('disabled', 'disabled');
            $('.' + self.data('target') ).trigger('change');
		}
	});

    $('#solidres').on('click', '.guestinfo input#processonlinepayment', function() {
        var self = $(this);
        if (self.is(':checked')) {
            $('.' + self.data('target') ).show();
        } else {
            $('.' + self.data('target') ).hide();
        }
    });

	$('#solidres').on('change', '.tariff_selection', function() {
		var self = $(this);
        if (self.val() == '') {
            $('a.tariff_breakdown_' + self.data('roomid')).hide();
            $('span.tariff_breakdown_' + self.data('roomid')).text('0');
            return false;
        }
		var parent = self.parents('.room_selection_wrapper');
		var input = parent.find('.room_selection_details input[type="text"]');
		var checkboxes = parent.find('.room_selection_details input[type="checkbox"]');
		var select = parent.find('.room_selection_details select').not(self);
		var spans = parent.find('dt span');
		var breakdown_trigger = parent.find('dt a.toggle_breakdown');
		var breakdown_holder = parent.find('dt span.breakdown');
		var extra_input_hidden = parent.find('.extras_row_roomtypeform input[type="hidden"]');
		var adjoining_layer = self.find(':selected').data('adjoininglayer');

		input.attr('name', input.attr('name').replace(/^(jform\[room_types\])(\[[0-9]+\])(\[[-?0-9a-z]*\])(.*)$/, '$1$2[' + self.val() + ']$4'));
		if (extra_input_hidden.length > 0) {
			extra_input_hidden.attr('name', extra_input_hidden.attr('name').replace(/^(jform\[room_types\])(\[[0-9]+\])(\[[0-9a-z]*\])(.*)$/, '$1$2[' + self.val() + ']$4'));
		}

		select.each(function () {
			var self_sel = $(this);
			self_sel.attr('name', self_sel.attr('name').replace(/^(jform\[room_types\])(\[[0-9]+\])(\[[-?0-9a-z]*\])(.*)$/, '$1$2[' + self.val() + ']$4'));
			self_sel.attr('data-tariffid', self.val());
			if (self_sel.attr('data-identity')) {
				self_sel.attr('data-identity', self_sel.attr('data-identity').replace(/^([0-9]+)(_)([-?0-9a-z]*)(_)(.*)$/, '$1$2' + self.val() + '$4$5'));
			}
			self_sel.attr('data-adjoininglayer', adjoining_layer);
		});
		checkboxes.each(function() {
			$(this).removeAttr('disabled');
		});
		breakdown_trigger.attr('data-target', breakdown_trigger.data('target').replace(/^([0-9]+)(_)([0-9a-z]*)(_)(.*)$/, '$1$2' + self.val() + '$4$5'));
		breakdown_holder.attr('id', breakdown_holder.attr('id').replace(/^([a-z]+)(_)([0-9]+)(_)([-?0-9a-z]*)(_)(.*)$/, '$1$2$3$4' + self.val() + '$6$7'));
		spans.each(function () {
			var self_spa = $(this);
			self_spa.attr('class', self_spa.attr('class').replace(/^([a-z]+)(_)([0-9]+)(_)([-?0-9a-z]*)(_)(.*)$/, '$1$2$3$4' + self.val() + '$6$7'));
		});

		if (self.val() != '') {
			$('.tariff_breakdown_' + self.data('roomid')).show();
		} else {
			$('.tariff_breakdown_' + self.data('roomid')).hide();
		}

		$('#room' + self.data('roomid') + ' .adults_number.trigger_tariff_calculating').trigger('change');
	});

    $('#solidres').on('change paste keyup', '#sr-reservation-form-confirmation .total_price_tax_excl_single_line', function() {
        var sum = 0;
        $.each($('.total_price_tax_excl_single_line'), function() {
            sum += parseFloat($(this).val() != '' ? $(this).val() : 0 );
        });
        $('.total_price_tax_excl').text(sum);
        updateGrandTotal();
    });

    $('#solidres').on('change paste keyup', '#sr-reservation-form-confirmation .room_price_tax_amount_single_line', function() {
        var sum = 0;
        $.each($('.room_price_tax_amount_single_line'), function() {
            sum += parseFloat($(this).val() != '' ? $(this).val() : 0 );
        });
        $('.tax_amount').text(sum);
        updateGrandTotal();
    });

    $('#solidres').on('change paste keyup', '#sr-reservation-form-confirmation .extra_price_single_line', function() {
        var sum = 0;
        $.each($('.extra_price_single_line'), function() {
            sum += parseFloat($(this).val() != '' ? $(this).val() : 0 );
        });
        $('.total_extra_price').text(sum);
        updateGrandTotal();
    });

    $('#solidres').on('change paste keyup', '#sr-reservation-form-confirmation .extra_tax_single_line', function() {
        var sum = 0;
        $.each($('.extra_tax_single_line'), function() {
            sum += parseFloat($(this).val() != '' ? $(this).val() : 0 );
        });
        $('.total_extra_tax').text(sum);
        updateGrandTotal();
    });

    function updateGrandTotal() {
        sum = 0;
        $.each($('.grand_total_sub'), function() {
            sum += parseFloat($(this).text() != '' ? $(this).text() : 0 );
        });
        $('.grand_total').text(sum);
    }

    $('.toggle_child_ages').click(function () {
        $(this).next('ul').toggle();
    });
});