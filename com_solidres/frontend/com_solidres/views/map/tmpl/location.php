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
$protocol = JFactory::getConfig()->get('force_ssl') == 2 ? 'https' : 'http';
$solidresParams = JComponentHelper::getParams('com_solidres');
$googleMapApiKey = $solidresParams->get('google_map_api_key', '');
$doc->addScript( $protocol . '://maps.google.com/maps/api/js' . (!empty($googleMapApiKey) ? '?key=' . $googleMapApiKey : '' ));

?>

<div id="inline_location_map"></div>

<script>
	Solidres.jQuery(function($) {
		var map;
		var marker;
		var markers = new Array();
		var infowindow = new google.maps.InfoWindow({
			maxWidth: 160
		});

		$.ajax({
			url: Solidres.options.get('BaseURI') + 'index.php?option=com_solidres&task=map.getMarkers&format=json&location=<?php echo $this->location ?>',
			data: {},
			dataType:"json",
			success: function(data) {
				// Setup the different icons and shadows

				var iconCounter = 0;
				map = new google.maps.Map(document.getElementById('inline_location_map'), {
					zoom: 10,
					center: new google.maps.LatLng(-37.92, 151.25),
					mapTypeId: google.maps.MapTypeId.ROADMAP
				});

				for (var i = 0; i < data.length; i++) {
					marker = new google.maps.Marker({
						position: new google.maps.LatLng(data[i]['lat'], data[i]['lng']),
						map: map,
						icon : '<?php echo SRURI_MEDIA ?>/assets/images/icon-hotel-' + data[i]['rating'] + '.png'
					});

					markers.push(marker);

					google.maps.event.addListener(marker, 'click', (function(marker, i) {
						return function() {
							infowindow.setContent('<h4>'+data[i]['name']+'</h4>' +
								'<p>'+ data[i]['address_1'] +'</p>');
							infowindow.open(map, marker);
						}
					})(marker, i));
				}

				var bounds = new google.maps.LatLngBounds();
				$.each(markers, function (index, marker) {
					bounds.extend(marker.position);
				});
				map.fitBounds(bounds);
			}
		});
	});
</script>